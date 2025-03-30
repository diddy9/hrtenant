<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave_Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'user_id', 'type', 'days'];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
