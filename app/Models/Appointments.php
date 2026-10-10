<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    protected $table = 'appointments';

    protected $primaryKey = 'appointment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'appointment_id',
        'pet_id',
        'owner_id',
        'vet_id',
        'appointment_date',
        'appointment_time',
        'reason',
        'status',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'pet_id');
    }

    public function vet()
    {
        return $this->belongsTo(Users::class, 'vet_id', 'user_id');
    }
}