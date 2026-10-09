@extends('vetcare.layouts.manager')

@section('title', 'ภาพรวมคลินิก | VetCare')
@section('page-name', 'Dashboard')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/vetcare/manager-dashboard.css') }}?v=2"
    >
@endpush

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

                    <span>รายได้วันนี้</span>
                    <strong>฿{{ number_format($todayRevenue) }}</strong>

                </div>

            </div>



            {{-- รายได้เดือนนี้ --}}

            <div class="manager-summary-card">

                <div class="manager-summary-icon icon-revenue">
                    📈
                </div>

                <div class="manager-summary-info">

                    <span>รายได้เดือนนี้</span>
                    <strong>฿{{ number_format($monthlyRevenueTotal) }}</strong>

                </div>

            </div>



            {{-- สัตว์รับบริการ --}}

            <div class="manager-summary-card">

                <div class="manager-summary-icon icon-pet">
                    🐾
                </div>

                <div class="manager-summary-info">
                    <span>สัตว์รับบริการ</span>
                    <strong>{{ $petsServedToday }}</strong>
                    <small>วันนี้</small>
                </div>

            </div>



            {{-- รอชำระ --}}

            <div class="manager-summary-card">

                <div class="manager-summary-icon icon-payment">
                    💳
                </div>

                <div class="manager-summary-info">
                    <span>รอชำระเงิน</span>
                    <strong>{{ $pendingPaymentsCount }}</strong>
                    <small>รายการ</small>
                </div>

            </div>



            {{-- แจ้งเตือนสต็อก --}}

            <div class="manager-summary-card">

                <div class="manager-summary-icon icon-alert">
                    ⚠️
                </div>

                <div class="manager-summary-info">
                    <span>แจ้งเตือนสต็อก</span>
                    <strong>{{ $stockAlertsCount }}</strong>
                    <small>รายการ</small>
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

                                    {{$medicine['status'] === 'low' ? 'ใกล้หมด' : 'หมดสต็อก'}}

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

                                                {{ $transaction['invoice_number'] }}

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


                                            @elseif ($transaction['status'] === 'unpaid')

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