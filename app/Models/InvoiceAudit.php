<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAudit extends Model
{
    protected $table = 'invoice_audits';

    protected $primaryKey = 'audit_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // ตารางนี้ใช้ audited_at แทน created_at / updated_at
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'audited_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'audited_by', 'user_id');
    }
}