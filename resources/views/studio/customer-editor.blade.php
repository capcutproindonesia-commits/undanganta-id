<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Edit Undangan — {{ $template->name }}</title>
<style>
:root{--blue:#2563eb;--border:#d8e5f5;--text:#1f2a3d;--muted:#6b7a90}
*{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--text);background:#f5f8fc}
button,input,textarea,select{font:inherit}.ce-shell{min-height:100dvh;display:grid;grid-template-rows:52px 1fr}
.ce-top{height:52px;background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;padding:0 14px;position:sticky;top:0;z-index:30}
.ce-title{font-size:13px;font-weight:850;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.ce-spacer{flex:1}.ce-status{font-size:10px;color:var(--muted)}
.ce-btn{height:34px;border:1px solid #cbdcf2;border-radius:9px;background:#fff;color:#26476f;padding:0 12px;font-size:10px;font-weight:800;cursor:pointer}.ce-btn.primary{background:var(--blue);border-color:var(--blue);color:#fff}
.ce-main{display:grid;grid-template-columns:330px minmax(0,1fr);min-height:0}.ce-panel{background:#fbfdff;border-right:1px solid var(--border);padding:14px;overflow:auto}
.ce-tabs{display:flex;gap:6px;margin-bottom:12px}.ce-tab{height:34px;padding:0 12px;border:1px solid #cbdcf2;background:#fff;border-radius:9px;font-size:10px;font-weight:800;color:#33567f}.ce-tab.active{background:#e7f0ff;color:#174d9b;border-color:#8eb1ec}
.ce-section{display:none}.ce-section.active{display:block}.ce-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px}.wide{grid-column:1/-1}
.ce-field label{display:block;font-size:9px;font-weight:800;color:#63738a;margin:0 0 5px}.ce-field input,.ce-field textarea,.ce-field select{width:100%;border:1px solid var(--border);border-radius:9px;background:#fff;padding:9px 10px;font-size:11px;color:var(--text);outline:none}.ce-field textarea{min-height:70px;resize:vertical}
.ce-note{font-size:9px;line-height:1.45;color:var(--muted);margin:8px 0 12px}.ce-design-list{display:flex;flex-direction:column;gap:7px}.ce-layer{border:1px solid var(--border);background:#f4f8ff;border-radius:10px;padding:9px;cursor:pointer;text-align:left}.ce-layer.active{border-color:#7ba3e4;background:#eaf3ff}.ce-layer b{font-size:10px}.ce-layer small{display:block;color:var(--muted);font-size:8px;margin-top:2px}
.ce-design-controls{margin-top:12px;padding:10px;border:1px solid var(--border);border-radius:11px;background:#fff}.ce-preview-wrap{min-width:0;padding:18px;overflow:auto;display:flex;justify-content:center;align-items:flex-start}
.ce-device{width:min(390px,100%);background:#fff;border:1px solid #cbd8e8;border-radius:16px;overflow:hidden;box-shadow:0 8px 28px rgba(29,54,89,.08)}.ce-page{position:relative;width:390px;height:844px;transform-origin:top left;overflow:hidden}.ce-layer-preview{position:absolute;box-sizing:border-box}.ce-text{white-space:pre-wrap;overflow-wrap:anywhere}.ce-img{width:100%;height:100%;object-fit:cover}
.ce-opening{position:relative;width:100%;height:620px;overflow:hidden;background:#111827;display:flex;align-items:center;justify-content:center}.ce-opening-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.ce-opening-shade{position:absolute;inset:0;background:rgba(0,0,0,.25)}
.ce-opening-content{position:relative;z-index:2;width:calc(100% - 32px);text-align:center;color:#fff}.ce-opening-title{font-size:30px;line-height:1.1;margin:8px 0 20px;overflow-wrap:anywhere}.ce-opening-eyebrow{font-size:11px}.ce-opening-btn{display:inline-flex;height:34px;align-items:center;padding:0 14px;border:0;border-radius:999px;color:#fff;font-size:10px;font-weight:800}
.ce-mobile-nav{display:none}
@media(max-width:760px){
.ce-shell{grid-template-rows:48px 1fr}.ce-top{height:48px;padding:0 8px}.ce-title{max-width:130px;font-size:10px}.ce-status{display:none}.ce-btn{height:32px;padding:0 9px;font-size:9px}
.ce-main{display:block;position:relative;min-height:calc(100dvh - 48px)}.ce-panel{position:fixed;left:6px;right:6px;bottom:58px;height:min(66dvh,620px);z-index:50;border:1px solid var(--border);border-radius:14px;padding:11px;box-shadow:0 12px 32px rgba(23,49,83,.14);display:none}.ce-panel.mobile-open{display:block}
.ce-preview-wrap{padding:12px 8px 72px;min-height:calc(100dvh - 48px)}.ce-device{width:min(390px,100%);border-radius:12px}
.ce-mobile-nav{display:grid;grid-template-columns:repeat(3,1fr);position:fixed;left:0;right:0;bottom:0;height:58px;background:#fff;border-top:1px solid var(--border);z-index:60;padding-bottom:env(safe-area-inset-bottom)}
.ce-mobile-nav button{border:0;background:#fff;color:#4b6484;font-size:9px;font-weight:800}.ce-mobile-nav button.active{color:var(--blue);background:#f0f6ff}}
</style>
</head>
<body>
<div class="ce-shell">
<header class="ce-top"><div class="ce-title">{{ $template->name }}</div><div class="ce-spacer"></div><span id="saveStatus" class="ce-status">Tersimpan</span><button id="previewTop" class="ce-btn" type="button">Preview</button><button id="saveBtn" class="ce-btn primary" type="button">Simpan</button></header>
<main class="ce-main">
<aside id="editorPanel" class="ce-panel">
<div class="ce-tabs"><button class="ce-tab active" data-tab="content" type="button">Edit Cepat</button>@if($template->is_customer_editable)<button class="ce-tab" data-tab="design" type="button">Edit Desain</button>@endif</div>
<section class="ce-section active" data-section="content">
<div class="ce-grid">
<div class="ce-field"><label>Nama Pria</label><input data-content="groom_name"></div><div class="ce-field"><label>Nama Wanita</label><input data-content="bride_name"></div>
<div class="ce-field wide"><label>Nama Pasangan</label><input data-content="couple_names"></div><div class="ce-field"><label>Tanggal Acara</label><input data-content="event_date"></div>
<div class="ce-field"><label>Lokasi</label><input data-content="venue_name"></div><div class="ce-field wide"><label>Quote</label><textarea data-content="quote"></textarea></div>
<div class="ce-field wide"><label>Doa</label><textarea data-content="prayer"></textarea></div><div class="ce-field wide"><label>Teks Pembuka</label><textarea data-content="opening_text"></textarea></div>
<div class="ce-field wide"><label>Teks Penutup</label><textarea data-content="closing_text"></textarea></div>
</div><div class="ce-note">Data customer hanya mengubah instance undangan. Template master admin tidak berubah.</div>
</section>
@if($template->is_customer_editable)
<section class="ce-section" data-section="design"><div class="ce-note">Hanya layer Full Edit yang dapat diubah.</div><div id="designLayers" class="ce-design-list"></div>
<div id="designControls" class="ce-design-controls" hidden><div class="ce-grid">
<div class="ce-field"><label>Warna Teks</label><input id="designColor" type="color"></div><div class="ce-field"><label>Background</label><input id="designBackground" type="color"></div>
<div class="ce-field"><label>Ukuran Font</label><input id="designFontSize" type="number" min="8" max="120"></div><div class="ce-field"><label>Opacity</label><input id="designOpacity" type="number" min=".05" max="1" step=".05"></div>
<div class="ce-field wide"><label>Rata Teks</label><select id="designAlign"><option value="left">Kiri</option><option value="center">Tengah</option><option value="right">Kanan</option></select></div>
</div></div></section>
@endif
</aside>
<section class="ce-preview-wrap"><div class="ce-device"><div id="previewPages"></div></div></section>
</main>
<nav class="ce-mobile-nav"><button data-mobile="content" class="active" type="button">Edit Cepat</button>@if($template->is_customer_editable)<button data-mobile="design" type="button">Desain</button>@endif<button data-mobile="preview" type="button">Preview</button></nav>
</div>
<script>
(() => {
const csrf=@json(csrf_token());
const updateUrl=@json(route('studio.customer.update',$instance));
const publicUrl=@json(route('studio.public.show',$instance->public_token));
const template=@json($snapshot);
let content=@json($content);
let overrides=@json($designOverrides);
const editable=@json((bool)$template->is_customer_editable);
const $=id=>document.getElementById(id);
let selectedLayerId=null,saveTimer=null,dirty=false;
function policy(l){return l.customerEditPolicy||'full'}
function esc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]))}
function bound(l){const k=l.binding||'none';if(k!=='none'&&Object.prototype.hasOwnProperty.call(content,k)){const v=content[k];if(typeof v==='string'&&v.trim()!=='')return v}return l.text||''}
function merged(l){return Object.assign({},l,overrides[l.id]||{})}
function renderOpening(){const c=template.openingCover||{};if(c.enabled===false)return;const w=document.createElement('div');w.className='ce-opening';const dc=template.desktopCover||{};if(dc.image){const media=document.createElement((dc.mediaType==='video'||/\.mp4(?:\?|$)/i.test(dc.image))?'video':'img');media.className='ce-opening-bg';media.src=dc.image;media.style.objectFit=dc.fit||'cover';media.style.objectPosition=`${50+Number(dc.posX||0)}% ${50+Number(dc.posY||0)}%`;if(media.tagName==='VIDEO'){media.muted=true;media.autoplay=true;media.loop=true;media.playsInline=true}w.appendChild(media)}const s=document.createElement('div');s.className='ce-opening-shade';w.appendChild(s);const box=document.createElement('div');box.className='ce-opening-content';const e=document.createElement('div');e.className='ce-opening-eyebrow';e.textContent=c.eyebrow||'The Wedding of';const n=document.createElement('div');n.className='ce-opening-title';n.textContent=(c.bindNames!==false&&content.couple_names)||c.names||'Nama & Nama';const b=document.createElement('button');b.className='ce-opening-btn';b.type='button';b.style.background=c.buttonColor||'#2563eb';b.textContent=c.buttonText||'Buka Undangan';b.onclick=()=>w.remove();box.append(e,n,b);w.appendChild(box);$('previewPages').appendChild(w)}
function renderLayer(raw,p){const l=merged(raw);if(l.hidden)return;if(l.optional&&l.hideWhenEmpty&&l.binding&&!content[l.binding])return;const el=document.createElement('div');el.className='ce-layer-preview';el.style.left=l.x+'px';el.style.top=l.y+'px';el.style.width=l.width+'px';el.style.height=l.height+'px';el.style.zIndex=l.zIndex||1;el.style.opacity=l.opacity??1;el.style.transform=`rotate(${l.rotation||0}deg)`;if(l.type==='text'){el.classList.add('ce-text');el.textContent=bound(l);el.style.color=l.color||'#111827';el.style.fontFamily=l.fontFamily||'inherit';el.style.fontSize=(l.fontSize||16)+'px';el.style.fontWeight=l.fontWeight||400;el.style.fontStyle=l.fontStyle||'normal';el.style.textAlign=l.textAlign||'left';el.style.background=l.background||'transparent';el.style.borderRadius=(l.borderRadius||0)+'px'}else if(l.type==='image'&&l.src){const i=document.createElement('img');i.className='ce-img';i.src=l.src;el.appendChild(i)}else{el.style.background=l.background||'#BFD8FF';el.style.borderRadius=(l.borderRadius||0)+'px'}p.appendChild(el)}
function renderPreview(){$('previewPages').innerHTML='';renderOpening();(template.pages||[]).forEach(pg=>{const shell=document.createElement('div');shell.style.width='100%';shell.style.overflow='hidden';const page=document.createElement('div');page.className='ce-page';page.style.background=pg.background||'#fff';(pg.layers||[]).slice().sort((a,b)=>(a.zIndex||0)-(b.zIndex||0)).forEach(l=>renderLayer(l,page));shell.appendChild(page);$('previewPages').appendChild(shell);requestAnimationFrame(()=>{const s=Math.min(1,shell.clientWidth/390);page.style.transform=`scale(${s})`;shell.style.height=(844*s)+'px'})})}
function findLayer(id){for(const p of template.pages||[]){const l=(p.layers||[]).find(x=>x.id===id);if(l)return l}return null}
function renderDesignList(){const h=$('designLayers');if(!h)return;h.innerHTML='';(template.pages||[]).flatMap(p=>p.layers||[]).filter(l=>policy(l)==='full').forEach(l=>{const r=document.createElement('button');r.type='button';r.className='ce-layer';r.dataset.id=l.id;r.innerHTML=`<b>${esc(l.name||l.type)}</b><small>${esc(l.type)} · Full Edit</small>`;r.onclick=()=>selectLayer(l.id);h.appendChild(r)})}
function selectLayer(id){selectedLayerId=id;document.querySelectorAll('.ce-layer').forEach(x=>x.classList.toggle('active',x.dataset.id===id));const b=findLayer(id),o=overrides[id]||{};if(!b)return;$('designControls').hidden=false;$('designColor').value=o.color||b.color||'#111827';$('designBackground').value=o.background||((b.background&&/^#[0-9a-f]{6}$/i.test(b.background))?b.background:'#ffffff');$('designFontSize').value=o.fontSize||b.fontSize||16;$('designOpacity').value=o.opacity??b.opacity??1;$('designAlign').value=o.textAlign||b.textAlign||'left'}
function design(k,v){if(!selectedLayerId||!editable)return;const l=findLayer(selectedLayerId);if(!l||policy(l)!=='full')return;overrides[selectedLayerId]=Object.assign({},overrides[selectedLayerId]||{}, {[k]:v});dirty=true;renderPreview();queueSave()}
async function saveNow(){if(saveTimer){clearTimeout(saveTimer);saveTimer=null}$('saveStatus').textContent='Menyimpan…';try{const r=await fetch(updateUrl,{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({content,design_overrides:overrides})});if(!r.ok)throw new Error('save');const j=await r.json();dirty=false;$('saveStatus').textContent='Tersimpan '+(j.saved_at||'')}catch(e){$('saveStatus').textContent='Gagal menyimpan'}}
function queueSave(){if(saveTimer)clearTimeout(saveTimer);saveTimer=setTimeout(saveNow,650)}
document.querySelectorAll('[data-content]').forEach(i=>{const k=i.dataset.content;i.value=content[k]||'';i.addEventListener('input',()=>{content[k]=i.value;dirty=true;renderPreview();queueSave()})});
document.querySelectorAll('.ce-tab').forEach(b=>b.onclick=()=>{document.querySelectorAll('.ce-tab').forEach(x=>x.classList.toggle('active',x===b));document.querySelectorAll('.ce-section').forEach(s=>s.classList.toggle('active',s.dataset.section===b.dataset.tab))});
$('saveBtn').onclick=saveNow;$('previewTop').onclick=()=>window.open(publicUrl,'_blank','noopener');
if($('designColor'))$('designColor').oninput=e=>design('color',e.target.value);if($('designBackground'))$('designBackground').oninput=e=>design('background',e.target.value);if($('designFontSize'))$('designFontSize').oninput=e=>design('fontSize',Number(e.target.value));if($('designOpacity'))$('designOpacity').oninput=e=>design('opacity',Number(e.target.value));if($('designAlign'))$('designAlign').onchange=e=>design('textAlign',e.target.value);
document.querySelectorAll('[data-mobile]').forEach(b=>b.onclick=()=>{document.querySelectorAll('[data-mobile]').forEach(x=>x.classList.toggle('active',x===b));if(b.dataset.mobile==='preview'){$('editorPanel').classList.remove('mobile-open');return}$('editorPanel').classList.add('mobile-open');document.querySelector(`.ce-tab[data-tab="${b.dataset.mobile}"]`)?.click()});
window.addEventListener('resize',renderPreview);window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue=''}});renderDesignList();renderPreview();
})();
</script>
</body>
</html>
