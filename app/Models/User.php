<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable; 
use Laravel\Lumen\Auth\Authorizable;   
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements JWTSubject, AuthenticatableContract
{
    use Authenticatable, Authorizable; 

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden   = ['password'];

    // JWT methods
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }
}
