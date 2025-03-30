<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'start_date', 'end_date'];

    public function hostname()
    {
        return $this->belongsTo(Hostname::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
