<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VetHelper
{
    /** สร้างรหัสถัดไป เช่น nextId('owners','owner_id','O',4) => O0011 */
    public static function nextId(string $table, string $column, string $prefix, int $digits): string
    {
        $max = DB::table($table)->where($column, 'like', $prefix . '%')->max($column);
        $n = $max ? (int) substr($max, strlen($prefix)) : 0;

        return $prefix . str_pad((string) ($n + 1), $digits, '0', STR_PAD_LEFT);
    }

    /** เลขที่ใบเสร็จ INV-2026-0001 */
    public static function nextInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->year . '-';
        $max = DB::table('invoices')->where('invoice_number', 'like', $prefix . '%')->max('invoice_number');
        $n = $max ? (int) substr($max, strlen($prefix)) : 0;

        return $prefix . str_pad((string) ($n + 1), 4, '0', STR_PAD_LEFT);
    }

    /** user_id ของผู้ที่ login อยู่ (หาจากอีเมล เพื่อไม่ผูกกับ primary key ของ User model) */
    public static function userId($authUser): ?string
    {
        if (! $authUser) {
            return null;
        }

        return DB::table('users')->where('email', $authUser->email)->value('user_id');
    }

    /** วันที่ไทยแบบยาว เช่น วันพุธที่ 7 ตุลาคม 2569 */
    public static function thaiDate(?Carbon $d = null): string
    {
        $d = $d ?: now('Asia/Bangkok');
        $months = [1=>'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $days = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];

        return 'วัน' . $days[$d->dayOfWeek] . 'ที่ ' . $d->day . ' ' . $months[$d->month] . ' ' . ($d->year + 543);
    }

    /** วันที่ไทยแบบสั้น dd/mm/พ.ศ. */
    public static function thaiShort($value): string
    {
        if (! $value) {
            return '-';
        }
        $d = Carbon::parse($value);

        return $d->format('d/m/') . ($d->year + 543);
    }

    /**
     * สถานะนัดหมายที่แสดงใน UI
     * DB เก็บได้แค่ scheduled/completed/cancelled จึงคำนวณ waiting/serving จากข้อมูลจริง
     *   completed -> done | cancelled -> cancelled
     *   scheduled + มีบันทึกการรักษาแล้ว -> serving (รอชำระเงิน)
     *   scheduled + วันนี้ -> waiting | scheduled + วันอื่น -> confirmed
     */
    public static function appointmentStatus(string $raw, bool $hasTreatment, string $date, string $todayYmd): string
    {
        return match (true) {
            $raw === 'completed' => 'done',
            $raw === 'cancelled' => 'cancelled',
            $hasTreatment => 'serving',
            substr($date, 0, 10) === $todayYmd => 'waiting',
            default => 'confirmed',
        };
    }

    public static function fullName(?string $first, ?string $last): string
    {
        return trim(($first ?? '') . ' ' . ($last ?? ''));
    }
}