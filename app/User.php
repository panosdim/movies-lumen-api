<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'first_name', 'last_name', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'first_name',
        'last_name',
    ];

    public function getFirstNameAttribute($value)
    {
        return $this->attributes['first_name'];
    }

    public function getLastNameAttribute($value)
    {
        return $this->attributes['last_name'];
    }

    protected $maps = ['first_name' => 'firstName', 'last_name' => 'lastName'];

    protected $appends = ['firstName', 'lastName'];
}
