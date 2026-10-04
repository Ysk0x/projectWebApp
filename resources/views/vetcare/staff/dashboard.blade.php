@extends('vetcare.layouts.staff')

@section('title', 'Staff Dashboard')

@section('page-name', 'Staff Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/dashboard.css') }}">
@endpush

@section('content')

<div class="staff-dashboard-page">
    <div class="dashboard-topbar">

        <div>
            <h1 class="dashboard-title">
                ภาพรวมคลินิก
            </h1>

            <p class="dashboard-subtitle">
                ตารางงานและคิววันนี้
            </p>
        </div>

        <div class="dashboard-topbar-right">

            <div class="dashboard-date">
                วันพฤหัสบดีที่ 24 กันยายน 2569
            </div>

            <div class="notification-bell">
                🔔
                <span class="notification-badge">3</span>
            </div>

        </div>

    </div>


    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-blue">📋</div>

                <div class="summary-content">
                    <p class="summary-label">คิววันนี้</p>
                    <h2 class="summary-value">12</h2>
                    <p class="summary-unit">รายการ</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-green">🐾</div>

                <div class="summary-content">
                    <p class="summary-label">สัตว์รอรับบริการ</p>
                    <h2 class="summary-value">6</h2>
                    <p class="summary-unit">ตัว</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-yellow">💳</div>

                <div class="summary-content">
                    <p class="summary-label">รอชำระเงิน</p>
                    <h2 class="summary-value">4</h2>
                    <p class="summary-unit">รายการ</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-pink">📌</div>

                <div class="summary-content">
                    <p class="summary-label">ติดตามเคส</p>
                    <h2 class="summary-value">3</h2>
                    <p class="summary-unit">รายการ</p>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-xl-8">

            <div class="dashboard-panel schedule-panel">

                <div class="panel-header">
                    <h2 class="panel-title">
                        ตารางงานวันนี้
                    </h2>

                    <button class="btn btn-view-all">
                        ดูทั้งหมด
                    </button>
                </div>


                <div class="table-responsive">
                    <table class="table schedule-table">
                        <thead>
                            <tr>
                                <th>เวลา</th>
                                <th>สัตว์เลี้ยง</th>
                                <th>เจ้าของ</th>
                                <th>ประเภทบริการ</th>
                                <th>สถานะ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td class="time-text">09:00</td>
                                <td>มะม่วง</td>
                                <td>วิชัย สมใจ</td>
                                <td>ตรวจทั่วไป</td>
                                <td>
                                    <span class="status-badge status-done">เสร็จสิ้น</span>
                                </td>
                                <td class="text-center">-</td>
                            </tr>

                            <tr>
                                <td class="time-text">09:30</td>
                                <td>มุก</td>
                                <td>พิมพ์ใจ ดีงาม</td>
                                <td>ฉีดวัคซีน</td>
                                <td>
                                    <span class="status-badge status-done">เสร็จสิ้น</span>
                                </td>
                                <td class="text-center">-</td>
                            </tr>

                            <tr>
                                <td class="time-text">10:00</td>
                                <td>แจ็ค</td>
                                <td>ประสิทธิ์ สุขดี</td>
                                <td>รักษาโรคผิวหนัง</td>
                                <td>
                                    <span class="status-badge status-serving">กำลังรับบริการ</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-action-outline">ชำระเงิน</button>
                                </td>
                            </tr>

                            <tr>
                                <td class="time-text">11:00</td>
                                <td>บาสโก้</td>
                                <td>สมศักดิ์ ใจดี</td>
                                <td>ตรวจฟัน</td>
                                <td>
                                    <span class="status-badge status-waiting">รอรับบริการ</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-action-primary">รักษา</button>
                                </td>
                            </tr>

                            <tr>
                                <td class="time-text">13:00</td>
                                <td>ดาว</td>
                                <td>บุญมี รักสวย</td>
                                <td>ทำหมัน</td>
                                <td>
                                    <span class="status-badge status-waiting">รอรับบริการ</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-action-primary">รักษา</button>
                                </td>
                            </tr>

                            <tr>
                                <td class="time-text">14:30</td>
                                <td>ลัคกี้</td>
                                <td>สุดา มีแก้ว</td>
                                <td>ตรวจทั่วไป</td>
                                <td>
                                    <span class="status-badge status-appointment">นัดหมาย</span>
                                </td>
                                <td class="text-center">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>



    
        <div class="col-xl-4">
            <div class="dashboard-panel side-panel mb-4">

                <div class="panel-header">
                    <h2 class="panel-title">
                        📌 เคสที่ต้องติดตาม
                    </h2>
                </div>


                <div class="follow-card">
                    <div class="follow-head">
                        <h5>บาสโก้</h5>
                        <span class="follow-tag tag-today">วันนี้</span>
                    </div>

                    <p class="follow-owner">สมศักดิ์ ใจดี</p>
                    <p class="follow-note">ติดตามอาการหลังผ่าตัด 7 วัน</p>
                </div>


                <div class="follow-card">
                    <div class="follow-head">
                        <h5>มะม่วง</h5>
                        <span class="follow-tag tag-tomorrow">พรุ่งนี้</span>
                    </div>

                    <p class="follow-owner">วิชัย สมใจ</p>
                    <p class="follow-note">นัดติดตามผลการรักษา</p>
                </div>


                <div class="follow-card">
                    <div class="follow-head">
                        <h5>ลัคกี้</h5>
                        <span class="follow-tag tag-3days">3 วัน</span>
                    </div>

                    <p class="follow-owner">สุดา มีแก้ว</p>
                    <p class="follow-note">ตรวจเลือด follow-up</p>
                </div>

            </div>



          
            <div class="dashboard-panel side-panel">

                <div class="panel-header">
                    <h2 class="panel-title">
                        💳 รอชำระเงิน
                    </h2>
                </div>


                <div class="payment-item">
                    <div>
                        <h5>แจ็ค</h5>
                        <p>ประสิทธิ์ สุขดี</p>
                    </div>

                    <div class="payment-right">
                        <div class="payment-price">฿920</div>
                        <a href="#" class="payment-link">ชำระ</a>
                    </div>
                </div>


                <div class="payment-item">
                    <div>
                        <h5>บาสโก้</h5>
                        <p>สมศักดิ์ ใจดี</p>
                    </div>

                    <div class="payment-right">
                        <div class="payment-price">฿3,800</div>
                        <a href="#" class="payment-link">ชำระ</a>
                    </div>
                </div>


                <div class="payment-item">
                    <div>
                        <h5>ลัคกี้</h5>
                        <p>สุดา มีแก้ว</p>
                    </div>

                    <div class="payment-right">
                        <div class="payment-price">฿650</div>
                        <a href="#" class="payment-link">ชำระ</a>
                    </div>
                </div>


                <div class="payment-item">
                    <div>
                        <h5>ดาว</h5>
                        <p>บุญมี รักสวย</p>
                    </div>

                    <div class="payment-right">
                        <div class="payment-price">฿5,200</div>
                        <a href="#" class="payment-link">ชำระ</a>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection