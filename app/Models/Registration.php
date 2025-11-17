<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'celebration_id',
        'name',
        'email',
        'token',
        'qr_path',
        'checked_in',
        'checked_in_at',
        'verified_by',
        'id_type',
        'id_number',
    ];

   
    public function celebration()
    {
        return $this->belongsTo(Celebration::class);
    }


    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
