<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'created_by',
        'gross',
        'deductions',
        'net',
        'desig_id',
        'payment_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'desig_id');
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
