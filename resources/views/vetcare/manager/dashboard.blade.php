@extends('vetcare.layouts.manager')

@section('title', 'ภาพรวมคลินิก | VetCare')
@section('page-name', 'Dashboard')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/vetcare/manager-dashboard.css') }}?v=2"
    >
@endpush


@php

    /* =========================================
       DATE
    ========================================= */

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


    /* =========================================
       REVENUE PERIOD
    ========================================= */

    $revenuePeriod = request('period') === 'daily'
        ? 'daily'
        : 'monthly';


    /* =========================================
       DAILY REVENUE
    ========================================= */

    $dailyRevenues = [

        [
            'label' => 'จ.',
            'amount' => '12.5K',
            'height' => 42,
        ],

        [
            'label' => 'อ.',
            'amount' => '18.2K',
            'height' => 62,
        ],

        [
            'label' => 'พ.',
            'amount' => '15.8K',
            'height' => 53,
        ],

        [
            'label' => 'พฤ.',
            'amount' => '21.4K',
            'height' => 73,
        ],

        [
            'label' => 'ศ.',
            'amount' => '24.8K',
            'height' => 86,
        ],

        [
            'label' => 'ส.',
            'amount' => '19.6K',
            'height' => 67,
        ],

        [
            'label' => 'อา.',
            'amount' => '18.4K',
            'height' => 63,
        ],

    ];


    /* =========================================
       MONTHLY REVENUE
    ========================================= */

    $monthlyRevenues = [

        [
            'label' => 'พ.ค.',
            'amount' => '195K',
            'height' => 50,
        ],

        [
            'label' => 'มิ.ย.',
            'amount' => '260K',
            'height' => 67,
        ],

        [
            'label' => 'ก.ค.',
            'amount' => '310K',
            'height' => 84,
        ],

        [
            'label' => 'ส.ค.',
            'amount' => '275K',
            'height' => 72,
        ],

        [
            'label' => 'ก.ย.',
            'amount' => '284K',
            'height' => 76,
        ],

    ];


    /* =========================================
       SELECT REVENUE DATA
    ========================================= */

    $revenues = $revenuePeriod === 'daily'
        ? $dailyRevenues
        : $monthlyRevenues;


    /* =========================================
       STOCK ALERTS
    ========================================= */

    $stockAlerts = [

        [
            'name' => 'Amoxicillin 250mg',
            'stock' => '8 เม็ด',
            'status' => 'low',
            'label' => 'ใกล้หมด',
        ],

        [
            'name' => 'Prednisolone 5mg',
            'stock' => '0 เม็ด',
            'status' => 'out',
            'label' => 'หมดสต็อก',
        ],

        [
            'name' => 'Ivermectin 1%',
            'stock' => '12 ml',
            'status' => 'low',
            'label' => 'ใกล้หมด',
        ],

        [
            'name' => 'Metronidazole 200mg',
            'stock' => '0 เม็ด',
            'status' => 'out',
            'label' => 'หมดสต็อก',
        ],

        [
            'name' => 'Dexamethasone Injection',
            'stock' => '5 ขวด',
            'status' => 'low',
            'label' => 'ใกล้หมด',
        ],

    ];


    /* =========================================
       RECENT TRANSACTIONS
    ========================================= */

    $transactions = [

        [
            'invoice' => 'INV-2026-089',
            'owner' => 'วิชัย สมใจ',
            'pet' => 'มะม่วง',
            'petType' => 'สุนัข',
            'amount' => 1850,
            'method' => 'เงินสด',
            'status' => 'paid',
        ],

        [
            'invoice' => 'INV-2026-088',
            'owner' => 'พิมพ์ใจ ดีงาม',
            'pet' => 'มุก',
            'petType' => 'แมว',
            'amount' => 3200,
            'method' => 'โอนเงิน',
            'status' => 'paid',
        ],

        [
            'invoice' => 'INV-2026-087',
            'owner' => 'ประสิทธิ์ สุขดี',
            'pet' => 'แจ็ค',
            'petType' => 'สุนัข',
            'amount' => 920,
            'method' => 'เงินสด',
            'status' => 'waiting',
        ],

        [
            'invoice' => 'INV-2026-086',
            'owner' => 'บุญมี รักสวย',
            'pet' => 'ดาว',
            'petType' => 'แมว',
            'amount' => 2450,
            'method' => 'โอนเงิน',
            'status' => 'paid',
        ],

        [
            'invoice' => 'INV-2026-085',
            'owner' => 'สมศักดิ์ ใจดี',
            'pet' => 'บาสโก้',
            'petType' => 'สุนัข',
            'amount' => 5600,
            'method' => 'เงินสด',
            'status' => 'cancelled',
        ],

    ];


    /* =========================================
       RECENT ACTIVITIES
    ========================================= */

    $activities = [

        [
            'time' => '09:15',
            'text' => 'วิภา รักดี บันทึกการรักษา มะม่วง',
        ],

        [
            'time' => '10:30',
            'text' => 'ออกใบเสร็จ INV-2026-089',
        ],

        [
            'time' => '11:00',
            'text' => 'เพิ่มสัตว์เลี้ยงใหม่ มุก',
        ],

        [
            'time' => '13:45',
            'text' => 'จ่ายยา Amoxicillin จำนวน 20 เม็ด',
        ],

        [
            'time' => '14:20',
            'text' => 'ยกเลิกใบเสร็จ INV-2026-085',
        ],

    ];

@endphp


@section('content')

<div class="manager-dashboard-page">

    <header class="manager-dashboard-topbar">

        <div>

            <h1>
               ภาพรวมคลินิก
            </h1>

            <p>
                ภาพรวมการดำเนินงานของคลินิก
            </p>

        </div>


        <div class="manager-topbar-right">

            <span class="manager-date">
                {{ $thaiDate }}
            </span>

            <div class="manager-bell">

                🔔

                <b>
                    3
                </b>

            </div>

        </div>

    </header>



    <div class="manager-dashboard-content">



        <div class="manager-summary-grid">


            {{-- รายได้วันนี้ --}}

            <div class="manager-summary-card">

                <div class="manager-summary-icon icon-money">
                    💰
                </div>

                <div class="manager-summary-info">

                    <span>
                        รายได้วันนี้
                    </span>

                    <strong>
                        ฿18,450
                    </strong>

                    <small class="positive-text">
                        ↑ 12% จากเมื่อวาน
                    </small>

                </div>

            </div>



            {{-- รายได้เดือนนี้ --}}

            <div class="manager-summary-card">

                <div class="manager-summary-icon icon-revenue">
                    📈
                </div>

                <div class="manager-summary-info">

                    <span>
                        รายได้เดือนนี้
                    </span>

                    <strong>
                        ฿284,200
                    </strong>

                    <small>
                        เป้าหมาย ฿300,000
                    </small>

                </div>

            </div>



            {{-- สัตว์รับบริการ --}}

            <div class="manager-summary-card">

                <div class="manager-summary-icon icon-pet">
                    🐾
                </div>

                <div class="manager-summary-info">

                    <span>
                        สัตว์รับบริการ
                    </span>

                    <strong>
                        24
                    </strong>

                    <small>
                        วันนี้
                    </small>

                </div>

            </div>



            {{-- รอชำระ --}}

            <div class="manager-summary-card">

                <div class="manager-summary-icon icon-payment">
                    💳
                </div>

                <div class="manager-summary-info">

                    <span>
                        รอชำระเงิน
                    </span>

                    <strong>
                        7
                    </strong>

                    <small>
                        รายการ
                    </small>

                </div>

            </div>



            {{-- แจ้งเตือนสต็อก --}}

            <div class="manager-summary-card">

                <div class="manager-summary-icon icon-alert">
                    ⚠️
                </div>

                <div class="manager-summary-info">

                    <span>
                        แจ้งเตือนสต็อก
                    </span>

                    <strong>
                        5
                    </strong>

                    <small>
                        รายการ
                    </small>

                </div>

            </div>


        </div>



        {{-- =========================================
             REVENUE + STOCK
        ========================================== --}}

        <div class="row g-3 mb-3">


            {{-- =====================================
                 REVENUE
            ====================================== --}}

            <div class="col-xl-9">

                <section class="manager-card revenue-card">


                    <div class="manager-card-header">

                        <div>

                            <h2>

                                {{ $revenuePeriod === 'daily'
                                    ? 'รายรับ 7 วันล่าสุด'
                                    : 'รายรับรายเดือน'
                                }}

                            </h2>

                            <p class="manager-card-description">

                                {{ $revenuePeriod === 'daily'
                                    ? 'สรุปรายรับย้อนหลัง 7 วัน'
                                    : 'สรุปรายรับย้อนหลัง 5 เดือน'
                                }}

                            </p>

                        </div>


                        {{-- =========================
                             DAILY / MONTHLY BUTTON
                        ========================== --}}

                        <div class="revenue-view-buttons">

                            <a
                                href="{{ route(
                                    'vetcare.manager.dashboard',
                                    [
                                        'period' => 'daily'
                                    ]
                                ) }}"
                                class="{{ $revenuePeriod === 'daily'
                                    ? 'active'
                                    : ''
                                }}"
                            >

                                รายวัน

                            </a>


                            <a
                                href="{{ route(
                                    'vetcare.manager.dashboard',
                                    [
                                        'period' => 'monthly'
                                    ]
                                ) }}"
                                class="{{ $revenuePeriod === 'monthly'
                                    ? 'active'
                                    : ''
                                }}"
                            >

                                รายเดือน

                            </a>

                        </div>

                    </div>



                    {{-- =================================
                         CHART
                    ================================== --}}

                    <div class="revenue-chart">


                        @foreach ($revenues as $revenue)

                            <div class="revenue-column">


                                <div class="revenue-bar-wrapper">


                                    <span class="revenue-value">

                                        ฿{{ $revenue['amount'] }}

                                    </span>


                                    <div class="revenue-bar-track">


                                        <div
                                            class="revenue-bar"
                                            style="height: {{ $revenue['height'] }}%;"
                                        >
                                        </div>


                                    </div>


                                </div>


                                <span class="revenue-label">

                                    {{ $revenue['label'] }}

                                </span>


                            </div>

                        @endforeach


                    </div>


                </section>

            </div>



            {{-- =====================================
                 STOCK ALERT
            ====================================== --}}

            <div class="col-xl-3">

                <section class="manager-card stock-alert-card">


                    <div class="manager-card-header">

                        <div>

                            <h2>
                                ⚠️ ยาใกล้หมด / หมดสต็อก
                            </h2>

                            <p class="manager-card-description">
                                รายการที่ควรตรวจสอบ
                            </p>

                        </div>

                    </div>



                    <div class="stock-alert-list">


                        @foreach ($stockAlerts as $medicine)

                            <div class="stock-alert-item">


                                <div class="stock-alert-info">

                                    <strong>
                                        {{ $medicine['name'] }}
                                    </strong>

                                    <small>

                                        คงเหลือ
                                        {{ $medicine['stock'] }}

                                    </small>

                                </div>


                                <span
                                    class="stock-alert-status
                                           stock-{{ $medicine['status'] }}"
                                >

                                    {{ $medicine['label'] }}

                                </span>


                            </div>

                        @endforeach


                    </div>



                    <a
                        href="{{ route(
                            'vetcare.manager.medicines'
                        ) }}"
                        class="view-medicine-link"
                    >

                        ดูคลังยาทั้งหมด →

                    </a>


                </section>

            </div>


        </div>



        {{-- =========================================
             TRANSACTIONS + ACTIVITIES
        ========================================== --}}

        <div class="row g-3">


            {{-- =====================================
                 TRANSACTIONS
            ====================================== --}}

            <div class="col-xl-9">

                <section class="manager-card transaction-card">


                    <div class="manager-card-header">

                        <div>

                            <h2>
                                รายการชำระเงินล่าสุด
                            </h2>

                            <p class="manager-card-description">
                                รายการทางการเงินล่าสุดของคลินิก
                            </p>

                        </div>


                        <a
                            href="{{ route(
                                'vetcare.manager.invoices'
                            ) }}"
                            class="manager-view-all"
                        >

                            ดูทั้งหมด →

                        </a>

                    </div>



                    <div class="table-responsive">

                        <table class="table manager-transaction-table">


                            <thead>

                                <tr>

                                    <th>
                                        เลขที่ใบเสร็จ
                                    </th>

                                    <th>
                                        เจ้าของ / สัตว์
                                    </th>

                                    <th>
                                        จำนวนเงิน
                                    </th>

                                    <th>
                                        วิธีชำระ
                                    </th>

                                    <th>
                                        สถานะ
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                @foreach ($transactions as $transaction)

                                    <tr>


                                        <td>

                                            <a
                                                href="{{ route(
                                                    'vetcare.manager.invoices'
                                                ) }}"
                                                class="invoice-link"
                                            >

                                                {{ $transaction['invoice'] }}

                                            </a>

                                        </td>


                                        <td>

                                            <strong>
                                                {{ $transaction['owner'] }}
                                            </strong>

                                            <small>

                                                {{ $transaction['pet'] }}

                                                •

                                                {{ $transaction['petType'] }}

                                            </small>

                                        </td>


                                        <td>

                                            <strong class="transaction-amount">

                                                ฿{{ number_format(
                                                    $transaction['amount']
                                                ) }}

                                            </strong>

                                        </td>


                                        <td>

                                            {{ $transaction['method'] }}

                                        </td>


                                        <td>


                                            @if ($transaction['status'] === 'paid')

                                                <span
                                                    class="transaction-status
                                                           transaction-paid"
                                                >

                                                    ชำระแล้ว

                                                </span>


                                            @elseif ($transaction['status'] === 'waiting')

                                                <span
                                                    class="transaction-status
                                                           transaction-waiting"
                                                >

                                                    รอชำระ

                                                </span>


                                            @else

                                                <span
                                                    class="transaction-status
                                                           transaction-cancelled"
                                                >

                                                    ยกเลิก

                                                </span>

                                            @endif


                                        </td>


                                    </tr>

                                @endforeach


                            </tbody>


                        </table>

                    </div>


                </section>

            </div>



            {{-- =====================================
                 ACTIVITY
            ====================================== --}}

            <div class="col-xl-3">

                <section class="manager-card activity-card">


                    <div class="manager-card-header">

                        <div>

                            <h2>
                                กิจกรรมล่าสุด
                            </h2>

                            <p class="manager-card-description">
                                ความเคลื่อนไหวในระบบ
                            </p>

                        </div>

                    </div>



                    <div class="activity-list">


                        @foreach ($activities as $activity)

                            <div class="activity-item">


                                <span class="activity-time">

                                    {{ $activity['time'] }}

                                </span>


                                <div class="activity-line">

                                    <span class="activity-dot">
                                    </span>

                                </div>


                                <p>

                                    {{ $activity['text'] }}

                                </p>


                            </div>

                        @endforeach


                    </div>


                </section>

            </div>


        </div>


    </div>


</div>

@endsection