<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>بل پرنٹ — {{ $client->name }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <style>
    *{box-sizing:border-box}
    body,html{margin:0;background:#f2f2f2;color:#111;font-family:'Noto Nastaliq Urdu',serif;direction:rtl}
    .button-container{position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 16px;background:#f7f8f8;border-bottom:1px solid #d5dada;direction:ltr}
    .button-group,.profile-mark,.filter{display:flex;align-items:center;gap:7px}
    .profile-mark{margin-right:auto;direction:ltr;font-weight:700;color:#333}
    .profile-mark img{width:38px;height:38px;object-fit:contain}
    .button-container button,.button-container select{min-height:36px;border:1px solid #9da6a8;border-radius:4px;background:#fff;color:#111;padding:6px 12px;font:700 13px Arial,sans-serif;cursor:pointer}
    .button-container button:hover{background:#e8eeee}
    .button-container button:disabled{opacity:.45;cursor:not-allowed}
    .bill-count{min-width:76px;text-align:center;font:700 13px Arial,sans-serif}
    .filter select{min-width:170px}
    #all-bills-container{width:210mm;max-width:100%;margin:16px auto;padding:10mm;background:#fff}
    .bill-container{display:none;min-height:270mm;margin:0 0 16px;padding:12mm;border:1px solid #d0d0d0;background:#fff;box-shadow:0 0 10px #00000014;page-break-after:always}
    .bill-container.active{display:block}
    .company-header{display:flex;flex-direction:column;align-items:center;gap:3px;text-align:center;margin-bottom:12px;padding:8px 0 10px;border-bottom:1px solid #d5d5d5;line-height:1.8}
    .company-name{margin:0;padding:0 6px;color:#7a1515;font-size:34px;font-weight:700;line-height:1.8}
    .company-title{margin:2px 0 0;padding-top:3px;font-size:17px;font-weight:700;line-height:1.8}
    .company-address{margin-top:2px;font-size:14px;font-weight:700;line-height:1.8}
    .contact-row{display:flex;justify-content:center;flex-wrap:wrap;gap:16px;margin-bottom:5px;direction:ltr;font:700 14px Arial,sans-serif}
    .party-summary-box{display:grid;grid-template-columns:1fr 2fr 1fr;grid-template-areas:"invoice name date" "nag name container";align-items:center;margin-top:12px;border:1px solid #444;background:#eee;direction:ltr}
    .corner{padding:7px 10px;font-size:13px;font-weight:700;direction:rtl;text-align:center}
    .corner-invoice{grid-area:invoice}.corner-date{grid-area:date}.corner-nag{grid-area:nag}.corner-container{grid-area:container}
    .pname{grid-area:name;display:flex;min-height:72px;align-items:center;justify-content:center;padding:8px;border-left:1px solid #aaa;border-right:1px solid #aaa;color:#173c8c;text-align:center;font-size:23px;font-weight:700;direction:rtl}
    table{width:100%;border-collapse:collapse;margin-top:10px;font-size:14px}
    th,td{border:1px solid #222;padding:5px 8px;text-align:center}
    th{background:#f1f1f1}
    .header-1{margin-top:8px}
    .header-1 th{background:#e6e6e6}
    .tables-container{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-top:18px;direction:rtl}
    .detail{width:60%}
    .expense{width:35%}
    .detail h3,.expense h3{margin:0 0 4px;text-align:center;font-size:18px}
    .expense table th{text-align:right}
    .num{font-family:Arial,sans-serif;font-variant-numeric:tabular-nums;font-weight:700}
    .total-row td,.total-row th{background:#f4e3a8;font-weight:700}
    .bill-total{text-align:center;margin:8px 0 0;font-size:20px;font-weight:700}
    .footer{margin-top:26px;padding:8px 0;border-top:1px solid #999;text-align:center;font:16px Arial,sans-serif;direction:ltr}
    .empty-note{padding:20px;color:#666}
    @page{size:A4 portrait;margin:5mm}
    @media print{
      body,html{background:#fff!important}
      .button-container{display:none!important}
      #all-bills-container{width:100%;max-width:none;margin:0;padding:0}
      .bill-container{display:none!important;position:relative;height:287mm;min-height:287mm;margin:0;padding:3mm 3mm 16mm;border:0;box-shadow:none;background:#fff!important;break-inside:avoid;page-break-inside:avoid;break-after:page;page-break-after:always}
      .bill-container.active,body.print-all .bill-container{display:block!important}
      .bill-container:last-child{break-after:auto;page-break-after:auto}
      .company-header{gap:1.5mm;margin-bottom:5mm;padding:3mm 0 4mm;border-top:0;border-bottom:1px solid #d5d5d5;line-height:1.9}
      .company-name{margin:0 0 1mm;padding:0 2mm;color:#111;font-size:21pt;line-height:1.9}
      .company-title{margin:1mm 0 0;padding-top:1mm;font-size:12pt;line-height:1.9}
      .company-address{margin-top:1mm;font-size:10pt;line-height:1.9}
      .contact-row{gap:8px;margin-bottom:2mm;font-size:9pt}
      .party-summary-box{margin-top:4mm}
      .corner{padding:2px 4px;font-size:8pt;line-height:1.55}
      .pname{min-height:42px;padding:3px;background:transparent;color:#111;font-size:14pt;line-height:1.55}
      table{margin-top:4px;font-size:8pt;line-height:1.5}
      th,td{border:1px solid #111;padding:1mm 1.2mm;line-height:1.55;vertical-align:middle}
      .expense table th{padding-right:5mm;padding-left:1.5mm;text-align:right}
      .tables-container{display:grid!important;grid-template-columns:minmax(0,1.65fr) minmax(0,.85fr);gap:3mm;margin-top:5mm;direction:rtl;align-items:start}
      .detail,.expense{width:auto;min-width:0;break-inside:avoid;page-break-inside:avoid}
      .detail h3,.expense h3{margin-bottom:1px;font-size:11pt;line-height:1.2}
      .bill-total{margin:2mm 0 0;text-align:center;font-size:11pt;line-height:1.35}
      .footer{position:absolute;bottom:4mm;left:5mm;right:5mm;margin:0;padding:2mm 0 0;border-top:1px solid #999;background:#fff;text-align:center;font-size:11pt;line-height:1.5}
    }
    @media(max-width:760px){.button-container{position:static;flex-direction:column;align-items:stretch}.button-group{justify-content:center;flex-wrap:wrap}.profile-mark{display:none}.filter{justify-content:center}#all-bills-container{width:100%;margin:0;padding:10px}.bill-container{min-height:auto;padding:18px}.company-name{font-size:24px}.tables-container{flex-direction:column}.detail,.expense{width:100%}}
  </style>
</head>
<body>
  <div class="button-container">
    <div class="button-group">
      <button type="button" class="close-btn" onclick="history.back()">Close</button>
      <button type="button" onclick="printCurrentBill()">PDF</button>
      <button type="button" onclick="downloadJpg()">JPG</button>
      <button type="button" onclick="printAllBills()">Print</button>
      <button type="button" id="prev-btn" onclick="showPreviousBill()">Previous</button>
      <span class="bill-count" id="bill-count"></span>
      <button type="button" id="next-btn" onclick="showNextBill()">Next</button>
    </div>
    <div class="profile-mark"><img src="{{ asset('images/elemeentra-logo.jpeg') }}" alt=""><span>{{ $firmName }}</span></div>
  </div>

  <div id="all-bills-container">
    @foreach($bills as $bill)
      <article class="bill-container {{ $loop->first ? 'active' : '' }}" data-invoice="{{ $bill->inv_no }}">
        @php
          $chargesByName = $bill->charges->keyBy('name_en');
          $expenseLabels = [
              'Karaya' => 'کرایہ',
              'Commission' => 'کمیشن',
              'Kharch' => 'کسٹم',
              'Mazdoori' => 'مزدوری',
              'Munshiana' => 'منشیانہ',
              'Market Fee' => 'مارکیٹ فیس',
              'Store Rent' => 'سٹور کرایہ',
          ];
        @endphp
        <header class="company-header">
          @if($firmPhones->isNotEmpty())
            <div class="contact-row">
              @foreach($firmPhones as $phone)<span>{{ $phone }}</span>@endforeach
            </div>
          @endif
          <h1 class="company-name">{{ $firmName }}</h1>
          <div class="company-title">سبزی و فروٹ کمیشن ایجنٹس</div>
          @if($firmAddress)<div class="company-address">{{ $firmAddress }}</div>@endif
        </header>

        <div class="party-summary-box">
          <div class="corner corner-invoice">بل نمبر: <span class="num">{{ $bill->inv_no }}</span></div>
          <div class="corner corner-date">تاریخ: <span class="num">{{ \Illuminate\Support\Carbon::parse($bill->bill_date)->format('d-m-Y') }}</span></div>
          <div class="pname">{{ $bill->party_name ?: '—' }}</div>
          <div class="corner corner-nag">ٹوٹل نگ: <span class="num">{{ number_format((float) $bill->lot_nag, 0) }}</span></div>
          <div class="corner corner-container">کنٹینر نمبر: {{ $bill->container_no ?: '—' }}</div>
        </div>

        <div class="tables-container">
          <section class="detail">
            <h3>تفصیل مال</h3>
            <table><thead><tr><th>نگ</th><th>جنس</th><th>ریٹ</th><th>رقم</th></tr></thead><tbody>
              @forelse($bill->lines as $line)
                <tr>
                  <td class="num">{{ number_format((float) $line->nag, 0) }}</td>
                  <td>{{ $line->item_name ?: '—' }}</td>
                  <td class="num">{{ number_format((float) $line->rate, 2) }}</td>
                  <td class="num">{{ number_format((float) $line->party_amount, 2) }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="empty-note">اس بل میں کوئی تفصیلی اندراج نہیں۔</td></tr>
              @endforelse
              <tr class="total-row"><th class="num">{{ number_format((float) $bill->lot_nag, 0) }}</th><th>کل رقم</th><td></td><td class="num">{{ number_format((float) $bill->gross_amount, 2) }}</td></tr>
            </tbody></table>
          </section>

          <section class="expense">
            <h3>اخراجات</h3>
            <table><tbody>
              @foreach($expenseLabels as $nameEn => $label)
                @php $charge = $chargesByName->get($nameEn); @endphp
                <tr><th>{{ $label }}</th><td class="num">{{ number_format((float) ($charge->amount ?? 0), 2) }}</td></tr>
              @endforeach
              <tr><th>خام رقم</th><td class="num">{{ number_format((float) $bill->gross_amount, 2) }}</td></tr>
              <tr><th>کل خرچہ</th><td class="num">{{ number_format((float) $bill->expense_amount, 2) }}</td></tr>
              <tr class="total-row"><th>صافی رقم</th><td class="num">{{ number_format((float) $bill->safi_amount, 2) }}</td></tr>
            </tbody></table>
            <p class="bill-total">کل جمع رقم: <span class="num">{{ number_format((float) $bill->safi_amount, 2) }}</span></p>
          </section>
        </div>

        <footer class="footer">Software by Elemeentra | WhatsApp:03260161325</footer>
      </article>
    @endforeach
  </div>

  <script>
    const billContainer = document.getElementById('all-bills-container');
    let billPages = Array.from(billContainer.querySelectorAll('.bill-container'));
    let currentBillIndex = 0;

    function showBill(index){
      if(!billPages.length) return;
      currentBillIndex = Math.max(0, Math.min(index, billPages.length - 1));
      billPages.forEach((bill, billIndex) => bill.classList.toggle('active', billIndex === currentBillIndex));
      document.getElementById('bill-count').textContent = `Bill ${currentBillIndex + 1} of ${billPages.length}`;
      document.getElementById('prev-btn').disabled = currentBillIndex === 0;
      document.getElementById('next-btn').disabled = currentBillIndex === billPages.length - 1;
    }

    function showPreviousBill(){ showBill(currentBillIndex - 1); }
    function showNextBill(){ showBill(currentBillIndex + 1); }
    function printCurrentBill(){ document.body.classList.remove('print-all'); window.print(); }
    function printAllBills(){
      document.body.classList.add('print-all');
      window.print();
      window.addEventListener('afterprint', () => document.body.classList.remove('print-all'), {once:true});
    }

    async function downloadJpg(){
      const currentBill = billPages[currentBillIndex];
      if(!currentBill) return;
      if(typeof window.html2canvas !== 'function'){
        alert('JPG لائبریری لوڈ نہیں ہوسکی۔ انٹرنیٹ کنکشن چیک کریں۔');
        return;
      }

      try{
        await document.fonts.ready;
        const canvas = await window.html2canvas(currentBill, {
          scale: 2,
          useCORS: true,
          backgroundColor: '#fff',
          logging: false,
        });
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.95));
        if(!blob){
          throw new Error('JPG image could not be created.');
        }

        const downloadUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `invoice_${currentBill.dataset.invoice}.jpg`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        setTimeout(() => URL.revokeObjectURL(downloadUrl), 1500);
      }catch(error){
        console.error('JPG export failed.', error);
        alert('JPG تیار نہیں ہوسکی۔ دوبارہ کوشش کریں۔');
      }
    }

    showBill(0);
  </script>
</body>
</html>
