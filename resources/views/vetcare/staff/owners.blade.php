@extends('vetcare.layouts.staff')

@section('title', 'Owners & Pets')
@section('page-name', 'Owners & Pets')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/owners.css') }}">
@endpush

@php
    use App\Support\VetHelper;

    $speciesIcon = fn ($s) => match (true) {
        str_contains((string) $s, 'สุนัข') => '🐶',
        str_contains((string) $s, 'แมว') => '🐱',
        str_contains((string) $s, 'กระต่าย') => '🐰',
        default => '🐾',
    };
    $genderLabel = ['M' => 'ตัวผู้', 'F' => 'ตัวเมีย'];
    $speciesOptions = ['สุนัข', 'แมว', 'กระต่าย', 'นก', 'หนูแฮมสเตอร์', 'อื่นๆ'];
    $reopen = old('_modal');
@endphp

@section('content')

<div class="owners-page">

    <div class="owners-header">
        <div>
            <h1 class="owners-title">เจ้าของสัตว์และสัตว์เลี้ยง</h1>
        </div>

        <div class="owners-header-right">
            <span class="owners-date">{{ $thaiDate }}</span>
            <div class="owners-bell">🔔<span class="bell-number">3</span></div>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="owners-toolbar">
        <form method="GET" action="{{ route('vetcare.staff.owners') }}" class="owner-search">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="ค้นหาเจ้าของ / เบอร์โทร / สัตว์เลี้ยง...">
        </form>

        <div class="owner-actions">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addOwnerModal">+ เพิ่มเจ้าของ</button>

            @if ($selectedOwner)
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPetModal">+ เพิ่มสัตว์เลี้ยง</button>
            @endif
        </div>
    </div>

    <div class="row g-4">

        {{-- ================= OWNER LIST ================= --}}
        <div class="col-lg-5 col-xl-4">
            <h2 class="owner-list-title">เจ้าของสัตว์ ({{ $owners->count() }} คน)</h2>

            <div class="owner-list">
                @forelse ($owners as $owner)
                    @php $ownerName = VetHelper::fullName($owner->first_name, $owner->last_name); @endphp
                    <a href="{{ route('vetcare.staff.owners', ['owner' => $owner->owner_id, 'search' => $search]) }}"
                       class="owner-item {{ $selectedOwner && $selectedOwner->owner_id === $owner->owner_id ? 'active' : '' }}"
                       style="text-decoration:none;color:inherit">
                        <div class="owner-avatar">{{ mb_substr($owner->first_name, 0, 1) }}</div>

                        <div class="owner-item-info">
                            <div class="owner-item-top">
                                <h3>{{ $ownerName }}</h3>
                                <span class="owner-pet-count">{{ $owner->pet_count }} ตัว</span>
                            </div>
                            <p class="owner-phone">{{ $owner->phone ?: '-' }}</p>
                            <p class="owner-address">{{ $owner->address ?: '-' }}</p>
                        </div>
                    </a>
                @empty
                    <p class="text-muted p-3">ไม่พบเจ้าของสัตว์</p>
                @endforelse
            </div>
        </div>

        {{-- ================= DETAIL ================= --}}
        <div class="col-lg-7 col-xl-8">

            @if ($selectedOwner)

                <div class="owner-detail-card">
                    <div class="detail-card-header">
                        <h2>ข้อมูลเจ้าของ</h2>

                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editOwnerModal">แก้ไข</button>
                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteOwnerModal">ลบ</button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="detail-label">รหัสเจ้าของ</p>
                            <p class="detail-value">{{ $selectedOwner->owner_id }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="detail-label">ชื่อ-นามสกุล</p>
                            <p class="detail-value">{{ VetHelper::fullName($selectedOwner->first_name, $selectedOwner->last_name) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="detail-label">เบอร์โทร</p>
                            <p class="detail-value">{{ $selectedOwner->phone ?: '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="detail-label">อีเมล</p>
                            <p class="detail-value">{{ $selectedOwner->email ?: '-' }}</p>
                        </div>
                        <div class="col-12">
                            <p class="detail-label">ที่อยู่</p>
                            <p class="detail-value">{{ $selectedOwner->address ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <h2 class="pet-section-title">สัตว์เลี้ยง ({{ $pets->count() }} ตัว)</h2>

                @forelse ($pets as $pet)
                    <div class="pet-card">
                        <div class="pet-main">
                            <div class="pet-icon">{{ $speciesIcon($pet->species) }}</div>

                            <div class="pet-info">
                                <h3>{{ $pet->pet_name }}</h3>
                                <p>{{ $pet->species }}{{ $pet->breed ? ' • ' . $pet->breed : '' }}</p>

                                <div class="pet-tags">
                                    @if ($pet->gender)<span>{{ $genderLabel[$pet->gender] }}</span>@endif
                                    @if ($pet->birth_date)<span>เกิด {{ VetHelper::thaiShort($pet->birth_date) }}</span>@endif
                                    @if ($pet->weight)<span>{{ rtrim(rtrim(number_format($pet->weight, 2), '0'), '.') }} กก.</span>@endif
                                    @if ($pet->color)<span>{{ $pet->color }}</span>@endif
                                </div>
                            </div>
                        </div>

                        <div class="pet-actions">
                            <a href="{{ route('vetcare.staff.treatments', ['pet' => $pet->pet_id]) }}" class="btn btn-outline-primary btn-sm">🩺 รักษา</a>
                            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editPetModal-{{ $pet->pet_id }}">แก้ไข</button>

                            <form method="POST" action="{{ route('vetcare.staff.pets.destroy', $pet->pet_id) }}" class="d-inline"
                                  onsubmit="return confirm('ลบสัตว์เลี้ยง {{ $pet->pet_name }} ใช่หรือไม่?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">ลบ</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">เจ้าของรายนี้ยังไม่มีสัตว์เลี้ยง</p>
                @endforelse

                <button class="btn btn-outline-primary add-pet-button" data-bs-toggle="modal" data-bs-target="#addPetModal">+ เพิ่มสัตว์เลี้ยง</button>

            @else
                <div class="owner-detail-card"><p class="text-muted mb-0">ยังไม่มีข้อมูลเจ้าของ กด "+ เพิ่มเจ้าของ" เพื่อเริ่มต้น</p></div>
            @endif

        </div>
    </div>
</div>


{{-- ================= ADD OWNER ================= --}}
<div class="modal fade" id="addOwnerModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('vetcare.staff.owners.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_modal" value="addOwnerModal">

            <div class="modal-header">
                <h5 class="modal-title">เพิ่มเจ้าของสัตว์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                @include('vetcare.staff.partials.owner-fields', ['o' => null, 'useOld' => $reopen === 'addOwnerModal'])
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึก</button>
            </div>
        </form>
    </div>
</div>

@if ($selectedOwner)

    {{-- ================= EDIT OWNER ================= --}}
    <div class="modal fade" id="editOwnerModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('vetcare.staff.owners.update', $selectedOwner->owner_id) }}" class="modal-content">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="editOwnerModal">

                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขข้อมูลเจ้าของ {{ $selectedOwner->owner_id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @include('vetcare.staff.partials.owner-fields', ['o' => $selectedOwner, 'useOld' => $reopen === 'editOwnerModal'])
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= DELETE OWNER ================= --}}
    <div class="modal fade" id="deleteOwnerModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <form method="POST" action="{{ route('vetcare.staff.owners.destroy', $selectedOwner->owner_id) }}" class="modal-content">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    ต้องการลบ <strong>{{ VetHelper::fullName($selectedOwner->first_name, $selectedOwner->last_name) }}</strong> ใช่หรือไม่?
                    <div class="small text-muted mt-2">ลบได้เฉพาะเจ้าของที่ไม่มีสัตว์เลี้ยงและไม่มีประวัติการใช้บริการ</div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-danger">ลบ</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= ADD PET ================= --}}
    <div class="modal fade" id="addPetModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('vetcare.staff.pets.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="_modal" value="addPetModal">
                <input type="hidden" name="owner_id" value="{{ $selectedOwner->owner_id }}">

                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มสัตว์เลี้ยงของ {{ VetHelper::fullName($selectedOwner->first_name, $selectedOwner->last_name) }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @include('vetcare.staff.partials.pet-fields', ['p' => null, 'useOld' => $reopen === 'addPetModal', 'speciesOptions' => $speciesOptions])
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= EDIT PET (หนึ่ง modal ต่อสัตว์ 1 ตัว) ================= --}}
    @foreach ($pets as $pet)
        <div class="modal fade" id="editPetModal-{{ $pet->pet_id }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('vetcare.staff.pets.update', $pet->pet_id) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="editPetModal-{{ $pet->pet_id }}">

                    <div class="modal-header">
                        <h5 class="modal-title">แก้ไขข้อมูล {{ $pet->pet_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        @include('vetcare.staff.partials.pet-fields', ['p' => $pet, 'useOld' => $reopen === 'editPetModal-' . $pet->pet_id, 'speciesOptions' => $speciesOptions])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endif

{{-- เปิด modal เดิมกลับมาเมื่อ validation ไม่ผ่าน --}}
@if ($reopen)
    <script>
        window.addEventListener('load', function () {
            var el = document.getElementById(@json($reopen));
            if (el && window.bootstrap) { new bootstrap.Modal(el).show(); }
        });
    </script>
@endif

@endsection