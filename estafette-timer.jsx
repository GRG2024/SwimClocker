import { useState, useRef, useCallback, useEffect } from "react";

const LAP_M = 50, DEBOUNCE = 500;
const COLORS = ["#f59e0b","#3b82f6","#10b981","#a855f7","#ef4444","#ec4899","#14b8a6","#f97316"];
const MONTHS = ["jan","feb","mrt","apr","mei","jun","jul","aug","sep","okt","nov","dec"];
const ROSTER = ["Chantal","Mirelle","Marleen","Jan","Deacon","Teun","Karin","Lieke","Renko","Sophie","Jack","Floor","Femke","Eef","Yara","Kristian","Jerre"];

const fmt=ms=>{if(ms==null)return"—";const m=Math.floor(ms/60000),s=Math.floor((ms%60000)/1000),c=Math.floor((ms%1000)/10);return m>0?`${m}:${String(s).padStart(2,"0")}.${String(c).padStart(2,"0")}`:`${s}.${String(c).padStart(2,"0")}`;};
const fmtLong=ms=>{if(ms==null)return"—";const h=Math.floor(ms/3600000),m=Math.floor((ms%3600000)/60000),s=Math.floor((ms%60000)/1000),c=Math.floor((ms%1000)/10);if(h>0)return`${h}:${String(m).padStart(2,"0")}:${String(s).padStart(2,"0")}.${String(c).padStart(2,"0")}`;if(m>0)return`${m}:${String(s).padStart(2,"0")}.${String(c).padStart(2,"0")}`;return`${s}.${String(c).padStart(2,"0")}`;};
const fmtDist=m=>m>=1000?`${(m/1000).toFixed(1)}km`:`${m}m`;
const genTeamName=n=>{const d=new Date();return`${d.getDate()} ${MONTHS[d.getMonth()]} - Ploeg ${n}`;};

let audioCtx=null;
const getAudio=()=>{if(!audioCtx)audioCtx=new(window.AudioContext||window.webkitAudioContext)();return audioCtx;};
const playBeep=(f=880,d=.15,v=.3)=>{const c=getAudio(),o=c.createOscillator(),g=c.createGain();o.connect(g);g.connect(c.destination);o.frequency.value=f;o.type="sine";g.gain.setValueAtTime(v,c.currentTime);g.gain.exponentialRampToValueAtTime(.001,c.currentTime+d);o.start(c.currentTime);o.stop(c.currentTime+d);};
const playShot=()=>{const ctx=getAudio(),now=ctx.currentTime;const gL=.06,gB=ctx.createBuffer(1,ctx.sampleRate*gL,ctx.sampleRate),gD=gB.getChannelData(0);for(let i=0;i<gD.length;i++)gD[i]=(Math.random()*2-1)*Math.pow(1-i/gD.length,2);const gN=ctx.createBufferSource();gN.buffer=gB;const gF=ctx.createBiquadFilter();gF.type="bandpass";gF.frequency.value=300;gF.Q.value=2;const gG=ctx.createGain();gG.gain.setValueAtTime(.6,now);gG.gain.exponentialRampToValueAtTime(.001,now+gL);gN.connect(gF);gF.connect(gG);gG.connect(ctx.destination);gN.start(now);const dur=.45,vs=now+.04;const f=ctx.createOscillator(),fG=ctx.createGain();f.type="sawtooth";f.frequency.setValueAtTime(180,vs);f.frequency.linearRampToValueAtTime(220,vs+.1);f.frequency.linearRampToValueAtTime(200,vs+dur);fG.gain.setValueAtTime(0,vs);fG.gain.linearRampToValueAtTime(.5,vs+.03);fG.gain.setValueAtTime(.5,vs+dur*.6);fG.gain.exponentialRampToValueAtTime(.001,vs+dur);const f1=ctx.createBiquadFilter();f1.type="bandpass";f1.frequency.value=500;f1.Q.value=5;const f2=ctx.createBiquadFilter();f2.type="bandpass";f2.frequency.value=1000;f2.Q.value=5;const f2G=ctx.createGain();f2G.gain.value=.7;const f3=ctx.createBiquadFilter();f3.type="bandpass";f3.frequency.value=2800;f3.Q.value=4;const f3G=ctx.createGain();f3G.gain.value=.5;f.connect(f1);f1.connect(fG);fG.connect(ctx.destination);f.connect(f2);f2.connect(f2G);f2G.connect(fG);f.connect(f3);f3.connect(f3G);f3G.connect(fG);f.start(vs);f.stop(vs+dur);const f4=ctx.createOscillator(),fG2=ctx.createGain();f4.type="sawtooth";f4.frequency.setValueAtTime(184,vs);f4.frequency.linearRampToValueAtTime(224,vs+.1);f4.frequency.linearRampToValueAtTime(204,vs+dur);fG2.gain.setValueAtTime(0,vs);fG2.gain.linearRampToValueAtTime(.3,vs+.03);fG2.gain.setValueAtTime(.3,vs+dur*.6);fG2.gain.exponentialRampToValueAtTime(.001,vs+dur);const f1b=ctx.createBiquadFilter();f1b.type="bandpass";f1b.frequency.value=520;f1b.Q.value=4;f4.connect(f1b);f1b.connect(fG2);fG2.connect(ctx.destination);f4.start(vs);f4.stop(vs+dur);const nB=ctx.createBuffer(1,ctx.sampleRate*dur,ctx.sampleRate),nD=nB.getChannelData(0);for(let i=0;i<nD.length;i++)nD[i]=(Math.random()*2-1);const bn=ctx.createBufferSource();bn.buffer=nB;const bF=ctx.createBiquadFilter();bF.type="bandpass";bF.frequency.value=3000;bF.Q.value=1;const bG=ctx.createGain();bG.gain.setValueAtTime(0,vs);bG.gain.linearRampToValueAtTime(.12,vs+.03);bG.gain.setValueAtTime(.12,vs+dur*.6);bG.gain.exponentialRampToValueAtTime(.001,vs+dur);bn.connect(bF);bF.connect(bG);bG.connect(ctx.destination);bn.start(vs);};

export default function App(){
  const [teams,setTeams]=useState([{id:1,name:genTeamName(1),swimmers:["Renko","Teun","Jan"]}]);
  const [activeTeam,setActiveTeam]=useState(0);
  const [phase,setPhase]=useState("setup"); // setup|countdown|running|finished|dashboard
  const [elapsed,setElapsed]=useState(0);
  const [swimmer,setSwimmer]=useState(0);
  const [splits,setSplits]=useState([]);
  const [cdVal,setCdVal]=useState(3);
  const [flash,setFlash]=useState(null);
  const [confirmFinish,setConfirmFinish]=useState(false);
  const [finishedSessions,setFinishedSessions]=useState([]);

  const t0=useRef(null),raf=useRef(null),lastTap=useRef(0);
  const splitsRef=useRef([]),swimmerRef=useRef(0),phaseRef=useRef("setup"),namesRef=useRef([]);
  splitsRef.current=splits;swimmerRef.current=swimmer;phaseRef.current=phase;

  const cur=teams[activeTeam]||teams[0];
  namesRef.current=cur.swimmers;

  const tick=useCallback(()=>{if(t0.current)setElapsed(Date.now()-t0.current);raf.current=requestAnimationFrame(tick);},[]);
  useEffect(()=>()=>{if(raf.current)cancelAnimationFrame(raf.current);},[]);
  useEffect(()=>{let wl=null;if((phase==="running"||phase==="countdown")&&"wakeLock"in navigator){navigator.wakeLock.request("screen").then(l=>wl=l).catch(()=>{});}return()=>{if(wl)wl.release().catch(()=>{});};},[phase]);
  useEffect(()=>{if(phase==="running"||phase==="countdown"){const p=e=>e.preventDefault();document.addEventListener("touchmove",p,{passive:false});document.addEventListener("gesturestart",p,{passive:false});return()=>{document.removeEventListener("touchmove",p);document.removeEventListener("gesturestart",p);}}},[phase]);

  const addTeam=()=>{setTeams(t=>[...t,{id:Date.now(),name:genTeamName(t.length+1),swimmers:["Renko","Teun","Jan"]}]);setActiveTeam(teams.length);};
  const removeTeam=idx=>{if(teams.length<=1)return;setTeams(t=>{const n=t.filter((_,i)=>i!==idx).map((tm,i)=>({...tm,name:genTeamName(i+1)}));return n;});setActiveTeam(a=>a>=teams.length-1?Math.max(0,teams.length-2):a);};
  const setSwimmerName=(ti,si,val)=>setTeams(t=>t.map((tm,i)=>i===ti?{...tm,swimmers:tm.swimmers.map((s,j)=>j===si?val:s)}:tm));
  const addSwimmerSlot=()=>{const used=new Set(cur.swimmers);const next=ROSTER.find(n=>!used.has(n))||"Zwemmer "+(cur.swimmers.length+1);setTeams(t=>t.map((tm,i)=>i===activeTeam?{...tm,swimmers:[...tm.swimmers,next]}:tm));};
  const removeSwimmerSlot=si=>{if(cur.swimmers.length<=2)return;setTeams(t=>t.map((tm,i)=>i===activeTeam?{...tm,swimmers:tm.swimmers.filter((_,j)=>j!==si)}:tm));};

  const startCountdown=()=>{setPhase("countdown");setSplits([]);setSwimmer(0);setElapsed(0);setCdVal(3);setConfirmFinish(false);getAudio();playBeep(660,.15,.4);let c=3;const iv=setInterval(()=>{c--;if(c>0){setCdVal(c);playBeep(660,.15,.4);}else{clearInterval(iv);setCdVal("GO!");playShot();setTimeout(()=>{t0.current=Date.now();setPhase("running");raf.current=requestAnimationFrame(tick);},400);}},1000);};
  const handleSplit=useCallback(()=>{if(phaseRef.current!=="running")return;const now=Date.now();if(now-lastTap.current<DEBOUNCE)return;lastTap.current=now;const total=now-t0.current,cs=splitsRef.current,sw=swimmerRef.current,prev=cs.length>0?cs[cs.length-1].total:0,lap=total-prev,round=Math.floor(cs.length/namesRef.current.length)+1;setFlash(COLORS[sw%COLORS.length]);setTimeout(()=>setFlash(null),200);setSplits(s=>[...s,{swimmer:sw,name:namesRef.current[sw],round,total,lap,split_number:cs.length+1}]);setSwimmer((sw+1)%namesRef.current.length);setConfirmFinish(false);},[]);
  const finishRace=()=>{if(raf.current)cancelAnimationFrame(raf.current);const tot=splitsRef.current.length>0?splitsRef.current[splitsRef.current.length-1].total:elapsed;setElapsed(tot);setPhase("finished");setFinishedSessions(h=>[{id:Date.now(),teamName:cur.name,total:tot,splits:[...splitsRef.current],names:[...cur.swimmers]},...h].slice(0,30));};
  const reset=()=>{setPhase("setup");setSplits([]);setSwimmer(0);setElapsed(0);t0.current=null;setConfirmFinish(false);if(raf.current)cancelAnimationFrame(raf.current);};

  const fonts=`@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Outfit:wght@400;500;600;700;800&display=swap');`;
  const base={fontFamily:"'Outfit',sans-serif",userSelect:"none",WebkitUserSelect:"none"};
  const mono={fontFamily:"'JetBrains Mono',monospace"};
  const totalDist=splits.length*LAP_M;

  // ═══ RUNNING ═══
  if(phase==="running"){
    const col=COLORS[swimmer%COLORS.length];const last3=splits.slice(-3).reverse();const currentRound=Math.floor(splits.length/cur.swimmers.length)+1;
    return(<div onTouchStart={e=>{if(e.target.closest("[data-nt]"))return;e.preventDefault();handleSplit();}} onClick={e=>{if(e.target.closest("[data-nt]"))return;handleSplit();}}
      style={{position:"fixed",inset:0,zIndex:9999,background:flash?`radial-gradient(circle at center,${flash}50 0%,${flash}20 50%,#0a1628 100%)`:"#0a1628",display:"flex",flexDirection:"column",alignItems:"center",justifyContent:"center",cursor:"pointer",transition:"background .15s",...base,overflow:"hidden"}}>
      <style>{fonts}{`@keyframes ep{0%,100%{opacity:.3}50%{opacity:.8}}@keyframes bob{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}`}</style>
      <div style={{position:"absolute",inset:"8px",borderRadius:"20px",border:`3px solid ${col}30`,animation:"ep 2s ease-in-out infinite",pointerEvents:"none"}}/>
      <div style={{position:"absolute",top:14,left:14,display:"flex",flexDirection:"column",gap:4}}>
        <div style={{fontSize:12,fontWeight:700,color:"rgba(255,255,255,.5)"}}>{cur.name}</div>
        <div style={{...mono,fontSize:12,fontWeight:600,color:"rgba(255,255,255,.3)",background:"rgba(255,255,255,.06)",borderRadius:8,padding:"4px 10px"}}>Ronde {currentRound} · {splits.length} splits</div>
      </div>
      <div style={{position:"absolute",top:60,right:14,...mono,fontSize:12,fontWeight:600,color:"#3b82f6",background:"rgba(59,130,246,.1)",border:"1px solid rgba(59,130,246,.15)",borderRadius:20,padding:"3px 10px"}}>🏊 {fmtDist(totalDist)}</div>
      <div data-nt="1" style={{position:"absolute",top:14,right:14,display:"flex",gap:6,zIndex:10}}>
        {confirmFinish?<><div onClick={e=>{e.stopPropagation();finishRace()}} onTouchStart={e=>e.stopPropagation()} style={{background:"rgba(239,68,68,.2)",border:"1px solid rgba(239,68,68,.4)",borderRadius:8,padding:"6px 14px",cursor:"pointer",color:"#ef4444",fontSize:12,fontWeight:700}}>🏁 Bevestig</div>
          <div onClick={e=>{e.stopPropagation();setConfirmFinish(false)}} onTouchStart={e=>e.stopPropagation()} style={{background:"rgba(255,255,255,.06)",borderRadius:8,padding:"6px 10px",cursor:"pointer",color:"rgba(255,255,255,.35)",fontSize:12,fontWeight:600}}>✕</div></>:
          <div onClick={e=>{e.stopPropagation();setConfirmFinish(true)}} onTouchStart={e=>e.stopPropagation()} style={{background:"rgba(255,255,255,.06)",borderRadius:8,padding:"6px 14px",cursor:"pointer",color:"rgba(255,255,255,.35)",fontSize:12,fontWeight:600}}>🏁 Finish</div>}
      </div>
      <div style={{...mono,fontSize:"min(20vw,80px)",fontWeight:700,letterSpacing:"-0.04em",lineHeight:1}}>{fmtLong(elapsed)}</div>
      <div style={{marginTop:16,display:"flex",alignItems:"center",gap:10,padding:"8px 20px",borderRadius:40,background:`${col}20`,border:`2px solid ${col}40`}}>
        <div style={{width:10,height:10,borderRadius:"50%",background:col,boxShadow:`0 0 10px ${col}`,animation:"ep 1s ease-in-out infinite"}}/>
        <span style={{fontSize:18,fontWeight:700}}>{cur.swimmers[swimmer]}</span></div>
      {last3.length>0&&<div style={{marginTop:28,display:"flex",flexDirection:"column",gap:4,width:"80%",maxWidth:320}}>
        {last3.map((s,i)=><div key={splits.length-i} style={{display:"flex",justifyContent:"space-between",padding:"6px 14px",borderRadius:8,background:"rgba(255,255,255,.04)",opacity:i===0?1:.5+.2*(2-i)}}>
          <div style={{display:"flex",alignItems:"center",gap:8}}><div style={{width:8,height:8,borderRadius:"50%",background:COLORS[s.swimmer%COLORS.length]}}/><span style={{fontSize:12,color:"rgba(255,255,255,.4)"}}>R{s.round} {s.name}</span></div>
          <span style={{...mono,fontSize:15,fontWeight:700}}>{fmt(s.lap)}</span></div>)}</div>}
      {splits.length>0&&<div style={{position:"absolute",bottom:90,left:12,right:12,display:"flex",gap:6}}>
        {cur.swimmers.map((name,i)=>{const laps=splits.filter(s=>s.swimmer===i),ci=COLORS[i%COLORS.length],act=swimmer===i,d=laps.length*LAP_M;if(!laps.length)return<div key={i} style={{flex:1,padding:"8px 6px",borderRadius:10,background:"rgba(255,255,255,.03)",textAlign:"center",opacity:.3}}><div style={{fontSize:10,color:"rgba(255,255,255,.3)"}}>{name.split(" ")[0]}</div><div style={{...mono,fontSize:13,color:"rgba(255,255,255,.3)"}}>—</div></div>;const ts=laps.map(l=>l.lap),avg=ts.reduce((a,b)=>a+b,0)/ts.length,best=Math.min(...ts);return<div key={i} style={{flex:1,padding:"8px 6px",borderRadius:10,background:act?`${ci}15`:"rgba(255,255,255,.03)",border:`1px solid ${act?ci+"30":"transparent"}`,textAlign:"center"}}><div style={{fontSize:10,color:act?ci:"rgba(255,255,255,.3)",fontWeight:600}}>{name.split(" ")[0]}</div><div style={{...mono,fontSize:15,fontWeight:700}}>{fmt(avg)}</div><div style={{fontSize:9,color:"rgba(255,255,255,.3)"}}>⚡{fmt(best)} · {fmtDist(d)}</div></div>;})}
      </div>}
      <div style={{position:"absolute",bottom:40,display:"flex",flexDirection:"column",alignItems:"center",gap:6}}>
        <div style={{fontSize:32,animation:"bob 1.5s ease-in-out infinite"}}>👆</div><span style={{fontSize:15,color:"rgba(255,255,255,.3)",fontWeight:600}}>TIK OVERAL</span></div>
    </div>);
  }

  // ═══ COUNTDOWN ═══
  if(phase==="countdown")return(
    <div style={{position:"fixed",inset:0,zIndex:9999,background:"#0a1628",display:"flex",flexDirection:"column",alignItems:"center",justifyContent:"center",...base}}>
      <style>{fonts}{`@keyframes cp{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.15);opacity:.5}}`}</style>
      <div style={{...mono,fontSize:cdVal==="GO!"?"min(30vw,140px)":"min(40vw,180px)",fontWeight:700,color:cdVal==="GO!"?"#10b981":"#f59e0b",animation:cdVal==="GO!"?"none":"cp 1s ease-in-out infinite",transform:cdVal==="GO!"?"scale(1.2)":"none",transition:"transform .2s"}}>{cdVal}</div>
      <div style={{fontSize:16,color:"rgba(255,255,255,.3)",fontWeight:600,marginTop:12}}>{cur.name} — klaar?</div></div>);

  // ═══ SETUP / FINISHED / DASHBOARD ═══
  const bgStyle={minHeight:"100vh",background:"linear-gradient(180deg,#0a1628 0%,#0d2137 50%,#122d4f 100%)",...base,color:"#fff"};
  const inner={maxWidth:480,margin:"0 auto",padding:"28px 16px 100px"};

  // Dashboard data from finished sessions
  const allSplitsFromHistory=finishedSessions.flatMap(s=>s.splits.map(sp=>({...sp,sessionName:s.teamName})));
  const swimmerStatsMap={};
  allSplitsFromHistory.forEach(s=>{if(!swimmerStatsMap[s.name])swimmerStatsMap[s.name]={laps:[],sessions:new Set()};swimmerStatsMap[s.name].laps.push(s.lap);swimmerStatsMap[s.name].sessions.add(s.sessionName);});
  const dashStats=Object.entries(swimmerStatsMap).map(([name,d])=>{const ts=d.laps;return{name,totalLaps:ts.length,sessions:d.sessions.size,avg:ts.reduce((a,b)=>a+b,0)/ts.length,best:Math.min(...ts),dist:ts.length*LAP_M,avgDist:d.sessions.size?(ts.length*LAP_M/d.sessions.size):0};}).sort((a,b)=>a.avg-b.avg);

  // Nav tabs
  const navTabs=[{id:"setup",icon:"⏱️",label:"Timer"},{id:"dashboard",icon:"🏅",label:"Dashboard"},{id:"finished",icon:"📊",label:"Resultaat"}];

  return(
    <div style={bgStyle}>
      <style>{fonts}</style>
      <div style={inner}>
        {/* Nav */}
        <div style={{display:"flex",gap:4,marginBottom:20,background:"rgba(255,255,255,.03)",borderRadius:12,padding:4}}>
          {navTabs.map(tab=>(<div key={tab.id} onClick={()=>{if(tab.id==="finished"&&phase!=="finished")return;setPhase(tab.id==="setup"?"setup":tab.id==="dashboard"?"dashboard":"finished");}}
            style={{flex:1,textAlign:"center",padding:"8px 0",borderRadius:10,fontSize:12,fontWeight:600,cursor:"pointer",
              background:phase===tab.id||(tab.id==="setup"&&phase==="setup")?"rgba(59,130,246,.15)":"transparent",
              color:phase===tab.id||(tab.id==="setup"&&phase==="setup")?"#3b82f6":"rgba(255,255,255,.3)",
              opacity:tab.id==="finished"&&phase!=="finished"?.3:1}}>
            <span style={{display:"block",fontSize:16}}>{tab.icon}</span>{tab.label}</div>))}
        </div>

        {/* ═══ DASHBOARD ═══ */}
        {phase==="dashboard"&&(<>
          <div style={{textAlign:"center",marginBottom:20}}>
            <div style={{fontSize:11,fontWeight:600,letterSpacing:".18em",textTransform:"uppercase",color:"rgba(255,255,255,.25)",marginBottom:6}}>Dashboard</div>
            <div style={{fontSize:26,fontWeight:800,letterSpacing:"-0.03em",background:"linear-gradient(135deg,#fff,rgba(255,255,255,.65))",WebkitBackgroundClip:"text",WebkitTextFillColor:"transparent"}}>Team Overzicht</div>
          </div>
          {/* Totals */}
          <div style={{display:"grid",gridTemplateColumns:"1fr 1fr",gap:8,marginBottom:20}}>
            {[["Totale afstand",fmtDist(allSplitsFromHistory.length*LAP_M),"#3b82f6"],["Totaal banen",allSplitsFromHistory.length,"#10b981"],["Sessies",finishedSessions.length,"#f59e0b"],["Zwemmers",dashStats.length,"#a855f7"]].map(([l,v,c])=>
              <div key={l} style={{padding:"12px 14px",borderRadius:12,background:"rgba(255,255,255,.03)",border:`1px solid ${c}20`,textAlign:"center"}}>
                <div style={{fontSize:10,color:"rgba(255,255,255,.3)",marginBottom:4}}>{l}</div>
                <div style={{...mono,fontSize:22,fontWeight:700,color:c}}>{v}</div></div>)}
          </div>
          {/* Swimmer table */}
          {dashStats.length>0?<>
            <div style={{fontSize:11,fontWeight:600,letterSpacing:".12em",textTransform:"uppercase",color:"rgba(255,255,255,.25)",marginBottom:8}}>Alle zwemmers ({dashStats.length})</div>
            <div style={{display:"grid",gridTemplateColumns:"2fr 1fr 1fr 1fr 1fr",gap:2,padding:"6px 10px",fontSize:10,fontWeight:600,color:"rgba(255,255,255,.3)",borderBottom:"1px solid rgba(255,255,255,.06)",marginBottom:4}}>
              <div>Naam</div><div style={{textAlign:"right"}}>Gem.</div><div style={{textAlign:"right"}}>Snelste</div><div style={{textAlign:"right"}}>Gem. afst.</div><div style={{textAlign:"right"}}>Totaal</div></div>
            {dashStats.map((s,i)=>{const ci=COLORS[i%COLORS.length];const medal=i===0?"🥇":i===1?"🥈":i===2?"🥉":"";
              return<div key={s.name} style={{padding:"10px 12px",borderRadius:12,background:"rgba(255,255,255,.03)",border:`1px solid ${ci}15`,marginBottom:6}}>
                <div style={{display:"grid",gridTemplateColumns:"2fr 1fr 1fr 1fr 1fr",gap:2,alignItems:"center"}}>
                  <div style={{display:"flex",alignItems:"center",gap:8}}>
                    <span style={{fontSize:14,minWidth:20}}>{medal||<span style={{...mono,fontSize:11,color:"rgba(255,255,255,.15)"}}>{i+1}</span>}</span>
                    <div style={{width:8,height:8,borderRadius:"50%",background:ci}}/>
                    <span style={{fontSize:13,fontWeight:700}}>{s.name}</span></div>
                  <div style={{textAlign:"right",...mono,fontSize:13,fontWeight:700,color:ci}}>{fmt(s.avg)}</div>
                  <div style={{textAlign:"right",...mono,fontSize:13,fontWeight:700,color:"#10b981"}}>{fmt(s.best)}</div>
                  <div style={{textAlign:"right",...mono,fontSize:12,color:"rgba(255,255,255,.5)"}}>{fmtDist(Math.round(s.avgDist))}</div>
                  <div style={{textAlign:"right",...mono,fontSize:12,color:"rgba(255,255,255,.5)"}}>{fmtDist(s.dist)}</div></div>
                <div style={{display:"flex",gap:8,marginTop:4,fontSize:11,color:"rgba(255,255,255,.3)"}}><span>{s.totalLaps} banen</span> · <span>{s.sessions} sessies</span></div>
              </div>;})}
          </>:<div style={{textAlign:"center",color:"rgba(255,255,255,.3)",padding:"40px 0"}}>Nog geen data — ga zwemmen! 🏊</div>}
        </>)}

        {/* ═══ SETUP ═══ */}
        {phase==="setup"&&(<>
          <div style={{textAlign:"center",marginBottom:20}}>
            <div style={{fontSize:11,fontWeight:600,letterSpacing:".18em",textTransform:"uppercase",color:"rgba(255,255,255,.25)",marginBottom:6}}>Marathon Estafette</div>
            <div style={{fontSize:26,fontWeight:800,letterSpacing:"-0.03em",background:"linear-gradient(135deg,#fff,rgba(255,255,255,.65))",WebkitBackgroundClip:"text",WebkitTextFillColor:"transparent"}}>3 × 50m ∞</div>
          </div>
          {/* Team tabs */}
          <div style={{display:"flex",gap:6,marginBottom:16,overflowX:"auto",paddingBottom:4}}>
            {teams.map((t,i)=><div key={t.id} onClick={()=>setActiveTeam(i)} style={{padding:"8px 16px",borderRadius:10,fontSize:13,fontWeight:600,cursor:"pointer",whiteSpace:"nowrap",flexShrink:0,border:`1px solid ${i===activeTeam?"rgba(59,130,246,.3)":"rgba(255,255,255,.08)"}`,background:i===activeTeam?"rgba(59,130,246,.15)":"rgba(255,255,255,.05)",color:i===activeTeam?"#3b82f6":"rgba(255,255,255,.5)"}}>
              {t.name}{teams.length>1&&<span onClick={e=>{e.stopPropagation();removeTeam(i);}} style={{marginLeft:6,opacity:.4,cursor:"pointer"}}>✕</span>}</div>)}
            <div onClick={addTeam} style={{padding:"8px 16px",borderRadius:10,fontSize:13,fontWeight:600,cursor:"pointer",whiteSpace:"nowrap",flexShrink:0,border:"1px dashed rgba(255,255,255,.15)",background:"transparent",color:"rgba(255,255,255,.3)"}}>+ Ploeg</div>
          </div>
          {/* Swimmer selectors */}
          <div style={{display:"flex",flexDirection:"column",gap:6,marginBottom:12}}>
            {cur.swimmers.map((n,i)=>{const ci=COLORS[i%COLORS.length];const used=new Set(cur.swimmers);
              return<div key={`${cur.id}-${i}`} style={{display:"flex",gap:6,alignItems:"center"}}>
                <div style={{width:10,height:10,borderRadius:"50%",background:ci,flexShrink:0}}/>
                <select value={n} onChange={e=>setSwimmerName(activeTeam,i,e.target.value)}
                  style={{flex:1,background:"rgba(255,255,255,.05)",border:`1px solid ${ci}25`,borderRadius:8,padding:"8px 12px",color:"#fff",fontSize:15,fontFamily:"'Outfit',sans-serif",outline:"none",appearance:"auto",WebkitAppearance:"auto"}}>
                  {ROSTER.map(r=><option key={r} value={r} disabled={used.has(r)&&r!==n}>{r}{used.has(r)&&r!==n?" (in ploeg)":""}</option>)}
                </select>
                {cur.swimmers.length>2&&<div onClick={()=>removeSwimmerSlot(i)} style={{width:28,height:28,borderRadius:8,background:"rgba(239,68,68,.1)",display:"flex",alignItems:"center",justifyContent:"center",cursor:"pointer",flexShrink:0,color:"#ef4444",fontSize:14,fontWeight:700}}>✕</div>}
              </div>;})}
            <button onClick={addSwimmerSlot} style={{padding:8,borderRadius:8,border:"1px dashed rgba(255,255,255,.12)",background:"none",color:"rgba(255,255,255,.3)",fontSize:13,cursor:"pointer",fontFamily:"'Outfit',sans-serif"}}>+ Zwemmer toevoegen</button>
          </div>
          <button onClick={startCountdown} style={{width:"100%",padding:20,background:"linear-gradient(135deg,#3b82f6,#2563eb)",border:"none",borderRadius:16,color:"#fff",fontSize:20,fontWeight:700,cursor:"pointer",fontFamily:"'Outfit',sans-serif",boxShadow:"0 6px 30px rgba(59,130,246,.35)"}}>▶ Start {cur.name}</button>
        </>)}

        {/* ═══ FINISHED ═══ */}
        {phase==="finished"&&(()=>{
          const names=cur.swimmers,sp=splits,rounds=Math.ceil(sp.length/names.length);
          const stats=names.map((name,i)=>{const laps=sp.filter(s=>s.swimmer===i);if(!laps.length)return{name,laps:0};const ts=laps.map(l=>l.lap);return{name,laps:laps.length,dist:laps.length*LAP_M,avg:ts.reduce((a,b)=>a+b,0)/ts.length,best:Math.min(...ts),worst:Math.max(...ts)};});
          return<>
            <div style={{textAlign:"center",marginBottom:24}}>
              <div style={{fontSize:12,fontWeight:700,color:"rgba(255,255,255,.5)",marginBottom:4}}>{cur.name}</div>
              <div style={{...mono,fontSize:48,fontWeight:700,color:"#10b981",lineHeight:1,margin:"8px 0"}}>{fmtLong(elapsed)}</div>
              <div style={{fontSize:14,color:"#10b981",fontWeight:600,marginBottom:8}}>🏁 {sp.length} splits · {rounds} rondes</div>
              <span style={{...mono,display:"inline-flex",alignItems:"center",gap:4,padding:"6px 16px",borderRadius:20,background:"rgba(59,130,246,.1)",border:"1px solid rgba(59,130,246,.15)",fontSize:14,fontWeight:600,color:"#3b82f6"}}>🏊 {fmtDist(totalDist)} totaal</span></div>
            {stats.map((s,i)=>s.laps===0?null:<div key={i} style={{padding:"12px 14px",borderRadius:12,background:"rgba(255,255,255,.03)",border:`1px solid ${COLORS[i%COLORS.length]}20`,marginBottom:8}}>
              <div style={{display:"flex",alignItems:"center",gap:8,marginBottom:8}}>
                <div style={{width:10,height:10,borderRadius:"50%",background:COLORS[i%COLORS.length]}}/>
                <span style={{fontSize:14,fontWeight:700}}>{s.name}</span>
                <span style={{...mono,marginLeft:"auto",fontSize:12,fontWeight:600,color:"#3b82f6",background:"rgba(59,130,246,.1)",border:"1px solid rgba(59,130,246,.15)",borderRadius:20,padding:"3px 10px"}}>{fmtDist(s.dist)}</span>
                <span style={{fontSize:12,color:"rgba(255,255,255,.3)"}}>{s.laps}×</span></div>
              <div style={{display:"flex",gap:8}}>
                {[["Gem.",fmt(s.avg),COLORS[i%COLORS.length]],["Snelste",fmt(s.best),"#10b981"],["Langzaamst",fmt(s.worst),"rgba(255,255,255,.5)"]].map(([l,v,c])=>
                  <div key={l} style={{flex:1,padding:6,borderRadius:8,background:"rgba(255,255,255,.03)",textAlign:"center"}}>
                    <div style={{fontSize:10,color:"rgba(255,255,255,.3)",marginBottom:2}}>{l}</div>
                    <div style={{...mono,fontSize:14,fontWeight:700,color:c}}>{v}</div></div>)}</div></div>)}
            <button onClick={reset} style={{width:"100%",padding:18,marginTop:20,background:"linear-gradient(135deg,#3b82f6,#2563eb)",border:"none",borderRadius:16,color:"#fff",fontSize:18,fontWeight:700,cursor:"pointer",fontFamily:"'Outfit',sans-serif",boxShadow:"0 6px 30px rgba(59,130,246,.3)"}}>🔄 Volgende ploeg</button>
          </>;
        })()}
      </div>
    </div>);
}
