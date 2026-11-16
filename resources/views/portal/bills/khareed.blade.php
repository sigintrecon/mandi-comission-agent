<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>خرید بل — {{ $client->name }}</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--sw:60px}
body{font-family:'Noto Nastaliq Urdu',serif;background:#b8daff;height:100vh;display:flex;overflow:hidden;direction:rtl}
/* ── Sidebars ── */
#ls,#rs{position:fixed;top:0;bottom:0;width:var(--sw);background:linear-gradient(180deg,#0d47a1,#1565c0);display:flex;flex-direction:column;z-index:100}
#ls{right:0;box-shadow:-2px 0 8px rgba(0,0,0,.3)}
#rs{left:0;box-shadow:2px 0 8px rgba(0,0,0,.3)}
.s-top{height:60px;display:flex;align-items:center;justify-content:center;background:#0a3880;border-bottom:1px solid rgba(255,255,255,.15);flex-shrink:0}
.s-top img{width:36px;height:36px;object-fit:contain;border-radius:4px}
.s-nav{flex:1;overflow-y:auto;overflow-x:hidden}.s-nav::-webkit-scrollbar{width:0}
.si{display:flex;flex-direction:column;align-items:center;justify-content:center;width:var(--sw);min-height:68px;color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.08);transition:background .15s;padding:6px 4px;cursor:pointer;background:none;border-top:none;border-left:none;border-right:none}
.si:hover,.si.active{background:rgba(255,255,255,.18)}
.si svg{margin-bottom:3px;flex-shrink:0}
.si span{font-size:11px;line-height:1.3;text-align:center}
.s-bot{border-top:1px solid rgba(255,255,255,.15)}
/* ── Main ── */
#main{margin-right:var(--sw);margin-left:var(--sw);flex:1;display:flex;flex-direction:column;height:100vh;min-height:0;overflow:hidden}
.tbar{height:36px;background:#b8d9fb;padding:3px 8px;display:flex;align-items:center;gap:4px;border-bottom:1px solid #d5e5f7;flex-shrink:0;direction:ltr}
.bill-search-form{display:flex;align-items:center;gap:4px}
.bill-search{height:28px;width:245px;border:1px solid #888;padding:3px 6px;font:700 15px Arial,sans-serif}
.bill-search-button{height:28px;width:34px;border:1px solid #888;background:#e8f2fd;font-size:17px;line-height:1;cursor:pointer}
.notice{background:#d4edda;color:#155724;padding:8px 14px;border-radius:5px;margin:4px 10px;font-size:16px}
.errors{background:#f8d7da;color:#721c24;padding:8px 14px;border-radius:5px;margin:4px 10px}
.errors ul{margin:4px 0 0 16px}
/* ── Scroll body ── */
.body-scroll{flex:1;overflow-y:auto;padding:0 7px 8px;display:flex;flex-direction:column;gap:0;background:#b8daff}
.notice{margin:2px 4px;padding:3px 8px;font-size:13px}
.errors{margin:2px 4px;padding:3px 8px;font-size:13px}
.sale-caption{height:22px;padding:0 4px;font-size:12px;line-height:22px;color:#222}
#billForm{display:flex;flex-direction:column;gap:0}
.hcard{background:transparent;border:0;min-width:0;padding:0 3px 3px;margin:0}
.frow{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:0 2px;align-items:end}
.fg{display:flex;min-width:0;flex-direction:column;gap:0}
.fg label{height:24px;font-size:15px;line-height:24px;color:#111;font-weight:700;font-family:inherit;text-align:center;white-space:nowrap}
.header-grid .fg label{margin-bottom:4px}
.fg input,.fg select{height:29px;min-width:0;width:100%;font-size:15px;font-weight:800;padding:2px 5px;border:1px solid #d0d0d0;border-radius:0;font-family:inherit;text-align:right;background:#fff}
.fg input:focus,.fg select:focus{outline:2px solid #1565c0;outline-offset:-2px}
.fg input[readonly]{background:#add8e6}
.head-inv{grid-column:span 1}.head-date{grid-column:span 2}.head-party{grid-column:span 2}
.head-marfat{grid-column:span 2}.head-marka{grid-column:span 1}.head-nag{grid-column:span 1}
.head-vehicle{grid-column:span 1}.head-container{grid-column:span 2}
.line-entry{position:relative;padding-bottom:2px}
#newLineRow{grid-template-columns:190px 70px 75px 75px 120px 70px 90px 95px 40px;gap:0 3px;justify-content:start}
#newLineRow .fg label{height:24px;font-size:15px;line-height:24px}
#newLineRow .fg input{height:29px;font-size:14px;padding:2px 3px}
#newLineRow .customer-field{grid-column:auto}
.field-code{display:block;min-height:14px;height:14px;padding:0 2px;color:#24527a;font-family:Arial,sans-serif;font-size:10px;font-weight:700;line-height:14px;text-align:right}
#newLineRow .fg label{height:28px;line-height:16px}
#newLineRow .fg label .field-code{height:12px;min-height:12px;line-height:12px}
.btn-add-line{height:29px;width:29px;min-width:29px;background:#28a745;color:#fff;border:0;border-radius:2px;padding:0;font-size:20px;line-height:29px;cursor:pointer;font-family:Arial,sans-serif;font-weight:bold}
.btn-add-line:hover{background:#218838}
.lot-notice{display:none;position:absolute;top:100%;right:3px;z-index:10;width:calc(100% - 271px);min-height:40px;margin:0;padding:6px 8px;background:#fff3cd;color:#664d03;border-right:4px solid #ffc107;font-size:13px;line-height:1.8}
.lot-notice.visible{display:block}
.work-area{display:grid;grid-template-columns:minmax(0,1fr) 265px;gap:6px;min-height:235px;direction:rtl}
.work-area.has-lot-notice .lines-body{margin-top:40px}
.lines-wrap{grid-column:1;min-width:0;overflow:auto;background:transparent}
.lines-body{max-height:210px;min-width:850px;overflow:auto}
.line-row{display:flex;gap:5px;padding:3px 5px;min-height:36px;border:0;border-bottom:1px solid #f1d8bc;align-items:center;background:#ffe4c4}
.line-row:hover{background:#ffdbad}
.line-row input{height:30px;min-width:0;font-size:14px;font-weight:800;padding:2px 4px;border:1px solid #f0d2b2;border-radius:2px;font-family:inherit;text-align:right;background:#ffe4c4}
.line-row input:focus{outline:2px solid #1565c0;background:#fff}
.line-row input[readonly]{background:#add8e6}
.lr-date{width:96px}.lr-date::-webkit-calendar-picker-indicator{display:none}.lr-cname{width:180px}.lr-nag{width:60px}.lr-waz{width:60px}
.lr-rate{width:70px}.lr-jins{width:110px}.lr-lag{width:60px}.lr-t2rate{width:85px}.lr-amt{width:95px}
.btn-del-line{height:32px;min-width:32px;background:#dc3545;border:0;border-radius:3px;color:#fff;font-size:17px;cursor:pointer}
.bottom-grid{display:flex;gap:0;min-width:0}
.totals-card{order:0;flex:1;min-width:0;background:transparent;border:0;padding:0 5px}
.totals-card .totals-fields{display:grid;grid-template-columns:repeat(7,minmax(52px,1fr));gap:4px;align-items:end}
.tot-row{display:flex;min-width:0;flex-direction:column;gap:0;margin:0}
.tot-row label{min-width:0;height:24px;font-size:13px;line-height:24px;font-weight:700;text-align:center;white-space:nowrap}
.tot-row input{width:100%;height:30px;font-size:15px;font-weight:800;padding:2px 5px;border:0;border-radius:0;font-family:inherit;text-align:right;background:#add8e6}
.tot-row input[readonly]{background:#add8e6}
.charges-card{grid-column:2;grid-row:1;background:transparent;border:0;border-radius:0;padding:0;min-width:0;margin-top:-8px}
.charge-row{display:grid;grid-template-columns:94px 80px 80px;align-items:center;gap:3px;margin:0 0 2px}
.charge-row label{font-size:12px;line-height:22px;white-space:nowrap}
.charge-row input{width:100%;height:24px;min-width:0;font-size:13px;font-weight:700;padding:2px 4px;border:1px solid #777;border-radius:0;font-family:Arial,sans-serif;text-align:right;background:#fff}
.charge-row .c-amt{background:#add8e6;border-color:#567586}
.btn-row{display:flex;gap:4px;margin:4px 0 0;flex-wrap:wrap}
.btn-complete,.btn-pending{border:0;border-radius:3px;padding:3px 8px;font-size:13px;font-family:inherit;cursor:pointer;font-weight:bold;white-space:nowrap}
.btn-complete{background:#ffc107;color:#212529}
.btn-pending{background:#28a745;color:#fff}
.btn-cancel-edit,.btn-edit-bill{display:inline-block;border:0;border-radius:3px;background:#0d6efd;color:#fff;padding:3px 8px;font-size:13px;font-family:inherit;font-weight:bold;text-decoration:none;white-space:nowrap}
.btn-cancel-edit{background:#6c757d}
.bill-list-toolbar{min-height:42px;margin-top:5px;padding:4px 8px;display:flex;align-items:center;justify-content:space-between;gap:8px;background:#212529;color:#fff}
.bill-list-toolbar label{font-size:13px;white-space:nowrap}
.bill-list-toolbar input{vertical-align:middle}
.bill-list-actions{display:flex;gap:3px;flex-wrap:wrap}
.bill-list-actions button,.bill-list-actions a{border:0;border-radius:3px;background:#0d6efd;color:#fff;padding:5px 10px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none}
.bill-list-actions button:hover,.bill-list-actions button.active,.bill-list-actions a:hover{background:#084298}
.bill-list-actions a:first-child{background:#28a745}
.bills-tbl-wrap{background:transparent;border:0;border-radius:0;overflow:auto}
table{width:100%;border-collapse:collapse;white-space:nowrap}
thead{position:sticky;top:0;z-index:5}
th{background:#212529;color:#fff;padding:7px 10px;text-align:right;font-size:13px;font-family:inherit}
td{padding:6px 10px;text-align:right;border-bottom:1px solid #ccc;font-size:13px;background:#17a2b8;color:#fff}
tr:hover td{background:#138496}
.badge-pending{background:#ffc107;color:#212529;padding:1px 6px;border-radius:4px;font-size:12px}
.badge-posted{background:#28a745;color:#fff;padding:1px 6px;border-radius:4px;font-size:12px}
@media(max-width:1100px){
  #ls,#rs{width:48px;--sw:48px}
  #main{margin-right:48px;margin-left:48px}
  .work-area{grid-template-columns:minmax(0,1fr) 265px}
  .head-party{grid-column:span 2}.head-marfat{grid-column:span 2}
  .head-marka{grid-column:span 2}.head-nag{grid-column:span 2}
}
@media(max-width:760px){
  .body-scroll{overflow-x:auto}
  .head-party,.head-marfat{grid-column:span 3}
  .head-marka,.head-nag,.head-vehicle,.head-container{grid-column:span 2}
  #newLineRow{min-width:900px}
  .work-area{grid-template-columns:minmax(0,1fr)}
  .charges-card{grid-column:1;grid-row:2}
  .charges-card .charge-row{grid-template-columns:100px 1fr 1fr}
  .charges-card{margin-top:0}
  .lot-notice{position:static;width:auto;margin:0 3px 3px}
  .work-area.has-lot-notice .lines-body{margin-top:0}
  .totals-card .totals-fields{grid-template-columns:repeat(4,minmax(52px,1fr))}
}
</style>
</head>
<body>

{{-- ══ Right Sidebar (Vouchers/Reports) ══ --}}
<div id="rs">
  <div class="s-top"><svg width="26" height="26" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg></div>
  <div class="s-nav">
    <a href="{{ route('client.portal.vouchers.jv', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg><span>JV</span></a>
    <a href="{{ route('client.portal.vouchers.debit', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/></svg><span>بنام رقم</span></a>
    <a href="{{ route('client.portal.vouchers.credit', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg><span>جمع رقم</span></a>
    <a href="{{ route('client.portal.vouchers.agrai', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>وصولی رقم</span></a>
    <a href="{{ route('client.portal.reports.agrai', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg><span>شیٹ</span></a>
    <a href="{{ route('client.portal.reports.cash', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg><span>کیش بک</span></a>
    <a href="{{ route('client.portal.reports.dailybook', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg><span>روزنامچہ</span></a>
    <a href="{{ route('client.portal.reports.balance', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.988 0 01-2.031.352 5.989 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z"/></svg><span>بیلنس</span></a>
    <a href="{{ route('client.portal.ledger.party', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>کھاتہ پارٹی</span></a>
  </div>
</div>

{{-- ══ Left Sidebar (Main Nav) ══ --}}
<div id="ls">
  <div class="s-top"><img src="{{ asset('images/elemeentra-logo.jpeg') }}" alt="Logo"></div>
  <div class="s-nav">
    <a href="{{ route('client.portal.accounts.create', $client->slug) }}" class="si"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg><span>نیو کسٹمر</span></a>
    <a href="{{ route('client.portal.items.create', $client->slug) }}" class="si"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg><span>نیو جنس</span></a>
    <a href="{{ route('client.portal.bills.sale', $client->slug) }}" class="si"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg><span>سیل مال</span></a>
    <a href="{{ route('client.portal.bills.khareed', $client->slug) }}" class="si active"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg><span>خرید بل</span></a>
    <a href="{{ route('client.portal.bills.customer', $client->slug) }}" class="si"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg><span>کسٹمر خرید</span></a>
    <a href="{{ route('client.portal.bills.index', $client->slug) }}" class="si"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.224.72-.101 1.5.247 2.158m0 0a3 3 0 004.985 1.218M7 10h10m-7 3h7"/></svg><span>بل پرنٹ</span></a>
    <a href="{{ route('client.portal.ledger.banam', $client->slug) }}" class="si"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.17-.789 3.376 3.376 0 016.34 0z"/></svg><span>بنام گاہک</span></a>
    <a href="{{ route('client.portal.stock.ledger', $client->slug) }}" class="si"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5"/></svg><span>مال کھاتہ</span></a>
  </div>
  <div class="s-bot">
    <a href="{{ route('client.portal.settings', $client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span style="font-family:sans-serif;font-size:9px">Settings</span></a>
    <form method="POST" action="/{{ $client->slug }}/logout">@csrf
      <button type="submit" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg><span style="font-family:sans-serif;font-size:9px">Logout</span></button></form>
  </div>
</div>

{{-- ══ Main Content ══ --}}
<div id="main">
  <div class="tbar">
    <form method="GET" action="{{ route('client.portal.bills.khareed', $client->slug) }}" class="bill-search-form">
      <input type="search" class="bill-search" name="search" value="{{ $billSearch }}" placeholder="search invoice" aria-label="search invoice">
      <button type="submit" class="bill-search-button" aria-label="تلاش">🔎</button>
    </form>
  </div>

  <div class="body-scroll">

    <div class="sale-caption">مال خرید کھاتہ</div>

    {{-- Flash messages --}}
    @if(session('success'))
      <div class="notice">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="errors"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('client.portal.bills.khareed.store', $client->slug) }}" id="billForm">
      @csrf
      <input type="hidden" name="bill_id" id="bill_id" value="{{ $editableBill->id ?? '' }}">

      <div class="hcard">
        <div class="frow header-grid">
          <div class="fg head-inv"><label>انوائس</label>
            <input class="w-inv" type="text" name="inv_no" id="inv_no" value="{{ old('inv_no', $nextInv) }}" required>
          </div>
          <div class="fg head-date"><label>تاریخ</label>
            <input class="w-date" type="date" name="bill_date" id="bill_date" value="{{ old('bill_date', date('Y-m-d')) }}" required>
          </div>
          <div class="fg head-party"><label id="lbl_party">بیوپاری</label>
            <input class="w-party" type="text" id="party_code_input" list="party_list"
                   placeholder="کوڈ یا نام" autocomplete="off"
                   value="{{ old('party_name_display','') }}">
            <input type="hidden" name="party_account_id" id="party_account_id" value="{{ old('party_account_id','') }}">
            <datalist id="party_list">
              @foreach($parties as $p)
                <option value="{{ $p->code }}" data-id="{{ $p->id }}" data-name="{{ $p->name_ur }}">{{ $p->name_ur }} ({{ $p->city }})</option>
              @endforeach
            </datalist>
          </div>
          <div class="fg head-marfat"><label>معرفت</label>
            <input class="w-marfat" type="text" id="marfat_input" list="marfat_list"
                   placeholder="معرفت" autocomplete="off"
                   value="{{ old('marfat_name_display','') }}">
            <input type="hidden" name="marfat_account_id" id="marfat_account_id" value="{{ old('marfat_account_id','') }}">
            <datalist id="marfat_list">
              @foreach($marfatList as $m)
                <option value="{{ $m->code }}" data-id="{{ $m->id }}" data-name="{{ $m->name_ur }}">{{ $m->name_ur }}</option>
              @endforeach
            </datalist>
          </div>
          <div class="fg head-marka"><label>مارکہ</label>
            <input class="w-marka" type="text" name="marka" list="marka_list" value="{{ old('marka','') }}">
            <datalist id="marka_list">
              @foreach($markaList as $mk)<option value="{{ $mk }}">@endforeach
            </datalist>
          </div>
          <div class="fg head-nag"><label>کل نگ</label>
            <input class="w-nag" type="number" id="lot_nag_input" min="0" step="1" placeholder="کل نگ">
          </div>
          <div class="fg head-vehicle"><label>گاڑی نمبر</label>
            <input class="w-veh" type="text" name="vehicle_no" value="{{ old('vehicle_no','') }}">
          </div>
          <div class="fg head-container"><label>کنٹینر نمبر</label>
            <input class="w-cont" type="text" name="container_no" value="{{ old('container_no','') }}">
          </div>
        </div>
        <input type="hidden" name="item_id" id="item_id" value="{{ old('item_id','') }}">
        <datalist id="item_list">
          @foreach($items as $it)
            <option value="{{ $it->code }}" data-id="{{ $it->id }}" data-name="{{ $it->name_ur }}"
              data-com="{{ $it->default_commission_pct }}"
              data-maz="{{ $it->default_mazdoori_rate }}"
              data-mkt="{{ $it->default_market_fee_rate }}"
              data-mun="{{ $it->default_munshiana_rate }}"
              data-lag="{{ $it->default_lagana }}">{{ $it->name_ur }}</option>
          @endforeach
        </datalist>
      </div>

      <div class="hcard line-entry">
        <div class="frow" id="newLineRow">
          <div class="fg customer-field"><label>نام<span class="field-code" id="customerCodeDisplay"></span></label>
            <input type="text" id="nl_cname" list="cust_list" placeholder="کوڈ یا نام" autocomplete="off">
            <input type="hidden" id="nl_cid">
            <datalist id="cust_list">
              @foreach($customers as $c)
                <option value="{{ $c->code }}">{{ $c->name_ur }} ({{ $c->city }})</option>
                <option value="{{ $c->name_ur }}">{{ $c->code }} ({{ $c->city }})</option>
              @endforeach
            </datalist>
          </div>
          <div class="fg"><label>نگ</label><input type="number" id="nl_nag" min="0" step="1" placeholder="0"></div>
          <div class="fg"><label>وزن</label><input type="number" id="nl_waz" min="0" step="any" placeholder="0"></div>
          <div class="fg"><label>ریٹ</label><input type="number" id="nl_rate" min="0" step="any" placeholder="0"></div>
          <div class="fg"><label>جنس<span class="field-code" id="itemCodeDisplay"></span></label>
            <input type="text" id="nl_jins" list="item_list2" placeholder="جنس" autocomplete="off">
            <datalist id="item_list2">
              @foreach($items as $it)
                <option value="{{ $it->code }}">{{ $it->name_ur }}</option>
                <option value="{{ $it->name_ur }}">{{ $it->code }}</option>
              @endforeach
            </datalist>
          </div>
          <div class="fg"><label>لگانا</label><input type="number" id="nl_lag" min="0" step="any" value="10"></div>
          <div class="fg"><label>بپاری ریٹ</label><input type="number" id="nl_t2rate" min="0" step="any" placeholder="0"></div>
          <div class="fg"><label>ٹوٹل رقم</label><input type="number" id="nl_amt" readonly placeholder="0"></div>
          <div class="fg add-line-field" style="justify-content:flex-end;padding-bottom:0">
            <button type="button" class="btn-add-line" id="btnAddLine" aria-label="لائن شامل کریں">+</button>
          </div>
        </div>
        <div id="lotNotice" class="lot-notice" role="status" aria-live="polite"></div>
      </div>

      <div class="work-area">
        <div class="lines-wrap">
          <div class="lines-body" id="linesBody"></div>
        </div>

        <div class="charges-card">
          <div class="charge-row">
            <label>کمیشن %</label>
            <input type="number" name="charges[commission_pct]" id="c_comm_pct" min="0" max="100" step="0.1" value="0" oninput="calcCharges()">
            <input type="text" class="c-amt" id="c_comm_amt" readonly>
          </div>
          <div class="charge-row">
            <label>کرایہ</label>
            <input type="number" name="charges[karaya]" id="c_karaya" min="0" value="0" oninput="calcCharges()">
            <input type="text" class="c-amt" id="c_karaya_amt" readonly>
          </div>
          <div class="charge-row">
            <label>خرچہ</label>
            <input type="number" name="charges[kharch]" id="c_kharch" min="0" value="0" oninput="calcCharges()">
            <input type="text" class="c-amt" id="c_kharch_amt" readonly>
          </div>
          <div class="charge-row">
            <label>مزدوری</label>
            <input type="number" name="charges[mazdoori]" id="c_maz" min="0" value="0" oninput="calcCharges()">
            <input type="text" class="c-amt" id="c_maz_amt" readonly>
          </div>
          <div class="charge-row">
            <label>مارکیٹ</label>
            <input type="number" name="charges[market]" id="c_mkt" min="0" value="0" oninput="calcCharges()">
            <input type="text" class="c-amt" id="c_mkt_amt" readonly>
          </div>
          <div class="charge-row">
            <label>منشیانہ</label>
            <input type="number" name="charges[munshiana]" id="c_mun" min="0" value="0" oninput="calcCharges()">
            <input type="text" class="c-amt" id="c_mun_amt" readonly>
          </div>
          <div class="charge-row">
            <label>سٹور کرایہ</label>
            <input type="number" name="charges[stor_karaya]" id="c_stor" min="0" value="0" oninput="calcCharges()">
            <input type="text" class="c-amt" id="c_stor_amt" readonly>
          </div>
          <div class="charge-row">
            <label>نقدی بل</label>
            <input type="number" name="charges[naqdi]" id="c_naqdi" min="0" value="0" oninput="calcCharges()">
          </div>
        </div>
      </div>

      <div class="bottom-grid">
        <div class="totals-card">
          <div class="totals-fields">
            <div class="tot-row"><label>کل نگ</label><input type="text" id="t_nag" readonly></div>
            <div class="tot-row"><label>خام رقم</label><input type="text" id="t_gross" readonly></div>
            <div class="tot-row"><label>کل خرچہ</label><input type="text" id="t_exp" readonly></div>
            <div class="tot-row"><label>صافی رقم</label><input type="text" id="t_safi" readonly></div>
            <div class="tot-row"><label>لگانا</label><input type="text" id="t_lagana" value="0" readonly></div>
            <div class="tot-row"><label>ڈیفرنس</label><input type="text" id="t_diff" readonly></div>
            <div class="tot-row"><label>اوسط</label><input type="text" id="t_avg" readonly></div>
          </div>
          <div class="btn-row">
            <input type="hidden" name="status" id="billStatus" value="posted">
            <button type="submit" class="btn-complete" id="btnCompleteBill" data-bill-status="posted" onclick="document.getElementById('billStatus').value='posted'">محفوظ کریں (Alt+M)</button>
            <button type="submit" class="btn-pending" id="btnPendingBill" data-bill-status="pending" onclick="document.getElementById('billStatus').value='pending'">نامکمل بیجک Alt + n</button>
            @if($editableBill)
              <a class="btn-cancel-edit" href="{{ route('client.portal.bills.khareed', ['slug' => $client->slug, 'search' => $billSearch]) }}">ترمیم منسوخ</a>
            @endif
          </div>
        </div>
      </div>

    </form>{{-- end form --}}

    <div class="bill-list-toolbar">
      <label><input type="checkbox" id="selectAllBills"> تمام بل منتخب کریں</label>
      <div class="bill-list-actions" role="group" aria-label="بل فلٹر">
        <a href="{{ route('client.portal.bills.sale.all', $client->slug) }}">تمام سیل بل</a>
        <button type="button" data-bill-filter="posted" aria-pressed="false">مکمل سیل بل</button>
        <button type="button" data-bill-filter="pending" aria-pressed="false">نامکمل بیجک</button>
        <a href="{{ route('client.portal.bills.sale.customers', $client->slug) }}">تمام کسٹمر وضاحت</a>
        <a href="{{ route('client.portal.bills.all', $client->slug) }}">تمام بل</a>
      </div>
    </div>

    <div class="bills-tbl-wrap">
      <table id="recentBillsTable">
        <thead>
          <tr>
            <th>حالت</th><th>کل نگ</th><th>جنس</th><th>تاریخ</th><th>انوائس</th><th>ترمیم</th><th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentBills as $b)
            <tr data-status="{{ $b->status }}">
              <td><span class="badge-{{ $b->status }}">{{ $b->status === 'pending' ? 'نامکمل' : 'مکمل' }}</span></td>
              <td>{{ number_format($b->lot_nag, 0) }}</td>
              <td>{{ $b->item_names ?: '—' }}</td>
              <td>{{ $b->bill_date }}</td>
              <td>{{ $b->inv_no }}</td>
              <td><a class="btn-edit-bill" href="{{ route('client.portal.bills.khareed', ['slug' => $client->slug, 'search' => $billSearch, 'edit' => $b->id]) }}">ترمیم</a></td>
              <td><input type="checkbox" class="recent-bill-select" aria-label="بل منتخب کریں"></td>
            </tr>
          @empty
            <tr><td colspan="7" style="text-align:center;background:#fff;color:#666">{{ $billSearch !== '' ? 'کوئی انوائس نہیں ملی' : 'کوئی بل نہیں' }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>{{-- end body-scroll --}}
</div>{{-- end main --}}

<script>
// ═══════════════════════════════════════════════
// DATA from server
// ═══════════════════════════════════════════════
const PARTIES = @json($parties->keyBy('code'));
const CUSTOMERS = @json($customers->keyBy('code'));
const MARFAT  = @json($marfatList->keyBy('code'));
const ITEMS   = @json($items->keyBy('code'));
const EDITABLE_BILL = @json($editableBill);

let lineCount = 0;
let lines = [];   // { cid, cname, date, nag, wazan, rate, jins, lagana, t2rate, amt }

function findCustomer(value){
    const codeOrName = value.trim();
    return CUSTOMERS[codeOrName]
        || Object.values(CUSTOMERS).find(customer => customer.name_ur === codeOrName)
        || null;
}

function findItem(value){
    const codeOrName = value.trim();
    return ITEMS[codeOrName]
        || Object.values(ITEMS).find(item => item.name_ur === codeOrName)
        || null;
}

function updateEntryIdentity(inputId, hiddenId, displayId, record, nameKey, codeKey){
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    const display = document.getElementById(displayId);
    const selected = record(input.value);

    hidden.value = selected ? selected.id : '';
    display.textContent = selected ? selected[codeKey] : '';
    if(selected){
        input.value = selected[nameKey];
    }

    return selected;
}

function escapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);
}

function getEnteredNag(){
    return lines.reduce((total, line) => total + (Number(line.nag) || 0), 0);
}

function recalculateLineAmounts(){
    lines.forEach((line, index) => {
        const laganaAmount = (Number(line.nag) || 0) * (Number(line.lag) || 0);
        const rateAmount = (Number(line.wazan) || 0) > 0
            ? Number(line.wazan) * Number(line.rate)
            : Number(line.nag) * Number(line.rate);
        line.amt = Math.round(rateAmount + laganaAmount);

        const amountInput = document.querySelectorAll('.line-row')[index]?.querySelector('.lr-amt');
        if(amountInput){
            amountInput.value = line.amt;
        }
    });
}

function getLotNagLimit(){
    const value = document.getElementById('lot_nag_input').value.trim();
    if(value === '') return null;

    const limit = Number(value);
    return Number.isFinite(limit) && limit >= 0 ? limit : null;
}

function showLotNotice(message){
    const notice = document.getElementById('lotNotice');
    notice.textContent = message;
    notice.classList.toggle('visible', message !== '');
    document.querySelector('.work-area').classList.toggle('has-lot-notice', message !== '');
}

function refreshLotNotice(){
    const limit = getLotNagLimit();
    if(limit === null){
        showLotNotice('');
        return;
    }

    const remaining = limit - getEnteredNag();
    if(remaining < 0){
        showLotNotice('درج شدہ نگ بیوپاری کے کل نگ سے زیادہ ہیں۔ پہلے لائنوں میں نگ درست کریں۔');
    } else if(remaining === 0){
        showLotNotice('اس بیوپاری کے تمام نگ فروخت ہو چکے ہیں۔ اس انوائس میں مزید اندراج نہیں ہوگا؛ اگلے بیوپاری کے لیے نئی انوائس درج کریں۔');
    } else {
        showLotNotice('');
    }
}

document.getElementById('nl_cname').addEventListener('change', function(){
    updateEntryIdentity('nl_cname', 'nl_cid', 'customerCodeDisplay', findCustomer, 'name_ur', 'code');
});

// ═══════════════════════════════════════════════
// PARTY autocomplete
// ═══════════════════════════════════════════════
document.getElementById('party_code_input').addEventListener('change', function(){
    const code = this.value.trim();
    const p = PARTIES[code];
    if(p){
        document.getElementById('party_account_id').value = p.id;
        document.getElementById('lbl_party').textContent = p.name_ur;
        // Auto-fill item defaults if jins not yet chosen
        // Fill charges from item when jins is set
    } else {
        // Try match by name
        for(const k in PARTIES){
            if(PARTIES[k].name_ur === code){
                document.getElementById('party_account_id').value = PARTIES[k].id;
                document.getElementById('lbl_party').textContent = PARTIES[k].name_ur;
                break;
            }
        }
    }
});

// ═══════════════════════════════════════════════
// ITEM autocomplete → fill charges
// ═══════════════════════════════════════════════
document.getElementById('nl_jins').addEventListener('change', function(){
    const item = updateEntryIdentity('nl_jins', 'item_id', 'itemCodeDisplay', findItem, 'name_ur', 'code');
    if(!item) return;

    document.getElementById('c_comm_pct').value = item.default_commission_pct || 0;
    document.getElementById('c_maz').value       = item.default_mazdoori_rate || 0;
    document.getElementById('c_mkt').value       = item.default_market_fee_rate || 0;
    document.getElementById('c_mun').value       = item.default_munshiana_rate || 0;
    document.getElementById('nl_lag').value      = item.default_lagana || 10;
    calcLineAmt();
    calcCharges();
});
document.getElementById('lot_nag_input').addEventListener('input', refreshLotNotice);

// ═══════════════════════════════════════════════
// MARFAT autocomplete
// ═══════════════════════════════════════════════
document.getElementById('marfat_input').addEventListener('change', function(){
    const code = this.value.trim();
    const m = MARFAT[code];
    if(m) document.getElementById('marfat_account_id').value = m.id;
});

// ═══════════════════════════════════════════════
// NEW LINE calculation
// ═══════════════════════════════════════════════
function calcLineAmt(){
    const nag   = parseFloat(document.getElementById('nl_nag').value)   || 0;
    const wazan = parseFloat(document.getElementById('nl_waz').value)   || 0;
    const rate  = parseFloat(document.getElementById('nl_rate').value)  || 0;
    const lag   = parseFloat(document.getElementById('nl_lag').value)   || 0;
    let amt;
    if(wazan > 0){ amt = wazan * rate + nag * lag; }
    else          { amt = nag * (rate + lag); }
    document.getElementById('nl_amt').value = Math.round(amt);
}
['nl_nag','nl_waz','nl_rate','nl_lag'].forEach(id=>{
    document.getElementById(id).addEventListener('input', calcLineAmt);
});

// ═══════════════════════════════════════════════
// ADD LINE
// ═══════════════════════════════════════════════
document.getElementById('btnAddLine').addEventListener('click', function(){
    const limit = getLotNagLimit();
    const totalNag = getEnteredNag();
    if(limit !== null && totalNag >= limit){
        refreshLotNotice();
        return;
    }

    const cnameEl = document.getElementById('nl_cname');
    const customer = updateEntryIdentity('nl_cname', 'nl_cid', 'customerCodeDisplay', findCustomer, 'name_ur', 'code');
    if(!customer){ alert('گاہک منتخب کریں'); cnameEl.focus(); return; }

    const nag    = parseFloat(document.getElementById('nl_nag').value)   || 0;
    const wazan  = parseFloat(document.getElementById('nl_waz').value)   || 0;
    const rate   = parseFloat(document.getElementById('nl_rate').value)  || 0;
    const lag    = parseFloat(document.getElementById('nl_lag').value)   || 0;
    const item = updateEntryIdentity('nl_jins', 'item_id', 'itemCodeDisplay', findItem, 'name_ur', 'code');
    const jins   = item ? item.name_ur : document.getElementById('nl_jins').value.trim();
    const t2rate = parseFloat(document.getElementById('nl_t2rate').value)|| 0;
    const lineDate = document.getElementById('bill_date').value;

    if(nag <= 0 || rate <= 0){ alert('نگ اور ریٹ درج کریں'); return; }
    if(limit !== null && totalNag + nag > limit){
        showLotNotice(`صرف ${limit - totalNag} نگ باقی ہیں۔ اس بیوپاری کی کل نگ سے زیادہ اندراج نہیں ہو سکتا۔`);
        document.getElementById('nl_nag').focus();
        return;
    }

    let amt;
    if(wazan > 0){ amt = wazan * rate + nag * lag; }
    else          { amt = nag * (rate + lag); }

    lineCount++;
    const idx = lineCount;
    lines.push({
        idx,
        cid: customer.id,
        curname: customer.name_ur,
        customerCode: customer.code,
        lineDate,
        nag,
        wazan,
        rate,
        jins,
        itemId: item ? item.id : '',
        itemCode: item ? item.code : '',
        lag,
        t2rate,
        amt: Math.round(amt),
    });
    recalculateLineAmounts();
    if(item) document.getElementById('item_id').value = item.id;

    renderLines();
    updateTotals();

    // Clear entry row
    document.getElementById('nl_cname').value  = '';
    document.getElementById('nl_cid').value    = '';
    document.getElementById('customerCodeDisplay').textContent = '';
    document.getElementById('nl_nag').value    = '';
    document.getElementById('nl_waz').value    = '';
    document.getElementById('nl_rate').value   = '';
    document.getElementById('nl_jins').value   = '';
    document.getElementById('itemCodeDisplay').textContent = '';
    document.getElementById('nl_t2rate').value = '';
    document.getElementById('nl_amt').value    = '';
    document.getElementById('nl_cname').focus();
});

// ═══════════════════════════════════════════════
// RENDER LINES TABLE + hidden inputs
// ═══════════════════════════════════════════════
function renderLines(){
    const body = document.getElementById('linesBody');
    body.innerHTML = '';
    lines.forEach((ln, i) => {
        const row = document.createElement('div');
        row.className = 'line-row';
        row.innerHTML = `
          <input class="lr-date" type="date" value="${escapeHtml(ln.lineDate)}"
            onchange="updateLineField(${i}, 'lineDate', this.value)">
          <input class="lr-cname" type="text" list="cust_list" value="${escapeHtml(ln.curname)}"
            onchange="updateLineField(${i}, 'customer', this.value)">
          <input class="lr-nag"  type="number" value="${ln.nag}"
            onchange="updateLineField(${i}, 'nag', this.value)">
          <input class="lr-waz"  type="number" step="any" value="${ln.wazan||''}"
            onchange="updateLineField(${i}, 'wazan', this.value)">
          <input class="lr-rate" type="number" step="any" value="${ln.rate}"
            onchange="updateLineField(${i}, 'rate', this.value)">
          <input class="lr-jins" type="text" list="item_list2" value="${escapeHtml(ln.jins)}"
            onchange="updateLineField(${i}, 'jins', this.value)">
          <input class="lr-lag"  type="number" step="any" value="${ln.lag}"
            onchange="updateLineField(${i}, 'lag', this.value)">
          <input class="lr-t2rate" type="number" step="any" value="${ln.t2rate||''}"
            onchange="updateLineField(${i}, 't2rate', this.value)">
          <input class="lr-amt"  type="number" value="${ln.amt}" readonly>
          <button type="button" class="btn-del-line" onclick="deleteLine(${i})">✕</button>

          <input type="hidden" data-line-field="line_date" name="lines[${i}][line_date]" value="${ln.lineDate}">
          <input type="hidden" data-line-field="customer_account_id" name="lines[${i}][customer_account_id]" value="${ln.cid}">
          <input type="hidden" data-line-field="item_id" name="lines[${i}][item_id]" value="${ln.itemId||''}">
          <input type="hidden" data-line-field="nag" name="lines[${i}][nag]" value="${ln.nag}">
          <input type="hidden" data-line-field="wazan" name="lines[${i}][wazan]" value="${ln.wazan||''}">
          <input type="hidden" data-line-field="rate" name="lines[${i}][rate]" value="${ln.rate}">
          <input type="hidden" data-line-field="lagana" name="lines[${i}][lagana]" value="${ln.lag}">
          <input type="hidden" data-line-field="trader_rate" name="lines[${i}][trader_rate]" value="${ln.t2rate||''}">
        `;
        body.appendChild(row);
    });
}

function updateLineField(i, field, value){
    const ln = lines[i];
    if(field === 'nag'){
        const newNag = value === '' ? 0 : (parseFloat(value) || 0);
        const limit = getLotNagLimit();
        if(limit !== null && getEnteredNag() - ln.nag + newNag > limit){
            document.querySelectorAll('.line-row')[i].querySelector('.lr-nag').value = ln.nag;
            showLotNotice(`صرف ${limit - (getEnteredNag() - ln.nag)} نگ اس انوائس میں درج کیے جا سکتے ہیں۔`);
            return;
        }
    }

    if(field === 'customer'){
        const customer = findCustomer(value);
        ln.cid = customer ? customer.id : '';
        ln.curname = customer ? customer.name_ur : value;
        ln.customerCode = customer ? customer.code : '';
    } else if(field === 'jins'){
        const item = findItem(value);
        ln.jins = item ? item.name_ur : value;
        ln.itemId = item ? item.id : '';
        ln.itemCode = item ? item.code : '';
        if(item) document.getElementById('item_id').value = item.id;
    } else if(['nag', 'wazan', 'rate', 'lag', 't2rate'].includes(field)){
        ln[field] = value === '' ? 0 : (parseFloat(value) || 0);
    } else {
        ln[field] = value;
    }

    const row = document.querySelectorAll('.line-row')[i];
    if(field === 'customer'){
        row.querySelector('.lr-cname').value = ln.curname;
    } else if(field === 'jins'){
        row.querySelector('.lr-jins').value = ln.jins;
    }
    const hiddenFields = {
        line_date: ln.lineDate,
        customer_account_id: ln.cid,
        item_id: ln.itemId,
        nag: ln.nag,
        wazan: ln.wazan || '',
        rate: ln.rate,
        lagana: ln.lag,
        trader_rate: ln.t2rate || '',
    };
    Object.entries(hiddenFields).forEach(([name, fieldValue]) => {
        row.querySelector(`[data-line-field="${name}"]`).value = fieldValue;
    });

    recalculateLineAmounts();
    updateTotals();
}

function deleteLine(i){
    if(!confirm('یہ لائن حذف کریں؟')) return;
    lines.splice(i, 1);
    renderLines();
    updateTotals();
}

// ═══════════════════════════════════════════════
// TOTALS
// ═══════════════════════════════════════════════
function updateTotals(){
    let totalNag=0, totalGross=0, totalLagana=0, totalDiff=0;
    lines.forEach(ln=>{
        const nag    = ln.nag   || 0;
        const wazan  = ln.wazan || 0;
        const rate   = ln.rate  || 0;
        const lag    = ln.lag   || 0;
        const t2     = ln.t2rate|| 0;

        let partyAmt, diff;
        if(t2 > 0){
            partyAmt = wazan>0 ? wazan*t2 : nag*t2;
            diff     = nag * (rate - t2);
        } else {
            partyAmt = wazan>0 ? wazan*rate : nag*rate;
            diff     = 0;
        }

        totalNag    += nag;
        totalGross  += partyAmt;
        totalLagana += nag * lag;
        totalDiff   += diff;
    });

    document.getElementById('t_nag').value    = totalNag;
    document.getElementById('t_gross').value  = Math.round(totalGross);
    document.getElementById('t_lagana').value = Number(totalLagana.toFixed(2));
    document.getElementById('t_diff').value   = Math.round(totalDiff);
    document.getElementById('t_avg').value    = totalNag > 0 ? (totalGross / totalNag).toFixed(1) : '0.0';

    refreshLotNotice();
    calcCharges();
}

// ═══════════════════════════════════════════════
// CHARGES
// ═══════════════════════════════════════════════
function calcCharges(){
    const gross = parseFloat(document.getElementById('t_gross').value) || 0;
    const nag   = parseFloat(document.getElementById('t_nag').value)   || 0;

    const commPct  = parseFloat(document.getElementById('c_comm_pct').value) || 0;
    const karaya   = parseFloat(document.getElementById('c_karaya').value)   || 0;
    const kharch   = parseFloat(document.getElementById('c_kharch').value)   || 0;
    const maz      = parseFloat(document.getElementById('c_maz').value)      || 0;
    const mkt      = parseFloat(document.getElementById('c_mkt').value)      || 0;
    const mun      = parseFloat(document.getElementById('c_mun').value)      || 0;
    const stor     = parseFloat(document.getElementById('c_stor').value)     || 0;
    const naqdi    = parseFloat(document.getElementById('c_naqdi').value)    || 0;

    const commAmt  = Math.round(gross * commPct / 100);
    const karayaA  = Math.round(karaya * nag);
    const kharchA  = Math.round(kharch * nag);
    const mazA     = Math.round(maz    * nag);
    const mktA     = Math.round(mkt    * nag);
    const munA     = Math.round(mun    * nag);
    const storA    = Math.round(stor   * nag);

    document.getElementById('c_comm_amt').value  = commAmt;
    document.getElementById('c_karaya_amt').value= karayaA;
    document.getElementById('c_kharch_amt').value= kharchA;
    document.getElementById('c_maz_amt').value   = mazA;
    document.getElementById('c_mkt_amt').value   = mktA;
    document.getElementById('c_mun_amt').value   = munA;
    document.getElementById('c_stor_amt').value  = storA;

    const totalExp = commAmt + karayaA + kharchA + mazA + mktA + munA + storA + naqdi;
    document.getElementById('t_exp').value  = Number(totalExp.toFixed(2));
    document.getElementById('t_safi').value = Number((gross - totalExp).toFixed(2));
}

// ═══════════════════════════════════════════════
// FORM VALIDATION — ensure at least 1 line
// ═══════════════════════════════════════════════
document.getElementById('billForm').addEventListener('submit', function(e){
    if(e.submitter && e.submitter.dataset.billStatus){
        document.getElementById('billStatus').value = e.submitter.dataset.billStatus;
    }

    if(lines.length === 0){
        e.preventDefault();
        alert('کم از کم ایک لائن شامل کریں۔');
        return;
    }

    const lotNagLimit = getLotNagLimit();
    if(lotNagLimit !== null && getEnteredNag() > lotNagLimit){
        e.preventDefault();
        refreshLotNotice();
        return;
    }

    const invalidLineIndex = lines.findIndex(line => !line.cid);
    if(invalidLineIndex !== -1){
        e.preventDefault();
        alert('ہر لائن کے لیے درست گاہک منتخب کریں۔');
        document.querySelectorAll('.lr-cname')[invalidLineIndex].focus();
    }
});

const recentBillRows = Array.from(document.querySelectorAll('#recentBillsTable tbody tr[data-status]'));
let recentBillStatusFilter = 'all';

function filterRecentBills(){
    recentBillRows.forEach(row => {
        const matchesStatus = recentBillStatusFilter === 'all' || row.dataset.status === recentBillStatusFilter;
        row.hidden = !matchesStatus;
    });
}

document.querySelectorAll('[data-bill-filter]').forEach(button => {
    button.addEventListener('click', function(){
        recentBillStatusFilter = this.dataset.billFilter;
        document.querySelectorAll('[data-bill-filter]').forEach(filterButton => {
            filterButton.classList.toggle('active', filterButton === this);
            filterButton.setAttribute('aria-pressed', filterButton === this ? 'true' : 'false');
        });
        filterRecentBills();
    });
});

document.getElementById('selectAllBills').addEventListener('change', function(){
    document.querySelectorAll('.recent-bill-select').forEach(checkbox => {
        checkbox.checked = this.checked && !checkbox.closest('tr').hidden;
    });
});

if(EDITABLE_BILL){
    document.getElementById('inv_no').value = EDITABLE_BILL.inv_no;
    document.getElementById('bill_date').value = EDITABLE_BILL.bill_date;
    document.getElementById('party_account_id').value = EDITABLE_BILL.party_account_id || '';
    document.getElementById('marfat_account_id').value = EDITABLE_BILL.marfat_account_id || '';
    document.getElementById('item_id').value = EDITABLE_BILL.item_id || '';
    document.getElementById('lot_nag_input').value = EDITABLE_BILL.lot_nag;
    document.querySelector('[name="marka"]').value = EDITABLE_BILL.marka || '';
    document.querySelector('[name="vehicle_no"]').value = EDITABLE_BILL.vehicle_no || '';
    document.querySelector('[name="container_no"]').value = EDITABLE_BILL.container_no || '';
    document.getElementById('billStatus').value = EDITABLE_BILL.status;

    const party = Object.values(PARTIES).find(account => Number(account.id) === Number(EDITABLE_BILL.party_account_id));
    if(party){
        document.getElementById('party_code_input').value = party.code;
        document.getElementById('lbl_party').textContent = party.name_ur;
    }

    const marfat = Object.values(MARFAT).find(account => Number(account.id) === Number(EDITABLE_BILL.marfat_account_id));
    if(marfat){
        document.getElementById('marfat_input').value = marfat.code;
    }

    const chargeInputs = {
        Commission: 'c_comm_pct',
        Karaya: 'c_karaya',
        Kharch: 'c_kharch',
        Mazdoori: 'c_maz',
        'Market Fee': 'c_mkt',
        Munshiana: 'c_mun',
        'Store Rent': 'c_stor',
        'Cash Payment': 'c_naqdi',
    };
    Object.entries(chargeInputs).forEach(([name, inputId]) => {
        document.getElementById(inputId).value = EDITABLE_BILL.charges[name] || 0;
    });

    lines = EDITABLE_BILL.lines.map((line, index) => ({
        idx: index + 1,
        cid: line.customer_account_id,
        curname: line.customer_name || line.customer_code || '',
        customerCode: line.customer_code || '',
        lineDate: line.line_date || EDITABLE_BILL.bill_date,
        nag: Number(line.nag) || 0,
        wazan: Number(line.wazan) || 0,
        rate: Number(line.rate) || 0,
        jins: line.item_name || line.item_code || '',
        itemId: line.item_id || '',
        itemCode: line.item_code || '',
        lag: Number(line.lagana) || 0,
        laganaAmount: (Number(line.nag) || 0) * (Number(line.lagana) || 0),
        t2rate: Number(line.trader_rate) || 0,
        amt: Math.round((Number(line.wazan) > 0
            ? Number(line.wazan) * Number(line.rate)
            : Number(line.nag) * Number(line.rate))
            + (Number(line.nag) || 0) * (Number(line.lagana) || 0)),
    }));
    lineCount = lines.length;
    renderLines();
    updateTotals();
}

// Enter key moves to next field
document.addEventListener('keydown', function(e){
    if(e.altKey && !e.ctrlKey && !e.metaKey && !e.repeat && e.key.toLowerCase() === 'n'){
        e.preventDefault();
        document.getElementById('billForm').requestSubmit(document.getElementById('btnPendingBill'));
        return;
    }

    if(e.altKey && !e.ctrlKey && !e.metaKey && !e.repeat && e.key.toLowerCase() === 'm'){
        e.preventDefault();
        document.getElementById('billStatus').value = 'posted';
        document.getElementById('billForm').requestSubmit(document.getElementById('btnCompleteBill'));
        return;
    }

    if(e.key === 'Enter' && !e.shiftKey){
        if(document.activeElement.id === 'btnAddLine'){
            e.preventDefault();
            document.getElementById('btnAddLine').click();
            return;
        }

        if(document.activeElement.closest('.bill-search-form')){
            return;
        }

        const focusable = Array.from(document.querySelectorAll(
            'input:not([readonly]):not([type=hidden]), select, button'
        )).filter(el => el.offsetParent !== null);
        const idx = focusable.indexOf(document.activeElement);
        if(idx > -1 && idx < focusable.length - 1){
            e.preventDefault();
            focusable[idx+1].focus();
        }
    }
});
</script>
</body>
</html>