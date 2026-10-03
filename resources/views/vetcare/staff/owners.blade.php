@extends('vetcare.layouts.staff')

@section('title', 'Owners & Pets')

@section('page-name', 'Owners & Pets')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/vetcare/owners.css') }}">
@endpush


@section('content')

<div class="owners-page">


    <div class="owners-header">

        <div>
            <h1 class="owners-title">
                เจ้าของสัตว์และสัตว์เลี้ยง 
            </h1>
        </div>

        <div class="owners-header-right">

            <span class="owners-date">
                วันพฤหัสบดีที่ 24 กันยายน 2569
            </span>

            <div class="owners-bell">
                🔔
                <span class="bell-number">3</span>
            </div>

        </div>

    </div>


    <div class="owners-toolbar">


        <div class="owner-search">

            <span class="search-icon">
                🔍
            </span>

            <input
                type="text"
                class="form-control"
                placeholder="ค้นหาเจ้าของ / สัตว์เลี้ยง..."
            >

        </div>

        <div class="owner-actions">

            <button
                class="btn btn-outline-primary"
                data-bs-toggle="modal"
                data-bs-target="#addOwnerModal"
            >
                + เพิ่มเจ้าของ
            </button>


            <button class="btn btn-primary">
                + เพิ่มสัตว์เลี้ยง
            </button>

        </div>

    </div>


    <div class="row g-4">




        <div class="col-lg-5 col-xl-4">

            <h2 class="owner-list-title">
                เจ้าของสัตว์ (5 คน)
            </h2>


            <div class="owner-list">


                <div class="owner-item">

                    <div class="owner-avatar">
                        ว
                    </div>


                    <div class="owner-item-info">

                        <div class="owner-item-top">

                            <h3>
                                วิชัย สมใจ
                            </h3>

                            <span class="owner-pet-count">
                                2 ตัว
                            </span>

                        </div>


                        <p class="owner-phone">
                            081-234-5678
                        </p>

                        <p class="owner-address">
                            12/3 ถ.สุขุมวิท กรุงเทพ
                        </p>

                    </div>

                </div>





                <div class="owner-item">

                    <div class="owner-avatar">
                        พ
                    </div>


                    <div class="owner-item-info">

                        <div class="owner-item-top">

                            <h3>
                                พิมพ์ใจ ดีงาม
                            </h3>

                            <span class="owner-pet-count">
                                1 ตัว
                            </span>

                        </div>


                        <p class="owner-phone">
                            089-765-4321
                        </p>

                        <p class="owner-address">
                            45 ถ.ลาดพร้าว 15 กรุงเทพ
                        </p>

                    </div>

                </div>



                <div class="owner-item active">

                    <div class="owner-avatar">
                        ป
                    </div>


                    <div class="owner-item-info">

                        <div class="owner-item-top">

                            <h3>
                                ประสิทธิ์ สุขดี
                            </h3>

                            <span class="owner-pet-count">
                                1 ตัว
                            </span>

                        </div>


                        <p class="owner-phone">
                            062-111-2233
                        </p>

                        <p class="owner-address">
                            7/8 ม.3 ถ.บางรัก กรุงเทพ
                        </p>

                    </div>

                </div>



                <div class="owner-item">

                    <div class="owner-avatar">
                        ส
                    </div>


                    <div class="owner-item-info">

                        <div class="owner-item-top">

                            <h3>
                                สมศักดิ์ ใจดี
                            </h3>

                            <span class="owner-pet-count">
                                1 ตัว
                            </span>

                        </div>


                        <p class="owner-phone">
                            093-444-5566
                        </p>

                        <p class="owner-address">
                            88 ถ.รัชดา กรุงเทพ
                        </p>

                    </div>

                </div>




                <div class="owner-item">

                    <div class="owner-avatar">
                        บ
                    </div>


                    <div class="owner-item-info">

                        <div class="owner-item-top">

                            <h3>
                                บุญมี รักสวย
                            </h3>

                            <span class="owner-pet-count">
                                2 ตัว
                            </span>

                        </div>


                        <p class="owner-phone">
                            085-999-1234
                        </p>

                        <p class="owner-address">
                            3/3 ถ.พหลโยธิน กรุงเทพ
                        </p>

                    </div>

                </div>


            </div>

        </div>




        <div class="col-lg-7 col-xl-8">


            <div class="owner-detail-card">

                <div class="detail-card-header">

                    <h2>
                        ข้อมูลเจ้าของ
                    </h2>

                    <button class="btn btn-outline-primary btn-sm">
                        แก้ไข
                    </button>

                </div>


                <div class="row g-3">

                    <div class="col-md-6">

                        <p class="detail-label">
                            รหัสเจ้าของ
                        </p>

                        <p class="detail-value">
                            OWN-003
                        </p>

                    </div>


                    <div class="col-md-6">

                        <p class="detail-label">
                            ชื่อ-นามสกุล
                        </p>

                        <p class="detail-value">
                            ประสิทธิ์ สุขดี
                        </p>

                    </div>


                    <div class="col-md-6">

                        <p class="detail-label">
                            เบอร์โทร
                        </p>

                        <p class="detail-value">
                            062-111-2233
                        </p>

                    </div>


                    <div class="col-md-6">

                        <p class="detail-label">
                            ที่อยู่
                        </p>

                        <p class="detail-value">
                            7/8 ม.3 ถ.บางรัก กรุงเทพ
                        </p>

                    </div>

                </div>

            </div>



          

            <h2 class="pet-section-title">
                สัตว์เลี้ยง (1 ตัว)
            </h2>



           

            <div class="pet-card">

                <div class="pet-main">

                    <div class="pet-icon">
                        🐶
                    </div>


                    <div class="pet-info">

                        <h3>
                            แจ็ค
                        </h3>

                        <p>
                            สุนัข • บีเกิล
                        </p>


                        <div class="pet-tags">

                            <span>
                                ตัวผู้
                            </span>

                            <span>
                                เกิด 10/08/2561
                            </span>

                        </div>

                    </div>

                </div>


                <div class="pet-actions">

                    <button class="btn btn-outline-primary btn-sm">
                        🩺 รักษา
                    </button>

                    <button class="btn btn-outline-secondary btn-sm">
                        ประวัติ
                    </button>

                </div>

            </div>



        

            <button class="btn btn-outline-primary add-pet-button">
                + เพิ่มสัตว์เลี้ยง
            </button>


        </div>

    </div>

</div>




<div
    class="modal fade"
    id="addOwnerModal"
    tabindex="-1"
>

    <div class="modal-dialog">

        <div class="modal-content">


            <div class="modal-header">

                <h5 class="modal-title">
                    เพิ่มเจ้าของสัตว์
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">

                <div class="mb-3">

                    <label class="form-label">
                        ชื่อ-นามสกุล
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        placeholder="กรอกชื่อ-นามสกุล"
                    >

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        เบอร์โทร
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        placeholder="กรอกเบอร์โทร"
                    >

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        ที่อยู่
                    </label>

                    <textarea
                        class="form-control"
                        rows="3"
                        placeholder="กรอกที่อยู่"
                    ></textarea>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    ยกเลิก
                </button>


                <button
                    type="button"
                    class="btn btn-primary"
                >
                    บันทึก
                </button>

            </div>


        </div>

    </div>

</div>

@endsection