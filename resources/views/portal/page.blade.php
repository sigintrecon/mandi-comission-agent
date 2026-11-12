<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title }} — {{ $client->name }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box}body{margin:0;background:#eef5fb;color:#17233b;font-family:'Noto Nastaliq Urdu',serif}
    header{background:#0d47a1;color:#fff;padding:12px 22px;display:flex;align-items:center;gap:18px}
    header a{color:#fff;text-decoration:none}header strong{margin-inline-end:auto}
    main{max-width:1180px;margin:24px auto;padding:0 16px}.card{background:#fff;border-radius:10px;padding:20px;margin-bottom:18px;box-shadow:0 2px 9px #102e4b12}
    h1{font-size:23px;margin:0 0 16px;color:#0d47a1}h2{font-size:18px;margin:0 0 14px}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px}
    label{display:block;font-weight:bold;font-size:14px}input,select,textarea{width:100%;margin-top:5px;padding:9px;border:1px solid #bdc9d6;border-radius:6px;font:inherit;font-size:14px}
    button,.button{border:0;border-radius:6px;background:#1565c0;color:#fff;padding:9px 16px;font:inherit;cursor:pointer;text-decoration:none;display:inline-block}
    .muted{color:#617286;font-size:13px}.notice{padding:10px 14px;border-radius:6px;margin-bottom:12px;background:#e8f5e9;color:#216b2c}
    .errors{background:#ffebee;color:#a21c2c;padding:10px 14px;border-radius:6px;margin-bottom:12px}.errors ul{margin:4px 0}
    .table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;white-space:nowrap}th,td{padding:9px 11px;text-align:right;border-bottom:1px solid #e3eaf0}th{background:#e8f1fa;color:#0d47a1}
    .actions{display:flex;gap:8px;align-items:center;margin-top:16px}.check{display:flex;align-items:center;gap:8px;margin-top:12px}.check input{width:auto;margin:0}
    @media(max-width:600px){header{flex-wrap:wrap}main{margin-top:14px;padding:0 10px}.card{padding:14px}}
  </style>
</head>
<body>
<header>
  <a href="{{ route('client.portal.dashboard', ['slug' => $client->slug]) }}">ڈیش بورڈ</a>
  <strong>{{ $client->name }}</strong>
  <form method="POST" action="{{ route('client.portal.logout', ['slug' => $client->slug]) }}">@csrf<button type="submit">لاگ آؤٹ</button></form>
</header>
<main>
  <h1>{{ $title }}</h1>
  @if (session('success'))
    <div class="notice">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="errors"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif

  @if ($page === 'accounts.create')
    <section class="card">
      <form method="POST" action="{{ route($postRoute, ['slug' => $client->slug]) }}">@csrf
        <div class="grid">
          <label>اکاؤنٹ کوڈ<input name="code" value="{{ old('code') }}" required maxlength="20"></label>
          <label>اکاؤنٹ کی قسم<select name="account_type_id" required><option value="">منتخب کریں</option>@foreach ($accountTypes as $type)<option value="{{ $type->id }}" @selected(old('account_type_id') == $type->id)>{{ $type->name_ur }} ({{ $type->name_en }})</option>@endforeach</select></label>
          <label>نام (اردو)<input name="name_ur" value="{{ old('name_ur') }}" required></label>
          <label>نام (English)<input name="name_en" value="{{ old('name_en') }}"></label>
          <label>فون<input name="phone" value="{{ old('phone') }}"></label>
          <label>شہر<input name="city" value="{{ old('city') }}"></label>
          <label>پتہ<input name="address" value="{{ old('address') }}"></label>
          <label>کمیشن فیصد<input type="number" name="commission_pct" min="0" max="100" step="0.01" value="{{ old('commission_pct', 0) }}"></label>
          <label>لگانہ<input type="number" name="default_lagana" min="0" step="0.01" value="{{ old('default_lagana', 0) }}"></label>
          <label>ریکوری گروپ<select name="recovery_group_id"><option value="">کوئی نہیں</option>@foreach ($recoveryGroups as $group)<option value="{{ $group->id }}">{{ $group->name_ur }}</option>@endforeach</select></label>
        </div><div class="actions"><button type="submit">کھاتہ محفوظ کریں</button><a class="button" href="{{ route('client.portal.accounts.index', ['slug' => $client->slug]) }}">تمام کھاتے</a></div>
      </form>
    </section>
  @elseif ($page === 'items.create')
    <section class="card">
      <form method="POST" action="{{ route($postRoute, ['slug' => $client->slug]) }}">@csrf
        <div class="grid">
          <label>جنس کا نام (اردو)<input name="name_ur" value="{{ old('name_ur') }}" required></label>
          <label>نام (English)<input name="name_en" value="{{ old('name_en') }}"></label>
          <label>کمیشن فیصد<input type="number" name="default_commission_pct" min="0" max="100" step="0.01" value="{{ old('default_commission_pct', 0) }}"></label>
          <label>مزدوری ریٹ<input type="number" name="default_mazdoori_rate" min="0" step="0.01" value="{{ old('default_mazdoori_rate', 0) }}"></label>
          <label>مارکیٹ فیس ریٹ<input type="number" name="default_market_fee_rate" min="0" step="0.01" value="{{ old('default_market_fee_rate', 0) }}"></label>
          <label>منشیانہ ریٹ<input type="number" name="default_munshiana_rate" min="0" step="0.01" value="{{ old('default_munshiana_rate', 0) }}"></label>
          <label>لگانہ<input type="number" name="default_lagana" min="0" step="0.01" value="{{ old('default_lagana', 10) }}"></label>
        </div><div class="actions"><button type="submit">جنس محفوظ کریں</button></div>
      </form>
    </section>
  @elseif (str_starts_with($page, 'bills.') && $page !== 'bills.index')
    <section class="card">
      <p class="muted">یہ بل پہلے زیرِ التوا محفوظ ہوگا؛ اس مرحلے پر لیجر میں اندراج نہیں ہوگا۔</p>
      <form method="POST" action="{{ route($postRoute, ['slug' => $client->slug]) }}">@csrf
        <div class="grid">
          <label>تاریخ<input type="date" name="bill_date" value="{{ old('bill_date', now()->toDateString()) }}" required></label>
          <label>پارٹی اکاؤنٹ<select name="party_account_id"><option value="">منتخب کریں</option>@foreach ($accounts as $account)<option value="{{ $account->id }}">{{ $account->name_ur }} ({{ $account->code }})</option>@endforeach</select></label>
          <label>کسٹمر اکاؤنٹ<select name="customer_account_id" required><option value="">منتخب کریں</option>@foreach ($accounts as $account)<option value="{{ $account->id }}">{{ $account->name_ur }} ({{ $account->code }})</option>@endforeach</select></label>
          <label>جنس<select name="item_id" required><option value="">منتخب کریں</option>@foreach ($items as $item)<option value="{{ $item->id }}">{{ $item->name_ur }}</option>@endforeach</select></label>
          <label>نگ<input type="number" name="nag" min="0" step="0.01" value="{{ old('nag', 0) }}" required></label>
          <label>وزن<input type="number" name="wazan" min="0" step="0.01" value="{{ old('wazan') }}"></label>
          <label>ریٹ<input type="number" name="rate" min="0" step="0.01" value="{{ old('rate', 0) }}" required></label>
          <label>مارکہ<input name="marka" value="{{ old('marka') }}"></label>
          <label>گاڑی نمبر<input name="vehicle_no" value="{{ old('vehicle_no') }}"></label>
          <label>کنٹینر نمبر<input name="container_no" value="{{ old('container_no') }}"></label>
        </div><div class="actions"><button type="submit">زیرِ التوا بل محفوظ کریں</button><a class="button" href="{{ route('client.portal.bills.index', ['slug' => $client->slug]) }}">تمام بل</a></div>
      </form>
    </section>
  @elseif (str_starts_with($page, 'vouchers.'))
    <section class="card">
      <form method="POST" action="{{ route($postRoute, ['slug' => $client->slug]) }}">@csrf
        <div class="grid">
          <label>تاریخ<input type="date" name="voucher_date" value="{{ old('voucher_date', now()->toDateString()) }}" required></label>
          @if ($page === 'vouchers.jv')
            <label>ڈیبٹ اکاؤنٹ<select name="debit_account_id" required><option value="">منتخب کریں</option>@foreach ($accounts as $account)<option value="{{ $account->id }}">{{ $account->name_ur }} ({{ $account->code }})</option>@endforeach</select></label>
            <label>کریڈٹ اکاؤنٹ<select name="credit_account_id" required><option value="">منتخب کریں</option>@foreach ($accounts as $account)<option value="{{ $account->id }}">{{ $account->name_ur }} ({{ $account->code }})</option>@endforeach</select></label>
          @else
            <label>اکاؤنٹ<select name="account_id" required><option value="">منتخب کریں</option>@foreach ($accounts as $account)<option value="{{ $account->id }}">{{ $account->name_ur }} ({{ $account->code }})</option>@endforeach</select></label>
            @if ($page === 'vouchers.agrai')
              <label>ریکوری گروپ<select name="recovery_group_id" required><option value="">منتخب کریں</option>@foreach ($recoveryGroups as $group)<option value="{{ $group->id }}">{{ $group->name_ur }}</option>@endforeach</select></label>
            @endif
          @endif
          <label>رقم<input type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required></label>
          <label>تفصیل<input name="narration" value="{{ old('narration') }}"></label>
        </div><div class="actions"><button type="submit">واؤچر پوسٹ کریں</button></div>
      </form>
    </section>
  @elseif ($page === 'settings')
    <section class="card">
      <form method="POST" action="{{ route($postRoute, ['slug' => $client->slug]) }}">@csrf
        <div class="grid">
          <label>ادارے کا نام<input name="firm_name" value="{{ old('firm_name', $settings['firm_name'] ?? $client->name) }}"></label>
          <label>فون<input name="phone" value="{{ old('phone', $settings['phone'] ?? $client->phone) }}"></label>
          <label>پتہ<input name="address" value="{{ old('address', $settings['address'] ?? $client->address) }}"></label>
        </div><div class="actions"><button type="submit">سیٹنگز محفوظ کریں</button></div>
      </form>
    </section>
  @elseif ($page === 'ai-settings')
    <section class="card">
      <p class="muted">API key محفوظ ہونے پر AI چیٹ فعال ہوگی۔ پہلے سے محفوظ key دوبارہ ظاہر نہیں کی جاتی۔</p>
      @if ($aiConfigured)<p>AI key پہلے سے محفوظ ہے۔</p>@endif
      <form method="POST" action="{{ route($postRoute, ['slug' => $client->slug]) }}">@csrf
        <label>AI API key<input type="password" name="ai_api_key" autocomplete="new-password" maxlength="255"></label>
        @if ($aiConfigured)<label class="check"><input type="checkbox" name="remove_ai_api_key" value="1"> محفوظ شدہ key حذف کریں</label>@endif
        <div class="actions"><button type="submit">AI سیٹنگز محفوظ کریں</button></div>
      </form>
    </section>
  @elseif ($page === 'ledger.party')
    <section class="card">
      <form method="GET" action="{{ route('client.portal.ledger.party', ['slug' => $client->slug]) }}" class="grid">
        <label>اکاؤنٹ<select name="account_id"><option value="">تمام اکاؤنٹس</option>@foreach ($accounts as $account)<option value="{{ $account->id }}" @selected(request('account_id') == $account->id)>{{ $account->name_ur }} ({{ $account->code }})</option>@endforeach</select></label>
        <label>شروع تاریخ<input type="date" name="from" value="{{ request('from') }}"></label>
        <label>اختتام تاریخ<input type="date" name="to" value="{{ request('to') }}"></label>
        <div class="actions"><button type="submit">رپورٹ دیکھیں</button></div>
      </form>
    </section>
  @elseif (in_array($page, ['reports.dailybook', 'reports.cash'], true))
    <section class="card">
      <form method="GET" action="{{ url()->current() }}" class="grid">
        <label>شروع تاریخ<input type="date" name="from" value="{{ request('from', now()->toDateString()) }}"></label>
        <label>اختتام تاریخ<input type="date" name="to" value="{{ request('to', now()->toDateString()) }}"></label>
        <div class="actions"><button type="submit">رپورٹ دیکھیں</button></div>
      </form>
    </section>
  @endif

  @if ($rows->isNotEmpty())
    <section class="card table-wrap">
      <table>
        <thead><tr>@foreach (array_keys((array) $rows->first()) as $column)<th>{{ str_replace('_', ' ', $column) }}</th>@endforeach</tr></thead>
        <tbody>
          @foreach ($rows as $row)
            <tr>@foreach ((array) $row as $value)<td>{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</td>@endforeach</tr>
          @endforeach
        </tbody>
      </table>
    </section>
  @elseif (in_array($page, ['accounts.index', 'bills.index', 'reports.dailybook', 'reports.cash', 'reports.balance', 'reports.agrai', 'ledger.party', 'ledger.banam', 'stock.ledger'], true))
    <section class="card muted">اس وقت دکھانے کے لیے کوئی ریکارڈ موجود نہیں۔</section>
  @endif
</main>
</body>
</html>
