@extends('vetcare.layouts.staff')

@section('title', 'ดูคลังยา | VetCare')
@section('page-name', 'Inventory')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/inventory.css') }}?v=1">
@endpush

@php
    $statusLabels = ['all' => 'ทุกสถานะ', 'normal' => 'ปกติ', 'low' => 'ใกล้หมด', 'out' => 'หมดสต็อก'];
@endphp

@section('content')

<div class="inventory-page">

    <header class="inventory-topbar">
        <h1>คลังยา</h1>
        <div class="inventory-topbar-right">
            <span>{{ $thaiDate }}</span>
            <span class="inventory-bell">🔔<b>3</b></span>
        </div>
    </header>

    <div class="inventory-content">

        <div class="inventory-info">
            <span class="inventory-info-icon">ℹ</span>
            <p>
                หน้านี้สำหรับ <strong>ดูและค้นหาสต็อกยา</strong> เท่านั้น
                การเพิ่ม แก้ไข หรือลบรายการยา ต้องดำเนินการโดย <strong>ผู้จัดการ</strong>
            </p>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="inventory-summary-card"><span>รายการยาทั้งหมด</span><strong>{{ $totalCount }}</strong></div></div>
            <div class="col-6 col-xl-3"><div class="inventory-summary-card"><span>ปกติ</span><strong class="summary-normal">{{ $normalCount }}</strong></div></div>
            <div class="col-6 col-xl-3"><div class="inventory-summary-card"><span>ใกล้หมด</span><strong class="summary-low">{{ $lowCount }}</strong></div></div>
            <div class="col-6 col-xl-3"><div class="inventory-summary-card"><span>หมดสต็อก</span><strong class="summary-out">{{ $outCount }}</strong></div></div>
        </div>

        <div class="row g-3">

            {{-- STOCK LIST --}}
            <div class="col-xl-8">
                <section class="inventory-card">
                    <div class="inventory-card-header">
                        <h2>รายการสต็อกยา</h2>

                        <form action="{{ route('vetcare.staff.inventory') }}" method="GET" class="inventory-filter">
                            <div class="inventory-search">
                                <span>🔍</span>
                                <input type="text" name="search" value="{{ $search }}" placeholder="ค้นหาชื่อยา / รหัสยา...">
                            </div>

                            <select name="status" class="inventory-select">
                                @foreach ($statusLabels as $key => $label)
                                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="inventory-filter-btn">กรอง</button>

                            @if ($search !== '' || $status !== 'all')
                                <a href="{{ route('vetcare.staff.inventory') }}" class="inventory-clear-btn">ล้าง</a>
                            @endif
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table inventory-table">
                            <thead>
                                <tr>
                                    <th>รหัสยา</th>
                                    <th>ชื่อยา</th>
                                    <th>หน่วย</th>
                                    <th>คงเหลือ</th>
                                    <th>สถานะ</th>
                                    <th>รายละเอียด</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($medicines as $m)
                                    <tr class="{{ $selectedMedicine && $selectedMedicine->medicine_id === $m->medicine_id ? 'inventory-selected-row' : '' }}">
                                        <td class="inventory-code">{{ $m->medicine_id }}</td>
                                        <td><strong class="inventory-medicine-name">{{ $m->medicine_name }}</strong></td>
                                        <td>{{ $m->unit }}</td>
                                        <td><strong class="stock-number stock-{{ $m->stock_status }}">{{ $m->stock_quantity }}</strong></td>
                                        <td><span class="inventory-status status-{{ $m->stock_status }}">{{ $statusLabels[$m->stock_status] }}</span></td>
                                        <td>
                                            <a href="{{ route('vetcare.staff.inventory', ['search' => $search, 'status' => $status, 'selected' => $m->medicine_id]) }}#medicine-detail"
                                               class="inventory-detail-btn">ดูรายละเอียด</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="inventory-empty-table">ไม่พบรายการยาตามเงื่อนไขที่ค้นหา</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="inventory-table-footer">แสดง {{ $medicines->count() }} จาก {{ $totalCount }} รายการ</div>
                </section>
            </div>

            {{-- DETAIL --}}
            <div class="col-xl-4">
                <section class="inventory-card medicine-detail-card" id="medicine-detail">
                    @if ($selectedMedicine)
                        <h2 class="medicine-detail-name">{{ $selectedMedicine->medicine_name }}</h2>

                        <span class="inventory-status detail-status status-{{ $selectedMedicine->stock_status }}">
                            {{ $statusLabels[$selectedMedicine->stock_status] }}
                        </span>

                        <div class="medicine-detail-list">
                            <div><span>รหัสยา</span><strong>{{ $selectedMedicine->medicine_id }}</strong></div>
                            <div><span>หน่วย</span><strong>{{ $selectedMedicine->unit }}</strong></div>
                            <div>
                                <span>คงเหลือ</span>
                                <strong class="stock-number stock-{{ $selectedMedicine->stock_status }}">{{ $selectedMedicine->stock_quantity }} {{ $selectedMedicine->unit }}</strong>
                            </div>
                            <div><span>จุดแจ้งเตือน</span><strong>{{ $selectedMedicine->minimum_stock ?? 0 }} {{ $selectedMedicine->unit }}</strong></div>
                            <div><span>ราคาขาย</span><strong>฿{{ number_format($selectedMedicine->selling_price ?? 0, 2) }}</strong></div>
                            <div class="medicine-storage"><span>รายละเอียด</span><strong>{{ $selectedMedicine->description ?: '-' }}</strong></div>
                        </div>

                        <div class="medicine-view-note">Staff สามารถดูข้อมูลได้เท่านั้น</div>
                    @else
                        <div class="medicine-empty-detail">
                            <h3>เลือกยาเพื่อดูรายละเอียด</h3>
                            <p>กดปุ่ม "ดูรายละเอียด" จากรายการด้านซ้าย</p>
                        </div>
                    @endif
                </section>
            </div>

        </div>
    </div>
</div>

@endsection