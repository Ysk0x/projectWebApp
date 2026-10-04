@extends('vetcare.layouts.staff')

@section('title', 'ชำระเงินและใบเสร็จ | VetCare')
@section('page-name', 'Billing')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/vetcare/billing.css') }}?v=5">
@endpush


@php

    // =========================================
    // วันที่
    // =========================================

    $today = now('Asia/Bangkok');

    $thaiMonths = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม',
    ];

    $thaiDays = [
        'อาทิตย์',
        'จันทร์',
        'อังคาร',
        'พุธ',
        'พฤหัสบดี',
        'ศุกร์',
        'เสาร์',
    ];

    $thaiDate =
        'วัน' .
        $thaiDays[$today->dayOfWeek] .
        'ที่ ' .
        $today->day .
        ' ' .
        $thaiMonths[$today->month] .
        ' ' .
        ($today->year + 543);


    // =========================================
    // รับข้อมูลจากหน้า Treatments
    // =========================================

    $petName = request('pet', 'แจ็ค');

    $ownerName = request(
        'owner',
        'ประสิทธิ์ สุขดี'
    );


    // =========================================
    // วิธีชำระเงิน
    // =========================================

    $payment = request('method') === 'transfer'
        ? 'transfer'
        : 'cash';

    $paymentName = $payment === 'transfer'
        ? 'โอนเงิน'
        : 'เงินสด';


    // =========================================
    // สถานะ UI
    // =========================================

    $isPaid = request('paid') === '1';

    $printMode = request('print') === '1';


    // =========================================
    // MOCK PATIENT DATA
    // =========================================

    $patient = [
        'phone' => '062-111-2233',
        'type' => 'สุนัข',
        'breed' => 'บีเกิล',
        'date' =>
            $today->format('d/m/') .
            ($today->year + 543),

        'staff' => 'วิภา รักดี',

        'diagnosis' =>
            'โรคผิวหนังอักเสบจากเชื้อแบคทีเรีย',
    ];


    // =========================================
    // MOCK MEDICINES
    // =========================================

    $treatmentPrice = 500;

    $medicines = [

        [
            'name' => 'Amoxicillin 250mg',
            'quantity' => 20,
            'unit' => 'เม็ด',
            'price' => 8,
        ],

        [
            'name' => 'Ivermectin 1%',
            'quantity' => 2,
            'unit' => 'ml',
            'price' => 35,
        ],

    ];


    // =========================================
    // ราคา
    // =========================================

    $medicineTotal = 0;

    foreach ($medicines as $medicine) {

        $medicineTotal +=
            $medicine['quantity']
            *
            $medicine['price'];

    }

    $discount = 0;

    $grandTotal =
        $treatmentPrice
        +
        $medicineTotal
        -
        $discount;


    // =========================================
    // Query Parameters
    // =========================================

    $linkParams = [

        'pet' => $petName,

        'owner' => $ownerName,

    ];

@endphp


@section('content')

<div class="billing-page">


    {{-- =========================================
         NORMAL HEADER
    ========================================== --}}

    @if (!$printMode)

        <header class="bill-topbar">

            <h1>
                ชำระเงินและออกใบเสร็จ
            </h1>

            <div class="bill-topbar-right">

                <span>
                    {{ $thaiDate }}
                </span>

                <span class="bill-bell">

                    

                    <b>3</b>

                </span>

            </div>

        </header>

    @else

        <div class="bill-print-help">

            <a
                href="{{ route(
                    'vetcare.staff.billing',
                    [
                        'pet' => $petName,
                        'owner' => $ownerName,
                        'method' => $payment,
                        'paid' => $isPaid ? '1' : '0'
                    ]
                ) }}"
            >

                ← กลับไปหน้าชำระเงิน

            </a>

            <p>
                กด Command + P บน Mac
                หรือ Ctrl + P บน Windows
            </p>

        </div>

    @endif



    <div class="{{ $printMode ? 'bill-print-content' : 'bill-content' }}">


        {{-- =========================================
             SUCCESS
        ========================================== --}}

        @if ($isPaid && !$printMode)

            <div class="bill-success-message">

                <div class="bill-success-icon">
                    ✓
                </div>

                <div>

                    <strong>
                        บันทึกการชำระเงินเรียบร้อยแล้ว
                    </strong>

                    <p>

                        ชำระด้วย {{ $paymentName }}

                        จำนวน

                        ฿{{ number_format($grandTotal) }}

                    </p>

                </div>

            </div>

        @endif



        <div class="{{ $printMode ? '' : 'row g-3' }}">


            {{-- =========================================
                 LEFT SIDE
            ========================================== --}}

            @if (!$printMode)

                <div class="col-lg-6">


                    {{-- PATIENT --}}

                    <section class="bill-card">

                        <h2 class="bill-card-title">
                             ข้อมูลผู้ป่วย
                        </h2>


                        <div class="bill-info-grid">

                            <div>

                                <span>เจ้าของ</span>

                                <strong>
                                    {{ $ownerName }}
                                </strong>

                            </div>


                            <div>

                                <span>เบอร์โทร</span>

                                <strong>
                                    {{ $patient['phone'] }}
                                </strong>

                            </div>


                            <div>

                                <span>สัตว์เลี้ยง</span>

                                <strong>
                                    {{ $petName }}
                                </strong>

                            </div>


                            <div>

                                <span>ชนิด / พันธุ์</span>

                                <strong>

                                    {{ $patient['type'] }}

                                    /

                                    {{ $patient['breed'] }}

                                </strong>

                            </div>


                            <div>

                                <span>วันที่รักษา</span>

                                <strong>
                                    {{ $patient['date'] }}
                                </strong>

                            </div>


                            <div>

                                <span>พนักงาน</span>

                                <strong>
                                    {{ $patient['staff'] }}
                                </strong>

                            </div>

                        </div>


                        <div class="bill-diagnosis">

                            <strong>
                                การวินิจฉัย
                            </strong>

                            <p>
                                {{ $patient['diagnosis'] }}
                            </p>

                        </div>

                    </section>



                    {{-- =========================================
                         EXPENSES
                    ========================================== --}}

                    <section class="bill-card">

                        <h2 class="bill-card-title">
                            รายการค่าใช้จ่าย
                        </h2>


                        <div class="table-responsive">

                            <table class="table bill-expense-table">

                                <thead>

                                    <tr>

                                        <th>รายการ</th>

                                        <th>จำนวน</th>

                                        <th>ราคา</th>

                                        <th>รวม</th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <tr>

                                        <td>
                                            ค่ารักษาโรคผิวหนัง
                                        </td>

                                        <td>
                                            1
                                        </td>

                                        <td>
                                            ฿{{ number_format(
                                                $treatmentPrice
                                            ) }}
                                        </td>

                                        <td>
                                            ฿{{ number_format(
                                                $treatmentPrice
                                            ) }}
                                        </td>

                                    </tr>


                                    @foreach ($medicines as $medicine)

                                        <tr>

                                            <td>
                                                {{ $medicine['name'] }}
                                            </td>

                                            <td>

                                                {{ $medicine['quantity'] }}

                                                {{ $medicine['unit'] }}

                                            </td>

                                            <td>

                                                ฿{{ number_format(
                                                    $medicine['price']
                                                ) }}

                                            </td>

                                            <td>

                                                ฿{{ number_format(
                                                    $medicine['quantity']
                                                    *
                                                    $medicine['price']
                                                ) }}

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>



                        {{-- TOTAL --}}

                        <div class="bill-totals">

                            <div>

                                <span>
                                    ค่ารักษา
                                </span>

                                <strong>

                                    ฿{{ number_format(
                                        $treatmentPrice
                                    ) }}

                                </strong>

                            </div>


                            <div>

                                <span>
                                    ค่ายา
                                </span>

                                <strong>

                                    ฿{{ number_format(
                                        $medicineTotal
                                    ) }}

                                </strong>

                            </div>


                            <div>

                                <span>
                                    ส่วนลด
                                </span>

                                <strong>
                                    ฿{{ number_format($discount) }}
                                </strong>

                            </div>


                            <div class="bill-grand-total">

                                <span>
                                    ยอดรวมทั้งหมด
                                </span>

                                <strong>

                                    ฿{{ number_format(
                                        $grandTotal
                                    ) }}

                                </strong>

                            </div>

                        </div>

                    </section>



                    {{-- =========================================
                         PAYMENT
                    ========================================== --}}

                    <section
                        class="bill-card"
                        id="payment-method"
                    >

                        <h2 class="bill-card-title">
                            วิธีชำระเงิน
                        </h2>


                        {{-- PAYMENT BUTTONS --}}

                        <div class="bill-payment-options">


                            <a
                                href="{{ route(
                                    'vetcare.staff.billing',
                                    [
                                        'pet' => $petName,
                                        'owner' => $ownerName,
                                        'method' => 'cash'
                                    ]
                                ) }}#payment-method"
                                class="bill-payment-option
                                {{ $payment === 'cash'
                                    ? 'active'
                                    : ''
                                }}"
                            >

                                เงินสด

                            </a>


                            <a
                                href="{{ route(
                                    'vetcare.staff.billing',
                                    [
                                        'pet' => $petName,
                                        'owner' => $ownerName,
                                        'method' => 'transfer'
                                    ]
                                ) }}#payment-method"
                                class="bill-payment-option
                                {{ $payment === 'transfer'
                                    ? 'active'
                                    : ''
                                }}"
                            >

                                 โอนเงิน

                            </a>

                        </div>



                        {{-- CASH --}}

                        @if ($payment === 'cash')

                            <div class="bill-payment-info">

                                <strong>
                                    ชำระด้วยเงินสด
                                </strong>

                                <p>

                                    ยอดที่ต้องชำระ

                                    ฿{{ number_format(
                                        $grandTotal
                                    ) }}

                                </p>

                            </div>


                        {{-- TRANSFER --}}

                        @else

                            <div class="bill-transfer-info">

                                <p>
                                    สแกน QR Code เพื่อชำระเงิน
                                </p>


                                <div class="bill-qr-area">


                                    {{-- ใส่ QR ภายหลัง --}}

                                    <img
                                        src=""
                                        alt="QR Code"
                                        class="bill-qr-image"
                                    >


                                    <p class="bill-qr-placeholder">

                                        พื้นที่สำหรับใส่ QR Code

                                    </p>

                                </div>


                                <div class="bill-bank-details">

                                    <p>

                                        <strong>
                                            ธนาคาร
                                        </strong>

                                        : [ชื่อธนาคาร]

                                    </p>


                                    <p>

                                        <strong>
                                            ชื่อบัญชี
                                        </strong>

                                        : [ชื่อคลินิก]

                                    </p>


                                    <p>

                                        <strong>
                                            เลขที่บัญชี
                                        </strong>

                                        : [เลขบัญชี]

                                    </p>

                                </div>

                            </div>

                        @endif



                        {{-- =========================================
                             BUTTONS
                        ========================================== --}}

                        <div class="bill-action-buttons">


                            {{-- PRINT --}}

                            <a
                                href="{{ route(
                                    'vetcare.staff.billing',
                                    [
                                        'pet' => $petName,
                                        'owner' => $ownerName,
                                        'method' => $payment,
                                        'paid' => $isPaid
                                            ? '1'
                                            : '0',
                                        'print' => '1'
                                    ]
                                ) }}"
                                class="bill-btn-print"
                                target="_blank"
                                rel="noopener"
                            >

                                เปิดใบเสร็จสำหรับพิมพ์

                            </a>



                            {{-- SAVE PAYMENT --}}

                            @if (!$isPaid)

                                <form
                                    method="GET"
                                    action="{{ route(
                                        'vetcare.staff.billing'
                                    ) }}"
                                    class="bill-payment-form"
                                >

                                    <input
                                        type="hidden"
                                        name="pet"
                                        value="{{ $petName }}"
                                    >

                                    <input
                                        type="hidden"
                                        name="owner"
                                        value="{{ $ownerName }}"
                                    >

                                    <input
                                        type="hidden"
                                        name="method"
                                        value="{{ $payment }}"
                                    >

                                    <input
                                        type="hidden"
                                        name="paid"
                                        value="1"
                                    >


                                    <button
                                        type="submit"
                                        class="bill-btn-save"
                                    >

                                        ✓ บันทึกการชำระเงิน

                                    </button>

                                </form>


                            @else

                                <button
                                    type="button"
                                    class="bill-btn-paid"
                                    disabled
                                >

                                    ✓ บันทึกการชำระแล้ว

                                </button>

                            @endif

                        </div>


                        @if (!$isPaid)

                           

                        @endif

                    </section>

                </div>

            @endif



            {{-- =========================================
                 RECEIPT
                 ไม่มี @include แล้ว
            ========================================== --}}

            <div class="{{ $printMode
                ? 'bill-print-column'
                : 'col-lg-6'
            }}">


                <section
                    class="bill-card bill-receipt"
                    id="receipt-preview"
                >


                    {{-- RECEIPT HEADER --}}

                    <div class="bill-receipt-header">

                        
                        <h2>
                            VETCARE
                        </h2>

                        <p>
                            ระบบจัดการคลินิกสัตว์
                        </p>

                        <small>
                            ที่อยู่และเบอร์โทรของคลินิก
                        </small>

                    </div>



                    {{-- TITLE --}}

                    <div class="bill-receipt-title">

                        <h3>
                            ใบเสร็จรับเงิน
                        </h3>


                        @if ($isPaid)

                            <span class="bill-paid-badge">

                                ✓ ชำระแล้ว

                            </span>

                        @else

                            <span class="bill-unpaid-badge">

                                รอชำระ

                            </span>

                        @endif

                    </div>



                    {{-- RECEIPT INFO --}}

                    <div class="bill-receipt-info">


                        <div>

                            <span>
                                เลขที่
                            </span>

                            <strong>
                                INV-DEMO-001
                            </strong>

                        </div>


                        <div>

                            <span>
                                วันที่
                            </span>

                            <strong>
                                {{ $patient['date'] }}
                            </strong>

                        </div>

                    </div>



                    {{-- PATIENT --}}

                    <div class="bill-receipt-patient">


                        <div>

                            <span>
                                เจ้าของ
                            </span>

                            <strong>
                                {{ $ownerName }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                สัตว์เลี้ยง
                            </span>

                            <strong>
                                {{ $petName }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                พนักงาน
                            </span>

                            <strong>
                                {{ $patient['staff'] }}
                            </strong>

                        </div>

                    </div>



                    {{-- RECEIPT TABLE --}}

                    <table class="bill-receipt-table">

                        <thead>

                            <tr>

                                <th>รายการ</th>

                                <th>จำนวน</th>

                                <th>รวม</th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr>

                                <td>
                                    ค่ารักษาโรคผิวหนัง
                                </td>

                                <td>
                                    1 ครั้ง
                                </td>

                                <td>
                                    ฿{{ number_format(
                                        $treatmentPrice
                                    ) }}
                                </td>

                            </tr>


                            @foreach ($medicines as $medicine)

                                <tr>

                                    <td>

                                        {{ $medicine['name'] }}

                                    </td>

                                    <td>

                                        {{ $medicine['quantity'] }}

                                        {{ $medicine['unit'] }}

                                    </td>

                                    <td>

                                        ฿{{ number_format(
                                            $medicine['quantity']
                                            *
                                            $medicine['price']
                                        ) }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>



                    {{-- RECEIPT TOTAL --}}

                    <div class="bill-receipt-summary">


                        <div>

                            <span>
                                ค่ารักษา
                            </span>

                            <strong>

                                ฿{{ number_format(
                                    $treatmentPrice
                                ) }}

                            </strong>

                        </div>


                        <div>

                            <span>
                                ค่ายา
                            </span>

                            <strong>

                                ฿{{ number_format(
                                    $medicineTotal
                                ) }}

                            </strong>

                        </div>


                        <div class="bill-receipt-grand">

                            <span>
                                ยอดรวม
                            </span>

                            <strong>

                                ฿{{ number_format(
                                    $grandTotal
                                ) }}

                            </strong>

                        </div>


                        <div>

                            <span>
                                วิธีชำระเงิน
                            </span>

                            <strong>
                                {{ $paymentName }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                สถานะ
                            </span>


                            @if ($isPaid)

                                <strong class="bill-status-paid">

                                    ชำระเงินแล้ว

                                </strong>

                            @else

                                <strong class="bill-status-unpaid">

                                    รอชำระเงิน

                                </strong>

                            @endif

                        </div>

                    </div>



                    {{-- FOOTER --}}

                    <div class="bill-receipt-footer">

                        <p>
                            ขอบคุณที่ใช้บริการ VETCARE
                        </p>

                        <p>
                            กรุณาเก็บใบเสร็จไว้เป็นหลักฐาน
                        </p>


                        @if ($isPaid)

                            <strong class="receipt-paid-text">

                                ✓ บันทึกการชำระเงินแล้ว

                            </strong>

                        @else

                           

                        @endif

                    </div>


                </section>


            </div>


        </div>


    </div>


</div>

@endsection