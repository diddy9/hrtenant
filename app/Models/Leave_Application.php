<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Leave_Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'sid',
        'reliver_id',
        'category_id',
        'start_date',
        'end_date',
        'days',
        'status',
        'leave_year',
    ];

    protected $dates = ['start_date', 'end_date', 'deleted_at'];

     // Relationships
     public function user() {
        return $this->belongsTo(User::class);
    }

    public function supervisor() {
        return $this->belongsTo(User::class, 'sid');
    }

    public function reliever() {
        return $this->belongsTo(User::class, 'reliver_id');
    }

    public function category() {
        return $this->belongsTo(Leave_Category::class, 'category_id');
    }

    // Check if the leave falls within the tenant's financial year
    public function isWithinFinancialYear() {
        $tenantFinancialYear = FinancialYear::where('tenant_id', auth()->user()->tenant_id)->first();
        if (!$tenantFinancialYear) {
            return false;
        }

        $startDate = Carbon::parse($tenantFinancialYear->start_date);
        $endDate = Carbon::parse($tenantFinancialYear->end_date);

        return Carbon::parse($this->start_date)->between($startDate, $endDate);
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
