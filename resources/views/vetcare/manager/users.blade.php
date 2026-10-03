@extends('vetcare.layouts.manager')

@section('title', 'ผู้ใช้งานและสิทธิ์ | VetCare')
@section('page-name', 'Users')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/vetcare/manager-users.css') }}?v=1"
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
       MOCK USERS
    ========================================= */

    $users = [

        [
            'id' => 'USR-001',
            'username' => 'admin',
            'name' => 'สมชาย มีสุข',
            'role' => 'manager',
            'status' => 'active',
            'created' => '01/01/2569',
        ],

        [
            'id' => 'USR-002',
            'username' => 'staff01',
            'name' => 'วิภา รักดี',
            'role' => 'staff',
            'status' => 'active',
            'created' => '15/03/2569',
        ],

        [
            'id' => 'USR-003',
            'username' => 'staff02',
            'name' => 'ประยุทธ์ สวยงาม',
            'role' => 'staff',
            'status' => 'active',
            'created' => '20/03/2569',
        ],

        [
            'id' => 'USR-004',
            'username' => 'staff03',
            'name' => 'สุดา มณี',
            'role' => 'staff',
            'status' => 'inactive',
            'created' => '01/04/2569',
        ],

        [
            'id' => 'USR-005',
            'username' => 'staff04',
            'name' => 'บุญมี แก้วดี',
            'role' => 'staff',
            'status' => 'active',
            'created' => '10/05/2569',
        ],

        [
            'id' => 'USR-006',
            'username' => 'manager2',
            'name' => 'ณัฐพล เจริญดี',
            'role' => 'manager',
            'status' => 'active',
            'created' => '01/06/2569',
        ],

    ];


    /* =========================================
       SUMMARY
    ========================================= */

    $totalUsers = count($users);

    $managerCount = count(
        array_filter(
            $users,
            fn ($user) =>
                $user['role'] === 'manager'
        )
    );

    $staffCount = count(
        array_filter(
            $users,
            fn ($user) =>
                $user['role'] === 'staff'
        )
    );

    $inactiveCount = count(
        array_filter(
            $users,
            fn ($user) =>
                $user['status'] === 'inactive'
        )
    );


    /* =========================================
       SEARCH
    ========================================= */

    $search = trim(
        (string) request('search', '')
    );

    $filteredUsers = array_values(
        array_filter(
            $users,
            function ($user) use ($search) {

                if ($search === '') {
                    return true;
                }

                $text =
                    $user['id'] .
                    ' ' .
                    $user['username'] .
                    ' ' .
                    $user['name'];

                return mb_stripos(
                    $text,
                    $search
                ) !== false;

            }
        )
    );


    /* =========================================
       PANEL
    ========================================= */

    $panel = request('panel');

    $selectedUser = null;

    if (
        in_array(
            $panel,
            ['edit', 'reset', 'delete'],
            true
        )
    ) {

        foreach ($users as $user) {

            if (
                $user['id']
                === request('id')
            ) {

                $selectedUser = $user;

                break;

            }

        }

    }

@endphp


@section('content')

<div class="manager-users-page">


    {{-- =====================================
         HEADER
    ====================================== --}}

    <header class="manager-users-topbar">

        <div>

            <h1>
                จัดการบุคลากรและสิทธิ์
            </h1>

            <p>
                จัดการบัญชีผู้ใช้งานและบทบาทภายในคลินิก
            </p>

        </div>


        <div class="manager-users-topbar-right">

            <span>
                {{ $thaiDate }}
            </span>

            <div class="manager-users-bell">

                🔔

                <b>
                    3
                </b>

            </div>

        </div>

    </header>



    <div class="manager-users-content">


        {{-- =====================================
             SUMMARY
        ====================================== --}}

        <div class="row g-3 mb-3">


            <div class="col-6 col-xl-3">

                <div class="user-summary-card">

                    <span>
                        ผู้ใช้งานทั้งหมด
                    </span>

                    <strong>
                        {{ $totalUsers }}
                    </strong>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="user-summary-card">

                    <span>
                        ผู้จัดการ
                    </span>

                    <strong class="manager-count">
                        {{ $managerCount }}
                    </strong>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="user-summary-card">

                    <span>
                        พนักงาน
                    </span>

                    <strong class="staff-count">
                        {{ $staffCount }}
                    </strong>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="user-summary-card">

                    <span>
                        ไม่ใช้งาน
                    </span>

                    <strong class="inactive-count">
                        {{ $inactiveCount }}
                    </strong>

                </div>

            </div>


        </div>



        {{-- =====================================
             USER TABLE CARD
        ====================================== --}}

        <section class="manager-users-card">


            {{-- HEADER --}}

            <div class="manager-users-card-header">


                <h2>
                    รายชื่อผู้ใช้งาน
                </h2>


                <div class="users-toolbar">


                    {{-- SEARCH --}}

                    <form
                        method="GET"
                        action="{{ route(
                            'vetcare.manager.users'
                        ) }}"
                        class="users-search"
                    >

                        <span>
                            🔍
                        </span>

                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="ค้นหาชื่อ / username..."
                        >

                    </form>



                    {{-- ADD USER --}}

                    <a
                        href="{{ route(
                            'vetcare.manager.users',
                            [
                                'panel' => 'create'
                            ]
                        ) }}#user-modal"
                        class="add-user-btn"
                    >

                        + เพิ่มผู้ใช้งาน

                    </a>


                </div>


            </div>



            {{-- =====================================
                 TABLE
            ====================================== --}}

            <div class="table-responsive">

                <table class="table manager-users-table">


                    <thead>

                        <tr>

                            <th>
                                รหัสผู้ใช้
                            </th>

                            <th>
                                Username
                            </th>

                            <th>
                                ชื่อพนักงาน
                            </th>

                            <th>
                                บทบาท
                            </th>

                            <th>
                                สถานะ
                            </th>

                            <th>
                                วันที่สร้าง
                            </th>

                            <th>
                                การจัดการ
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        @forelse (
                            $filteredUsers
                            as $user
                        )


                            <tr>


                                <td class="user-code">

                                    {{ $user['id'] }}

                                </td>


                                <td>

                                    <strong class="username-text">

                                        {{ $user['username'] }}

                                    </strong>

                                </td>


                                <td>

                                    {{ $user['name'] }}

                                </td>


                                {{-- ROLE --}}

                                <td>


                                    @if (
                                        $user['role']
                                        === 'manager'
                                    )

                                        <span class="role-badge role-manager">

                                            Manager / Admin

                                        </span>

                                    @else

                                        <span class="role-badge role-staff">

                                            Staff

                                        </span>

                                    @endif


                                </td>



                                {{-- STATUS --}}

                                <td>


                                    @if (
                                        $user['status']
                                        === 'active'
                                    )

                                        <span class="user-status status-active">

                                            ใช้งาน

                                        </span>

                                    @else

                                        <span class="user-status status-inactive">

                                            ระงับ

                                        </span>

                                    @endif


                                </td>


                                <td>

                                    {{ $user['created'] }}

                                </td>



                                {{-- ACTION --}}

                                <td>


                                    <div class="user-action-buttons">


                                        {{-- EDIT --}}

                                        <a
                                            href="{{ route(
                                                'vetcare.manager.users',
                                                [
                                                    'panel' => 'edit',
                                                    'id' => $user['id']
                                                ]
                                            ) }}#user-modal"
                                            class="user-edit-btn"
                                        >

                                            แก้ไข

                                        </a>



                                        {{-- RESET PASSWORD --}}

                                        <a
                                            href="{{ route(
                                                'vetcare.manager.users',
                                                [
                                                    'panel' => 'reset',
                                                    'id' => $user['id']
                                                ]
                                            ) }}#user-modal"
                                            class="user-reset-btn"
                                        >

                                            รีเซ็ต

                                        </a>



                                        {{-- DELETE --}}

                                        <a
                                            href="{{ route(
                                                'vetcare.manager.users',
                                                [
                                                    'panel' => 'delete',
                                                    'id' => $user['id']
                                                ]
                                            ) }}#user-modal"
                                            class="user-delete-btn"
                                        >

                                            ลบ

                                        </a>


                                    </div>


                                </td>


                            </tr>


                        @empty


                            <tr>

                                <td
                                    colspan="7"
                                    class="users-empty"
                                >

                                    ไม่พบผู้ใช้งาน

                                </td>

                            </tr>


                        @endforelse


                    </tbody>


                </table>

            </div>


            <div class="users-table-footer">

                แสดง

                {{ count($filteredUsers) }}

                จาก

                {{ $totalUsers }}

                ผู้ใช้งาน

            </div>


        </section>


    </div>



    {{-- =================================================
         CREATE USER MODAL
    ================================================== --}}

    @if ($panel === 'create')


        <div
            class="manager-modal-overlay"
            id="user-modal"
        >


            <div class="manager-user-modal">


                <div class="manager-modal-header">


                    <h2>
                        เพิ่มผู้ใช้งานใหม่
                    </h2>


                    <a
                        href="{{ route(
                            'vetcare.manager.users'
                        ) }}"
                        class="modal-close"
                    >

                        ×

                    </a>


                </div>



                <div class="manager-modal-body">


                    <div class="mb-3">

                        <label class="form-label">

                            ชื่อผู้ใช้ (Username)

                        </label>


                        <input
                            type="text"
                            class="form-control"
                            placeholder="กรอก Username"
                        >

                    </div>



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

                            รหัสผ่าน (Password)

                        </label>


                        <input
                            type="password"
                            class="form-control"
                            placeholder="กรอกรหัสผ่าน"
                        >

                    </div>



                    <div class="mb-3">

                        <label class="form-label">

                            บทบาท (Role)

                        </label>


                        <select class="form-select">

                            <option value="staff">

                                พนักงาน (Staff)

                            </option>

                            <option value="manager">

                                ผู้จัดการ (Manager)

                            </option>

                        </select>

                    </div>



                    <div>

                        <label class="form-label">

                            สถานะ

                        </label>


                        <select class="form-select">

                            <option value="active">

                                ใช้งาน (Active)

                            </option>

                            <option value="inactive">

                                ระงับ (Inactive)

                            </option>

                        </select>

                    </div>


                </div>



                <div class="manager-modal-footer">


                    <a
                        href="{{ route(
                            'vetcare.manager.users'
                        ) }}"
                        class="modal-cancel-btn"
                    >

                        ยกเลิก

                    </a>


                    <button
                        type="button"
                        class="modal-save-btn"
                    >

                        บันทึก

                    </button>


                </div>


            </div>


        </div>


    @endif



    {{-- =================================================
         EDIT USER
    ================================================== --}}

    @if (
        $panel === 'edit'
        && $selectedUser
    )


        <div
            class="manager-modal-overlay"
            id="user-modal"
        >


            <div class="manager-user-modal">


                <div class="manager-modal-header">

                    <h2>
                        แก้ไขผู้ใช้งาน
                    </h2>

                    <a
                        href="{{ route(
                            'vetcare.manager.users'
                        ) }}"
                        class="modal-close"
                    >
                        ×
                    </a>

                </div>



                <div class="manager-modal-body">


                    <div class="mb-3">

                        <label class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $selectedUser['username'] }}"
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            ชื่อ-นามสกุล
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $selectedUser['name'] }}"
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            บทบาท
                        </label>

                        <select class="form-select">

                            <option
                                {{ $selectedUser['role'] === 'staff'
                                    ? 'selected'
                                    : ''
                                }}
                            >
                                พนักงาน (Staff)
                            </option>

                            <option
                                {{ $selectedUser['role'] === 'manager'
                                    ? 'selected'
                                    : ''
                                }}
                            >
                                ผู้จัดการ (Manager)
                            </option>

                        </select>

                    </div>


                    <div>

                        <label class="form-label">
                            สถานะ
                        </label>

                        <select class="form-select">

                            <option
                                {{ $selectedUser['status'] === 'active'
                                    ? 'selected'
                                    : ''
                                }}
                            >
                                ใช้งาน (Active)
                            </option>

                            <option
                                {{ $selectedUser['status'] === 'inactive'
                                    ? 'selected'
                                    : ''
                                }}
                            >
                                ระงับ (Inactive)
                            </option>

                        </select>

                    </div>


                </div>



                <div class="manager-modal-footer">

                    <a
                        href="{{ route(
                            'vetcare.manager.users'
                        ) }}"
                        class="modal-cancel-btn"
                    >
                        ยกเลิก
                    </a>

                    <button
                        type="button"
                        class="modal-save-btn"
                    >
                        บันทึกการแก้ไข
                    </button>

                </div>


            </div>


        </div>


    @endif



    {{-- =================================================
         RESET PASSWORD
    ================================================== --}}

    @if (
        $panel === 'reset'
        && $selectedUser
    )


        <div
            class="manager-modal-overlay"
            id="user-modal"
        >


            <div class="manager-user-modal small-modal">


                <div class="manager-modal-header">

                    <h2>
                        รีเซ็ตรหัสผ่าน
                    </h2>

                    <a
                        href="{{ route(
                            'vetcare.manager.users'
                        ) }}"
                        class="modal-close"
                    >
                        ×
                    </a>

                </div>


                <div class="manager-modal-body">


                    <p class="modal-description">

                        กำหนดรหัสผ่านใหม่สำหรับ

                        <strong>
                            {{ $selectedUser['username'] }}
                        </strong>

                    </p>


                    <label class="form-label">

                        รหัสผ่านใหม่

                    </label>


                    <input
                        type="password"
                        class="form-control"
                        placeholder="กรอกรหัสผ่านใหม่"
                    >


                </div>


                <div class="manager-modal-footer">

                    <a
                        href="{{ route(
                            'vetcare.manager.users'
                        ) }}"
                        class="modal-cancel-btn"
                    >
                        ยกเลิก
                    </a>

                    <button
                        type="button"
                        class="modal-reset-confirm"
                    >
                        รีเซ็ตรหัสผ่าน
                    </button>

                </div>


            </div>


        </div>


    @endif



    {{-- =================================================
         DELETE USER
    ================================================== --}}

    @if (
        $panel === 'delete'
        && $selectedUser
    )


        <div
            class="manager-modal-overlay"
            id="user-modal"
        >


            <div class="manager-user-modal small-modal">


                <div class="manager-modal-header">

                    <h2>
                        ยืนยันการลบผู้ใช้งาน
                    </h2>

                    <a
                        href="{{ route(
                            'vetcare.manager.users'
                        ) }}"
                        class="modal-close"
                    >
                        ×
                    </a>

                </div>


                <div class="manager-modal-body">


                    <div class="delete-warning-icon">
                        ⚠️
                    </div>


                    <p class="delete-message">

                        คุณต้องการลบบัญชี

                        <strong>
                            {{ $selectedUser['username'] }}
                        </strong>

                        ใช่หรือไม่?

                    </p>


                    <small class="delete-warning-text">

                        การดำเนินการนี้จะไม่สามารถย้อนกลับได้

                    </small>


                </div>


                <div class="manager-modal-footer">

                    <a
                        href="{{ route(
                            'vetcare.manager.users'
                        ) }}"
                        class="modal-cancel-btn"
                    >
                        ยกเลิก
                    </a>

                    <button
                        type="button"
                        class="modal-delete-confirm"
                    >
                        ลบผู้ใช้งาน
                    </button>

                </div>


            </div>


        </div>


    @endif


</div>

@endsection