<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayslipDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payslip_details';

    protected $fillable = [
        'tenant_id',
        'created_by',
        'desig_id',
        'item_name',
        'amount',
        'type',
        'status',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
