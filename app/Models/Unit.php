<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'department_id', 'tenant_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }

}
