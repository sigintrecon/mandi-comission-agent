<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $reportType === 'customers' ? 'تمام کسٹمر وضاحت' : ($reportType === 'sale' ? 'تمام سیل بل' : 'تمام بل') }} — {{ $client->name }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box}
    :root{--ink:#20333c;--nav:#173d49;--accent:#187c72;--canvas:#f1f5f5;--line:#d8e1e3;--muted:#66777d}
    body{margin:0;background:var(--canvas);color:var(--ink);font-family:'Noto Nastaliq Urdu',serif}
    .report-header{background:var(--nav);color:#fff;border-bottom:3px solid var(--accent)}
    .header-inner{max-width:1600px;min-height:82px;margin:0 auto;padding:12px 20px;display:flex;align-items:center;gap:14px}
    .back-link{width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:none;color:#fff;text-decoration:none;border:1px solid #ffffff66;border-radius:4px}
    .brand-mark{width:44px;height:44px;object-fit:contain;padding:3px;background:#fff;border-radius:4px}
    .header-title{margin-inline-end:auto}
    .header-kicker,.header-client span{display:block;color:#c5d8dc;font-size:11px;line-height:1.5}
    .header-title h1{margin:0;color:#fff;font-size:20px;line-height:1.6}
    .header-client{min-width:150px;padding-inline-start:18px;border-inline-start:1px solid #ffffff38}
    .header-client strong{display:block;color:#fff;font-size:14px}
    main{max-width:1600px;margin:20px auto;padding:0 16px}
    .card{background:#fff;border:1px solid var(--line);border-radius:6px;padding:14px;margin-bottom:14px}
    .filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;align-items:end}
    label{display:block;color:#3d535a;font-weight:700;font-size:13px}
    input,select{width:100%;margin-top:4px;padding:7px;border:1px solid #c6d2d5;border-radius:4px;font:inherit;font-size:13px;background:#fff}
    button,.button{border:0;border-radius:4px;background:var(--accent);color:#fff;padding:7px 12px;font:inherit;font-size:13px;cursor:pointer;text-decoration:none;display:inline-block}
    button:hover,.button:hover{background:#12665e}
    .secondary{background:#61747a}
    .table-wrap{overflow:auto}
    table{width:100%;border-collapse:collapse;white-space:nowrap;font-size:13px}
    th,td{padding:8px 10px;text-align:right;border-bottom:1px solid #e3e9e9}
    th{background:#e9f1f0;color:#254a50;font-size:12px;font-weight:700}
    tbody tr:nth-child(even){background:#f8faf9}
    tbody tr:hover{background:#edf6f4}
    .sale-report-table{min-width:1080px}
    .sale-report-table th,.sale-report-table td{padding:10px 12px}
    .invoice-cell{display:flex;align-items:center;gap:8px}
    .invoice-number{font-weight:700;color:#173d49}
    .open-bill{padding:4px 8px;font-size:12px;white-space:nowrap}
    .date-name{line-height:1.8}
    .muted{color:var(--muted);font-size:12px}
    .numeric{font-family:Arial,sans-serif;font-variant-numeric:tabular-nums}
    .amount-cell{font-weight:700}
    .credit-cell{color:#17695f;font-weight:700}
    .bill-card{border:1px solid var(--line);border-radius:6px;margin-bottom:12px;overflow:hidden}
    .bill-heading{display:flex;gap:14px;flex-wrap:wrap;background:#e9f1f0;padding:8px 10px;font-size:13px}
    .bill-heading strong{color:#254a50}
    .bill-lines,.bill-charges{padding:8px 10px}
    .bill-charges{border-top:1px solid #e3e9e9}
    .pagination{margin-top:14px}
    .pagination nav{display:flex;justify-content:center;gap:5px;flex-wrap:wrap}
    .pagination a,.pagination span{display:inline-block;padding:5px 9px;border:1px solid #c6d2d5;border-radius:4px;text-decoration:none;color:#17695f;background:#fff}
    .pagination [aria-current=page] span{background:var(--accent);color:#fff}
    .status{font-weight:700}
    @media(max-width:600px){main{margin-top:12px;padding:0 8px}.card{padding:10px}.header-inner{min-height:70px;padding:9px 10px;gap:9px}.brand-mark{width:38px;height:38px}.header-title h1{font-size:16px}.header-client{min-width:0;padding-inline-start:9px}}
  </style>
</head>
<body>
<header class="report-header">
  <div class="header-inner">
    <a class="back-link" href="{{ route('client.portal.bills.sale', $client->slug) }}" aria-label="واپس جائیں" title="واپس جائیں" onclick="if (history.length > 1) { history.back(); return false; }">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6M9 12h12"/>
      </svg>
    </a>
    <img class="brand-mark" src="{{ asset('images/elemeentra-logo.jpeg') }}" alt="">
    <div class="header-title">
      <span class="header-kicker">کاروباری رپورٹ</span>
      <h1>{{ $reportType === 'customers' ? 'تمام کسٹمر وضاحت' : ($reportType === 'sale' ? 'تمام سیل بل' : 'تمام بل') }}</h1>
    </div>
    <div class="header-client"><span>کاروبار</span><strong>{{ $client->name }}</strong></div>
  </div>
</header>
<main>
  <section class="card">
    @php
      $reportRoute = $reportType === 'customers'
          ? 'client.portal.bills.sale.customers'
          : ($reportType === 'sale' ? 'client.portal.bills.sale.all' : 'client.portal.bills.all');
    @endphp
    <form method="GET" action="{{ route($reportRoute, $client->slug) }}" class="filters">
      <label>سے تاریخ<input type="date" name="from" value="{{ $filters['from'] }}"></label>
      <label>تک تاریخ<input type="date" name="to" value="{{ $filters['to'] }}"></label>
      <label>کسٹمر
        <select name="customer_id">
          <option value="">تمام کسٹمر</option>
          @foreach($customers as $customer)
            <option value="{{ $customer->id }}" @selected($filters['customer_id'] == $customer->id)>{{ $customer->name_ur }} ({{ $customer->code }})</option>
          @endforeach
        </select>
      </label>
      <label>جنس
        <select name="item_id">
          <option value="">تمام جنس</option>
          @foreach($items as $item)
            <option value="{{ $item->id }}" @selected($filters['item_id'] == $item->id)>{{ $item->name_ur }} ({{ $item->code }})</option>
          @endforeach
        </select>
      </label>
      <label>انوائس نمبر<input type="text" name="invoice" maxlength="50" value="{{ $filters['invoice'] }}"></label>
      <label>حالت
        <select name="status">
          <option value="">تمام</option>
          <option value="posted" @selected($filters['status'] === 'posted')>مکمل</option>
          <option value="pending" @selected($filters['status'] === 'pending')>نامکمل</option>
        </select>
      </label>
      <label>فی صفحہ
        <select name="per_page">
          @foreach([10, 25, 50, 100] as $pageSize)
            <option value="{{ $pageSize }}" @selected($filters['per_page'] === $pageSize)>{{ $pageSize }}</option>
          @endforeach
        </select>
      </label>
      <div><button type="submit">فلٹر کریں</button> <a class="button secondary" href="{{ route($reportRoute, $client->slug) }}">فلٹر صاف کریں</a></div>
    </form>
  </section>

  @if($reportType === 'sale')
    <section class="card">
      <form id="selectedPrintForm" method="GET" action="{{ route('client.portal.bills.print', $client->slug) }}"></form>
      <div style="display:flex;justify-content:flex-start;margin-bottom:10px">
        <button type="submit" form="selectedPrintForm">منتخب بل پرنٹ</button>
      </div>
      <div class="table-wrap">
        <table class="sale-report-table">
          <thead>
            <tr>
              <th>Option / INV</th><th>Date / Name</th><th>Jins</th><th>Marka</th>
              <th>Veh#</th><th>Nag</th><th>Amount</th><th>Exp</th><th>Credit Amount</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bills as $bill)
              <tr>
                <td>
                  <div class="invoice-cell">
                    <input type="checkbox" name="ids[]" form="selectedPrintForm" value="{{ $bill->id }}" aria-label="انوائس {{ $bill->inv_no }} منتخب کریں">
                    <span class="invoice-number">{{ $bill->inv_no }}/{{ $bill->bill_date }}</span>
                    <a class="button open-bill" href="{{ route('client.portal.bills.sale', ['slug' => $client->slug, 'search' => $bill->inv_no, 'edit' => $bill->id]) }}">ترمیم</a>
                    <form method="POST" action="{{ route('client.portal.bills.destroy', ['slug' => $client->slug, 'bill' => $bill->id]) }}" onsubmit="return confirm('کیا آپ یہ بل حذف کرنا چاہتے ہیں؟')">
                      @csrf
                      <button type="submit" class="open-bill" style="background:#b42318">حذف</button>
                    </form>
                  </div>
                </td>
                <td class="date-name">
                  <div><strong>{{ $bill->party_code }}</strong> {{ $bill->party_name ?: '—' }}</div>
                  @if($bill->party_name_en)<div class="muted">{{ $bill->party_name_en }}</div>@endif
                </td>
                <td>{{ $bill->item_names ?: '—' }}</td>
                <td>{{ $bill->marka ?: '—' }}</td>
                <td>{{ $bill->vehicle_no ?: '—' }}</td>
                <td class="numeric">{{ number_format((float) $bill->lot_nag, 2) }}</td>
                <td class="numeric amount-cell">{{ number_format((float) $bill->amount, 2) }}</td>
                <td class="numeric">{{ number_format((float) $bill->expenses, 2) }}</td>
                <td class="numeric credit-cell">{{ number_format((float) $bill->credit_amount, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="9" class="muted">کوئی ریکارڈ نہیں ملا۔</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pagination">{{ $bills->links() }}</div>
    </section>
  @elseif($reportType === 'customers')
    <section class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>تاریخ</th><th>انوائس</th><th>حالت</th><th>کسٹمر</th><th>بیوپاری</th><th>جنس</th><th>نگ</th><th>وزن</th><th>ریٹ</th><th>لگانا</th><th>لگانا رقم</th><th>کل رقم</th><th>بیوپاری رقم</th><th>ڈیفرنس</th></tr></thead>
          <tbody>
            @forelse($lines as $line)
              <tr>
                <td>{{ $line->bill_date }}</td><td>{{ $line->inv_no }}</td>
                <td class="status">{{ $line->status === 'pending' ? 'نامکمل' : 'مکمل' }}</td>
                <td>{{ $line->customer_name }} ({{ $line->customer_code }})</td>
                <td>{{ $line->party_name ?: '—' }}</td><td>{{ $line->item_name ?: '—' }}</td>
                <td>{{ number_format((float) $line->nag, 2) }}</td><td>{{ $line->wazan === null ? '—' : number_format((float) $line->wazan, 2) }}</td>
                <td>{{ number_format((float) $line->rate, 2) }}</td><td>{{ number_format((float) $line->lagana, 2) }}</td>
                <td>{{ number_format((float) $line->lagana_amount, 2) }}</td><td>{{ number_format((float) $line->cust_amount, 2) }}</td>
                <td>{{ number_format((float) $line->party_amount, 2) }}</td><td>{{ number_format((float) $line->diff_amount, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="14" class="muted">کوئی ریکارڈ نہیں ملا۔</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pagination">{{ $lines->links() }}</div>
    </section>
  @else
    <section class="card">
      @forelse($bills as $bill)
        <article class="bill-card">
          <div class="bill-heading">
            <strong>انوائس {{ $bill->inv_no }}</strong>
            <span>تاریخ: {{ $bill->bill_date }}</span>
            <span>قسم: {{ [1 => 'سیل مال', 2 => 'خرید', 3 => 'کسٹمر خرید'][$bill->bill_type] ?? $bill->bill_type }}</span>
            <span>حالت: {{ $bill->status === 'pending' ? 'نامکمل' : 'مکمل' }}</span>
            <span>بیوپاری: {{ $bill->party_name ?: '—' }}</span>
            <span>معرفت: {{ $bill->marfat_name ?: '—' }}</span>
            <span>کل نگ: {{ number_format((float) $bill->lot_nag, 2) }}</span>
            @if($bill->marka)<span>مارکہ: {{ $bill->marka }}</span>@endif
            @if($bill->vehicle_no)<span>گاڑی: {{ $bill->vehicle_no }}</span>@endif
            @if($bill->container_no)<span>کنٹینر: {{ $bill->container_no }}</span>@endif
            @if($bill->bill_type === 1)
              <a class="button" href="{{ route('client.portal.bills.sale', ['slug' => $client->slug, 'search' => $bill->inv_no, 'edit' => $bill->id]) }}">ترمیم</a>
            @endif
            @if(in_array((int) $bill->bill_type, [1, 2], true))
              <a class="button" href="{{ route('client.portal.bills.print', ['slug' => $client->slug, 'ids' => [$bill->id]]) }}">پرنٹ</a>
              <form method="POST" action="{{ route('client.portal.bills.destroy', ['slug' => $client->slug, 'bill' => $bill->id]) }}" style="display:inline" onsubmit="return confirm('کیا آپ یہ بل حذف کرنا چاہتے ہیں؟')">
                @csrf
                <button type="submit" style="background:#b42318">حذف</button>
              </form>
            @endif
          </div>
          <div class="bill-lines table-wrap">
            <table>
              <thead><tr><th>{{ (int) $bill->bill_type === 2 ? 'بیوپاری' : 'کسٹمر' }}</th><th>جنس</th><th>نگ</th><th>وزن</th><th>ریٹ</th><th>لگانا</th><th>لگانا رقم</th><th>کل رقم</th><th>بیوپاری رقم</th><th>ڈیفرنس</th></tr></thead>
              <tbody>
                @forelse($bill->lines as $line)
                  <tr>
                    <td>{{ (int) $bill->bill_type === 2 ? ($bill->party_name ?: '—').' ('.$bill->party_code.')' : (($line->customer_name ?: '—').($line->customer_code ? ' ('.$line->customer_code.')' : '')) }}</td>
                    <td>{{ $line->item_name ?: '—' }}{{ $line->item_code ? ' ('.$line->item_code.')' : '' }}</td>
                    <td>{{ number_format((float) $line->nag, 2) }}</td><td>{{ $line->wazan === null ? '—' : number_format((float) $line->wazan, 2) }}</td>
                    <td>{{ number_format((float) $line->rate, 2) }}</td><td>{{ number_format((float) $line->lagana, 2) }}</td>
                    <td>{{ number_format((float) $line->lagana_amount, 2) }}</td><td>{{ number_format((float) $line->cust_amount, 2) }}</td>
                    <td>{{ number_format((float) $line->party_amount, 2) }}</td><td>{{ number_format((float) $line->diff_amount, 2) }}</td>
                  </tr>
                @empty
                  <tr><td colspan="10" class="muted">اس بل میں کوئی تفصیلی اندراج نہیں۔</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($bill->charges->isNotEmpty())
            <div class="bill-charges">
              <strong>اخراجات:</strong>
              @foreach($bill->charges as $charge)
                <span>{{ $charge->name_en }} ({{ number_format((float) $charge->rate, 2) }}): {{ number_format((float) $charge->amount, 2) }}</span>@if(!$loop->last)، @endif
              @endforeach
            </div>
          @endif
        </article>
      @empty
        <p class="muted">کوئی بل نہیں ملا۔</p>
      @endforelse
      <div class="pagination">{{ $bills->links() }}</div>
    </section>
  @endif
</main>
</body>
</html>
