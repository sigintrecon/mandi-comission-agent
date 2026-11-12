<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ایڈ نیو کھاتہ — {{ $client->name }}</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--sw:68px;--sh:72px;--blue:#1565c0;--dark:#0d47a1}
body{font-family:'Noto Nastaliq Urdu',serif;background:#b8daff;height:100vh;display:flex;overflow:hidden;direction:rtl}

/* SIDEBARS */
#ls,#rs{position:fixed;top:0;bottom:0;width:var(--sw);background:linear-gradient(180deg,#0d47a1,#1565c0);display:flex;flex-direction:column;z-index:100}
#ls{right:0;box-shadow:-2px 0 8px rgba(0,0,0,.3)}
#rs{left:0;box-shadow:2px 0 8px rgba(0,0,0,.3)}
.s-top{height:60px;display:flex;align-items:center;justify-content:center;background:#0a3880;border-bottom:1px solid rgba(255,255,255,.15);flex-shrink:0}
.s-top img{width:36px;height:36px;object-fit:contain;border-radius:4px}
.s-nav{flex:1;overflow-y:auto;overflow-x:hidden}.s-nav::-webkit-scrollbar{width:0}
.si{display:flex;flex-direction:column;align-items:center;justify-content:center;width:var(--sw);min-height:var(--sh);color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.08);transition:background .15s;padding:6px 4px;cursor:pointer;background:none;border-top:none;border-left:none;border-right:none}
.si:hover,.si.active{background:rgba(255,255,255,.18)}
.si svg{margin-bottom:3px;flex-shrink:0}
.si span{font-size:11px;line-height:1.3;text-align:center}
.s-bot{border-top:1px solid rgba(255,255,255,.15)}

/* MAIN */
#main{margin-right:var(--sw);margin-left:var(--sw);flex:1;display:flex;flex-direction:column;height:100vh;min-height:0;overflow:hidden}

/* TITLE BAR */
.tbar{background:linear-gradient(90deg,#0d47a1,#1565c0);padding:7px 14px;display:flex;align-items:center;gap:10px;border-bottom:2px solid rgba(255,255,255,.12);flex-shrink:0}
.tbar h3{color:#fff;font-size:15px;font-family:inherit;margin-right:8px}
.tbar .lbtn{margin-left:auto;background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.4);color:#fca5a5;padding:3px 12px;border-radius:6px;font-size:12px;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:5px;transition:background .15s}
.tbar .lbtn:hover{background:rgba(239,68,68,.35)}

/* FORM CARD */
.fcard{background:#fff;border:10px groove #1565c0;border-radius:10px;margin:12px;padding:14px;flex:0 0 auto;max-height:calc(100vh - 230px);overflow-y:auto}
legend{font-size:22px;color:#0d47a1;font-family:inherit;font-weight:bold;padding:0 8px;margin-bottom:10px}
.frow{display:flex;flex-wrap:wrap;gap:8px;direction:rtl}
.fg{display:flex;flex-direction:column;gap:3px}
.fg label{font-size:17px;color:#000;font-weight:900;font-family:inherit}
.fg input,.fg select{font-size:18px !important;font-weight:800;padding:5px 8px;border:1px solid #aaa;border-radius:5px;font-family:inherit;width:100%}
.fg input[readonly]{background:#add8e6}
.fg input:focus,.fg select:focus{outline:2px solid #1565c0;border-color:#1565c0}
.w-code{width:110px}
.w-date{width:140px}
.w-type{width:160px}
.w-agrai{width:120px}
.w-sm{width:150px}
.w-md{width:180px}
.w-lg{width:220px}
.w-xl{width:260px}
.w-amt{width:120px}
.w-side{width:140px}

.btn-save{background:#dc3545;color:#fff;border:2px solid #dc3545;padding:7px 18px;border-radius:6px;font-size:17px;font-family:inherit;cursor:pointer;font-weight:bold;transition:background .2s}
.btn-save:hover{background:#b02a37}

/* NOTICES */
.notice{background:#d4edda;color:#155724;padding:8px 14px;border-radius:5px;margin:0 12px 8px;font-size:16px}
.lookup-notice{background:#e8f2ff;color:#174a7e;border-right:4px solid #1565c0;padding:4px 12px;border-radius:4px;margin:0 12px 8px;font-size:14px}
.lookup-notice[hidden]{display:none}
.errors{background:#f8d7da;color:#721c24;padding:8px 14px;border-radius:5px;margin:0 12px 8px}
.errors ul{margin:4px 0 0 16px}

/* TABLE */
.tbl-wrap{margin:12px;overflow:auto;flex:1 1 auto;min-height:0;max-height:none}
table{width:100%;border-collapse:collapse;white-space:nowrap;direction:rtl}
thead{position:sticky;top:0;z-index:10}
th{background:#212529;color:#fff;padding:7px 10px;text-align:right;font-size:14px;font-family:inherit}
td{padding:6px 10px;text-align:right;border-bottom:1px solid #ccc;font-size:14px;background:#17a2b8;color:#fff}
tr:hover td{background:#138496}
.btn-del{background:none;border:none;color:#ff4444;cursor:pointer;padding:4px;display:inline-flex;align-items:center;justify-content:center}
.btn-del svg{width:18px;height:18px}
.btn-del:hover{color:#c62828}
.search-box{margin:0 12px 8px;display:flex;flex-wrap:wrap;gap:8px;direction:rtl;flex:0 0 auto}
.search-box input,.search-box select{padding:6px 10px;border:1px solid #aaa;border-radius:5px;font-size:15px;font-family:inherit;min-width:150px}
.search-box input{width:260px}
.no-results{display:none;padding:12px;text-align:center;background:#fff;color:#555}

@media(max-width:700px){.frow{flex-direction:column}.w-code,.w-date,.w-type,.w-agrai,.w-sm,.w-md,.w-lg,.w-xl,.w-amt,.w-side{width:100%}}
</style>
</head>
<body>

{{-- RIGHT SIDEBAR (vouchers/reports) --}}
<div id="rs">
  <div class="s-top">
    <svg width="26" height="26" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
  </div>
  <div class="s-nav">
    <a href="{{ route('client.portal.vouchers.jv', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg><span>JV</span></a>
    <a href="{{ route('client.portal.vouchers.debit', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/></svg><span>بنام رقم</span></a>
    <a href="{{ route('client.portal.vouchers.credit', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg><span>جمع رقم</span></a>
    <a href="{{ route('client.portal.vouchers.agrai', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>وصولی رقم</span></a>
    <a href="{{ route('client.portal.reports.agrai', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg><span>شیٹ</span></a>
    <a href="{{ route('client.portal.reports.cash', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg><span>کیش بک</span></a>
    <a href="{{ route('client.portal.reports.dailybook', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg><span>روزنامچہ</span></a>
    <a href="{{ route('client.portal.reports.balance', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z"/></svg><span>بیلنس</span></a>
    <a href="{{ route('client.portal.ledger.party', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>کھاتہ پارٹی</span></a>
  </div>
</div>

{{-- LEFT SIDEBAR (main nav) --}}
<div id="ls">
  <div class="s-top"><img src="{{ asset('images/elemeentra-logo.jpeg') }}" alt="Logo"></div>
  <div class="s-nav">
    <a href="{{ route('client.portal.accounts.create', $client->slug) }}" class="si active">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
      <span>نیو کسٹمر</span></a>
    <a href="{{ route('client.portal.items.create', $client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
      <span>نیو جنس</span></a>
    <a href="{{ route('client.portal.bills.sale', $client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg>
      <span>سیل مال</span></a>
    <a href="{{ route('client.portal.bills.khareed', $client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
      <span>خرید بل</span></a>
    <a href="{{ route('client.portal.bills.customer', $client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
      <span>کسٹمر خرید</span></a>
    <a href="{{ route('client.portal.bills.index', $client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.224.72-.101 1.5.247 2.158m0 0a3 3 0 004.985 1.218M7 10h10m-7 3h7m-10.5 3H17a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0017 4.5H7A2.25 2.25 0 004.75 6.75v8.25A2.25 2.25 0 007 17.25z"/></svg>
      <span>بل پرنٹ</span></a>
    <a href="{{ route('client.portal.ledger.banam', $client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.17-.789 3.376 3.376 0 016.34 0z"/></svg>
      <span>بنام گاہک</span></a>
    <a href="{{ route('client.portal.stock.ledger', $client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605"/></svg>
      <span>مال کھاتہ</span></a>
  </div>
  <div class="s-bot">
    <a href="{{ route('client.portal.settings', $client->slug) }}" class="si">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      <span style="font-family:sans-serif;font-size:9px">Settings</span></a>
    <form method="POST" action="/{{ $client->slug }}/logout">@csrf
      <button type="submit" class="si">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
        <span style="font-family:sans-serif;font-size:9px">Logout</span></button></form>
  </div>
</div>

{{-- MAIN --}}
<div id="main">
  <div class="tbar">
    <svg width="18" height="18" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
    <h3>ایڈ نیو کھاتہ</h3>
    <a href="{{ route('client.portal.dashboard', $client->slug) }}" class="lbtn">ڈیش بورڈ</a>
  </div>

  @if(session('success'))
    <div class="notice">{{ session('success') }}</div>
  @endif
  <div id="accountLookupStatus" class="lookup-notice" role="status" aria-live="polite" hidden></div>
  @if($errors->any())
    <div class="errors"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  {{-- FORM --}}
  <div class="fcard">
    <fieldset style="border:none">
      <legend>ایڈ نیو کھاتہ</legend>
      <form id="accountForm" method="POST" action="{{ route('client.portal.accounts.store', $client->slug) }}">
        @csrf
        <div class="frow">
          <div class="fg w-code">
            <label>پارٹی نمبر</label>
            <input name="code" id="code" value="{{ old('code', $nextCode) }}" required maxlength="20" autofocus>
          </div>
          <div class="fg w-date">
            <label>تاریخ</label>
            <input type="date" name="created_date" value="{{ old('created_date', date('Y-m-d')) }}">
          </div>
          <div class="fg w-type">
            <label>ٹائپ</label>
            <select name="account_type_id" id="account_type_id" required>
              @foreach($accountTypes as $type)
                <option value="{{ $type->id }}" @selected(old('account_type_id', $accountTypes->first()->id ?? null) == $type->id)>
                  {{ $type->name_ur }} ({{ $type->name_en }})
                </option>
              @endforeach
            </select>
          </div>
          <div class="fg w-agrai">
            <label>اگراہی</label>
            <select name="recovery_group_id">
              @foreach($recoveryGroups as $g)
                <option value="{{ $g->id }}" @selected(old('recovery_group_id', $recoveryGroups->first()->id ?? null) == $g->id)>
                  {{ $g->name_en === 'Dead' ? 'Dead/Old' : $g->name_en }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="fg w-lg">
            <label>نام انگلش</label>
            <input name="name_en" id="name_en" value="{{ old('name_en') }}" autocomplete="off">
          </div>
          <div class="fg w-lg">
            <label>نام اردو</label>
            <input name="name_ur" id="name_ur" value="{{ old('name_ur') }}" dir="rtl" required>
          </div>
          <div class="fg w-xl">
            <label>پتہ / Address</label>
            <input name="city" id="city" list="cityList" value="{{ old('city') }}" autocomplete="off">
            <datalist id="cityList">
              @foreach($cities as $c)<option value="{{ $c }}">@endforeach
            </datalist>
          </div>
          <div class="fg w-md">
            <label>فون نمبر</label>
            <input name="phone" value="{{ old('phone') }}">
          </div>
          <div class="fg w-sm">
            <label>کمیشن %</label>
            <input type="number" name="commission_pct" min="0" max="100" step="0.01"
                   value="{{ old('commission_pct', 0) }}">
          </div>
          <div class="fg w-xl">
            <label>تفصیلات</label>
            <input name="bank_details" value="{{ old('bank_details') }}">
          </div>
          <div class="fg w-amt">
            <label>اوپننگ بیلنس</label>
            <input type="number" name="opening_balance" min="0" step="0.01"
                   value="{{ old('opening_balance', 0) }}">
          </div>
          <div class="fg w-side">
            <label>جمع / بنام</label>
            <select name="opening_balance_side" id="opening_balance_side" required>
              <option value="jama" @selected(old('opening_balance_side', 'jama') === 'jama')>جمع (Credit)</option>
              <option value="banam" @selected(old('opening_balance_side', 'jama') === 'banam')>بنام (Debit)</option>
            </select>
          </div>
          <div class="fg" style="justify-content:flex-end;padding-top:22px">
            <button type="submit" class="btn-save">محفوظ کریں</button>
          </div>
        </div>
      </form>
    </fieldset>
  </div>

  {{-- SEARCH + TABLE --}}
  <div class="search-box">
    <input type="search" id="searchInput" placeholder="نام، نمبر، فون، پتہ وغیرہ تلاش کریں..." aria-label="کھاتے تلاش کریں">
    <select id="filterAccountType" aria-label="کھاتہ قسم سے فلٹر کریں">
      <option value="">تمام کھاتہ اقسام</option>
      @foreach($accountTypes as $type)
        <option value="{{ $type->id }}">{{ $type->name_ur }} ({{ $type->name_en }})</option>
      @endforeach
    </select>
    <select id="filterBalanceSide" aria-label="جمع یا بنام سے فلٹر کریں">
      <option value="">جمع / بنام سب</option>
      <option value="jama">جمع (Credit)</option>
      <option value="banam">بنام (Debit)</option>
    </select>
  </div>
  <div class="tbl-wrap">
    <table id="accountTable">
      <thead>
        <tr>
          <th>پارٹی نمبر</th>
          <th>تاریخ</th>
          <th>نام انگلش</th>
          <th>نام اردو</th>
          <th>کھاتہ قسم</th>
          <th>اوپننگ بیلنس</th>
          <th>جمع / بنام</th>
          <th>فون نمبر#</th>
          <th>پتہ</th>
          <th>کمیشن</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($accounts as $a)
        @php
          $balanceSide = (float) $a->opening_credit > 0
              ? 'jama'
              : ((float) $a->opening_debit > 0 ? 'banam' : 'jama');
        @endphp
        <tr
          data-search="{{ mb_strtolower(implode(' ', [$a->name_ur, $a->name_en, $a->code, $a->created_at, $a->opening_credit, $a->opening_debit, $a->phone, $a->type_ur, $a->type_en, $a->recovery_group, $a->city, $a->commission_pct])) }}"
          data-account-type="{{ $a->account_type_id }}"
          data-balance-side="{{ $balanceSide }}"
        >
          <td>{{ $a->code }}</td>
          <td>{{ \Carbon\Carbon::parse($a->created_at)->format('Y-m-d') }}</td>
          <td>{{ $a->name_en }}</td>
          <td>{{ $a->name_ur }}</td>
          <td>{{ $a->type_ur }}</td>
          <td>
            @if((float) $a->opening_credit > 0)
              {{ number_format($a->opening_credit,0) }}
            @elseif((float) $a->opening_debit > 0)
              {{ number_format($a->opening_debit,0) }}
            @else
              0
            @endif
          </td>
          <td>{{ $balanceSide === 'jama' ? 'جمع (Credit)' : 'بنام (Debit)' }}</td>
          <td>{{ $a->phone }}</td>
          <td>{{ $a->city }}</td>
          <td>{{ $a->commission_pct }}</td>
          <td>
            <form method="POST" action="{{ route('client.portal.accounts.destroy', ['slug' => $client->slug, 'account' => $a->id]) }}" onsubmit="return confirm('کیا آپ واقعی یہ کھاتہ حذف کرنا چاہتے ہیں؟')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-del" title="حذف" aria-label="حذف">
                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18m-2 0-.867 14.142A2 2 0 0116.138 22H7.862a2 2 0 01-1.995-1.858L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2m-7 4v7m6-7v7"/>
                </svg>
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div id="noAccountResults" class="no-results">کوئی کھاتہ نہیں ملا۔</div>
  </div>
</div>{{-- /main --}}

<script>
const accountForm = document.getElementById('accountForm');
const accountCode = document.getElementById('code');
const accountLookupUrl = @json(route('client.portal.accounts.lookup', ['slug' => $client->slug]));
const today = @json(now()->toDateString());
const lookupStatus = document.getElementById('accountLookupStatus');
let loadedAccountCode = null;
let lookupCheckedCode = null;
let lookupPromise = null;
let lookupPromiseCode = null;
let lookupTimer = null;

function clearAccountFields() {
  [
    'created_date',
    'account_type_id',
    'recovery_group_id',
    'name_en',
    'name_ur',
    'city',
    'phone',
    'commission_pct',
    'bank_details',
    'opening_balance',
    'opening_balance_side',
  ].forEach(function(name) {
    const field = accountForm.elements.namedItem(name);
    if (field) {
      if (field.tagName === 'SELECT') {
        field.selectedIndex = 0;
      } else {
        field.value = name === 'created_date'
          ? today
          : (name === 'opening_balance' || name === 'commission_pct' ? '0' : '');
      }
    }
  });
}

function showLookupStatus(message) {
  lookupStatus.textContent = message;
  lookupStatus.hidden = message === '';
}

async function lookupAccount() {
  const code = accountCode.value.trim();
  if (!code) {
    return null;
  }
  if (loadedAccountCode === code) {
    return true;
  }
  if (lookupCheckedCode === code) {
    return false;
  }
  if (lookupPromiseCode === code && lookupPromise) {
    return lookupPromise;
  }

  lookupPromiseCode = code;
  lookupPromise = fetch(accountLookupUrl + '?' + new URLSearchParams({ code }), {
    headers: { Accept: 'application/json' },
  }).then(function(response) {
    if (!response.ok) {
      throw new Error('Lookup request failed');
    }
    return response.json();
  }).then(function(result) {
    if (accountCode.value.trim() !== code) {
      return null;
    }

    if (!result.account) {
      clearAccountFields();
      loadedAccountCode = null;
      lookupCheckedCode = code;
      showLookupStatus('');
      return false;
    }

    const account = result.account;
    Object.keys(account).forEach(function(name) {
      const field = accountForm.elements.namedItem(name);
      if (field && name !== 'id' && name !== 'code' && name !== 'created_at') {
        field.value = account[name] ?? '';
      }
    });
    loadedAccountCode = code;
    lookupCheckedCode = code;
    showLookupStatus('پرانا کھاتہ مل گیا؛ محفوظ کرنے پر تبدیلیاں اپ ڈیٹ ہوں گی۔');
    return true;
  }).catch(function() {
    showLookupStatus('کھاتے کی تلاش میں مسئلہ ہوا؛ دوبارہ کوشش کریں۔');
    return null;
  }).finally(function() {
    if (lookupPromiseCode === code) {
      lookupPromise = null;
      lookupPromiseCode = null;
    }
  });

  return lookupPromise;
}

accountCode.addEventListener('input', function() {
  clearTimeout(lookupTimer);
  if (loadedAccountCode !== accountCode.value.trim()) {
    loadedAccountCode = null;
    lookupCheckedCode = null;
    clearAccountFields();
    showLookupStatus('');
  }
  lookupTimer = setTimeout(lookupAccount, 350);
});

accountCode.addEventListener('blur', function() {
  clearTimeout(lookupTimer);
  lookupAccount();
});

accountForm.addEventListener('submit', async function(event) {
  const code = accountCode.value.trim();
  if (!code || lookupCheckedCode === code) {
    return;
  }

  event.preventDefault();
  const lookupResult = await lookupAccount();
  if (lookupResult !== null) {
    accountForm.requestSubmit(accountForm.querySelector('.btn-save'));
  }
});

accountForm.addEventListener('keydown', async function(event) {
  if (event.key !== 'Enter' || event.isComposing) {
    return;
  }

  if (event.shiftKey) {
    event.preventDefault();
    accountForm.requestSubmit(accountForm.querySelector('.btn-save'));
    return;
  }

  if (event.target.matches('button[type="submit"]')) {
    return;
  }

  event.preventDefault();
  if (event.target === accountCode) {
    await lookupAccount();
  }

  const fields = Array.from(accountForm.querySelectorAll(
    'input:not([type="hidden"]):not([disabled]), select:not([disabled]), button[type="submit"]:not([disabled])'
  )).filter(function(field) {
    return field.getClientRects().length > 0;
  });
  const currentIndex = fields.indexOf(event.target);
  const nextField = fields[currentIndex + 1];

  if (nextField && nextField.matches('button[type="submit"]')) {
    accountForm.requestSubmit(nextField);
  } else if (nextField) {
    nextField.focus();
  } else {
    accountForm.requestSubmit(accountForm.querySelector('.btn-save'));
  }
});

const accountFilters = [
  document.getElementById('searchInput'),
  document.getElementById('filterAccountType'),
  document.getElementById('filterBalanceSide'),
];

function filterTable() {
  const search = document.getElementById('searchInput').value.trim().toLocaleLowerCase();
  const accountType = document.getElementById('filterAccountType').value;
  const balanceSide = document.getElementById('filterBalanceSide').value;
  let visibleRows = 0;

  document.querySelectorAll('#accountTable tbody tr').forEach(function(row) {
    const matches = row.dataset.search.includes(search)
      && (!accountType || row.dataset.accountType === accountType)
      && (!balanceSide || row.dataset.balanceSide === balanceSide);

    row.style.display = matches ? '' : 'none';
    visibleRows += matches ? 1 : 0;
  });

  document.getElementById('noAccountResults').style.display = visibleRows ? 'none' : 'block';
}

accountFilters.forEach(function(filter) {
  filter.addEventListener('input', filterTable);
  filter.addEventListener('change', filterTable);
});
</script>
</body>
</html>
