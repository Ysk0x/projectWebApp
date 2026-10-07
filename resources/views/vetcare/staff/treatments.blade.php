@extends('vetcare.layouts.staff')

@section('title', 'การรักษาและจ่ายยา')
@section('page-name', 'Treatments')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/treatments.css') }}?v=1">
@endpush

@php
    use App\Support\VetHelper;

    $today = now('Asia/Bangkok');
    $isEdit = (bool) $editing;
    $genderLabel = ['M' => 'ตัวผู้', 'F' => 'ตัวเมีย'];
    $speciesIcon = fn ($s) => match (true) {
        str_contains((string) $s, 'สุนัข') => '🐶',
        str_contains((string) $s, 'แมว') => '🐱',
        str_contains((string) $s, 'กระต่าย') => '🐰',
        default => '🐾',
    };
    $invoiceLabels = ['paid' => 'ชำระแล้ว', 'unpaid' => 'รอชำระเงิน', 'cancelled' => 'ยกเลิกบิล'];

    // แถวยาที่ต้องแสดง (กรณี validation ไม่ผ่าน คืนค่าเดิมให้)
    $medRows = old('meds', [['medicine_id' => '', 'quantity' => 1, 'directions' => '']]);
    $medRows = array_values($medRows) ?: [['medicine_id' => '', 'quantity' => 1, 'directions' => '']];

    $defaultStaff = old('staff_id', $isEdit ? $editing->staff_id : ($appointment->staff_id ?? $currentUserId));
    $defaultDesc  = old('service_desc', $appointment->service_type ?? '');
@endphp

@section('content')

<div class="treat-page">

    <header class="treat-topbar">
        <h1>การรักษาและจ่ายยา</h1>
        <div class="treat-topbar-right">
            <span class="treat-date">{{ $thaiDate }}</span>
            <div class="treat-bell">🔔<span class="treat-bell-count">3</span></div>
        </div>
    </header>

    <div class="treat-content">

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        {{-- ================= ยังไม่ได้เลือกสัตว์ ================= --}}
        @if (! $pet)

            <section class="treat-card">
                <h2 class="treat-card-title">เลือกสัตว์เลี้ยงที่จะรักษา</h2>

                <form method="GET" action="{{ route('vetcare.staff.treatments') }}" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">สัตว์เลี้ยง (เจ้าของ)</label>
                        <select name="pet" class="form-select" required>
                            <option value="">-- เลือกสัตว์เลี้ยง --</option>
                            @foreach ($petList as $p)
                                <option value="{{ $p->pet_id }}">{{ $p->pet_name }} ({{ trim($p->first_name . ' ' . $p->last_name) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100">เริ่มบันทึกการรักษา</button>
                    </div>
                </form>

                <p class="text-muted mt-3 mb-0">หรือกดปุ่ม "รักษา" จากหน้า <a href="{{ route('vetcare.staff.appointments') }}">นัดหมาย</a> / <a href="{{ route('vetcare.staff.dashboard') }}">แดชบอร์ด</a></p>
            </section>

        @else

        <div class="row g-3">

            {{-- ================= LEFT: PET INFO + HISTORY ================= --}}
            <div class="col-lg-4 col-xl-4">

                <section class="treat-card pet-detail-card">
                    <h2 class="treat-card-title">ข้อมูลสัตว์เลี้ยง</h2>

                    <div class="treat-pet-heading">
                        <div class="treat-pet-avatar">{{ $speciesIcon($pet->species) }}</div>
                        <div class="treat-pet-heading-info">
                            <h3>{{ $pet->pet_name }}</h3>
                            <p>เจ้าของ: {{ VetHelper::fullName($pet->first_name, $pet->last_name) }}</p>
                        </div>
                    </div>

                    <div class="treat-pet-details">
                        <div class="treat-detail-row"><span>ชนิด</span><strong>{{ $pet->species }}</strong></div>
                        <div class="treat-detail-row"><span>พันธุ์</span><strong>{{ $pet->breed ?: '-' }}</strong></div>
                        <div class="treat-detail-row"><span>เพศ</span><strong>{{ $genderLabel[$pet->gender] ?? '-' }}</strong></div>
                        <div class="treat-detail-row"><span>น้ำหนัก</span><strong>{{ $pet->weight ? $pet->weight . ' กก.' : '-' }}</strong></div>
                        <div class="treat-detail-row"><span>รหัสสัตว์</span><strong>{{ $pet->pet_id }}</strong></div>
                        <div class="treat-detail-row"><span>เบอร์โทรเจ้าของ</span><strong>{{ $pet->phone ?: '-' }}</strong></div>
                    </div>

                    <a href="#treatment-history" class="treat-history-link">ดูประวัติการรักษา</a>
                </section>

                <section class="treat-card history-card" id="treatment-history">
                    <h2 class="treat-card-title">ประวัติล่าสุด</h2>

                    @forelse ($histories as $h)
                        <div class="treat-history-item">
                            <span class="history-date">{{ VetHelper::thaiShort($h->treatment_date) }}</span>
                            <strong class="history-service">{{ \Illuminate\Support\Str::limit($h->diagnosis ?: $h->symptoms, 40) }}</strong>
                            <span class="history-status">{{ $invoiceLabels[$h->invoice_status] ?? 'เสร็จสิ้น' }}</span>
                            <a href="{{ route('vetcare.staff.treatments', ['edit' => $h->treatment_id]) }}" class="small">แก้ไข</a>
                        </div>
                    @empty
                        <p class="text-muted mb-0">ยังไม่มีประวัติการรักษา</p>
                    @endforelse
                </section>
            </div>


            {{-- ================= RIGHT: FORM ================= --}}
            <div class="col-lg-8 col-xl-8">

                <form method="POST"
                      action="{{ $isEdit ? route('vetcare.staff.treatments.update', $editing->treatment_id) : route('vetcare.staff.treatments.store') }}">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    <input type="hidden" name="pet_id" value="{{ $pet->pet_id }}">
                    @if ($appointment)<input type="hidden" name="appointment_id" value="{{ $appointment->appointment_id }}">@endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        </div>
                    @endif

                    <section class="treat-card treatment-card">
                        <h2 class="treat-card-title">
                            {{ $isEdit ? 'แก้ไขบันทึกการรักษา ' . $editing->treatment_id : 'บันทึกการรักษา' }}
                        </h2>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="treatment-date">วันที่รักษา</label>
                                <input type="date" id="treatment-date" name="treatment_date" class="form-control"
                                       value="{{ $isEdit ? \Carbon\Carbon::parse($editing->treatment_date)->format('Y-m-d') : old('treatment_date', $today->format('Y-m-d')) }}"
                                       {{ $isEdit ? 'disabled' : 'required' }}>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="treatment-staff">พนักงานผู้รับผิดชอบ</label>
                                <select id="treatment-staff" name="staff_id" class="form-select" required>
                                    @foreach ($staffList as $s)
                                        <option value="{{ $s->user_id }}" @selected($defaultStaff === $s->user_id)>{{ $s->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="symptoms">อาการ</label>
                            <textarea id="symptoms" name="symptoms" class="form-control treat-textarea" rows="2" maxlength="1000" required
                                      placeholder="ระบุอาการที่พบ...">{{ old('symptoms', $editing->symptoms ?? '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="diagnosis">การวินิจฉัย</label>
                            <textarea id="diagnosis" name="diagnosis" class="form-control treat-textarea" rows="2" maxlength="1000"
                                      placeholder="ผลการวินิจฉัย...">{{ old('diagnosis', $editing->diagnosis ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="form-label" for="treatment-notes">บันทึกการรักษา</label>
                            <textarea id="treatment-notes" name="treatment_notes" class="form-control treat-textarea" rows="2" maxlength="2000"
                                      placeholder="รายละเอียดการรักษา...">{{ old('treatment_notes', $editing->treatment_notes ?? '') }}</textarea>
                        </div>
                    </section>


                    @if (! $isEdit)

                        <section class="treat-card">
                            <h2 class="treat-card-title">ค่ารักษา</h2>

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">รายการค่าบริการ</label>
                                    <input type="text" name="service_desc" class="form-control" maxlength="255"
                                           value="{{ $defaultDesc }}" placeholder="เช่น ค่าตรวจรักษาโรค">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">ค่ารักษา (บาท)</label>
                                    <input type="number" name="service_fee" class="form-control" min="0" step="0.01"
                                           value="{{ old('service_fee', 0) }}" required>
                                </div>
                            </div>
                        </section>


                        <section class="treat-card medicine-card" id="medicine-area">
                            <h2 class="treat-card-title">สั่งยา</h2>

                            <div id="medicine-rows">
                                @foreach ($medRows as $i => $row)
                                    <div class="medicine-form-row" data-med-row>
                                        <div class="medicine-field">
                                            <label class="form-label">เลือกยา</label>
                                            <select name="meds[{{ $i }}][medicine_id]" class="form-select">
                                                <option value="">-- เลือกยา --</option>
                                                @foreach ($medicines as $m)
                                                    <option value="{{ $m->medicine_id }}" @selected(($row['medicine_id'] ?? '') === $m->medicine_id)>
                                                        {{ $m->medicine_name }} (เหลือ {{ $m->stock_quantity }} {{ $m->unit }} • ฿{{ number_format($m->selling_price, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="medicine-field medicine-quantity">
                                            <label class="form-label">จำนวน</label>
                                            <input type="number" name="meds[{{ $i }}][quantity]" min="1" class="form-control" value="{{ $row['quantity'] ?? 1 }}">
                                        </div>

                                        <div class="medicine-field">
                                            <label class="form-label">วิธีใช้</label>
                                            <input type="text" name="meds[{{ $i }}][directions]" class="form-control" maxlength="500"
                                                   value="{{ $row['directions'] ?? '' }}" placeholder="ระบุวิธีใช้ตามคำสั่งสัตวแพทย์">
                                        </div>

                                        <div class="medicine-field" style="flex:0 0 auto;align-self:flex-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-remove-med>✕</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-medicine">+ เพิ่มยา</button>
                            <small class="d-block text-muted mt-2">เมื่อบันทึก ระบบจะตัดสต็อกยาและออกใบเสร็จ (รอชำระ) ให้อัตโนมัติ</small>
                        </section>

                        {{-- แม่แบบแถวยา สำหรับปุ่ม "+ เพิ่มยา" --}}
                        <template id="med-row-template">
                            <div class="medicine-form-row" data-med-row>
                                <div class="medicine-field">
                                    <label class="form-label">เลือกยา</label>
                                    <select name="meds[__i__][medicine_id]" class="form-select">
                                        <option value="">-- เลือกยา --</option>
                                        @foreach ($medicines as $m)
                                            <option value="{{ $m->medicine_id }}">{{ $m->medicine_name }} (เหลือ {{ $m->stock_quantity }} {{ $m->unit }} • ฿{{ number_format($m->selling_price, 2) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="medicine-field medicine-quantity">
                                    <label class="form-label">จำนวน</label>
                                    <input type="number" name="meds[__i__][quantity]" min="1" class="form-control" value="1">
                                </div>
                                <div class="medicine-field">
                                    <label class="form-label">วิธีใช้</label>
                                    <input type="text" name="meds[__i__][directions]" class="form-control" maxlength="500" placeholder="ระบุวิธีใช้">
                                </div>
                                <div class="medicine-field" style="flex:0 0 auto;align-self:flex-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-remove-med>✕</button>
                                </div>
                            </div>
                        </template>

                    @else
                        <div class="alert alert-info">โหมดแก้ไขแก้ได้เฉพาะข้อความบันทึก ส่วนยา/ค่ารักษาที่ออกใบเสร็จไปแล้วไม่สามารถแก้ไขได้ เพื่อไม่ให้ยอดเงินและสต็อกคลาดเคลื่อน</div>
                    @endif


                    <div class="treat-footer-actions">
                        <a href="{{ route('vetcare.staff.appointments') }}" class="treat-btn-cancel">ยกเลิก</a>
                        <button type="submit" class="treat-btn-confirm">
                            {{ $isEdit ? 'บันทึกการแก้ไข' : 'บันทึกการรักษาและไปชำระเงิน →' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>

        @endif

    </div>
</div>

@if ($pet && ! $isEdit)
    <script>
        (function () {
            var rows = document.getElementById('medicine-rows');
            var tpl  = document.getElementById('med-row-template');
            var next = rows.querySelectorAll('[data-med-row]').length;

            document.getElementById('add-medicine').addEventListener('click', function () {
                var html = tpl.innerHTML.replace(/__i__/g, next++);
                rows.insertAdjacentHTML('beforeend', html);
            });

            rows.addEventListener('click', function (e) {
                if (!e.target.matches('[data-remove-med]')) return;
                var all = rows.querySelectorAll('[data-med-row]');
                var row = e.target.closest('[data-med-row]');
                if (all.length > 1) { row.remove(); }
                else { row.querySelector('select').value = ''; }   // เหลือแถวสุดท้ายให้เคลียร์ค่าแทน
            });
        })();
    </script>
@endif

@endsection