<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hostname extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fqdn', 'created_by', 
    ];

    public function userRoles(){
        return $this->hasMany('App\Models\UserRole');
    }

    public function tenantModules(){
        return $this->hasMany(TenantModule::class, 'tenant_id');
    }
    
}
