<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="color-scheme" content="light">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $template->name }}</title>
<style>
:root{--mobile-width:390px;--page-height:844px}
*{box-sizing:border-box}html,body{margin:0;min-height:100%;background:#fff;color:#111827;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;overflow-x:hidden}
body{min-height:100dvh}.pr-root{min-height:100dvh;background:#fff}.pr-layout{min-height:100dvh}
.pr-cover{display:none}.pr-content{width:100%;min-width:0}.pr-page-shell{position:relative;width:100%;overflow:hidden;margin:0 auto}.pr-page{position:relative;width:390px;height:844px;transform-origin:top left;overflow:hidden}
.pr-layer{position:absolute;box-sizing:border-box}.pr-text{white-space:pre-wrap;overflow-wrap:anywhere}.pr-image{width:100%;height:100%;object-fit:cover;display:block}.pr-frame{width:100%;height:100%;overflow:hidden}.pr-frame img{width:100%;height:100%;object-fit:cover;display:block}
.pr-grid{width:100%;height:100%;display:grid;overflow:hidden}
.pr-grid.pr-grid-2h{grid-template-columns:repeat(2,1fr);grid-template-rows:1fr}
.pr-grid.pr-grid-2v{grid-template-columns:1fr;grid-template-rows:repeat(2,1fr)}
.pr-grid.pr-grid-3{grid-template-columns:repeat(2,1fr);grid-template-rows:repeat(2,1fr)}
.pr-grid.pr-grid-3 .pr-grid-cell:first-child{grid-row:1/3}
.pr-grid.pr-grid-4{grid-template-columns:repeat(2,1fr);grid-template-rows:repeat(2,1fr)}
.pr-grid.pr-grid-6{grid-template-columns:repeat(3,1fr);grid-template-rows:repeat(2,1fr)}
.pr-grid.pr-grid-mosaic{grid-template-columns:1.35fr .65fr;grid-template-rows:repeat(2,1fr)}
.pr-grid.pr-grid-mosaic .pr-grid-cell:first-child{grid-row:1/3}
.pr-grid-cell{position:relative;min-width:0;min-height:0;overflow:hidden;background:#f1f5f9}
.pr-grid-cell img,.pr-grid-cell video{display:block;width:100%;height:100%;object-fit:cover}
.pr-grid-empty{width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f8fafc;color:#94a3b8;font-size:10px}
.pr-lightbox{position:fixed;inset:0;z-index:100001;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,.92);padding:24px}
.pr-lightbox[hidden]{display:none}
.pr-lightbox img,.pr-lightbox video{max-width:min(92vw,1100px);max-height:90vh;object-fit:contain}
.pr-lightbox-close{position:absolute;right:18px;top:18px;width:38px;height:38px;border:0;border-radius:50%;background:#fff;color:#111827;font-size:22px;cursor:pointer}

.pr-opening{position:fixed;inset:0;z-index:99999;overflow:hidden;background:#0f172a;display:flex;align-items:center;justify-content:center}.pr-opening.hidden{display:none}
.pr-opening-media{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.pr-opening-shade{position:absolute;inset:0;background:rgba(0,0,0,.28)}
.pr-opening-content{position:absolute;z-index:3;left:50%;top:50%;transform:translate(-50%,-50%);width:min(86%,720px);max-width:calc(100% - 28px);text-align:center;color:#fff;padding:0 8px}
.pr-opening-inner{display:flex;flex-direction:column;align-items:center;text-align:center;width:100%}.pr-opening-eyebrow{font-size:12px;letter-spacing:.04em}.pr-opening-names{font-family:Georgia,serif;line-height:1.05;margin:10px auto 24px;overflow-wrap:anywhere}.pr-opening-btn{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 18px;border:0;border-radius:999px;color:#fff;font-size:11px;font-weight:800;cursor:pointer}
@media(max-width:599px){.pr-opening-content{max-width:340px}.pr-opening-names{line-height:1.08;margin-bottom:16px}.pr-opening-btn{min-height:32px;padding:0 13px}}
@media(min-width:600px) and (max-width:899px){.pr-opening-content{max-width:560px}.pr-opening-names{line-height:1.08;margin-bottom:20px}.pr-opening-btn{min-height:35px;padding:0 15px}}
.pr-anim{visibility:hidden}.pr-anim.running{visibility:visible}
.pr-opening.is-exiting{pointer-events:none}
@keyframes prOpeningExitFade{to{opacity:0}}
@keyframes prOpeningExitSlideUp{to{opacity:0;transform:translateY(-100%)}}
@keyframes prOpeningExitZoom{to{opacity:0;transform:scale(1.08)}}
@keyframes prOpeningExitBlur{to{opacity:0;filter:blur(18px)}}
@keyframes prOpeningExitCurtain{to{transform:translateY(-100%)}}
@keyframes prFade{from{opacity:0}to{opacity:1}}@keyframes prFadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}@keyframes prZoom{from{opacity:0;transform:scale(.94)}to{opacity:1;transform:scale(1)}}@keyframes prSoftScale{from{opacity:0;transform:scale(.98)}to{opacity:1;transform:scale(1)}}
@media(min-width:900px){
.pr-layout.cover-left,.pr-layout.cover-right{display:grid;min-height:100dvh}
.pr-layout.cover-left{grid-template-columns:minmax(340px,1fr) 390px}
.pr-layout.cover-right{grid-template-columns:390px minmax(340px,1fr)}
.pr-cover{display:block;position:sticky;top:0;height:100dvh;overflow:hidden;background:#0f172a}.pr-layout.cover-right .pr-cover{grid-column:2}.pr-layout.cover-right .pr-content{grid-column:1;grid-row:1}
.pr-cover-media{width:100%;height:100%;object-fit:cover;display:block}.pr-content{width:390px;max-width:390px;margin:0;background:#fff}.pr-page-shell{width:390px;max-width:390px;margin:0}
.pr-layout.centered{display:block;min-height:100dvh;background:#fff}
.pr-layout.centered .pr-content{width:100%;max-width:none;margin:0;background:#fff}
.pr-layout.centered .pr-page-shell{width:min(390px,100%);max-width:390px;margin:0 auto}
}
@media(min-width:600px) and (max-width:899px){.pr-cover{display:none!important}.pr-root{background:#eef1f5}.pr-layout{display:flex;justify-content:center}.pr-content{width:390px;max-width:390px;margin:0;background:#fff}.pr-page-shell{width:390px;max-width:390px;margin:0}}
@media(max-width:599px){.pr-cover{display:none!important}.pr-content{width:100%;max-width:none}.pr-page-shell{width:min(390px,100%);max-width:390px;margin:0 auto}}

.pr-functional{position:absolute;box-sizing:border-box;overflow:auto;padding:16px;background:rgba(255,255,255,.94);border:1px solid rgba(148,163,184,.28);border-radius:16px;color:#1f2937;z-index:60}
.pr-functional h3{margin:0 0 10px;font-size:20px;line-height:1.15;text-align:center}.pr-functional p{margin:5px 0;font-size:12px;line-height:1.45;color:#667085}
.pr-f-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.pr-f-wide{grid-column:1/-1}.pr-f-field{width:100%;min-height:38px;border:1px solid #d7e0ea;border-radius:9px;background:#fff;padding:8px 9px;font-size:11px;color:#1f2937}
textarea.pr-f-field{min-height:70px;resize:vertical}.pr-f-btn{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:0 13px;border:0;border-radius:9px;background:#2563eb;color:#fff;font-size:10px;font-weight:800;text-decoration:none;cursor:pointer}
.pr-countdown{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-top:10px}.pr-countdown div{padding:8px 3px;border-radius:9px;background:#eef5ff;text-align:center}.pr-countdown b{display:block;font-size:18px}.pr-countdown span{font-size:7px;color:#667085}
.pr-gift-list,.pr-wish-list{display:flex;flex-direction:column;gap:7px}.pr-gift,.pr-wish{padding:9px;border:1px solid #e2e8f0;border-radius:10px;background:#fff}.pr-gift b,.pr-wish b{display:block;font-size:10px;margin-bottom:3px}.pr-gift span,.pr-wish span{font-size:9px;color:#667085;overflow-wrap:anywhere}
.pr-photo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin-top:10px}.pr-photo-grid img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:7px}.pr-music-wrap{display:flex;align-items:center;justify-content:center;height:100%}.pr-music-btn{width:58px;height:58px;border:0;border-radius:50%;background:#2563eb;color:#fff;font-size:23px;cursor:pointer}
.pr-runtime-message{position:fixed;left:50%;top:16px;transform:translateX(-50%);z-index:100000;max-width:calc(100% - 24px);padding:10px 13px;border-radius:9px;background:#111827;color:#fff;font-size:10px}
@media(max-width:500px){.pr-functional{padding:11px}.pr-functional h3{font-size:16px}.pr-f-grid{grid-template-columns:1fr}.pr-f-wide{grid-column:auto}.pr-countdown b{font-size:14px}}

@keyframes prFocusIn{from{opacity:0;filter:blur(12px) brightness(1.12)}to{opacity:1;filter:blur(0) brightness(1)}}
@keyframes prWipeUp{from{opacity:.2;clip-path:inset(100% 0 0 0)}to{opacity:1;clip-path:inset(0 0 0 0)}}
@keyframes prWipeLeft{from{opacity:.2;clip-path:inset(0 0 0 100%)}to{opacity:1;clip-path:inset(0 0 0 0)}}
@keyframes prFlash{0%,100%{opacity:1}25%{opacity:.2}50%{opacity:1}75%{opacity:.45}}
@keyframes prFlicker{0%,18%,22%,62%,64%,100%{opacity:1}20%,63%{opacity:.28}40%{opacity:.72}}
@keyframes prBreathe{0%,100%{opacity:1;filter:brightness(1)}50%{opacity:.78;filter:brightness(1.08)}}
@keyframes prGlowPulse{0%,100%{filter:brightness(1)}50%{filter:brightness(1.12)}}

</style>
</head>
<body>
<div id="root" class="pr-root"></div>
@if(session('ok'))<script>window.__studioFlash=@json(session('ok'));</script>@endif
<script>
(() => {
const template=@json($snapshot);
const content=@json($content);
const overrides=@json($designOverrides);
const token=@json($token);
const production=@json($production);
const root=document.getElementById('root');
const clamp=(v,a,b)=>Math.max(a,Math.min(b,v));

function rewritePublicUrl(url){
    if(!url||typeof url!=='string')return url||'';
    let m=url.match(/\/admin\/studio\/assets\/(\d+)\/file(?:\?.*)?$/);
    if(m)return `/i/${token}/assets/${m[1]}/file`;
    m=url.match(/\/admin\/studio\/fonts\/(\d+)\/file(?:\?.*)?$/);
    if(m)return `/i/${token}/fonts/${m[1]}/file`;
    return url;
}
function boundValue(l){
    const key=l.binding||'none';
    if(key!=='none'&&Object.prototype.hasOwnProperty.call(content,key)){
        const v=content[key];
        if(typeof v==='string'&&v.trim()!=='')return v;
        if(v&&typeof v==='object'&&v.url)return rewritePublicUrl(v.url);
    }
    return l.text||'';
}
function merged(l){return Object.assign({},l,overrides?.[l.id]||{})}
function mediaEl(src,type='image'){
    src=rewritePublicUrl(src);
    const video=type==='video'||/\.mp4(?:\?|$)/i.test(src||'');
    const el=document.createElement(video?'video':'img');el.src=src||'';
    if(video){el.muted=true;el.defaultMuted=true;el.autoplay=true;el.loop=true;el.playsInline=true;el.setAttribute('muted','');el.setAttribute('playsinline','')}
    return el;
}
function objectPosition(l){return `${clamp(50+Number(l.posX||0),0,100)}% ${clamp(50+Number(l.posY||0),0,100)}%`}
/* UNDANGANTA_PHASE9_MEDIA_ADJUSTMENTS_V1 */
function publicMediaEffects(l){
    const e=l?.effects&&typeof l.effects==='object'?l.effects:{};
    return {brightness:clamp(Number(e.brightness??100),0,200),contrast:clamp(Number(e.contrast??100),0,200),saturation:clamp(Number(e.saturation??100),0,200),grayscale:clamp(Number(e.grayscale??0),0,100)};
}
function publicMediaFilter(l){const e=publicMediaEffects(l);return `brightness(${e.brightness}%) contrast(${e.contrast}%) saturate(${e.saturation}%) grayscale(${e.grayscale}%)`}
function publicMediaTransform(l,base=''){return `${base}${base?' ':''}scaleX(${l?.flipX?-1:1}) scaleY(${l?.flipY?-1:1})`}
function applyPublicCellCrop(container,img,cell,layer=null){
    if(!container||!img)return;

    const sync=()=>{
        const w=Math.max(1,Number(container.clientWidth||1));
        const h=Math.max(1,Number(container.clientHeight||1));
        const nw=Math.max(1,Number(img.naturalWidth||cell?.naturalWidth||1));
        const nh=Math.max(1,Number(img.naturalHeight||cell?.naturalHeight||1));
        // Overscan by two CSS pixels per side so rotation antialiasing never
        // reveals the populated cell's fallback background.
        const mediaBleed=2;
        const cover=Math.max(
            (w+(mediaBleed*2))/nw,
            (h+(mediaBleed*2))/nh
        );
        const zoom=clamp(Number(cell?.scale||1),1,4);
        const fullW=nw*cover*zoom;
        const fullH=nh*cover*zoom;
        const maxX=Math.max(0,(fullW-w)/2);
        const maxY=Math.max(0,(fullH-h)/2);

        const legacyX=maxX>0?clamp(Number(cell?.posX||0)/maxX,-1,1):0;
        const legacyY=maxY>0?clamp(Number(cell?.posY||0)/maxY,-1,1):0;
        const cropX=Number.isFinite(Number(cell?.cropX))?clamp(Number(cell.cropX),-1,1):legacyX;
        const cropY=Number.isFinite(Number(cell?.cropY))?clamp(Number(cell.cropY),-1,1):legacyY;
        const px=cropX*maxX,py=cropY*maxY;

        img.style.position='absolute';
        img.style.left='50%';img.style.top='50%';
        img.style.width=fullW+'px';img.style.height=fullH+'px';
        img.style.minWidth='0';img.style.minHeight='0';
        img.style.maxWidth='none';img.style.maxHeight='none';
        img.style.objectFit='fill';
        img.style.transform=publicMediaTransform(layer,`translate(calc(-50% + ${px}px),calc(-50% + ${py}px))`);
        img.style.filter=publicMediaFilter(layer);
        img.style.transformOrigin='center center';
    };

    if(img.complete&&img.naturalWidth>0)sync();
    else img.addEventListener('load',sync,{once:true});

    if(window.ResizeObserver){
        const ro=new ResizeObserver(sync);
        ro.observe(container);
    }
}

function applyAnimation(el,l){
    const type=l.animation||'none';if(type==='none')return;
    const map={
        'fade':'prFade','fade-up':'prFadeUp','fade-down':'prFadeUp','fade-left':'prFadeUp','fade-right':'prFadeUp',
        'zoom':'prZoom','soft-scale':'prSoftScale','pop':'prZoom','blur-in':'prFade',
        'focus-in':'prFocusIn','reveal-up':'prFadeUp','wipe-up':'prWipeUp','wipe-left':'prWipeLeft',
        'flash':'prFlash','flicker':'prFlicker','breathe':'prBreathe','glow-pulse':'prGlowPulse','float':'prFadeUp'
    };
    const anim=map[type];if(!anim)return;
    el.classList.add('pr-anim');el.dataset.anim=anim;el.dataset.duration=Number(l.duration||.8);el.dataset.delay=Number(l.delay||0);el.dataset.loop=l.loop?'1':'0';
}

const functionalTypes=new Set(['rsvp','gift','location','countdown','wishes','music','guest_photo']);
let runtimeAudio=null;
function componentBounds(layers){const xs=layers.map(l=>Number(l.x||0)),ys=layers.map(l=>Number(l.y||0)),rs=layers.map(l=>Number(l.x||0)+Number(l.width||0)),bs=layers.map(l=>Number(l.y||0)+Number(l.height||0));return{x:Math.min(...xs),y:Math.min(...ys),w:Math.max(...rs)-Math.min(...xs),h:Math.max(...bs)-Math.min(...ys)}}
function sectionEnabled(type){if(!production)return false;const sections=production.sections||{},map={location:'event',countdown:'countdown',rsvp:'rsvp',gift:'gift',wishes:'wishes',guest_photo:'guest_photo',music:'music'};return sections[map[type]||type]!==false}
function escHtml(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]))}
function escAttr(v){return escHtml(v)}
function addRuntimeMessage(text){if(!text)return;const el=document.createElement('div');el.className='pr-runtime-message';el.textContent=text;document.body.appendChild(el);setTimeout(()=>el.remove(),3500)}
function createFunctional(type,layers){
 if(!production||!sectionEnabled(type))return null;const b=componentBounds(layers),el=document.createElement('div');el.className='pr-functional pr-functional-'+type;el.style.left=b.x+'px';el.style.top=b.y+'px';el.style.width=Math.max(80,b.w)+'px';el.style.height=Math.max(70,b.h)+'px';const bg=layers.find(x=>x.type==='shape')?.background;if(bg)el.style.background=bg;const gt=production.guest?.token||new URLSearchParams(location.search).get('g')||'',csrf=document.querySelector('meta[name=csrf-token]')?.content||'';
 if(type==='rsvp'){el.innerHTML=`<h3>RSVP</h3><form method="POST" action="/i/${token}/rsvp"><input type="hidden" name="_token" value="${escAttr(csrf)}"><input type="hidden" name="guest_token" value="${escAttr(gt)}"><div class="pr-f-grid"><input class="pr-f-field" name="name" required placeholder="Nama" value="${escAttr(production.guest?.name||'')}" ${production.guest?'readonly':''}><select class="pr-f-field" name="status" required><option value="hadir">Hadir</option><option value="tidak_hadir">Tidak hadir</option><option value="ragu">Masih ragu</option></select><input class="pr-f-field" type="number" min="1" max="20" name="party_size" value="${Number(production.guest?.party_size||1)}" placeholder="Jumlah tamu"><textarea class="pr-f-field pr-f-wide" name="message" placeholder="Ucapan & doa"></textarea><button class="pr-f-btn pr-f-wide" type="submit">Kirim Konfirmasi</button></div></form>`}
 else if(type==='gift'){const gs=Array.isArray(production.gift_accounts)?production.gift_accounts:[];el.innerHTML='<h3>Wedding Gift</h3><div class="pr-gift-list">'+(gs.length?gs.map(g=>`<div class="pr-gift"><b>${escHtml(g.bank||'Rekening')}</b><span>${escHtml(g.number||'')} · ${escHtml(g.name||'')}</span></div>`).join(''):'<p>Informasi hadiah belum tersedia.</p>')+'</div>'}
 else if(type==='location'){const u=production.maps_url||'';el.innerHTML=`<h3>${escHtml(production.venue_name||'Lokasi Acara')}</h3><p>${escHtml(production.venue_address||'')}</p>${u?`<a class="pr-f-btn" target="_blank" rel="noopener noreferrer" href="${escAttr(u)}">Buka Maps</a>`:''}`}
 else if(type==='countdown'){el.innerHTML='<h3>Menuju Hari Bahagia</h3><div class="pr-countdown"><div><b data-cd="d">00</b><span>HARI</span></div><div><b data-cd="h">00</b><span>JAM</span></div><div><b data-cd="m">00</b><span>MENIT</span></div><div><b data-cd="s">00</b><span>DETIK</span></div></div>';const t=production.event_date?new Date(production.event_date).getTime():0;if(t){const tick=()=>{let x=Math.max(0,t-Date.now()),set=(k,v)=>{const n=el.querySelector(`[data-cd="${k}"]`);if(n)n.textContent=String(v).padStart(2,'0')};set('d',Math.floor(x/86400000));set('h',Math.floor(x/3600000)%24);set('m',Math.floor(x/60000)%60);set('s',Math.floor(x/1000)%60)};tick();setInterval(tick,1000)}}
 else if(type==='wishes'){const ws=Array.isArray(production.wishes)?production.wishes:[];el.innerHTML='<h3>Ucapan & Doa</h3><div class="pr-wish-list">'+(ws.length?ws.slice(0,8).map(w=>`<div class="pr-wish"><b>${escHtml(w.name||'Tamu')}</b><span>${escHtml(w.message||'')}</span></div>`).join(''):'<p>Belum ada ucapan.</p>')+'</div>'}
 else if(type==='music'){if(!production.music_url){el.innerHTML='<div class="pr-music-wrap"><p>Musik belum diatur.</p></div>'}else{el.innerHTML='<div class="pr-music-wrap"><button class="pr-music-btn" type="button" aria-label="Musik">♫</button></div>';const btn=el.querySelector('button');btn.onclick=async()=>{try{if(!runtimeAudio){runtimeAudio=new Audio(production.music_url);runtimeAudio.loop=true}if(runtimeAudio.paused){await runtimeAudio.play();btn.textContent='Ⅱ'}else{runtimeAudio.pause();btn.textContent='♫'}}catch(e){}}}}
 else if(type==='guest_photo'){const premium=['premium','pro'].includes(String(production.plan||'').toLowerCase());if(!premium){el.innerHTML='<h3>Guest Photo</h3><p>Fitur ini tersedia untuk Premium/Pro.</p>'}else{const ps=Array.isArray(production.photos)?production.photos:[];el.innerHTML=`<h3>Bagikan Momenmu</h3><form method="POST" enctype="multipart/form-data" action="/i/${token}/photo"><input type="hidden" name="_token" value="${escAttr(csrf)}"><input type="hidden" name="guest_token" value="${escAttr(gt)}"><div class="pr-f-grid"><input class="pr-f-field" name="guest_name" required placeholder="Nama" value="${escAttr(production.guest?.name||'')}" ${production.guest?'readonly':''}><input class="pr-f-field" type="file" name="photo" accept="image/*" required><input class="pr-f-field pr-f-wide" name="caption" placeholder="Caption opsional"><button class="pr-f-btn pr-f-wide" type="submit">Kirim Foto</button></div></form>${ps.length?'<div class="pr-photo-grid">'+ps.slice(0,9).map(p=>`<img loading="lazy" src="${escAttr(p.url)}" alt="">`).join('')+'</div>':''}`}}
 return el
}
function renderFunctionalComponents(pg,page,device){if(!production)return;const groups={};(pg.layers||[]).forEach(l=>{if(functionalTypes.has(l.componentType))(groups[l.componentType]??=[]).push(l)});const s=publicPageScale(pg,device);Object.entries(groups).forEach(([type,layers])=>{const el=createFunctional(type,layers);if(!el)return;const b=componentBounds(layers);el.style.left=(b.x*s.sx)+'px';el.style.top=(b.y*s.sy)+'px';el.style.width=(Math.max(80,b.w)*s.sx)+'px';el.style.height=(Math.max(70,b.h)*s.sy)+'px';page.appendChild(el)})}

function publicDevice(){
    const w=window.innerWidth||390;
    return w<600?'mobile':(w<900?'tablet':'desktop');
}
function publicDeviceProfile(device=publicDevice()){
    // Invitation content keeps the HP ratio on every viewport.
    // Dedicated desktop Sticky Cover is handled separately and is untouched.
    return {width:390,height:844};
}
function publicPageScale(pg,device=publicDevice()){
    const d=publicDeviceProfile(device),bw=Number(pg.width||390),bh=Number(pg.height||844);
    return {sx:d.width/bw,sy:d.height/bh,fs:Math.min(d.width/bw,d.height/bh),width:d.width,height:d.height};
}
function publicLayerGeometry(l,pg,device=publicDevice()){
    const s=publicPageScale(pg,device);
    const baseX=Number(l.x||0),baseY=Number(l.y||0);
    const baseW=Math.max(1,Number(l.width||1)),baseH=Math.max(1,Number(l.height||1));
    const cx=(baseX+baseW/2)*s.sx;
    const cy=(baseY+baseH/2)*s.sy;
    const width=baseW*s.fs;
    const height=baseH*s.fs;
    return {x:cx-width/2,y:cy-height/2,width,height,fontSize:Number(l.fontSize||16)*s.fs};
}

function applyPublicShapeStyle(l,el,scale=1){
    const kind=l.shapeKind||'rect';
    el.style.background=l.background||'#BFD8FF';
    el.style.clipPath='';
    if(kind==='circle'||kind==='oval')el.style.borderRadius='50%';
    else if(kind==='rounded')el.style.borderRadius=(Number(l.borderRadius||18)*scale)+'px';
    else if(kind==='line')el.style.borderRadius='999px';
    else if(kind==='triangle')el.style.clipPath='polygon(50% 0,100% 100%,0 100%)';
    else if(kind==='star')el.style.clipPath='polygon(50% 0,61% 35%,98% 35%,68% 57%,79% 94%,50% 72%,21% 94%,32% 57%,2% 35%,39% 35%)';
    else if(kind==='heart')el.style.clipPath='polygon(50% 92%,7% 50%,7% 26%,20% 9%,36% 8%,50% 22%,64% 8%,80% 9%,93% 26%,93% 50%)';
    else if(kind==='hex')el.style.clipPath='polygon(25% 7%,75% 7%,100% 50%,75% 93%,25% 93%,0 50%)';
}

function renderLayer(raw,page,pg,device){
    const l=merged(raw);if(l.hidden)return;
    if(production&&functionalTypes.has(l.componentType))return;
    if(l.optional&&l.hideWhenEmpty){
        if(l.type==='grid'){
            const kind=l.gridKind||'4';
            const count=({'2h':2,'2v':2,'3':3,'4':4,'6':6,'mosaic':3})[kind]||4;
            const cells=Array.isArray(l.cells)?l.cells.slice(0,count):[];
            const hasValue=cells.some(cell=>{
                const key=cell?.binding||'none';
                if(key==='none')return !!cell?.src;
                const v=content?.[key];
                return !!(typeof v==='string'?v:(v&&v.url));
            });
            if(!hasValue)return;
        }else if(l.binding&&!content?.[l.binding])return;
    }

    const geom=publicLayerGeometry(l,pg,device),ps=publicPageScale(pg,device);
    const el=document.createElement('div');el.className='pr-layer';el.style.left=geom.x+'px';el.style.top=geom.y+'px';el.style.width=geom.width+'px';el.style.height=geom.height+'px';el.style.zIndex=Number(l.zIndex||1);el.style.opacity=l.opacity??1;el.style.transform=`rotate(${Number(l.rotation||0)}deg)`;el.style.borderRadius=(Number(l.borderRadius||0)*ps.fs)+'px';el.style.overflow=(l.type==='frame'||l.type==='image')?'hidden':'visible';

    if(l.type==='text'){
        el.classList.add('pr-text');el.textContent=boundValue(l);el.style.color=l.color||'#111827';el.style.fontFamily=l.fontFamily||'inherit';el.style.fontSize=geom.fontSize+'px';el.style.fontWeight=l.fontWeight||400;el.style.fontStyle=l.fontStyle||'normal';el.style.textAlign=l.textAlign||'left';el.style.background=l.background||'transparent';el.style.lineHeight=l.lineHeight||1.2;
    } else if(l.type==='image'){
        const src=(l.binding&&content?.[l.binding]?.url)||l.src;
        if(src){const img=mediaEl(src,l.mediaType);img.className='pr-image';img.style.objectFit=l.fit||'cover';img.style.objectPosition=objectPosition(l);img.style.transform=publicMediaTransform(l,`scale(${Number(l.scale||1)})`);img.style.transformOrigin='center center';img.style.filter=publicMediaFilter(l);el.appendChild(img)}
    } else if(l.type==='frame'){
        const src=(l.binding&&content?.[l.binding]?.url)||l.cells?.[0]?.src;
        el.classList.add('pr-frame');
        if(src){
            const img=mediaEl(src,'image');
            const c=l.cells?.[0]||{};
            el.appendChild(img);
            requestAnimationFrame(()=>applyPublicCellCrop(el,img,c,l));
        }
    } else if(l.type==='grid'){
        const kind=l.gridKind||'4';
        const count=({'2h':2,'2v':2,'3':3,'4':4,'6':6,'mosaic':3})[kind]||4;
        const cells=Array.isArray(l.cells)
            ? l.cells.slice(0,count).map(c=>Object.assign({src:null,posX:0,posY:0,scale:1,binding:'none'},c||{}))
            : [];
        while(cells.length<count)cells.push({src:null,posX:0,posY:0,scale:1,binding:'none'});

        if(l.binding&&l.binding!=='none'&&!cells.some(c=>c.binding&&c.binding!=='none')){
            cells[0].binding=l.binding;
        }

        cells.forEach((cell,index)=>{
            const key=cell.binding||'none';
            if(key!=='none'){
                const bound=content?.[key];
                const url=(bound&&typeof bound==='object')?bound.url:(typeof bound==='string'?bound:'');
                cells[index]=Object.assign({},cell,{src:url||''});
            }
        });

        const grid=document.createElement('div');
        grid.className='pr-grid pr-grid-'+kind;
        grid.style.gap=Math.max(0,Number(l.gap||0))+'px';
        grid.style.borderRadius=Math.max(0,Number(l.borderRadius||0))+'px';

        cells.forEach((cell,index)=>{
            const cellEl=document.createElement('div');
            cellEl.className='pr-grid-cell';
            const src=cell?.src;
            if(src){
                const media=mediaEl(src,'image');
                cellEl.style.position='relative';
                cellEl.style.overflow='hidden';
                cellEl.appendChild(media);
                requestAnimationFrame(()=>applyPublicCellCrop(cellEl,media,cell));

                if(l.galleryLightbox!==false){
                    cellEl.style.cursor='zoom-in';
                    cellEl.addEventListener('click',()=>{
                        let box=document.getElementById('prLightbox');
                        if(!box){
                            box=document.createElement('div');
                            box.id='prLightbox';
                            box.className='pr-lightbox';
                            box.hidden=true;
                            const close=document.createElement('button');
                            close.type='button';
                            close.className='pr-lightbox-close';
                            close.textContent='×';
                            close.onclick=()=>{box.hidden=true;box.querySelectorAll('img,video').forEach(n=>n.remove())};
                            box.addEventListener('click',e=>{if(e.target===box)close.click()});
                            box.appendChild(close);
                            document.body.appendChild(box);
                        }
                        box.querySelectorAll('img,video').forEach(n=>n.remove());
                        const large=mediaEl(src,'image');
                        box.appendChild(large);
                        box.hidden=false;
                    });
                }
            }else{
                const empty=document.createElement('div');
                empty.className='pr-grid-empty';
                empty.textContent='';
                cellEl.appendChild(empty);
            }
            grid.appendChild(cellEl);
        });
        el.style.overflow='hidden';
        el.appendChild(grid);
    } else {
        applyPublicShapeStyle(l,el,ps.fs);
    }
    applyAnimation(el,l);page.appendChild(el);
}
function renderPage(pg,device=publicDevice()){
    const d=publicDeviceProfile(device);
    const shell=document.createElement('section');shell.className='pr-page-shell';
    const page=document.createElement('div');page.className='pr-page';page.style.background=pg.background||'#fff';
    page.style.width=d.width+'px';page.style.height=d.height+'px';
    (pg.layers||[]).slice().sort((a,b)=>(a.zIndex||0)-(b.zIndex||0)).forEach(l=>renderLayer(l,page,pg,device));
    renderFunctionalComponents(pg,page,device);
    shell.appendChild(page);
    requestAnimationFrame(()=>{
        const s=Math.min(1,shell.clientWidth/d.width);
        page.style.transform=`scale(${s})`;
        shell.style.height=(d.height*s)+'px';
    });
    return shell;
}
function renderDesktopCover(layout){
    const dc=template.desktopCover||{};
    const override=content?.desktop_cover_media?.url||'';
    const src=override||dc.image||'';
    if(!src)return null;
    const cover=document.createElement('aside');cover.className='pr-cover';
    const media=mediaEl(src,dc.mediaType);media.className='pr-cover-media';media.style.objectFit=dc.fit||'cover';media.style.objectPosition=objectPosition(dc);media.style.transform=`scale(${Number(dc.scale||1)})`;media.style.transformOrigin=objectPosition(dc);cover.appendChild(media);return cover;
}
function openingDevice(){return publicDevice();}
function openingMediaSettings(c,device){
    const by=c.mediaByDevice||{};
    return Object.assign({fit:c.fit||'cover',scale:1,x:50,y:50},by[device]||{});
}
function applyOpeningMedia(media,c,device){
    const m=openingMediaSettings(c,device);
    media.style.objectFit=m.fit||'cover';
    media.style.objectPosition=`${clamp(Number(m.x??50),0,100)}% ${clamp(Number(m.y??50),0,100)}%`;
    media.style.transform=`scale(${Math.max(1,Number(m.scale||1))})`;
    media.style.transformOrigin=media.style.objectPosition;
}
function exitOpening(wrap,c){
    const type=c.exitAnimation||'fade',duration=Math.max(.15,Number(c.exitDuration||.55));
    if(type==='none'){wrap.remove();document.body.style.overflow='';return;}
    const map={fade:'prOpeningExitFade','slide-up':'prOpeningExitSlideUp','zoom-out':'prOpeningExitZoom','blur-out':'prOpeningExitBlur','curtain-up':'prOpeningExitCurtain'};
    wrap.classList.add('is-exiting');
    wrap.style.animation=`${map[type]||map.fade} ${duration}s cubic-bezier(.2,.7,.2,1) both`;
    let done=false;const finish=()=>{if(done)return;done=true;wrap.remove();document.body.style.overflow='';};
    wrap.addEventListener('animationend',finish,{once:true});setTimeout(finish,Math.ceil(duration*1000)+150);
}
function renderOpening(){
    const c=template.openingCover||{};if(c.enabled===false)return;
    const wrap=document.createElement('div');wrap.className='pr-opening';
    const device=openingDevice();
    const openingSrc=content?.opening_cover_media?.url||c.image||'';
    if(openingSrc){
        const media=mediaEl(openingSrc,c.mediaType||'image');media.className='pr-opening-media';
        applyOpeningMedia(media,c,device);wrap.appendChild(media);
    }
    const shade=document.createElement('div');shade.className='pr-opening-shade';wrap.appendChild(shade);
    const contentBox=document.createElement('div');contentBox.className='pr-opening-content';
    contentBox.style.left=clamp(Number(c.contentX??c.posX??50),10,90)+'%';
    contentBox.style.top=clamp(Number(c.contentY??c.posY??50),10,90)+'%';
    const inner=document.createElement('div');inner.className='pr-opening-inner';
    const deviceScale=device==='mobile'?.72:(device==='tablet'?.86:1);
    const eb=document.createElement('div');eb.className='pr-opening-eyebrow';eb.style.fontSize=Math.max(9,14*deviceScale)+'px';eb.textContent=c.eyebrow||'';
    const names=document.createElement('div');names.className='pr-opening-names';names.style.color=c.textColor||'#fff';names.textContent=(c.bindNames!==false&&content?.couple_names)||c.names||'Nama & Nama';
    const length=names.textContent.length,lengthScale=length>28?.72:(length>20?.84:1);
    names.style.fontSize=Math.max(device==='mobile'?21:24,Number(c.nameSize||46)*lengthScale*deviceScale)+'px';
    const btn=document.createElement('button');btn.className='pr-opening-btn';btn.type='button';btn.style.background=c.buttonColor||'#2563eb';btn.style.fontSize=Math.max(8,11*deviceScale)+'px';btn.textContent=c.buttonText||'Buka Undangan';btn.onclick=()=>exitOpening(wrap,c);
    inner.append(eb,names,btn);contentBox.appendChild(inner);wrap.appendChild(contentBox);document.body.appendChild(wrap);document.body.style.overflow='hidden';

    const map={'fade':'prFade','fade-up':'prFadeUp','zoom':'prZoom','soft-scale':'prSoftScale'};
    const anim=map[c.animation||'fade-up'];if(anim)inner.style.animation=`${anim} ${Number(c.duration||.8)}s ease-out both`;
}
function activateAnimations(){
    const els=[...document.querySelectorAll('.pr-anim')];
    if(!('IntersectionObserver' in window)){els.forEach(run);return}
    const io=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){run(e.target);if(e.target.dataset.loop!=='1')io.unobserve(e.target)}}),{threshold:.16});
    els.forEach(el=>io.observe(el));
    function run(el){el.classList.add('running');el.style.animation=`${el.dataset.anim} ${el.dataset.duration}s ease-out ${el.dataset.delay}s ${el.dataset.loop==='1'?'infinite':'1'} both`}
}
function render(){
    const settings=template.settings||{};
    const requestedLayout=settings.desktopLayout||'cover-left';
    const cover=renderDesktopCover(requestedLayout);
    const effectiveLayout=cover?requestedLayout:'centered';

    const shell=document.createElement('div');
    shell.className='pr-layout '+effectiveLayout;
    shell.style.setProperty('--cover-width',Number(settings.desktopCoverWidth||56)+'vw');

    const contentEl=document.createElement('main');
    contentEl.className='pr-content';
    const renderDevice=publicDevice();(template.pages||[]).forEach(pg=>contentEl.appendChild(renderPage(pg,renderDevice)));

    if(effectiveLayout==='cover-right'){
        shell.append(contentEl,cover);
    }else if(effectiveLayout==='cover-left'){
        shell.append(cover,contentEl);
    }else{
        shell.append(contentEl);
    }

    root.appendChild(shell);
    renderOpening();
    requestAnimationFrame(activateAnimations);
}
let lastPublicDevice=publicDevice();
window.addEventListener('resize',()=>{
    const next=publicDevice();
    if(next!==lastPublicDevice){
        lastPublicDevice=next;root.innerHTML='';render();return;
    }
    document.querySelectorAll('.pr-page-shell').forEach(shell=>{
        const page=shell.firstElementChild;if(!page)return;
        const w=parseFloat(page.style.width)||390,h=parseFloat(page.style.height)||844,s=Math.min(1,shell.clientWidth/w);
        page.style.transform=`scale(${s})`;shell.style.height=(h*s)+'px';
    });
});
render();if(window.__studioFlash)addRuntimeMessage(window.__studioFlash);
})();
</script>
</body>
</html>
