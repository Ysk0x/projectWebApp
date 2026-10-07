<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id', 'owner_id');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'pet_id');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'invoice_id');
    }
}