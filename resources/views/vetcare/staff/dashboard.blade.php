@extends('vetcare.layouts.staff')

@section('title', 'Staff Dashboard')
@section('page-name', 'Staff Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/dashboard.css') }}">
@endpush

@php
    $statusLabels = ['done' => 'เสร็จสิ้น', 'serving' => 'รอชำระเงิน', 'waiting' => 'รอรับบริการ', 'confirmed' => 'นัดหมาย'];
    $statusClass  = ['done' => 'status-done', 'serving' => 'status-serving', 'waiting' => 'status-waiting', 'confirmed' => 'status-appointment'];
@endphp

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
                    <h2 class="summary-value">{{ $queueCount }}</h2>
                    <p class="summary-unit">รายการ</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-green">🐾</div>
                <div class="summary-content">
                    <p class="summary-label">สัตว์รอรับบริการ</p>
                    <h2 class="summary-value">{{ $waitingCount }}</h2>
                    <p class="summary-unit">ตัว</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-yellow">💳</div>
                <div class="summary-content">
                    <p class="summary-label">รอชำระเงิน</p>
                    <h2 class="summary-value">{{ $unpaidCount }}</h2>
                    <p class="summary-unit">รายการ</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon icon-pink">📌</div>
                <div class="summary-content">
                    <p class="summary-label">ติดตามเคส</p>
                    <h2 class="summary-value">{{ count($followUps) }}</h2>
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
                            @forelse ($schedule as $row)
                                <tr>
                                    <td class="time-text">{{ $row['time'] }}</td>
                                    <td>{{ $row['pet'] }}</td>
                                    <td>{{ $row['owner'] }}</td>
                                    <td>{{ $row['service'] }}</td>
                                    <td>
                                        <span class="status-badge {{ $statusClass[$row['status']] ?? '' }}">
                                            {{ $statusLabels[$row['status']] ?? $row['status'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($row['status'] === 'serving' && $row['invoice_id'])
                                            <a href="{{ route('vetcare.staff.billing', ['invoice' => $row['invoice_id']]) }}" class="btn btn-action-outline">ชำระเงิน</a>
                                        @elseif ($row['status'] === 'waiting')
                                            <a href="{{ route('vetcare.staff.treatments', ['appointment' => $row['id']]) }}" class="btn btn-action-primary">รักษา</a>
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

                @forelse ($followUps as $case)
                    <div class="follow-card">
                        <div class="follow-head">
                            <h5>{{ $case['pet'] }}</h5>
                            <span class="follow-tag tag-{{ $case['tag'] }}">{{ $case['label'] }}</span>
                        </div>
                        <p class="follow-owner">{{ $case['owner'] }}</p>
                        <p class="follow-note">{{ $case['note'] }}</p>
                    </div>
                @empty
                    <p class="text-muted px-3 pb-3 mb-0">ไม่มีนัดหมายใน 7 วันข้างหน้า</p>
                @endforelse
            </div>

            <div class="dashboard-panel side-panel">
                <div class="panel-header">
                    <h2 class="panel-title">💳 รอชำระเงิน</h2>
                </div>

                @forelse ($unpaidInvoices as $inv)
                    <div class="payment-item">
                        <div>
                            <h5>{{ $inv->pet_name }}</h5>
                            <p>{{ trim($inv->first_name . ' ' . $inv->last_name) }}</p>
                        </div>
                        <div class="payment-right">
                            <div class="payment-price">฿{{ number_format($inv->total_amount) }}</div>
                            <a href="{{ route('vetcare.staff.billing', ['invoice' => $inv->invoice_id]) }}" class="payment-link">ชำระ</a>
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