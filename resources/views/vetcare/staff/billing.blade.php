@extends('vetcare.layouts.staff')

@section('title', 'ชำระเงินและใบเสร็จ | VetCare')
@section('page-name', 'Billing')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/billing.css') }}?v=5">
@endpush

@section('content')

<div class="billing-page">

@if (! $invoice)

    {{-- ================= LIST: เลือกใบเสร็จที่จะชำระ ================= --}}
    <header class="bill-topbar">
        <h1>ชำระเงินและออกใบเสร็จ</h1>
        <div class="bill-topbar-right">
            <span>{{ $thaiDate }}</span>
            <span class="bill-bell"><b>3</b></span>
        </div>
    </header>

    <div class="bill-content">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <section class="bill-card">
            <h2 class="bill-card-title">รายการรอชำระเงิน ({{ $unpaid->count() }})</h2>

            <div class="table-responsive">
                <table class="table bill-expense-table">
                    <thead>
                        <tr><th>เลขที่</th><th>เจ้าของ</th><th>สัตว์เลี้ยง</th><th>ยอดรวม</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($unpaid as $row)
                            <tr>
                                <td>{{ $row->invoice_number }}</td>
                                <td>{{ trim($row->first_name . ' ' . $row->last_name) }}</td>
                                <td>{{ $row->pet_name }}</td>
                                <td>฿{{ number_format($row->total_amount) }}</td>
                                <td><a href="{{ route('vetcare.staff.billing', ['invoice' => $row->invoice_id]) }}" class="btn btn-primary btn-sm">ชำระเงิน</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">ไม่มีรายการรอชำระเงิน</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bill-card">
            <h2 class="bill-card-title">ชำระแล้วล่าสุด</h2>

            <div class="table-responsive">
                <table class="table bill-expense-table">
                    <thead>
                        <tr><th>เลขที่</th><th>เจ้าของ</th><th>สัตว์เลี้ยง</th><th>ยอดรวม</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($recent as $row)
                            <tr>
                                <td>{{ $row->invoice_number }}</td>
                                <td>{{ trim($row->first_name . ' ' . $row->last_name) }}</td>
                                <td>{{ $row->pet_name }}</td>
                                <td>฿{{ number_format($row->total_amount) }}</td>
                                <td><a href="{{ route('vetcare.staff.billing', ['invoice' => $row->invoice_id]) }}" class="btn btn-outline-secondary btn-sm">ดูใบเสร็จ</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">ยังไม่มีรายการ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

@else

    {{-- ================= HEADER ================= --}}
    @if (! $printMode)
        <header class="bill-topbar">
            <h1>ชำระเงินและออกใบเสร็จ</h1>
            <div class="bill-topbar-right">
                <span>{{ $thaiDate }}</span>
                <span class="bill-bell"><b>3</b></span>
            </div>
        </header>
    @else
        <div class="bill-print-help">
            <a href="{{ route('vetcare.staff.billing', ['invoice' => $invoice->invoice_id]) }}">← กลับไปหน้าชำระเงิน</a>
            <p>กด Command + P บน Mac หรือ Ctrl + P บน Windows</p>
        </div>
    @endif

    <div class="{{ $printMode ? 'bill-print-content' : 'bill-content' }}">

        @if (! $printMode)
            @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if ($errors->any())
                <div class="alert alert-danger">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            @endif

            @if ($isPaid)
                <div class="bill-success-message">
                    <div class="bill-success-icon">✓</div>
                    <div>
                        <strong>บันทึกการชำระเงินเรียบร้อยแล้ว</strong>
                        <p>ชำระด้วย {{ $methodName }} จำนวน ฿{{ number_format($grandTotal) }}</p>
                    </div>
                </div>
            @elseif ($isCancelled)
                <div class="alert alert-secondary">ใบเสร็จนี้ถูกยกเลิกแล้ว ไม่สามารถรับชำระเงินได้</div>
            @endif
        @endif

        <div class="{{ $printMode ? '' : 'row g-3' }}">

            {{-- ================= LEFT ================= --}}
            @if (! $printMode)
                <div class="col-lg-6">

                    <section class="bill-card">
                        <h2 class="bill-card-title">ข้อมูลผู้ป่วย</h2>

                        <div class="bill-info-grid">
                            <div><span>เจ้าของ</span><strong>{{ $owner }}</strong></div>
                            <div><span>เบอร์โทร</span><strong>{{ $invoice->phone ?: '-' }}</strong></div>
                            <div><span>สัตว์เลี้ยง</span><strong>{{ $invoice->pet_name }}</strong></div>
                            <div><span>ชนิด / พันธุ์</span><strong>{{ $invoice->species }} / {{ $invoice->breed ?: '-' }}</strong></div>
                            <div><span>วันที่รักษา</span><strong>{{ $treatment ? \App\Support\VetHelper::thaiShort($treatment->treatment_date) : $invoiceDate }}</strong></div>
                            <div><span>พนักงาน</span><strong>{{ $treatment->staff_name ?? '-' }}</strong></div>
                        </div>

                        @if ($treatment && $treatment->diagnosis)
                            <div class="bill-diagnosis">
                                <strong>การวินิจฉัย</strong>
                                <p>{{ $treatment->diagnosis }}</p>
                            </div>
                        @endif
                    </section>

                    <section class="bill-card">
                        <h2 class="bill-card-title">รายการค่าใช้จ่าย</h2>

                        <div class="table-responsive">
                            <table class="table bill-expense-table">
                                <thead><tr><th>รายการ</th><th>จำนวน</th><th>ราคา</th><th>รวม</th></tr></thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr>
                                            <td>{{ $item->description }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>฿{{ number_format($item->unit_price, 2) }}</td>
                                            <td>฿{{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="bill-totals">
                            <div><span>ค่ารักษา</span><strong>฿{{ number_format($serviceTotal) }}</strong></div>
                            <div><span>ค่ายา</span><strong>฿{{ number_format($medicineTotal) }}</strong></div>
                            <div><span>ส่วนลด</span><strong>฿{{ number_format($discount) }}</strong></div>
                            <div class="bill-grand-total"><span>ยอดรวมทั้งหมด</span><strong>฿{{ number_format($grandTotal) }}</strong></div>
                        </div>
                    </section>

                    {{-- ================= PAYMENT ================= --}}
                    <section class="bill-card" id="payment-method">
                        <h2 class="bill-card-title">วิธีชำระเงิน</h2>

                        @if (! $isPaid && ! $isCancelled)
                            <div class="bill-payment-options">
                                @foreach ($methods as $key => $m)
                                    <a href="{{ route('vetcare.staff.billing', ['invoice' => $invoice->invoice_id, 'method' => $key]) }}#payment-method"
                                       class="bill-payment-option {{ $method === $key ? 'active' : '' }}">{{ $m['label'] }}</a>
                                @endforeach
                            </div>

                            @if ($method === 'cash')
                                <div class="bill-payment-info">
                                    <strong>ชำระด้วยเงินสด</strong>
                                    <p>ยอดที่ต้องชำระ ฿{{ number_format($grandTotal) }}</p>
                                </div>
                            @elseif ($method === 'transfer')
                                <div class="bill-transfer-info">
                                    <p>สแกน QR Code เพื่อชำระเงิน</p>
                                    <div class="bill-qr-area">
                                        <p class="bill-qr-placeholder">พื้นที่สำหรับใส่ QR Code</p>
                                    </div>
                                    <div class="bill-bank-details">
                                        <p><strong>ธนาคาร</strong> : [ชื่อธนาคาร]</p>
                                        <p><strong>ชื่อบัญชี</strong> : [ชื่อคลินิก]</p>
                                        <p><strong>เลขที่บัญชี</strong> : [เลขบัญชี]</p>
                                    </div>
                                </div>
                            @else
                                <div class="bill-payment-info">
                                    <strong>ชำระด้วยบัตรเครดิต</strong>
                                    <p>ยอดที่ต้องชำระ ฿{{ number_format($grandTotal) }}</p>
                                </div>
                            @endif
                        @elseif ($payment)
                            <div class="bill-payment-info">
                                <strong>{{ $methodName }}</strong>
                                <p>รับโดย {{ $payment->receiver }} • อ้างอิง {{ $payment->reference_no }}</p>
                            </div>
                        @endif

                        <div class="bill-action-buttons">
                            <a href="{{ route('vetcare.staff.billing', ['invoice' => $invoice->invoice_id, 'print' => '1']) }}"
                               class="bill-btn-print" target="_blank" rel="noopener">เปิดใบเสร็จสำหรับพิมพ์</a>

                            @if (! $isPaid && ! $isCancelled)
                                <form method="POST" action="{{ route('vetcare.staff.billing.pay', $invoice->invoice_id) }}" class="bill-payment-form">
                                    @csrf
                                    <input type="hidden" name="method" value="{{ $method }}">

                                    @if ($method !== 'cash')
                                        <input type="text" name="reference_no" class="form-control form-control-sm mb-2"
                                               maxlength="100" placeholder="เลขอ้างอิงการโอน/สลิป (ไม่บังคับ)">
                                    @endif

                                    <button type="submit" class="bill-btn-save"
                                            onclick="return confirm('ยืนยันรับชำระ ฿{{ number_format($grandTotal) }} ด้วย {{ $methodName }} ?')">
                                        ✓ บันทึกการชำระเงิน
                                    </button>
                                </form>
                            @elseif ($isPaid)
                                <button type="button" class="bill-btn-paid" disabled>✓ บันทึกการชำระแล้ว</button>
                            @endif
                        </div>
                    </section>
                </div>
            @endif

            {{-- ================= RECEIPT ================= --}}
            <div class="{{ $printMode ? 'bill-print-column' : 'col-lg-6' }}">
                <section class="bill-card bill-receipt" id="receipt-preview">

                    <div class="bill-receipt-header">
                        <h2>VETCARE</h2>
                        <p>ระบบจัดการคลินิกสัตว์</p>
                        <small>ที่อยู่และเบอร์โทรของคลินิก</small>
                    </div>

                    <div class="bill-receipt-title">
                        <h3>ใบเสร็จรับเงิน</h3>
                        @if ($isPaid)
                            <span class="bill-paid-badge">✓ ชำระแล้ว</span>
                        @elseif ($isCancelled)
                            <span class="bill-unpaid-badge">ยกเลิกบิล</span>
                        @else
                            <span class="bill-unpaid-badge">รอชำระ</span>
                        @endif
                    </div>

                    <div class="bill-receipt-info">
                        <div><span>เลขที่</span><strong>{{ $invoice->invoice_number }}</strong></div>
                        <div><span>วันที่</span><strong>{{ $invoiceDate }}</strong></div>
                    </div>

                    <div class="bill-receipt-patient">
                        <div><span>เจ้าของ</span><strong>{{ $owner }}</strong></div>
                        <div><span>สัตว์เลี้ยง</span><strong>{{ $invoice->pet_name }}</strong></div>
                        <div><span>พนักงาน</span><strong>{{ $payment->receiver ?? ($treatment->staff_name ?? '-') }}</strong></div>
                    </div>

                    <table class="bill-receipt-table">
                        <thead><tr><th>รายการ</th><th>จำนวน</th><th>รวม</th></tr></thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>฿{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="bill-receipt-summary">
                        <div><span>ค่ารักษา</span><strong>฿{{ number_format($serviceTotal) }}</strong></div>
                        <div><span>ค่ายา</span><strong>฿{{ number_format($medicineTotal) }}</strong></div>
                        @if ($discount > 0)
                            <div><span>ส่วนลด</span><strong>-฿{{ number_format($discount) }}</strong></div>
                        @endif
                        <div class="bill-receipt-grand"><span>ยอดรวม</span><strong>฿{{ number_format($grandTotal) }}</strong></div>
                        <div><span>วิธีชำระเงิน</span><strong>{{ $isPaid ? $methodName : '-' }}</strong></div>
                        <div>
                            <span>สถานะ</span>
                            @if ($isPaid)<strong class="bill-status-paid">ชำระเงินแล้ว</strong>
                            @elseif ($isCancelled)<strong class="bill-status-unpaid">ยกเลิกบิล</strong>
                            @else<strong class="bill-status-unpaid">รอชำระเงิน</strong>@endif
                        </div>
                    </div>

                    <div class="bill-receipt-footer">
                        <p>ขอบคุณที่ใช้บริการ VETCARE</p>
                        <p>กรุณาเก็บใบเสร็จไว้เป็นหลักฐาน</p>
                        @if ($isPaid)<strong class="receipt-paid-text">✓ บันทึกการชำระเงินแล้ว</strong>@endif
                    </div>
                </section>
            </div>

        </div>
    </div>

@endif

</div>

@endsection