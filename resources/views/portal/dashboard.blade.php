<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $client->name }} &mdash; ڈیش بورڈ</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--sw:68px;--sh:72px;--blue:#1565c0;--dark:#0d47a1;--bg:hsl(199,73%,45%)}
body{font-family:'Noto Nastaliq Urdu','Jameel Noori Nastaleeq',serif;background:var(--bg);min-height:100vh;display:flex;overflow:hidden}

/* ── SIDEBARS ── */
#ls,#rs{position:fixed;top:0;bottom:0;width:var(--sw);background:linear-gradient(180deg,#0d47a1,#1565c0);display:flex;flex-direction:column;z-index:100}
#ls{left:0;box-shadow:2px 0 8px rgba(0,0,0,.3)}
#rs{right:0;box-shadow:-2px 0 8px rgba(0,0,0,.3)}
.s-top{height:60px;display:flex;align-items:center;justify-content:center;background:#0a3880;border-bottom:1px solid rgba(255,255,255,.15);flex-shrink:0}
.s-top img{width:36px;height:36px;object-fit:contain;border-radius:4px}
.s-nav{flex:1;overflow-y:auto;overflow-x:hidden}
.s-nav::-webkit-scrollbar{width:0}
.si{display:flex;flex-direction:column;align-items:center;justify-content:center;width:var(--sw);min-height:var(--sh);color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.08);transition:background .15s;padding:6px 4px;cursor:pointer;background:none;border-left:none;border-right:none;border-top:none}
.si:hover,.si:focus{background:rgba(255,255,255,.12);outline:none}
.si svg{margin-bottom:3px;flex-shrink:0}
.si span{font-size:11px;direction:rtl;line-height:1.3;text-align:center}
.s-bot{border-top:1px solid rgba(255,255,255,.15)}

/* ── MAIN ── */
#main{margin-left:var(--sw);margin-right:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh;overflow-y:auto}
#main::-webkit-scrollbar{width:6px}
#main::-webkit-scrollbar-thumb{background:rgba(255,255,255,.3);border-radius:3px}

/* title bar */
.tbar{background:linear-gradient(90deg,#1565c0,#0d47a1);padding:7px 14px;display:flex;align-items:center;gap:10px;direction:rtl;border-bottom:2px solid rgba(255,255,255,.12);flex-shrink:0}
.tbar h3{color:#fff;font-size:14px;font-family:inherit}
.tbar .lbtn{margin-right:auto;background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.4);color:#fca5a5;padding:3px 12px;border-radius:6px;font-size:12px;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:5px;transition:background .15s;direction:ltr}
.tbar .lbtn:hover{background:rgba(239,68,68,.35)}

/* top icon buttons */
.trow{background:var(--bg);padding:10px 10px 0;direction:rtl}
.trow ul{list-style:none;display:flex;flex-wrap:wrap;gap:7px;justify-content:center}
.trow li{position:relative}
.ibox{width:110px;height:110px;background:#fff;border:1px solid #ccc;border-radius:4px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;text-decoration:none;padding:8px;transition:box-shadow .15s}
.ibox:hover{box-shadow:0 4px 12px rgba(0,0,0,.2);background:#f0f8ff}
.ibox svg{width:48px;height:48px;margin-bottom:4px}
.ibox .lbl{font-size:16px;font-weight:bold;color:#00008b;font-family:inherit;line-height:1.2;text-align:center}
.badge{position:absolute;top:4px;right:4px;background:#ff6600;color:#fff;border-radius:50%;width:20px;height:20px;font-size:11px;display:flex;align-items:center;justify-content:center;font-family:sans-serif;font-weight:bold;z-index:1}
.ddm{display:none;position:absolute;top:114px;right:0;background:#1565c0;border:1px solid rgba(255,255,255,.3);border-radius:4px;min-width:155px;z-index:200;box-shadow:0 4px 12px rgba(0,0,0,.3)}
.trow li:hover .ddm{display:block}
.ddm a{display:block;padding:8px 13px;color:#fff;font-size:15px;font-family:inherit;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.1);transition:background .1s;direction:rtl;white-space:nowrap}
.ddm a:hover{background:rgba(255,255,255,.15)}
.ddm a:last-child{border-bottom:none}

/* quick boxes */
.qrow{background:var(--bg);padding:7px 10px;direction:rtl}
.qrow ul{list-style:none;display:flex;flex-wrap:wrap;gap:5px;justify-content:center}
.qbox{width:110px;height:85px;background:#fff;border:1px solid #ccc;border-radius:4px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-decoration:none;color:#00008b;padding:5px;transition:box-shadow .15s}
.qbox:hover{box-shadow:0 3px 10px rgba(0,0,0,.2);background:#f0f8ff}
.qbox svg{margin-bottom:3px}
.qbox span{font-size:13px;font-weight:bold;font-family:inherit;text-align:center;line-height:1.3}

/* hero */
.hero{flex:1;min-height:220px;position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px;text-align:center;direction:rtl;overflow:hidden}
.hero-bg{position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1540420773420-3366772f4999?w=1800&q=70') bottom center/cover no-repeat}
.hero-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(to bottom,rgba(33,150,243,.5) 0%,rgba(33,150,243,.1) 55%,transparent 100%)}
.hero-ct{position:relative;z-index:1}
.hero-ct h1{font-size:clamp(26px,3.5vw,56px);font-weight:900;color:#000080;text-shadow:1px 1px 0 #fff,-1px -1px 0 #fff,0 0 10px rgba(255,255,255,.6);line-height:1.4;margin-bottom:8px}
.hero-ct .sub{font-size:clamp(13px,1.8vw,20px);color:#00008b;text-shadow:0 0 8px #fff;line-height:2}

/* daily book widget */
.dbw{background:var(--bg);padding:8px 10px 10px}
.wcard{background:rgba(255,255,255,.92);border-radius:6px;overflow:hidden;max-width:480px}
.wh{background:#1565c0;padding:6px 12px;display:flex;align-items:center;gap:8px;direction:rtl}
.wh h6{color:#fff;font-size:14px;font-family:inherit}
.wb{padding:10px}
.dr{display:flex;gap:7px;margin-bottom:7px;direction:ltr}
.dr input{padding:4px 7px;border:1px solid #bbb;border-radius:4px;font-size:12px;flex:1}
.dr button{background:#1565c0;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:11px;white-space:nowrap}
.dbt{width:100%;border-collapse:collapse;direction:rtl;font-family:inherit}
.dbt th,.dbt td{padding:4px 7px;font-size:13px;border-bottom:1px solid #ddd}
.dbt th{background:#e3f2fd;color:#0d47a1;font-weight:bold}
.dbt .tot{font-weight:bold;background:#dbeafe}

/* clock */
#clkw{position:fixed;bottom:16px;right:calc(var(--sw) + 10px);z-index:50}
.clk{width:88px;height:88px;border-radius:50%;background:radial-gradient(circle,#1a1a2e 60%,#0a0a1a 100%);border:3px solid #444;position:relative;box-shadow:0 4px 16px rgba(0,0,0,.7),inset 0 0 10px rgba(255,255,255,.04)}
.clk::before{content:'';position:absolute;top:50%;left:50%;width:7px;height:7px;background:#fff;border-radius:50%;transform:translate(-50%,-50%);z-index:10;box-shadow:0 0 4px rgba(255,255,255,.8)}
.ck-tk{position:absolute;top:50%;left:50%;width:1.5px;height:7px;background:rgba(255,255,255,.4);transform-origin:50% -33px;margin-left:-.75px}
.ck-tk.major{width:2.5px;height:10px;background:rgba(255,255,255,.7);transform-origin:50% -30px}
.hnd{position:absolute;bottom:50%;left:50%;transform-origin:50% 100%;border-radius:2px}
.h-hr{width:3.5px;height:22px;background:#fff;margin-left:-1.75px;box-shadow:0 0 3px rgba(255,255,255,.5)}
.h-mn{width:2px;height:30px;background:#ccc;margin-left:-1px;box-shadow:0 0 2px rgba(255,255,255,.4)}
.h-sc{width:1px;height:34px;background:#f44;margin-left:-.5px;box-shadow:0 0 3px rgba(255,0,0,.6)}
.cn{position:absolute;color:rgba(255,255,255,.8);font-size:8px;font-family:sans-serif;font-weight:bold;line-height:1;transform:translate(-50%,-50%)}

/* AI float */
#aif{position:fixed;bottom:20px;left:calc(var(--sw)+12px);z-index:200;display:none}
.afb{width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;cursor:pointer;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(99,102,241,.55);transition:transform .2s,box-shadow .2s;position:relative;z-index:1}
.afb:hover{transform:scale(1.1);box-shadow:0 6px 24px rgba(99,102,241,.75)}
.apu{position:absolute;inset:-5px;border-radius:50%;border:2px solid rgba(99,102,241,.45);animation:pr 2s ease-out infinite;pointer-events:none}
@keyframes pr{0%{transform:scale(1);opacity:.8}100%{transform:scale(1.6);opacity:0}}

/* AI panel */
#aip{position:fixed;bottom:84px;left:calc(var(--sw)+12px);width:310px;background:#1e1b4b;border-radius:12px;border:1px solid rgba(99,102,241,.3);box-shadow:0 8px 32px rgba(0,0,0,.55);z-index:300;display:none;flex-direction:column;overflow:hidden}
#aip.open{display:flex}
.aph{background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:9px 13px;display:flex;align-items:center;justify-content:space-between;direction:ltr}
.aph .title{color:#fff;font-weight:bold;font-family:sans-serif;font-size:12px;display:flex;align-items:center;gap:6px}
.aph button{background:none;border:none;color:#fff;cursor:pointer;font-size:17px;line-height:1}
.ams{flex:1;max-height:250px;overflow-y:auto;padding:9px;display:flex;flex-direction:column;gap:7px}
.ams::-webkit-scrollbar{width:3px}
.ams::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.am{padding:7px 9px;border-radius:8px;font-size:12px;font-family:sans-serif;line-height:1.5;max-width:88%}
.am.bot{background:rgba(99,102,241,.22);color:#c7d2fe;align-self:flex-start}
.am.usr{background:rgba(99,102,241,.55);color:#fff;align-self:flex-end}
.ald{padding:4px 10px;display:none}
.ad{display:inline-block;width:5px;height:5px;border-radius:50%;background:#818cf8;margin:0 2px;animation:db 1.4s infinite}
.ad:nth-child(2){animation-delay:.2s}.ad:nth-child(3){animation-delay:.4s}
@keyframes db{0%,80%,100%{transform:scale(0)}40%{transform:scale(1)}}
.air{display:flex;gap:6px;padding:9px;border-top:1px solid rgba(255,255,255,.1);direction:ltr}
.air input{flex:1;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:6px;padding:5px 9px;color:#fff;font-size:12px;outline:none}
.air input::placeholder{color:rgba(255,255,255,.35)}
.air button{background:#6366f1;border:none;border-radius:6px;padding:5px 9px;color:#fff;cursor:pointer;display:flex;align-items:center}
</style>
</head>
<body>

<!-- LEFT SIDEBAR -->
<div id="ls">
  <div class="s-top"><img src="{{ asset('images/elemeentra-logo.jpeg') }}" alt="Logo"></div>
  <div class="s-nav">
    <a href="{{ route('client.portal.accounts.create',$client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
      <span>نیو کسٹمر</span></a>
    <a href="{{ route('client.portal.items.create',$client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
      <span>نیو جنس</span></a>
    <a href="{{ route('client.portal.bills.sale',$client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg>
      <span>سیل مال</span></a>
    <a href="{{ route('client.portal.bills.khareed',$client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
      <span>خرید بل</span></a>
    <a href="{{ route('client.portal.bills.customer',$client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
      <span>کسٹمر خرید</span></a>
    <a href="{{ route('client.portal.bills.index',$client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.224.72-.101 1.5.247 2.158m0 0a3 3 0 004.985 1.218M7 10h10m-7 3h7m-10.5 3H17a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0017 4.5H7A2.25 2.25 0 004.75 6.75v8.25A2.25 2.25 0 007 17.25z"/></svg>
      <span>بل پرنٹ</span></a>
    <a href="{{ route('client.portal.ledger.banam',$client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.17-.789 3.376 3.376 0 016.34 0z"/></svg>
      <span>بنام گاہک</span></a>
    <a href="{{ route('client.portal.stock.ledger',$client->slug) }}" class="si">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605"/></svg>
      <span>مال کھاتہ</span></a>
  </div>
  <div class="s-bot">
    <a href="{{ route('client.portal.settings',$client->slug) }}" class="si">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      <span style="font-family:sans-serif;font-size:9px">Settings</span></a>
    <a href="{{ route('client.portal.ai-settings',$client->slug) }}" class="si">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
      <span style="font-family:sans-serif;font-size:9px">AI Agent</span></a>
    <form method="POST" action="/{{ $client->slug }}/logout">@csrf
      <button type="submit" class="si">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
        <span style="font-family:sans-serif;font-size:9px">Logout</span></button></form>
  </div>
</div>

<!-- RIGHT SIDEBAR -->
<div id="rs">
  <div class="s-top">
    <svg width="26" height="26" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
  </div>
  <div class="s-nav">
    <a href="{{ route('client.portal.vouchers.jv',$client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg><span>JV</span></a>
    <a href="{{ route('client.portal.vouchers.debit',$client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/></svg><span>بنام رقم</span></a>
    <a href="{{ route('client.portal.vouchers.credit',$client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg><span>جمع رقم</span></a>
    <a href="{{ route('client.portal.vouchers.agrai',$client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>وصولی رقم</span></a>
    <a href="{{ route('client.portal.reports.agrai',$client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg><span>شیٹ</span></a>
    <a href="{{ route('client.portal.reports.cash',$client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg><span>کیش بک</span></a>
    <a href="{{ route('client.portal.reports.dailybook',$client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg><span>روزنامچہ</span></a>
    <a href="{{ route('client.portal.reports.balance',$client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z"/></svg><span>بیلنس</span></a>
    <a href="{{ route('client.portal.ledger.party',$client->slug) }}" class="si"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>کھاتہ پارٹی</span></a>
  </div>
</div>

<!-- MAIN -->
<div id="main">

  <!-- TITLE BAR -->
  <div class="tbar">
    <svg width="18" height="18" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/></svg>
    <h3>{{ $client->name }}
      @if ($client->shop_number)
        &mdash; دکان {{ $client->shop_number }}
      @endif
      @if ($client->city)
        &mdash; {{ $client->city }}
      @endif
    </h3>
    <form method="POST" action="/{{ $client->slug }}/logout" style="margin-right:auto">@csrf
      <button type="submit" class="lbtn">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
        Logout</button></form>
  </div>

  <!-- TOP ICON BUTTONS ROW -->
  <div class="trow"><ul>
    <li><span class="badge">30</span>
      <a href="{{ route('client.portal.accounts.create',$client->slug) }}" class="ibox">
        <svg fill="none" stroke="#1565c0" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
        <span class="lbl">نیو کھاتہ</span></a>
      <div class="ddm"><a href="{{ route('client.portal.accounts.create',$client->slug) }}">ایڈ نیو کسٹمر</a><a href="{{ route('client.portal.items.create',$client->slug) }}">ایڈ نیو جنس</a><a href="{{ route('client.portal.accounts.index',$client->slug) }}">تمام کسٹمر</a></div></li>
    <li><span class="badge">30</span>
      <a href="{{ route('client.portal.bills.sale',$client->slug) }}" class="ibox">
        <svg fill="none" stroke="#1565c0" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg>
        <span class="lbl">سیل مال</span></a>
      <div class="ddm"><a href="{{ route('client.portal.bills.sale',$client->slug) }}">سیل مال</a></div></li>
    <li><span class="badge">30</span>
      <a href="#" class="ibox">
        <svg fill="none" stroke="#1565c0" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
        <span class="lbl">انوائس</span></a>
      <div class="ddm"><a href="{{ route('client.portal.reports.balance',$client->slug) }}">بیلنس شیٹ</a><a href="{{ route('client.portal.reports.dailybook',$client->slug) }}">روزنامچہ</a><a href="{{ route('client.portal.reports.agrai',$client->slug) }}">اگراہی شیٹ</a><a href="{{ route('client.portal.ledger.banam',$client->slug) }}">بنام گاہک</a></div></li>
    <li><span class="badge">30</span>
      <a href="{{ route('client.portal.reports.dailybook',$client->slug) }}" class="ibox">
        <svg fill="none" stroke="#1565c0" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
        <span class="lbl">روزنامچہ</span></a>
      <div class="ddm"><a href="{{ route('client.portal.vouchers.debit',$client->slug) }}">بنام</a><a href="{{ route('client.portal.vouchers.credit',$client->slug) }}">جمع</a><a href="{{ route('client.portal.reports.dailybook',$client->slug) }}">روزنامچہ</a></div></li>
    <li><span class="badge">30</span>
      <a href="{{ route('client.portal.ledger.party',$client->slug) }}" class="ibox">
        <svg fill="none" stroke="#1565c0" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.17-.789 3.376 3.376 0 016.34 0z"/></svg>
        <span class="lbl">کھاتہ</span></a>
      <div class="ddm"><a href="{{ route('client.portal.ledger.party',$client->slug) }}">کھاتہ پارٹی</a></div></li>
    <li><span class="badge">30</span>
      <a href="{{ route('client.portal.stock.ledger',$client->slug) }}" class="ibox">
        <svg fill="none" stroke="#1565c0" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
        <span class="lbl">اسٹاک</span></a>
      <div class="ddm"><a href="{{ route('client.portal.stock.ledger',$client->slug) }}">اسٹاک لیجر</a></div></li>
  </ul></div>

  <!-- QUICK BOXES -->
  <div class="qrow"><ul>
    @php $ql=[['l'=>'روزنامچہ','r'=>'client.portal.reports.dailybook','i'=>'cal'],['l'=>'کیش بک','r'=>'client.portal.reports.cash','i'=>'csh'],['l'=>'بیلنس شیٹ','r'=>'client.portal.reports.balance','i'=>'scl'],['l'=>'اگراہی شیٹ','r'=>'client.portal.reports.agrai','i'=>'doc'],['l'=>'اگراہی جمع','r'=>'client.portal.vouchers.agrai','i'=>'$'],['l'=>'بنام','r'=>'client.portal.vouchers.debit','i'=>'$'],['l'=>'جمع','r'=>'client.portal.vouchers.credit','i'=>'$'],['l'=>'JV جمع/بنام','r'=>'client.portal.vouchers.jv','i'=>'sw'],['l'=>'بنام اگراہی','r'=>'client.portal.ledger.banam','i'=>'$'],['l'=>'تمام بل','r'=>'client.portal.bills.index','i'=>'lst'],['l'=>'بیوپاری کھاتہ','r'=>'client.portal.ledger.party','i'=>'usr']]; @endphp
    @foreach($ql as $q)
    <li><a href="{{ route($q['r'],$client->slug) }}" class="qbox">
      <svg width="26" height="26" fill="none" stroke="#1565c0" stroke-width="1.5" viewBox="0 0 24 24">
        @if($q['i']==='cal')<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
        @elseif($q['i']==='csh')<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        @elseif($q['i']==='scl')<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z"/>
        @elseif($q['i']==='doc')<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
        @elseif($q['i']==='sw')<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
        @elseif($q['i']==='lst')<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
        @elseif($q['i']==='usr')<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
        @else<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
        @endif
      </svg>
      <span>{{ $q['l'] }}</span></a></li>
    @endforeach
  </ul></div>

  <!-- HERO -->
  <div class="hero">
    <div class="hero-bg"></div>
    <div class="hero-ct">
      <h1>{{ $client->name }}</h1>
      @if($client->proprietor_name)<div class="sub">سرپرست {{ $client->proprietor_name }}</div>@endif
      @if($client->address)<div class="sub">{{ $client->address }}</div>@endif
      @if ($client->shop_number || $client->city)
        <div class="sub">
          @if ($client->shop_number)
            دکان نمبر {{ $client->shop_number }}
          @endif
          @if ($client->city)
            {{ $client->city }}
          @endif
        </div>
      @endif
      @if($client->phone)<div class="sub">{{ $client->phone }}</div>@endif
    </div>
  </div>

  <!-- DAILY BOOK -->
  <div class="dbw">
    <div class="wcard">
      <div class="wh">
        <svg width="15" height="15" fill="none" stroke="#93c5fd" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
        <h6>روزنامچہ</h6>
      </div>
      <div class="wb">
        <form method="GET" action="{{ route('client.portal.reports.dailybook',$client->slug) }}">
          <div class="dr">
            <input type="date" name="from" value="{{ date('Y-m-d') }}">
            <input type="date" name="to" value="{{ date('Y-m-d') }}">
            <button type="submit">روزنامچہ دیکھیں</button>
          </div>
        </form>
        <table class="dbt">
          <thead><tr><th>نام کھاتہ</th><th>بنام</th><th>جمع</th></tr></thead>
          <tbody>
            @forelse($todayEntries??[] as $e)
            <tr><td>{{ $e->account_name }}</td><td>{{ number_format($e->debit,0) }}</td><td>{{ number_format($e->credit,0) }}</td></tr>
            @empty
            <tr><td colspan="3" style="text-align:center;color:#888;font-family:sans-serif;font-size:11px;padding:9px">آج کی کوئی انٹری نہیں</td></tr>
            @endforelse
            <tr class="tot"><td>ٹوٹل</td><td>{{ number_format(collect($todayEntries??[])->sum('debit'),0) }}</td><td>{{ number_format(collect($todayEntries??[])->sum('credit'),0) }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div><!-- /main -->

<!-- CLOCK -->
<div id="clkw">
  <div class="clk">
    @for($i=0;$i<60;$i++)<div class="ck-tk {{ $i%5==0?'major':'' }}" style="transform:rotate({{ $i*6 }}deg) translateX(-50%)"></div>@endfor
    <span class="cn" style="top:9%;left:50%">12</span>
    <span class="cn" style="top:50%;left:91%">3</span>
    <span class="cn" style="top:91%;left:50%">6</span>
    <span class="cn" style="top:50%;left:9%">9</span>
    <div class="hnd h-hr" id="hr"></div>
    <div class="hnd h-mn" id="mn"></div>
    <div class="hnd h-sc" id="sc"></div>
  </div>
</div>

<!-- AI FLOAT -->
<div id="aif">
  <div class="apu"></div>
  <button class="afb" onclick="toggleAi()" title="AI Assistant">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
  </button>
</div>

<!-- AI PANEL -->
<div id="aip">
  <div class="aph">
    <span class="title">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
      Mandi AI Assistant
    </span>
    <button onclick="toggleAi()">&#x2715;</button>
  </div>
  <div class="ams" id="ams">
    <div class="am bot">السلام علیکم! کھاتہ، بل، یا رپورٹ کے بارے میں پوچھیں۔</div>
  </div>
  <div class="ald" id="ald"><span class="ad"></span><span class="ad"></span><span class="ad"></span></div>
  <div class="air">
    <input type="text" id="ai-in" placeholder="سوال لکھیں..." onkeydown="if(event.key==='Enter')sendAi()">
    <button onclick="sendAi()"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg></button>
  </div>
</div>

<script>
(function(){
  function tick(){
    const d=new Date(),h=(d.getHours()%12)*30+d.getMinutes()*.5,m=d.getMinutes()*6+d.getSeconds()*.1,s=d.getSeconds()*6;
    document.getElementById('hr').style.transform=`rotate(${h}deg)`;
    document.getElementById('mn').style.transform=`rotate(${m}deg)`;
    document.getElementById('sc').style.transform=`rotate(${s}deg)`;
  }
  tick();setInterval(tick,1000);
})();
(function(){
  if({{ $client->ai_api_key ? 'true' : 'false' }})
    document.getElementById('aif').style.display='block';
})();
function toggleAi(){document.getElementById('aip').classList.toggle('open')}
async function sendAi(){
  const inp=document.getElementById('ai-in'),msgs=document.getElementById('ams'),ld=document.getElementById('ald'),t=inp.value.trim();
  if(!t)return;inp.value='';
  const ub=document.createElement('div');ub.className='am usr';ub.textContent=t;msgs.appendChild(ub);msgs.scrollTop=msgs.scrollHeight;
  ld.style.display='block';
  try{
    const r=await fetch('/{{ $client->slug }}/ai-chat',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},body:JSON.stringify({message:t})});
    const data=await r.json();
    const bb=document.createElement('div');bb.className='am bot';bb.textContent=data.reply||'معذرت، جواب نہیں آیا۔';msgs.appendChild(bb);
  }catch(e){const eb=document.createElement('div');eb.className='am bot';eb.textContent='خطا: کنکشن نہیں ہوا۔';msgs.appendChild(eb);}
  finally{ld.style.display='none';msgs.scrollTop=msgs.scrollHeight;}
}
</script>
</body>
</html>