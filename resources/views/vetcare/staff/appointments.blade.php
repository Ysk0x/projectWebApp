@extends('vetcare.layouts.staff')

@section('title', 'Staff Appointments')
@section('page-name', 'Appointments')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/appointments.css') }}?v=3">
@endpush

@php
    $today = now('Asia/Bangkok');

    // ป้ายสถานะที่ใช้แสดง/กรอง (waiting/serving คำนวณจากข้อมูลจริง)
    $statusLabels = [
        'all'       => 'ทุกสถานะ',
        'confirmed' => 'นัดหมาย',
        'waiting'   => 'รอรับบริการ',
        'serving'   => 'รอชำระเงิน',
        'done'      => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
    ];

    // ค่าที่ DB เก็บได้จริง (ใช้ใน dropdown แก้ไข)
    $editStatusLabels = ['scheduled' => 'นัดหมาย', 'completed' => 'เสร็จสิ้น', 'cancelled' => 'ยกเลิก'];

    $thaiMonths = [1=>'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];

    // ปฏิทิน
    $monthParam = (is_string($monthInput) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthInput)) ? $monthInput : $today->format('Y-m');
    $monthStart = \Carbon\Carbon::create((int) substr($monthParam, 0, 4), (int) substr($monthParam, 5, 2), 1);
    $monthTitle = $thaiMonths[$monthStart->month] . ' ' . ($monthStart->year + 543);
    $leadingDays = $monthStart->dayOfWeek;
    $totalCells = (int) ceil(($leadingDays + $monthStart->daysInMonth) / 7) * 7;

    $isEdit = $panel === 'edit';
    $showForm = $panel === 'create' || ($isEdit && $editing);
    $val = fn ($key, $default = '') => old($key, $isEdit ? ($editing[$key] ?? $default) : $default);
@endphp

@section('content')

<div class="appt-page">

    <header class="appt-topbar">
        <h1>ตารางนัดหมาย</h1>
        <div class="appt-topbar-right">
            <span>{{ $thaiDate }}</span>
            <span class="appt-bell">🔔<b>3</b></span>
        </div>
    </header>

    <div class="appt-content">

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="appt-toolbar">
            <nav class="appt-tabs">
                <a href="{{ route('vetcare.staff.appointments') }}" class="appt-tab {{ $view === 'list' ? 'active' : '' }}">▤ รายการ</a>
                <a href="{{ route('vetcare.staff.appointments', ['view' => 'calendar']) }}" class="appt-tab {{ $view === 'calendar' ? 'active' : '' }}">📅 ปฏิทิน</a>
            </nav>

            <a href="{{ route('vetcare.staff.appointments', ['view' => $view, 'panel' => 'create']) }}#appointment-form" class="appt-create-btn">+ สร้างนัดหมาย</a>
        </div>


        {{-- ================= CREATE / EDIT FORM ================= --}}
        @if ($showForm)
            <section class="appt-card appt-form-panel" id="appointment-form">
                <div class="appt-card-header">
                    <h2>{{ $isEdit ? 'แก้ไขนัดหมาย ' . $editing['id'] : 'สร้างนัดหมายใหม่' }}</h2>
                    <a href="{{ route('vetcare.staff.appointments') }}" class="appt-small-btn">ปิด</a>
                </div>

                <form method="POST"
                      action="{{ $isEdit ? route('vetcare.staff.appointments.update', $editing['id']) : route('vetcare.staff.appointments.store') }}">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">สัตว์เลี้ยง (เจ้าของ)</label>
                            <select name="pet_id" class="form-select" required>
                                <option value="">-- เลือกสัตว์เลี้ยง --</option>
                                @foreach ($pets as $pet)
                                    <option value="{{ $pet->pet_id }}" @selected($val('pet_id') === $pet->pet_id)>
                                        {{ $pet->pet_name }} ({{ trim($pet->first_name . ' ' . $pet->last_name) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">ยังไม่มีสัตว์เลี้ยง? เพิ่มได้ที่หน้า <a href="{{ route('vetcare.staff.owners') }}">เจ้าของสัตว์</a></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">พนักงาน / สัตวแพทย์</label>
                            <select name="staff_id" class="form-select" required>
                                <option value="">-- เลือกพนักงาน --</option>
                                @foreach ($staffList as $s)
                                    <option value="{{ $s->user_id }}" @selected($val('staff_id') === $s->user_id)>{{ $s->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">วันที่นัดหมาย</label>
                            <input type="date" name="appointment_date" class="form-control" value="{{ old('appointment_date', $isEdit ? $editing['date'] : ($selectedDate ?: $today->format('Y-m-d'))) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">เวลา</label>
                            <input type="time" name="appointment_time" class="form-control" value="{{ old('appointment_time', $isEdit ? $editing['time'] : '09:00') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ประเภทบริการ</label>
                            <select name="service_type" class="form-select" required>
                                @php $currentService = old('service_type', $isEdit ? $editing['service'] : ''); @endphp
                                @foreach ($services as $service)
                                    <option value="{{ $service }}" @selected($currentService === $service)>{{ $service }}</option>
                                @endforeach
                                @if ($currentService && ! in_array($currentService, $services, true))
                                    <option value="{{ $currentService }}" selected>{{ $currentService }}</option>
                                @endif
                            </select>
                        </div>

                        @if ($isEdit)
                            <div class="col-md-6">
                                <label class="form-label">สถานะ</label>
                                <select name="status" class="form-select">
                                    @foreach ($editStatusLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(old('status', $editing['raw_status']) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label">หมายเหตุ</label>
                            <textarea name="notes" class="form-control" rows="2" maxlength="500">{{ old('notes', $isEdit ? $editing['notes'] : '') }}</textarea>
                        </div>
                    </div>

                    <div class="appt-form-actions">
                        <a href="{{ route('vetcare.staff.appointments') }}" class="btn btn-outline-secondary btn-sm">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary btn-sm">{{ $isEdit ? 'บันทึกการแก้ไข' : 'บันทึกนัดหมาย' }}</button>
                    </div>
                </form>
            </section>
        @endif


        {{-- ================= CANCEL CONFIRM ================= --}}
        @if ($panel === 'cancel' && $editing)
            <section class="appt-card appt-confirm" id="appointment-form">
                <h2>ยืนยันการยกเลิกนัดหมาย</h2>
                <p>{{ $editing['id'] }} — {{ $editing['pet'] }} ({{ $editing['owner'] }})</p>

                <form method="POST" action="{{ route('vetcare.staff.appointments.cancel', $editing['id']) }}">
                    @csrf
                    @method('PUT')
                    <a href="{{ route('vetcare.staff.appointments') }}" class="btn btn-outline-secondary btn-sm">กลับ</a>
                    <button type="submit" class="btn btn-danger btn-sm">ยืนยันยกเลิก</button>
                </form>
            </section>
        @endif


        @if ($view === 'list')

            <section class="appt-card">
                <div class="appt-card-header appt-list-header">
                    <h2>
                        {{ $selectedDate ? 'รายการนัดหมายวันที่ ' . date('d/m/Y', strtotime($selectedDate)) : 'รายการนัดหมายทั้งหมด' }}
                    </h2>

                    <form method="GET" action="{{ route('vetcare.staff.appointments') }}" class="appt-filters">
                        <input type="hidden" name="view" value="list">
                        @if ($selectedDate)<input type="hidden" name="date" value="{{ $selectedDate }}">@endif

                        <div class="appt-search-wrap">
                            <span>⌕</span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="ค้นหาเจ้าของ / สัตว์...">
                        </div>

                        <select name="status">
                            @foreach ($statusLabels as $key => $label)
                                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="appt-filter-btn">ค้นหา</button>
                        <a href="{{ route('vetcare.staff.appointments') }}" class="appt-small-btn">ล้าง</a>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table appt-table">
                        <thead>
                            <tr>
                                <th>รหัส</th>
                                <th>วัน/เวลา</th>
                                <th>เจ้าของ</th>
                                <th>สัตว์เลี้ยง</th>
                                <th>ประเภทบริการ</th>
                                <th>พนักงาน</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($filtered as $item)
                                <tr>
                                    <td class="appt-id">{{ $item['id'] }}</td>
                                    <td class="appt-date-cell">
                                        <span>{{ date('d/m/Y', strtotime($item['date'])) }}</span>
                                        <strong>{{ $item['time'] }}</strong>
                                    </td>
                                    <td>{{ $item['owner'] }}</td>
                                    <td>{{ $item['pet'] }}</td>
                                    <td>{{ $item['service'] }}</td>
                                    <td>{{ $item['staff'] }}</td>
                                    <td>
                                        <span class="appt-status status-{{ $item['status'] }}">{{ $statusLabels[$item['status']] }}</span>
                                    </td>
                                    <td>
                                        <div class="appt-row-actions">
                                            @if ($item['raw_status'] === 'scheduled' && ! in_array($item['status'], ['serving'], true))
                                                <a class="appt-edit-btn" href="{{ route('vetcare.staff.treatments', ['appointment' => $item['id']]) }}">รักษา</a>
                                            @endif

                                            <a class="appt-edit-btn" href="{{ route('vetcare.staff.appointments', ['panel' => 'edit', 'id' => $item['id']]) }}#appointment-form">แก้ไข</a>

                                            @if ($item['raw_status'] === 'scheduled')
                                                <a class="appt-cancel-btn" href="{{ route('vetcare.staff.appointments', ['panel' => 'cancel', 'id' => $item['id']]) }}#appointment-form">ยกเลิก</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="appt-no-data">ไม่พบข้อมูลนัดหมาย</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="appt-table-footer">แสดง {{ count($filtered) }} รายการ</div>
            </section>

        @else

            {{-- ================= CALENDAR ================= --}}
            <section class="appt-card appt-calendar-panel">
                <div class="appt-card-header appt-calendar-header">
                    <h2>ปฏิทินนัดหมาย — {{ $monthTitle }}</h2>

                    <div class="appt-calendar-nav">
                        <a href="{{ route('vetcare.staff.appointments', ['view' => 'calendar', 'month' => $monthStart->copy()->subMonth()->format('Y-m')]) }}">‹</a>
                        <a href="{{ route('vetcare.staff.appointments', ['view' => 'calendar', 'month' => $today->format('Y-m')]) }}">เดือนนี้</a>
                        <a href="{{ route('vetcare.staff.appointments', ['view' => 'calendar', 'month' => $monthStart->copy()->addMonth()->format('Y-m')]) }}">›</a>
                    </div>
                </div>

                <div class="appt-calendar-grid">
                    @foreach (['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'] as $dayLabel)
                        <div class="appt-weekday">{{ $dayLabel }}</div>
                    @endforeach

                    @for ($i = 0; $i < $totalCells; $i++)
                        @php
                            $dayNumber = $i - $leadingDays + 1;
                            $validDay = $dayNumber >= 1 && $dayNumber <= $monthStart->daysInMonth;
                            $cellDate = $validDay ? $monthStart->copy()->day($dayNumber)->format('Y-m-d') : null;
                            $hasAppointment = $cellDate && in_array($cellDate, $appointmentDates, true);
                            $isToday = $cellDate === $today->format('Y-m-d');
                        @endphp

                        <div class="appt-calendar-cell">
                            @if ($validDay)
                                <a href="{{ route('vetcare.staff.appointments', ['view' => 'list', 'date' => $cellDate]) }}"
                                   class="appt-day-number {{ $isToday ? 'selected' : '' }}">
                                    {{ $dayNumber }}
                                    @if ($hasAppointment)<span class="appt-dot"></span>@endif
                                </a>
                            @endif
                        </div>
                    @endfor
                </div>

                <p class="appt-calendar-help">จุดสีเขียว = มีนัดหมาย • กดวันที่เพื่อดูรายการ</p>
            </section>

        @endif

    </div>
</div>

@endsection