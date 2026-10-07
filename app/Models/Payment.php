<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'payment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // ตาราง payments ไม่มี created_at / updated_at
    public $timestamps = false;

    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by', 'user_id');
    }
}