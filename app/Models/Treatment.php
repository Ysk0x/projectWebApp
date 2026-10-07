<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    protected $table = 'treatments';
    protected $primaryKey = 'treatment_id';
    public $incrementing = false;
    protected $keyType = 'string';
}
