<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Celebration extends Model
{
      protected $fillable = [
        'agency_id',
        'created_by',
        'name',
        'description',
        'location',
        'start_date',
        'end_date',
        'max_tickets',
        'status',
        'ticket_types',
    ];

    protected $casts = [
    'ticket_types' => 'array',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

     public function registrations()
    {
        return $this->hasMany(Registration::class);
    }


}
