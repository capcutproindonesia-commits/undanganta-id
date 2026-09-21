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
.pr-opening{position:fixed;inset:0;z-index:99999;overflow:hidden;background:#0f172a;display:flex;align-items:center;justify-content:center}.pr-opening.hidden{display:none}
.pr-opening-media{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.pr-opening-shade{position:absolute;inset:0;background:rgba(0,0,0,.28)}
.pr-opening-content{position:absolute;z-index:3;left:50%;top:50%;transform:translate(-50%,-50%);width:min(86%,720px);max-width:calc(100% - 28px);text-align:center;color:#fff;padding:0 8px}
.pr-opening-inner{display:flex;flex-direction:column;align-items:center;text-align:center;width:100%}.pr-opening-eyebrow{font-size:12px;letter-spacing:.04em}.pr-opening-names{font-size:clamp(28px,8vw,52px);line-height:1.08;margin:10px auto 22px;overflow-wrap:anywhere}.pr-opening-btn{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 18px;border:0;border-radius:999px;color:#fff;font-size:11px;font-weight:800;cursor:pointer}
.pr-anim{visibility:hidden}.pr-anim.running{visibility:visible}
@keyframes prFade{from{opacity:0}to{opacity:1}}@keyframes prFadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}@keyframes prZoom{from{opacity:0;transform:scale(.94)}to{opacity:1;transform:scale(1)}}@keyframes prSoftScale{from{opacity:0;transform:scale(.98)}to{opacity:1;transform:scale(1)}}
@media(min-width:900px){
.pr-layout.cover-left,.pr-layout.cover-right{display:grid;min-height:100dvh}
.pr-layout.cover-left{grid-template-columns:minmax(340px,var(--cover-width,56vw)) minmax(390px,1fr)}
.pr-layout.cover-right{grid-template-columns:minmax(390px,1fr) minmax(340px,var(--cover-width,56vw))}
.pr-cover{display:block;position:sticky;top:0;height:100dvh;overflow:hidden;background:#0f172a}.pr-layout.cover-right .pr-cover{grid-column:2}.pr-layout.cover-right .pr-content{grid-column:1;grid-row:1}
.pr-cover-media{width:100%;height:100%;object-fit:cover;display:block}.pr-content{max-width:760px;margin:0 auto;background:#fff}.pr-page-shell{max-width:390px}
}
@media(max-width:899px){.pr-cover{display:none!important}.pr-content{width:100%}.pr-page-shell{max-width:390px}}

.pr-functional{position:absolute;box-sizing:border-box;overflow:auto;padding:16px;background:rgba(255,255,255,.94);border:1px solid rgba(148,163,184,.28);border-radius:16px;color:#1f2937;z-index:60}
.pr-functional h3{margin:0 0 10px;font-size:20px;line-height:1.15;text-align:center}.pr-functional p{margin:5px 0;font-size:12px;line-height:1.45;color:#667085}
.pr-f-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.pr-f-wide{grid-column:1/-1}.pr-f-field{width:100%;min-height:38px;border:1px solid #d7e0ea;border-radius:9px;background:#fff;padding:8px 9px;font-size:11px;color:#1f2937}
textarea.pr-f-field{min-height:70px;resize:vertical}.pr-f-btn{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:0 13px;border:0;border-radius:9px;background:#2563eb;color:#fff;font-size:10px;font-weight:800;text-decoration:none;cursor:pointer}
.pr-countdown{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-top:10px}.pr-countdown div{padding:8px 3px;border-radius:9px;background:#eef5ff;text-align:center}.pr-countdown b{display:block;font-size:18px}.pr-countdown span{font-size:7px;color:#667085}
.pr-gift-list,.pr-wish-list{display:flex;flex-direction:column;gap:7px}.pr-gift,.pr-wish{padding:9px;border:1px solid #e2e8f0;border-radius:10px;background:#fff}.pr-gift b,.pr-wish b{display:block;font-size:10px;margin-bottom:3px}.pr-gift span,.pr-wish span{font-size:9px;color:#667085;overflow-wrap:anywhere}
.pr-photo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin-top:10px}.pr-photo-grid img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:7px}.pr-music-wrap{display:flex;align-items:center;justify-content:center;height:100%}.pr-music-btn{width:58px;height:58px;border:0;border-radius:50%;background:#2563eb;color:#fff;font-size:23px;cursor:pointer}
.pr-runtime-message{position:fixed;left:50%;top:16px;transform:translateX(-50%);z-index:100000;max-width:calc(100% - 24px);padding:10px 13px;border-radius:9px;background:#111827;color:#fff;font-size:10px}
@media(max-width:500px){.pr-functional{padding:11px}.pr-functional h3{font-size:16px}.pr-f-grid{grid-template-columns:1fr}.pr-f-wide{grid-column:auto}.pr-countdown b{font-size:14px}}
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
function applyAnimation(el,l){
    const type=l.animation||'none';if(type==='none')return;
    const map={'fade':'prFade','fade-up':'prFadeUp','zoom':'prZoom','soft-scale':'prSoftScale','pop':'prZoom','blur-in':'prFade','reveal-up':'prFadeUp','float':'prFadeUp'};
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
function renderFunctionalComponents(pg,page){if(!production)return;const groups={};(pg.layers||[]).forEach(l=>{if(functionalTypes.has(l.componentType))(groups[l.componentType]??=[]).push(l)});Object.entries(groups).forEach(([type,layers])=>{const el=createFunctional(type,layers);if(el)page.appendChild(el)})}

function renderLayer(raw,page){
    const l=merged(raw);if(l.hidden)return;
    if(production&&functionalTypes.has(l.componentType))return;
    if(l.optional&&l.hideWhenEmpty&&l.binding&&!content?.[l.binding])return;

    const el=document.createElement('div');el.className='pr-layer';el.style.left=Number(l.x||0)+'px';el.style.top=Number(l.y||0)+'px';el.style.width=Number(l.width||1)+'px';el.style.height=Number(l.height||1)+'px';el.style.zIndex=Number(l.zIndex||1);el.style.opacity=l.opacity??1;el.style.transform=`rotate(${Number(l.rotation||0)}deg)`;el.style.borderRadius=Number(l.borderRadius||0)+'px';el.style.overflow=(l.type==='frame'||l.type==='image')?'hidden':'visible';

    if(l.type==='text'){
        el.classList.add('pr-text');el.textContent=boundValue(l);el.style.color=l.color||'#111827';el.style.fontFamily=l.fontFamily||'inherit';el.style.fontSize=Number(l.fontSize||16)+'px';el.style.fontWeight=l.fontWeight||400;el.style.fontStyle=l.fontStyle||'normal';el.style.textAlign=l.textAlign||'left';el.style.background=l.background||'transparent';el.style.lineHeight=l.lineHeight||1.2;
    } else if(l.type==='image'){
        const src=(l.binding&&content?.[l.binding]?.url)||l.src;
        if(src){const img=mediaEl(src,l.mediaType);img.className='pr-image';img.style.objectFit=l.fit||'cover';img.style.objectPosition=objectPosition(l);img.style.transform=`scale(${Number(l.scale||1)})`;el.appendChild(img)}
    } else if(l.type==='frame'){
        const src=(l.binding&&content?.[l.binding]?.url)||l.cells?.[0]?.src;
        el.classList.add('pr-frame');if(src){const img=mediaEl(src,'image');img.style.objectFit='cover';const c=l.cells?.[0]||{};img.style.objectPosition=`${clamp(50+Number(c.posX||0),0,100)}% ${clamp(50+Number(c.posY||0),0,100)}%`;img.style.transform=`scale(${Number(c.scale||1)})`;el.appendChild(img)}
    } else {
        el.style.background=l.background||'#BFD8FF';
    }
    applyAnimation(el,l);page.appendChild(el);
}
function renderPage(pg){
    const shell=document.createElement('section');shell.className='pr-page-shell';
    const page=document.createElement('div');page.className='pr-page';page.style.background=pg.background||'#fff';
    (pg.layers||[]).slice().sort((a,b)=>(a.zIndex||0)-(b.zIndex||0)).forEach(l=>renderLayer(l,page));
    renderFunctionalComponents(pg,page);
    shell.appendChild(page);
    requestAnimationFrame(()=>{const s=Math.min(1,shell.clientWidth/Number(pg.width||390));page.style.width=Number(pg.width||390)+'px';page.style.height=Number(pg.height||844)+'px';page.style.transform=`scale(${s})`;shell.style.height=(Number(pg.height||844)*s)+'px'});
    return shell;
}
function renderDesktopCover(layout){
    const dc=template.desktopCover||{};if(!dc.image)return null;
    const cover=document.createElement('aside');cover.className='pr-cover';
    const media=mediaEl(dc.image,dc.mediaType);media.className='pr-cover-media';media.style.objectFit=dc.fit||'cover';media.style.objectPosition=objectPosition(dc);media.style.transform=`scale(${Number(dc.scale||1)})`;media.style.transformOrigin=objectPosition(dc);cover.appendChild(media);return cover;
}
function renderOpening(){
    const c=template.openingCover||{};if(c.enabled===false)return;
    const wrap=document.createElement('div');wrap.className='pr-opening';
    const dc=template.desktopCover||{};
    if(dc.image){const media=mediaEl(dc.image,dc.mediaType);media.className='pr-opening-media';media.style.objectFit=dc.fit||'cover';media.style.objectPosition=objectPosition(dc);media.style.transform=`scale(${Number(dc.scale||1)})`;media.style.transformOrigin=objectPosition(dc);wrap.appendChild(media)}
    const shade=document.createElement('div');shade.className='pr-opening-shade';wrap.appendChild(shade);
    const contentBox=document.createElement('div');contentBox.className='pr-opening-content';contentBox.style.left=clamp(Number(c.posX??50),10,90)+'%';contentBox.style.top=clamp(Number(c.posY??50),10,90)+'%';
    const inner=document.createElement('div');inner.className='pr-opening-inner';
    const eb=document.createElement('div');eb.className='pr-opening-eyebrow';eb.textContent=c.eyebrow||'';
    const names=document.createElement('div');names.className='pr-opening-names';names.style.fontSize=Number(c.nameSize||46)+'px';names.style.color=c.textColor||'#fff';names.textContent=(c.bindNames!==false&&content?.couple_names)||c.names||'Nama & Nama';
    const btn=document.createElement('button');btn.className='pr-opening-btn';btn.type='button';btn.style.background=c.buttonColor||'#2563eb';btn.textContent=c.buttonText||'Buka Undangan';btn.onclick=()=>{wrap.classList.add('hidden');document.body.style.overflow=''};
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
    const settings=template.settings||{},layout=settings.desktopLayout||'cover-left';
    const shell=document.createElement('div');shell.className='pr-layout '+layout;shell.style.setProperty('--cover-width',Number(settings.desktopCoverWidth||56)+'vw');
    const cover=renderDesktopCover(layout);
    const contentEl=document.createElement('main');contentEl.className='pr-content';
    (template.pages||[]).forEach(pg=>contentEl.appendChild(renderPage(pg)));
    if(layout==='cover-right'){shell.append(contentEl);if(cover)shell.append(cover)}else{if(cover)shell.append(cover);shell.append(contentEl)}
    root.appendChild(shell);renderOpening();requestAnimationFrame(activateAnimations);
}
window.addEventListener('resize',()=>{document.querySelectorAll('.pr-page-shell').forEach(shell=>{const page=shell.firstElementChild;if(!page)return;const w=Number(page.style.width.replace('px','')||390),h=Number(page.style.height.replace('px','')||844),s=Math.min(1,shell.clientWidth/w);page.style.transform=`scale(${s})`;shell.style.height=(h*s)+'px'})});
render();if(window.__studioFlash)addRuntimeMessage(window.__studioFlash);
})();
</script>
</body>
</html>
