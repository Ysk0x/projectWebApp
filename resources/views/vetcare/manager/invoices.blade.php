@extends('vetcare.layouts.manager')

@section('title', 'ตรวจสอบใบเสร็จ | VetCare')
@section('page-name', 'Invoices')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vetcare/manager-invoices.css') }}?v=1">
@endpush

@php
    $today = now('Asia/Bangkok');
    $thaiMonths = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
    $thaiDays = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
    $thaiDate = 'วัน' . $thaiDays[$today->dayOfWeek] . 'ที่ ' . $today->day . ' ' . $thaiMonths[$today->month] . ' ' . ($today->year + 543);

    $statusLabels = ['paid' => 'ชำระแล้ว', 'pending' => 'รอดำเนินการ', 'void' => 'ยกเลิกบิล'];
    $auditLabels  = ['done' => 'ตรวจสอบแล้ว', 'waiting' => 'รอตรวจสอบ'];
@endphp

@section('content')

<div class="manager-invoices-page">

    <header class="manager-invoices-topbar">
        <div>
            <h1>รายงานการเงิน / ตรวจสอบใบเสร็จ</h1>
            <p>ตรวจสอบรายการชำระเงินและใบเสร็จของคลินิก</p>
        </div>
        <div class="manager-invoices-topbar-right">
            <span>{{ $thaiDate }}</span>
            <div class="manager-invoices-bell">🔔<b>3</b></div>
        </div>
    </header>

    <div class="manager-invoices-content">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- SUMMARY --}}
        <div class="invoice-summary-grid">
            <div class="invoice-summary-card"><span>ใบเสร็จทั้งหมด</span><strong>{{ $totalInvoices }}</strong></div>
            <div class="invoice-summary-card"><span>รายได้รวม</span><strong class="summary-revenue">฿{{ number_format($totalRevenue) }}</strong></div>
            <div class="invoice-summary-card"><span>ชำระแล้ว</span><strong class="summary-paid">{{ $paidCount }}</strong></div>
            <div class="invoice-summary-card"><span>รอตรวจสอบ</span><strong class="summary-audit">{{ $auditWaitingCount }}</strong></div>
            <div class="invoice-summary-card"><span>ยกเลิกบิล</span><strong class="summary-void">{{ $voidCount }}</strong></div>
        </div>

        {{-- MAIN CARD --}}
        <section class="manager-invoice-card">
            <div class="manager-invoice-card-header">
                <div>
                    <h2>รายการใบเสร็จทั้งหมด</h2>
                    <p>ตรวจสอบรายละเอียดและสถานะของใบเสร็จ</p>
                </div>

                <form method="GET" action="{{ route('vetcare.manager.invoices') }}" class="invoice-search">
                    <span>🔍</span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="ค้นหาเลขที่ / เจ้าของ / สัตว์ / พนักงาน...">
                </form>
            </div>

            <div class="table-responsive">
                <table class="table manager-invoice-table">
                    <thead>
                        <tr>
                            <th>เลขที่ใบเสร็จ</th>
                            <th>วันที่</th>
                            <th>พนักงานผู้รับเงิน</th>
                            <th>เจ้าของสัตว์</th>
                            <th>จำนวนเงิน</th>
                            <th>สถานะ</th>
                            <th>AUDIT</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td><strong class="invoice-number">{{ $invoice['number'] }}</strong></td>
                                <td>{{ $invoice['date'] }}</td>
                                <td>{{ $invoice['staff'] }}</td>
                                <td>
                                    <strong class="invoice-owner">{{ $invoice['owner'] }}</strong>
                                    <small class="invoice-pet">{{ $invoice['pet'] }}</small>
                                </td>
                                <td><strong class="invoice-amount">฿{{ number_format($invoice['amount']) }}</strong></td>
                                <td>
                                    <span class="invoice-status invoice-status-{{ $invoice['status'] }}">
                                        {{ $statusLabels[$invoice['status']] ?? $invoice['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="invoice-audit audit-{{ $invoice['audit'] }}">
                                        {{ $auditLabels[$invoice['audit']] }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('vetcare.manager.invoices', ['panel' => 'detail', 'id' => $invoice['id'], 'search' => $search]) }}#invoice-modal" class="invoice-detail-btn">ดูรายละเอียด</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="invoice-empty">ไม่พบรายการใบเสร็จ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="invoice-table-footer">
                แสดง {{ count($invoices) }} จาก {{ $totalInvoices }} รายการ
            </div>
        </section>
    </div>


    {{-- ================= DETAIL ================= --}}
    @if ($panel === 'detail' && $selectedInvoice)
        <div class="invoice-modal-overlay" id="invoice-modal">
            <div class="invoice-modal">

                <div class="invoice-modal-header">
                    <h2>รายละเอียดใบเสร็จ</h2>
                    <span class="invoice-status invoice-status-{{ $selectedInvoice['status'] }}">
                        {{ $statusLabels[$selectedInvoice['status']] ?? $selectedInvoice['status'] }}
                    </span>
                </div>

                <div class="invoice-modal-body">
                    <div class="invoice-detail-row"><span>เลขที่ใบเสร็จ</span><strong class="modal-invoice-number">{{ $selectedInvoice['number'] }}</strong></div>
                    <div class="invoice-detail-row"><span>วันที่</span><strong>{{ $selectedInvoice['date'] }}</strong></div>
                    <div class="invoice-detail-row"><span>พนักงานผู้รับเงิน</span><strong>{{ $selectedInvoice['staff'] }}</strong></div>
                    <div class="invoice-detail-row"><span>เจ้าของสัตว์</span><strong>{{ $selectedInvoice['owner'] }}</strong></div>
                    <div class="invoice-detail-row"><span>สัตว์เลี้ยง</span><strong>{{ $selectedInvoice['pet'] }}</strong></div>
                    <div class="invoice-detail-row"><span>วิธีชำระเงิน</span><strong>{{ $selectedInvoice['payment'] }}</strong></div>

                    {{-- ITEMS --}}
                    @if ($items->count())
                        <div class="invoice-detail-row" style="display:block">
                            <span>รายการ</span>
                            <table class="table table-sm mb-0 mt-1">
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr>
                                            <td>{{ $item->description ?: ($item->item_type === 'medicine' ? 'ค่ายา' : 'ค่าบริการ') }}</td>
                                            <td class="text-end">{{ $item->quantity }} × ฿{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end">฿{{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($selectedInvoice['discount'] > 0)
                        <div class="invoice-detail-row"><span>ยอดก่อนหักส่วนลด</span><strong>฿{{ number_format($selectedInvoice['subtotal'], 2) }}</strong></div>
                        <div class="invoice-detail-row"><span>ส่วนลด</span><strong>-฿{{ number_format($selectedInvoice['discount'], 2) }}</strong></div>
                    @endif

                    <div class="invoice-detail-row invoice-amount-row"><span>จำนวนเงิน</span><strong>฿{{ number_format($selectedInvoice['amount']) }}</strong></div>

                    <div class="invoice-detail-row">
                        <span>สถานะ Audit</span>
                        <span class="invoice-audit audit-{{ $selectedInvoice['audit'] }}">{{ $auditLabels[$selectedInvoice['audit']] }}</span>
                    </div>

                    {{-- AUDIT HISTORY --}}
                    @foreach ($audits as $audit)
                        <div class="invoice-detail-row" style="display:block">
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($audit->audited_at)->format('d/m/') . (\Carbon\Carbon::parse($audit->audited_at)->year + 543) }}
                                · {{ $audit->auditor }}
                            </small><br>
                            <small>{{ $audit->old_status }} → {{ $audit->new_status }} — {{ $audit->remarks }}</small>
                        </div>
                    @endforeach
                </div>

                <div class="invoice-modal-footer">
                    <a href="{{ route('vetcare.manager.invoices') }}" class="invoice-modal-close">ปิด</a>

                    @if ($selectedInvoice['raw_status'] !== 'cancelled')
                        <a href="{{ route('vetcare.manager.invoices', ['panel' => 'void', 'id' => $selectedInvoice['id']]) }}#invoice-modal" class="invoice-void-btn">
                            ยกเลิกบิล (Void)
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif


    {{-- ================= VOID ================= --}}
    @if ($panel === 'void' && $selectedInvoice)
        <div class="invoice-modal-overlay" id="invoice-modal">
            <div class="invoice-modal invoice-small-modal">
                <form method="POST" action="{{ route('vetcare.manager.invoices.void', $selectedInvoice['id']) }}" style="display: contents">
                    @csrf
                    @method('PUT')

                    <div class="invoice-modal-header">
                        <h2>ยืนยันการยกเลิกบิล</h2>
                    </div>

                    <div class="invoice-void-body">
                        <div class="invoice-void-icon">⚠️</div>

                        <p>ต้องการยกเลิกใบเสร็จ <strong>{{ $selectedInvoice['number'] }}</strong> ใช่หรือไม่?</p>

                        <span>จำนวนเงิน <strong>฿{{ number_format($selectedInvoice['amount']) }}</strong></span>

                        <div class="mt-3 text-start">
                            <label class="form-label">หมายเหตุ (ไม่บังคับ)</label>
                            <input type="text" name="remarks" class="form-control" maxlength="500" value="{{ old('remarks') }}" placeholder="เหตุผลที่ยกเลิก">
                            @error('remarks')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <small>การยกเลิกจะถูกบันทึกในประวัติ Audit ของใบเสร็จ</small>
                    </div>

                    <div class="invoice-modal-footer">
                        <a href="{{ route('vetcare.manager.invoices', ['panel' => 'detail', 'id' => $selectedInvoice['id']]) }}#invoice-modal" class="invoice-modal-close">กลับ</a>
                        <button type="submit" class="invoice-void-confirm">ยืนยันยกเลิกบิล</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

@endsection