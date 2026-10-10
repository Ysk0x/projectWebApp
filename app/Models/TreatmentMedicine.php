<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentMedicine extends Model
{
    protected $table = 'treatment_medicines';

    protected $primaryKey = 'treatment_medicine_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'treatment_medicine_id',
        'treatment_id',
        'medicine_id',
        'quantity',
        'unit_price',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'treatment_id', 'treatment_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'medicine_id');
    }
}
