<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['name'];  // Allow mass assignment for 'name' column

    public function userRoles(){
        return $this->hasMany('App\Models\UserRole');
    }
}
