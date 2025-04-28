<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{FinancialYear, Leave_Application, Leave_Category, User};
use Carbon\Carbon;
use DB;

class ResetLeaveBalances extends Command
{
    protected $signature = 'leave:reset';
    protected $description = 'Reset leave balances at the end of the financial year and carry over 10%';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now();

        // Get all tenants
        $financialYears = FinancialYear::all();

        foreach ($financialYears as $fy) {
            // Check if today matches their financial year end date
            if ($today->format('m-d') === $fy->end_date) {
                $this->resetTenantLeave($fy->tenant_id);
            }
        }

        $this->info('Leave balances reset successfully.');
    }

    public function resetTenantLeave(){
        $tenantId = auth()->user()->tenant_id;

        // Fetch the tenant's carryover setting
        $financialYear = FinancialYear::where('tenant_id', $tenantId)->first();
        
        if (!$financialYear) {
            return response()->json(['error' => 'Financial year settings not found'], 400);
        }

        $carryoverPercentage = $financialYear->carryover_percentage ?? 0; // Default to 0% if not set

        $categories = Leave_Category::all();
        $users = User::where('tenant_id', $tenantId)->get();

        foreach ($users as $user) {
            foreach ($categories as $category) {
                $totalAllocated = $category->days;

                $leaveTaken = Leave_Application::where('user_id', $user->id)
                    ->where('category_id', $category->id)
                    ->where('leave_year', date('Y'))
                    ->where('status', 'GRANTED')
                    ->sum('days');

                $remaining = max($totalAllocated - $leaveTaken, 0);
                $carryOver = floor(($carryoverPercentage / 100) * $remaining);

                // Optionally: Clean up old leave balances if needed

                // Insert carryover as a new record
                if ($carryOver > 0) {
                    Leave_Application::create([
                        'user_id' => $user->id,
                        'sid' => null,
                        'reliver_id' => null,
                        'category_id' => $category->id,
                        'start_date' => now(),
                        'end_date' => now(),
                        'days' => $carryOver,
                        'status' => 'CARRYOVER',
                        'leave_year' => date('Y', strtotime('+1 year')),
                    ]);
                }
            }
        }
        
        return response()->json(['message' => 'Leave balances reset successfully.']);
    }



}
