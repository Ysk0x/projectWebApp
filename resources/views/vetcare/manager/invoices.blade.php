@extends('vetcare.layouts.manager')

@section('title', 'ตรวจสอบใบเสร็จ | VetCare')
@section('page-name', 'Invoices')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/vetcare/manager-invoices.css') }}?v=1"
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
       MOCK INVOICES
    ========================================= */

    $invoices = [

        [
            'id' => 'INV-2026-089',
            'date' => '03/10/2569',
            'staff' => 'วิภา รักดี',
            'owner' => 'วิชัย สมใจ',
            'pet' => 'มะม่วง',
            'amount' => 1850,
            'payment' => 'เงินสด',
            'status' => 'paid',
            'audit' => 'done',
        ],

        [
            'id' => 'INV-2026-088',
            'date' => '03/10/2569',
            'staff' => 'วิภา รักดี',
            'owner' => 'พิมพ์ใจ ดีงาม',
            'pet' => 'มุก',
            'amount' => 3200,
            'payment' => 'โอนเงิน',
            'status' => 'paid',
            'audit' => 'waiting',
        ],

        [
            'id' => 'INV-2026-087',
            'date' => '03/10/2569',
            'staff' => 'ประยุทธ์ สวยงาม',
            'owner' => 'ประสิทธิ์ สุขดี',
            'pet' => 'แจ็ค',
            'amount' => 920,
            'payment' => 'เงินสด',
            'status' => 'pending',
            'audit' => 'waiting',
        ],

        [
            'id' => 'INV-2026-086',
            'date' => '02/10/2569',
            'staff' => 'วิภา รักดี',
            'owner' => 'บุญมี รักสวย',
            'pet' => 'ดาว',
            'amount' => 2450,
            'payment' => 'โอนเงิน',
            'status' => 'paid',
            'audit' => 'done',
        ],

        [
            'id' => 'INV-2026-085',
            'date' => '02/10/2569',
            'staff' => 'วิภา รักดี',
            'owner' => 'สมศักดิ์ ใจดี',
            'pet' => 'บาสโก้',
            'amount' => 5600,
            'payment' => 'เงินสด',
            'status' => 'void',
            'audit' => 'done',
        ],

        [
            'id' => 'INV-2026-084',
            'date' => '01/10/2569',
            'staff' => 'ประยุทธ์ สวยงาม',
            'owner' => 'สุดา มีแก้ว',
            'pet' => 'ลัคกี้',
            'amount' => 650,
            'payment' => 'เงินสด',
            'status' => 'paid',
            'audit' => 'done',
        ],

        [
            'id' => 'INV-2026-083',
            'date' => '01/10/2569',
            'staff' => 'วิภา รักดี',
            'owner' => 'นิยม สวยดี',
            'pet' => 'โมจิ',
            'amount' => 5120,
            'payment' => 'โอนเงิน',
            'status' => 'paid',
            'audit' => 'waiting',
        ],

    ];


    /* =========================================
       STATUS LABELS
    ========================================= */

    $statusLabels = [
        'paid' => 'ชำระแล้ว',
        'pending' => 'รอดำเนินการ',
        'void' => 'ยกเลิกบิล',
    ];

    $auditLabels = [
        'done' => 'ตรวจสอบแล้ว',
        'waiting' => 'รอตรวจสอบ',
    ];


    /* =========================================
       SUMMARY
    ========================================= */

    $totalInvoices = count($invoices);

    $paidCount = count(
        array_filter(
            $invoices,
            fn ($invoice) =>
                $invoice['status'] === 'paid'
        )
    );

    $auditWaitingCount = count(
        array_filter(
            $invoices,
            fn ($invoice) =>
                $invoice['audit'] === 'waiting'
        )
    );

    $voidCount = count(
        array_filter(
            $invoices,
            fn ($invoice) =>
                $invoice['status'] === 'void'
        )
    );

    $totalRevenue = 0;

    foreach ($invoices as $invoice) {

        if ($invoice['status'] === 'paid') {
            $totalRevenue += $invoice['amount'];
        }

    }


    /* =========================================
       SEARCH
    ========================================= */

    $search = trim(
        (string) request('search', '')
    );

    $filteredInvoices = array_values(
        array_filter(
            $invoices,
            function ($invoice) use ($search) {

                if ($search === '') {
                    return true;
                }

                $text =
                    $invoice['id'] .
                    ' ' .
                    $invoice['staff'] .
                    ' ' .
                    $invoice['owner'] .
                    ' ' .
                    $invoice['pet'];

                return mb_stripos(
                    $text,
                    $search
                ) !== false;

            }
        )
    );


    /* =========================================
       SELECTED INVOICE
    ========================================= */

    $panel = request('panel');

    $selectedInvoice = null;

    if (
        in_array(
            $panel,
            ['detail', 'void'],
            true
        )
    ) {

        foreach ($invoices as $invoice) {

            if (
                $invoice['id']
                === request('id')
            ) {

                $selectedInvoice = $invoice;

                break;
            }

        }

    }

@endphp


@section('content')

<div class="manager-invoices-page">


    {{-- =========================================
         HEADER
    ========================================== --}}

    <header class="manager-invoices-topbar">

        <div>

            <h1>
                รายงานการเงิน / ตรวจสอบใบเสร็จ
            </h1>

            <p>
                ตรวจสอบรายการชำระเงินและใบเสร็จของคลินิก
            </p>

        </div>


        <div class="manager-invoices-topbar-right">

            <span>
                {{ $thaiDate }}
            </span>

            <div class="manager-invoices-bell">

                🔔

                <b>
                    3
                </b>

            </div>

        </div>

    </header>



    <div class="manager-invoices-content">


        {{-- =========================================
             SUMMARY
        ========================================== --}}

        <div class="invoice-summary-grid">


            {{-- ALL --}}

            <div class="invoice-summary-card">

                <span>
                    ใบเสร็จทั้งหมด
                </span>

                <strong>
                    {{ $totalInvoices }}
                </strong>

            </div>



            {{-- REVENUE --}}

            <div class="invoice-summary-card">

                <span>
                    รายได้รวม
                </span>

                <strong class="summary-revenue">

                    ฿{{ number_format(
                        $totalRevenue
                    ) }}

                </strong>

            </div>



            {{-- PAID --}}

            <div class="invoice-summary-card">

                <span>
                    ชำระแล้ว
                </span>

                <strong class="summary-paid">

                    {{ $paidCount }}

                </strong>

            </div>



            {{-- WAIT AUDIT --}}

            <div class="invoice-summary-card">

                <span>
                    รอตรวจสอบ
                </span>

                <strong class="summary-audit">

                    {{ $auditWaitingCount }}

                </strong>

            </div>



            {{-- VOID --}}

            <div class="invoice-summary-card">

                <span>
                    ยกเลิกบิล
                </span>

                <strong class="summary-void">

                    {{ $voidCount }}

                </strong>

            </div>


        </div>



        {{-- =========================================
             MAIN CARD
        ========================================== --}}

        <section class="manager-invoice-card">


            <div class="manager-invoice-card-header">


                <div>

                    <h2>
                        รายการใบเสร็จทั้งหมด
                    </h2>

                    <p>
                        ตรวจสอบรายละเอียดและสถานะของใบเสร็จ
                    </p>

                </div>



                {{-- =================================
                     SEARCH
                ================================== --}}

                <form
                    method="GET"
                    action="{{ route(
                        'vetcare.manager.invoices'
                    ) }}"
                    class="invoice-search"
                >

                    <span>
                        🔍
                    </span>

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="ค้นหาเลขที่ / เจ้าของ / พนักงาน..."
                    >

                </form>


            </div>



            {{-- =========================================
                 TABLE
            ========================================== --}}

            <div class="table-responsive">


                <table class="table manager-invoice-table">


                    <thead>

                        <tr>

                            <th>
                                เลขที่ใบเสร็จ
                            </th>

                            <th>
                                วันที่
                            </th>

                            <th>
                                พนักงานผู้รับเงิน
                            </th>

                            <th>
                                เจ้าของสัตว์
                            </th>

                            <th>
                                จำนวนเงิน
                            </th>

                            <th>
                                สถานะ
                            </th>

                            <th>
                                AUDIT
                            </th>

                            <th>
                                การจัดการ
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        @forelse (
                            $filteredInvoices
                            as $invoice
                        )


                            <tr>


                                {{-- INVOICE --}}

                                <td>

                                    <strong class="invoice-number">

                                        {{ $invoice['id'] }}

                                    </strong>

                                </td>



                                {{-- DATE --}}

                                <td>

                                    {{ $invoice['date'] }}

                                </td>



                                {{-- STAFF --}}

                                <td>

                                    {{ $invoice['staff'] }}

                                </td>



                                {{-- OWNER --}}

                                <td>

                                    <strong class="invoice-owner">

                                        {{ $invoice['owner'] }}

                                    </strong>

                                    <small class="invoice-pet">

                                        {{ $invoice['pet'] }}

                                    </small>

                                </td>



                                {{-- AMOUNT --}}

                                <td>

                                    <strong class="invoice-amount">

                                        ฿{{ number_format(
                                            $invoice['amount']
                                        ) }}

                                    </strong>

                                </td>



                                {{-- STATUS --}}

                                <td>


                                    <span
                                        class="invoice-status
                                        invoice-status-{{ $invoice['status'] }}"
                                    >

                                        {{ $statusLabels[
                                            $invoice['status']
                                        ] }}

                                    </span>


                                </td>



                                {{-- AUDIT --}}

                                <td>


                                    <span
                                        class="invoice-audit
                                        audit-{{ $invoice['audit'] }}"
                                    >

                                        {{ $auditLabels[
                                            $invoice['audit']
                                        ] }}

                                    </span>


                                </td>



                                {{-- ACTION --}}

                                <td>


                                    <a
                                        href="{{ route(
                                            'vetcare.manager.invoices',
                                            [
                                                'panel' => 'detail',
                                                'id' => $invoice['id'],
                                                'search' => $search
                                            ]
                                        ) }}#invoice-modal"
                                        class="invoice-detail-btn"
                                    >

                                        ดูรายละเอียด

                                    </a>


                                </td>


                            </tr>


                        @empty


                            <tr>

                                <td
                                    colspan="8"
                                    class="invoice-empty"
                                >

                                    ไม่พบรายการใบเสร็จ

                                </td>

                            </tr>


                        @endforelse


                    </tbody>


                </table>


            </div>



            <div class="invoice-table-footer">

                แสดง

                {{ count($filteredInvoices) }}

                จาก

                {{ $totalInvoices }}

                รายการ

            </div>


        </section>


    </div>



    {{-- =================================================
         INVOICE DETAIL MODAL
    ================================================== --}}

    @if (
        $panel === 'detail'
        &&
        $selectedInvoice
    )


        <div
            class="invoice-modal-overlay"
            id="invoice-modal"
        >


            <div class="invoice-modal">


                {{-- HEADER --}}

                <div class="invoice-modal-header">


                    <h2>
                        รายละเอียดใบเสร็จ
                    </h2>


                    <span
                        class="invoice-status
                        invoice-status-{{ $selectedInvoice['status'] }}"
                    >

                        {{ $statusLabels[
                            $selectedInvoice['status']
                        ] }}

                    </span>


                </div>



                {{-- BODY --}}

                <div class="invoice-modal-body">


                    <div class="invoice-detail-row">

                        <span>
                            เลขที่ใบเสร็จ
                        </span>

                        <strong class="modal-invoice-number">

                            {{ $selectedInvoice['id'] }}

                        </strong>

                    </div>



                    <div class="invoice-detail-row">

                        <span>
                            วันที่
                        </span>

                        <strong>

                            {{ $selectedInvoice['date'] }}

                        </strong>

                    </div>



                    <div class="invoice-detail-row">

                        <span>
                            พนักงานผู้รับเงิน
                        </span>

                        <strong>

                            {{ $selectedInvoice['staff'] }}

                        </strong>

                    </div>



                    <div class="invoice-detail-row">

                        <span>
                            เจ้าของสัตว์
                        </span>

                        <strong>

                            {{ $selectedInvoice['owner'] }}

                        </strong>

                    </div>



                    <div class="invoice-detail-row">

                        <span>
                            สัตว์เลี้ยง
                        </span>

                        <strong>

                            {{ $selectedInvoice['pet'] }}

                        </strong>

                    </div>



                    <div class="invoice-detail-row">

                        <span>
                            วิธีชำระเงิน
                        </span>

                        <strong>

                            {{ $selectedInvoice['payment'] }}

                        </strong>

                    </div>



                    <div class="invoice-detail-row invoice-amount-row">

                        <span>
                            จำนวนเงิน
                        </span>

                        <strong>

                            ฿{{ number_format(
                                $selectedInvoice['amount']
                            ) }}

                        </strong>

                    </div>



                    <div class="invoice-detail-row">

                        <span>
                            สถานะ Audit
                        </span>

                        <span
                            class="invoice-audit
                            audit-{{ $selectedInvoice['audit'] }}"
                        >

                            {{ $auditLabels[
                                $selectedInvoice['audit']
                            ] }}

                        </span>

                    </div>


                </div>



                {{-- FOOTER --}}

                <div class="invoice-modal-footer">


                    <a
                        href="{{ route(
                            'vetcare.manager.invoices'
                        ) }}"
                        class="invoice-modal-close"
                    >

                        ปิด

                    </a>



                    @if (
                        $selectedInvoice['status']
                        !== 'void'
                    )


                        <a
                            href="{{ route(
                                'vetcare.manager.invoices',
                                [
                                    'panel' => 'void',
                                    'id' => $selectedInvoice['id']
                                ]
                            ) }}#invoice-modal"
                            class="invoice-void-btn"
                        >

                            ยกเลิกบิล (Void)

                        </a>


                    @endif


                </div>


            </div>


        </div>


    @endif



    @if (
        $panel === 'void'
        &&
        $selectedInvoice
    )


        <div
            class="invoice-modal-overlay"
            id="invoice-modal"
        >


            <div class="invoice-modal invoice-small-modal">


                <div class="invoice-modal-header">

                    <h2>
                        ยืนยันการยกเลิกบิล
                    </h2>

                </div>



                <div class="invoice-void-body">


                    <div class="invoice-void-icon">
                        ⚠️
                    </div>


                    <p>

                        ต้องการยกเลิกใบเสร็จ

                        <strong>

                            {{ $selectedInvoice['id'] }}

                        </strong>

                        ใช่หรือไม่?

                    </p>


                    <span>

                        จำนวนเงิน

                        <strong>

                            ฿{{ number_format(
                                $selectedInvoice['amount']
                            ) }}

                        </strong>

                    </span>


                    <small>

                        ขณะนี้เป็น UI เท่านั้น
                        ยังไม่มีการแก้ไขข้อมูลจริง

                    </small>


                </div>



                <div class="invoice-modal-footer">


                    <a
                        href="{{ route(
                            'vetcare.manager.invoices',
                            [
                                'panel' => 'detail',
                                'id' => $selectedInvoice['id']
                            ]
                        ) }}#invoice-modal"
                        class="invoice-modal-close"
                    >

                        กลับ

                    </a>


                    <button
                        type="button"
                        class="invoice-void-confirm"
                        disabled
                    >

                        ยืนยันยกเลิกบิล

                    </button>


                </div>


            </div>


        </div>


    @endif


</div>

@endsection