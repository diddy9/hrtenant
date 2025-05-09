<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pay_Staff_Details extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'user_id',
        'pid',
        'item_name',
        'amount',
        'type'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payslip()
    {
        return $this->belongsTo(Payslip::class, 'pid');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
