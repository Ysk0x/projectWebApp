@php
    $v = fn ($key) => $useOld ? old($key) : ($o->{$key} ?? '');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">ชื่อ</label>
        <input type="text" name="first_name" class="form-control" value="{{ $v('first_name') }}" maxlength="50" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">นามสกุล</label>
        <input type="text" name="last_name" class="form-control" value="{{ $v('last_name') }}" maxlength="50" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">เบอร์โทร</label>
        <input type="text" name="phone" class="form-control" value="{{ $v('phone') }}" maxlength="20" placeholder="08x-xxx-xxxx">
    </div>
    <div class="col-md-6">
        <label class="form-label">อีเมล</label>
        <input type="email" name="email" class="form-control" value="{{ $v('email') }}" maxlength="100">
    </div>
    <div class="col-12">
        <label class="form-label">ที่อยู่</label>
        <textarea name="address" class="form-control" rows="3" maxlength="255">{{ $v('address') }}</textarea>
    </div>
</div>