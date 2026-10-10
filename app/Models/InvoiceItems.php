<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItems extends Model
{
    protected $table = 'invoice_items';

    protected $primaryKey = 'invoice_item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'invoice_item_id',
        'invoice_id',
        'medicine_id',
        'description',
        'quantity',
        'unit_price',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'medicine_id');
    }
}