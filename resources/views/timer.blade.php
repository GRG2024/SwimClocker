<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0a1628">
    <title>Swim Timer</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--bg:#0a1628;--c1:#f59e0b;--c2:#3b82f6;--c3:#10b981;--c4:#a855f7;--c5:#ef4444;--c6:#ec4899;--c7:#14b8a6;--c8:#f97316;
            --w:#fff;--w70:rgba(255,255,255,.7);--w50:rgba(255,255,255,.5);--w30:rgba(255,255,255,.3);--w15:rgba(255,255,255,.15);--w05:rgba(255,255,255,.05);
            --font:'Outfit',-apple-system,sans-serif;--mono:'JetBrains Mono',monospace}
        html,body{height:100%;overflow:hidden}
        body{font-family:var(--font);background:var(--bg);color:var(--w);-webkit-user-select:none;user-select:none;-webkit-tap-highlight-color:transparent;overscroll-behavior:none}
        @keyframes edgePulse{0%,100%{opacity:.3}50%{opacity:.8}}
        @keyframes bobble{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
        @keyframes countPop{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.15);opacity:.5}}
        @keyframes flashIn{0%{opacity:.8}100%{opacity:0}}
        .view{display:none;height:100vh;flex-direction:column}.view.active{display:flex}
        .inner{max-width:480px;width:100%;margin:0 auto;padding:28px 16px 120px}
        .lbl{font-size:11px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--w30);margin-bottom:6px}
        .title{font-size:26px;font-weight:800;letter-spacing:-.03em;background:linear-gradient(135deg,#fff,rgba(255,255,255,.65));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .inp{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:10px 14px;color:#fff;font-size:15px;font-family:var(--font);outline:none;width:100%}
        .inp:focus{border-color:var(--c2)}
        .btn{width:100%;padding:18px;border:none;border-radius:16px;font-size:20px;font-weight:700;cursor:pointer;font-family:var(--font);transition:transform .1s}
        .btn:active{transform:scale(.97)}
        .btn-p{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;box-shadow:0 6px 30px rgba(59,130,246,.35)}
        .btn-s{background:var(--w05);border:1px solid rgba(255,255,255,.08);color:var(--w50);font-size:14px;padding:12px}
        .card{padding:12px 14px;border-radius:12px;background:rgba(255,255,255,.03);margin-bottom:8px}
        .dist{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.15);font-size:11px;font-weight:600;color:var(--c2);font-family:var(--mono)}
        .saving{position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:300;background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.3);border-radius:20px;padding:6px 16px;font-size:12px;color:#10b981;font-weight:600;transition:opacity .3s;opacity:0;pointer-events:none}
        .bottom-nav{position:fixed;bottom:0;left:0;right:0;background:rgba(10,22,40,.95);backdrop-filter:blur(12px);border-top:1px solid rgba(255,255,255,.06);display:flex;z-index:50;padding-bottom:env(safe-area-inset-bottom,0)}
        .nav-btn{flex:1;padding:10px 0 8px;text-align:center;cursor:pointer;color:var(--w30);font-size:11px;font-weight:600;border:none;background:none;font-family:var(--font)}
        .nav-btn.active{color:var(--c2)}
        .nav-icon{font-size:20px;display:block;margin-bottom:2px}
        .team-tabs{display:flex;gap:6px;margin-bottom:16px;overflow-x:auto;padding-bottom:4px}
        .team-tab{padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;border:1px solid rgba(255,255,255,.08);background:var(--w05);color:var(--w50);flex-shrink:0}
        .team-tab.active{background:rgba(59,130,246,.15);border-color:rgba(59,130,246,.3);color:var(--c2)}
        .team-tab.add{border-style:dashed;color:var(--w30)}
        .back-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;background:var(--w05);border:1px solid rgba(255,255,255,.08);color:var(--w50);font-size:13px;font-weight:500;cursor:pointer;margin-bottom:16px;font-family:var(--font)}
        .export-btn{display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:8px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);color:#10b981;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font);text-decoration:none}
        table.splits-tbl{width:100%;border-collapse:collapse;font-size:12px}
        .splits-tbl th{text-align:left;padding:6px 8px;border-bottom:1px solid rgba(255,255,255,.1);color:var(--w30);font-weight:600;font-size:11px;position:sticky;top:0;background:var(--bg)}
        .splits-tbl td{padding:5px 8px;border-bottom:1px solid rgba(255,255,255,.04);font-family:var(--mono);font-size:12px}
        .splits-tbl tr:hover td{background:rgba(255,255,255,.02)}
    </style>
</head>
<body>
    <div id="savingIndicator" class="saving">Opgeslagen ✓</div>

    <!-- ═══ SETUP ═══ -->
    <div id="setup" class="view active" style="overflow-y:auto">
        <div class="inner">
            <div style="text-align:center;margin-bottom:20px">
                <div class="lbl">Marathon Estafette</div>
                <div class="title">3 × 50m ∞</div>
            </div>
            <div class="team-tabs" id="teamTabs"></div>
            <div id="teamConfig"></div>
            <div style="padding:14px;border-radius:12px;margin-bottom:16px;background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.12);font-size:13px;color:rgba(255,255,255,.45);line-height:1.7">
                <div style="font-size:12px;font-weight:700;color:var(--w50);margin-bottom:8px">📋 HOE HET WERKT</div>
                <span style="color:var(--w70)">1.</span> Maak ploegen aan · <span style="color:var(--w70)">2.</span> Telefoon <strong style="color:var(--w70)">plat op de kant</strong><br>
                <span style="color:var(--w70)">3.</span> Vertrekkende zwemmer tikt = split · <span style="color:var(--w70)">4.</span> 🏁 Finish als klaar
            </div>
            <button class="btn btn-p" onclick="startTeam()" id="startBtn" style="margin-bottom:12px">▶ Start Ploeg</button>
        </div>
    </div>

    <!-- ═══ COUNTDOWN ═══ -->
    <div id="countdown" class="view" style="position:fixed;inset:0;z-index:200;background:var(--bg);align-items:center;justify-content:center">
        <div id="countdownNum" style="font-family:var(--mono);font-size:min(40vw,180px);font-weight:700;color:var(--c1);animation:countPop 1s ease-in-out infinite">3</div>
        <div id="countdownTeam" style="font-size:16px;color:var(--w30);font-weight:600;margin-top:12px"></div>
        <style>#countdown.go #countdownNum{animation:none !important;transform:scale(1.2)}</style>
    </div>

    <!-- ═══ TIMER ═══ -->
    <div id="timer" class="view" style="position:fixed;inset:0;z-index:100;align-items:center;justify-content:center;cursor:pointer"
         ontouchstart="handleTimerTouch(event)" onclick="handleTimerClick(event)">
        <div id="flashOverlay" style="position:absolute;inset:0;pointer-events:none;opacity:0"></div>
        <div id="edgeRing" style="position:absolute;inset:8px;border-radius:20px;border:3px solid rgba(255,255,255,.15);animation:edgePulse 2s ease-in-out infinite;pointer-events:none"></div>
        <div style="position:absolute;top:14px;left:14px;display:flex;flex-direction:column;gap:4px">
            <div id="teamBadge" style="font-size:12px;font-weight:700;color:var(--w50)"></div>
            <div id="roundBadge" style="background:rgba(255,255,255,.06);border-radius:8px;padding:4px 10px;font-size:12px;font-weight:600;color:var(--w30);font-family:var(--mono);display:inline-block"></div>
        </div>
        <div id="finishArea" style="position:absolute;top:14px;right:14px;display:flex;gap:6px;z-index:10"></div>
        <div id="distTotal" class="dist" style="position:absolute;top:60px;right:14px"></div>
        <div id="timerDisplay" style="font-family:var(--mono);font-size:min(20vw,80px);font-weight:700;letter-spacing:-.04em;line-height:1">0.00</div>
        <div id="swimmerBadge" style="margin-top:16px;display:flex;align-items:center;gap:10px;padding:8px 20px;border-radius:40px">
            <div id="swimmerDot" style="width:10px;height:10px;border-radius:50%;animation:edgePulse 1s ease-in-out infinite"></div>
            <span id="swimmerName" style="font-size:18px;font-weight:700"></span>
        </div>
        <div id="splitsLive" style="margin-top:28px;display:flex;flex-direction:column;gap:4px;width:80%;max-width:320px"></div>
        <div id="swimmerAvgs" style="position:absolute;bottom:90px;left:12px;right:12px;display:flex;gap:6px"></div>
        <div style="position:absolute;bottom:40px;display:flex;flex-direction:column;align-items:center;gap:6px">
            <div style="font-size:32px;animation:bobble 1.5s ease-in-out infinite">👆</div>
            <span style="font-size:15px;color:var(--w30);font-weight:600">TIK OVERAL</span>
        </div>
    </div>

    <!-- ═══ RESULTS (after finish) ═══ -->
    <div id="results" class="view" style="overflow-y:auto"><div class="inner" id="resultsInner"></div></div>

    <!-- ═══ HISTORY ═══ -->
    <div id="history" class="view" style="overflow-y:auto"><div class="inner" id="historyInner"></div></div>

    <!-- ═══ DETAIL (ploeg detail from DB) ═══ -->
    <div id="detail" class="view" style="overflow-y:auto"><div class="inner" id="detailInner"></div></div>

    <!-- ═══ PIN MODAL ═══ -->
    <div id="pinModal" style="display:none;position:fixed;inset:0;z-index:500;background:rgba(10,22,40,.95);backdrop-filter:blur(12px);
        display:none;flex-direction:column;align-items:center;justify-content:center;padding:20px">
        <div style="width:100%;max-width:320px;text-align:center">
            <div style="font-size:40px;margin-bottom:16px">🔒</div>
            <div style="font-size:18px;font-weight:700;margin-bottom:8px">PIN vereist</div>
            <div style="font-size:13px;color:var(--w30);margin-bottom:24px">Voer de PIN in om geschiedenis en exports te bekijken</div>
            <input type="tel" id="pinInput" class="inp" maxlength="8" placeholder="PIN"
                   style="text-align:center;font-size:24px;letter-spacing:8px;font-family:var(--mono);margin-bottom:12px"
                   onkeydown="if(event.key==='Enter')verifyPin()">
            <div id="pinError" style="font-size:12px;color:#ef4444;margin-bottom:12px;opacity:0">Onjuiste PIN</div>
            <button onclick="verifyPin()" style="width:100%;padding:14px;background:linear-gradient(135deg,#3b82f6,#2563eb);
                border:none;border-radius:12px;color:#fff;font-size:16px;font-weight:700;cursor:pointer;font-family:var(--font)">Ontgrendel</button>
            <button onclick="closePinModal()" style="width:100%;padding:10px;margin-top:8px;background:none;border:none;
                color:var(--w30);font-size:13px;cursor:pointer;font-family:var(--font)">Annuleren</button>
        </div>
    </div>

    <!-- ═══ DASHBOARD ═══ -->
    <div id="dashboard" class="view" style="overflow-y:auto"><div class="inner" id="dashboardInner"></div></div>

    <!-- ═══ NAV ═══ -->
    <div class="bottom-nav" id="bottomNav" style="display:none">
        <button class="nav-btn active" onclick="showView('setup')" data-view="setup"><span class="nav-icon">⏱️</span>Timer</button>
        <button class="nav-btn" onclick="showView('dashboard')" data-view="dashboard"><span class="nav-icon">🏅</span>Dashboard</button>
        <button class="nav-btn" onclick="showView('history')" data-view="history"><span class="nav-icon">📊</span>Sessies</button>
    </div>

<script>
const API=location.origin+'/api/swim',LAP_M=50,DEBOUNCE=500;
const COLORS=['#f59e0b','#3b82f6','#10b981','#a855f7','#ef4444','#ec4899','#14b8a6','#f97316'];
const MONTHS=['jan','feb','mrt','apr','mei','jun','jul','aug','sep','okt','nov','dec'];
const ROSTER=['Chantal','Mirelle','Marleen','Jan','Deacon','Teun','Karin','Lieke','Renko','Sophie','Jack','Floor','Femke','Eef','Yara','Kristian','Jerre'];

// ─── State ───
let teams=[],activeTeamIdx=0;
let authed=false, pendingView=null;
let nextPloegNumber=1;
let T={startTime:null,startedAt:null,swimmer:0,splits:[],elapsed:0,rafId:null,lastTap:0,wakeLock:null,confirmFinish:false,finishLockedTime:null,teamName:'',names:[]};

// ─── Fmt ───
const fmt=ms=>{if(ms==null)return'—';const m=Math.floor(ms/60000),s=Math.floor((ms%60000)/1000),c=Math.floor((ms%1000)/10);return m>0?`${m}:${String(s).padStart(2,'0')}.${String(c).padStart(2,'0')}`:`${s}.${String(c).padStart(2,'0')}`;};
const fmtLong=ms=>{if(ms==null)return'—';const h=Math.floor(ms/3600000),m=Math.floor((ms%3600000)/60000),s=Math.floor((ms%60000)/1000),c=Math.floor((ms%1000)/10);if(h>0)return`${h}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}.${String(c).padStart(2,'0')}`;if(m>0)return`${m}:${String(s).padStart(2,'0')}.${String(c).padStart(2,'0')}`;return`${s}.${String(c).padStart(2,'0')}`;};
const fmtSec=s=>s!=null?s.toFixed(2):'—';
const fmtDist=m=>m>=1000?`${(m/1000).toFixed(1)}km`:`${m}m`;
const genName=n=>{const d=new Date();return`${d.getDate()} ${MONTHS[d.getMonth()]} - Ploeg ${n}`;};

// ─── Teams ───
function addTeam(){const saved=JSON.parse(localStorage.getItem('swimNames')||'null');const num=nextPloegNumber+teams.length;teams.push({id:Date.now(),name:genName(num),ploegNum:num,swimmers:saved&&saved.length?[...saved]:['Renko','Teun','Jan']});activeTeamIdx=teams.length-1;renderSetup();}
function removeTeam(i){if(teams.length<=1)return;teams.splice(i,1);if(activeTeamIdx>=teams.length)activeTeamIdx=teams.length-1;renderSetup();}
function setSwimmer(ti,si,val){teams[ti].swimmers[si]=val;localStorage.setItem('swimNames',JSON.stringify(teams[ti].swimmers));renderSetup();}
function addSwimmerSlot(){const t=teams[activeTeamIdx];const used=new Set(t.swimmers);const next=ROSTER.find(n=>!used.has(n))||'Zwemmer '+(t.swimmers.length+1);t.swimmers.push(next);renderSetup();}
function removeSwimmerSlot(si){const t=teams[activeTeamIdx];if(t.swimmers.length<=2)return;t.swimmers.splice(si,1);renderSetup();}
function renderSetup(){
    document.getElementById('teamTabs').innerHTML=teams.map((t,i)=>`<div class="team-tab ${i===activeTeamIdx?'active':''}" onclick="activeTeamIdx=${i};renderSetup()">${t.name}${teams.length>1?`<span onclick="event.stopPropagation();removeTeam(${i})" style="margin-left:6px;opacity:.4;cursor:pointer">✕</span>`:''}</div>`).join('')+`<div class="team-tab add" onclick="addTeam()">+ Ploeg</div>`;
    const t=teams[activeTeamIdx];
    const usedNames=new Set(t.swimmers);
    document.getElementById('teamConfig').innerHTML=`<div style="display:flex;flex-direction:column;gap:6px;margin-bottom:12px">
        ${t.swimmers.map((n,i)=>{
            const ci=COLORS[i%COLORS.length];
            const opts=ROSTER.map(r=>`<option value="${r}" ${r===n?'selected':''}${usedNames.has(r)&&r!==n?' disabled':''}>${r}${usedNames.has(r)&&r!==n?' (in ploeg)':''}</option>`).join('');
            return`<div style="display:flex;gap:6px;align-items:center">
                <div style="width:10px;height:10px;border-radius:50%;background:${ci};flex-shrink:0"></div>
                <select class="inp" onchange="setSwimmer(${activeTeamIdx},${i},this.value)" style="border-color:${ci}25;padding:8px 12px;cursor:pointer;appearance:auto;-webkit-appearance:auto">
                    ${opts}
                    <option disabled>──────</option>
                    <option value="${n}" ${!ROSTER.includes(n)?'selected':''}>${!ROSTER.includes(n)?n+' (handmatig)':'Typ zelf...'}</option>
                </select>
                ${t.swimmers.length>2?`<div onclick="removeSwimmerSlot(${i})" style="width:28px;height:28px;border-radius:8px;background:rgba(239,68,68,.1);display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;color:#ef4444;font-size:14px;font-weight:700">✕</div>`:''}
            </div>`;
        }).join('')}
        <button onclick="addSwimmerSlot()" style="padding:8px;border-radius:8px;border:1px dashed rgba(255,255,255,.12);background:none;color:var(--w30);font-size:13px;cursor:pointer;font-family:var(--font)">+ Zwemmer toevoegen</button>
    </div>`;
    document.getElementById('startBtn').textContent=`▶ Start ${t.name}`;
}

// ─── Views ───
function showView(id){
    // Gate protected views behind PIN
    if((id==='history'||id==='detail'||id==='dashboard')&&!authed){pendingView=id;showPinModal();return;}
    document.querySelectorAll('.view').forEach(v=>v.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    document.querySelectorAll('.nav-btn').forEach(b=>b.classList.toggle('active',b.dataset.view===id));
    document.getElementById('bottomNav').style.display=(id==='timer'||id==='countdown')?'none':'flex';
    if(id==='history')loadHistory();
    if(id==='dashboard')loadDashboard();
    if(id==='setup')renderSetup();
}

// ─── PIN Auth ───
function showPinModal(){document.getElementById('pinModal').style.display='flex';document.getElementById('pinInput').value='';document.getElementById('pinError').style.opacity='0';setTimeout(()=>document.getElementById('pinInput').focus(),100);}
function closePinModal(){document.getElementById('pinModal').style.display='none';pendingView=null;}
async function verifyPin(){
    const pin=document.getElementById('pinInput').value;
    if(!pin)return;
    try{
        const r=await fetch(API+'/auth',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({pin}),credentials:'same-origin'});
        const d=await r.json();
        if(d.success){authed=true;document.getElementById('pinModal').style.display='none';if(pendingView){showView(pendingView);pendingView=null;}}
        else{document.getElementById('pinError').style.opacity='1';document.getElementById('pinInput').value='';document.getElementById('pinInput').focus();}
    }catch{document.getElementById('pinError').style.opacity='1';}
}

// ─── Countdown & Timer (same as before, compressed) ───
// ─── Sound Effects (Web Audio API) ───
let audioCtx=null;
function getAudio(){if(!audioCtx)audioCtx=new(window.AudioContext||window.webkitAudioContext)();return audioCtx;}
function playBeep(freq=880,dur=0.15,vol=0.3){
    const ctx=getAudio(),osc=ctx.createOscillator(),gain=ctx.createGain();
    osc.connect(gain);gain.connect(ctx.destination);
    osc.frequency.value=freq;osc.type='sine';
    gain.gain.setValueAtTime(vol,ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001,ctx.currentTime+dur);
    osc.start(ctx.currentTime);osc.stop(ctx.currentTime+dur);
}
function playShot(){
    const ctx=getAudio(),now=ctx.currentTime;
    // Screamed "GO!" — formant synthesis
    // "G" consonant: short noise burst
    const gLen=0.06,gBuf=ctx.createBuffer(1,ctx.sampleRate*gLen,ctx.sampleRate),gData=gBuf.getChannelData(0);
    for(let i=0;i<gData.length;i++)gData[i]=(Math.random()*2-1)*Math.pow(1-i/gData.length,2);
    const gNoise=ctx.createBufferSource();gNoise.buffer=gBuf;
    const gFilt=ctx.createBiquadFilter();gFilt.type='bandpass';gFilt.frequency.value=300;gFilt.Q.value=2;
    const gGain=ctx.createGain();gGain.gain.setValueAtTime(0.6,now);gGain.gain.exponentialRampToValueAtTime(0.001,now+gLen);
    gNoise.connect(gFilt);gFilt.connect(gGain);gGain.connect(ctx.destination);gNoise.start(now);

    // "O" vowel: fundamental + formants (screaming = high intensity, slight pitch rise)
    const dur=0.45,vowelStart=now+0.04;
    const fund=ctx.createOscillator(),fGain=ctx.createGain();
    fund.type='sawtooth';
    fund.frequency.setValueAtTime(180,vowelStart);
    fund.frequency.linearRampToValueAtTime(220,vowelStart+0.1);
    fund.frequency.linearRampToValueAtTime(200,vowelStart+dur);
    fGain.gain.setValueAtTime(0,vowelStart);
    fGain.gain.linearRampToValueAtTime(0.5,vowelStart+0.03);
    fGain.gain.setValueAtTime(0.5,vowelStart+dur*0.6);
    fGain.gain.exponentialRampToValueAtTime(0.001,vowelStart+dur);

    // Formant 1 (O vowel ~500Hz)
    const f1=ctx.createBiquadFilter();f1.type='bandpass';f1.frequency.value=500;f1.Q.value=5;
    // Formant 2 (O vowel ~1000Hz)
    const f2=ctx.createBiquadFilter();f2.type='bandpass';f2.frequency.value=1000;f2.Q.value=5;
    const f2Gain=ctx.createGain();f2Gain.gain.value=0.7;
    // Formant 3 (presence/scream ~2800Hz)
    const f3=ctx.createBiquadFilter();f3.type='bandpass';f3.frequency.value=2800;f3.Q.value=4;
    const f3Gain=ctx.createGain();f3Gain.gain.value=0.5;

    fund.connect(f1);f1.connect(fGain);fGain.connect(ctx.destination);
    fund.connect(f2);f2.connect(f2Gain);f2Gain.connect(fGain);
    fund.connect(f3);f3.connect(f3Gain);f3Gain.connect(fGain);
    fund.start(vowelStart);fund.stop(vowelStart+dur);

    // Second voice (slightly detuned = thicker scream)
    const fund2=ctx.createOscillator(),f2Gain2=ctx.createGain();
    fund2.type='sawtooth';
    fund2.frequency.setValueAtTime(184,vowelStart);
    fund2.frequency.linearRampToValueAtTime(224,vowelStart+0.1);
    fund2.frequency.linearRampToValueAtTime(204,vowelStart+dur);
    f2Gain2.gain.setValueAtTime(0,vowelStart);
    f2Gain2.gain.linearRampToValueAtTime(0.3,vowelStart+0.03);
    f2Gain2.gain.setValueAtTime(0.3,vowelStart+dur*0.6);
    f2Gain2.gain.exponentialRampToValueAtTime(0.001,vowelStart+dur);
    const f1b=ctx.createBiquadFilter();f1b.type='bandpass';f1b.frequency.value=520;f1b.Q.value=4;
    fund2.connect(f1b);f1b.connect(f2Gain2);f2Gain2.connect(ctx.destination);
    fund2.start(vowelStart);fund2.stop(vowelStart+dur);

    // Breath/air noise layer (scream texture)
    const nLen=dur,nBuf=ctx.createBuffer(1,ctx.sampleRate*nLen,ctx.sampleRate),nData=nBuf.getChannelData(0);
    for(let i=0;i<nData.length;i++)nData[i]=(Math.random()*2-1);
    const breathNoise=ctx.createBufferSource();breathNoise.buffer=nBuf;
    const bFilt=ctx.createBiquadFilter();bFilt.type='bandpass';bFilt.frequency.value=3000;bFilt.Q.value=1;
    const bGain=ctx.createGain();
    bGain.gain.setValueAtTime(0,vowelStart);
    bGain.gain.linearRampToValueAtTime(0.12,vowelStart+0.03);
    bGain.gain.setValueAtTime(0.12,vowelStart+dur*0.6);
    bGain.gain.exponentialRampToValueAtTime(0.001,vowelStart+dur);
    breathNoise.connect(bFilt);bFilt.connect(bGain);bGain.connect(ctx.destination);
    breathNoise.start(vowelStart);
}

function startTeam(){const t=teams[activeTeamIdx];T={startTime:null,startedAt:null,swimmer:0,splits:[],elapsed:0,rafId:null,lastTap:0,wakeLock:null,confirmFinish:false,finishLockedTime:null,teamName:t.name,names:[...t.swimmers]};showView('countdown');document.getElementById('countdownTeam').textContent=t.name;
    // Init audio context on user gesture
    getAudio();
    let c=3;document.getElementById('countdownNum').textContent=c;document.getElementById('countdownNum').style.color='var(--c1)';
    playBeep(660,0.15,0.4);
    const iv=setInterval(()=>{c--;
        if(c>0){document.getElementById('countdownNum').textContent=c;playBeep(660,0.15,0.4);}
        else{clearInterval(iv);document.getElementById('countdownNum').textContent='GO!';document.getElementById('countdownNum').style.color='var(--c3)';playShot();
            setTimeout(()=>{T.startTime=Date.now();T.startedAt=new Date().toISOString();showView('timer');acquireWL();updateTimerUI();tickFn();},400);}
    },1000);}
function tickFn(){T.elapsed=Date.now()-T.startTime;document.getElementById('timerDisplay').textContent=fmtLong(T.elapsed);T.rafId=requestAnimationFrame(tickFn);}
function handleTimerTouch(e){if(e.target.closest('#finishArea'))return;e.preventDefault();recordSplit();}
function handleTimerClick(e){if(e.target.closest('#finishArea'))return;recordSplit();}
function recordSplit(){const now=Date.now();if(now-T.lastTap<DEBOUNCE)return;T.lastTap=now;const total=now-T.startTime,prev=T.splits.length?T.splits[T.splits.length-1].total:0,lap=total-prev,round=Math.floor(T.splits.length/T.names.length)+1;
T.splits.push({swimmer_index:T.swimmer,swimmer_name:T.names[T.swimmer],round,split_number:T.splits.length+1,lap_time_ms:lap,total_time_ms:total,total,lap,name:T.names[T.swimmer]});
const ov=document.getElementById('flashOverlay'),col=COLORS[T.swimmer%COLORS.length];ov.style.background=`radial-gradient(circle at center,${col}50 0%,${col}20 50%,transparent 100%)`;ov.style.animation='none';ov.offsetHeight;ov.style.animation='flashIn .3s ease-out forwards';
T.swimmer=(T.swimmer+1)%T.names.length;T.confirmFinish=false;T.finishLockedTime=null;updateTimerUI();}
function updateTimerUI(){const col=COLORS[T.swimmer%COLORS.length],round=Math.floor(T.splits.length/T.names.length)+1,dist=T.splits.length*LAP_M;
document.getElementById('edgeRing').style.borderColor=col+'30';document.getElementById('teamBadge').textContent=T.teamName;
document.getElementById('roundBadge').textContent=`Ronde ${round} · ${T.splits.length} splits`;
document.getElementById('distTotal').innerHTML=`🏊 ${fmtDist(dist)}`;
document.getElementById('swimmerBadge').style.cssText=`margin-top:16px;display:flex;align-items:center;gap:10px;padding:8px 20px;border-radius:40px;background:${col}20;border:2px solid ${col}40`;
document.getElementById('swimmerDot').style.cssText=`width:10px;height:10px;border-radius:50%;background:${col};box-shadow:0 0 10px ${col};animation:edgePulse 1s ease-in-out infinite`;
document.getElementById('swimmerName').textContent=T.names[T.swimmer];
const last3=T.splits.slice(-3).reverse();
document.getElementById('splitsLive').innerHTML=last3.map((s,i)=>`<div style="display:flex;justify-content:space-between;padding:6px 14px;border-radius:8px;background:var(--w05);opacity:${i===0?1:.5+.2*(2-i)}"><div style="display:flex;align-items:center;gap:8px"><div style="width:8px;height:8px;border-radius:50%;background:${COLORS[s.swimmer_index%COLORS.length]}"></div><span style="font-size:12px;color:var(--w50)">R${s.round} ${s.name}</span></div><span style="font-family:var(--mono);font-size:15px;font-weight:700">${fmt(s.lap)}</span></div>`).join('');
// Avgs
const av=document.getElementById('swimmerAvgs');
if(T.splits.length){av.innerHTML=T.names.map((name,i)=>{const laps=T.splits.filter(s=>s.swimmer_index===i),ci=COLORS[i%COLORS.length],act=T.swimmer===i,d=laps.length*LAP_M;if(!laps.length)return`<div style="flex:1;padding:8px 6px;border-radius:10px;background:rgba(255,255,255,.03);text-align:center;opacity:.3"><div style="font-size:10px;color:var(--w30)">${name.split(' ')[0]}</div><div style="font-family:var(--mono);font-size:13px;color:var(--w30)">—</div></div>`;const ts=laps.map(l=>l.lap),avg=ts.reduce((a,b)=>a+b,0)/ts.length,best=Math.min(...ts);return`<div style="flex:1;padding:8px 6px;border-radius:10px;background:${act?ci+'15':'rgba(255,255,255,.03)'};border:1px solid ${act?ci+'30':'transparent'};text-align:center"><div style="font-size:10px;color:${act?ci:'var(--w30)'};font-weight:600">${name.split(' ')[0]}</div><div style="font-family:var(--mono);font-size:15px;font-weight:700">${fmt(avg)}</div><div style="font-size:9px;color:var(--w30)">⚡${fmt(best)} · ${fmtDist(d)}</div></div>`;}).join('');}else av.innerHTML='';
renderFinishBtn();}
function toggleFinishConfirm(){if(T.confirmFinish){finishRace();}else{T.finishLockedTime=Date.now()-T.startTime;T.confirmFinish=true;renderFinishBtn();}}
function cancelFinish(){T.confirmFinish=false;T.finishLockedTime=null;renderFinishBtn();}
function renderFinishBtn(){const a=document.getElementById('finishArea');a.innerHTML=T.confirmFinish?`<button ontouchstart="event.stopPropagation()" onclick="event.stopPropagation();finishRace()" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.4);border-radius:8px;padding:6px 14px;cursor:pointer;color:#ef4444;font-size:12px;font-weight:700;font-family:var(--font)">🏁 Bevestig (${fmtLong(T.finishLockedTime)})</button><button ontouchstart="event.stopPropagation()" onclick="event.stopPropagation();cancelFinish()" style="background:rgba(255,255,255,.06);border:none;border-radius:8px;padding:6px 10px;cursor:pointer;color:var(--w30);font-size:12px;font-weight:600;font-family:var(--font)">✕</button>`:`<button ontouchstart="event.stopPropagation()" onclick="event.stopPropagation();toggleFinishConfirm()" style="background:rgba(255,255,255,.06);border:none;border-radius:8px;padding:6px 14px;cursor:pointer;color:var(--w30);font-size:12px;font-weight:600;font-family:var(--font)">🏁 Finish</button>`;}
function finishRace(){if(T.rafId)cancelAnimationFrame(T.rafId);releaseWL();const tot=T.finishLockedTime||(T.splits.length?T.splits[T.splits.length-1].total:T.elapsed);saveSession(tot);showLocalResults(tot);}

// ─── Local Results (after timer finish) ───
function showLocalResults(tot){
    const names=T.names,sp=T.splits,rounds=Math.ceil(sp.length/names.length),dist=sp.length*LAP_M;
    let h=`<div style="text-align:center;margin-bottom:24px"><div class="lbl">${T.teamName}</div>
        <div style="font-family:var(--mono);font-size:48px;font-weight:700;color:#10b981;line-height:1;margin:8px 0">${fmtLong(tot)}</div>
        <div style="font-size:14px;color:#10b981;font-weight:600;margin-bottom:8px">🏁 ${sp.length} splits · ${rounds} rondes</div>
        <span class="dist" style="font-size:14px;padding:6px 16px">🏊 ${fmtDist(dist)}</span></div>`;
    h+=buildStatsHTML(names,sp);
    h+=buildSplitsTableHTML(names,sp);
    h+=`<button class="btn btn-p" onclick="showView('setup')" style="margin-top:20px">🔄 Volgende ploeg</button>`;
    document.getElementById('resultsInner').innerHTML=h;
    showView('results');
}

// ─── Shared HTML builders ───
function buildStatsHTML(names,splits){
    let h=`<div class="lbl" style="margin-bottom:8px">Per zwemmer</div>`;
    names.forEach((name,i)=>{
        const laps=splits.filter(s=>(s.swimmer_index??s.swimmer)===i);
        if(!laps.length)return;
        const ts=laps.map(l=>l.lap_time_ms||l.lap),avg=ts.reduce((a,b)=>a+b,0)/ts.length,best=Math.min(...ts),worst=Math.max(...ts),d=laps.length*LAP_M;
        const ci=COLORS[i%COLORS.length];
        h+=`<div class="card" style="border:1px solid ${ci}20"><div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
            <div style="width:10px;height:10px;border-radius:50%;background:${ci}"></div>
            <span style="font-size:14px;font-weight:700">${name}</span>
            <span class="dist" style="margin-left:auto">${fmtDist(d)}</span>
            <span style="font-size:12px;color:var(--w30)">${laps.length} banen</span></div>
            <div style="display:flex;gap:8px">
                <div style="flex:1;padding:6px;border-radius:8px;background:rgba(255,255,255,.03);text-align:center"><div style="font-size:10px;color:var(--w30);margin-bottom:2px">Gem.</div><div style="font-family:var(--mono);font-size:14px;font-weight:700;color:${ci}">${fmt(avg)}</div></div>
                <div style="flex:1;padding:6px;border-radius:8px;background:rgba(255,255,255,.03);text-align:center"><div style="font-size:10px;color:var(--w30);margin-bottom:2px">Snelste</div><div style="font-family:var(--mono);font-size:14px;font-weight:700;color:#10b981">${fmt(best)}</div></div>
                <div style="flex:1;padding:6px;border-radius:8px;background:rgba(255,255,255,.03);text-align:center"><div style="font-size:10px;color:var(--w30);margin-bottom:2px">Langzaamst</div><div style="font-family:var(--mono);font-size:14px;font-weight:700;color:var(--w50)">${fmt(worst)}</div></div>
            </div></div>`;
    });
    return h;
}

function buildSplitsTableHTML(names,splits){
    // Group by round
    const byRound={};
    splits.forEach(s=>{const r=s.round;if(!byRound[r])byRound[r]={};byRound[r][(s.swimmer_index??s.swimmer)]=s;});
    const roundNums=Object.keys(byRound).map(Number).sort((a,b)=>a-b);

    let h=`<div class="lbl" style="margin:16px 0 8px">Alle rondes</div>
    <div style="max-height:300px;overflow-y:auto;border-radius:12px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02)">
    <table class="splits-tbl"><thead><tr><th>#</th>`;
    names.forEach((n,i)=>h+=`<th style="color:${COLORS[i%COLORS.length]}">${n}</th>`);
    h+=`<th>Totaal</th></tr></thead><tbody>`;
    roundNums.forEach(r=>{
        h+=`<tr><td style="color:var(--w30)">${r}</td>`;
        names.forEach((n,i)=>{
            const s=byRound[r]?.[i];
            const t=s?(s.lap_time_ms||s.lap):null;
            h+=`<td>${t!=null?fmtSec(t/1000):'—'}</td>`;
        });
        // Find last split in this round for cumulative
        const roundSplits=Object.values(byRound[r]||{});
        const lastTotal=roundSplits.length?Math.max(...roundSplits.map(s=>s.total_time_ms||s.total||0)):0;
        h+=`<td style="color:var(--w30)">${lastTotal?fmtLong(lastTotal):'—'}</td></tr>`;
    });
    h+=`</tbody></table></div>`;
    return h;
}

function drawChart(canvasId, names, splits){
    setTimeout(()=>{
        const canvas=document.getElementById(canvasId);
        if(!canvas)return;
        const ctx=canvas.getContext('2d');
        const W=canvas.width=canvas.offsetWidth*2,H=canvas.height=canvas.offsetHeight*2;
        ctx.scale(2,2);
        const w=W/2,h=H/2;
        const pad={t:10,r:10,b:24,l:40};

        // Per swimmer, get lap times in order
        const series=names.map((n,i)=>splits.filter(s=>(s.swimmer_index??s.swimmer)===i).map(s=>(s.lap_time_ms||s.lap)/1000));
        const allVals=series.flat().filter(v=>v>0);
        if(!allVals.length)return;
        const minV=Math.min(...allVals)*.95,maxV=Math.max(...allVals)*1.05;
        const maxLen=Math.max(...series.map(s=>s.length));

        function x(i){return pad.l+i/(maxLen-1||1)*(w-pad.l-pad.r);}
        function y(v){return pad.t+(1-(v-minV)/(maxV-minV))*(h-pad.t-pad.b);}

        // Grid
        ctx.strokeStyle='rgba(255,255,255,.06)';ctx.lineWidth=1;
        for(let i=0;i<5;i++){const yy=pad.t+i/4*(h-pad.t-pad.b);ctx.beginPath();ctx.moveTo(pad.l,yy);ctx.lineTo(w-pad.r,yy);ctx.stroke();
            ctx.fillStyle='rgba(255,255,255,.2)';ctx.font='9px JetBrains Mono';ctx.textAlign='right';
            ctx.fillText(fmtSec(maxV-(maxV-minV)*i/4),pad.l-4,yy+3);}

        // Lines
        series.forEach((data,si)=>{
            if(!data.length)return;
            ctx.strokeStyle=COLORS[si%COLORS.length];ctx.lineWidth=1.5;ctx.beginPath();
            data.forEach((v,j)=>{j===0?ctx.moveTo(x(j),y(v)):ctx.lineTo(x(j),y(v));});
            ctx.stroke();
        });

        // X labels
        ctx.fillStyle='rgba(255,255,255,.2)';ctx.font='9px JetBrains Mono';ctx.textAlign='center';
        [0,Math.floor(maxLen/2),maxLen-1].forEach(i=>{if(i<maxLen)ctx.fillText(i+1,x(i),h-4);});
    },50);
}

// ─── API ───
async function saveSession(tot){const p={name:null,team_name:T.teamName,total_time_ms:tot,lap_distance_m:LAP_M,swimmers:T.names,started_at:T.startedAt,splits:T.splits.map(s=>({swimmer_index:s.swimmer_index,swimmer_name:s.swimmer_name,round:s.round,split_number:s.split_number,lap_time_ms:s.lap_time_ms,total_time_ms:s.total_time_ms}))};try{const r=await fetch(API+'/sessions',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(p)});if(r.ok)showSaving();else storeLocal(p);}catch{storeLocal(p);}}
function storeLocal(p){const q=JSON.parse(localStorage.getItem('pending')||'[]');q.push(p);localStorage.setItem('pending',JSON.stringify(q));showSaving('Offline opgeslagen');}
async function syncPending(){const q=JSON.parse(localStorage.getItem('pending')||'[]');if(!q.length)return;const rem=[];for(const p of q){try{const r=await fetch(API+'/sessions',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(p)});if(!r.ok)rem.push(p);}catch{rem.push(p);}}localStorage.setItem('pending',JSON.stringify(rem));}
function showSaving(t='Opgeslagen ✓'){const e=document.getElementById('savingIndicator');e.textContent=t;e.style.opacity='1';setTimeout(()=>e.style.opacity='0',2000);}

// ─── Auth-aware fetch ───
async function authFetch(url,opts={}){
    opts.credentials='same-origin';
    const r=await fetch(url,opts);
    if(r.status===401){authed=false;pendingView='history';showPinModal();throw new Error('auth');}
    return r;
}

// ─── Dashboard ───
async function loadDashboard(){
    const c=document.getElementById('dashboardInner');
    c.innerHTML=`<div style="text-align:center;margin-bottom:24px"><div class="lbl">Dashboard</div><div class="title">Team Overzicht</div></div><div style="text-align:center;color:var(--w30);padding:40px 0">Laden...</div>`;
    try{
        const [sR,seR]=await Promise.all([authFetch(API+'/swimmers/stats'),authFetch(API+'/sessions?per_page=100')]);
        const stats=await sR.json(),seD=await seR.json(),sessions=seD.data||seD;

        // Totals
        const totalSessions=sessions.length;
        const totalSplits=sessions.reduce((a,s)=>a+s.total_splits,0);
        const totalDistM=sessions.reduce((a,s)=>a+(s.total_distance_m||(s.total_splits*LAP_M)),0);
        const totalTimeMs=sessions.reduce((a,s)=>a+s.total_time_ms,0);

        let h=`<div style="text-align:center;margin-bottom:24px"><div class="lbl">Dashboard</div><div class="title">Team Overzicht</div></div>`;

        // Totals row
        h+=`<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px">
            <div class="card" style="text-align:center;border:1px solid rgba(59,130,246,.15)">
                <div style="font-size:10px;color:var(--w30);margin-bottom:4px">Totale afstand</div>
                <div style="font-family:var(--mono);font-size:22px;font-weight:700;color:var(--c2)">${fmtDist(totalDistM)}</div></div>
            <div class="card" style="text-align:center;border:1px solid rgba(16,185,129,.15)">
                <div style="font-size:10px;color:var(--w30);margin-bottom:4px">Totaal banen</div>
                <div style="font-family:var(--mono);font-size:22px;font-weight:700;color:var(--c3)">${totalSplits}</div></div>
            <div class="card" style="text-align:center;border:1px solid rgba(245,158,11,.15)">
                <div style="font-size:10px;color:var(--w30);margin-bottom:4px">Sessies</div>
                <div style="font-family:var(--mono);font-size:22px;font-weight:700;color:var(--c1)">${totalSessions}</div></div>
            <div class="card" style="text-align:center;border:1px solid rgba(168,85,247,.15)">
                <div style="font-size:10px;color:var(--w30);margin-bottom:4px">Totale zwemtijd</div>
                <div style="font-family:var(--mono);font-size:22px;font-weight:700;color:var(--c4)">${fmtLong(totalTimeMs)}</div></div>
        </div>`;

        // Export
        h+=`<div style="display:flex;gap:8px;margin-bottom:16px"><a href="${API}/swimmers/export" class="export-btn">📥 Export CSV</a></div>`;

        // Swimmer cards sorted by avg
        if(stats?.length){
            const sorted=[...stats].sort((a,b)=>(a.avg_lap_ms||9999)-(b.avg_lap_ms||9999));
            h+=`<div class="lbl" style="margin-bottom:8px">Alle zwemmers (${sorted.length})</div>`;

            // Table header
            h+=`<div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:2px;padding:6px 10px;font-size:10px;font-weight:600;color:var(--w30);border-bottom:1px solid rgba(255,255,255,.06);margin-bottom:4px">
                <div>Naam</div><div style="text-align:right">Gem.</div><div style="text-align:right">Snelste</div><div style="text-align:right">Gem. afstand</div><div style="text-align:right">Totaal</div></div>`;

            sorted.forEach((s,i)=>{
                const ci=COLORS[i%COLORS.length];
                const totalDist=s.total_distance_m||s.total_laps*LAP_M;
                const avgDistPerSession=s.total_sessions?(totalDist/s.total_sessions):0;
                const rank=i+1;
                const medal=rank===1?'🥇':rank===2?'🥈':rank===3?'🥉':'';

                h+=`<div class="card" style="border:1px solid ${ci}15;cursor:pointer;padding:10px 12px" onclick="loadSwimmerDetail('${s.swimmer_name}')">
                    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:2px;align-items:center">
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-size:14px;min-width:20px">${medal||`<span style="font-family:var(--mono);font-size:11px;color:var(--w15)">${rank}</span>`}</span>
                            <div style="width:8px;height:8px;border-radius:50%;background:${ci}"></div>
                            <span style="font-size:13px;font-weight:700">${s.swimmer_name}</span>
                        </div>
                        <div style="text-align:right;font-family:var(--mono);font-size:13px;font-weight:700;color:${ci}">${fmt(s.avg_lap_ms)}</div>
                        <div style="text-align:right;font-family:var(--mono);font-size:13px;font-weight:700;color:#10b981">${fmt(s.best_lap_ms)}</div>
                        <div style="text-align:right;font-family:var(--mono);font-size:12px;color:var(--w50)">${fmtDist(Math.round(avgDistPerSession))}</div>
                        <div style="text-align:right;font-family:var(--mono);font-size:12px;color:var(--w50)">${fmtDist(totalDist)}</div>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:6px;font-size:11px;color:var(--w30)">
                        <span>${s.total_laps} banen</span> · <span>${s.total_sessions} sessies</span>
                        ${s.recent_avg_ms?` · <span>recent ${fmt(s.recent_avg_ms)}</span>`:''}
                    </div>
                </div>`;
            });
        } else {
            h+=`<div style="text-align:center;color:var(--w30);padding:40px 0">Nog geen data — ga zwemmen! 🏊</div>`;
        }

        c.innerHTML=h;
    }catch(e){
        if(e.message==='auth')return;
        c.innerHTML=`<div style="text-align:center;color:var(--w30);padding:40px 0">Kan server niet bereiken</div>`;
    }
}

// ─── History ───
async function loadHistory(){
    const c=document.getElementById('historyInner');
    c.innerHTML=`<div style="text-align:center;margin-bottom:24px"><div class="lbl">Geschiedenis</div><div class="title">Zwemmers & Sessies</div></div><div style="text-align:center;color:var(--w30);padding:40px 0">Laden...</div>`;
    try{
        const [sR,seR]=await Promise.all([authFetch(API+'/swimmers/stats'),authFetch(API+'/sessions?per_page=50')]);
        const stats=await sR.json(),seD=await seR.json(),sessions=seD.data||seD;
        let h=`<div style="text-align:center;margin-bottom:24px"><div class="lbl">Geschiedenis</div><div class="title">Zwemmers & Sessies</div></div>`;

        // Export all swimmers
        h+=`<div style="display:flex;gap:8px;margin-bottom:16px"><a href="${API}/swimmers/export" class="export-btn">📥 Export zwemmers CSV</a></div>`;

        // Swimmer cards
        if(stats?.length){
            h+=`<div class="lbl" style="margin-bottom:8px">Individuele zwemmers</div>`;
            stats.forEach((s,i)=>{
                const ci=COLORS[i%COLORS.length],d=s.total_distance_m||s.total_laps*LAP_M;
                h+=`<div class="card" style="border:1px solid ${ci}20;cursor:pointer" onclick="loadSwimmerDetail('${s.swimmer_name}')">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                        <div style="width:10px;height:10px;border-radius:50%;background:${ci}"></div>
                        <span style="font-size:15px;font-weight:700">${s.swimmer_name}</span>
                        <span class="dist" style="margin-left:auto">${fmtDist(d)}</span></div>
                    <div style="display:flex;gap:4px;margin-bottom:6px;font-size:12px;color:var(--w30)">
                        <span>${s.total_laps} banen</span> · <span>${s.total_sessions} sessies</span></div>
                    <div style="display:flex;gap:6px">
                        <div style="flex:1;padding:6px;border-radius:8px;background:rgba(255,255,255,.03);text-align:center"><div style="font-size:10px;color:var(--w30);margin-bottom:2px">Gem.</div><div style="font-family:var(--mono);font-size:14px;font-weight:700;color:${ci}">${fmt(s.avg_lap_ms)}</div></div>
                        <div style="flex:1;padding:6px;border-radius:8px;background:rgba(255,255,255,.03);text-align:center"><div style="font-size:10px;color:var(--w30);margin-bottom:2px">Snelste</div><div style="font-family:var(--mono);font-size:14px;font-weight:700;color:#10b981">${fmt(s.best_lap_ms)}</div></div>
                        <div style="flex:1;padding:6px;border-radius:8px;background:rgba(255,255,255,.03);text-align:center"><div style="font-size:10px;color:var(--w30);margin-bottom:2px">Recent</div><div style="font-family:var(--mono);font-size:14px;font-weight:700;color:var(--w70)">${fmt(s.recent_avg_ms)}</div></div>
                    </div></div>`;
            });
        }

        // Session list
        if(sessions?.length){
            h+=`<div class="lbl" style="margin:20px 0 8px">Sessies (klik voor detail)</div>`;
            sessions.forEach(s=>{
                const date=new Date(s.started_at).toLocaleString('nl-NL',{weekday:'short',day:'numeric',month:'short'}),d=s.total_distance_m||(s.total_splits*LAP_M);
                h+=`<div class="card" style="cursor:pointer;border:1px solid rgba(255,255,255,.05)" onclick="loadSessionDetail(${s.id})">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                        <div><span style="font-size:13px;font-weight:600;color:var(--w70)">${s.team_name||'Ploeg'}</span>
                            <span style="font-size:11px;color:var(--w30);margin-left:8px">${date}</span></div>
                        <span style="font-family:var(--mono);font-size:16px;font-weight:700;color:#10b981">${fmtLong(s.total_time_ms)}</span></div>
                    <div style="display:flex;gap:8px;align-items:center;font-size:11px;color:var(--w30)">
                        <span>${s.total_splits} splits · ${s.total_rounds} rondes</span>
                        <span class="dist">${fmtDist(d)}</span>
                        <span style="margin-left:auto">${(s.swimmers||[]).join(', ')}</span></div></div>`;
            });
        }
        c.innerHTML=h;
    }catch{c.innerHTML=`<div style="text-align:center;color:var(--w30);padding:40px 0">Kan server niet bereiken</div>`;}
}

// ─── Session Detail (Ploeg view) ───
async function loadSessionDetail(id){
    const c=document.getElementById('detailInner');
    c.innerHTML=`<div style="text-align:center;color:var(--w30);padding:80px 0">Laden...</div>`;
    showView('detail');
    try{
        const r=await authFetch(API+'/sessions/'+id);
        const s=await r.json();
        const names=s.swimmers||[],sp=s.splits||[];
        const date=new Date(s.started_at).toLocaleString('nl-NL',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
        const dist=s.total_distance_m||(s.total_splits*LAP_M);

        let h=`<div class="back-btn" onclick="showView('history')">← Terug</div>`;
        h+=`<div style="text-align:center;margin-bottom:24px">
            <div class="lbl">${s.team_name}</div>
            <div style="font-size:13px;color:var(--w30);margin-bottom:8px">${date}</div>
            <div style="font-family:var(--mono);font-size:44px;font-weight:700;color:#10b981;line-height:1;margin:8px 0">${fmtLong(s.total_time_ms)}</div>
            <div style="font-size:14px;color:#10b981;font-weight:600;margin-bottom:8px">${s.total_splits} splits · ${s.total_rounds} rondes</div>
            <span class="dist" style="font-size:14px;padding:6px 16px">🏊 ${fmtDist(dist)} totaal</span></div>`;

        // Export + Delete
        h+=`<div style="display:flex;gap:8px;margin-bottom:16px">
            <a href="${API}/sessions/${id}/export" class="export-btn">📥 Export CSV</a>
            <button onclick="deleteSession(${s.id},'${(s.team_name||'').replace(/'/g,"\\'")}')" style="flex:1;padding:10px;border-radius:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#ef4444;font-size:13px;font-weight:600;cursor:pointer">🗑️ Verwijder</button>
        </div>`;

        // Stats
        h+=buildStatsHTML(names,sp);

        // Verval chart
        h+=`<div class="lbl" style="margin:16px 0 8px">Verval per zwemmer</div>
            <div style="border-radius:12px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);padding:12px;margin-bottom:16px">
                <canvas id="chartSession" style="width:100%;height:180px"></canvas>
                <div style="display:flex;gap:12px;justify-content:center;margin-top:8px">
                    ${names.map((n,i)=>`<div style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--w50)"><div style="width:8px;height:8px;border-radius:50%;background:${COLORS[i%COLORS.length]}"></div>${n}</div>`).join('')}
                </div>
            </div>`;

        // Splits table
        h+=buildSplitsTableHTML(names,sp);

        c.innerHTML=h;
        drawChart('chartSession',names,sp);
    }catch(e){c.innerHTML=`<div style="text-align:center;color:var(--w30);padding:40px 0">Fout bij laden: ${e.message}</div>`;}
}

// ─── Swimmer Detail (across all sessions) ───
async function loadSwimmerDetail(name){
    const c=document.getElementById('detailInner');
    c.innerHTML=`<div style="text-align:center;color:var(--w30);padding:80px 0">Laden...</div>`;
    showView('detail');
    try{
        const [logR,statsR]=await Promise.all([authFetch(API+'/swimmers/'+encodeURIComponent(name)+'/log?per_page=200'),authFetch(API+'/swimmers/stats')]);
        const logD=await logR.json(),splits=(logD.data||logD);
        const allStats=await statsR.json();
        const myStats=allStats.find(s=>s.swimmer_name===name)||{};
        const dist=myStats.total_distance_m||myStats.total_laps*LAP_M||splits.length*LAP_M;
        const ci=COLORS[(allStats.findIndex(s=>s.swimmer_name===name))%COLORS.length]||COLORS[0];

        let h=`<div class="back-btn" onclick="showView('history')">← Terug</div>`;
        h+=`<div style="text-align:center;margin-bottom:24px">
            <div style="width:48px;height:48px;border-radius:50%;background:${ci}20;border:2px solid ${ci}40;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:22px">🏊</div>
            <div class="title">${name}</div>
            <div style="display:flex;gap:8px;justify-content:center;margin-top:8px">
                <span class="dist" style="font-size:13px;padding:5px 14px">${fmtDist(dist)}</span>
                <span style="font-size:13px;color:var(--w30);padding:5px 0">${myStats.total_laps||splits.length} banen · ${myStats.total_sessions||'?'} sessies</span></div></div>`;

        // Stats
        h+=`<div style="display:flex;gap:8px;margin-bottom:16px">
            <div style="flex:1;padding:10px;border-radius:10px;background:rgba(255,255,255,.03);text-align:center"><div style="font-size:10px;color:var(--w30);margin-bottom:4px">Gemiddeld</div><div style="font-family:var(--mono);font-size:20px;font-weight:700;color:${ci}">${fmt(myStats.avg_lap_ms)}</div></div>
            <div style="flex:1;padding:10px;border-radius:10px;background:rgba(255,255,255,.03);text-align:center"><div style="font-size:10px;color:var(--w30);margin-bottom:4px">Snelste ooit</div><div style="font-family:var(--mono);font-size:20px;font-weight:700;color:#10b981">${fmt(myStats.best_lap_ms)}</div></div>
            <div style="flex:1;padding:10px;border-radius:10px;background:rgba(255,255,255,.03);text-align:center"><div style="font-size:10px;color:var(--w30);margin-bottom:4px">Recent (10)</div><div style="font-family:var(--mono);font-size:20px;font-weight:700;color:var(--w70)">${fmt(myStats.recent_avg_ms)}</div></div></div>`;

        // Performance chart (all laps chronologically)
        const lapTimes=splits.slice().reverse().map(s=>s.lap_time_ms);
        if(lapTimes.length>1){
            h+=`<div class="lbl" style="margin-bottom:8px">Prestatieverloop (alle banen)</div>
            <div style="border-radius:12px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);padding:12px;margin-bottom:16px">
                <canvas id="chartSwimmer" style="width:100%;height:160px"></canvas></div>`;
        }

        // Per-session breakdown
        const bySess={};
        splits.forEach(s=>{const sid=s.swim_session_id;if(!bySess[sid])bySess[sid]={session:s.session,splits:[]};bySess[sid].splits.push(s);});

        h+=`<div class="lbl" style="margin-bottom:8px">Per sessie</div>`;
        Object.values(bySess).forEach(g=>{
            const sess=g.session||{};
            const times=g.splits.map(s=>s.lap_time_ms);
            const avg=times.reduce((a,b)=>a+b,0)/times.length;
            const best=Math.min(...times);
            const d=g.splits.length*LAP_M;
            const date=sess.started_at?new Date(sess.started_at).toLocaleString('nl-NL',{day:'numeric',month:'short'}):'-';
            h+=`<div class="card" style="border:1px solid ${ci}15;cursor:pointer" onclick="loadSessionDetail(${sess.id})">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                    <span style="font-size:13px;font-weight:600;color:var(--w70)">${sess.team_name||date}</span>
                    <span style="font-size:11px;color:var(--w30)">${date}</span></div>
                <div style="display:flex;gap:8px;font-size:12px">
                    <span style="color:${ci};font-family:var(--mono);font-weight:700">gem ${fmtSec(avg/1000)}s</span>
                    <span style="color:#10b981;font-family:var(--mono)">⚡${fmtSec(best/1000)}s</span>
                    <span class="dist">${fmtDist(d)}</span>
                    <span style="color:var(--w30)">${g.splits.length} banen</span></div></div>`;
        });

        c.innerHTML=h;

        // Draw swimmer chart
        if(lapTimes.length>1){
            setTimeout(()=>{
                const canvas=document.getElementById('chartSwimmer');if(!canvas)return;
                const ctx=canvas.getContext('2d');
                const W=canvas.width=canvas.offsetWidth*2,H=canvas.height=canvas.offsetHeight*2;
                ctx.scale(2,2);const w=W/2,hh=H/2;
                const pad={t:10,r:10,b:24,l:40};
                const minV=Math.min(...lapTimes)/1000*.95,maxV=Math.max(...lapTimes)/1000*1.05;
                const xF=i=>pad.l+i/(lapTimes.length-1)*(w-pad.l-pad.r);
                const yF=v=>pad.t+(1-(v-minV)/(maxV-minV))*(hh-pad.t-pad.b);
                ctx.strokeStyle='rgba(255,255,255,.06)';ctx.lineWidth=1;
                for(let i=0;i<5;i++){const yy=pad.t+i/4*(hh-pad.t-pad.b);ctx.beginPath();ctx.moveTo(pad.l,yy);ctx.lineTo(w-pad.r,yy);ctx.stroke();ctx.fillStyle='rgba(255,255,255,.2)';ctx.font='9px JetBrains Mono';ctx.textAlign='right';ctx.fillText(fmtSec(maxV-(maxV-minV)*i/4),pad.l-4,yy+3);}
                // Trend line
                const n=lapTimes.length,sx=lapTimes.reduce((a,_,i)=>a+i,0),sy=lapTimes.reduce((a,v)=>a+v/1000,0),sxy=lapTimes.reduce((a,v,i)=>a+i*(v/1000),0),sx2=lapTimes.reduce((a,_,i)=>a+i*i,0);
                const slope=(n*sxy-sx*sy)/(n*sx2-sx*sx),intercept=(sy-slope*sx)/n;
                ctx.strokeStyle=ci+'40';ctx.lineWidth=1;ctx.setLineDash([4,4]);ctx.beginPath();ctx.moveTo(xF(0),yF(intercept));ctx.lineTo(xF(n-1),yF(slope*(n-1)+intercept));ctx.stroke();ctx.setLineDash([]);
                // Data
                ctx.strokeStyle=ci;ctx.lineWidth=1.5;ctx.beginPath();
                lapTimes.forEach((v,i)=>{const vv=v/1000;i===0?ctx.moveTo(xF(i),yF(vv)):ctx.lineTo(xF(i),yF(vv));});ctx.stroke();
            },50);
        }
    }catch(e){c.innerHTML=`<div style="text-align:center;color:var(--w30);padding:40px 0">Fout: ${e.message}</div>`;}
}

// ─── Delete session ───
async function deleteSession(id,name){
    if(!confirm(`Weet je zeker dat je "${name}" wilt verwijderen? Dit kan niet ongedaan worden.`))return;
    try{
        const r=await authFetch(API+'/sessions/'+id,{method:'DELETE',headers:{'Accept':'application/json'}});
        if(r.ok){showView('history');loadHistory();}
        else alert('Verwijderen mislukt');
    }catch(e){if(e.message!=='auth')alert('Fout: '+e.message);}
}

// ─── Wake Lock (always on) ───
let globalWakeLock=null;
async function acquireWL(){try{if('wakeLock'in navigator){globalWakeLock=await navigator.wakeLock.request('screen');}}catch{}}
async function releaseWL(){} // never release — keep screen on

// ─── Init ───
(async function(){
    document.addEventListener('gesturestart',e=>e.preventDefault());
    // Keep screen on — reacquire after tab switch
    acquireWL();
    document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible')acquireWL();});
    // Restore ploeg number from session, or fetch new one from server
    const stored=sessionStorage.getItem('ploegNumber');
    if(stored){nextPloegNumber=parseInt(stored);}
    else{try{const r=await fetch(API+'/teams/next-name');if(r.ok){const d=await r.json();nextPloegNumber=d.number;sessionStorage.setItem('ploegNumber',d.number);}}catch{}}
    addTeam();
    document.getElementById('bottomNav').style.display='flex';
    syncPending();
    // Check if already authed (cookie exists)
    fetch(API+'/swimmers/stats',{credentials:'same-origin'}).then(r=>{if(r.ok)authed=true;}).catch(()=>{});
})();
</script>
</body>
</html>
