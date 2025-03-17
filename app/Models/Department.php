<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'tenant_id'];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }

}
