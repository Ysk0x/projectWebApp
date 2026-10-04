@extends('vetcare.layouts.manager')

@section('title', 'คลังยาและข้อมูลยา | VetCare')
@section('page-name', 'Medicines')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/vetcare/manager-medicines.css') }}?v=1"
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
       MOCK MEDICINES
    ========================================= */

    $medicines = [

        [
            'id' => 'MED-001',
            'name' => 'Amoxicillin 250mg',
            'unit' => 'เม็ด',
            'cost' => 3.50,
            'price' => 8.00,
            'stock' => 8,
            'status' => 'low',
        ],

        [
            'id' => 'MED-002',
            'name' => 'Prednisolone 5mg',
            'unit' => 'เม็ด',
            'cost' => 2.00,
            'price' => 5.00,
            'stock' => 3,
            'status' => 'out',
        ],

        [
            'id' => 'MED-003',
            'name' => 'Ivermectin 1%',
            'unit' => 'ml',
            'cost' => 15.00,
            'price' => 35.00,
            'stock' => 12,
            'status' => 'low',
        ],

        [
            'id' => 'MED-004',
            'name' => 'Metronidazole 200mg',
            'unit' => 'เม็ด',
            'cost' => 2.50,
            'price' => 6.00,
            'stock' => 0,
            'status' => 'out',
        ],

        [
            'id' => 'MED-005',
            'name' => 'Dexamethasone Inj.',
            'unit' => 'ml',
            'cost' => 25.00,
            'price' => 55.00,
            'stock' => 5,
            'status' => 'low',
        ],

        [
            'id' => 'MED-006',
            'name' => 'Penicillin G 1MU',
            'unit' => 'ขวด',
            'cost' => 45.00,
            'price' => 120.00,
            'stock' => 38,
            'status' => 'normal',
        ],

        [
            'id' => 'MED-007',
            'name' => 'Cephalexin 500mg',
            'unit' => 'แคปซูล',
            'cost' => 5.00,
            'price' => 12.00,
            'stock' => 120,
            'status' => 'normal',
        ],

        [
            'id' => 'MED-008',
            'name' => 'Furosemide 40mg',
            'unit' => 'เม็ด',
            'cost' => 3.00,
            'price' => 7.00,
            'stock' => 65,
            'status' => 'normal',
        ],

        [
            'id' => 'MED-009',
            'name' => 'Omeprazole 20mg',
            'unit' => 'แคปซูล',
            'cost' => 4.00,
            'price' => 10.00,
            'stock' => 60,
            'status' => 'normal',
        ],

        [
            'id' => 'MED-010',
            'name' => 'Vitamin B Complex',
            'unit' => 'ขวด',
            'cost' => 18.00,
            'price' => 40.00,
            'stock' => 200,
            'status' => 'normal',
        ],

    ];


    /* =========================================
       STATUS
    ========================================= */

    $statusLabels = [
        'normal' => 'ปกติ',
        'low' => 'ใกล้หมด',
        'out' => 'หมดสต็อก',
    ];


    /* =========================================
       SUMMARY
    ========================================= */

    $totalMedicines = count($medicines);

    $normalCount = count(
        array_filter(
            $medicines,
            fn ($medicine) =>
                $medicine['status'] === 'normal'
        )
    );

    $lowCount = count(
        array_filter(
            $medicines,
            fn ($medicine) =>
                $medicine['status'] === 'low'
        )
    );

    $outCount = count(
        array_filter(
            $medicines,
            fn ($medicine) =>
                $medicine['status'] === 'out'
        )
    );


    /* =========================================
       SEARCH
    ========================================= */

    $search = trim(
        (string) request('search', '')
    );

    $filteredMedicines = array_values(
        array_filter(
            $medicines,
            function ($medicine) use ($search) {

                if ($search === '') {
                    return true;
                }

                $text =
                    $medicine['id'] .
                    ' ' .
                    $medicine['name'] .
                    ' ' .
                    $medicine['unit'];

                return mb_stripos(
                    $text,
                    $search
                ) !== false;

            }
        )
    );


    /* =========================================
       CREATE / EDIT / DELETE PANEL
    ========================================= */

    $panel = request('panel');

    $selectedMedicine = null;

    if (
        in_array(
            $panel,
            ['edit', 'delete'],
            true
        )
    ) {

        foreach ($medicines as $medicine) {

            if (
                $medicine['id']
                === request('id')
            ) {

                $selectedMedicine = $medicine;

                break;
            }

        }

    }

@endphp


@section('content')

<div class="manager-medicines-page">



    <header class="manager-medicines-topbar">

        <div>

            <h1>
                คลังยาและข้อมูลยา
            </h1>

            <p>
                จัดการรายการยา คลังยา และราคาขายภายในคลินิก
            </p>

        </div>


        <div class="manager-medicines-topbar-right">

            <span>
                {{ $thaiDate }}
            </span>

            <div class="manager-medicines-bell">

                🔔

                <b>
                    3
                </b>

            </div>

        </div>

    </header>



    <div class="manager-medicines-content">


        <div class="row g-3 mb-3">


            <div class="col-6 col-xl-3">

                <div class="medicine-summary-card">

                    <span>
                        รายการยาทั้งหมด
                    </span>

                    <strong>
                        {{ $totalMedicines }}
                    </strong>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="medicine-summary-card">

                    <span>
                        ปกติ
                    </span>

                    <strong class="summary-normal">
                        {{ $normalCount }}
                    </strong>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="medicine-summary-card">

                    <span>
                        ใกล้หมด
                    </span>

                    <strong class="summary-low">
                        {{ $lowCount }}
                    </strong>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="medicine-summary-card">

                    <span>
                        หมดสต็อก
                    </span>

                    <strong class="summary-out">
                        {{ $outCount }}
                    </strong>

                </div>

            </div>


        </div>

        <section class="manager-medicine-card">

            <div class="manager-medicine-card-header">


                <h2>
                    รายการยาทั้งหมด
                </h2>


                <div class="medicine-toolbar">


                    {{-- SEARCH --}}

                    <form
                        method="GET"
                        action="{{ route(
                            'vetcare.manager.medicines'
                        ) }}"
                        class="medicine-search"
                    >

                        <span>
                            🔍
                        </span>

                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="ค้นหาชื่อยา / รหัสยา..."
                        >

                    </form>



                    {{-- ADD --}}

                    <a
                        href="{{ route(
                            'vetcare.manager.medicines',
                            [
                                'panel' => 'create'
                            ]
                        ) }}#medicine-modal"
                        class="add-medicine-btn"
                    >

                        + เพิ่มยาใหม่

                    </a>


                </div>


            </div>



    

            <div class="table-responsive">


                <table class="table manager-medicine-table">


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
                                ราคาต้นทุน
                            </th>

                            <th>
                                ราคาขาย / หน่วย
                            </th>

                            <th>
                                คงเหลือ
                            </th>

                            <th>
                                สถานะ
                            </th>

                            <th>
                                การจัดการ
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        @forelse (
                            $filteredMedicines
                            as $medicine
                        )


                            <tr>


                                {{-- ID --}}

                                <td class="medicine-code">

                                    {{ $medicine['id'] }}

                                </td>



                                {{-- NAME --}}

                                <td>

                                    <strong class="medicine-name">

                                        {{ $medicine['name'] }}

                                    </strong>

                                </td>



                                {{-- UNIT --}}

                                <td>

                                    {{ $medicine['unit'] }}

                                </td>



                                {{-- COST --}}

                                <td>

                                    ฿{{ number_format(
                                        $medicine['cost'],
                                        2
                                    ) }}

                                </td>



                                {{-- PRICE --}}

                                <td>

                                    <strong class="medicine-sale-price">

                                        ฿{{ number_format(
                                            $medicine['price'],
                                            2
                                        ) }}

                                    </strong>

                                </td>



                                {{-- STOCK --}}

                                <td>

                                    <strong
                                        class="medicine-stock
                                        stock-{{ $medicine['status'] }}"
                                    >

                                        {{ $medicine['stock'] }}

                                    </strong>

                                </td>



                                {{-- STATUS --}}

                                <td>

                                    <span
                                        class="medicine-status
                                        status-{{ $medicine['status'] }}"
                                    >

                                        {{ $statusLabels[
                                            $medicine['status']
                                        ] }}

                                    </span>

                                </td>



                                {{-- ACTIONS --}}

                                <td>

                                    <div class="medicine-actions">


                                        {{-- EDIT --}}

                                        <a
                                            href="{{ route(
                                                'vetcare.manager.medicines',
                                                [
                                                    'panel' => 'edit',
                                                    'id' => $medicine['id']
                                                ]
                                            ) }}#medicine-modal"
                                            class="medicine-edit-btn"
                                        >

                                            แก้ไข

                                        </a>


                                        {{-- DELETE --}}

                                        <a
                                            href="{{ route(
                                                'vetcare.manager.medicines',
                                                [
                                                    'panel' => 'delete',
                                                    'id' => $medicine['id']
                                                ]
                                            ) }}#medicine-modal"
                                            class="medicine-delete-btn"
                                        >

                                            ลบ

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        @empty


                            <tr>

                                <td
                                    colspan="8"
                                    class="medicine-empty"
                                >

                                    ไม่พบรายการยา

                                </td>

                            </tr>


                        @endforelse


                    </tbody>


                </table>


            </div>



            <div class="medicine-table-footer">

                แสดง

                {{ count($filteredMedicines) }}

                จาก

                {{ $totalMedicines }}

                รายการ

            </div>


        </section>


    </div>



    {{-- =================================================
         CREATE MEDICINE
    ================================================== --}}

    @if ($panel === 'create')


        <div
            class="manager-medicine-modal-overlay"
            id="medicine-modal"
        >


            <div class="manager-medicine-modal">


                {{-- HEADER --}}

                <div class="medicine-modal-header">

                    <h2>
                        เพิ่มยาใหม่
                    </h2>

                    <a
                        href="{{ route(
                            'vetcare.manager.medicines'
                        ) }}"
                        class="medicine-modal-close"
                    >

                        ×

                    </a>

                </div>



                {{-- BODY --}}

                <div class="medicine-modal-body">


                    {{-- NAME --}}

                    <div class="mb-3">

                        <label class="form-label">
                            ชื่อยา
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            placeholder="กรอกชื่อยา"
                        >

                    </div>



                    {{-- UNIT --}}

                    <div class="mb-3">

                        <label class="form-label">
                            หน่วย
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            placeholder="เช่น เม็ด / ml / ขวด"
                        >

                    </div>



                    {{-- COST --}}

                    <div class="mb-3">

                        <label class="form-label">
                            ราคาต้นทุน (บาท)
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                        >

                    </div>



                    {{-- SALE PRICE --}}

                    <div class="mb-3">

                        <label class="form-label">
                            ราคาขาย / หน่วย (บาท)
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                        >

                    </div>



                    {{-- STOCK --}}

                    <div>

                        <label class="form-label">
                            จำนวนคงเหลือ
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            min="0"
                            placeholder="0"
                        >

                    </div>


                </div>



                {{-- FOOTER --}}

                <div class="medicine-modal-footer">


                    <a
                        href="{{ route(
                            'vetcare.manager.medicines'
                        ) }}"
                        class="medicine-modal-cancel"
                    >

                        ยกเลิก

                    </a>


                    <button
                        type="button"
                        class="medicine-modal-save"
                    >

                        บันทึก

                    </button>


                </div>


            </div>


        </div>


    @endif



    {{-- =================================================
         EDIT MEDICINE
    ================================================== --}}

    @if (
        $panel === 'edit'
        &&
        $selectedMedicine
    )


        <div
            class="manager-medicine-modal-overlay"
            id="medicine-modal"
        >


            <div class="manager-medicine-modal">


                <div class="medicine-modal-header">

                    <h2>
                        แก้ไขข้อมูลยา
                    </h2>

                    <a
                        href="{{ route(
                            'vetcare.manager.medicines'
                        ) }}"
                        class="medicine-modal-close"
                    >

                        ×

                    </a>

                </div>



                <div class="medicine-modal-body">


                    <div class="mb-3">

                        <label class="form-label">
                            ชื่อยา
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $selectedMedicine['name'] }}"
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            หน่วย
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $selectedMedicine['unit'] }}"
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            ราคาต้นทุน (บาท)
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            step="0.01"
                            value="{{ $selectedMedicine['cost'] }}"
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            ราคาขาย / หน่วย (บาท)
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            step="0.01"
                            value="{{ $selectedMedicine['price'] }}"
                        >

                    </div>


                    <div>

                        <label class="form-label">
                            จำนวนคงเหลือ
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            value="{{ $selectedMedicine['stock'] }}"
                        >

                    </div>


                </div>



                <div class="medicine-modal-footer">


                    <a
                        href="{{ route(
                            'vetcare.manager.medicines'
                        ) }}"
                        class="medicine-modal-cancel"
                    >

                        ยกเลิก

                    </a>


                    <button
                        type="button"
                        class="medicine-modal-save"
                    >

                        บันทึก

                    </button>


                </div>


            </div>


        </div>


    @endif



    {{-- =================================================
         DELETE
    ================================================== --}}

    @if (
        $panel === 'delete'
        &&
        $selectedMedicine
    )


        <div
            class="manager-medicine-modal-overlay"
            id="medicine-modal"
        >


            <div class="manager-medicine-modal small-medicine-modal">


                <div class="medicine-modal-header">

                    <h2>
                        ยืนยันการลบยา
                    </h2>

                    <a
                        href="{{ route(
                            'vetcare.manager.medicines'
                        ) }}"
                        class="medicine-modal-close"
                    >

                        ×

                    </a>

                </div>


                <div class="medicine-delete-body">


                    <div class="medicine-delete-icon">
                        ⚠️
                    </div>


                    <p>

                        ต้องการลบ

                        <strong>
                            {{ $selectedMedicine['name'] }}
                        </strong>

                        ใช่หรือไม่?

                    </p>


                    <small>
                        การดำเนินการนี้ไม่สามารถย้อนกลับได้
                    </small>


                </div>


                <div class="medicine-modal-footer">


                    <a
                        href="{{ route(
                            'vetcare.manager.medicines'
                        ) }}"
                        class="medicine-modal-cancel"
                    >

                        ยกเลิก

                    </a>


                    <button
                        type="button"
                        class="medicine-delete-confirm"
                    >

                        ลบยา

                    </button>


                </div>


            </div>


        </div>


    @endif


</div>

@endsection