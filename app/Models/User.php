<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'profile_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function components()
    {
        return $this->hasMany(Component::class);
    }

    public function restocks()
    {
        return $this->hasMany(Restock::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

}
