@extends('vetcare.layouts.manager')

@section('title', 'ผู้ใช้งานและสิทธิ์ | VetCare')
@section('page-name', 'Users')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/manager-users.css') }}?v=1">
@endpush

@php
    $today = now('Asia/Bangkok');
    $thaiMonths = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
    $thaiDays = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
    $thaiDate = 'วัน' . $thaiDays[$today->dayOfWeek] . 'ที่ ' . $today->day . ' ' . $thaiMonths[$today->month] . ' ' . ($today->year + 543);

    $roleLabels = ['manager' => 'Manager / Admin', 'staff' => 'Staff', 'vet' => 'สัตวแพทย์ (Vet)'];
    $roleClass  = ['manager' => 'role-manager', 'staff' => 'role-staff', 'vet' => 'role-staff'];
@endphp

@section('content')

<div class="manager-users-page">

    {{-- HEADER --}}
    <header class="manager-users-topbar">
        <div>
            <h1>จัดการบุคลากรและสิทธิ์</h1>
            <p>จัดการบัญชีผู้ใช้งานและบทบาทภายในคลินิก</p>
        </div>
        <div class="manager-users-topbar-right">
            <span>{{ $thaiDate }}</span>
            <div class="manager-users-bell">🔔<b>3</b></div>
        </div>
    </header>

    <div class="manager-users-content">

        {{-- FLASH --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- SUMMARY --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
                <div class="user-summary-card"><span>ผู้ใช้งานทั้งหมด</span><strong>{{ $totalUsers }}</strong></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="user-summary-card"><span>ผู้จัดการ</span><strong class="manager-count">{{ $managerCount }}</strong></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="user-summary-card"><span>พนักงาน</span><strong class="staff-count">{{ $staffCount }}</strong></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="user-summary-card"><span>ไม่ใช้งาน</span><strong class="inactive-count">{{ $inactiveCount }}</strong></div>
            </div>
        </div>

        {{-- TABLE --}}
        <section class="manager-users-card">
            <div class="manager-users-card-header">
                <h2>รายชื่อผู้ใช้งาน</h2>

                <div class="users-toolbar">
                    <form method="GET" action="{{ route('vetcare.manager.users') }}" class="users-search">
                        <span>🔍</span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="ค้นหาชื่อ / อีเมล...">
                    </form>

                    <a href="{{ route('vetcare.manager.users', ['panel' => 'create']) }}#user-modal" class="add-user-btn">
                        + เพิ่มผู้ใช้งาน
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table manager-users-table">
                    <thead>
                        <tr>
                            <th>รหัสผู้ใช้</th>
                            <th>อีเมล (Username)</th>
                            <th>ชื่อพนักงาน</th>
                            <th>บทบาท</th>
                            <th>สถานะ</th>
                            <th>วันที่สร้าง</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $created = $user->created_at ? \Carbon\Carbon::parse($user->created_at) : null;
                            @endphp
                            <tr>
                                <td class="user-code">{{ $user->user_id }}</td>
                                <td><strong class="username-text">{{ $user->email }}</strong></td>
                                <td>{{ $user->full_name }}</td>
                                <td>
                                    <span class="role-badge {{ $roleClass[$user->role] ?? 'role-staff' }}">
                                        {{ $roleLabels[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td>
                                    @if ($user->status === 'active')
                                        <span class="user-status status-active">ใช้งาน</span>
                                    @else
                                        <span class="user-status status-inactive">ระงับ</span>
                                    @endif
                                </td>
                                <td>{{ $created ? $created->format('d/m/') . ($created->year + 543) : '-' }}</td>
                                <td>
                                    <div class="user-action-buttons">
                                        <a href="{{ route('vetcare.manager.users', ['panel' => 'edit', 'id' => $user->user_id, 'search' => $search]) }}#user-modal" class="user-edit-btn">แก้ไข</a>
                                        <a href="{{ route('vetcare.manager.users', ['panel' => 'reset', 'id' => $user->user_id, 'search' => $search]) }}#user-modal" class="user-reset-btn">รีเซ็ต</a>
                                        <a href="{{ route('vetcare.manager.users', ['panel' => 'delete', 'id' => $user->user_id, 'search' => $search]) }}#user-modal" class="user-delete-btn">ลบ</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="users-empty">ไม่พบผู้ใช้งาน</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="users-table-footer">
                แสดง {{ $users->count() }} จาก {{ $totalUsers }} ผู้ใช้งาน
            </div>
        </section>
    </div>


    {{-- ================= CREATE ================= --}}
    @if ($panel === 'create')
        <div class="manager-modal-overlay" id="user-modal">
            <div class="manager-user-modal">
                <form method="POST" action="{{ route('vetcare.manager.users.store') }}" style="display: contents">
                    @csrf

                    <div class="manager-modal-header">
                        <h2>เพิ่มผู้ใช้งานใหม่</h2>
                        <a href="{{ route('vetcare.manager.users') }}" class="modal-close">×</a>
                    </div>

                    <div class="manager-modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">อีเมล (Username)</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="name@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ชื่อ-นามสกุล</label>
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" placeholder="กรอกชื่อ-นามสกุล" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รหัสผ่าน (Password)</label>
                            <input type="password" name="password" class="form-control" placeholder="อย่างน้อย 8 ตัวอักษร" required minlength="8">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">บทบาท (Role)</label>
                            <select name="role" class="form-select">
                                <option value="staff"   @selected(old('role', 'staff') === 'staff')>พนักงาน (Staff)</option>
                                <option value="vet"     @selected(old('role') === 'vet')>สัตวแพทย์ (Vet)</option>
                                <option value="manager" @selected(old('role') === 'manager')>ผู้จัดการ (Manager)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="active"   @selected(old('status', 'active') === 'active')>ใช้งาน (Active)</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>ระงับ (Inactive)</option>
                            </select>
                        </div>
                    </div>

                    <div class="manager-modal-footer">
                        <a href="{{ route('vetcare.manager.users') }}" class="modal-cancel-btn">ยกเลิก</a>
                        <button type="submit" class="modal-save-btn">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    {{-- ================= EDIT ================= --}}
    @if ($panel === 'edit' && $selectedUser)
        <div class="manager-modal-overlay" id="user-modal">
            <div class="manager-user-modal">
                <form method="POST" action="{{ route('vetcare.manager.users.update', $selectedUser->user_id) }}" style="display: contents">
                    @csrf
                    @method('PUT')

                    <div class="manager-modal-header">
                        <h2>แก้ไขผู้ใช้งาน <small class="text-muted">{{ $selectedUser->user_id }}</small></h2>
                        <a href="{{ route('vetcare.manager.users') }}" class="modal-close">×</a>
                    </div>

                    <div class="manager-modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">อีเมล (Username)</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $selectedUser->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ชื่อ-นามสกุล</label>
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $selectedUser->full_name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">บทบาท</label>
                            @php $currentRole = old('role', $selectedUser->role); @endphp
                            <select name="role" class="form-select">
                                <option value="staff"   @selected($currentRole === 'staff')>พนักงาน (Staff)</option>
                                <option value="vet"     @selected($currentRole === 'vet')>สัตวแพทย์ (Vet)</option>
                                <option value="manager" @selected($currentRole === 'manager')>ผู้จัดการ (Manager)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">สถานะ</label>
                            @php $currentStatus = old('status', $selectedUser->status); @endphp
                            <select name="status" class="form-select">
                                <option value="active"   @selected($currentStatus === 'active')>ใช้งาน (Active)</option>
                                <option value="inactive" @selected($currentStatus === 'inactive')>ระงับ (Inactive)</option>
                            </select>
                        </div>
                    </div>

                    <div class="manager-modal-footer">
                        <a href="{{ route('vetcare.manager.users') }}" class="modal-cancel-btn">ยกเลิก</a>
                        <button type="submit" class="modal-save-btn">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    {{-- ================= RESET PASSWORD ================= --}}
    @if ($panel === 'reset' && $selectedUser)
        <div class="manager-modal-overlay" id="user-modal">
            <div class="manager-user-modal small-modal">
                <form method="POST" action="{{ route('vetcare.manager.users.reset', $selectedUser->user_id) }}" style="display: contents">
                    @csrf
                    @method('PUT')

                    <div class="manager-modal-header">
                        <h2>รีเซ็ตรหัสผ่าน</h2>
                        <a href="{{ route('vetcare.manager.users') }}" class="modal-close">×</a>
                    </div>

                    <div class="manager-modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif

                        <p class="modal-description">
                            กำหนดรหัสผ่านใหม่สำหรับ <strong>{{ $selectedUser->email }}</strong>
                        </p>

                        <label class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" name="password" class="form-control" placeholder="อย่างน้อย 8 ตัวอักษร" required minlength="8">
                    </div>

                    <div class="manager-modal-footer">
                        <a href="{{ route('vetcare.manager.users') }}" class="modal-cancel-btn">ยกเลิก</a>
                        <button type="submit" class="modal-reset-confirm">รีเซ็ตรหัสผ่าน</button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    {{-- ================= DELETE ================= --}}
    @if ($panel === 'delete' && $selectedUser)
        <div class="manager-modal-overlay" id="user-modal">
            <div class="manager-user-modal small-modal">
                <form method="POST" action="{{ route('vetcare.manager.users.destroy', $selectedUser->user_id) }}" style="display: contents">
                    @csrf
                    @method('DELETE')

                    <div class="manager-modal-header">
                        <h2>ยืนยันการลบผู้ใช้งาน</h2>
                        <a href="{{ route('vetcare.manager.users') }}" class="modal-close">×</a>
                    </div>

                    <div class="manager-modal-body">
                        <div class="delete-warning-icon">⚠️</div>

                        <p class="delete-message">
                            คุณต้องการลบบัญชี <strong>{{ $selectedUser->email }}</strong> ใช่หรือไม่?
                        </p>

                        <small class="delete-warning-text">
                            การดำเนินการนี้จะไม่สามารถย้อนกลับได้
                            (หากบัญชีนี้มีประวัติในระบบ จะถูกเปลี่ยนเป็น "ระงับ" แทน)
                        </small>
                    </div>

                    <div class="manager-modal-footer">
                        <a href="{{ route('vetcare.manager.users') }}" class="modal-cancel-btn">ยกเลิก</a>
                        <button type="submit" class="modal-delete-confirm">ลบผู้ใช้งาน</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

@endsection