<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notifications extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'mid', 'sender', 'receiver', 'status', 'type', 'message'];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
