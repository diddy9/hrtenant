<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantModule extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['tenant_id', 'module_id'];

     // Relationship with Hostname (Tenant)
     public function tenant()
     {
         return $this->belongsTo(Hostname::class, 'tenant_id');
     }

      // Relationship with Module
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
