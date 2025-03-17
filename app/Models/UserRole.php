<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['tenant_id', 'user_id', 'role_id'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function hostname()
    {
        return $this->belongsTo('App\Models\Hostname');
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
