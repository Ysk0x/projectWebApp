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
            <h1 class="dashboard-title">ภาพรวมคลินิก</h1>
            <p class="dashboard-subtitle">ตารางงานและคิววันนี้</p>
        </div>

        <div class="dashboard-topbar-right">
            <div class="dashboard-date">{{ $thaiDate }}</div>
            <div class="notification-bell">🔔<span class="notification-badge">3</span></div>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    {{-- SUMMARY --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-blue">📋</div>
                <div class="summary-content">
                    <p class="summary-label">คิววันนี้</p>
                    <h2 class="summary-value">{{ $todayScheduleCount }}</h2>
                    <p class="summary-unit">รายการ</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-green">🐾</div>
                <div class="summary-content">
                    <p class="summary-label">สัตว์รอรับบริการ</p>
                    <h2 class="summary-value">{{ $waitingTreatment }}</h2>
                    <p class="summary-unit">ตัว</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-yellow">💳</div>
                <div class="summary-content">
                    <p class="summary-label">รอชำระเงิน</p>
                    <h2 class="summary-value">{{ $waitingPayment }}</h2>
                    <p class="summary-unit">รายการ</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-pink">📌</div>
                <div class="summary-content">
                    <p class="summary-label">ติดตามเคส</p>
                    <h2 class="summary-value">{{ $followCaseCount }}</h2>
                    <p class="summary-unit">รายการ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- SCHEDULE --}}
        <div class="col-xl-8">
            <div class="dashboard-panel schedule-panel">
                <div class="panel-header">
                    <h2 class="panel-title">ตารางงานวันนี้</h2>
                    <a href="{{ route('vetcare.staff.appointments') }}" class="btn btn-view-all">ดูทั้งหมด</a>
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
                            @forelse ($todaySchedule as $row)
                                <tr>
                                    <td class="time-text">{{ substr($row->appointment_time, 0, 5) }}</td>
                                    <td>{{ $row -> pet_name }}</td>
                                    <td>{{ $row -> owner_name }}</td>
                                    <td>{{ $row -> service_type }}</td>
                                    <td>
                                        <span class="status-badge
                                            @if ($row->status === 'scheduled')
                                                status-waiting
                                            @elseif ($row->status === 'completed')
                                                status-completed
                                            @elseif ($row->status === 'cancelled')
                                                status-cancelled
                                            @endif
                                        ">
                                            @if ($row->status === 'scheduled')
                                                รอรับบริการ
                                            @elseif ($row->status === 'completed')
                                                เสร็จสิ้น
                                            @elseif ($row->status === 'cancelled')
                                                ยกเลิก
                                            @else
                                                {{ $row->status }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($row-> status === 'scheduled')
                                            <a href="{{ route('vetcare.staff.treatments', ['appointment' => $row -> appointment_id]) }}" class="btn btn-action-primary">รักษา</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีนัดหมายวันนี้</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SIDE --}}
        <div class="col-xl-4">

            <div class="dashboard-panel side-panel mb-4">
                <div class="panel-header">
                    <h2 class="panel-title">📌 เคสที่ต้องติดตาม</h2>
                </div>

                @forelse ($followCase as $case)
                    <div class="follow-card">
                        <div class="follow-head">
                            <h5>{{ $case -> pet_name }}</h5>
                            {{-- <span class="follow-tag tag-{{ $case['tag'] }}">{{ $case['label'] }}</span> --}}
                        </div>
                        <p class="follow-owner">{{ $case -> owner_name }}</p>
                        <p class="follow-note">หมายเหตุ: 
                                                @if (empty($case -> notes))
                                                    -
                                                @else
                                                    {{ $case -> notes }}
                                                @endif
                        </p>
                        <p class="follow-service-type">ประเภท: {{ $case -> service_type }}</p>
                    </div>
                @empty
                    <p class="text-muted px-3 pb-3 mb-0">ไม่มีนัดหมายใน 7 วันข้างหน้า</p>
                @endforelse
            </div>

            <div class="dashboard-panel side-panel">
                <div class="panel-header">
                    <h2 class="panel-title">💳 รอชำระเงิน</h2>
                </div>

                @forelse ($unpaidInvoice as $inv)
                    <div class="payment-item">
                        <div>
                            <h5>{{ $inv -> pet_name }}</h5>
                            <p>{{ $inv-> owner_name }}</p>
                        </div>
                        <div class="payment-right">
                            <div class="payment-price">฿{{ number_format($inv->total_amount) }}</div>
                            <a href="{{ route('vetcare.staff.billing', ['invoice' => $inv -> invoice_id]) }}" class="payment-link">ชำระ</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted px-3 pb-3 mb-0">ไม่มีรายการรอชำระเงิน</p>
                @endforelse
            </div>

        </div>
    </div>

</div>

@endsection