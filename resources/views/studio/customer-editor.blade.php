<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $template->name }} — Basic Editor</title>
<style>
:root{
  --pro-accent:#2563eb;
  --pro-accent-soft:#eaf2ff;
  --pro-ink:#172033;
  --pro-work:#f1f2f5;
  --pro-line:#dfe2e6;
  --pro-muted:#7d838b;
  --panel-w:350px;
  --top-h:58px;
}
*{box-sizing:border-box}
html,body{margin:0;min-height:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#1f2328;background:var(--pro-work);-webkit-font-smoothing:antialiased}
button,input,textarea,select{font:inherit}
.ce-shell{min-height:100dvh}
.ce-top{
  height:var(--top-h);display:flex;align-items:center;gap:8px;padding:8px 12px;
  background:linear-gradient(110deg,#eaf4ff 0%,#cfe4ff 48%,#9fc5ff 100%);
  color:var(--pro-ink);box-shadow:0 1px 0 rgba(0,0,0,.08);
  position:sticky;top:0;z-index:100
}
.ce-plan{font-size:9px;font-weight:850;letter-spacing:.08em;text-transform:uppercase;color:#40638b;background:rgba(255,255,255,.6);border:1px solid rgba(37,99,235,.15);border-radius:999px;padding:4px 7px}
.ce-title{max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:800}
.ce-spacer{flex:1}.ce-status{font-size:10px;color:#46617d;white-space:nowrap}
.ce-btn{height:38px;border:0;border-radius:10px;padding:0 12px;background:rgba(255,255,255,.76);color:var(--pro-ink);font-size:11px;font-weight:800;cursor:pointer}
.ce-btn:hover{background:#fff}.ce-btn.primary{background:var(--pro-accent);color:#fff;box-shadow:0 2px 10px rgba(37,99,235,.22)}
.ce-main{display:grid;grid-template-columns:var(--panel-w) minmax(0,1fr);align-items:start;min-height:calc(100dvh - var(--top-h))}
.ce-panel{
  background:#fff;border-right:1px solid #e1e4e8;position:sticky;top:var(--top-h);
  height:calc(100dvh - var(--top-h));overflow:auto;overscroll-behavior:contain;scrollbar-width:thin;z-index:50
}
.ce-tabs{height:50px;display:grid;grid-template-columns:repeat(3,1fr);border-bottom:1px solid #eee;background:#fff;position:sticky;top:0;z-index:20}
.ce-tab{border:0;background:#fff;color:#666f7a;font-size:10px;font-weight:800;cursor:pointer;border-bottom:2px solid transparent}
.ce-tab:hover{background:#f7faff;color:#2563eb}.ce-tab.active{background:var(--pro-accent-soft);color:#2563eb;border-bottom-color:#2563eb}
.ce-section{display:none;padding:14px}.ce-section.active{display:block}
.ce-section-title{font-size:10px;font-weight:850;letter-spacing:.07em;text-transform:uppercase;color:#7d838b;margin:2px 0 10px}
.ce-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.ce-field{min-width:0}.ce-field.wide{grid-column:1/-1}
.ce-field label{display:block;font-size:9px;font-weight:850;color:#777e87;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px}
.ce-field input,.ce-field textarea{
  width:100%;border:1px solid #d9dce2;border-radius:8px;background:#fff;padding:8px 9px;font-size:11px;outline:none;color:#272b30
}
.ce-field input:focus,.ce-field textarea:focus{border-color:#78a5f5;box-shadow:0 0 0 3px rgba(37,99,235,.08)}
.ce-field textarea{min-height:72px;resize:vertical}
.ce-help{font-size:9px;line-height:1.5;color:#7d838b;background:#f8fafc;border:1px solid #edf0f4;border-radius:9px;padding:8px;margin-top:10px}
.ce-media-card{padding:10px 0;border-bottom:1px solid #edf0f3}.ce-media-card:first-of-type{padding-top:0}
.ce-media-row{display:flex;align-items:center;gap:9px}
.ce-thumb{width:58px;height:58px;flex:0 0 auto;border:1px solid #dfe3e8;border-radius:9px;background:#f3f4f6;overflow:hidden;display:grid;place-items:center;color:#98a1ad;font-size:9px}
.ce-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.ce-media-info{flex:1;min-width:0}.ce-media-info b{display:block;font-size:11px}.ce-media-info small{display:block;font-size:8px;color:#89919b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:3px}
.ce-upload{display:none}
.ce-feature{display:flex;align-items:flex-start;gap:9px;padding:10px 0;border-bottom:1px solid #edf0f3}
.ce-feature-dot{width:9px;height:9px;border-radius:50%;background:#22c55e;margin-top:3px;box-shadow:0 0 0 3px #dcfce7}
.ce-feature.missing .ce-feature-dot{background:#94a3b8;box-shadow:0 0 0 3px #f1f5f9}
.ce-feature-copy{min-width:0;flex:1}.ce-feature-copy b{display:block;font-size:11px}.ce-feature-copy small{display:block;font-size:9px;color:#7d838b;line-height:1.45;margin-top:2px}
.ce-feature-tag{font-size:8px;font-weight:800;color:#2563eb;background:#eaf2ff;border-radius:999px;padding:3px 6px;white-space:nowrap}
.ce-feature.missing .ce-feature-tag{color:#64748b;background:#f1f5f9}
.ce-workspace{min-width:0;background:#f0f1f5;padding:18px 26px 48px}
.ce-preview-tools{
  width:max-content;margin:0 auto 12px;display:flex;gap:0;background:#fff;border:1px solid #dfe2e6;border-radius:9px;overflow:hidden;
  position:sticky;top:70px;z-index:30;box-shadow:0 3px 12px rgba(15,23,42,.06)
}
.ce-mini{height:32px;min-width:78px;border:0;border-right:1px solid #e4e7eb;background:#fff;color:#59616c;font-size:10px;font-weight:800;cursor:pointer}
.ce-mini:last-child{border-right:0}.ce-mini.active{background:#2563eb;color:#fff}
.ce-device{width:390px;max-width:100%;margin:0 auto;background:#fff;box-shadow:0 14px 38px rgba(27,31,36,.16);transition:width .2s}
.ce-device.desktop{width:760px}
.ce-page-shell{width:100%;overflow:hidden}.ce-page{position:relative;width:390px;height:844px;transform-origin:top left;overflow:hidden}
.ce-layer-preview{position:absolute;overflow:hidden}.ce-text{display:flex;align-items:center;white-space:pre-wrap;line-height:1.2}
.ce-img{width:100%;height:100%;object-fit:cover;display:block}
.ce-grid-preview{display:grid;width:100%;height:100%;grid-template-columns:repeat(2,1fr);grid-template-rows:repeat(2,1fr);overflow:hidden}
.ce-grid-preview>div{overflow:hidden}.ce-grid-preview img{width:100%;height:100%;object-fit:cover}
.ce-opening{position:relative;height:844px;overflow:hidden;background:#0f172a}.ce-opening-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.ce-opening-shade{position:absolute;inset:0;background:rgba(0,0,0,.30)}
.ce-opening-content{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#fff;text-align:center;padding:28px}
.ce-opening-eyebrow{font-size:12px;letter-spacing:.1em;text-transform:uppercase}.ce-opening-title{font-family:Georgia,serif;font-size:42px;margin:8px 0 20px}
.ce-opening-btn{border:0;border-radius:9px;padding:10px 14px;color:#fff;font-weight:750}
.ce-component{width:100%;height:100%;padding:12px;border-radius:inherit;background:linear-gradient(145deg,#f8fbff,#eaf2ff);border:1px solid #cfe0ff;color:#172033;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;overflow:hidden}
.ce-component.dark{background:linear-gradient(145deg,#172033,#25334a);border-color:#34445f;color:#fff}
.ce-component b{font-size:13px}.ce-component small{font-size:9px;opacity:.7;line-height:1.4;margin-top:5px}.ce-component .metric{font-size:22px;font-weight:850;color:#2563eb}.ce-component.dark .metric{color:#9fc5ff}
.ce-component button{margin-top:8px;height:28px;border:0;border-radius:7px;background:#2563eb;color:#fff;padding:0 9px;font-size:9px;font-weight:800;cursor:pointer}
.ce-error{font-size:9px;color:#b42318;background:#fff1f2;padding:4px 6px;border-radius:6px;margin-top:5px}
.ce-mobile-nav{display:none}
@media(max-width:820px){
  :root{--top-h:54px}.ce-top{height:54px;padding:7px 8px}.ce-plan,.ce-status{display:none}.ce-title{font-size:12px}
  .ce-main{display:block}.ce-panel{position:static;height:auto;border-right:0}.ce-tabs{top:54px}.ce-section{padding:12px}
  .ce-grid{grid-template-columns:1fr}.ce-field.wide{grid-column:auto}.ce-workspace{padding:12px 10px 66px}.ce-preview-tools{top:64px}
  .ce-mobile-nav{display:grid;grid-template-columns:repeat(4,1fr);position:fixed;left:0;right:0;bottom:0;height:58px;background:#fff;border-top:1px solid #dfe2e6;z-index:120}
  .ce-mobile-nav button{border:0;background:#fff;color:#555;font-size:9px;font-weight:750}.ce-mobile-nav button.active{color:#2563eb;background:#f5f9ff}
}
</style>
</head>
<body>
<div class="ce-shell">
<header class="ce-top">
  <span class="ce-plan">Basic</span>
  <div class="ce-title">{{ $template->name }}</div>
  <div class="ce-spacer"></div>
  <span id="saveStatus" class="ce-status">Tersimpan</span>
  <button id="previewTop" class="ce-btn" type="button">Preview Publik</button>
  <button id="saveBtn" class="ce-btn primary" type="button">Simpan</button>
</header>

<main class="ce-main">
<aside class="ce-panel">
  <nav class="ce-tabs">
    <button class="ce-tab active" data-tab="content" type="button">Konten</button>
    <button class="ce-tab" data-tab="media" type="button">Media</button>
    <button class="ce-tab" data-tab="features" type="button">Fitur</button>
  </nav>

  <section class="ce-section active" data-section="content">
    <div class="ce-section-title">Data undangan</div>
    <div class="ce-grid">
      <div class="ce-field"><label>Nama Pria</label><input data-content="groom_name"></div>
      <div class="ce-field"><label>Nama Wanita</label><input data-content="bride_name"></div>
      <div class="ce-field wide"><label>Nama Pasangan</label><input data-content="couple_names"></div>
      <div class="ce-field wide"><label>Tanggal Acara</label><input data-content="event_date" type="datetime-local"></div>
      <div class="ce-field"><label>Lokasi</label><input data-content="venue_name"></div>
      <div class="ce-field wide"><label>Alamat</label><textarea data-content="venue_address"></textarea></div>
      <div class="ce-field wide"><label>Google Maps URL</label><input data-content="maps_url" placeholder="https://maps.google.com/..."></div>
      <div class="ce-field wide"><label>Quote</label><textarea data-content="quote"></textarea></div>
      <div class="ce-field wide"><label>Doa</label><textarea data-content="prayer"></textarea></div>
      <div class="ce-field wide"><label>Teks Pembuka</label><textarea data-content="opening_text"></textarea></div>
      <div class="ce-field wide"><label>Love Story</label><textarea data-content="story"></textarea></div>
      <div class="ce-field wide"><label>Teks Penutup</label><textarea data-content="closing_text"></textarea></div>
    </div>
    <div class="ce-help">Basic hanya mengisi data yang sudah disediakan template admin. Layout dan desain tidak dapat diubah customer.</div>
  </section>

  <section class="ce-section" data-section="media">
    <div class="ce-section-title">Media template</div>
    <div id="mediaCards"></div>
    <div class="ce-help">Foto tersimpan pada instance customer dan tidak mengubah master template admin.</div>
  </section>

  <section class="ce-section" data-section="features">
    <div class="ce-section-title">Fitur pada template</div>
    <div id="featureStatus"></div>
    <div class="ce-help">Countdown memakai Tanggal Acara. Maps memakai Google Maps URL. RSVP dan Wishes bekerja pada undangan publik setelah dipublish.</div>
  </section>
</aside>

<section class="ce-workspace">
  <div class="ce-preview-tools">
    <button class="ce-mini active" data-preview-mode="mobile" type="button">Mobile</button>
    <button class="ce-mini" data-preview-mode="desktop" type="button">Desktop</button>
  </div>
  <div id="previewDevice" class="ce-device"><div id="previewPages"></div></div>
</section>
</main>

<nav class="ce-mobile-nav">
  <button class="active" data-mobile="content">Konten</button>
  <button data-mobile="media">Media</button>
  <button data-mobile="features">Fitur</button>
  <button data-mobile="preview">Preview</button>
</nav>
</div>

<script>
(()=>{
const csrf=document.querySelector('meta[name="csrf-token"]').content;
const template=@json($snapshot);
let content=@json($content);
const updateUrl=@json(route('studio.customer.quick.update',$instance));
const uploadUrl=@json(route('studio.customer.media.upload',$instance));
const publicUrl=@json(route('studio.public.show',$instance->public_token));
const $=id=>document.getElementById(id);
let saveTimer=null,dirty=false,countdownTimer=null;
const mediaKeys=[
 ['groom_photo','Foto Mempelai Pria'],
 ['bride_photo','Foto Mempelai Wanita'],
 ['couple_photo','Foto Pasangan'],
 ['gallery_1','Galeri 1'],
 ['gallery_2','Galeri 2'],
 ['gallery_3','Galeri 3'],
 ['opening_cover_media','Opening Cover'],
 ['desktop_cover_media','Desktop Sticky Cover']
];
const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
function mediaUrl(key){
 const v=content[key];
 return v&&typeof v==='object'?(v.url||''):'';
}
function bound(l){
 const key=l.binding;
 if(key&&typeof content[key]==='string'&&content[key].trim()!=='')return content[key];
 return l.text||'';
}
function pageHasRole(role){return (template.pages||[]).some(p=>(p.role||'').toLowerCase()===role)}
function componentSet(){return new Set((template.pages||[]).flatMap(p=>p.layers||[]).map(l=>l.componentType).filter(Boolean))}
function addMediaTo(el,src){
 if(!src)return;
 const i=document.createElement('img');
 i.className='ce-img';i.alt='';i.loading='eager';i.decoding='async';i.src=src;
 i.onerror=()=>{el.innerHTML='<div class="ce-error">Foto gagal dimuat</div>'};
 el.appendChild(i)
}
function renderOpening(){
 const c=template.openingCover||{};
 if(c.enabled===false)return;
 const w=document.createElement('div');w.className='ce-opening';
 const src=mediaUrl('opening_cover_media')||c.image||'';
 if(src){const media=document.createElement('img');media.className='ce-opening-bg';media.src=src;media.onerror=()=>media.remove();w.appendChild(media)}
 const shade=document.createElement('div');shade.className='ce-opening-shade';w.appendChild(shade);
 const box=document.createElement('div');box.className='ce-opening-content';
 const e=document.createElement('div');e.className='ce-opening-eyebrow';e.textContent=c.eyebrow||'The Wedding of';
 const n=document.createElement('div');n.className='ce-opening-title';n.textContent=(c.bindNames!==false&&content.couple_names)||c.names||'Nama & Nama';
 const b=document.createElement('button');b.className='ce-opening-btn';b.type='button';b.style.background=c.buttonColor||'#2563eb';b.textContent=c.buttonText||'Buka Undangan';b.onclick=()=>w.remove();
 box.append(e,n,b);w.appendChild(box);$('previewPages').appendChild(w)
}
function countdownText(){
 const dt=content.event_date?new Date(content.event_date):null;
 if(!dt||Number.isNaN(dt.getTime()))return {metric:'--',small:'Isi Tanggal Acara'};
 const diff=Math.max(0,dt.getTime()-Date.now());
 const days=Math.floor(diff/86400000);
 const hours=Math.floor((diff%86400000)/3600000);
 return {metric:`${days} hari`,small:`${hours} jam menuju acara`};
}
function componentCard(l){
 const c=document.createElement('div');c.className='ce-component';
 const type=l.componentType||'';
 if(type==='countdown'){
   const t=countdownText();c.innerHTML=`<b>Countdown</b><div class="metric">${esc(t.metric)}</div><small>${esc(t.small)}</small>`;
 }else if(type==='location'){
   const ok=Boolean((content.maps_url||'').trim());c.innerHTML=`<b>${esc(content.venue_name||'Lokasi Acara')}</b><small>${esc(content.venue_address||'Isi alamat dan Google Maps URL')}</small>${ok?'<button type="button">Buka Maps</button>':''}`;
   c.querySelector('button')?.addEventListener('click',e=>{e.stopPropagation();window.open(content.maps_url,'_blank','noopener')});
 }else if(type==='rsvp'){
   c.innerHTML='<b>RSVP</b><small>Form konfirmasi hadir tersedia di undangan publik.</small><button type="button">Preview Publik</button>';
   c.querySelector('button').onclick=e=>{e.stopPropagation();window.open(publicUrl,'_blank','noopener')};
 }else if(type==='wishes'){
   c.classList.add('dark');c.innerHTML='<b>Wishes / Ucapan</b><small>Ucapan tamu tampil dan dikirim melalui undangan publik.</small><button type="button">Preview Publik</button>';
   c.querySelector('button').onclick=e=>{e.stopPropagation();window.open(publicUrl,'_blank','noopener')};
 }else{
   c.innerHTML=`<b>${esc(l.name||type||'Fitur')}</b><small>Komponen template aktif</small>`;
 }
 return c;
}
function renderLayer(l,p){
 if(l.hidden)return;
 if(l.optional&&l.hideWhenEmpty&&l.binding&&!content[l.binding])return;
 const el=document.createElement('div');el.className='ce-layer-preview';
 Object.assign(el.style,{
   left:(l.x||0)+'px',top:(l.y||0)+'px',width:(l.width||0)+'px',height:(l.height||0)+'px',
   zIndex:String(l.zIndex||1),opacity:String(l.opacity??1),transform:`rotate(${l.rotation||0}deg)`,
   borderRadius:(l.borderRadius||0)+'px'
 });
 if(l.componentType){el.appendChild(componentCard(l))}
 else if(l.type==='text'){
   el.classList.add('ce-text');el.textContent=bound(l);el.style.color=l.color||'#111827';el.style.fontFamily=l.fontFamily||'inherit';
   el.style.fontSize=(l.fontSize||16)+'px';el.style.fontWeight=l.fontWeight||400;el.style.fontStyle=l.fontStyle||'normal';
   el.style.textAlign=l.textAlign||'left';el.style.justifyContent=l.textAlign==='center'?'center':(l.textAlign==='right'?'flex-end':'flex-start');
   el.style.background=l.background||'transparent'
 }else if(l.type==='image'){addMediaTo(el,(l.binding&&mediaUrl(l.binding))||l.src)}
 else if(l.type==='frame'){addMediaTo(el,(l.binding&&mediaUrl(l.binding))||l.cells?.[0]?.src)}
 else if(l.type==='grid'){
   const g=document.createElement('div');g.className='ce-grid-preview';
   const cells=Array.isArray(l.cells)?l.cells.slice(0,4):[];while(cells.length<4)cells.push({});
   const boundSrc=l.binding&&mediaUrl(l.binding);if(boundSrc)cells[0]=Object.assign({},cells[0],{src:boundSrc});
   cells.forEach(cell=>{const d=document.createElement('div');if(cell.src)addMediaTo(d,cell.src);g.appendChild(d)});el.appendChild(g)
 }else{el.style.background=l.background||'#BFD8FF'}
 p.appendChild(el)
}
function scalePages(){
 document.querySelectorAll('.ce-page-shell').forEach(shell=>{
   const page=shell.firstElementChild;if(!page)return;
   const scale=Math.min(1,shell.clientWidth/390);page.style.transform=`scale(${scale})`;shell.style.height=(844*scale)+'px'
 })
}
function renderPreview(){
 $('previewPages').innerHTML='';renderOpening();
 (template.pages||[]).forEach(pg=>{
   const shell=document.createElement('div');shell.className='ce-page-shell';
   const page=document.createElement('div');page.className='ce-page';page.style.background=pg.background||'#fff';
   (pg.layers||[]).slice().sort((a,b)=>(a.zIndex||0)-(b.zIndex||0)).forEach(l=>renderLayer(l,page));
   shell.appendChild(page);$('previewPages').appendChild(shell)
 });
 requestAnimationFrame(scalePages)
}
function renderMediaCards(){
 const box=$('mediaCards');box.innerHTML='';
 mediaKeys.forEach(([key,label])=>{
   const v=content[key],src=mediaUrl(key),card=document.createElement('div');card.className='ce-media-card';
   card.innerHTML=`<div class="ce-media-row"><div class="ce-thumb">${src?`<img src="${esc(src)}" alt="">`:'Belum ada'}</div><div class="ce-media-info"><b>${esc(label)}</b><small>${esc(v?.name||'JPG / PNG / WebP / GIF · maks. 10 MB')}</small></div><button class="ce-btn" type="button">Upload</button><input class="ce-upload" type="file" accept="image/jpeg,image/png,image/webp,image/gif"></div>`;
   const img=card.querySelector('img');if(img)img.onerror=()=>{img.parentElement.textContent='Gagal'};
   const btn=card.querySelector('button'),inp=card.querySelector('input');btn.onclick=()=>inp.click();inp.onchange=()=>uploadMedia(key,inp.files?.[0]);box.appendChild(card)
 })
}
async function uploadMedia(key,file){
 if(!file)return;
 $('saveStatus').textContent='Mengupload…';
 const fd=new FormData();fd.append('key',key);fd.append('file',file);
 try{
   const r=await fetch(uploadUrl,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},body:fd});
   const j=await r.json();if(!r.ok)throw new Error(j.message||'Upload gagal');
   content=j.content||content;$('saveStatus').textContent='Media tersimpan';renderMediaCards();renderPreview()
 }catch(e){$('saveStatus').textContent='Upload gagal';alert(e.message||'Upload gagal')}
}
function renderFeatures(){
 const found=componentSet();
 const gallery=(template.pages||[]).some(p=>(p.layers||[]).some(l=>['gallery_1','gallery_2','gallery_3'].includes(l.binding)));
 const defs=[
  ['Opening Cover',template.openingCover?.enabled!==false,'Media → Opening Cover'],
  ['Countdown',found.has('countdown'),'Konten → Tanggal Acara'],
  ['Maps / Lokasi',found.has('location'),'Konten → Lokasi, Alamat, Maps URL'],
  ['Gallery',gallery,'Media → Galeri'],
  ['RSVP',found.has('rsvp'),'Aktif pada undangan publik'],
  ['Wishes / Ucapan',found.has('wishes'),'Aktif pada undangan publik'],
  ['Closing',pageHasRole('closing'),'Konten → Teks Penutup']
 ];
 $('featureStatus').innerHTML=defs.map(([name,ok,help])=>`<div class="ce-feature ${ok?'':'missing'}"><span class="ce-feature-dot"></span><div class="ce-feature-copy"><b>${esc(name)}</b><small>${esc(help)}</small></div><span class="ce-feature-tag">${ok?'Aktif':'Tidak ada'}</span></div>`).join('')
}
async function saveNow(){
 if(saveTimer){clearTimeout(saveTimer);saveTimer=null}
 $('saveStatus').textContent='Menyimpan…';
 try{
   const r=await fetch(updateUrl,{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({content,design_overrides:[]})});
   const j=await r.json();if(!r.ok)throw new Error(j.message||'Gagal menyimpan');
   content=j.instance?.content||content;dirty=false;$('saveStatus').textContent='Tersimpan '+(j.saved_at||'');renderMediaCards()
 }catch(e){$('saveStatus').textContent='Gagal menyimpan'}
}
function queueSave(){if(saveTimer)clearTimeout(saveTimer);saveTimer=setTimeout(saveNow,650)}
document.querySelectorAll('[data-content]').forEach(input=>{
 const key=input.dataset.content;
 let value=content[key]||'';
 if(input.type==='datetime-local'&&value) value=String(value).slice(0,16);
 input.value=value;
 input.addEventListener('input',()=>{
   content[key]=input.value;
   if((key==='groom_name'||key==='bride_name')&&!String(content.couple_names||'').trim()){
     content.couple_names=`${content.groom_name||''} & ${content.bride_name||''}`.trim()
     const couple=document.querySelector('[data-content="couple_names"]');if(couple)couple.value=content.couple_names
   }
   dirty=true;renderPreview();renderFeatures();queueSave()
 })
});
document.querySelectorAll('.ce-tab').forEach(btn=>btn.onclick=()=>{
 document.querySelectorAll('.ce-tab').forEach(x=>x.classList.toggle('active',x===btn));
 document.querySelectorAll('.ce-section').forEach(s=>s.classList.toggle('active',s.dataset.section===btn.dataset.tab))
});
document.querySelectorAll('[data-preview-mode]').forEach(btn=>btn.onclick=()=>{
 document.querySelectorAll('[data-preview-mode]').forEach(x=>x.classList.toggle('active',x===btn));
 $('previewDevice').classList.toggle('desktop',btn.dataset.previewMode==='desktop');
 requestAnimationFrame(scalePages)
});
document.querySelectorAll('[data-mobile]').forEach(btn=>btn.onclick=()=>{
 document.querySelectorAll('[data-mobile]').forEach(x=>x.classList.toggle('active',x===btn));
 if(btn.dataset.mobile==='preview'){document.querySelector('.ce-workspace')?.scrollIntoView({behavior:'smooth'});return}
 document.querySelector(`.ce-tab[data-tab="${btn.dataset.mobile}"]`)?.click()
});
$('saveBtn').onclick=saveNow;
$('previewTop').onclick=async()=>{if(dirty)await saveNow();window.open(publicUrl,'_blank','noopener')};
window.addEventListener('resize',scalePages);
window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue=''}});
countdownTimer=setInterval(()=>{if(document.querySelector('[data-preview-mode]'))renderPreview()},60000);
renderMediaCards();renderFeatures();renderPreview();
})();
</script>
</body>
</html>
