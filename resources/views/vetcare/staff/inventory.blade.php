@extends('vetcare.layouts.staff')

@section('title', 'ดูคลังยา | VetCare')
@section('page-name', 'Inventory')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/vetcare/inventory.css') }}?v=1">
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
       MOCK MEDICINE DATA
       UI ONLY
    ========================================= */

    $medicines = [

        [
            'id' => 'MED-001',
            'name' => 'Amoxicillin 250mg',
            'category' => 'ยาปฏิชีวนะ',
            'unit' => 'เม็ด',
            'stock' => 8,
            'minimum' => 10,
            'status' => 'low',
            'lot' => 'AMX-2601',
            'expiry' => '15/03/2570',
            'storage' => 'เก็บในที่แห้ง อุณหภูมิห้อง',
        ],

        [
            'id' => 'MED-002',
            'name' => 'Prednisolone 5mg',
            'category' => 'ยาสเตียรอยด์',
            'unit' => 'เม็ด',
            'stock' => 3,
            'minimum' => 5,
            'status' => 'out',
            'lot' => 'PRE-2602',
            'expiry' => '20/01/2570',
            'storage' => 'เก็บในที่แห้ง หลีกเลี่ยงแสง',
        ],

        [
            'id' => 'MED-003',
            'name' => 'Ivermectin 1%',
            'category' => 'ยาฆ่าพยาธิ',
            'unit' => 'ml',
            'stock' => 12,
            'minimum' => 15,
            'status' => 'low',
            'lot' => 'IVE-2603',
            'expiry' => '08/05/2570',
            'storage' => 'เก็บที่อุณหภูมิห้อง',
        ],

        [
            'id' => 'MED-004',
            'name' => 'Metronidazole 200mg',
            'category' => 'ยาปฏิชีวนะ',
            'unit' => 'เม็ด',
            'stock' => 0,
            'minimum' => 10,
            'status' => 'out',
            'lot' => 'MET-2601',
            'expiry' => '12/02/2570',
            'storage' => 'เก็บในที่แห้ง',
        ],

        [
            'id' => 'MED-005',
            'name' => 'Cephalexin 250mg',
            'category' => 'ยาปฏิชีวนะ',
            'unit' => 'แคปซูล',
            'stock' => 35,
            'minimum' => 10,
            'status' => 'normal',
            'lot' => 'CEP-2605',
            'expiry' => '25/08/2570',
            'storage' => 'เก็บในที่แห้ง',
        ],

        [
            'id' => 'MED-006',
            'name' => 'Meloxicam 1.5mg',
            'category' => 'ยาแก้อักเสบ',
            'unit' => 'เม็ด',
            'stock' => 24,
            'minimum' => 10,
            'status' => 'normal',
            'lot' => 'MEL-2603',
            'expiry' => '30/06/2570',
            'storage' => 'เก็บที่อุณหภูมิห้อง',
        ],

        [
            'id' => 'MED-007',
            'name' => 'Chlorhexidine',
            'category' => 'น้ำยาฆ่าเชื้อ',
            'unit' => 'ขวด',
            'stock' => 18,
            'minimum' => 5,
            'status' => 'normal',
            'lot' => 'CHL-2608',
            'expiry' => '17/09/2570',
            'storage' => 'หลีกเลี่ยงแสงแดด',
        ],

        [
            'id' => 'MED-008',
            'name' => 'Vitamin B Complex',
            'category' => 'วิตามิน',
            'unit' => 'ขวด',
            'stock' => 20,
            'minimum' => 5,
            'status' => 'normal',
            'lot' => 'VIT-2602',
            'expiry' => '11/11/2570',
            'storage' => 'เก็บในที่เย็นและแห้ง',
        ],

        [
            'id' => 'MED-009',
            'name' => 'Doxycycline 100mg',
            'category' => 'ยาปฏิชีวนะ',
            'unit' => 'เม็ด',
            'stock' => 7,
            'minimum' => 10,
            'status' => 'low',
            'lot' => 'DOX-2604',
            'expiry' => '14/07/2570',
            'storage' => 'เก็บในที่แห้ง',
        ],

        [
            'id' => 'MED-010',
            'name' => 'Cetirizine 10mg',
            'category' => 'ยาแก้แพ้',
            'unit' => 'เม็ด',
            'stock' => 30,
            'minimum' => 10,
            'status' => 'normal',
            'lot' => 'CET-2601',
            'expiry' => '22/10/2570',
            'storage' => 'เก็บที่อุณหภูมิห้อง',
        ],

    ];


    /* =========================================
       STATUS
    ========================================= */

    $statusLabels = [
        'all' => 'ทุกสถานะ',
        'normal' => 'ปกติ',
        'low' => 'ใกล้หมด',
        'out' => 'หมดสต็อก',
    ];


    /* =========================================
       SUMMARY
    ========================================= */

    $totalCount = count($medicines);

    $normalCount = count(array_filter(
        $medicines,
        fn ($medicine) =>
            $medicine['status'] === 'normal'
    ));

    $lowCount = count(array_filter(
        $medicines,
        fn ($medicine) =>
            $medicine['status'] === 'low'
    ));

    $outCount = count(array_filter(
        $medicines,
        fn ($medicine) =>
            $medicine['status'] === 'out'
    ));


    /* =========================================
       SEARCH / FILTER
    ========================================= */

    $search = trim(
        (string) request('search', '')
    );

    $status = request(
        'status',
        'all'
    );

    if (!array_key_exists(
        $status,
        $statusLabels
    )) {
        $status = 'all';
    }


    $filteredMedicines = array_values(
        array_filter(
            $medicines,
            function ($medicine) use (
                $search,
                $status
            ) {

                $text =
                    $medicine['id'] .
                    ' ' .
                    $medicine['name'] .
                    ' ' .
                    $medicine['category'];

                $matchSearch =
                    $search === '' ||
                    mb_stripos(
                        $text,
                        $search
                    ) !== false;

                $matchStatus =
                    $status === 'all' ||
                    $medicine['status']
                    === $status;

                return
                    $matchSearch &&
                    $matchStatus;
            }
        )
    );


    /* =========================================
       SELECTED MEDICINE
    ========================================= */

    $selectedMedicine = null;

    $selectedId = request('selected');

    if ($selectedId) {

        foreach ($medicines as $medicine) {

            if (
                $medicine['id']
                === $selectedId
            ) {

                $selectedMedicine =
                    $medicine;

                break;
            }

        }

    }

@endphp


@section('content')

<div class="inventory-page">


    <header class="inventory-topbar">


        <h1>
            คลังยา
        </h1>


        <div class="inventory-topbar-right">


            <span>
                {{ $thaiDate }}
            </span>


            <span class="inventory-bell">

                🔔

                <b>
                    3
                </b>

            </span>


        </div>


    </header>



    <div class="inventory-content">


        {{-- =====================================
             INFO MESSAGE
        ====================================== --}}

        <div class="inventory-info">


            <span class="inventory-info-icon">
                ℹ
            </span>


            <p>

                หน้านี้สำหรับ

                <strong>
                    ดูและค้นหาสต็อกยา
                </strong>

                เท่านั้น

                การเพิ่ม แก้ไข หรือลบรายการยา

                ต้องดำเนินการโดย

                <strong>
                    ผู้จัดการ
                </strong>

            </p>


        </div>



        {{-- =====================================
             SUMMARY
        ====================================== --}}

        <div class="row g-3 mb-3">


            <div class="col-6 col-xl-3">

                <div class="inventory-summary-card">

                    <span>
                        รายการยาทั้งหมด
                    </span>

                    <strong>
                        {{ $totalCount }}
                    </strong>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="inventory-summary-card">

                    <span>
                        ปกติ
                    </span>

                    <strong class="summary-normal">
                        {{ $normalCount }}
                    </strong>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="inventory-summary-card">

                    <span>
                        ใกล้หมด
                    </span>

                    <strong class="summary-low">
                        {{ $lowCount }}
                    </strong>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="inventory-summary-card">

                    <span>
                        หมดสต็อก
                    </span>

                    <strong class="summary-out">
                        {{ $outCount }}
                    </strong>

                </div>

            </div>


        </div>



        {{-- =====================================
             MAIN AREA
        ====================================== --}}

        <div class="row g-3">


            {{-- =================================
                 STOCK LIST
            ================================== --}}

            <div class="col-xl-8">


                <section class="inventory-card">


                    <div class="inventory-card-header">


                        <h2>
                            รายการสต็อกยา
                        </h2>


                        {{-- FILTER --}}

                        <form
                            action="{{ route(
                                'vetcare.staff.inventory'
                            ) }}"
                            method="GET"
                            class="inventory-filter"
                        >


                            <div class="inventory-search">


                                <span>
                                    🔍
                                </span>


                                <input
                                    type="text"
                                    name="search"
                                    value="{{ $search }}"
                                    placeholder="ค้นหาชื่อยา / รหัสยา..."
                                >


                            </div>



                            <select
                                name="status"
                                class="inventory-select"
                            >


                                @foreach (
                                    $statusLabels
                                    as $key => $label
                                )

                                    <option
                                        value="{{ $key }}"
                                        {{ $status === $key
                                            ? 'selected'
                                            : ''
                                        }}
                                    >

                                        {{ $label }}

                                    </option>

                                @endforeach


                            </select>



                            <button
                                type="submit"
                                class="inventory-filter-btn"
                            >

                                กรอง

                            </button>


                            @if (
                                $search !== '' ||
                                $status !== 'all'
                            )

                                <a
                                    href="{{ route(
                                        'vetcare.staff.inventory'
                                    ) }}"
                                    class="inventory-clear-btn"
                                >

                                    ล้าง

                                </a>

                            @endif


                        </form>


                    </div>



                    {{-- ==========================
                         TABLE
                    =========================== --}}

                    <div class="table-responsive">


                        <table class="table inventory-table">


                            <thead>


                                <tr>

                                    <th>
                                        รหัสยา
                                    </th>

                                    <th>
                                        ชื่อยา
                                    </th>

                                    <th>
                                        หน่วย
                                    </th>

                                    <th>
                                        คงเหลือ
                                    </th>

                                    <th>
                                        สถานะ
                                    </th>

                                    <th>
                                        รายละเอียด
                                    </th>

                                </tr>


                            </thead>



                            <tbody>


                                @forelse (
                                    $filteredMedicines
                                    as $medicine
                                )


                                    <tr
                                        class="{{ $selectedId === $medicine['id']
                                            ? 'inventory-selected-row'
                                            : ''
                                        }}"
                                    >


                                        <td class="inventory-code">

                                            {{ $medicine['id'] }}

                                        </td>


                                        <td>

                                            <strong class="inventory-medicine-name">

                                                {{ $medicine['name'] }}

                                            </strong>

                                        </td>


                                        <td>

                                            {{ $medicine['unit'] }}

                                        </td>


                                        <td>


                                            <strong class="stock-number stock-{{ $medicine['status'] }}">

                                                {{ $medicine['stock'] }}

                                            </strong>


                                        </td>


                                        <td>


                                            <span
                                                class="inventory-status status-{{ $medicine['status'] }}"
                                            >

                                                {{ $statusLabels[
                                                    $medicine['status']
                                                ] }}

                                            </span>


                                        </td>


                                        <td>


                                            <a
                                                href="{{ route(
                                                    'vetcare.staff.inventory',
                                                    [
                                                        'search' => $search,
                                                        'status' => $status,
                                                        'selected' => $medicine['id']
                                                    ]
                                                ) }}#medicine-detail"
                                                class="inventory-detail-btn"
                                            >

                                                ดูรายละเอียด

                                            </a>


                                        </td>


                                    </tr>


                                @empty


                                    <tr>

                                        <td
                                            colspan="6"
                                            class="inventory-empty-table"
                                        >

                                            ไม่พบรายการยาตามเงื่อนไขที่ค้นหา

                                        </td>

                                    </tr>


                                @endforelse


                            </tbody>


                        </table>


                    </div>



                    <div class="inventory-table-footer">

                        แสดง

                        {{ count($filteredMedicines) }}

                        จาก

                        {{ $totalCount }}

                        รายการ

                    </div>


                </section>


            </div>



            {{-- =================================
                 MEDICINE DETAIL
            ================================== --}}

            <div class="col-xl-4">


                <section
                    class="inventory-card medicine-detail-card"
                    id="medicine-detail"
                >


                    @if ($selectedMedicine)


                        {{-- =======================
                             SELECTED
                        ======================== --}}

                    


                        <h2 class="medicine-detail-name">

                            {{ $selectedMedicine['name'] }}

                        </h2>


                        <span
                            class="inventory-status detail-status status-{{ $selectedMedicine['status'] }}"
                        >

                            {{ $statusLabels[
                                $selectedMedicine['status']
                            ] }}

                        </span>



                        <div class="medicine-detail-list">


                            <div>

                                <span>
                                    รหัสยา
                                </span>

                                <strong>
                                    {{ $selectedMedicine['id'] }}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    ประเภทยา
                                </span>

                                <strong>
                                    {{ $selectedMedicine['category'] }}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    หน่วย
                                </span>

                                <strong>
                                    {{ $selectedMedicine['unit'] }}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    คงเหลือ
                                </span>

                                <strong
                                    class="stock-number stock-{{ $selectedMedicine['status'] }}"
                                >

                                    {{ $selectedMedicine['stock'] }}

                                    {{ $selectedMedicine['unit'] }}

                                </strong>

                            </div>


                            <div>

                                <span>
                                    จุดแจ้งเตือน
                                </span>

                                <strong>

                                    {{ $selectedMedicine['minimum'] }}

                                    {{ $selectedMedicine['unit'] }}

                                </strong>

                            </div>


                            <div>

                                <span>
                                    Lot
                                </span>

                                <strong>

                                    {{ $selectedMedicine['lot'] }}

                                </strong>

                            </div>


                            <div>

                                <span>
                                    วันหมดอายุ
                                </span>

                                <strong>

                                    {{ $selectedMedicine['expiry'] }}

                                </strong>

                            </div>


                            <div class="medicine-storage">

                                <span>
                                    การจัดเก็บ
                                </span>

                                <strong>

                                    {{ $selectedMedicine['storage'] }}

                                </strong>

                            </div>


                        </div>



                        <div class="medicine-view-note">

                            Staff สามารถดูข้อมูลได้เท่านั้น

                        </div>


                    @else


                        {{-- =======================
                             NO SELECTION
                        ======================== --}}

                        <div class="medicine-empty-detail">


                           

                            <h3>

                                เลือกยาเพื่อดูรายละเอียด

                            </h3>


                            <p>

                                กดปุ่ม “ดูรายละเอียด”
                                จากรายการด้านซ้าย

                            </p>


                        </div>


                    @endif


                </section>


            </div>


        </div>


    </div>


</div>

@endsection