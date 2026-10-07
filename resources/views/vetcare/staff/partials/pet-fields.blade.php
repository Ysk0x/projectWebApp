@php
    $v = fn ($key) => $useOld ? old($key) : ($p->{$key} ?? '');
    $currentSpecies = $v('species');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">ชื่อสัตว์เลี้ยง</label>
        <input type="text" name="pet_name" class="form-control" value="{{ $v('pet_name') }}" maxlength="100" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">ชนิดสัตว์</label>
        <input type="text" name="species" class="form-control" value="{{ $currentSpecies }}" list="species-list" maxlength="50" required>
        <datalist id="species-list">
            @foreach ($speciesOptions as $opt)<option value="{{ $opt }}">@endforeach
        </datalist>
    </div>
    <div class="col-md-6">
        <label class="form-label">พันธุ์</label>
        <input type="text" name="breed" class="form-control" value="{{ $v('breed') }}" maxlength="100">
    </div>
    <div class="col-md-6">
        <label class="form-label">เพศ</label>
        <select name="gender" class="form-select">
            <option value="">ไม่ระบุ</option>
            <option value="M" @selected($v('gender') === 'M')>ตัวผู้</option>
            <option value="F" @selected($v('gender') === 'F')>ตัวเมีย</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">วันเกิด</label>
        <input type="date" name="birth_date" class="form-control" value="{{ $v('birth_date') }}" max="{{ now()->format('Y-m-d') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">สี</label>
        <input type="text" name="color" class="form-control" value="{{ $v('color') }}" maxlength="50">
    </div>
    <div class="col-md-3">
        <label class="form-label">น้ำหนัก (กก.)</label>
        <input type="number" name="weight" class="form-control" min="0" max="9999.99" step="0.01" value="{{ $v('weight') }}">
    </div>
</div>