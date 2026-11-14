<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $reportType === 'customers' ? 'تمام کسٹمر وضاحت' : ($reportType === 'sale' ? 'تمام سیل بل' : 'تمام بل') }} — {{ $client->name }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box}
    body{margin:0;background:#eef5fb;color:#17233b;font-family:'Noto Nastaliq Urdu',serif}
    header{background:#0d47a1;color:#fff;padding:10px 20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
    header .back-link{width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;border:1px solid #ffffff66;border-radius:5px}
    header h1{margin:0;color:#fff;font-size:19px}
    header strong{margin-inline-end:auto}
    main{max-width:1440px;margin:20px auto;padding:0 14px}
    h1{font-size:22px;color:#0d47a1;margin:0 0 14px}
    .card{background:#fff;border-radius:8px;padding:14px;margin-bottom:14px;box-shadow:0 2px 8px #102e4b12}
    .filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;align-items:end}
    label{display:block;font-weight:700;font-size:13px}
    input,select{width:100%;margin-top:4px;padding:7px;border:1px solid #bdc9d6;border-radius:4px;font:inherit;font-size:13px}
    button,.button{border:0;border-radius:4px;background:#1565c0;color:#fff;padding:7px 12px;font:inherit;font-size:13px;cursor:pointer;text-decoration:none;display:inline-block}
    .secondary{background:#546e7a}
    .table-wrap{overflow:auto}
    table{width:100%;border-collapse:collapse;white-space:nowrap;font-size:13px}
    th,td{padding:7px 9px;text-align:right;border:1px solid #dfe7ef}
    th{background:#e8f1fa;color:#0d47a1}
    .bill-card{border:1px solid #cbd9e7;border-radius:6px;margin-bottom:12px;overflow:hidden}
    .bill-heading{display:flex;gap:14px;flex-wrap:wrap;background:#e8f1fa;padding:8px 10px;font-size:13px}
    .bill-heading strong{color:#0d47a1}
    .bill-lines,.bill-charges{padding:8px 10px}
    .bill-charges{border-top:1px solid #e3eaf0}
    .muted{color:#617286;font-size:12px}
    .pagination{margin-top:14px}
    .pagination nav{display:flex;justify-content:center;gap:5px;flex-wrap:wrap}
    .pagination a,.pagination span{display:inline-block;padding:5px 9px;border:1px solid #bdc9d6;border-radius:4px;text-decoration:none;color:#0d47a1;background:#fff}
    .pagination [aria-current=page] span{background:#1565c0;color:#fff}
    .status{font-weight:700}
    @media(max-width:600px){main{margin-top:12px;padding:0 8px}.card{padding:10px}header{padding:8px 10px}}
  </style>
</head>
<body>
<header>
  <a class="back-link" href="{{ route('client.portal.bills.sale', $client->slug) }}" aria-label="واپس جائیں" title="واپس جائیں" onclick="if (history.length > 1) { history.back(); return false; }">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6M9 12h12"/>
    </svg>
  </a>
  <h1>{{ $reportType === 'customers' ? 'تمام کسٹمر وضاحت' : ($reportType === 'sale' ? 'تمام سیل بل' : 'تمام بل') }}</h1>
  <strong>{{ $client->name }}</strong>
  <form method="POST" action="{{ route('client.portal.logout', $client->slug) }}">@csrf<button type="submit">لاگ آؤٹ</button></form>
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

  @if($reportType === 'customers')
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
          </div>
          <div class="bill-lines table-wrap">
            <table>
              <thead><tr><th>کسٹمر</th><th>جنس</th><th>نگ</th><th>وزن</th><th>ریٹ</th><th>لگانا</th><th>لگانا رقم</th><th>کل رقم</th><th>بیوپاری رقم</th><th>ڈیفرنس</th></tr></thead>
              <tbody>
                @forelse($bill->lines as $line)
                  <tr>
                    <td>{{ $line->customer_name ?: '—' }}{{ $line->customer_code ? ' ('.$line->customer_code.')' : '' }}</td>
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
