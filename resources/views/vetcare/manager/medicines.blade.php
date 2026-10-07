@extends('vetcare.layouts.manager')

@section('title', 'คลังยาและข้อมูลยา | VetCare')
@section('page-name', 'Medicines')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/manager-medicines.css') }}?v=1">
@endpush

@php
    $today = now('Asia/Bangkok');
    $thaiMonths = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
    $thaiDays = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
    $thaiDate = 'วัน' . $thaiDays[$today->dayOfWeek] . 'ที่ ' . $today->day . ' ' . $thaiMonths[$today->month] . ' ' . ($today->year + 543);

    $statusLabels = ['normal' => 'ปกติ', 'low' => 'ใกล้หมด', 'out' => 'หมดสต็อก'];
@endphp

@section('content')

<div class="manager-medicines-page">

    <header class="manager-medicines-topbar">
        <div>
            <h1>คลังยาและข้อมูลยา</h1>
            <p>จัดการรายการยา คลังยา และราคาขายภายในคลินิก</p>
        </div>
        <div class="manager-medicines-topbar-right">
            <span>{{ $thaiDate }}</span>
            <div class="manager-medicines-bell">🔔<b>3</b></div>
        </div>
    </header>

    <div class="manager-medicines-content">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- SUMMARY --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
                <div class="medicine-summary-card"><span>รายการยาทั้งหมด</span><strong>{{ $totalMedicines }}</strong></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="medicine-summary-card"><span>ปกติ</span><strong class="summary-normal">{{ $normalCount }}</strong></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="medicine-summary-card"><span>ใกล้หมด</span><strong class="summary-low">{{ $lowCount }}</strong></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="medicine-summary-card"><span>หมดสต็อก</span><strong class="summary-out">{{ $outCount }}</strong></div>
            </div>
        </div>

        {{-- TABLE --}}
        <section class="manager-medicine-card">
            <div class="manager-medicine-card-header">
                <h2>รายการยาทั้งหมด</h2>

                <div class="medicine-toolbar">
                    <form method="GET" action="{{ route('vetcare.manager.medicines') }}" class="medicine-search">
                        <span>🔍</span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="ค้นหาชื่อยา / รหัสยา...">
                    </form>

                    <a href="{{ route('vetcare.manager.medicines', ['panel' => 'create']) }}#medicine-modal" class="add-medicine-btn">
                        + เพิ่มยาใหม่
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table manager-medicine-table">
                    <thead>
                        <tr>
                            <th>รหัสยา</th>
                            <th>ชื่อยา</th>
                            <th>หน่วย</th>
                            <th>ราคาต้นทุน</th>
                            <th>ราคาขาย / หน่วย</th>
                            <th>คงเหลือ</th>
                            <th>สถานะ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($medicines as $medicine)
                            <tr>
                                <td class="medicine-code">{{ $medicine->medicine_id }}</td>
                                <td><strong class="medicine-name">{{ $medicine->medicine_name }}</strong></td>
                                <td>{{ $medicine->unit }}</td>
                                <td>฿{{ number_format($medicine->cost_price ?? 0, 2) }}</td>
                                <td><strong class="medicine-sale-price">฿{{ number_format($medicine->selling_price ?? 0, 2) }}</strong></td>
                                <td>
                                    <strong class="medicine-stock stock-{{ $medicine->stock_status }}">{{ $medicine->stock_quantity }}</strong>
                                    <small class="text-muted">/ ขั้นต่ำ {{ $medicine->minimum_stock ?? 0 }}</small>
                                </td>
                                <td>
                                    <span class="medicine-status status-{{ $medicine->stock_status }}">
                                        {{ $statusLabels[$medicine->stock_status] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="medicine-actions">
                                        <a href="{{ route('vetcare.manager.medicines', ['panel' => 'edit', 'id' => $medicine->medicine_id, 'search' => $search]) }}#medicine-modal" class="medicine-edit-btn">แก้ไข</a>
                                        <a href="{{ route('vetcare.manager.medicines', ['panel' => 'delete', 'id' => $medicine->medicine_id, 'search' => $search]) }}#medicine-modal" class="medicine-delete-btn">ลบ</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="medicine-empty">ไม่พบรายการยา</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="medicine-table-footer">
                แสดง {{ $medicines->count() }} จาก {{ $totalMedicines }} รายการ
            </div>
        </section>
    </div>


    {{-- ================= CREATE ================= --}}
    @if ($panel === 'create')
        <div class="manager-medicine-modal-overlay" id="medicine-modal">
            <div class="manager-medicine-modal">
                <form method="POST" action="{{ route('vetcare.manager.medicines.store') }}" style="display: contents">
                    @csrf

                    <div class="medicine-modal-header">
                        <h2>เพิ่มยาใหม่</h2>
                        <a href="{{ route('vetcare.manager.medicines') }}" class="medicine-modal-close">×</a>
                    </div>

                    <div class="medicine-modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">ชื่อยา</label>
                            <input type="text" name="medicine_name" class="form-control" value="{{ old('medicine_name') }}" placeholder="กรอกชื่อยา" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หน่วย</label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit') }}" placeholder="เช่น เม็ด / ml / ขวด" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ราคาต้นทุน (บาท)</label>
                            <input type="number" name="cost_price" class="form-control" min="0" step="0.01" value="{{ old('cost_price') }}" placeholder="0.00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ราคาขาย / หน่วย (บาท)</label>
                            <input type="number" name="selling_price" class="form-control" min="0" step="0.01" value="{{ old('selling_price') }}" placeholder="0.00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">จำนวนคงเหลือ</label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" step="1" value="{{ old('stock_quantity') }}" placeholder="0" required>
                        </div>
                        <div>
                            <label class="form-label">จุดเตือนสต็อกต่ำ (จำนวนขั้นต่ำ)</label>
                            <input type="number" name="minimum_stock" class="form-control" min="0" step="1" value="{{ old('minimum_stock', 10) }}" placeholder="10" required>
                        </div>
                    </div>

                    <div class="medicine-modal-footer">
                        <a href="{{ route('vetcare.manager.medicines') }}" class="medicine-modal-cancel">ยกเลิก</a>
                        <button type="submit" class="medicine-modal-save">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    {{-- ================= EDIT ================= --}}
    @if ($panel === 'edit' && $selectedMedicine)
        <div class="manager-medicine-modal-overlay" id="medicine-modal">
            <div class="manager-medicine-modal">
                <form method="POST" action="{{ route('vetcare.manager.medicines.update', $selectedMedicine->medicine_id) }}" style="display: contents">
                    @csrf
                    @method('PUT')

                    <div class="medicine-modal-header">
                        <h2>แก้ไขข้อมูลยา <small class="text-muted">{{ $selectedMedicine->medicine_id }}</small></h2>
                        <a href="{{ route('vetcare.manager.medicines') }}" class="medicine-modal-close">×</a>
                    </div>

                    <div class="medicine-modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">ชื่อยา</label>
                            <input type="text" name="medicine_name" class="form-control" value="{{ old('medicine_name', $selectedMedicine->medicine_name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หน่วย</label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', $selectedMedicine->unit) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ราคาต้นทุน (บาท)</label>
                            <input type="number" name="cost_price" class="form-control" min="0" step="0.01" value="{{ old('cost_price', $selectedMedicine->cost_price) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ราคาขาย / หน่วย (บาท)</label>
                            <input type="number" name="selling_price" class="form-control" min="0" step="0.01" value="{{ old('selling_price', $selectedMedicine->selling_price) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">จำนวนคงเหลือ</label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" step="1" value="{{ old('stock_quantity', $selectedMedicine->stock_quantity) }}" required>
                        </div>
                        <div>
                            <label class="form-label">จุดเตือนสต็อกต่ำ (จำนวนขั้นต่ำ)</label>
                            <input type="number" name="minimum_stock" class="form-control" min="0" step="1" value="{{ old('minimum_stock', $selectedMedicine->minimum_stock) }}" required>
                        </div>
                    </div>

                    <div class="medicine-modal-footer">
                        <a href="{{ route('vetcare.manager.medicines') }}" class="medicine-modal-cancel">ยกเลิก</a>
                        <button type="submit" class="medicine-modal-save">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    {{-- ================= DELETE ================= --}}
    @if ($panel === 'delete' && $selectedMedicine)
        <div class="manager-medicine-modal-overlay" id="medicine-modal">
            <div class="manager-medicine-modal small-medicine-modal">
                <form method="POST" action="{{ route('vetcare.manager.medicines.destroy', $selectedMedicine->medicine_id) }}" style="display: contents">
                    @csrf
                    @method('DELETE')

                    <div class="medicine-modal-header">
                        <h2>ยืนยันการลบยา</h2>
                        <a href="{{ route('vetcare.manager.medicines') }}" class="medicine-modal-close">×</a>
                    </div>

                    <div class="medicine-delete-body">
                        <div class="medicine-delete-icon">⚠️</div>
                        <p>ต้องการลบ <strong>{{ $selectedMedicine->medicine_name }}</strong> ใช่หรือไม่?</p>
                        <small>
                            การดำเนินการนี้ไม่สามารถย้อนกลับได้
                            (หากยามีประวัติการใช้งาน จะถูกปิดการใช้งานแทนการลบถาวร)
                        </small>
                    </div>

                    <div class="medicine-modal-footer">
                        <a href="{{ route('vetcare.manager.medicines') }}" class="medicine-modal-cancel">ยกเลิก</a>
                        <button type="submit" class="medicine-delete-confirm">ลบยา</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

@endsection