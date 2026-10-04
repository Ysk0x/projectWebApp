
@extends('vetcare.layouts.staff')

@section('title', 'การรักษาและจ่ายยา')

@section('page-name', 'Treatments')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/vetcare/treatments.css') }}?v=1">
@endpush

@php
    // วันที่ปัจจุบันสำหรับแสดงบน UI
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

    $thaiDate = 'วัน'
        . $thaiDays[$today->dayOfWeek]
        . 'ที่ ' . $today->day
        . ' ' . $thaiMonths[$today->month]
        . ' ' . ($today->year + 543);

    // ข้อมูลจำลอง
    $pet = [
        'id' => 'PET-004',
        'name' => 'แจ็ค',
        'icon' => '🐶',
        'owner' => 'ประสิทธิ์ สุขดี',
        'type' => 'สุนัข',
        'breed' => 'บีเกิล',
        'gender' => 'ตัวผู้',
        'phone' => '062-111-2233',
    ];

    // ประวัติการรักษาตัวอย่าง
    $histories = [
        [
            'date' => '03/09/2567',
            'service' => 'รักษาโรคผิวหนัง',
            'status' => 'เสร็จสิ้น',
        ],
        [
            'date' => '15/08/2567',
            'service' => 'ฉีดวัคซีนประจำปี',
            'status' => 'เสร็จสิ้น',
        ],
    ];
@endphp


@section('content')

<div class="treat-page">


    <header class="treat-topbar">

        <h1>การรักษาและจ่ายยา</h1>

        <div class="treat-topbar-right">

            <span class="treat-date">
                {{ $thaiDate }}
            </span>

            <div class="treat-bell">
                🔔
                <span class="treat-bell-count">3</span>
            </div>

        </div>

    </header>



    <div class="treat-content">

        <div class="row g-3">


    

            <div class="col-lg-4 col-xl-4">


    
                <section class="treat-card pet-detail-card">

                    <h2 class="treat-card-title">
                        ข้อมูลสัตว์เลี้ยง
                    </h2>


                    <div class="treat-pet-heading">

                        <div class="treat-pet-avatar">
                            {{ $pet['icon'] }}
                        </div>

                        <div class="treat-pet-heading-info">

                            <h3>
                                {{ $pet['name'] }}
                            </h3>

                            <p>
                                เจ้าของ: {{ $pet['owner'] }}
                            </p>

                        </div>

                    </div>



                  

                    <div class="treat-pet-details">

                        <div class="treat-detail-row">

                            <span>ชนิด</span>

                            <strong>
                                {{ $pet['type'] }}
                            </strong>

                        </div>


                        <div class="treat-detail-row">

                            <span>พันธุ์</span>

                            <strong>
                                {{ $pet['breed'] }}
                            </strong>

                        </div>


                        <div class="treat-detail-row">

                            <span>เพศ</span>

                            <strong>
                                {{ $pet['gender'] }}
                            </strong>

                        </div>


                        <div class="treat-detail-row">

                            <span>รหัสสัตว์</span>

                            <strong>
                                {{ $pet['id'] }}
                            </strong>

                        </div>


                        <div class="treat-detail-row">

                            <span>เบอร์โทรเจ้าของ</span>

                            <strong>
                                {{ $pet['phone'] }}
                            </strong>

                        </div>

                    </div>



                    <!-- VIEW HISTORY -->

                    <a href="#treatment-history"
                       class="treat-history-link">

                        ดูประวัติการรักษา

                    </a>

                </section>



                <!-- RECENT HISTORY -->

                <section class="treat-card history-card"
                         id="treatment-history">

                    <h2 class="treat-card-title">
                        ประวัติล่าสุด
                    </h2>


                    @foreach ($histories as $history)

                        <div class="treat-history-item">

                            <span class="history-date">
                                {{ $history['date'] }}
                            </span>

                            <strong class="history-service">
                                {{ $history['service'] }}
                            </strong>

                            <span class="history-status">
                                {{ $history['status'] }}
                            </span>

                        </div>

                    @endforeach


                

                </section>

            </div>




            <div class="col-lg-8 col-xl-8">


          

                <section class="treat-card treatment-card">

                    <h2 class="treat-card-title">
                        บันทึกการรักษา
                    </h2>



                

                    <div class="row g-3 mb-3">

                        <div class="col-md-6">

                            <label class="form-label"
                                   for="treatment-date">

                                วันที่รักษา

                            </label>


                            <input
                                type="date"
                                id="treatment-date"
                                name="treatment_date"
                                class="form-control"
                                value="{{ $today->format('Y-m-d') }}"
                            >

                        </div>



                        <div class="col-md-6">

                            <label class="form-label"
                                   for="treatment-staff">

                                พนักงานผู้รับผิดชอบ

                            </label>


                            <select
                                id="treatment-staff"
                                name="staff"
                                class="form-select"
                            >

                                <option value="1">
                                    วิภา รักดี
                                </option>

                                <option value="2">
                                    ประยุทธ์ สวยงาม
                                </option>

                            </select>

                        </div>

                    </div>



                 

                    <div class="mb-3">

                        <label class="form-label"
                               for="symptoms">

                            อาการ 

                        </label>


                        <textarea
                            id="symptoms"
                            name="symptoms"
                            class="form-control treat-textarea"
                            rows="2"
                            placeholder="ระบุอาการที่พบ..."
                        ></textarea>

                    </div>



                 

                    <div class="mb-3">

                        <label class="form-label"
                               for="diagnosis">

                            การวินิจฉัย 

                        </label>


                        <textarea
                            id="diagnosis"
                            name="diagnosis"
                            class="form-control treat-textarea"
                            rows="2"
                            placeholder="ผลการวินิจฉัย..."
                        ></textarea>

                    </div>




                    <div>

                        <label class="form-label"
                               for="treatment-notes">

                            บันทึกการรักษา

                        </label>


                        <textarea
                            id="treatment-notes"
                            name="treatment_notes"
                            class="form-control treat-textarea"
                            rows="2"
                            placeholder="รายละเอียดการรักษา..."
                        ></textarea>

                    </div>

                </section>





                <section class="treat-card medicine-card"
                         id="medicine-area">

                    <h2 class="treat-card-title">
                         สั่งยา
                    </h2>


            

                    <div class="medicine-form-row">

                        <div class="medicine-field">

                            <label class="form-label"
                                   for="medicine-1">

                                เลือกยา

                            </label>


                            <select
                                id="medicine-1"
                                name="medicine_1"
                                class="form-select"
                            >

                                <option value="">
                                    -- เลือกยา --
                                </option>

                                <option value="amoxicillin">
                                    Amoxicillin 250mg
                                </option>

                                <option value="cephalexin">
                                    Cephalexin 250mg
                                </option>

                                <option value="chlorhexidine">
                                    Chlorhexidine
                                </option>

                            </select>

                        </div>



                        <div class="medicine-field medicine-quantity">

                            <label class="form-label"
                                   for="medicine-quantity-1">

                                จำนวน

                            </label>


                            <input
                                type="number"
                                id="medicine-quantity-1"
                                name="quantity_1"
                                min="1"
                                class="form-control"
                                value="1"
                            >

                        </div>



                        <div class="medicine-field">

                            <label class="form-label"
                                   for="medicine-directions-1">

                                วิธีใช้

                            </label>


                            <input
                                type="text"
                                id="medicine-directions-1"
                                name="directions_1"
                                class="form-control"
                                placeholder="ระบุวิธีใช้ตามคำสั่งสัตวแพทย์"
                            >

                        </div>

                    </div>





                    <details class="additional-medicine">

                        <summary class="treat-add-medicine">

                            <span class="add-label">
                                + เพิ่มยา
                            </span>

                            <span class="hide-label">
                                ซ่อนช่องกรอกเพิ่มเติม
                            </span>

                        </summary>



                        <div class="additional-medicine-content">

                            <h3>
                                ยารายการที่ 2
                            </h3>


                            <div class="medicine-form-row">

                                <div class="medicine-field">

                                    <label class="form-label"
                                           for="medicine-2">

                                        เลือกยา

                                    </label>


                                    <select
                                        id="medicine-2"
                                        name="medicine_2"
                                        class="form-select"
                                    >

                                        <option value="">
                                            -- เลือกยา --
                                        </option>

                                        <option value="amoxicillin">
                                            Amoxicillin 250mg
                                        </option>

                                        <option value="cephalexin">
                                            Cephalexin 250mg
                                        </option>

                                        <option value="chlorhexidine">
                                            Chlorhexidine
                                        </option>

                                    </select>

                                </div>



                                <div class="medicine-field medicine-quantity">

                                    <label class="form-label"
                                           for="medicine-quantity-2">

                                        จำนวน

                                    </label>


                                    <input
                                        type="number"
                                        id="medicine-quantity-2"
                                        name="quantity_2"
                                        min="1"
                                        class="form-control"
                                        value="1"
                                    >

                                </div>



                                <div class="medicine-field">

                                    <label class="form-label"
                                           for="medicine-directions-2">

                                        วิธีใช้

                                    </label>


                                    <input
                                        type="text"
                                        id="medicine-directions-2"
                                        name="directions_2"
                                        class="form-control"
                                        placeholder="ระบุวิธีใช้"
                                    >

                                </div>

                            </div>

                        </div>

                    </details>




                  

                    <div class="medicine-empty">

                        ยังไม่มีรายการยาที่บันทึก

                    </div>

                </section>




           

                <div class="treat-footer-actions">


            

                    <a
                        href="{{ route('vetcare.staff.appointments') }}"
                        class="treat-btn-cancel"
                    >

                        ยกเลิก

                    </a>

                    <button
                        type="button"
                        class="treat-btn-save"
                        disabled
                        title="รอเชื่อมระบบบันทึกการรักษา"
                    >

                        บันทึกการรักษา

                    </button>

                   
<a
    href="{{ route('vetcare.staff.billing', [
        'pet' => $pet['name'],
        'owner' => $pet['owner']
    ]) }}"
    class="treat-btn-confirm"
>
    ยืนยันการจ่ายยา →
</a>



                </div>

            </div>

        </div>

    </div>

</div>

@endsection
