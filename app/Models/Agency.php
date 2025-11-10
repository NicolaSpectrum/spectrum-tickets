<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'address',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function celebrations()
    {
        return $this->hasMany(Celebration::class);
    }

    public function getDisplayNameAttribute()
    {
        return "{$this->name}" . ($this->contact_name ? " ({$this->contact_name})" : '');
    }
}
