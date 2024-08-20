<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone'
    ];

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
