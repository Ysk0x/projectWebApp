
@extends('vetcare.layouts.staff')

@section('title', 'Staff Appointments')
@section('page-name', 'Appointments')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vetcare/appointments.css') }}?v=3">
@endpush

@php
    // ===================================
    // 1. MOCK DATA
    // ===================================

    $today = now('Asia/Bangkok');

    $appointments = [
        [
            'id' => 'APT-001',
            'date' => $today->format('Y-m-d'),
            'time' => '09:00',
            'owner' => 'วิชัย สมใจ',
            'pet' => 'มะม่วง',
            'service' => 'ตรวจทั่วไป',
            'staff' => 'วิภา รักดี',
            'status' => 'done',
        ],
        [
            'id' => 'APT-002',
            'date' => $today->format('Y-m-d'),
            'time' => '09:30',
            'owner' => 'พิมพ์ใจ ดีงาม',
            'pet' => 'มุก',
            'service' => 'ฉีดวัคซีน',
            'staff' => 'วิภา รักดี',
            'status' => 'done',
        ],
        [
            'id' => 'APT-003',
            'date' => $today->format('Y-m-d'),
            'time' => '10:00',
            'owner' => 'ประสิทธิ์ สุขดี',
            'pet' => 'แจ็ค',
            'service' => 'รักษาโรคผิวหนัง',
            'staff' => 'วิภา รักดี',
            'status' => 'serving',
        ],
        [
            'id' => 'APT-004',
            'date' => $today->format('Y-m-d'),
            'time' => '11:00',
            'owner' => 'สมศักดิ์ ใจดี',
            'pet' => 'บาสโก้',
            'service' => 'ตรวจฟัน',
            'staff' => 'ประยุทธ์ สวยงาม',
            'status' => 'waiting',
        ],
        [
            'id' => 'APT-005',
            'date' => $today->format('Y-m-d'),
            'time' => '13:00',
            'owner' => 'บุญมี รักสวย',
            'pet' => 'ดาว',
            'service' => 'ทำหมัน',
            'staff' => 'วิภา รักดี',
            'status' => 'waiting',
        ],
        [
            'id' => 'APT-006',
            'date' => $today->copy()->addDay()->format('Y-m-d'),
            'time' => '09:00',
            'owner' => 'สุดา มีแก้ว',
            'pet' => 'ลัคกี้',
            'service' => 'ติดตามอาการ',
            'staff' => 'ประยุทธ์ สวยงาม',
            'status' => 'confirmed',
        ],
    ];

    // ===================================
    // 2. DISPLAY SETTINGS
    // ===================================

    $statusLabels = [
        'all' => 'ทุกสถานะ',
        'confirmed' => 'นัดหมาย',
        'waiting' => 'รอรับบริการ',
        'serving' => 'กำลังรับบริการ',
        'done' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
    ];

    $thaiMonths = [
        1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม',
        'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน',
        'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];

    $thaiDays = [
        'อาทิตย์', 'จันทร์', 'อังคาร',
        'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'
    ];

    $todayThai =
        'วัน' . $thaiDays[$today->dayOfWeek] .
        'ที่ ' . $today->day . ' ' .
        $thaiMonths[$today->month] . ' ' .
        ($today->year + 543);

    // ===================================
    // 3. URL PARAMETERS
    // ===================================

    $view = request('view') === 'calendar'
        ? 'calendar'
        : 'list';

    $search = trim((string) request('search', ''));

    $status = request('status', 'all');

    if (!array_key_exists($status, $statusLabels)) {
        $status = 'all';
    }

    $selectedDate = request('date');

    if ($selectedDate) {
        $parsedDate = \DateTime::createFromFormat(
            '!Y-m-d',
            $selectedDate
        );

        if (
            !$parsedDate ||
            $parsedDate->format('Y-m-d') !== $selectedDate
        ) {
            $selectedDate = null;
        }
    }

    // ===================================
    // 4. FILTER MOCK DATA
    // ===================================

    $filtered = array_values(array_filter(
        $appointments,
        function ($item) use ($search, $status, $selectedDate) {

            $searchText =
                $item['id'] . ' ' .
                $item['owner'] . ' ' .
                $item['pet'] . ' ' .
                $item['service'];

            $matchSearch =
                $search === '' ||
                mb_stripos($searchText, $search) !== false;

            $matchStatus =
                $status === 'all' ||
                $item['status'] === $status;

            $matchDate =
                !$selectedDate ||
                $item['date'] === $selectedDate;

            return $matchSearch &&
                   $matchStatus &&
                   $matchDate;
        }
    ));

    // ===================================
    // 5. CREATE / EDIT / CANCEL UI
    // ===================================

    $panel = request('panel');
    $editing = null;

    if (in_array($panel, ['edit', 'cancel'], true)) {
        foreach ($appointments as $item) {
            if ($item['id'] === request('id')) {
                $editing = $item;
                break;
            }
        }
    }

    // ===================================
    // 6. CALENDAR
    // ===================================

    $monthInput = request(
        'month',
        $today->format('Y-m')
    );

    if (
        !is_string($monthInput) ||
        !preg_match(
            '/^\d{4}-(0[1-9]|1[0-2])$/',
            $monthInput
        )
    ) {
        $monthInput = $today->format('Y-m');
    }

    $monthStart = \Carbon\Carbon::create(
        (int) substr($monthInput, 0, 4),
        (int) substr($monthInput, 5, 2),
        1
    );

    $monthTitle =
        $thaiMonths[$monthStart->month] .
        ' ' .
        ($monthStart->year + 543);

    $leadingDays = $monthStart->dayOfWeek;

    $totalCells = (int) ceil(
        ($leadingDays + $monthStart->daysInMonth) / 7
    ) * 7;

    $appointmentDates = array_column(
        $appointments,
        'date'
    );
@endphp


@section('content')

<div class="appt-page">

    <!-- HEADER -->

    <header class="appt-topbar">

        <h1>ตารางนัดหมาย</h1>

        <div class="appt-topbar-right">

            <span></span>

            <span class="appt-bell">
                🔔
                <b>3</b>
            </span>

        </div>

    </header>


    <div class="appt-content">


        <div class="appt-toolbar">

            <nav class="appt-tabs">

                <a
                    href="{{ route('vetcare.staff.appointments') }}"
                    class="appt-tab {{ $view === 'list' ? 'active' : '' }}"
                >
                    ▤ รายการ
                </a>

                <a
                    href="{{ route('vetcare.staff.appointments', [
                        'view' => 'calendar'
                    ]) }}"
                    class="appt-tab {{ $view === 'calendar' ? 'active' : '' }}"
                >
                    📅 ปฏิทิน
                </a>

            </nav>


            <a
                href="{{ route('vetcare.staff.appointments', [
                    'view' => $view,
                    'panel' => 'create'
                ]) }}#appointment-form"
                class="appt-create-btn"
            >
                + สร้างนัดหมาย
            </a>

        </div>


        @if (
            $panel === 'create' ||
            ($panel === 'edit' && $editing)
        )

            @php
                $isEdit = $panel === 'edit';
            @endphp

            <section
                class="appt-card appt-form-panel"
                id="appointment-form"
            >

                <div class="appt-card-header">

                    <h2>
                        {{ $isEdit
                            ? 'แก้ไขนัดหมาย ' . $editing['id']
                            : 'สร้างนัดหมายใหม่'
                        }}
                    </h2>

                    <a
                        href="{{ route('vetcare.staff.appointments') }}"
                        class="appt-small-btn"
                    >
                        ปิด
                    </a>

                </div>


                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            ชื่อเจ้าของ
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $isEdit ? $editing['owner'] : '' }}"
                            placeholder="ชื่อ-นามสกุล"
                        >
                    </div>


                    <div class="col-md-6">
                        <label class="form-label">
                            ชื่อสัตว์เลี้ยง
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $isEdit ? $editing['pet'] : '' }}"
                            placeholder="ชื่อสัตว์เลี้ยง"
                        >
                    </div>


                    <div class="col-md-6">
                        <label class="form-label">
                            วันที่นัดหมาย
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            value="{{ $isEdit
                                ? $editing['date']
                                : $today->format('Y-m-d')
                            }}"
                        >
                    </div>


                    <div class="col-md-6">
                        <label class="form-label">
                            เวลา
                        </label>

                        <input
                            type="time"
                            class="form-control"
                            value="{{ $isEdit
                                ? $editing['time']
                                : '09:00'
                            }}"
                        >
                    </div>


                    <div class="col-md-6">
                        <label class="form-label">
                            ประเภทบริการ
                        </label>

                        <select class="form-select">

                            @foreach ([
                                'ตรวจทั่วไป',
                                'ฉีดวัคซีน',
                                'รักษาโรคผิวหนัง',
                                'ตรวจฟัน',
                                'ทำหมัน',
                                'ติดตามอาการ'
                            ] as $service)

                                <option
                                    {{ $isEdit &&
                                       $editing['service'] === $service
                                        ? 'selected'
                                        : ''
                                    }}
                                >
                                    {{ $service }}
                                </option>

                            @endforeach

                        </select>
                    </div>


                    <div class="col-md-6">
                        <label class="form-label">
                            พนักงาน
                        </label>

                        <select class="form-select">

                            <option>
                                วิภา รักดี
                            </option>

                            <option>
                                ประยุทธ์ สวยงาม
                            </option>

                        </select>
                    </div>


                    @if ($isEdit)

                        <div class="col-md-6">
                            <label class="form-label">
                                สถานะ
                            </label>

                            <select class="form-select">

                                @foreach ([
                                    'confirmed',
                                    'waiting',
                                    'serving',
                                    'done'
                                ] as $key)

                                    <option
                                        {{ $editing['status'] === $key
                                            ? 'selected'
                                            : ''
                                        }}
                                    >
                                        {{ $statusLabels[$key] }}
                                    </option>

                                @endforeach

                            </select>
                        </div>

                    @endif

                </div>


                <div class="appt-form-actions">

                    <a
                        href="{{ route('vetcare.staff.appointments') }}"
                        class="btn btn-outline-secondary btn-sm"
                    >
                        ยกเลิก
                    </a>

                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        disabled
                    >
                        บันทึก (UI เท่านั้น)
                    </button>

                </div>

            </section>

        @endif



        @if ($panel === 'cancel' && $editing)

            <section
                class="appt-card appt-confirm"
                id="appointment-form"
            >

                <h2>ยืนยันการยกเลิกนัดหมาย</h2>

                <p>
                    {{ $editing['id'] }} —
                    {{ $editing['pet'] }}
                    ({{ $editing['owner'] }})
                </p>

                <a
                    href="{{ route('vetcare.staff.appointments') }}"
                    class="btn btn-outline-secondary btn-sm"
                >
                    กลับ
                </a>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    disabled
                >
                    ยืนยันยกเลิก (UI เท่านั้น)
                </button>

            </section>

        @endif

        @if ($view === 'list')

            <section class="appt-card">

                <div class="appt-card-header appt-list-header">

                    <h2>
                        {{ $selectedDate
                            ? 'รายการนัดหมายวันที่ ' .
                              date('d/m/Y', strtotime($selectedDate))
                            : 'รายการนัดหมายทั้งหมด'
                        }}
                    </h2>


                    <!-- SEARCH -->

                    <form
                        method="GET"
                        action="{{ route('vetcare.staff.appointments') }}"
                        class="appt-filters"
                    >

                        <input
                            type="hidden"
                            name="view"
                            value="list"
                        >

                        @if ($selectedDate)
                            <input
                                type="hidden"
                                name="date"
                                value="{{ $selectedDate }}"
                            >
                        @endif


                        <div class="appt-search-wrap">

                            <span>⌕</span>

                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="ค้นหาเจ้าของ / สัตว์..."
                            >

                        </div>


                        <select name="status">

                            @foreach ($statusLabels as $key => $label)

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
                            class="appt-filter-btn"
                        >
                            ค้นหา
                        </button>


                        <a
                            href="{{ route('vetcare.staff.appointments') }}"
                            class="appt-small-btn"
                        >
                            ล้าง
                        </a>

                    </form>

                </div>


                <!-- TABLE -->

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

                                    <td class="appt-id">
                                        {{ $item['id'] }}
                                    </td>

                                    <td class="appt-date-cell">

                                        <span>
                                            {{ date(
                                                'd/m/Y',
                                                strtotime($item['date'])
                                            ) }}
                                        </span>

                                        <strong>
                                            {{ $item['time'] }}
                                        </strong>

                                    </td>

                                    <td>{{ $item['owner'] }}</td>

                                    <td>{{ $item['pet'] }}</td>

                                    <td>{{ $item['service'] }}</td>

                                    <td>{{ $item['staff'] }}</td>


                                    <td>
                                        <span
                                            class="appt-status status-{{ $item['status'] }}"
                                        >
                                            {{ $statusLabels[$item['status']] }}
                                        </span>
                                    </td>


                                    <td>

                                        <div class="appt-row-actions">

                                            <a
                                                class="appt-edit-btn"
                                                href="{{ route(
                                                    'vetcare.staff.appointments',
                                                    [
                                                        'panel' => 'edit',
                                                        'id' => $item['id']
                                                    ]
                                                ) }}#appointment-form"
                                            >
                                                แก้ไข
                                            </a>


                                            @if ($item['status'] !== 'done')

                                                <a
                                                    class="appt-cancel-btn"
                                                    href="{{ route(
                                                        'vetcare.staff.appointments',
                                                        [
                                                            'panel' => 'cancel',
                                                            'id' => $item['id']
                                                        ]
                                                    ) }}#appointment-form"
                                                >
                                                    ยกเลิก
                                                </a>

                                            @endif

                                        </div>

                                    </td>

                                </tr>


                            @empty

                                <tr>
                                    <td
                                        colspan="8"
                                        class="appt-no-data"
                                    >
                                        ไม่พบข้อมูลนัดหมาย
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                <div class="appt-table-footer">
                    แสดง {{ count($filtered) }} รายการ
                </div>

            </section>


        @else

            <!-- MONTHLY CALENDAR -->

            <section class="appt-card appt-calendar-panel">

                <div class="appt-card-header appt-calendar-header">

                    <h2>
                        ปฏิทินนัดหมาย — {{ $monthTitle }}
                    </h2>


                    <div class="appt-calendar-nav">

                        <a
                            href="{{ route(
                                'vetcare.staff.appointments',
                                [
                                    'view' => 'calendar',
                                    'month' => $monthStart
                                        ->copy()
                                        ->subMonth()
                                        ->format('Y-m')
                                ]
                            ) }}"
                        >
                            ‹
                        </a>


                        <a
                            href="{{ route(
                                'vetcare.staff.appointments',
                                [
                                    'view' => 'calendar',
                                    'month' => $today->format('Y-m')
                                ]
                            ) }}"
                        >
                            เดือนนี้
                        </a>


                        <a
                            href="{{ route(
                                'vetcare.staff.appointments',
                                [
                                    'view' => 'calendar',
                                    'month' => $monthStart
                                        ->copy()
                                        ->addMonth()
                                        ->format('Y-m')
                                ]
                            ) }}"
                        >
                            ›
                        </a>

                    </div>

                </div>


                <div class="appt-calendar-grid">

                    <!-- WEEK DAYS -->

                    @foreach ([
                        'อา', 'จ', 'อ', 'พ',
                        'พฤ', 'ศ', 'ส'
                    ] as $dayLabel)

                        <div class="appt-weekday">
                            {{ $dayLabel }}
                        </div>

                    @endforeach


                    <!-- MONTH DAYS -->

                    @for ($i = 0; $i < $totalCells; $i++)

                        @php
                            $dayNumber = $i - $leadingDays + 1;

                            $validDay =
                                $dayNumber >= 1 &&
                                $dayNumber <= $monthStart->daysInMonth;

                            $cellDate = $validDay
                                ? $monthStart
                                    ->copy()
                                    ->day($dayNumber)
                                    ->format('Y-m-d')
                                : null;

                            $hasAppointment =
                                $cellDate &&
                                in_array(
                                    $cellDate,
                                    $appointmentDates
                                );

                            $isToday =
                                $cellDate === $today->format('Y-m-d');
                        @endphp


                        <div class="appt-calendar-cell">

                            @if ($validDay)

                                <a
                                    href="{{ route(
                                        'vetcare.staff.appointments',
                                        [
                                            'view' => 'list',
                                            'date' => $cellDate
                                        ]
                                    ) }}"
                                    class="appt-day-number {{ $isToday
                                        ? 'selected'
                                        : ''
                                    }}"
                                >

                                    {{ $dayNumber }}


                                    @if ($hasAppointment)
                                        <span class="appt-dot"></span>
                                    @endif

                                </a>

                            @endif

                        </div>

                    @endfor

                </div>


                <p class="appt-calendar-help">
                    จุดสีเขียว = มีนัดหมาย • กดวันที่เพื่อดูรายการ
                </p>

            </section>

        @endif

    </div>

</div>

@endsection
