@extends('layouts.app')

@section('content')
@php
    $isInstanceMode = ($editorMode ?? 'master') === 'instance';
    $instance = $instance ?? null;
    $studioPlanCode = $isInstanceMode
        ? strtolower(trim((string) data_get($invitation ?? null, 'plan', 'premium')))
        : 'admin';
    $studioUiRole = $isInstanceMode && in_array($studioPlanCode, ['premium', 'royal'], true)
        ? $studioPlanCode
        : ($isInstanceMode ? 'premium' : 'admin');
    $canvasState = $isInstanceMode
        ? ($instance->template_snapshot ?: [])
        : ($template->canvas ?: []);

    $initialAssets = $assets
        ->map(function ($asset) use ($isInstanceMode, $instance) {
            return [
                'id' => $asset->id,
                'type' => $asset->type,
                'name' => $asset->name,
                'size' => (int) ($asset->size ?? 0),
                'sha256' => data_get($asset->metadata, 'sha256'),
                'url' => $isInstanceMode
                    ? route('studio.public.asset', ['token' => $instance->public_token, 'asset' => $asset->id])
                    : route('admin.studio.assets.file', $asset),
                'stream_url' => $isInstanceMode && $asset->type === 'video'
                    ? url('/studio/customer/instances/'.$instance->id.'/assets/'.$asset->id.'/stream')
                    : null,
                'delete_url' => !$isInstanceMode && \Illuminate\Support\Facades\Route::has('admin.studio.assets.delete')
                    ? route('admin.studio.assets.delete', $asset)
                    : null,
            ];
        })
        ->values()
        ->all();

    $initialFonts = $fonts
        ->map(function ($font) use ($isInstanceMode, $instance) {
            return [
                'id' => $font->id,
                'name' => $font->name,
                'family' => $font->family,
                'url' => $isInstanceMode
                    ? route('studio.public.font', ['token' => $instance->public_token, 'font' => $font->id])
                    : route('admin.studio.fonts.file', $font),
                'weight' => $font->weight,
                'style' => $font->style,
            ];
        })
        ->values()
        ->all();

    $initialPreviewInstance = (!$isInstanceMode && $previewInstance)
        ? [
            'id' => $previewInstance->id,
            'template_id' => $previewInstance->studio_template_id,
            'status' => $previewInstance->status,
            'content' => $previewInstance->content ?: [],
            'design_overrides' => $previewInstance->design_overrides ?: [],
            'updated_at' => optional($previewInstance->updated_at)->toIso8601String(),
        ]
        : null;
@endphp

<style>
    :root{--us-top:58px;--us-rail:74px;--us-lib:310px;--us-props:300px;--us-zoom:.68;--us-accent:#2563eb;--us-accent-soft:#eaf2ff;--us-ink:#172033}
    body{overflow:hidden}.us-wrap{height:calc(100vh - 64px);background:#f1f2f5;color:#1f2328;overflow:hidden;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    .us-top{height:var(--us-top);box-sizing:border-box;display:flex;align-items:center;gap:8px;padding:8px 10px;background:linear-gradient(110deg,#eaf4ff 0%,#cfe4ff 48%,#9fc5ff 100%);color:var(--us-ink);box-shadow:0 1px 0 rgba(0,0,0,.08);position:relative;z-index:120}
    .us-top-left,.us-top-center,.us-top-right{display:flex;align-items:center;gap:8px}.us-top-center{flex:1;justify-content:center;min-width:0}.us-top-right{justify-content:flex-end}.us-top-title{max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:700;opacity:.95}.us-icon-btn,.us-btn{height:38px;border:0;border-radius:10px;padding:0 12px;background:rgba(255,255,255,.42);color:var(--us-ink);font-weight:750;font-size:11px;cursor:pointer}.us-icon-btn{width:38px;padding:0;font-size:18px}.us-btn.light{background:rgba(255,255,255,.82);color:var(--us-ink)}.us-btn.primary{background:#2563eb;color:#fff;box-shadow:0 2px 10px rgba(37,99,235,.22)}.us-btn.danger{background:#fff;color:#b42318;border:1px solid #efc8c4}.us-btn:disabled{opacity:.45}.us-save-status{font-size:10px;opacity:.8}.us-meta{display:flex;align-items:center;gap:6px}.us-meta input,.us-meta select{height:34px;border:1px solid rgba(78,59,42,.14);background:rgba(255,255,255,.45);color:var(--us-ink);border-radius:9px;padding:0 9px;font-size:11px;outline:none}.us-meta input::placeholder{color:rgba(46,41,36,.55)}.us-meta select option{color:#222}.us-meta .us-name{width:160px}.us-meta .us-slug{width:135px}.us-check{display:flex;align-items:center;gap:6px;font-size:11px}.us-check input{width:auto}
    .us-body{height:calc(100% - var(--us-top));display:grid;grid-template-columns:var(--us-rail) var(--us-lib) minmax(0,1fr) var(--us-props);min-width:0;transition:grid-template-columns .30s cubic-bezier(.2,.75,.25,1)}
    .us-body.library-collapsed{--us-lib:0px}.us-body.properties-collapsed{--us-props:0px}.us-body.library-collapsed .us-library{opacity:0;pointer-events:none;border:0;transform:translateX(-18px)}.us-body.properties-collapsed .us-properties-panel{opacity:0;pointer-events:none;border:0;transform:translateX(18px)}.us-library,.us-properties-panel{transition:opacity .20s ease,transform .30s cubic-bezier(.2,.75,.25,1)}.us-workspace{transition:width .30s cubic-bezier(.2,.75,.25,1)}
    .us-rail{background:#fff;border-right:1px solid #e1e4e8;padding:10px 6px;display:flex;flex-direction:column;align-items:stretch;gap:4px;z-index:80}.us-rail-btn{border:0;background:transparent;border-radius:10px;padding:8px 4px;min-height:58px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;font-size:9px;font-weight:700;color:#4a4f55;cursor:pointer}.us-rail-btn .ico{font-size:20px;line-height:1}.us-rail-btn.active{background:var(--us-accent-soft);color:#2563eb}.us-rail-spacer{flex:1}
    .us-library{background:#fff;border-right:1px solid #e1e4e8;overflow:hidden;display:flex;flex-direction:column;z-index:70}.us-library-head{height:52px;display:flex;align-items:center;padding:0 14px;border-bottom:1px solid #eee;font-size:14px;font-weight:800}.us-library-collapse,.us-properties-collapse{position:absolute;top:50%;transform:translateY(-50%);z-index:110;width:30px;height:48px;border:1px solid #ded7cf;background:#fff;color:#655648;display:grid;place-items:center;cursor:pointer;font-size:18px;line-height:1;box-shadow:0 2px 8px rgba(55,43,33,.08);transition:left .30s cubic-bezier(.2,.75,.25,1),right .30s cubic-bezier(.2,.75,.25,1),background .15s ease,box-shadow .15s ease}.us-library-collapse:hover,.us-properties-collapse:hover{background:#f8f3ed;box-shadow:0 3px 10px rgba(55,43,33,.11)}.us-library-collapse{left:calc(var(--us-rail) + var(--us-lib) - 1px);border-radius:0 12px 12px 0}.us-body.library-collapsed .us-library-collapse{left:calc(var(--us-rail) - 1px)}.us-properties-collapse{right:calc(var(--us-props) - 1px);border-radius:12px 0 0 12px}.us-body.properties-collapsed .us-properties-collapse{right:-1px}.us-library-scroll{padding:14px;overflow:auto;flex:1}.us-panel-section[hidden]{display:none!important}.us-search{display:flex;gap:8px;margin-bottom:12px}.us-search input{flex:1;height:40px;border:1px solid #dadde3;border-radius:10px;padding:0 12px;font-size:12px}.us-section{margin-bottom:18px}.us-h{font-size:10px;font-weight:850;letter-spacing:.07em;text-transform:uppercase;color:#7d838b;margin:0 0 9px}.us-tools{display:grid;grid-template-columns:1fr 1fr;gap:8px}.us-tool{min-height:46px;border:1px solid #dedfe3;background:#fff;border-radius:11px;font-size:11px;font-weight:800;cursor:pointer}.us-tool:hover{border-color:#7aa7f7;background:#f2f7ff}.us-upload{padding:11px;border:1px dashed #c7cad1;border-radius:11px;background:#fafbfc}.us-upload input{width:100%;box-sizing:border-box;font-size:10px}.us-upload input[type=text]{border:1px solid #d9dce2;border-radius:9px;padding:9px}.us-small{font-size:10px;color:#7d838b;line-height:1.45}.us-asset-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:9px}.us-asset{aspect-ratio:1;border-radius:10px;overflow:hidden;background:#f3f4f6;border:1px solid #e1e3e8;cursor:pointer}.us-asset img,.us-asset video{width:100%;height:100%;object-fit:cover}.us-font-list,.us-list{display:flex;flex-direction:column;gap:6px}.us-font-item,.us-layer-row{display:flex;align-items:center;gap:7px;padding:9px;border:1px solid #e5e7eb;border-radius:9px;font-size:10px;background:#fff}.us-layer-row{cursor:pointer}.us-layer-row.active{border-color:#6f9df2;background:#edf4ff}.us-layer-row span,.us-font-item span{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .us-workspace{min-width:0;position:relative;display:flex;flex-direction:column;background:#f0f1f5;overflow:hidden}.us-contextbar{height:48px;display:flex;align-items:center;gap:7px;padding:0 12px;border-bottom:1px solid #dfe2e6;background:#fff}.us-contextbar .us-btn{height:32px;background:#fff;color:#222;border:1px solid #dedfe3}.us-canvas-zone{flex:1;overflow:auto;padding:28px 34px 22px;display:flex;flex-direction:column;align-items:center;gap:14px}.us-stagebar{display:flex;gap:7px;align-items:center;justify-content:center}.us-canvas-shell{--us-zoom:.68;position:relative;width:calc(390px * var(--us-zoom));height:calc(844px * var(--us-zoom));flex:0 0 auto}.us-canvas{position:absolute;left:0;top:0;width:390px;height:844px;overflow:hidden;background:#fff;transform:scale(var(--us-zoom));transform-origin:top left;box-shadow:0 14px 38px rgba(27,31,36,.16)}.us-canvas.transitioning{pointer-events:none}.us-layer{position:absolute;box-sizing:border-box;transform-origin:center center;touch-action:none;user-select:none}.us-layer.selected{outline:2px solid #2F6FED;outline-offset:1px}.us-layer.locked{cursor:not-allowed}.us-layer.hidden{display:none}.us-text{display:flex;align-items:center;white-space:pre-wrap;overflow:hidden}.us-image,.us-video{width:100%;height:100%;display:block;object-fit:cover;pointer-events:none}.us-shape{width:100%;height:100%;background:#BFD8FF}.us-handle{display:none;position:absolute;width:14px;height:14px;border:2px solid #fff;background:#2F6FED;border-radius:50%;right:-9px;bottom:-9px;cursor:nwse-resize;box-shadow:0 1px 4px rgba(0,0,0,.25)}.us-layer.selected .us-handle{display:block}.us-add-page-main{width:min(425px,90%);height:44px;border:1px solid #cfd2d8;border-radius:10px;background:#fff;font-size:12px;font-weight:800;cursor:pointer}.us-pages-wrap{border-top:1px solid #dfe2e6;background:#fff;padding:8px 12px}.us-pages{display:flex;gap:7px;align-items:center;overflow-x:auto;min-height:72px}.us-page-chip{flex:0 0 auto;width:112px;height:58px;border:1px solid #d6d9df;border-radius:9px;background:#fff;padding:7px 9px;text-align:left;cursor:pointer}.us-page-chip.active{border:2px solid #2F6FED;background:#EAF4FF}.us-page-chip b{font-size:10px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.us-page-chip small{font-size:9px;color:#8a8f97}.us-add-page{height:58px;flex:0 0 auto;padding:0 14px;border:1px dashed #b8bdc6;border-radius:9px;background:#fafafa;font-size:10px;font-weight:800;cursor:pointer}.us-work-bottom{height:44px;border-top:1px solid #dfe2e6;background:#fff;display:flex;align-items:center;justify-content:flex-end;gap:9px;padding:0 14px;font-size:11px}.us-work-bottom input[type=range]{width:120px}.us-grid-view-btn{width:34px;height:34px;border:1px solid #d8dce2;background:#fff;border-radius:8px;display:grid;place-items:center;cursor:pointer;font-size:16px}.us-grid-view-btn:hover{background:#F3F8FF;border-color:#9ABAF4}.us-page-chip{position:relative}.us-page-more{position:absolute;right:5px;top:5px;width:24px;height:22px;border:0;border-radius:7px;background:transparent;font-size:16px;line-height:1;display:grid;place-items:center;color:#49617F}.us-page-chip:hover .us-page-more,.us-page-chip.active .us-page-more{background:rgba(255,255,255,.78)}.us-context-menu{position:fixed;z-index:9999;width:230px;background:#fff;border:1px solid #dedfe3;border-radius:12px;box-shadow:0 12px 38px rgba(22,25,30,.18);padding:6px;display:none}.us-context-menu.open{display:block}.us-context-menu button{width:100%;height:36px;border:0;background:transparent;border-radius:8px;text-align:left;padding:0 10px;font-size:12px;color:#272b30;display:flex;align-items:center;justify-content:space-between;cursor:pointer}.us-context-menu button:hover{background:#f4f1ed}.us-context-menu button.danger{color:#b42318}.us-context-menu hr{border:0;border-top:1px solid #eceef1;margin:5px 0}.us-context-menu kbd{font:10px ui-monospace,SFMono-Regular,Menlo,monospace;color:#7a8088;background:#f3f4f6;padding:2px 5px;border-radius:5px}.us-page-grid-overlay{position:fixed;inset:0;z-index:9000;background:rgba(27,29,32,.28);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;padding:24px}.us-page-grid-overlay.open{display:flex}.us-page-grid-panel{width:min(900px,94vw);max-height:84vh;background:#fff;border-radius:16px;box-shadow:0 24px 70px rgba(0,0,0,.24);overflow:hidden;display:flex;flex-direction:column}.us-page-grid-head{height:54px;padding:0 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eceef1}.us-page-grid-head b{font-size:14px}.us-page-grid-close{width:34px;height:34px;border:0;border-radius:8px;background:#f3f4f6;cursor:pointer}.us-page-grid{padding:16px;overflow:auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px}.us-page-grid-card{border:1px solid #dfe2e6;border-radius:12px;background:#fff;padding:8px;cursor:pointer;text-align:left}.us-page-grid-card.active{border:2px solid #2F6FED;background:#EAF4FF}.us-page-grid-thumb{aspect-ratio:390/844;border-radius:7px;background:#FFFFFF;border:1px solid #eceef1;position:relative;overflow:hidden;margin-bottom:7px}.us-page-grid-card b{font-size:11px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.us-page-grid-card small{font-size:9px;color:#8a8f97}.us-mobile-nav{display:none}
    .us-properties-panel{background:#fff;border-left:1px solid #e1e4e8;overflow:auto;padding:14px}.us-properties-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}.us-properties-head b{font-size:13px}.us-field{margin-bottom:9px}.us-field label{display:block;font-size:9px;font-weight:850;color:#777e87;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px}.us-field input,.us-field select,.us-field textarea{width:100%;box-sizing:border-box;border:1px solid #d9dce2;border-radius:8px;padding:8px;background:#fff;font-size:11px}.us-grid2{display:grid;grid-template-columns:1fr 1fr;gap:7px}.us-page-actions{display:grid;grid-template-columns:1fr 1fr;gap:7px}.us-properties-panel .us-btn{background:#fff;color:#222;border:1px solid #d9dce2}.us-properties-panel .us-btn.danger{color:#b42318}.us-toast{position:fixed;left:50%;bottom:18px;transform:translateX(-50%);z-index:1000;background:#15171a;color:#fff;padding:10px 14px;border-radius:999px;font-size:11px;opacity:0;pointer-events:none;transition:.2s}.us-toast.show{opacity:1}
    @keyframes usFadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}@keyframes usZoom{from{opacity:0;transform:scale(.88)}to{opacity:1;transform:scale(1)}}@keyframes usFloat{0%,100%{translate:0 0}50%{translate:0 -10px}}@keyframes pageFade{from{opacity:0}to{opacity:1}}@keyframes pageSlideUp{from{opacity:.25;transform:translateY(70px)}to{opacity:1;transform:translateY(0)}}@keyframes pageSlideDown{from{opacity:.25;transform:translateY(-70px)}to{opacity:1;transform:translateY(0)}}@keyframes pageSlideLeft{from{opacity:.25;transform:translateX(70px)}to{opacity:1;transform:translateX(0)}}@keyframes pageSlideRight{from{opacity:.25;transform:translateX(-70px)}to{opacity:1;transform:translateX(0)}}@keyframes pageZoom{from{opacity:0;transform:scale(.88)}to{opacity:1;transform:scale(1)}}@keyframes pageBlur{from{opacity:0;filter:blur(15px);transform:scale(1.03)}to{opacity:1;filter:blur(0);transform:scale(1)}}
    @media(max-width:1100px){:root{--us-lib:270px;--us-props:270px}.us-meta .us-slug,.us-meta select,.us-top .us-check{display:none}}
    @media(max-width:760px){.us-library-collapse,.us-properties-collapse{display:none}.us-body.library-collapsed,.us-body.properties-collapsed{--us-lib:310px;--us-props:300px}.us-body.library-collapsed .us-library,.us-body.properties-collapsed .us-properties-panel{opacity:1;pointer-events:auto}body{overflow:hidden}.us-wrap{height:calc(100vh - 60px)}.us-top{height:54px;padding:7px 8px}.us-top .us-meta,.us-save-status,.us-top-center{display:none}.us-top-left{flex:1}.us-top-right .us-btn:not(.primary),#undoBtn,#redoBtn{display:none}.us-body{height:calc(100% - 54px);display:block;position:relative}.us-rail{display:none}.us-library{position:absolute;left:0;right:0;bottom:62px;height:min(62vh,520px);border:0;border-top:1px solid #dfe2e6;border-radius:18px 18px 0 0;box-shadow:0 -10px 36px rgba(0,0,0,.16);transform:translateY(110%);transition:.22s;z-index:200}.us-library.mobile-open{transform:translateY(0)}.us-close{display:block}.us-workspace{height:100%;padding-bottom:62px}.us-contextbar{height:42px;justify-content:center}.us-contextbar .us-btn{height:30px;font-size:10px}.us-canvas-zone{padding:18px 10px 12px;gap:12px}.us-canvas-shell{--us-zoom:.82}.us-add-page-main{height:40px}.us-pages-wrap{display:none}.us-work-bottom{display:flex;position:absolute;right:8px;bottom:70px;width:auto;height:40px;border:0;background:transparent;padding:0;z-index:205}.us-work-bottom>span,.us-work-bottom>input{display:none}.us-grid-view-btn{width:40px;height:40px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,.12)}.us-properties-panel{position:absolute;left:0;right:0;bottom:62px;height:min(64vh,540px);border:0;border-top:1px solid #dfe2e6;border-radius:18px 18px 0 0;box-shadow:0 -10px 36px rgba(0,0,0,.16);transform:translateY(110%);transition:.22s;z-index:210}.us-properties-panel.mobile-open{transform:translateY(0)}.us-mobile-nav{display:grid;grid-template-columns:repeat(6,1fr);position:absolute;left:0;right:0;bottom:0;height:62px;background:#fff;border-top:1px solid #dfe2e6;z-index:220}.us-mobile-nav button{border:0;background:#fff;font-size:9px;font-weight:750;color:#555;padding:5px 2px}.us-mobile-nav .ico{display:block;font-size:18px;margin-bottom:3px}.us-mobile-nav button.active{color:#2563eb}.us-top-title{display:none}}

    .us-element-tabs{display:flex;gap:6px;margin-bottom:12px}
    .us-element-tab{flex:1;height:34px;border:1px solid #dedfe3;background:#fff;border-radius:9px;font-size:10px;font-weight:800;cursor:pointer}
    .us-element-tab.active{background:#f3e7d8;border-color:#b99674;color:#493b31}
    .us-element-group[hidden]{display:none!important}
    .us-element-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
    .us-element-card{aspect-ratio:1;border:1px solid #e0e2e6;background:#fff;border-radius:10px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;cursor:pointer;padding:7px;font-size:9px;color:#5b6169}
    .us-element-card:hover{border-color:#b79470;background:#F4F8FF}
    .us-element-preview{width:34px;height:34px;background:#BFD8FF}
    .us-element-preview.circle{border-radius:50%}
    .us-element-preview.rounded{border-radius:9px}
    .us-element-preview.oval{border-radius:50%;width:42px;height:26px}
    .us-element-preview.line{height:3px;width:42px;margin:15px 0}
    .us-element-preview.triangle{clip-path:polygon(50% 0,100% 100%,0 100%)}
    .us-element-preview.star{clip-path:polygon(50% 0,61% 35%,98% 35%,68% 57%,79% 94%,50% 72%,21% 94%,32% 57%,2% 35%,39% 35%)}
    .us-element-preview.heart{clip-path:polygon(50% 90%,8% 49%,8% 25%,20% 10%,36% 8%,50% 22%,64% 8%,80% 10%,92% 25%,92% 49%)}
    .us-element-preview.hex{clip-path:polygon(25% 7%,75% 7%,100% 50%,75% 93%,25% 93%,0 50%)}
    .us-frame-preview{width:42px;height:52px;border:3px solid #c8b7a4;background:linear-gradient(135deg,#faf7f3,#ece5de);position:relative;overflow:hidden}
    .us-frame-preview::after{content:'✦';position:absolute;inset:0;display:grid;place-items:center;color:#ad9b89;font-size:14px}
    .us-frame-preview.circle{border-radius:50%;height:42px}
    .us-frame-preview.rounded{border-radius:10px}
    .us-frame-preview.arch{border-radius:22px 22px 4px 4px}
    .us-frame-preview.heart{clip-path:polygon(50% 92%,7% 50%,7% 26%,20% 9%,36% 8%,50% 22%,64% 8%,80% 9%,93% 26%,93% 50%);border:0;background:#d1c0ad}
    .us-grid-preview{width:46px;height:46px;display:grid;gap:2px}
    .us-grid-preview span{background:#d4c3b1;border-radius:2px}
    .us-media-cell{position:relative;overflow:hidden;background:#eee8e1;min-width:0;min-height:0}
    .us-media-cell.empty::before{content:'+';position:absolute;inset:0;display:grid;place-items:center;color:#9c8b79;font-size:22px;font-weight:400}
    .us-media-cell.selected-cell{outline:2px solid #9b7652;outline-offset:-2px}
    .us-media-cell img{position:absolute;left:50%;top:50%;width:100%;height:100%;object-fit:cover;transform-origin:center center;pointer-events:none}
    .us-frame-inner{width:100%;height:100%;overflow:hidden;background:#eee8e1}
    .us-frame-circle{border-radius:50%}.us-frame-rounded{border-radius:16px}.us-frame-arch{border-radius:50% 50% 10px 10px}.us-frame-heart{clip-path:polygon(50% 92%,7% 50%,7% 26%,20% 9%,36% 8%,50% 22%,64% 8%,80% 9%,93% 26%,93% 50%)}
    .us-grid-inner{width:100%;height:100%;display:grid;overflow:hidden}
    .us-grid-2h{grid-template-columns:1fr 1fr}
    .us-grid-2v{grid-template-rows:1fr 1fr}
    .us-grid-3{grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr}
    .us-grid-3 .us-media-cell:first-child{grid-row:1/3}
    .us-grid-4{grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr}
    .us-grid-6{grid-template-columns:repeat(3,1fr);grid-template-rows:1fr 1fr}
    .us-grid-mosaic{grid-template-columns:1.25fr .75fr;grid-template-rows:1fr 1fr}
    .us-grid-mosaic .us-media-cell:first-child{grid-row:1/3}
    .us-media-help{font-size:9px;line-height:1.45;color:#7b8188;padding:8px 9px;background:#f8f5f1;border-radius:9px;margin-top:8px}
    .us-asset[draggable="true"]{cursor:grab}.us-asset[draggable="true"]:active{cursor:grabbing}


    /* V1.4 Direct Canvas Manipulation */
    .us-layer{overflow:visible!important}
    .us-layer-content{position:absolute;inset:0;overflow:hidden;border-radius:inherit}
    .us-selection-handle{display:none;position:absolute;width:12px;height:12px;background:#fff;border:2px solid #8a6848;border-radius:50%;z-index:30;box-sizing:border-box;box-shadow:0 1px 4px rgba(0,0,0,.18);touch-action:none}
    .us-layer.selected:not(.locked)>.us-selection-handle{display:block}
    .us-selection-handle[data-handle="nw"]{left:-7px;top:-7px;cursor:nwse-resize}
    .us-selection-handle[data-handle="n"]{left:50%;top:-7px;transform:translateX(-50%);cursor:ns-resize}
    .us-selection-handle[data-handle="ne"]{right:-7px;top:-7px;cursor:nesw-resize}
    .us-selection-handle[data-handle="e"]{right:-7px;top:50%;transform:translateY(-50%);cursor:ew-resize}
    .us-selection-handle[data-handle="se"]{right:-7px;bottom:-7px;cursor:nwse-resize}
    .us-selection-handle[data-handle="s"]{left:50%;bottom:-7px;transform:translateX(-50%);cursor:ns-resize}
    .us-selection-handle[data-handle="sw"]{left:-7px;bottom:-7px;cursor:nesw-resize}
    .us-selection-handle[data-handle="w"]{left:-7px;top:50%;transform:translateY(-50%);cursor:ew-resize}
    .us-rotate-stem{display:none;position:absolute;left:50%;top:-31px;width:1px;height:24px;background:#8a6848;z-index:28}
    .us-layer.selected:not(.locked)>.us-rotate-stem{display:block}
    .us-rotate-handle{display:none;position:absolute;left:50%;top:-42px;width:18px;height:18px;transform:translateX(-50%);border:2px solid #8a6848;background:#fff;border-radius:50%;z-index:31;cursor:grab;box-shadow:0 1px 4px rgba(0,0,0,.16);touch-action:none}
    .us-layer.selected:not(.locked)>.us-rotate-handle{display:grid;place-items:center}
    .us-rotate-handle::after{content:'↻';font-size:10px;color:#6d5138;line-height:1}
    .us-layer.crop-active{outline:2px solid #2F6FED!important;outline-offset:2px}
    .us-layer.crop-active .us-media-cell{cursor:move}
    .us-layer.crop-active .us-media-cell.selected-cell{outline:2px dashed #2F6FED;outline-offset:-3px}
    .us-crop-badge{display:none;position:absolute;left:50%;top:-28px;transform:translateX(-50%);z-index:34;background:#2f2924;color:#fff;border-radius:999px;padding:4px 8px;font-size:9px;white-space:nowrap;pointer-events:none}
    .us-layer.crop-active>.us-crop-badge{display:block}
    .us-binding-badge{display:inline-flex;align-items:center;gap:4px;border:1px solid #dfd2c3;background:#f8f1e8;color:#6f5339;border-radius:999px;padding:3px 7px;font-size:9px;font-weight:750;margin-top:4px}
    .us-rule-box{margin:11px 0;padding:10px;border:1px solid #e4e0db;background:#faf8f5;border-radius:10px}
    .us-rule-box .us-h{margin-bottom:8px}
    .us-contextbar .us-crop-action{display:none}
    .us-contextbar.crop-mode .us-crop-action{display:inline-flex}
    .us-contextbar.crop-mode #previewTransition{display:none}
    .us-layer[data-edit-policy="content"]{--policy-ring:#c79c65}
    .us-layer[data-edit-policy="locked"]{--policy-ring:#b85b54}
    @media(max-width:760px){
        .us-selection-handle{width:20px;height:20px;border-width:2px}
        .us-selection-handle[data-handle="nw"]{left:-11px;top:-11px}
        .us-selection-handle[data-handle="n"]{top:-11px}
        .us-selection-handle[data-handle="ne"]{right:-11px;top:-11px}
        .us-selection-handle[data-handle="e"]{right:-11px}
        .us-selection-handle[data-handle="se"]{right:-11px;bottom:-11px}
        .us-selection-handle[data-handle="s"]{bottom:-11px}
        .us-selection-handle[data-handle="sw"]{left:-11px;bottom:-11px}
        .us-selection-handle[data-handle="w"]{left:-11px}
        .us-rotate-stem{top:-38px;height:28px}
        .us-rotate-handle{width:26px;height:26px;top:-52px}
        .us-crop-badge{top:-34px;font-size:10px;padding:5px 9px}
    }


    /* V1.4R — Responsive invitation architecture */
    .us-device-switch{display:flex;gap:4px;margin-left:auto}
    .us-device-btn{height:32px;border:1px solid #dedfe3;background:#fff;border-radius:8px;padding:0 10px;font-size:10px;font-weight:800;cursor:pointer;color:#4f5358}
    .us-device-btn:hover,.us-device-btn.active{background:#f3e7d8;border-color:#b99674;color:#493b31}
    .us-responsive-card{border:1px solid #e2ded9;background:#faf8f5;border-radius:11px;padding:10px;margin:10px 0}
    .us-responsive-card .us-h{margin-bottom:8px}
    .us-page-type-badge{display:inline-flex;align-items:center;border:1px solid #e0d4c6;background:#f7efe6;color:#73583f;border-radius:999px;padding:2px 6px;font-size:8px;font-weight:800;margin-left:5px;vertical-align:middle}
    .us-preview-overlay{position:fixed;inset:0;z-index:10050;background:#ece9e5;display:none;flex-direction:column}
    .us-preview-overlay.open{display:flex}
    .us-preview-top{height:54px;display:flex;align-items:center;gap:8px;padding:0 14px;background:#fff;border-bottom:1px solid #dfe2e6}
    .us-preview-top b{font-size:13px}
    .us-preview-top .spacer{flex:1}
    .us-preview-close{height:34px;border:1px solid #d9dce1;background:#fff;border-radius:9px;padding:0 12px;cursor:pointer}
    .us-preview-stage{flex:1;min-height:0;display:flex;align-items:center;justify-content:center;padding:18px;overflow:hidden}
    .us-preview-device{background:#fff;box-shadow:0 18px 50px rgba(23,25,28,.2);overflow:hidden;position:relative}
    .us-preview-device.desktop{width:min(1180px,96vw);height:min(720px,86vh);display:grid;grid-template-columns:minmax(0,1fr) 390px}
    .us-preview-device.tablet{width:min(390px,90vw);height:min(844px,86vh);border-radius:16px}
    .us-preview-device.mobile{width:min(390px,92vw);height:min(844px,86vh);border-radius:22px}
    .us-preview-cover{height:100%;overflow:hidden;background:#eee;position:relative}
    .us-preview-scroll{height:100%;overflow:auto;background:#fff;overscroll-behavior:contain}
    .us-preview-section{position:relative;overflow:hidden;transform-origin:top left;flex:0 0 auto}
    .us-preview-page-host{position:relative;overflow:hidden;transform-origin:top left;background:#fff}
    .us-preview-mobile-stack{height:100%;overflow:auto;background:#fff}
    .us-static-layer{position:absolute;box-sizing:border-box;transform-origin:center center;overflow:hidden}
    .us-static-layer img,.us-static-layer video{width:100%;height:100%;display:block;object-fit:cover}
    .us-static-text{display:flex;align-items:center;white-space:pre-wrap;overflow:hidden}
    .us-preview-note{position:absolute;left:10px;bottom:10px;background:rgba(32,29,26,.78);color:#fff;padding:5px 8px;border-radius:7px;font-size:9px;z-index:50}
    .us-gallery-settings[hidden]{display:none!important}
    .us-gallery-preview-pulse .us-media-cell{animation:usGalleryPulse 1.1s ease both}
    .us-gallery-preview-fade .us-media-cell{animation:usGalleryFade 1.1s ease both}
    .us-gallery-preview-slide .us-media-cell{animation:usGallerySlide 1.1s ease both}
    .us-gallery-preview-zoom .us-media-cell{animation:usGalleryZoom 1.1s ease both}
    .us-gallery-preview-blur .us-media-cell{animation:usGalleryBlur 1.1s ease both}
    @keyframes usGalleryPulse{0%{transform:scale(.96);opacity:.65}100%{transform:scale(1);opacity:1}}
    @keyframes usGalleryFade{0%{opacity:0}100%{opacity:1}}
    @keyframes usGallerySlide{0%{opacity:0;transform:translateY(20px)}100%{opacity:1;transform:none}}
    @keyframes usGalleryZoom{0%{opacity:0;transform:scale(.84)}100%{opacity:1;transform:scale(1)}}
    @keyframes usGalleryBlur{0%{opacity:0;filter:blur(10px)}100%{opacity:1;filter:none}}
    @media(max-width:760px){
        .us-device-switch{gap:2px}
        .us-device-btn{padding:0 7px}
        .us-preview-stage{padding:6px}
        .us-preview-top{padding:0 8px}
    }


    /* V1.5 — No-code component library + layer animation engine */
    .us-component-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .us-component-card{border:1px solid #e1ddd8;background:#fff;border-radius:11px;min-height:74px;padding:9px;text-align:left;cursor:pointer;display:flex;flex-direction:column;gap:5px}
    .us-component-card:hover{border-color:#6C9DFF;background:#F4F8FF}
    .us-component-card b{font-size:11px;color:#302b27}
    .us-component-card span{font-size:9px;line-height:1.35;color:#747980}
    .us-component-icon{font-size:18px;line-height:1}
    .us-component-note{font-size:9px;line-height:1.5;color:#70757d;background:#F2F7FF;border-radius:9px;padding:8px;margin-top:10px}
    .us-animation-preset-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;margin:8px 0}
    .us-animation-preset{height:38px;border:1px solid #dedfe3;background:#fff;border-radius:9px;font-size:10px;font-weight:750;cursor:pointer}
    .us-animation-preset:hover,.us-animation-preset.active{border-color:#b99674;background:#f3e7d8;color:#4c3b2d}
    .us-layer-anim-badge{display:inline-flex;margin-left:4px;font-size:8px;line-height:1;padding:2px 5px;border-radius:999px;background:#efe5da;color:#725b45;border:1px solid #e2d5c7}
    @keyframes usFade{from{opacity:0}to{opacity:1}}
    @keyframes usFadeDown{from{opacity:0;transform:translateY(-18px)}to{opacity:1;transform:none}}
    @keyframes usFadeLeft{from{opacity:0;transform:translateX(18px)}to{opacity:1;transform:none}}
    @keyframes usFadeRight{from{opacity:0;transform:translateX(-18px)}to{opacity:1;transform:none}}
    @keyframes usPop{0%{opacity:0;transform:scale(.78)}70%{opacity:1;transform:scale(1.04)}100%{transform:scale(1)}}
    @keyframes usSoftScale{from{opacity:0;transform:scale(.94)}to{opacity:1;transform:scale(1)}}
    @keyframes usBlurIn{from{opacity:0;filter:blur(9px);transform:scale(.98)}to{opacity:1;filter:blur(0);transform:none}}
    @keyframes usRevealUp{from{opacity:0;clip-path:inset(100% 0 0 0);transform:translateY(12px)}to{opacity:1;clip-path:inset(0 0 0 0);transform:none}}


    .us-asset{position:relative;overflow:hidden;background:#f2f3f4}
    .us-asset img,.us-asset video{width:100%;height:100%;object-fit:cover;display:block}
    .us-media-kind{position:absolute;right:5px;bottom:5px;background:rgba(25,25,25,.74);color:#fff;border-radius:5px;padding:2px 5px;font-size:7px;font-weight:800;letter-spacing:.04em}
    .us-media-fallback{position:absolute;inset:0;display:grid;place-items:center;padding:10px;text-align:center;font-size:9px;color:#777;background:#f3f3f3}
    .us-asset.media-error{border-color:#d8a7a1}


    /* V1.6 STEP 2 */
    .us-guide-line{position:absolute;left:0;right:0;height:0;border-top:1px dashed rgba(37,99,235,.72);z-index:45;pointer-events:none}
    .us-guide-line::before{content:attr(data-label);position:absolute;left:8px;top:-11px;background:#2563eb;color:#fff;padding:2px 6px;border-radius:999px;font-size:8px;font-weight:800}
    .us-guide-handle{position:absolute;left:50%;top:-9px;transform:translateX(-50%);width:38px;height:18px;border-radius:999px;background:#fff;border:1px solid #78a5f5;box-shadow:0 2px 7px rgba(37,99,235,.16);pointer-events:auto;cursor:ns-resize;touch-action:none}
    .us-guide-handle::after{content:'↕';position:absolute;inset:0;display:grid;place-items:center;color:#2563eb;font-size:10px;font-weight:800}
    .us-canvas.us-preview .us-guide-line{display:none}
    .us-transition-chip{flex:0 0 auto;display:flex;align-items:center;justify-content:center}
    .us-transition-chip-btn{height:30px;min-width:42px;padding:0 8px;border:1px solid #cbd8eb;background:#fff;border-radius:999px;color:#48617f;font-size:9px;font-weight:800;cursor:pointer}
    .us-transition-chip-btn:hover{border-color:#78a5f5;background:#eff6ff;color:#1d4ed8}
    .us-transition-popover{position:fixed;z-index:10020;width:230px;background:#fff;border:1px solid #dce3ee;border-radius:12px;padding:8px;box-shadow:0 16px 42px rgba(15,23,42,.2);display:none}
    .us-transition-popover.open{display:block}
    .us-transition-popover-title{font-size:10px;font-weight:850;color:#64748b;padding:3px 5px 8px;text-transform:uppercase;letter-spacing:.06em}
    .us-transition-options{display:grid;grid-template-columns:1fr 1fr;gap:6px}
    .us-transition-option{height:36px;border:1px solid #e1e6ef;background:#fff;border-radius:9px;font-size:10px;font-weight:750;cursor:pointer}
    .us-transition-option:hover,.us-transition-option.active{border-color:#78a5f5;background:#eff6ff;color:#1d4ed8}
    .us-advanced-folder{border:1px solid #e1e6ef;border-radius:10px;background:#fff;margin:9px 0;overflow:hidden}
    .us-advanced-folder>summary{cursor:pointer;padding:10px 11px;font-size:10px;font-weight:850;color:#475569;list-style:none;display:flex;align-items:center;justify-content:space-between}
    .us-advanced-folder>summary::-webkit-details-marker{display:none}.us-advanced-folder>summary::after{content:'⌄';font-size:13px;color:#64748b}.us-advanced-folder[open]>summary::after{content:'⌃'}
    .us-advanced-folder-body{padding:0 10px 10px}
    .us-dropzone{border:1.5px dashed #9bb8e8;border-radius:14px;background:#f7fbff;padding:18px 12px;text-align:center;cursor:pointer;transition:.16s}
    .us-dropzone:hover,.us-dropzone.dragover{border-color:#2563eb;background:#eef6ff}
    .us-dropzone-icon{width:38px;height:38px;border-radius:12px;background:#e6f0ff;color:#2563eb;display:grid;place-items:center;margin:0 auto 8px;font-size:19px}
    .us-dropzone b{display:block;font-size:11px;color:#1f2b3d;margin-bottom:3px}.us-dropzone span{font-size:9px;color:#748094}
    .us-upload-progress{height:4px;border-radius:999px;background:#e8edf5;overflow:hidden;margin-top:10px;display:none}.us-upload-progress>i{display:block;height:100%;width:0;background:#2563eb;transition:width .2s}
    .us-autosave-dot{width:7px;height:7px;border-radius:50%;display:inline-block;background:#94a3b8;margin-right:4px}.us-autosave-dot.saving{background:#f59e0b}.us-autosave-dot.saved{background:#22c55e}.us-autosave-dot.error{background:#ef4444}
    .us-preview-overlay{background:rgba(8,15,29,.94)!important;backdrop-filter:blur(4px)}.us-preview-top{background:#111827!important;border-color:#263248!important;color:#fff}.us-preview-top .us-small{color:#aab6c8}
    .us-preview-close,.us-preview-top .us-device-btn{background:#172033!important;border-color:#334155!important;color:#eaf2ff!important}
    .us-preview-stage{overflow:auto!important;align-items:flex-start!important;justify-content:center!important;padding:28px 18px 50px!important}.us-preview-device{margin:auto 0;flex:0 0 auto}
    .us-preview-device.mobile,.us-preview-device.tablet{height:min(844px,calc(100vh - 130px))!important}.us-preview-device.desktop{height:min(720px,calc(100vh - 130px))!important}
    .us-pages{align-items:center}
    @media(max-width:760px){.us-guide-handle{width:44px;height:22px;top:-11px}.us-transition-popover{width:min(250px,calc(100vw - 24px))}}


    /* V1.6 STEP 3 — Frame media visibility + crop UX */
    .us-frame-inner{position:relative;width:100%;height:100%;overflow:hidden}
    .us-frame-inner>.us-media-cell{position:relative;width:100%;height:100%;min-width:100%;min-height:100%}
    .us-frame-inner>.us-media-cell img,
    .us-grid-inner>.us-media-cell img{
        display:block;
        max-width:none;
        max-height:none;
        user-select:none;
        -webkit-user-drag:none;
    }
    .us-frame-inner.us-frame-polaroid>.us-media-cell{height:calc(100% - 16px)}
    .us-layer.crop-active>.us-layer-content{outline:2px solid #2563eb;outline-offset:2px}
    .us-layer.crop-active .us-selection-handle,
    .us-layer.crop-active .us-rotate-handle,
    .us-layer.crop-active .us-rotate-stem{display:none!important}
    .us-crop-toolbar{
        position:absolute;left:50%;bottom:-48px;transform:translateX(-50%);
        z-index:60;display:none;align-items:center;gap:6px;padding:6px;
        background:#101828;border:1px solid #344054;border-radius:12px;
        box-shadow:0 10px 30px rgba(15,23,42,.3);white-space:nowrap
    }
    .us-layer.crop-active>.us-crop-toolbar{display:flex}
    .us-crop-toolbar button{
        height:28px;border:1px solid #475467;background:#1d2939;color:#f8fafc;
        border-radius:8px;padding:0 8px;font-size:9px;font-weight:800;cursor:pointer
    }
    .us-crop-toolbar button:hover{background:#344054}
    .us-crop-toolbar .done{background:#2563eb;border-color:#2563eb}
    .us-crop-hint{
        position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);
        z-index:55;pointer-events:none;background:rgba(15,23,42,.78);color:#fff;
        padding:7px 10px;border-radius:999px;font-size:9px;font-weight:800;
        opacity:0;transition:opacity .18s
    }
    .us-layer.crop-active>.us-crop-hint{opacity:1}
    .us-layer.crop-active.crop-moving>.us-crop-hint{opacity:0}
    @media(max-width:760px){
        .us-crop-toolbar{bottom:-56px}
        .us-crop-toolbar button{height:34px;font-size:10px;padding:0 10px}
        .us-crop-hint{font-size:10px}
    }


    /* V1.6 STEP 3.1 — Desktop cover + preview transitions */
    .us-desktop-cover-card{margin-top:10px}
    .us-desktop-cover-preview{height:180px;border-radius:12px;overflow:hidden;background:#0f172a;position:relative;margin:8px 0}
    .us-desktop-cover-preview img{position:absolute;inset:0;width:100%;height:100%;display:block;transform-origin:center center;max-width:none;max-height:none}
    .us-desktop-cover-empty{position:absolute;inset:0;display:grid;place-items:center;text-align:center;padding:20px;color:#94a3b8;font-size:10px}
    .us-cover-dropzone{border:1.5px dashed #9bb8e8;border-radius:12px;background:#f7fbff;padding:14px 10px;text-align:center;cursor:pointer;transition:.16s}
    .us-cover-dropzone:hover,.us-cover-dropzone.dragover{border-color:#2563eb;background:#eef6ff}
    .us-cover-dropzone-icon{width:34px;height:34px;border-radius:10px;background:#e6f0ff;color:#2563eb;display:grid;place-items:center;margin:0 auto 7px;font-size:17px}
    .us-cover-dropzone b{display:block;font-size:10px;color:#1f2b3d}.us-cover-dropzone span{font-size:8px;color:#748094}
    .us-cover-progress{height:4px;border-radius:999px;background:#e8edf5;overflow:hidden;margin-top:8px;display:none}.us-cover-progress>i{display:block;height:100%;width:0;background:#2563eb;transition:width .2s}
    .us-preview-cover{isolation:isolate}
    .us-preview-cover-media{position:absolute;inset:0;overflow:hidden;background:#0f172a}
    .us-preview-cover-media img{position:absolute;inset:0;width:100%;height:100%;display:block;max-width:none;max-height:none;transform-origin:center center}
    .us-preview-cover-placeholder{position:absolute;inset:0;display:grid;place-items:center;padding:30px;text-align:center;color:#94a3b8;background:#0f172a;font-size:12px;font-weight:700}
    .us-preview-section.us-transition-pending{opacity:0}
    .us-preview-section.us-transition-running{will-change:transform,opacity,filter}

    /* V1.6 STEP 4 — Sample/Empty modes, realtime color, font reliability */
    .us-view-modes{display:flex;gap:5px;align-items:center}
    .us-view-mode{height:32px;border:1px solid #cbd8e8;background:#fff;border-radius:8px;padding:0 10px;font-size:9px;font-weight:800;color:#475569;cursor:pointer}
    .us-view-mode.active{background:#2563eb;border-color:#2563eb;color:#fff}
    .us-empty-binding{
        width:100%;height:100%;display:flex;align-items:center;justify-content:center;
        border:1px dashed #8fb2ed;background:rgba(234,244,255,.72);color:#2563eb;
        font-size:10px;font-weight:800;text-align:center;padding:8px;box-sizing:border-box
    }
    .us-empty-binding.media{min-height:100%}
    .us-font-dropzone{
        border:1.5px dashed #9bb8e8;border-radius:14px;background:#f7fbff;
        padding:16px 12px;text-align:center;cursor:pointer;transition:.16s
    }
    .us-font-dropzone:hover,.us-font-dropzone.dragover{border-color:#2563eb;background:#eef6ff}
    .us-font-dropzone-icon{width:38px;height:38px;border-radius:12px;background:#e6f0ff;color:#2563eb;display:grid;place-items:center;margin:0 auto 8px;font-size:18px}
    .us-font-dropzone b{display:block;font-size:11px;color:#1f2b3d}.us-font-dropzone span{font-size:9px;color:#748094}
    .us-font-upload-progress{height:4px;border-radius:999px;background:#e8edf5;overflow:hidden;margin-top:9px;display:none}
    .us-font-upload-progress>i{display:block;height:100%;width:0;background:#2563eb;transition:width .2s}
    .us-font-item{display:flex!important;align-items:center;gap:8px;cursor:pointer;padding:9px 10px!important}
    .us-font-item:hover{background:#eff6ff}
    .us-font-item .font-demo{flex:1;min-width:0;font-size:16px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .us-font-item .font-meta{font:8px Arial,sans-serif;color:#94a3b8}
    .us-font-item.active{outline:1px solid #78a5f5;background:#eff6ff}
    .us-color-live-note{font-size:8px;color:#64748b;margin-top:4px}
    .us-frame-kind-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}


    /* V1.6 STEP 5 — customer instance preview */
    .us-customer-preview-card{margin-top:10px;border:1px solid #cfe1ff;background:#f8fbff;border-radius:12px;padding:10px}
    .us-customer-preview-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px}
    .us-instance-state{font-size:8px;font-weight:850;border-radius:999px;padding:4px 7px;background:#eef2f6;color:#64748b}
    .us-instance-state.saved{background:#e8f7ee;color:#18723b}.us-instance-state.saving{background:#fff6df;color:#8a5b00}.us-instance-state.error{background:#fff0ef;color:#b42318}
    .us-customer-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px}
    .us-customer-grid .wide{grid-column:1/-1}
    .us-customer-field label{display:block;font-size:8px;font-weight:800;color:#617089;margin-bottom:4px}
    .us-customer-field input,.us-customer-field textarea,.us-customer-field select{width:100%;box-sizing:border-box;border:1px solid #d8e2ef;border-radius:8px;background:#fff;color:#1f2937;padding:7px 8px;font-size:10px;outline:none}
    .us-customer-field textarea{resize:vertical;min-height:56px}
    .us-customer-field input:focus,.us-customer-field textarea:focus,.us-customer-field select:focus{border-color:#7aa7f7;box-shadow:0 0 0 2px rgba(37,99,235,.08)}
    .us-customer-actions{display:flex;gap:6px;margin-top:9px}.us-customer-actions button{flex:1}
    .us-instance-note{font-size:8px;color:#748094;line-height:1.45;margin-top:7px}


    /* V1.6 STEP 5.1 — dedicated customer data panel */
    [data-panel-section="customer-data"]{padding-bottom:24px}
    [data-panel-section="customer-data"] .us-customer-preview-card{margin-top:0}


    /* V1.6 STEP 6 — Canva-like selection */
    .us-canvas{user-select:none}
    .us-layer.multi-selected{outline:1.5px solid #2f6fed!important;outline-offset:1px}
    .us-lasso-box{position:absolute;z-index:9998;border:1.5px solid #2f6fed;background:rgba(47,111,237,.10);pointer-events:none;box-sizing:border-box}
    .us-group-box{position:absolute;z-index:9997;border:1.5px solid #2f6fed;pointer-events:auto;box-sizing:border-box}
    .us-group-box .us-group-handle{position:absolute;width:12px;height:12px;border:2px solid #2f6fed;background:#fff;border-radius:50%;box-sizing:border-box;z-index:5;pointer-events:auto}
    .us-group-handle[data-group-handle="nw"]{left:-7px;top:-7px;cursor:nwse-resize}.us-group-handle[data-group-handle="ne"]{right:-7px;top:-7px;cursor:nesw-resize}
    .us-group-handle[data-group-handle="se"]{right:-7px;bottom:-7px;cursor:nwse-resize}.us-group-handle[data-group-handle="sw"]{left:-7px;bottom:-7px;cursor:nesw-resize}
    .us-group-badge{position:absolute;left:50%;top:-26px;transform:translateX(-50%);background:#2f6fed;color:#fff;border-radius:999px;padding:4px 8px;font-size:9px;font-weight:800;white-space:nowrap;pointer-events:none}
    .us-lock-indicator{position:absolute;right:-7px;top:-7px;width:16px;height:16px;z-index:12;pointer-events:none;border:1px solid #d8dce3;border-radius:5px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.12)}
    .us-lock-indicator::before{content:'';position:absolute;left:4px;top:2px;width:6px;height:6px;border:1.5px solid #4f5661;border-bottom:0;border-radius:5px 5px 0 0;box-sizing:border-box}
    .us-lock-indicator::after{content:'';position:absolute;left:3px;top:7px;width:8px;height:6px;border-radius:2px;background:#4f5661}
    .us-layer.inline-text-edit{outline:2px solid #2f6fed!important;z-index:9996!important}.us-layer.inline-text-edit .us-text{cursor:text!important;user-select:text!important;pointer-events:auto!important}
    .us-layer.inline-text-edit .us-selection-handle,.us-layer.inline-text-edit .us-rotate-handle,.us-layer.inline-text-edit .us-rotate-stem{display:none!important}
    .us-multi-note{font-size:9px;color:#64748b;margin-top:8px;line-height:1.45}


    /* V1.6 STEP 6.1 */
    .us-required{color:#d92d20;font-weight:900}
    .us-template-meta .us-field{margin:0}
    .us-template-meta input,.us-template-meta select{width:100%;height:38px;box-sizing:border-box;background:#fff;color:#172033;border:1px solid #cad8ea;border-radius:9px;padding:0 10px;outline:none}
    .us-template-meta input:focus,.us-template-meta select:focus{border-color:#6c9dff;box-shadow:0 0 0 3px rgba(47,111,237,.10)}
    .us-center-tools{display:grid;grid-template-columns:1fr 1fr;gap:6px}.us-center-tools #centerTextXY{grid-column:1/-1}
    .us-workspace{position:relative}
    .us-pages-toggle{position:absolute;right:12px;bottom:53px;z-index:130;height:32px;padding:0 10px;border:1px solid #b8ccef;border-radius:9px;background:#fff;color:#315b9c;font-size:9px;font-weight:850;cursor:pointer;box-shadow:0 4px 14px rgba(31,63,111,.12)}
    .us-pages-toggle:hover{background:#eef5ff;border-color:#7fa6ea}
    .us-workspace.pages-collapsed .us-pages-wrap{display:none}
    .us-static-layer.us-preview-animated{visibility:hidden}
    .us-static-layer.us-preview-animated.is-running{visibility:visible}
    @media(max-width:760px){.us-pages-toggle{right:8px;bottom:49px}}


    /* V1.6 STEP 6.2 — mobile editor */
    .us-font-current{border:1px solid #d5e2f5;background:#f5f9ff;border-radius:10px;padding:10px;margin-bottom:9px;font-size:12px;color:#315b9c;min-height:20px}
    .us-position-tools{display:flex;gap:6px;flex-wrap:wrap}
    .us-mini-tool{height:30px;padding:0 10px;border:1px solid #c9d9ef;background:#f6f9ff;border-radius:8px;color:#294f86;font-size:9px;font-weight:800;cursor:pointer}
    .us-mini-tool:hover{background:#eaf3ff;border-color:#8aace2}
    .us-desktop-cover-preview video,.us-preview-cover-media video{position:absolute;inset:0;width:100%;height:100%;display:block;max-width:none;max-height:none;transform-origin:center center}
    .us-pages-toggle{right:92px!important}
    .us-element-tab.active,.us-element-card.active,.us-tool.active,.us-transition-chip-btn.active{background:#eaf3ff!important;border-color:#9bb9e8!important;color:#264e87!important}
    @media(max-width:760px){
        body{overflow:hidden}
        .us-app{height:100dvh;min-height:100dvh}
        .us-topbar{height:52px;padding:0 8px;gap:6px}
        .us-topbar .us-brand{font-size:10px;max-width:145px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .us-topbar .us-top-left{gap:4px}
        .us-topbar .us-top-right{gap:4px}
        .us-topbar .us-btn{height:34px;padding:0 9px;font-size:9px}
        .us-topbar .us-save-status{display:none}

        .us-body{grid-template-columns:54px minmax(0,1fr)!important;height:calc(100dvh - 52px)!important}
        .us-rail{width:54px;min-width:54px;overflow-y:auto;overflow-x:hidden;padding:5px 3px}
        .us-rail-btn{min-height:48px;padding:4px 2px;font-size:7px;border-radius:9px}
        .us-rail-btn .ico{font-size:15px;margin-bottom:2px}

        .us-sidepanel{position:absolute!important;left:54px!important;top:0!important;bottom:0!important;width:min(82vw,310px)!important;z-index:700!important;box-shadow:8px 0 24px rgba(28,52,88,.12)}
        .us-body.panel-collapsed .us-sidepanel{transform:translateX(calc(-100% - 56px))!important}
        .us-sidepanel-inner{height:100%;overflow-y:auto}

        .us-main{grid-column:2!important;min-width:0;width:100%!important}
        .us-workspace{min-width:0;width:100%;overflow:hidden}
        .us-work-top{padding:5px 6px;gap:4px;overflow-x:auto;white-space:nowrap}
        .us-work-top .us-device-switch{margin-left:0}
        .us-work-top button{flex:0 0 auto;height:31px;padding:0 8px;font-size:8px}

        .us-canvas-scroll{padding:14px 8px 90px!important;overflow:auto!important}
        .us-canvas-stage{min-width:0!important}
        .us-canvas{transform-origin:top center}

        .us-properties-panel{position:absolute!important;right:0!important;top:0!important;bottom:0!important;width:min(84vw,320px)!important;z-index:720!important;box-shadow:-8px 0 24px rgba(28,52,88,.12)}
        .us-body.properties-collapsed .us-properties-panel{transform:translateX(100%)!important}

        .us-pages-wrap{padding:8px 54px 8px 8px!important;overflow-x:auto!important;max-height:96px}
        .us-pages{gap:7px}
        .us-page-card{min-width:112px!important;max-width:112px!important}
        .us-pages-toggle{right:10px!important;bottom:48px!important;height:30px!important;padding:0 8px!important}
        .us-work-bottom{height:42px;padding:0 8px;font-size:8px}
        .us-work-bottom input[type="range"]{max-width:110px}

        .us-panel-toggle,.us-properties-toggle{top:50%!important}
        .us-panel-toggle{left:54px!important}
        .us-body:not(.panel-collapsed) .us-panel-toggle{left:min(calc(54px + 82vw),364px)!important}
        .us-properties-toggle{right:0!important}
        .us-body:not(.properties-collapsed) .us-properties-toggle{right:min(84vw,320px)!important}

        .us-responsive-modal-card{width:calc(100vw - 16px)!important;height:calc(100dvh - 16px)!important;margin:8px!important;border-radius:12px!important}
        .us-preview-device.mobile,.us-preview-device.tablet,.us-preview-device.desktop{width:100%!important;max-width:100%!important}
    }


    /* V1.6 STEP 6.3 */
    .us-opening-cover-preview{position:absolute;inset:0;z-index:80;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#0f172a}
    .us-opening-cover-preview.hidden{display:none}
    .us-opening-cover-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
    .us-opening-cover-bg.video{pointer-events:none}
    .us-opening-cover-shade{position:absolute;inset:0;background:rgba(5,15,30,.22)}
    .us-opening-cover-content{position:absolute;left:50%;transform:translate(-50%,-50%);width:min(86%,720px);text-align:center;z-index:2}
    .us-opening-cover-eyebrow{font-size:14px;margin-bottom:8px;letter-spacing:.02em}
    .us-opening-cover-names{font-family:Georgia,serif;line-height:1.05;margin-bottom:24px;word-break:break-word}
    .us-opening-cover-button{border:0;border-radius:999px;padding:12px 20px;color:#fff;font-weight:800;cursor:pointer;box-shadow:0 8px 22px rgba(0,0,0,.18)}
    .us-sidepanel,.us-properties-panel{background:#F8FBFF!important}
    .us-responsive-card,.us-opening-cover-card,.us-desktop-cover-card,.us-customer-preview-card,
    .us-binding-card,.us-animation-card,.us-advanced-folder,.us-upload,.us-font-current,
    .us-page-grid-card,.us-context-card,.us-properties-panel .us-card,.us-sidepanel .us-card{
        background:#EEF5FF!important;border-color:#C9DCF6!important;
    }
    .us-element-card,.us-component-card,.us-tool,.us-btn:not(.primary),.us-device-btn,.us-view-mode,
    .us-transition-option,.us-animation-preset,.us-page-card,.us-page-grid-card{border-color:#C8D9F1}
    .us-element-card:hover,.us-component-card:hover,.us-tool:hover,.us-btn:not(.primary):hover,
    .us-device-btn:hover,.us-transition-option:hover,.us-animation-preset:hover{background:#EAF3FF!important;border-color:#8FAFE1!important}


    /* V1.6 STEP 6.4 — MOBILE LAYOUT REBUILD */
    .us-mobile-sheet-close{display:none;width:32px;height:32px;border:0;border-radius:9px;background:#EAF3FF;color:#2F5E9E;font-weight:900;cursor:pointer}
    @media(max-width:760px){
        html,body{width:100%;height:100%;margin:0;overflow:hidden!important}
        .us-wrap{position:fixed!important;inset:0!important;z-index:10000!important;width:100vw!important;height:100dvh!important;min-height:100dvh!important;background:#F3F7FD!important;overflow:hidden!important}

        .us-top{height:48px!important;min-height:48px!important;padding:6px 8px!important;display:flex!important;align-items:center!important;gap:6px!important;position:relative!important;z-index:500!important}
        .us-top-left{flex:0 0 auto!important}
        .us-top-center{display:flex!important;flex:1!important;min-width:0!important;justify-content:flex-start!important}
        .us-top-title{display:block!important;max-width:135px!important;font-size:10px!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}
        .us-top-right{gap:4px!important}
        .us-top-right .us-save-status,#undoBtn,#redoBtn{display:none!important}
        .us-top .us-icon-btn{width:34px!important;height:34px!important}
        .us-top .us-btn{height:34px!important;padding:0 9px!important;font-size:9px!important}
        #previewBtn,#saveBtn{display:inline-flex!important}

        .us-body{position:relative!important;display:block!important;width:100%!important;height:calc(100dvh - 48px)!important;min-height:0!important;overflow:hidden!important}
        .us-rail,.us-library-collapse,.us-properties-collapse{display:none!important}

        .us-workspace{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;min-width:0!important;padding:0 0 58px!important;overflow:hidden!important;background:#F2F6FC!important}
        .us-contextbar{height:40px!important;min-height:40px!important;padding:4px 7px!important;display:flex!important;justify-content:flex-start!important;gap:5px!important;overflow-x:auto!important;overflow-y:hidden!important;white-space:nowrap!important;scrollbar-width:none!important}
        .us-contextbar::-webkit-scrollbar{display:none!important}
        .us-contextbar .us-btn,.us-contextbar .us-view-mode,.us-contextbar .us-device-btn{height:30px!important;min-height:30px!important;padding:0 8px!important;font-size:8px!important;flex:0 0 auto!important}
        .us-device-switch{margin-left:0!important;flex:0 0 auto!important}

        .us-canvas-zone{position:relative!important;flex:1!important;min-height:0!important;height:calc(100% - 40px)!important;padding:14px 10px 72px!important;overflow:auto!important;display:flex!important;align-items:center!important;justify-content:flex-start!important;flex-direction:column!important;gap:10px!important}
        .us-stagebar{width:100%!important;flex:0 0 auto!important;overflow-x:auto!important}
        .us-canvas-shell{flex:0 0 auto!important;margin:0 auto!important}
        .us-canvas{transform-origin:top left!important}
        .us-add-page-main{width:min(390px,94%)!important;height:38px!important;font-size:10px!important}

        .us-pages-wrap{position:absolute!important;left:6px!important;right:6px!important;bottom:58px!important;max-height:92px!important;padding:7px 46px 7px 7px!important;background:#fff!important;border:1px solid #D7E4F5!important;border-radius:12px!important;box-shadow:0 8px 26px rgba(24,52,92,.13)!important;z-index:360!important;overflow-x:auto!important}
        .us-workspace.pages-collapsed .us-pages-wrap{display:none!important}
        .us-pages{min-height:66px!important;gap:6px!important}
        .us-page-chip{width:104px!important;height:54px!important;padding:6px 7px!important}
        .us-add-page{height:54px!important;padding:0 10px!important}
        .us-pages-toggle{display:inline-flex!important;align-items:center!important;justify-content:center!important;position:absolute!important;right:12px!important;bottom:66px!important;width:auto!important;height:32px!important;padding:0 10px!important;z-index:370!important;border-radius:9px!important;font-size:9px!important}
        .us-work-bottom{position:absolute!important;right:8px!important;bottom:62px!important;width:auto!important;height:36px!important;padding:0!important;border:0!important;background:transparent!important;z-index:350!important}
        .us-work-bottom>span,.us-work-bottom>input{display:none!important}
        .us-grid-view-btn{width:34px!important;height:34px!important;border-radius:9px!important}

        .us-library{position:absolute!important;left:6px!important;right:6px!important;top:auto!important;bottom:58px!important;width:auto!important;height:min(68dvh,620px)!important;max-height:calc(100% - 70px)!important;display:flex!important;opacity:1!important;pointer-events:auto!important;border:1px solid #D6E3F3!important;border-radius:16px 16px 10px 10px!important;background:#F8FBFF!important;box-shadow:0 -10px 34px rgba(24,52,92,.18)!important;transform:translateY(calc(100% + 72px))!important;transition:transform .22s ease!important;z-index:520!important}
        .us-library.mobile-open{transform:translateY(0)!important}
        .us-library-head{height:46px!important;min-height:46px!important;padding:0 10px 0 12px!important}
        .us-library-scroll{padding:10px!important;overflow:auto!important}
        .us-mobile-sheet-close{display:grid!important;place-items:center!important;margin-left:auto!important}

        .us-properties-panel{position:absolute!important;left:6px!important;right:6px!important;top:auto!important;bottom:58px!important;width:auto!important;height:min(70dvh,640px)!important;max-height:calc(100% - 70px)!important;display:block!important;opacity:1!important;pointer-events:auto!important;padding:12px!important;border:1px solid #D6E3F3!important;border-radius:16px 16px 10px 10px!important;background:#F8FBFF!important;box-shadow:0 -10px 34px rgba(24,52,92,.18)!important;transform:translateY(calc(100% + 72px))!important;transition:transform .22s ease!important;z-index:530!important;overflow:auto!important}
        .us-properties-panel.mobile-open{transform:translateY(0)!important}
        .us-properties-head{position:sticky!important;top:-12px!important;z-index:2!important;background:#F8FBFF!important;padding:6px 0 8px!important}
        .us-body.library-collapsed .us-library,.us-body.properties-collapsed .us-properties-panel{opacity:1!important;pointer-events:auto!important;border:1px solid #D6E3F3!important}

        .us-mobile-nav{position:absolute!important;left:0!important;right:0!important;bottom:0!important;height:58px!important;display:flex!important;align-items:stretch!important;overflow-x:auto!important;overflow-y:hidden!important;scrollbar-width:none!important;background:#fff!important;border-top:1px solid #D8E3F1!important;z-index:600!important;box-shadow:0 -4px 18px rgba(24,52,92,.08)!important}
        .us-mobile-nav::-webkit-scrollbar{display:none!important}
        .us-mobile-nav button{flex:0 0 62px!important;min-width:62px!important;height:58px!important;border:0!important;background:#fff!important;color:#52647A!important;padding:5px 3px!important;font-size:7px!important;font-weight:800!important}
        .us-mobile-nav .ico{display:block!important;font-size:15px!important;line-height:17px!important;margin-bottom:2px!important}
        .us-mobile-nav button.active{background:#EEF5FF!important;color:#2563EB!important}

        .us-transition-popover{max-width:calc(100vw - 20px)!important}
        .us-preview-overlay{z-index:12000!important}
        .us-preview-top{height:48px!important;padding:0 7px!important;gap:4px!important;overflow-x:auto!important}
        .us-preview-top b{font-size:10px!important;white-space:nowrap!important}
        .us-preview-top .us-device-btn,.us-preview-close{height:31px!important;padding:0 8px!important;font-size:8px!important;flex:0 0 auto!important}
        .us-preview-stage{padding:6px!important}
        .us-preview-device.mobile{width:min(390px,calc(100vw - 12px))!important;height:min(844px,calc(100dvh - 62px))!important}
        .us-preview-device.tablet{width:min(390px,calc(100vw - 12px))!important;height:min(844px,calc(100dvh - 62px))!important;max-width:390px!important;border-radius:10px!important}.us-preview-device.desktop{width:calc(100vw - 12px)!important;height:calc(100dvh - 62px)!important;max-width:none!important;border-radius:10px!important}
        .us-context-menu{max-width:calc(100vw - 20px)!important}
        .us-page-grid-overlay{padding:8px!important}
        .us-page-grid-panel{width:calc(100vw - 16px)!important;max-height:calc(100dvh - 16px)!important}
        .us-page-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;padding:10px!important;gap:8px!important}
    }


    /* V1.6 STEP 6.4 FINAL — CONTROL SYSTEM / MINIMAL UI */
    :root{
        --us-control-h:36px;
        --us-control-radius:9px;
        --us-border:#D6E2F2;
        --us-soft:#F5F9FF;
        --us-soft-2:#EEF5FF;
        --us-blue:#2563EB;
        --us-text:#1F2A3D;
        --us-muted:#6B7A90;
    }

    /* One consistent button system on desktop + mobile */
    .us-btn,.us-tool,.us-icon-btn,.us-device-btn,.us-view-mode,.us-mini-tool,
    .us-transition-option,.us-animation-preset,.us-grid-view-btn,.us-page-grid-close{
        box-sizing:border-box!important;
        min-height:var(--us-control-h)!important;
        height:var(--us-control-h)!important;
        border-radius:var(--us-control-radius)!important;
        padding:0 11px!important;
        font-size:10px!important;
        font-weight:750!important;
        line-height:1!important;
        box-shadow:none!important;
    }
    .us-icon-btn,.us-grid-view-btn,.us-page-grid-close{width:var(--us-control-h)!important;padding:0!important}
    .us-tool{min-height:var(--us-control-h)!important}
    .us-tool-primary,.us-btn.primary{background:var(--us-blue)!important;color:#fff!important;border-color:var(--us-blue)!important}
    .us-tool.danger,.us-btn.danger{background:#fff!important;color:#B42318!important;border-color:#E9C5C1!important}
    .us-tool:not(.danger):not(.us-tool-primary),.us-btn:not(.primary):not(.danger),.us-device-btn,.us-view-mode,.us-mini-tool{
        background:#fff!important;color:var(--us-text)!important;border:1px solid var(--us-border)!important
    }
    .us-tool:hover,.us-btn:hover,.us-device-btn:hover,.us-view-mode:hover,.us-mini-tool:hover{background:var(--us-soft)!important}
    .us-view-mode.active,.us-device-btn.active,.us-tool.active{background:#E7F0FF!important;border-color:#8EB1EC!important;color:#174D9B!important}

    /* Forms */
    .us-field{margin-bottom:10px!important}
    .us-field label{font-size:8px!important;color:#63738A!important;letter-spacing:.045em!important;margin-bottom:5px!important}
    .us-field input:not([type=checkbox]):not([type=color]):not([type=range]),
    .us-field select,.us-field textarea{
        min-height:36px!important;border:1px solid var(--us-border)!important;border-radius:9px!important;
        padding:8px 10px!important;background:#fff!important;font-size:10px!important;color:var(--us-text)!important
    }
    .us-field textarea{min-height:64px!important}

    /* Checkbox must stay a normal checkbox */
    .us-check{display:flex!important;align-items:center!important;gap:8px!important;min-height:28px!important;font-size:10px!important;color:var(--us-text)!important}
    .us-check input[type=checkbox],
    .us-properties-panel input[type=checkbox],
    .us-sidepanel input[type=checkbox],
    .us-library input[type=checkbox]{
        appearance:auto!important;-webkit-appearance:checkbox!important;
        width:15px!important;height:15px!important;min-width:15px!important;min-height:15px!important;
        padding:0!important;margin:0!important;border-radius:3px!important;box-shadow:none!important;accent-color:var(--us-blue)!important
    }

    /* Color picker must not become a giant full-width input */
    .us-field input[type=color],
    .us-properties-panel input[type=color],
    .us-sidepanel input[type=color],
    .us-library input[type=color]{
        width:46px!important;height:34px!important;min-width:46px!important;min-height:34px!important;
        padding:3px!important;border:1px solid var(--us-border)!important;border-radius:8px!important;
        background:#fff!important;cursor:pointer!important
    }
    .us-field input[type=range]{height:24px!important;padding:0!important}

    /* Softer minimalist panels */
    .us-library,.us-properties-panel{background:#FAFCFF!important}
    .us-responsive-card,.us-opening-cover-card,.us-desktop-cover-card,.us-customer-preview-card,
    .us-binding-card,.us-animation-card,.us-advanced-folder,.us-upload,.us-font-current{
        background:#F3F8FF!important;border:1px solid #D7E5F6!important;border-radius:12px!important;box-shadow:none!important
    }
    .us-library-scroll,.us-properties-panel{scrollbar-width:thin}
    .us-h{color:#58708E!important;font-size:9px!important}

    /* Cover direct adjust */
    .us-desktop-cover-preview{cursor:grab!important;touch-action:none!important;user-select:none!important}
    .us-desktop-cover-preview.is-adjusting{cursor:grabbing!important}
    .us-desktop-cover-preview img,.us-desktop-cover-preview video{pointer-events:none!important}
    .us-cover-adjust-note{margin:8px 0;padding:8px 9px;border:1px solid #D7E5F6;border-radius:9px;background:#F8FBFF;color:#65778F;font-size:9px;line-height:1.4}
    .us-cover-actions{display:grid;grid-template-columns:minmax(86px,1fr) auto auto;gap:6px;align-items:end}
    .us-cover-fit-field{margin:0!important}
    .us-cover-actions .us-tool{white-space:nowrap}

    /* Dedicated cover panel */
    [data-panel-section="cover"]>.us-h{
        font-size:13px!important;
        font-weight:850!important;
        margin-bottom:3px!important
    }
    [data-panel-section="cover"] .us-responsive-card{
        border:1px solid #d7e4fa!important;
        box-shadow:0 2px 8px rgba(37,99,235,.05)!important
    }
    [data-panel-section="cover"] .us-opening-cover-card,
    [data-panel-section="cover"] .us-desktop-cover-card{
        border-color:#b9cff5!important;
        background:#fbfdff!important
    }
    [data-panel-section="cover"] .us-opening-cover-card>.us-h,
    [data-panel-section="cover"] .us-desktop-cover-card>.us-h{
        color:#2563eb!important;
        font-weight:850!important
    }

    
    
    .us-cover-mode-tabs{display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin:0 0 10px}
    .us-cover-mode-tabs button{height:32px;border:1px solid #C8D9F1;border-radius:8px;background:#fff;color:#51657F;font-size:9px;font-weight:850;cursor:pointer}
    .us-cover-mode-tabs button.active{background:#173B6B;color:#fff;border-color:#173B6B}
    [data-cover-card][hidden]{display:none!important}

    /* Opening Cover V2 - device-safe media controls */
    .us-cover-subtitle{margin:12px 0 7px;font-size:9px;font-weight:850;letter-spacing:.08em;text-transform:uppercase;color:#58739A}
    .us-cover-device-tabs{display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin:7px 0}
    .us-cover-device-tabs button{height:30px;border:1px solid #C8D9F1;border-radius:8px;background:#fff;color:#51657F;font-size:9px;font-weight:800;cursor:pointer}
    .us-cover-device-tabs button.active{background:#2F6FED;color:#fff;border-color:#2F6FED}
    .us-opening-media-preview{position:relative;margin:7px auto 8px;overflow:hidden;border:1px solid #C8D9F1;border-radius:10px;background:#0f172a;touch-action:none;user-select:none}
    .us-opening-media-preview.mobile{width:140px;aspect-ratio:390/844}
    .us-opening-media-preview.tablet{width:190px;aspect-ratio:3/4}
    .us-opening-media-preview.desktop{width:100%;aspect-ratio:16/9}
    .us-opening-media-preview img,.us-opening-media-preview video{position:absolute;inset:0;width:100%;height:100%;display:block;pointer-events:none}
    .us-opening-media-empty{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:12px;text-align:center;color:#AFC2DB;font-size:9px}
    .us-opening-cover-preview.is-exiting{pointer-events:none}
    @keyframes usOpeningExitFade{to{opacity:0}}
    @keyframes usOpeningExitSlideUp{to{opacity:0;transform:translateY(-100%)}}
    @keyframes usOpeningExitZoom{to{opacity:0;transform:scale(1.08)}}
    @keyframes usOpeningExitBlur{to{opacity:0;filter:blur(18px)}}
    @keyframes usOpeningExitCurtain{to{transform:translateY(-100%)}}

    /* Opening cover controls */
    .us-opening-cover-button{min-height:38px!important;padding:0 18px!important;font-size:11px!important}
    .us-opening-cover-card .us-grid2{gap:7px!important}

    /* Dense desktop editor without looking cramped */
    @media(min-width:761px){
        .us-library-scroll{padding:12px!important}
        .us-properties-panel{padding:12px!important}
        .us-tools{gap:6px!important}
        .us-element-card,.us-component-card{border-radius:10px!important}
        .us-contextbar{height:46px!important;padding:0 10px!important;gap:6px!important}
    }

    /* Mobile uses the same control dimensions, only slightly tighter */
    @media(max-width:760px){
        :root{--us-control-h:34px;--us-control-radius:8px}
        .us-btn,.us-tool,.us-device-btn,.us-view-mode,.us-mini-tool{font-size:9px!important;padding:0 9px!important}
        .us-library-scroll,.us-properties-panel{padding:10px!important}
        .us-cover-actions{grid-template-columns:1fr 1fr!important}
        .us-cover-fit-field{grid-column:1/-1!important}
        .us-field input[type=color]{width:42px!important;height:32px!important;min-width:42px!important;min-height:32px!important}
        .us-check input[type=checkbox]{width:15px!important;height:15px!important}
    }


    /* STEP 6.5 — Opening Cover safe-area */
    .us-opening-cover-preview{overflow:hidden!important}
    .us-opening-cover-content{
        width:calc(100% - 32px)!important;
        max-width:720px!important;
        box-sizing:border-box!important;
        padding:0 12px!important;
        overflow:visible!important;
    }
    .us-opening-cover-eyebrow,.us-opening-cover-names{
        max-width:100%!important;
        white-space:normal!important;
        overflow-wrap:anywhere!important;
        word-break:normal!important;
    }
    .us-opening-cover-button{
        display:inline-flex!important;
        align-items:center!important;
        justify-content:center!important;
        max-width:calc(100% - 16px)!important;
        white-space:normal!important;
        overflow-wrap:anywhere!important;
    }
    .us-preview-device.mobile .us-opening-cover-content{
        width:calc(100% - 24px)!important;
        padding:0 8px!important;
    }


    /* STEP 6.5 — FINAL soft-blue editor chrome */
    .us-library,.us-properties-panel{background:#FAFCFF!important}
    .us-library-head,.us-properties-head,.us-contextbar,.us-pages-wrap,.us-work-bottom{
        background:#FFFFFF!important;
        border-color:#D8E5F5!important;
    }
    .us-responsive-card,.us-opening-cover-card,.us-desktop-cover-card,.us-customer-preview-card,
    .us-binding-card,.us-animation-card,.us-advanced-folder,.us-upload,.us-font-current,
    .us-element-card,.us-component-card,.us-page-chip,.us-page-card,.us-page-grid-card,
    .us-transition-popover,.us-context-menu{
        background:#F3F8FF!important;
        border-color:#D3E3F6!important;
        box-shadow:none!important;
    }
    .us-element-tab,.us-transition-chip-btn,.us-tool,.us-mini-tool,.us-device-btn,.us-view-mode,
    .us-animation-preset,.us-transition-option{
        background:#FFFFFF!important;
        border-color:#CBDCF2!important;
        color:#26476F!important;
        box-shadow:none!important;
    }
    .us-element-tab.active,.us-transition-chip-btn.active,.us-tool.active,.us-device-btn.active,
    .us-view-mode.active,.us-animation-preset.active,.us-transition-option.active{
        background:#E5F0FF!important;
        border-color:#8DAFE3!important;
        color:#174B94!important;
    }
    .us-element-tab:hover,.us-transition-chip-btn:hover,.us-tool:hover,.us-mini-tool:hover,
    .us-device-btn:hover,.us-view-mode:hover,.us-animation-preset:hover,.us-transition-option:hover{
        background:#EDF5FF!important;
        border-color:#9DBAE5!important;
    }


    /* STEP 6.6 — preview typography follows selected device */
    .us-opening-cover-content[data-preview-device="mobile"]{max-width:340px!important}
    .us-opening-cover-content[data-preview-device="tablet"]{max-width:560px!important}
    .us-opening-cover-content[data-preview-device="mobile"] .us-opening-cover-names{line-height:1.08!important;margin-bottom:16px!important}
    .us-opening-cover-content[data-preview-device="tablet"] .us-opening-cover-names{line-height:1.08!important;margin-bottom:20px!important}
    .us-opening-cover-content[data-preview-device="mobile"] .us-opening-cover-button{min-height:32px!important;padding:0 13px!important;border-radius:999px!important}
    .us-opening-cover-content[data-preview-device="tablet"] .us-opening-cover-button{min-height:35px!important;padding:0 15px!important}


    /* STEP 6.6 — Canvas button must remain reachable on mobile */
    @media(max-width:760px){
        .us-pages-toggle{
            display:inline-flex!important;
            position:absolute!important;
            right:10px!important;
            bottom:64px!important;
            z-index:680!important;
            visibility:visible!important;
            opacity:1!important;
            pointer-events:auto!important;
            background:#FFFFFF!important;
            border:1px solid #BFD3EE!important;
            color:#24518C!important;
            box-shadow:0 4px 14px rgba(31,63,111,.12)!important;
        }
        .us-library.mobile-open~.us-workspace .us-pages-toggle,
        .us-properties-panel.mobile-open~.us-workspace .us-pages-toggle{
            display:inline-flex!important;
        }
    }


    /* STEP 6.6 — clean mobile Preview / Save controls */
    @media(max-width:760px){
        .us-top-right{margin-left:auto!important;display:flex!important;align-items:center!important;gap:5px!important}
        #previewBtn,#saveBtn{
            height:32px!important;
            min-height:32px!important;
            border-radius:8px!important;
            padding:0 10px!important;
            font-size:9px!important;
            font-weight:800!important;
            white-space:nowrap!important;
            box-shadow:none!important;
        }
        #previewBtn{
            background:#FFFFFF!important;
            color:#244A7C!important;
            border:1px solid #C7D8EF!important;
        }
        #saveBtn{
            background:#2563EB!important;
            color:#FFFFFF!important;
            border:1px solid #2563EB!important;
        }
        @media(max-width:360px){
            .us-top-title{display:none!important}
            #previewBtn,#saveBtn{padding:0 8px!important}
        }
    }


    /* STEP 6.6 — no brown shape previews */
    .us-element-preview{background:#BFD8FF!important}
    .us-element-preview.line{background:#9BBEF4!important}
    .us-shape{background:#BFD8FF}


    /* STEP 6.6.1 — Opening Cover center control */
    .us-opening-position-row{
        display:flex;
        justify-content:center;
        margin:2px 0 10px;
    }
    .us-opening-position-row .us-mini-tool{
        width:auto!important;
        min-width:110px!important;
        height:32px!important;
        min-height:32px!important;
        padding:0 12px!important;
    }
    .us-opening-cover-content{
        left:50%;
        text-align:center!important;
    }


    /* STEP 6.6.2 — TRUE CENTER INSIDE OPENING COVER FRAME */
    .us-opening-cover-content{
        position:absolute!important;
        transform:translate(-50%,-50%)!important;
        width:min(86%,720px)!important;
        max-width:calc(100% - 32px)!important;
        box-sizing:border-box!important;
        padding:0 12px!important;
        text-align:center!important;
        overflow:visible!important;
    }
    .us-opening-cover-inner{
        display:flex!important;
        flex-direction:column!important;
        align-items:center!important;
        justify-content:center!important;
        width:100%!important;
        max-width:100%!important;
        margin:0 auto!important;
        text-align:center!important;
        transform-origin:center center!important;
    }
    .us-opening-cover-eyebrow,
    .us-opening-cover-names{
        width:100%!important;
        max-width:100%!important;
        margin-left:auto!important;
        margin-right:auto!important;
        text-align:center!important;
        white-space:normal!important;
        overflow-wrap:anywhere!important;
    }
    .us-opening-cover-button{
        align-self:center!important;
        margin-left:auto!important;
        margin-right:auto!important;
        max-width:calc(100% - 16px)!important;
    }
    .us-opening-cover-content[data-preview-device="mobile"]{
        width:calc(100% - 28px)!important;
        max-width:340px!important;
        padding:0 8px!important;
    }
    .us-opening-cover-content[data-preview-device="tablet"]{
        width:calc(100% - 40px)!important;
        max-width:560px!important;
    }


    /* V1.7 STEP 7 */
    .us-step7-tools{margin:0 0 12px;padding:10px;border:1px solid #D7E5F6;border-radius:12px;background:#F6FAFF}
    .us-align-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}
    .us-align-grid-2{grid-template-columns:repeat(2,minmax(0,1fr));margin-top:6px}
    .us-snap-guide{position:absolute;z-index:9995;pointer-events:none;background:#2F6FED;opacity:.9}
    .us-snap-guide.vertical{top:0;bottom:0;width:1px}
    .us-snap-guide.horizontal{left:0;right:0;height:1px}
    .us-layer.group-member{outline:1px dashed rgba(47,111,237,.35);outline-offset:1px}
    @media(max-width:760px){.us-step7-tools{padding:8px}.us-align-grid{gap:5px}#toggleSnap{display:inline-flex!important}}


/* V1.8 STEP 8 */
.us-step8-tools{margin:0 0 12px;padding:10px;border:1px solid #D7E5F6;border-radius:12px;background:#F6FAFF}
.us-safe-area{position:absolute;z-index:3;pointer-events:none;border:1px dashed rgba(47,111,237,.62);box-sizing:border-box}
.us-safe-area::before{content:"SAFE AREA";position:absolute;top:4px;left:6px;padding:2px 5px;border-radius:5px;background:rgba(234,244,255,.92);color:#2B5B9A;font-size:7px;font-weight:800}
.us-layer.outside-safe{outline:1px solid rgba(220,38,38,.55)!important;outline-offset:1px}
.us-preview-section,.us-preview-device,.us-opening-cover-preview,.us-preview-stack,.us-preview-scroll{max-width:100%!important;box-sizing:border-box!important;overflow-x:hidden!important}
@media(max-width:760px){.us-step8-tools{padding:8px}#toggleSafeArea{display:inline-flex!important}}


    /* CORE UI RECOVERY V3 */
    .us-contextbar .us-editor-device-switch{
        margin-left:auto;
        display:grid;
        grid-template-columns:repeat(3,minmax(72px,1fr));
        gap:5px;
    }
    .us-contextbar .us-editor-device-switch .us-device-btn{
        min-width:72px;
        height:34px;
        font-size:9px;
        font-weight:850;
    }
    .us-device-btn.active{
        background:#2563eb!important;
        border-color:#2563eb!important;
        color:#fff!important;
    }

    .us-workspace[data-editor-device="mobile"] .us-canvas-shell{
        outline:1px solid rgba(37,99,235,.16);
        box-shadow:0 12px 28px rgba(15,23,42,.10);
    }
    .us-workspace[data-editor-device="tablet"] .us-canvas-shell{
        outline:8px solid #eef3fa;
        outline-offset:8px;
        box-shadow:0 12px 32px rgba(15,23,42,.12);
    }
    .us-workspace[data-editor-device="desktop"] .us-canvas-zone{
        background:
          linear-gradient(90deg,rgba(37,99,235,.035),transparent 28%,transparent 72%,rgba(37,99,235,.035));
    }

    .us-cover-simple-note{
        margin:7px 0 10px;
        padding:8px 9px;
        border:1px solid #cfe0f7;
        border-radius:9px;
        background:#f5f9ff;
        color:#526d8d;
        font-size:9px;
        line-height:1.45;
    }
    .us-opening-media-preview{cursor:grab!important;touch-action:none!important}
    .us-opening-media-preview.is-adjusting{cursor:grabbing!important}

    /* Keep Canvas strip. It is a navigation aid, not a replacement for canvas. */
    .us-pages-wrap{display:block!important}
    .us-pages-toggle{visibility:visible!important;pointer-events:auto!important}

    @media(max-width:760px){
        .us-top{
            height:48px!important;
            min-height:48px!important;
        }
        .us-top-left{min-width:0!important;flex:1 1 auto!important}
        .us-top-title{
            max-width:118px!important;
            overflow:hidden!important;
            text-overflow:ellipsis!important;
            white-space:nowrap!important;
            font-size:9px!important;
        }
        .us-top-right{
            flex:0 0 auto!important;
            margin-left:auto!important;
            gap:4px!important;
        }
        #undoBtn,#redoBtn,.us-save-status{display:none!important}
        #previewBtn,#saveBtn{
            height:32px!important;
            min-height:32px!important;
            padding:0 9px!important;
            font-size:9px!important;
        }

        /* Device mode is the only primary canvas toolbar row on phones. */
        .us-contextbar{
            height:46px!important;
            min-height:46px!important;
            padding:5px 7px!important;
            gap:0!important;
        }
        .us-contextbar>#prevCanvas,
        .us-contextbar>#nextCanvas,
        .us-contextbar>#canvasCounter,
        .us-contextbar>#previewTransition,
        .us-contextbar>#cropDoneBtn,
        .us-contextbar>#cropResetBtn,
        .us-contextbar>.us-view-modes,
        .us-contextbar>#toggleSnap,
        .us-contextbar>#toggleSafeArea{
            display:none!important;
        }
        .us-contextbar .us-editor-device-switch{
            display:grid!important;
            width:100%!important;
            grid-template-columns:repeat(3,1fr)!important;
            gap:5px!important;
            margin:0!important;
        }
        .us-contextbar .us-editor-device-switch .us-device-btn{
            width:100%!important;
            min-width:0!important;
            height:34px!important;
            padding:0 4px!important;
            font-size:9px!important;
        }

        .us-canvas-zone{
            padding:18px 8px 18px!important;
        }
        .us-add-page-main{
            display:none!important;
        }

        /* Canvas strip stays visible on mobile and can be swiped horizontally. */
        .us-pages-wrap{
            display:block!important;
            padding:6px 8px!important;
            min-height:68px!important;
            overflow:hidden!important;
        }
        .us-pages{
            min-height:56px!important;
            overflow-x:auto!important;
            overflow-y:hidden!important;
            -webkit-overflow-scrolling:touch;
            scrollbar-width:none;
        }
        .us-pages::-webkit-scrollbar{display:none}
        .us-page-chip{width:98px!important;height:52px!important}
        .us-add-page{height:52px!important}
        .us-pages-toggle{
            right:8px!important;
            bottom:64px!important;
            height:32px!important;
            font-size:9px!important;
        }

        /* Every tool stays available; swipe the toolbar horizontally. */
        .us-mobile-nav{
            display:flex!important;
            align-items:stretch!important;
            justify-content:flex-start!important;
            gap:0!important;
            overflow-x:auto!important;
            overflow-y:hidden!important;
            -webkit-overflow-scrolling:touch!important;
            scrollbar-width:none!important;
            padding:0!important;
            height:58px!important;
        }
        .us-mobile-nav::-webkit-scrollbar{display:none}
        .us-mobile-nav button{
            flex:0 0 64px!important;
            min-width:64px!important;
            max-width:64px!important;
            height:58px!important;
            padding:5px 3px!important;
            font-size:8px!important;
            white-space:nowrap!important;
        }
        .us-mobile-nav .ico{font-size:17px!important}

        /* Sheets must stay above canvas and above bottom toolbar. */
        .us-library,.us-properties-panel{
            bottom:58px!important;
            max-height:calc(100dvh - 106px)!important;
        }
    }


    /* DEVICE FLOW STABILIZATION V4 */
    .us-canvas-zone{
        align-items:center!important;
        overflow:auto!important;
    }
    .us-device-stage{
        position:relative;
        display:flex;
        align-items:flex-start;
        justify-content:center;
        gap:18px;
        flex:0 0 auto;
        box-sizing:border-box;
        background:#e9edf3;
        border:1px solid #d9dee6;
        border-radius:14px;
        padding:24px;
        transition:width .2s ease,height .2s ease,background .2s ease;
        overflow:hidden;
    }
    .us-device-stage.mobile{
        width:calc(390px * var(--us-zoom) + 48px);
        min-height:calc(844px * var(--us-zoom) + 48px);
    }
    .us-device-stage.tablet{
        width:min(768px,calc(100vw - 120px));
        min-height:min(1024px,calc(100vh - 180px));
        padding-top:42px;
    }
    .us-device-stage.desktop{
        width:min(1180px,calc(100vw - 120px));
        min-height:min(760px,calc(100vh - 170px));
        display:grid;
        grid-template-columns:minmax(260px,56%) minmax(250px,44%);
        align-items:start;
        justify-items:center;
    }
    .us-device-stage.desktop.sticky-right{
        grid-template-columns:minmax(250px,44%) minmax(260px,56%);
    }
    .us-device-stage.desktop.sticky-right .us-device-sticky-pane{order:2}
    .us-device-stage.desktop.sticky-right .us-device-canvas-column{order:1}
    .us-device-canvas-column{
        min-width:0;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:flex-start;
    }
    .us-device-stage-label{
        height:20px;
        margin-bottom:8px;
        font-size:9px;
        font-weight:850;
        color:#596577;
        background:rgba(255,255,255,.88);
        border:1px solid #dfe4eb;
        border-radius:999px;
        padding:3px 8px;
        line-height:13px;
    }
    .us-device-sticky-pane{
        width:100%;
        height:min(660px,calc(100vh - 220px));
        border-radius:10px;
        overflow:hidden;
        box-shadow:0 10px 30px rgba(15,23,42,.14);
        background:#0f172a;
        cursor:pointer;
    }
    .us-device-sticky-pane[hidden]{display:none!important}
    .us-device-sticky-pane>*{width:100%!important;height:100%!important}

    .us-page-flow{
        width:min(430px,100%);
        display:flex;
        flex-direction:column;
        gap:18px;
        padding:8px 0 36px;
        flex:0 0 auto;
    }
    .us-page-flow-title{
        font-size:10px;
        font-weight:850;
        color:#667085;
        text-align:center;
        text-transform:uppercase;
        letter-spacing:.06em;
    }
    .us-page-flow-card{
        width:100%;
        padding:10px;
        background:#f9fafb;
        border:1px solid #dfe3e8;
        border-radius:12px;
        cursor:pointer;
        box-shadow:0 5px 18px rgba(15,23,42,.05);
    }
    .us-page-flow-card:hover{border-color:#9fc1f7;background:#fff}
    .us-page-flow-card-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
        margin-bottom:8px;
        font-size:9px;
        color:#687386;
    }
    .us-page-flow-card-head b{font-size:10px;color:#283548}
    .us-page-flow-preview{
        display:flex;
        justify-content:center;
        overflow:hidden;
        background:#eceff3;
        border-radius:8px;
        padding:10px;
        max-height:520px;
    }
    .us-page-flow-end{
        text-align:center;
        padding:12px;
        border:1px dashed #cfd6df;
        border-radius:10px;
        color:#89919c;
        font-size:9px;
        background:#f8f9fb;
    }

    @media(max-width:760px){
        .us-device-stage{
            width:100%!important;
            min-height:0!important;
            border:0!important;
            border-radius:0!important;
            background:transparent!important;
            padding:0!important;
            overflow:visible!important;
        }
        .us-device-stage.tablet,
        .us-device-stage.desktop{
            display:flex!important;
            flex-direction:column!important;
        }
        .us-device-stage.tablet:before,
        .us-device-stage.desktop:before{
            content:attr(data-device);
            display:block;
            text-transform:uppercase;
            font-size:8px;
            font-weight:850;
            color:#65748a;
        }
        .us-device-sticky-pane{display:none!important}
        .us-device-stage-label{margin-bottom:7px!important}
        .us-page-flow{
            width:100%!important;
            padding:10px 8px 86px!important;
            gap:12px!important;
        }
        .us-page-flow-preview{max-height:420px!important}
        .us-library.mobile-open,
        .us-properties-panel.mobile-open{
            display:flex!important;
            opacity:1!important;
            pointer-events:auto!important;
            transform:none!important;
            visibility:visible!important;
        }
        .us-library:not(.mobile-open),
        .us-properties-panel:not(.mobile-open){
            pointer-events:none!important;
        }
        .us-library-collapse,.us-properties-collapse{
            display:none!important;
        }
        .us-pages-wrap{display:block!important}
        .us-workspace.pages-collapsed .us-pages-wrap{display:none!important}
    }


    /* === V5 CONTINUOUS RESPONSIVE CORE === */
    .us-canvas-zone{
        flex:1!important;
        min-height:0!important;
        overflow:auto!important;
        padding:22px 28px 36px!important;
        align-items:stretch!important;
        display:block!important;
        scroll-behavior:smooth;
        overscroll-behavior:contain;
    }
    .us-editor-document{
        width:100%;
        max-width:1380px;
        margin:0 auto;
        display:block;
        min-height:100%;
    }
    .us-editor-document-main{min-width:0}
    .us-editor-document.desktop.sticky-left,
    .us-editor-document.desktop.sticky-right{
        display:grid;
        grid-template-columns:minmax(300px,56%) minmax(360px,44%);
        gap:18px;
        align-items:start;
    }
    .us-editor-document.desktop.sticky-right{
        grid-template-columns:minmax(360px,44%) minmax(300px,56%);
    }
    .us-editor-document.desktop.sticky-right .us-editor-sticky-cover{order:2}
    .us-editor-document.desktop.sticky-right .us-editor-document-main{order:1}
    .us-editor-sticky-cover{
        position:sticky;
        top:10px;
        height:calc(100dvh - 210px);
        min-height:500px;
        overflow:hidden;
        border-radius:12px;
        background:#0f172a;
        box-shadow:0 12px 30px rgba(15,23,42,.14);
    }
    .us-editor-sticky-cover[hidden]{display:none!important}
    .us-editor-sticky-cover>*{width:100%!important;height:100%!important}
    .us-device-stage-label{
        width:max-content;
        margin:0 auto 14px;
        padding:5px 10px;
        border:1px solid #dce3ec;
        background:#fff;
        border-radius:999px;
        color:#667085;
        font-size:9px;
        font-weight:850;
    }
    .us-live-pages{
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:28px;
        width:100%;
        min-width:0;
    }
    .us-live-page{
        position:relative;
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:8px;
        max-width:100%;
        padding:10px 12px 16px;
        border:1px solid transparent;
        border-radius:14px;
        transition:border-color .14s,background .14s,box-shadow .14s;
    }
    .us-live-page.active{
        border-color:#9fc3fb;
        background:rgba(234,243,255,.42);
        box-shadow:0 0 0 2px rgba(37,99,235,.06);
    }
    .us-live-page-head{
        width:100%;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        color:#707986;
        font-size:9px;
    }
    .us-live-page-head b{font-size:10px;color:#2c3748}
    .us-live-page .us-canvas-shell{position:relative;flex:0 0 auto}
    .us-live-page .us-canvas{
        position:absolute;
        left:0;top:0;
        overflow:hidden;
        transform-origin:top left;
        box-shadow:0 12px 34px rgba(23,28,38,.14);
    }
    .us-live-page.active .us-canvas{box-shadow:0 14px 38px rgba(37,99,235,.16)}
    .us-contextbar .us-editor-device-switch{margin-left:auto}
    .us-device-btn.active{background:#2563eb!important;color:#fff!important;border-color:#2563eb!important}

    /* Collapse panels only changes available workspace width, never logical canvas size. */
    .us-body.library-collapsed .us-library,
    .us-body.properties-collapsed .us-properties-panel{
        visibility:hidden!important;
    }
    .us-body:not(.library-collapsed) .us-library,
    .us-body:not(.properties-collapsed) .us-properties-panel{
        visibility:visible!important;
    }

    @media(max-width:760px){
        .us-contextbar{
            height:44px!important;
            min-height:44px!important;
            padding:5px 8px!important;
        }
        .us-contextbar>#prevCanvas,
        .us-contextbar>#nextCanvas,
        .us-contextbar>#canvasCounter,
        .us-contextbar>#previewTransition,
        .us-contextbar>#toggleSnap,
        .us-contextbar>#toggleSafeArea,
        .us-contextbar>.us-view-modes,
        .us-contextbar>.us-crop-action{
            display:none!important;
        }
        .us-contextbar .us-editor-device-switch{
            display:grid!important;
            grid-template-columns:1fr!important;
            width:100%!important;
            margin:0!important;
        }
        .us-contextbar [data-editor-device="tablet"],
        .us-contextbar [data-editor-device="desktop"]{
            display:none!important;
        }
        .us-contextbar [data-editor-device="mobile"]{
            width:100%!important;
            height:32px!important;
        }
        .us-canvas-zone{
            padding:12px 6px 96px!important;
        }
        .us-editor-document{
            display:block!important;
        }
        .us-editor-sticky-cover{display:none!important}
        .us-device-stage-label{margin-bottom:8px}
        .us-live-pages{gap:16px!important}
        .us-live-page{
            width:100%!important;
            padding:8px 2px 12px!important;
            border-radius:10px!important;
        }
        .us-live-page-head{padding:0 6px}
        .us-pages-wrap{
            display:block!important;
            overflow:hidden!important;
            min-height:64px!important;
        }
        .us-pages{overflow-x:auto!important;-webkit-overflow-scrolling:touch!important;scrollbar-width:none!important}
        .us-pages::-webkit-scrollbar{display:none}
        .us-mobile-nav{
            display:flex!important;
            overflow-x:auto!important;
            overflow-y:hidden!important;
            justify-content:flex-start!important;
            -webkit-overflow-scrolling:touch!important;
            scrollbar-width:none!important;
        }
        .us-mobile-nav::-webkit-scrollbar{display:none}
        .us-mobile-nav button{flex:0 0 64px!important;min-width:64px!important}
        .us-library,.us-properties-panel{
            left:6px!important;right:6px!important;bottom:58px!important;
            width:auto!important;
            transform:translateY(calc(100% + 72px))!important;
            visibility:visible!important;
            pointer-events:none!important;
        }
        .us-library.mobile-open,.us-properties-panel.mobile-open{
            transform:translateY(0)!important;
            pointer-events:auto!important;
        }
        .us-body.library-collapsed .us-library,
        .us-body.properties-collapsed .us-properties-panel{
            visibility:visible!important;
        }
    }


    /* MEDIA / ZOOM / CANVAS NAV FIX V2 */
    #pagesPanel[hidden]{display:none!important}
    .us-zoom-simple{
        display:flex;
        align-items:center;
        gap:5px;
        margin-right:auto;
    }
    .us-zoom-btn,.us-zoom-fit{
        height:30px;
        min-width:30px;
        padding:0 8px;
        border:1px solid #d3dae5;
        border-radius:8px;
        background:#fff;
        color:#334155;
        font-size:11px;
        font-weight:850;
        cursor:pointer;
    }
    .us-zoom-fit{font-size:9px}
    .us-zoom-btn:hover,.us-zoom-fit:hover{background:#eef5ff;border-color:#9ab7e5}
    .us-zoom-simple #zoomValue{
        min-width:40px;
        text-align:center;
        font-variant-numeric:tabular-nums;
        font-size:10px;
        font-weight:800;
        color:#526075;
    }
    @media(max-width:760px){
        .us-work-bottom>span:not(#zoomValue), #bottomCounter{display:none!important}
        .us-zoom-simple{margin-right:auto}
        .us-zoom-btn,.us-zoom-fit{height:32px;min-width:34px}
    }


    /* EDITOR INTERACTION FIX V1 */
    .us-live-pages .us-layer{animation:none!important}
    .us-live-pages .us-layer-content{animation:none}
    .us-live-pages .us-layer-content.us-editor-animation-preview{will-change:transform,opacity,filter}


    /* ANIMATION CORE FIX V1 */
    .us-animation-scope-card{margin-bottom:10px}
    .us-animation-scope-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:7px}
    .us-animation-scope-head b{font-size:10px;text-transform:uppercase;letter-spacing:.06em}
    .us-animation-scope-head span{font-size:9px;color:#718096;text-align:right}
    .us-canvas-animation-shell{position:relative;flex:0 0 auto;transform-origin:center center;pointer-events:auto}
    .us-canvas-animation-shell.us-editor-canvas-animation-preview{will-change:transform,opacity,filter}
    .us-live-pages .us-layer{animation:none!important}
    .us-live-pages .us-layer-content{animation:none}
    .us-live-pages .us-layer-content.us-editor-animation-preview{will-change:transform,opacity,filter}


    @keyframes usFocusIn{from{opacity:0;filter:blur(12px) brightness(1.12)}to{opacity:1;filter:blur(0) brightness(1)}}
    @keyframes usWipeUp{from{opacity:.2;clip-path:inset(100% 0 0 0)}to{opacity:1;clip-path:inset(0 0 0 0)}}
    @keyframes usWipeLeft{from{opacity:.2;clip-path:inset(0 0 0 100%)}to{opacity:1;clip-path:inset(0 0 0 0)}}
    @keyframes usFlash{0%,100%{opacity:1}25%{opacity:.2}50%{opacity:1}75%{opacity:.45}}
    @keyframes usFlicker{0%,18%,22%,62%,64%,100%{opacity:1}20%,63%{opacity:.28}40%{opacity:.72}}
    @keyframes usBreathe{0%,100%{opacity:1;filter:brightness(1)}50%{opacity:.78;filter:brightness(1.08)}}
    @keyframes usGlowPulse{0%,100%{filter:brightness(1) drop-shadow(0 0 0 rgba(255,255,255,0))}50%{filter:brightness(1.12) drop-shadow(0 0 12px rgba(255,255,255,.55))}}
    @keyframes pageFadeSoft{from{opacity:.25}to{opacity:1}}
    @keyframes pageFocusIn{from{opacity:.15;filter:blur(18px)}to{opacity:1;filter:blur(0)}}
    @keyframes pageWipeUp{from{opacity:.35;clip-path:inset(100% 0 0 0)}to{opacity:1;clip-path:inset(0 0 0 0)}}
    @keyframes pageWipeLeft{from{opacity:.35;clip-path:inset(0 0 0 100%)}to{opacity:1;clip-path:inset(0 0 0 0)}}


    /* SAFE EDITOR CONTROLS V1 */
    .us-animation-accordion{
        border:1px solid #dfe5ee;border-radius:11px;background:#fff;margin-bottom:10px;overflow:hidden
    }
    .us-animation-accordion>summary{
        list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;
        padding:10px 11px;background:#f8fafc;font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.05em
    }
    .us-animation-accordion>summary::-webkit-details-marker{display:none}
    .us-animation-accordion>summary::after{content:'⌄';font-size:14px;transition:transform .16s ease}
    .us-animation-accordion:not([open])>summary::after{transform:rotate(-90deg)}
    .us-animation-accordion>summary small{font-size:9px;font-weight:600;text-transform:none;letter-spacing:0;color:#718096;margin-left:auto}
    .us-animation-accordion-body{padding:10px}
    .us-properties-role{font-size:9px;font-weight:800;padding:3px 7px;border-radius:999px;background:#eef4ff;color:#2563eb;margin-left:6px}
    .studio-role-premium{--us-props:270px}
    .studio-role-premium .us-admin-template-properties{display:none!important}
    .studio-role-premium .us-properties-panel{background:#fff}
    .studio-role-admin .us-properties-panel{background:#fff}
    .us-element-loop-control{margin-top:8px}


    .us-preview-device.desktop .us-preview-scroll{
        width:390px;
        min-width:390px;
        max-width:390px;
        justify-self:end;
        overflow-y:auto;
        overflow-x:hidden;
    }
    .us-preview-device.desktop .us-preview-section,
    .us-preview-device.desktop .us-preview-page-host,
    .us-preview-device.tablet .us-preview-section,
    .us-preview-device.tablet .us-preview-page-host{
        width:390px!important;
        max-width:390px!important;
        margin:0!important;
    }
    .us-preview-device.tablet .us-preview-mobile-stack{
        width:390px!important;
        max-width:390px!important;
        margin:0!important;
    }


    .us-live-page{
        width:max-content;
        min-width:0;
        box-sizing:border-box;
        isolation:isolate;
        contain:layout paint;
    }
    .us-live-page-head{
        position:relative;
        z-index:2;
        flex:0 0 auto;
        min-height:18px;
        box-sizing:border-box;
    }
    .us-canvas-animation-shell{
        display:block;
        position:relative;
        flex:0 0 auto;
        box-sizing:content-box;
        overflow:visible;
        z-index:1;
    }
    .us-canvas-animation-shell>.us-canvas-shell{
        display:block;
        position:relative;
        margin:0!important;
    }
    .us-live-page + .us-live-page{
        margin-top:12px;
    }
    .us-editor-document-main{
        min-width:0;
        overflow:visible;
    }


    /* CANVAS WORKFLOW SIMPLIFY V1 */
    .us-live-page-head{min-height:32px;padding:0 2px 6px}
    .us-live-page-title-wrap{display:flex;align-items:center;gap:5px;min-width:0;flex:1}
    .us-live-page-number{flex:0 0 auto;font-size:10px;font-weight:850;color:#2c3748}
    .us-inline-page-name{
        width:min(210px,42vw);min-width:72px;height:26px;border:1px solid transparent;border-radius:6px;
        padding:0 6px;background:transparent;color:#2c3748;font:inherit;font-size:10px;font-weight:800;outline:none
    }
    .us-inline-page-name:hover{border-color:#d6deea;background:#fff}
    .us-inline-page-name:focus{border-color:#8db5ff;background:#fff;box-shadow:0 0 0 2px rgba(37,99,235,.08)}
    .us-inline-page-name::placeholder{color:#98a3b2;font-weight:650}
    .us-live-page-head-actions{display:flex;align-items:center;gap:5px;flex:0 0 auto}
    .us-live-page-device{font-size:9px;color:#7a8493;margin-right:2px}
    .us-live-page-action{
        width:25px;height:25px;padding:0;display:grid;place-items:center;border:1px solid #cfd8e6;border-radius:7px;
        background:#fff;color:#334155;font-size:16px;line-height:1;font-weight:800;cursor:pointer
    }
    .us-live-page-action:hover{background:#edf4ff;border-color:#8fb3ef;color:#1d4ed8}
    .us-live-page-action.delete:hover{background:#fff1f1;border-color:#f5a3a3;color:#b91c1c}
    .us-live-page-action:disabled{opacity:.35;cursor:not-allowed}
    @media(max-width:760px){
        .us-inline-page-name{width:130px}
        .us-live-page-device{display:none}
        .us-live-page-action{width:28px;height:28px}
    }


    /* CANVAS HEADER COMPACT V1 */
    .us-dashboard-back{
        height:34px;
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:0 11px;
        border:1px solid #d7dfeb;
        border-radius:9px;
        background:#fff;
        color:#24344a;
        font-size:10px;
        font-weight:800;
        cursor:pointer;
        white-space:nowrap;
    }
    .us-dashboard-back:hover{
        background:#f3f7fc;
        border-color:#b9c8dc;
    }
    .us-dashboard-arrow{
        font-size:9px;
        line-height:1;
    }

    .us-live-page-head{
        min-height:22px!important;
        padding:0 1px 3px!important;
        gap:5px!important;
    }
    .us-live-page-title-wrap{
        gap:3px!important;
    }
    .us-live-page-number{
        font-size:8px!important;
        font-weight:800!important;
    }
    .us-inline-page-name{
        width:min(150px,30vw)!important;
        min-width:54px!important;
        height:20px!important;
        padding:0 4px!important;
        border-radius:5px!important;
        font-size:8px!important;
        font-weight:750!important;
        line-height:20px!important;
    }
    .us-live-page-head-actions{
        gap:3px!important;
    }
    .us-live-page-device{
        display:none!important;
    }
    .us-live-page-action{
        width:21px!important;
        height:21px!important;
        border-radius:6px!important;
        font-size:13px!important;
    }

    @media(max-width:760px){
        .us-dashboard-back{
            height:32px;
            padding:0 9px;
            font-size:9px;
        }
        .us-inline-page-name{
            width:104px!important;
        }
        .us-live-page-action{
            width:23px!important;
            height:23px!important;
        }
    }


    /* CANVAS NAME MINI V1 */
    .us-live-page-head{
        min-height:24px!important;
        padding:0 0 4px!important;
    }
    .us-live-page-title-wrap{
        flex:0 1 auto!important;
        gap:3px!important;
    }
    .us-inline-page-name{
        width:118px!important;
        min-width:70px!important;
        max-width:130px!important;
        height:22px!important;
        min-height:22px!important;
        padding:0 5px!important;
        margin:0!important;
        border:1px solid #d8e0ec!important;
        border-radius:6px!important;
        background:#fff!important;
        font-size:9px!important;
        font-weight:750!important;
        line-height:20px!important;
        box-sizing:border-box!important;
    }
    .us-inline-page-name:focus{
        border-color:#8db5ff!important;
        box-shadow:0 0 0 2px rgba(37,99,235,.08)!important;
    }
    .us-live-page-head-actions{
        margin-left:auto!important;
    }
    .us-live-page-action{
        width:23px!important;
        height:23px!important;
        min-width:23px!important;
        min-height:23px!important;
        padding:0!important;
        font-size:14px!important;
    }

    @media(max-width:760px){
        .us-inline-page-name{
            width:102px!important;
            max-width:110px!important;
            height:21px!important;
            min-height:21px!important;
            font-size:8px!important;
        }
        .us-live-page-action{
            width:22px!important;
            height:22px!important;
            min-width:22px!important;
            min-height:22px!important;
        }
    }

    .us-customer-gallery-fields{display:contents}

    /* GRID CELL INTERACTION V1 */
    .us-layer[data-id] .us-media-cell{
        cursor:pointer;
        touch-action:manipulation;
    }
    .us-layer.crop-active .us-media-cell{
        touch-action:none;
    }
    .us-media-cell.selected-cell{
        outline:2px solid #2F6FED!important;
        outline-offset:-2px;
    }
    .us-cell-touch-delete{
        position:absolute;
        top:7px;
        right:7px;
        z-index:12;
        height:28px;
        padding:0 10px;
        border:0;
        border-radius:999px;
        background:rgba(28,28,30,.88);
        color:#fff;
        font-size:10px;
        font-weight:800;
        line-height:28px;
        box-shadow:0 3px 12px rgba(0,0,0,.24);
        cursor:pointer;
        -webkit-tap-highlight-color:transparent;
    }
    .us-cell-touch-delete:active{transform:scale(.96)}
    @media(pointer:fine){
        .us-cell-touch-delete{display:none!important}
    }


    /* CANVA MEDIA BEHAVIOR V1 */
    .us-cell-context-menu{width:238px}
    .us-cell-context-menu button small{
        margin-left:auto;
        color:#8a9099;
        font-size:9px;
        font-weight:600;
    }
    .us-cell-context-menu button:disabled{
        opacity:.38;
        cursor:not-allowed;
        background:transparent!important;
    }


    /* GRID CROP CANVA V2 */
    .us-layer.crop-active{
        outline:none!important;
    }
    .us-layer.crop-active>.us-layer-content{
        outline:2px solid #2563eb!important;
        outline-offset:2px!important;
    }
    .us-layer.crop-active .us-media-cell{
        overflow:hidden!important;
        background:#eee8e1;
        cursor:move;
    }
    .us-layer.crop-active .us-media-cell.selected-cell{
        outline:2px solid #2563eb!important;
        outline-offset:-2px!important;
    }

    /* Crop is direct-manipulation only. No floating buttons / badge / hint. */
    .us-crop-toolbar,
    .us-crop-badge,
    .us-crop-hint{
        display:none!important;
    }

    /* Keep image painting clipped strictly by its frame/grid cell. */
    .us-frame-inner,
    .us-grid-inner,
    .us-media-cell{
        overflow:hidden!important;
    }


    /* GRID CROP CANVA V3 */
    .us-layer.crop-active{
        overflow:visible!important;
    }
    .us-layer.crop-active>.us-layer-content{
        position:absolute!important;
        inset:0!important;
        width:100%!important;
        height:100%!important;
        z-index:3;
        overflow:hidden!important;
    }
    .us-crop-source-ghost{
        position:absolute;
        z-index:1;
        pointer-events:none;
        user-select:none;
        opacity:.34;
        filter:grayscale(1) brightness(.72);
        object-fit:fill;
        max-width:none!important;
        max-height:none!important;
        border:1px solid rgba(37,99,235,.35);
    }
    .us-layer.crop-active .us-selection-handle,
    .us-layer.crop-active .us-rotate-stem,
    .us-layer.crop-active .us-rotate-handle{
        z-index:6;
    }


    /* GRID CROP CANVA V3 RECOVERY */
    .us-crop-source-ghost{
        pointer-events:none!important;
    }
    .us-layer.crop-active{
        cursor:move;
    }


    /* GRID CROP CANVA V4 */
    .us-layer.crop-active{
        overflow:visible!important;
    }
    .us-crop-preview-wrap{
        position:absolute;
        z-index:1;
        overflow:hidden;
        pointer-events:none!important;
        border-radius:4px;
    }
    .us-crop-source-ghost{
        position:absolute;
        z-index:1;
        pointer-events:none!important;
        user-select:none;
        opacity:.42;
        filter:grayscale(1) brightness(.66);
        object-fit:fill;
        max-width:none!important;
        max-height:none!important;
    }
    .us-crop-window-mask{
        position:absolute;
        z-index:2;
        pointer-events:none!important;
        border:2px solid #2563eb;
        box-shadow:0 0 0 9999px rgba(62,62,62,.18);
        background:transparent;
    }
    .us-layer.crop-active>.us-layer-content{
        position:relative;
        z-index:3;
    }


    /* GRID CROP CANVA V5 — prevent crop content collapse */
    .us-layer.crop-active .us-frame-inner,
    .us-layer.crop-active .us-grid-inner{
        width:100%!important;
        height:100%!important;
        min-width:100%!important;
        min-height:100%!important;
    }
    .us-layer.crop-active .us-media-cell{
        min-width:0!important;
        min-height:0!important;
    }
    .us-layer.crop-active .us-crop-preview-wrap{
        pointer-events:none!important;
    }




    /* GRID CROP CANVA V7 — unified explicit crop geometry */
    .us-media-cell{position:relative;overflow:hidden!important}
    .us-media-cell>[data-cell-media-img]{
        position:absolute!important;
        left:50%!important;
        top:50%!important;
        min-width:0!important;
        min-height:0!important;
        max-width:none!important;
        max-height:none!important;
        object-fit:fill!important;
        pointer-events:none!important;
        user-select:none!important;
        will-change:width,height,transform;
    }


    /* DEVICE MEDIA REHYDRATE V1 */
    .us-media-cell>[data-cell-media-img]{
        opacity:1!important;
    }


    /* MEDIA NO FLICKER V1 */
    .us-media-cell>[data-cell-media-img]{
        transition:none!important;
        animation:none!important;
        backface-visibility:hidden;
    }


    /* PREMIUM GALLERY MANAGER V2 SAFE */
    .us-premium-gallery{margin-top:14px;padding-top:12px;border-top:1px solid #e5eaf1}
    .us-premium-gallery-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:8px}
    .us-premium-gallery-head b{display:block;font-size:11px;color:#111827}
    .us-premium-gallery-head span:not(.us-gallery-count){display:block;margin-top:2px;font-size:9px;line-height:1.35;color:#7a8493}
    .us-gallery-count{flex:none;padding:3px 7px;border-radius:999px;background:#eef4ff;color:#2563eb;font-size:9px;font-weight:800}
    .us-premium-gallery-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:7px}
    .us-premium-gallery-item{position:relative;aspect-ratio:1/1;border:1px solid #dce3ed;border-radius:9px;overflow:hidden;background:#f5f7fa;cursor:grab}
    .us-premium-gallery-item.dragging{opacity:.45}
    .us-premium-gallery-item.drag-over{outline:2px solid #2563eb;outline-offset:1px}
    .us-premium-gallery-item img{width:100%;height:100%;display:block;object-fit:cover}
    .us-premium-gallery-order{position:absolute;left:5px;top:5px;min-width:20px;height:20px;padding:0 5px;display:flex;align-items:center;justify-content:center;border-radius:999px;background:rgba(17,24,39,.76);color:#fff;font-size:8px;font-weight:800}
    .us-premium-gallery-remove{position:absolute;right:5px;top:5px;width:22px;height:22px;border:0;border-radius:50%;background:rgba(17,24,39,.8);color:#fff;font-size:14px;line-height:22px;cursor:pointer}
    .us-premium-gallery-mobile-actions{position:absolute;left:5px;right:5px;bottom:5px;display:flex;gap:4px}
    .us-premium-gallery-mobile-actions button{flex:1;height:22px;border:0;border-radius:5px;background:rgba(255,255,255,.92);color:#111827;font-size:9px;font-weight:800;cursor:pointer}
    .us-premium-gallery-mobile-actions button:disabled{opacity:.38}
    .us-premium-gallery-picker{margin-top:8px;padding:8px;border:1px solid #e3e8ef;border-radius:9px;background:#f8fafc}
    .us-premium-gallery-assets{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px;margin-top:7px;max-height:220px;overflow:auto}
    .us-premium-gallery-asset{position:relative;aspect-ratio:1/1;border:1px solid #dbe2eb;border-radius:7px;padding:0;overflow:hidden;background:#fff;cursor:pointer}
    .us-premium-gallery-asset img{width:100%;height:100%;object-fit:cover;display:block}
    .us-premium-gallery-asset.added::after{content:'✓';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(37,99,235,.58);color:#fff;font-size:20px;font-weight:900}
    .us-premium-gallery-asset.unsupported{opacity:.42;cursor:not-allowed}
    .us-premium-gallery-state{min-height:16px;margin-top:6px;font-size:9px;color:#7b8491}
    .us-premium-gallery-state.saving{color:#b7791f}.us-premium-gallery-state.saved{color:#15803d}.us-premium-gallery-state.error{color:#dc2626}
    @media(max-width:760px){
        .us-premium-gallery-list{grid-template-columns:repeat(3,minmax(0,1fr))}
        .us-premium-gallery-assets{grid-template-columns:repeat(3,minmax(0,1fr))}
        .us-premium-gallery-item{cursor:default}
    }


    /* GALLERY DEDICATED TAB V1 */
    .us-premium-gallery-standalone{
        margin-top:12px!important;
        padding-top:0!important;
        border-top:0!important;
    }
    .us-premium-gallery-panel-head{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:10px;
    }
    .us-premium-gallery-panel-head .us-h{margin-bottom:2px}
    [data-panel-section="gallery"]{
        padding-bottom:24px;
    }


    /* MEDIA LIBRARY + VIDEO STABILITY V1 */
    .us-asset{position:relative}
    .us-asset-media{width:100%;height:100%;position:relative;overflow:hidden;border-radius:inherit}
    .us-asset-delete{
        position:absolute;
        top:4px;
        right:4px;
        z-index:8;
        width:20px;
        height:20px;
        padding:0;
        display:flex;
        align-items:center;
        justify-content:center;
        border:0;
        border-radius:50%;
        background:rgba(20,24,32,.82);
        color:#fff;
        font-size:14px;
        font-weight:800;
        line-height:20px;
        cursor:pointer;
        box-shadow:0 2px 7px rgba(0,0,0,.18);
    }
    .us-asset-delete:hover{background:#b42318}
    .us-video-library-placeholder,
    .us-video-editor-placeholder{
        width:100%;
        height:100%;
        min-height:56px;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:3px;
        background:linear-gradient(145deg,#202631,#11151c);
        color:#fff;
    }
    .us-video-library-placeholder>span,
    .us-video-playmark{font-size:18px;opacity:.9}
    .us-video-library-placeholder small,
    .us-video-editor-placeholder small{font-size:8px;opacity:.7}
    .us-video-editor-shell{
        position:absolute;
        inset:0;
        overflow:hidden;
        background:#151a22;
    }
    .us-video-editor-shell .us-video-poster{
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
        pointer-events:none;
        animation:none!important;
        transition:none!important;
    }
    .us-video-editor-badge{
        position:absolute;
        left:5px;
        bottom:5px;
        z-index:2;
        padding:2px 5px;
        border-radius:4px;
        background:rgba(0,0,0,.64);
        color:#fff;
        font-size:7px;
        font-weight:800;
        pointer-events:none;
    }
    .us-premium-gallery-mobile-actions{display:none!important}


    /* VIDEO HQ PREVIEW PLAYBACK V1 */
    .us-responsive-preview-video{
        width:100%!important;
        height:100%!important;
        display:block!important;
        object-fit:cover!important;
        background:#0f172a;
        image-rendering:auto;
        filter:none!important;
        opacity:1!important;
    }
    .us-preview-device video.us-responsive-preview-video{
        pointer-events:auto!important;
    }

</style>


@if($isInstanceMode)
<style>
.us-template-meta,.us-admin-template-properties{display:none!important}
    #openCustomerDataPanel{display:block!important}
    #customerGalleryFields,.us-customer-actions{display:none!important}
</style>
@endif

{{-- UNDANGANTA_STUDIO_ROLE_UX_SEPARATION_V1 --}}
@if($isInstanceMode)
<style>
    /* Customer Premium/Royal: one clear editing model. */
    .us-view-modes{display:none!important}

    /* Legacy right inspector is Admin-only. Customer uses contextual left tools. */
    #toggleProperties,
    .us-properties-panel,
    #mobileProps{display:none!important}

    .us-body{--us-props:0px!important}

    /* Customer gets one explicit source-of-truth menu for invitation data. */
    .us-rail [data-panel-target="template"],
    .us-mobile-nav [data-panel-target="template"]{display:none!important}

    .us-rail [data-panel-target="customer-data"]{display:flex!important}
    .us-mobile-nav [data-panel-target="customer-data"]{display:flex!important}

    /* Couple identity belongs to Data Undangan, not Cover. */
    .us-field:has(#openingCoverNames),
    .us-check:has(#openingCoverBindNames){display:none!important}

    .us-role-data-note{
        margin:7px 0 9px;
        padding:7px 8px;
        border:1px solid #d8e5f5;
        border-radius:8px;
        background:#f7fbff;
        color:#58708e;
        font-size:9px;
        line-height:1.45
    }
</style>
@endif

{{-- UNDANGANTA_STUDIO_NAV_STABILIZATION_V1 --}}
@if($isInstanceMode)
<style>
    /*
     * Customer Premium/Royal navigation only.
     * CSS visual ordering only:
     * no DOM movement, no JS rewiring, no panel merging.
     */
    .us-rail [data-panel-target="cover"],
    .us-mobile-nav [data-panel-target="cover"]{order:10}

    .us-rail [data-panel-target="customer-data"],
    .us-mobile-nav [data-panel-target="customer-data"]{
        order:20!important;
        display:flex!important
    }

    .us-rail [data-panel-target="components"],
    .us-mobile-nav [data-panel-target="components"]{order:30}

    .us-rail [data-panel-target="text"],
    .us-mobile-nav [data-panel-target="text"]{order:40}

    .us-rail [data-panel-target="elements"],
    .us-mobile-nav [data-panel-target="elements"]{order:50}

    .us-rail [data-panel-target="uploads"],
    .us-mobile-nav [data-panel-target="uploads"]{order:60}

    .us-rail [data-panel-target="gallery"],
    .us-mobile-nav [data-panel-target="gallery"]{order:70}

    .us-rail [data-panel-target="fonts"],
    .us-mobile-nav [data-panel-target="fonts"]{order:80}

    .us-rail [data-panel-target="animation"],
    .us-mobile-nav [data-panel-target="animation"]{order:90}

    .us-rail [data-panel-target="layers"],
    .us-mobile-nav [data-panel-target="layers"]{order:100}

    .us-rail .us-rail-spacer{order:999}

    .us-rail [data-panel-target="template"],
    .us-mobile-nav [data-panel-target="template"]{display:none!important}

    /* UNDANGANTA_DELETE_GUARD_GUIDED_FOCUS_V1 */
    .us-delete-guard-backdrop{
        position:fixed;inset:0;z-index:2147483000;
        display:flex;align-items:center;justify-content:center;
        padding:20px;
        background:rgba(42,34,34,.20);
        backdrop-filter:blur(2px);
        -webkit-backdrop-filter:blur(2px);
    }
    .us-delete-guard-card{
        width:min(420px,calc(100vw - 32px));
        background:#fffaf9;
        border:1px solid #e7c8c5;
        border-radius:14px;
        box-shadow:0 18px 48px rgba(73,45,45,.12);
        padding:18px;
        color:#574a49;
    }
    .us-delete-guard-eyebrow{
        margin:0 0 6px;
        color:#8f3f3f;
        font-size:11px;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
    }
    .us-delete-guard-title{
        margin:0 0 7px;
        color:#713f3f;
        font-size:16px;
        line-height:1.3;
        font-weight:800;
    }
    .us-delete-guard-message{
        margin:0;
        color:#625453;
        font-size:13px;
        line-height:1.55;
    }
    .us-delete-guard-actions{
        display:flex;justify-content:flex-end;gap:8px;
        margin-top:16px;
    }
    .us-delete-guard-btn{
        min-height:36px;
        padding:0 12px;
        border-radius:9px;
        border:1px solid #ddcecc;
        background:#fff;
        color:#5e5150;
        font-size:12px;
        font-weight:750;
        cursor:pointer;
    }
    .us-delete-guard-btn:hover{background:#fbf7f6}
    .us-delete-guard-btn.primary{
        background:#b65c5c;
        border-color:#b65c5c;
        color:#fff;
    }
    .us-delete-guard-btn.primary:hover{
        background:#a95050;
        border-color:#a95050;
    }

    @keyframes usGuidedProblemPulseV1{
        0%,100%{
            outline-color:rgba(201,107,107,0);
            box-shadow:0 0 0 0 rgba(182,92,92,0);
        }
        22%{
            outline-color:#c96b6b;
            box-shadow:0 0 0 4px rgba(182,92,92,.15);
        }
        50%{
            outline-color:rgba(201,107,107,.55);
            box-shadow:0 0 0 7px rgba(182,92,92,.08);
        }
        72%{
            outline-color:#c96b6b;
            box-shadow:0 0 0 4px rgba(182,92,92,.14);
        }
    }
    .us-guided-problem-pulse{
        outline:2px solid #c96b6b!important;
        outline-offset:3px!important;
        animation:usGuidedProblemPulseV1 .62s ease-in-out 3!important;
        position:relative;
        z-index:2147482000!important;
    }
    @media (prefers-reduced-motion:reduce){
        .us-guided-problem-pulse{animation:none!important}
    }

    /* UNDANGANTA_STUDIO_CUSTOMER_UX_CLARITY_V1_SAFE */
    .studio-role-premium .us-customer-section-heading,
    .studio-role-royal .us-customer-section-heading{
        grid-column:1/-1;
        margin:9px 0 0;
        padding:8px 0 2px;
        border-top:1px solid rgba(15,23,42,.08);
        color:#6b7280;
        font-size:9.5px;
        line-height:1.2;
        font-weight:800;
        letter-spacing:.075em;
        text-transform:uppercase;
    }
    .studio-role-premium .us-customer-section-heading.first,
    .studio-role-royal .us-customer-section-heading.first{
        margin-top:0;
        padding-top:0;
        border-top:0;
    }
    .studio-role-premium .us-customer-field-note,
    .studio-role-royal .us-customer-field-note{
        display:block;
        margin-top:4px;
        color:#8a919c;
        font-size:10px;
        line-height:1.38;
        font-weight:500;
    }
    .studio-role-premium .us-customer-field[data-us-customer-hidden-clarity-v1="1"],
    .studio-role-royal .us-customer-field[data-us-customer-hidden-clarity-v1="1"]{
        display:none!important;
    }
    .studio-role-premium .us-customer-grid,
    .studio-role-royal .us-customer-grid{
        align-content:start;
    }

    /* UNDANGANTA_STUDIO_DELETE_GUARD_SCOPE_FIX_V2_SAFE */
    .us-dg2-overlay{
        position:fixed;inset:0;z-index:2147482500;
        display:flex;align-items:center;justify-content:center;
        padding:20px;background:rgba(15,23,42,.28);
    }
    .us-dg2-overlay[hidden]{display:none!important}
    .us-dg2-dialog{
        width:min(430px,calc(100vw - 32px));
        border:1px solid #E7C8C5;border-radius:14px;background:#FFFAF9;
        padding:18px;box-shadow:0 16px 42px rgba(15,23,42,.16);
        color:#713F3F;
    }
    .us-dg2-title{margin:0 0 7px;font-size:16px;line-height:1.3;font-weight:800;color:#713F3F}
    .us-dg2-text{margin:0;color:#765c5c;font-size:13px;line-height:1.55}
    .us-dg2-location{
        margin-top:11px;padding:9px 10px;border:1px solid #E7C8C5;
        border-radius:9px;background:#fff;color:#713F3F;font-size:12px;line-height:1.45;
    }
    .us-dg2-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:15px}
    .us-dg2-btn{
        appearance:none;border:1px solid #E7C8C5;border-radius:9px;background:#fff;
        color:#713F3F;padding:8px 12px;font:700 12px/1.2 inherit;cursor:pointer;
    }
    .us-dg2-btn.primary{border-color:#B65C5C;background:#B65C5C;color:#fff}
    .us-dg2-btn:focus-visible{outline:2px solid #C96B6B;outline-offset:2px}
    @keyframes usDg2Pulse{
        0%,100%{box-shadow:0 0 0 0 rgba(182,92,92,0);outline-color:rgba(201,107,107,0)}
        45%{box-shadow:0 0 0 7px rgba(182,92,92,.15);outline-color:#C96B6B}
    }
    .us-dg2-pulse{
        outline:2px solid #C96B6B!important;outline-offset:3px!important;
        animation:usDg2Pulse .48s ease-in-out 3!important;
    }

    /* UNDANGANTA_STUDIO_SOFT_UI_V1_SAFE */
    /* Visual-only polish. No layout architecture, data, canvas, crop, or renderer logic changes. */
    .us-wrap.studio-role-premium,
    .us-wrap.studio-role-royal{
        --us-soft-bg:#F5F7FA;
        --us-soft-surface:#FFFFFF;
        --us-soft-surface-2:#FAFBFC;
        --us-soft-border:#E2E7EE;
        --us-soft-border-strong:#D5DCE6;
        --us-soft-text:#202938;
        --us-soft-muted:#6F7A8A;
        --us-soft-primary:#2F6FED;
        --us-soft-primary-soft:#EEF4FF;
        --us-soft-shadow:0 1px 2px rgba(15,23,42,.035),0 8px 24px rgba(15,23,42,.035);
        color:var(--us-soft-text);
    }

    .us-wrap.studio-role-premium .us-top,
    .us-wrap.studio-role-royal .us-top{
        background:#FFFFFF!important;
        border-bottom:1px solid var(--us-soft-border)!important;
        box-shadow:0 1px 0 rgba(15,23,42,.02)!important;
    }

    .us-wrap.studio-role-premium .us-body,
    .us-wrap.studio-role-royal .us-body,
    .us-wrap.studio-role-premium .us-workspace,
    .us-wrap.studio-role-royal .us-workspace{
        background:var(--us-soft-bg)!important;
    }

    .us-wrap.studio-role-premium .us-rail,
    .us-wrap.studio-role-royal .us-rail{
        background:#FFFFFF!important;
        border-right:1px solid var(--us-soft-border)!important;
    }

    .us-wrap.studio-role-premium .us-rail-btn,
    .us-wrap.studio-role-royal .us-rail-btn{
        border-radius:12px!important;
        color:#5B6676!important;
        transition:background-color .16s ease,color .16s ease,box-shadow .16s ease,transform .16s ease!important;
    }
    .us-wrap.studio-role-premium .us-rail-btn:hover,
    .us-wrap.studio-role-royal .us-rail-btn:hover{
        background:#F6F8FB!important;
        color:#273244!important;
        transform:translateY(-1px);
    }
    .us-wrap.studio-role-premium .us-rail-btn.active,
    .us-wrap.studio-role-royal .us-rail-btn.active{
        background:var(--us-soft-primary-soft)!important;
        color:#245ECF!important;
        box-shadow:inset 0 0 0 1px #D9E5FF!important;
    }
    .us-wrap.studio-role-premium .us-rail-btn:active,
    .us-wrap.studio-role-royal .us-rail-btn:active{transform:scale(.98)}
    .us-wrap.studio-role-premium .us-rail-btn .ico,
    .us-wrap.studio-role-royal .us-rail-btn .ico{transition:transform .16s ease!important}
    .us-wrap.studio-role-premium .us-rail-btn:hover .ico,
    .us-wrap.studio-role-royal .us-rail-btn:hover .ico{transform:scale(1.05)}

    .us-wrap.studio-role-premium .us-library,
    .us-wrap.studio-role-royal .us-library{
        background:#FFFFFF!important;
        border-right:1px solid var(--us-soft-border)!important;
        box-shadow:6px 0 20px rgba(15,23,42,.025)!important;
    }
    .us-wrap.studio-role-premium .us-library-head,
    .us-wrap.studio-role-royal .us-library-head{
        background:#FFFFFF!important;
        border-bottom:1px solid var(--us-soft-border)!important;
    }
    .us-wrap.studio-role-premium .us-library-collapse,
    .us-wrap.studio-role-royal .us-library-collapse{
        border:1px solid var(--us-soft-border)!important;
        background:#FFFFFF!important;
        border-radius:999px!important;
        box-shadow:0 3px 12px rgba(15,23,42,.06)!important;
        transition:transform .16s ease,box-shadow .16s ease,background-color .16s ease!important;
    }
    .us-wrap.studio-role-premium .us-library-collapse:hover,
    .us-wrap.studio-role-royal .us-library-collapse:hover{
        background:#F8FAFC!important;
        box-shadow:0 5px 16px rgba(15,23,42,.08)!important;
        transform:translateY(-1px);
    }

    .us-wrap.studio-role-premium .us-contextbar,
    .us-wrap.studio-role-royal .us-contextbar,
    .us-wrap.studio-role-premium .us-pages-wrap,
    .us-wrap.studio-role-royal .us-pages-wrap,
    .us-wrap.studio-role-premium .us-work-bottom,
    .us-wrap.studio-role-royal .us-work-bottom{
        background:#FFFFFF!important;
        border-color:var(--us-soft-border)!important;
    }

    .us-wrap.studio-role-premium .us-canvas-zone,
    .us-wrap.studio-role-royal .us-canvas-zone{
        background:#F2F4F7!important;
    }
    .us-wrap.studio-role-premium .us-live-page,
    .us-wrap.studio-role-royal .us-live-page{
        border-radius:16px!important;
        border-color:transparent!important;
        transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease!important;
    }
    .us-wrap.studio-role-premium .us-live-page.active,
    .us-wrap.studio-role-royal .us-live-page.active{
        border-color:#AFC8F6!important;
        box-shadow:0 8px 28px rgba(15,23,42,.055)!important;
    }
    .us-wrap.studio-role-premium .us-canvas-shell,
    .us-wrap.studio-role-royal .us-canvas-shell{
        box-shadow:0 2px 12px rgba(15,23,42,.055)!important;
    }

    .us-wrap.studio-role-premium .us-responsive-card,
    .us-wrap.studio-role-royal .us-responsive-card,
    .us-wrap.studio-role-premium .us-opening-cover-card,
    .us-wrap.studio-role-royal .us-opening-cover-card,
    .us-wrap.studio-role-premium .us-desktop-cover-card,
    .us-wrap.studio-role-royal .us-desktop-cover-card,
    .us-wrap.studio-role-premium .us-customer-preview-card,
    .us-wrap.studio-role-royal .us-customer-preview-card,
    .us-wrap.studio-role-premium .us-binding-card,
    .us-wrap.studio-role-royal .us-binding-card,
    .us-wrap.studio-role-premium .us-animation-card,
    .us-wrap.studio-role-royal .us-animation-card,
    .us-wrap.studio-role-premium .us-advanced-folder,
    .us-wrap.studio-role-royal .us-advanced-folder,
    .us-wrap.studio-role-premium .us-upload,
    .us-wrap.studio-role-royal .us-upload,
    .us-wrap.studio-role-premium .us-font-current,
    .us-wrap.studio-role-royal .us-font-current,
    .us-wrap.studio-role-premium .us-element-card,
    .us-wrap.studio-role-royal .us-element-card,
    .us-wrap.studio-role-premium .us-component-card,
    .us-wrap.studio-role-royal .us-component-card,
    .us-wrap.studio-role-premium .us-page-card,
    .us-wrap.studio-role-royal .us-page-card,
    .us-wrap.studio-role-premium .us-page-grid-card,
    .us-wrap.studio-role-royal .us-page-grid-card{
        background:var(--us-soft-surface)!important;
        border:1px solid var(--us-soft-border)!important;
        border-radius:14px!important;
        box-shadow:0 1px 2px rgba(15,23,42,.02)!important;
    }

    .us-wrap.studio-role-premium .us-element-card,
    .us-wrap.studio-role-royal .us-element-card,
    .us-wrap.studio-role-premium .us-component-card,
    .us-wrap.studio-role-royal .us-component-card,
    .us-wrap.studio-role-premium .us-page-card,
    .us-wrap.studio-role-royal .us-page-card,
    .us-wrap.studio-role-premium .us-page-grid-card,
    .us-wrap.studio-role-royal .us-page-grid-card{
        transition:border-color .16s ease,box-shadow .16s ease,transform .16s ease!important;
    }
    .us-wrap.studio-role-premium .us-element-card:hover,
    .us-wrap.studio-role-royal .us-element-card:hover,
    .us-wrap.studio-role-premium .us-component-card:hover,
    .us-wrap.studio-role-royal .us-component-card:hover,
    .us-wrap.studio-role-premium .us-page-card:hover,
    .us-wrap.studio-role-royal .us-page-card:hover,
    .us-wrap.studio-role-premium .us-page-grid-card:hover,
    .us-wrap.studio-role-royal .us-page-grid-card:hover{
        border-color:#C9D6E8!important;
        box-shadow:0 6px 18px rgba(15,23,42,.05)!important;
        transform:translateY(-1px);
    }

    .us-wrap.studio-role-premium .us-h,
    .us-wrap.studio-role-royal .us-h{
        color:#778294!important;
        font-size:10px!important;
        font-weight:800!important;
        letter-spacing:.075em!important;
    }
    .us-wrap.studio-role-premium .us-small,
    .us-wrap.studio-role-royal .us-small,
    .us-wrap.studio-role-premium .us-customer-field-note,
    .us-wrap.studio-role-royal .us-customer-field-note{
        color:var(--us-soft-muted)!important;
        line-height:1.45!important;
    }

    .us-wrap.studio-role-premium .us-field label,
    .us-wrap.studio-role-royal .us-field label,
    .us-wrap.studio-role-premium .us-customer-field label,
    .us-wrap.studio-role-royal .us-customer-field label{
        color:#465163!important;
        font-weight:700!important;
    }

    .us-wrap.studio-role-premium input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
    .us-wrap.studio-role-royal input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
    .us-wrap.studio-role-premium select,
    .us-wrap.studio-role-royal select,
    .us-wrap.studio-role-premium textarea,
    .us-wrap.studio-role-royal textarea{
        background:#FFFFFF!important;
        border:1px solid #DCE3EC!important;
        border-radius:10px!important;
        box-shadow:0 1px 1px rgba(15,23,42,.018)!important;
        transition:border-color .16s ease,box-shadow .16s ease,background-color .16s ease!important;
    }
    .us-wrap.studio-role-premium input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):hover,
    .us-wrap.studio-role-royal input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):hover,
    .us-wrap.studio-role-premium select:hover,
    .us-wrap.studio-role-royal select:hover,
    .us-wrap.studio-role-premium textarea:hover,
    .us-wrap.studio-role-royal textarea:hover{
        border-color:#C9D3DF!important;
    }
    .us-wrap.studio-role-premium input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):focus,
    .us-wrap.studio-role-royal input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):focus,
    .us-wrap.studio-role-premium select:focus,
    .us-wrap.studio-role-royal select:focus,
    .us-wrap.studio-role-premium textarea:focus,
    .us-wrap.studio-role-royal textarea:focus{
        border-color:#8EB2F1!important;
        box-shadow:0 0 0 3px rgba(47,111,237,.10)!important;
        outline:none!important;
    }

    .us-wrap.studio-role-premium .us-tool,
    .us-wrap.studio-role-royal .us-tool,
    .us-wrap.studio-role-premium .us-btn:not(.primary),
    .us-wrap.studio-role-royal .us-btn:not(.primary),
    .us-wrap.studio-role-premium .us-mini-tool,
    .us-wrap.studio-role-royal .us-mini-tool,
    .us-wrap.studio-role-premium .us-device-btn,
    .us-wrap.studio-role-royal .us-device-btn,
    .us-wrap.studio-role-premium .us-view-mode,
    .us-wrap.studio-role-royal .us-view-mode,
    .us-wrap.studio-role-premium .us-transition-option,
    .us-wrap.studio-role-royal .us-transition-option,
    .us-wrap.studio-role-premium .us-animation-preset,
    .us-wrap.studio-role-royal .us-animation-preset{
        background:#FFFFFF!important;
        border:1px solid #DDE4ED!important;
        border-radius:10px!important;
        color:#344054!important;
        box-shadow:0 1px 1px rgba(15,23,42,.018)!important;
        transition:background-color .16s ease,border-color .16s ease,color .16s ease,box-shadow .16s ease,transform .16s ease!important;
    }
    .us-wrap.studio-role-premium .us-tool:hover,
    .us-wrap.studio-role-royal .us-tool:hover,
    .us-wrap.studio-role-premium .us-btn:not(.primary):hover,
    .us-wrap.studio-role-royal .us-btn:not(.primary):hover,
    .us-wrap.studio-role-premium .us-mini-tool:hover,
    .us-wrap.studio-role-royal .us-mini-tool:hover,
    .us-wrap.studio-role-premium .us-device-btn:hover,
    .us-wrap.studio-role-royal .us-device-btn:hover,
    .us-wrap.studio-role-premium .us-view-mode:hover,
    .us-wrap.studio-role-royal .us-view-mode:hover,
    .us-wrap.studio-role-premium .us-transition-option:hover,
    .us-wrap.studio-role-royal .us-transition-option:hover,
    .us-wrap.studio-role-premium .us-animation-preset:hover,
    .us-wrap.studio-role-royal .us-animation-preset:hover{
        background:#F8FAFC!important;
        border-color:#CBD5E1!important;
        transform:translateY(-1px);
        box-shadow:0 4px 10px rgba(15,23,42,.045)!important;
    }
    .us-wrap.studio-role-premium .us-tool:active,
    .us-wrap.studio-role-royal .us-tool:active,
    .us-wrap.studio-role-premium .us-btn:active,
    .us-wrap.studio-role-royal .us-btn:active,
    .us-wrap.studio-role-premium .us-device-btn:active,
    .us-wrap.studio-role-royal .us-device-btn:active,
    .us-wrap.studio-role-premium .us-view-mode:active,
    .us-wrap.studio-role-royal .us-view-mode:active,
    .us-wrap.studio-role-premium .us-animation-preset:active,
    .us-wrap.studio-role-royal .us-animation-preset:active{transform:scale(.985)}

    .us-wrap.studio-role-premium .us-btn.primary,
    .us-wrap.studio-role-royal .us-btn.primary,
    .us-wrap.studio-role-premium .us-device-btn.active,
    .us-wrap.studio-role-royal .us-device-btn.active,
    .us-wrap.studio-role-premium .us-view-mode.active,
    .us-wrap.studio-role-royal .us-view-mode.active{
        background:var(--us-soft-primary)!important;
        border-color:var(--us-soft-primary)!important;
        color:#FFFFFF!important;
        border-radius:10px!important;
        box-shadow:0 2px 6px rgba(47,111,237,.16)!important;
    }

    .us-wrap.studio-role-premium .us-dropzone,
    .us-wrap.studio-role-royal .us-dropzone,
    .us-wrap.studio-role-premium .us-font-dropzone,
    .us-wrap.studio-role-royal .us-font-dropzone{
        background:#FAFBFD!important;
        border-color:#CBD6E4!important;
        border-radius:14px!important;
        transition:border-color .18s ease,background-color .18s ease,box-shadow .18s ease!important;
    }
    .us-wrap.studio-role-premium .us-dropzone:hover,
    .us-wrap.studio-role-royal .us-dropzone:hover,
    .us-wrap.studio-role-premium .us-dropzone.dragover,
    .us-wrap.studio-role-royal .us-dropzone.dragover,
    .us-wrap.studio-role-premium .us-font-dropzone:hover,
    .us-wrap.studio-role-royal .us-font-dropzone:hover,
    .us-wrap.studio-role-premium .us-font-dropzone.dragover,
    .us-wrap.studio-role-royal .us-font-dropzone.dragover{
        background:#F6F9FF!important;
        border-color:#93B4EC!important;
        box-shadow:0 0 0 3px rgba(47,111,237,.06)!important;
    }

    .us-wrap.studio-role-premium .us-asset-card,
    .us-wrap.studio-role-royal .us-asset-card,
    .us-wrap.studio-role-premium .us-asset-item,
    .us-wrap.studio-role-royal .us-asset-item{
        border-radius:12px!important;
        overflow:hidden!important;
        border-color:var(--us-soft-border)!important;
        transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease!important;
    }
    .us-wrap.studio-role-premium .us-asset-card:hover,
    .us-wrap.studio-role-royal .us-asset-card:hover,
    .us-wrap.studio-role-premium .us-asset-item:hover,
    .us-wrap.studio-role-royal .us-asset-item:hover{
        transform:translateY(-1px);
        border-color:#C9D3E0!important;
        box-shadow:0 6px 16px rgba(15,23,42,.05)!important;
    }
    .us-wrap.studio-role-premium .us-asset-delete,
    .us-wrap.studio-role-royal .us-asset-delete{
        transition:transform .14s ease,background-color .14s ease,box-shadow .14s ease!important;
    }
    .us-wrap.studio-role-premium .us-asset-delete:hover,
    .us-wrap.studio-role-royal .us-asset-delete:hover{
        transform:scale(1.06);
        box-shadow:0 3px 10px rgba(15,23,42,.10)!important;
    }

    .us-wrap.studio-role-premium .us-customer-section-heading,
    .us-wrap.studio-role-royal .us-customer-section-heading{
        color:#4C5768!important;
        border-top:1px solid #EEF1F5!important;
        background:transparent!important;
        letter-spacing:.055em!important;
        padding-top:13px!important;
        margin-top:8px!important;
    }
    .us-wrap.studio-role-premium .us-customer-section-heading.first,
    .us-wrap.studio-role-royal .us-customer-section-heading.first{
        border-top:0!important;
        margin-top:0!important;
        padding-top:2px!important;
    }
    .us-wrap.studio-role-premium .us-customer-field,
    .us-wrap.studio-role-royal .us-customer-field{
        border-radius:11px!important;
    }

    /* Keep the proven Delete Guard behavior; soften only its presentation. */
    .us-dg2-overlay{background:rgba(31,41,55,.22)!important;backdrop-filter:blur(2px)}
    .us-dg2-dialog{
        border-radius:18px!important;
        background:#FFFCFB!important;
        box-shadow:0 20px 55px rgba(63,45,45,.15)!important;
        padding:20px!important;
    }
    .us-dg2-location{border-radius:11px!important;background:#FFFFFF!important}
    .us-dg2-btn{border-radius:10px!important;transition:transform .15s ease,box-shadow .15s ease,background-color .15s ease!important}
    .us-dg2-btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(113,63,63,.08)!important}
    .us-dg2-btn:active{transform:scale(.985)}

    .us-wrap.studio-role-premium .us-toast,
    .us-wrap.studio-role-royal .us-toast{
        border-radius:12px!important;
        box-shadow:0 12px 30px rgba(15,23,42,.12)!important;
    }

    @media (max-width:768px){
        .us-wrap.studio-role-premium .us-live-page,
        .us-wrap.studio-role-royal .us-live-page{border-radius:13px!important}
        .us-wrap.studio-role-premium .us-responsive-card,
        .us-wrap.studio-role-royal .us-responsive-card,
        .us-wrap.studio-role-premium .us-customer-preview-card,
        .us-wrap.studio-role-royal .us-customer-preview-card{border-radius:12px!important}
    }

    @media (prefers-reduced-motion:reduce){
        .us-wrap.studio-role-premium .us-rail-btn,
        .us-wrap.studio-role-royal .us-rail-btn,
        .us-wrap.studio-role-premium .us-tool,
        .us-wrap.studio-role-royal .us-tool,
        .us-wrap.studio-role-premium .us-btn,
        .us-wrap.studio-role-royal .us-btn,
        .us-wrap.studio-role-premium .us-element-card,
        .us-wrap.studio-role-royal .us-element-card,
        .us-wrap.studio-role-premium .us-component-card,
        .us-wrap.studio-role-royal .us-component-card,
        .us-wrap.studio-role-premium .us-page-card,
        .us-wrap.studio-role-royal .us-page-card,
        .us-wrap.studio-role-premium .us-asset-card,
        .us-wrap.studio-role-royal .us-asset-card{transition:none!important;transform:none!important}
    }


    /* UNDANGANTA_STUDIO_IOS_MINIMAL_LIGHT_V2_SAFE */
    /*
     * Visual-only light iOS-inspired refinement.
     * Goal: quiet hierarchy, hairline separators, low elevation, restrained translucency.
     * No dark-mode palette, no gradients, no exaggerated floating cards.
     */
    .us-wrap.studio-role-premium,
    .us-wrap.studio-role-royal{
        color-scheme:light!important;
        --im-bg:#F5F5F7;
        --im-surface:#FFFFFF;
        --im-surface-soft:#F8F8FA;
        --im-fill:#F2F2F7;
        --im-fill-strong:#EDEDF2;
        --im-label:#1D1D1F;
        --im-secondary:#6E6E73;
        --im-tertiary:#8E8E93;
        --im-line:rgba(60,60,67,.12);
        --im-line-soft:rgba(60,60,67,.075);
        --im-blue:#007AFF;
        --im-blue-soft:rgba(0,122,255,.095);
        --im-red:#FF3B30;
        --im-radius-xl:18px;
        --im-radius-lg:15px;
        --im-radius-md:12px;
        --im-radius-sm:10px;
        --im-ease:cubic-bezier(.25,.8,.25,1);
        font-family:-apple-system,BlinkMacSystemFont,"SF Pro Text","SF Pro Display","Segoe UI Variable Text","Segoe UI",sans-serif!important;
        color:var(--im-label)!important;
        background:var(--im-bg)!important;
        -webkit-font-smoothing:antialiased;
        text-rendering:optimizeLegibility;
    }

    html:has(.us-wrap.studio-role-premium),
    html:has(.us-wrap.studio-role-royal),
    body:has(.us-wrap.studio-role-premium),
    body:has(.us-wrap.studio-role-royal){
        color-scheme:light!important;
        background:var(--im-bg,#F5F5F7)!important;
    }

    /* Main app / top chrome: translucent but almost flat. */
    body:has(.us-wrap.studio-role-premium) .nav,
    body:has(.us-wrap.studio-role-royal) .nav,
    .us-wrap.studio-role-premium .us-top,
    .us-wrap.studio-role-royal .us-top{
        background:rgba(255,255,255,.88)!important;
        border-color:var(--im-line-soft)!important;
        box-shadow:none!important;
        -webkit-backdrop-filter:saturate(130%) blur(14px)!important;
        backdrop-filter:saturate(130%) blur(14px)!important;
    }
    body:has(.us-wrap.studio-role-premium) .nav-inner,
    body:has(.us-wrap.studio-role-royal) .nav-inner{background:transparent!important}

    .us-wrap.studio-role-premium .us-top-title,
    .us-wrap.studio-role-royal .us-top-title{
        color:var(--im-label)!important;
        font-weight:600!important;
        letter-spacing:-.015em!important;
    }
    .us-wrap.studio-role-premium .us-save-status,
    .us-wrap.studio-role-royal .us-save-status{color:var(--im-secondary)!important;font-weight:500!important}

    .us-wrap.studio-role-premium .us-body,
    .us-wrap.studio-role-royal .us-body,
    .us-wrap.studio-role-premium .us-workspace,
    .us-wrap.studio-role-royal .us-workspace,
    .us-wrap.studio-role-premium .us-canvas-zone,
    .us-wrap.studio-role-royal .us-canvas-zone,
    .us-wrap.studio-role-premium #editorDocument,
    .us-wrap.studio-role-royal #editorDocument{
        background:var(--im-bg)!important;
    }

    /* Rail: flat navigation, no icon tiles, only soft selected state. */
    .us-wrap.studio-role-premium .us-rail,
    .us-wrap.studio-role-royal .us-rail{
        background:rgba(255,255,255,.90)!important;
        border-right:1px solid var(--im-line-soft)!important;
        box-shadow:none!important;
        -webkit-backdrop-filter:saturate(125%) blur(12px)!important;
        backdrop-filter:saturate(125%) blur(12px)!important;
    }
    .us-wrap.studio-role-premium .us-rail-btn,
    .us-wrap.studio-role-royal .us-rail-btn{
        color:var(--im-secondary)!important;
        background:transparent!important;
        border:0!important;
        border-radius:12px!important;
        box-shadow:none!important;
        font-weight:550!important;
        transition:background-color .14s var(--im-ease),color .14s var(--im-ease),opacity .14s var(--im-ease)!important;
        transform:none!important;
    }
    .us-wrap.studio-role-premium .us-rail-btn .ico,
    .us-wrap.studio-role-royal .us-rail-btn .ico{
        width:28px!important;height:28px!important;
        display:inline-flex!important;align-items:center!important;justify-content:center!important;
        color:#3A3A3C!important;
        background:transparent!important;
        border:0!important;
        border-radius:0!important;
        box-shadow:none!important;
        transform:none!important;
    }
    .us-wrap.studio-role-premium .us-rail-btn:hover,
    .us-wrap.studio-role-royal .us-rail-btn:hover{background:rgba(118,118,128,.065)!important;color:var(--im-label)!important;transform:none!important}
    .us-wrap.studio-role-premium .us-rail-btn.active,
    .us-wrap.studio-role-royal .us-rail-btn.active{background:var(--im-blue-soft)!important;color:var(--im-blue)!important;box-shadow:none!important;transform:none!important}
    .us-wrap.studio-role-premium .us-rail-btn.active .ico,
    .us-wrap.studio-role-royal .us-rail-btn.active .ico{background:transparent!important;color:var(--im-blue)!important;box-shadow:none!important}
    .us-wrap.studio-role-premium .us-rail-btn:active,
    .us-wrap.studio-role-royal .us-rail-btn:active{opacity:.62!important;transform:none!important}

    /* Tool drawer: nearly solid white, no floating-panel shadow. */
    .us-wrap.studio-role-premium .us-library,
    .us-wrap.studio-role-royal .us-library{
        background:rgba(255,255,255,.94)!important;
        border-right:1px solid var(--im-line-soft)!important;
        box-shadow:none!important;
        -webkit-backdrop-filter:saturate(120%) blur(12px)!important;
        backdrop-filter:saturate(120%) blur(12px)!important;
    }
    .us-wrap.studio-role-premium .us-library-head,
    .us-wrap.studio-role-royal .us-library-head{
        background:transparent!important;
        border-bottom:1px solid var(--im-line-soft)!important;
        color:var(--im-label)!important;
        font-size:15px!important;
        font-weight:600!important;
        letter-spacing:-.012em!important;
    }
    .us-wrap.studio-role-premium .us-library-collapse,
    .us-wrap.studio-role-royal .us-library-collapse,
    .us-wrap.studio-role-premium .us-properties-collapse,
    .us-wrap.studio-role-royal .us-properties-collapse{
        background:rgba(255,255,255,.94)!important;
        border:1px solid var(--im-line)!important;
        color:var(--im-secondary)!important;
        border-radius:12px!important;
        box-shadow:0 1px 2px rgba(0,0,0,.035)!important;
        -webkit-backdrop-filter:blur(10px)!important;
        backdrop-filter:blur(10px)!important;
        transform:none!important;
    }
    .us-wrap.studio-role-premium .us-library-collapse:hover,
    .us-wrap.studio-role-royal .us-library-collapse:hover,
    .us-wrap.studio-role-premium .us-properties-collapse:hover,
    .us-wrap.studio-role-royal .us-properties-collapse:hover{background:#fff!important;box-shadow:0 1px 2px rgba(0,0,0,.035)!important;transform:none!important}

    /* Typography: quieter weights and less all-caps. */
    .us-wrap.studio-role-premium .us-h,
    .us-wrap.studio-role-royal .us-h{
        color:var(--im-secondary)!important;
        font-size:11px!important;
        line-height:1.25!important;
        font-weight:600!important;
        letter-spacing:.01em!important;
        text-transform:none!important;
    }
    .us-wrap.studio-role-premium .us-small,
    .us-wrap.studio-role-royal .us-small,
    .us-wrap.studio-role-premium .us-customer-field-note,
    .us-wrap.studio-role-royal .us-customer-field-note{color:var(--im-secondary)!important;line-height:1.45!important;font-weight:400!important}
    .us-wrap.studio-role-premium .us-field label,
    .us-wrap.studio-role-royal .us-field label,
    .us-wrap.studio-role-premium .us-customer-field label,
    .us-wrap.studio-role-royal .us-customer-field label{color:#3A3A3C!important;font-weight:550!important;letter-spacing:-.003em!important}

    /* Cards: flat grouped surfaces, minimal separation. */
    .us-wrap.studio-role-premium .us-responsive-card,
    .us-wrap.studio-role-royal .us-responsive-card,
    .us-wrap.studio-role-premium .us-opening-cover-card,
    .us-wrap.studio-role-royal .us-opening-cover-card,
    .us-wrap.studio-role-premium .us-desktop-cover-card,
    .us-wrap.studio-role-royal .us-desktop-cover-card,
    .us-wrap.studio-role-premium .us-customer-preview-card,
    .us-wrap.studio-role-royal .us-customer-preview-card,
    .us-wrap.studio-role-premium .us-binding-card,
    .us-wrap.studio-role-royal .us-binding-card,
    .us-wrap.studio-role-premium .us-animation-card,
    .us-wrap.studio-role-royal .us-animation-card,
    .us-wrap.studio-role-premium .us-advanced-folder,
    .us-wrap.studio-role-royal .us-advanced-folder,
    .us-wrap.studio-role-premium .us-upload,
    .us-wrap.studio-role-royal .us-upload,
    .us-wrap.studio-role-premium .us-font-current,
    .us-wrap.studio-role-royal .us-font-current,
    .us-wrap.studio-role-premium .us-element-card,
    .us-wrap.studio-role-royal .us-element-card,
    .us-wrap.studio-role-premium .us-component-card,
    .us-wrap.studio-role-royal .us-component-card,
    .us-wrap.studio-role-premium .us-page-card,
    .us-wrap.studio-role-royal .us-page-card,
    .us-wrap.studio-role-premium .us-page-grid-card,
    .us-wrap.studio-role-royal .us-page-grid-card,
    .us-wrap.studio-role-premium .us-context-card,
    .us-wrap.studio-role-royal .us-context-card{
        background:var(--im-surface)!important;
        border:1px solid var(--im-line-soft)!important;
        border-radius:14px!important;
        box-shadow:none!important;
    }

    .us-wrap.studio-role-premium .us-customer-section-heading,
    .us-wrap.studio-role-royal .us-customer-section-heading{
        background:transparent!important;
        border:0!important;
        border-top:1px solid var(--im-line-soft)!important;
        border-radius:0!important;
        color:var(--im-secondary)!important;
        box-shadow:none!important;
        font-weight:600!important;
        letter-spacing:.01em!important;
        padding-top:12px!important;
    }
    .us-wrap.studio-role-premium .us-customer-section-heading.first,
    .us-wrap.studio-role-royal .us-customer-section-heading.first{border-top:0!important;padding-top:2px!important}

    /* Inputs = iOS grouped form fill, not raised white boxes. */
    .us-wrap.studio-role-premium input:not([type=range]):not([type=checkbox]):not([type=radio]):not([type=color]),
    .us-wrap.studio-role-royal input:not([type=range]):not([type=checkbox]):not([type=radio]):not([type=color]),
    .us-wrap.studio-role-premium textarea,
    .us-wrap.studio-role-royal textarea,
    .us-wrap.studio-role-premium select,
    .us-wrap.studio-role-royal select{
        background:var(--im-fill)!important;
        border:1px solid transparent!important;
        border-radius:10px!important;
        box-shadow:none!important;
        color:var(--im-label)!important;
        min-height:36px!important;
        transition:background-color .14s var(--im-ease),border-color .14s var(--im-ease),box-shadow .14s var(--im-ease)!important;
    }
    .us-wrap.studio-role-premium textarea,
    .us-wrap.studio-role-royal textarea{border-radius:12px!important}
    .us-wrap.studio-role-premium input:not([type=range]):not([type=checkbox]):not([type=radio]):not([type=color]):focus,
    .us-wrap.studio-role-royal input:not([type=range]):not([type=checkbox]):not([type=radio]):not([type=color]):focus,
    .us-wrap.studio-role-premium textarea:focus,
    .us-wrap.studio-role-royal textarea:focus,
    .us-wrap.studio-role-premium select:focus,
    .us-wrap.studio-role-royal select:focus{
        background:#fff!important;
        border-color:rgba(0,122,255,.34)!important;
        box-shadow:0 0 0 3px rgba(0,122,255,.09)!important;
        outline:none!important;
    }

    /* Buttons: flat, compact, clear hierarchy. */
    .us-wrap.studio-role-premium .us-btn,
    .us-wrap.studio-role-royal .us-btn,
    .us-wrap.studio-role-premium .us-tool,
    .us-wrap.studio-role-royal .us-tool,
    .us-wrap.studio-role-premium .us-mini-tool,
    .us-wrap.studio-role-royal .us-mini-tool,
    .us-wrap.studio-role-premium .us-icon-btn,
    .us-wrap.studio-role-royal .us-icon-btn,
    .us-wrap.studio-role-premium .us-device-btn,
    .us-wrap.studio-role-royal .us-device-btn,
    .us-wrap.studio-role-premium .us-view-mode,
    .us-wrap.studio-role-royal .us-view-mode,
    .us-wrap.studio-role-premium .us-animation-preset,
    .us-wrap.studio-role-royal .us-animation-preset,
    .us-wrap.studio-role-premium .us-transition-option,
    .us-wrap.studio-role-royal .us-transition-option,
    .us-wrap.studio-role-premium .us-grid-view-btn,
    .us-wrap.studio-role-royal .us-grid-view-btn,
    .us-wrap.studio-role-premium .us-pages-toggle,
    .us-wrap.studio-role-royal .us-pages-toggle{
        background:rgba(255,255,255,.92)!important;
        border:1px solid var(--im-line)!important;
        border-radius:10px!important;
        box-shadow:none!important;
        color:var(--im-label)!important;
        font-weight:550!important;
        transition:background-color .14s var(--im-ease),border-color .14s var(--im-ease),color .14s var(--im-ease),opacity .14s var(--im-ease)!important;
        transform:none!important;
    }
    .us-wrap.studio-role-premium .us-btn:hover,
    .us-wrap.studio-role-royal .us-btn:hover,
    .us-wrap.studio-role-premium .us-tool:hover,
    .us-wrap.studio-role-royal .us-tool:hover,
    .us-wrap.studio-role-premium .us-mini-tool:hover,
    .us-wrap.studio-role-royal .us-mini-tool:hover,
    .us-wrap.studio-role-premium .us-device-btn:hover,
    .us-wrap.studio-role-royal .us-device-btn:hover,
    .us-wrap.studio-role-premium .us-view-mode:hover,
    .us-wrap.studio-role-royal .us-view-mode:hover,
    .us-wrap.studio-role-premium .us-animation-preset:hover,
    .us-wrap.studio-role-royal .us-animation-preset:hover{background:var(--im-surface-soft)!important;border-color:rgba(60,60,67,.16)!important;transform:none!important}
    .us-wrap.studio-role-premium .us-btn:active,
    .us-wrap.studio-role-royal .us-btn:active,
    .us-wrap.studio-role-premium .us-tool:active,
    .us-wrap.studio-role-royal .us-tool:active,
    .us-wrap.studio-role-premium .us-mini-tool:active,
    .us-wrap.studio-role-royal .us-mini-tool:active,
    .us-wrap.studio-role-premium .us-device-btn:active,
    .us-wrap.studio-role-royal .us-device-btn:active{opacity:.58!important;transform:none!important}

    .us-wrap.studio-role-premium .us-btn.primary,
    .us-wrap.studio-role-royal .us-btn.primary,
    .us-wrap.studio-role-premium #saveBtn,
    .us-wrap.studio-role-royal #saveBtn{
        background:var(--im-blue)!important;
        border-color:var(--im-blue)!important;
        color:#fff!important;
        box-shadow:none!important;
    }
    .us-wrap.studio-role-premium .danger,
    .us-wrap.studio-role-royal .danger{color:var(--im-red)!important}

    /* Toggle-like active controls are tint-only, not embossed. */
    .us-wrap.studio-role-premium .us-btn.active:not(.primary),
    .us-wrap.studio-role-royal .us-btn.active:not(.primary),
    .us-wrap.studio-role-premium .us-device-btn.active,
    .us-wrap.studio-role-royal .us-device-btn.active,
    .us-wrap.studio-role-premium .us-view-mode.active,
    .us-wrap.studio-role-royal .us-view-mode.active,
    .us-wrap.studio-role-premium .us-animation-preset.active,
    .us-wrap.studio-role-royal .us-animation-preset.active{
        background:var(--im-blue-soft)!important;
        border-color:rgba(0,122,255,.20)!important;
        color:var(--im-blue)!important;
        box-shadow:none!important;
    }

    /* Device selector reads as one segmented control. */
    .us-wrap.studio-role-premium .us-device-switch,
    .us-wrap.studio-role-royal .us-device-switch,
    .us-wrap.studio-role-premium .us-view-modes,
    .us-wrap.studio-role-royal .us-view-modes{
        gap:2px!important;
        padding:2px!important;
        background:rgba(118,118,128,.10)!important;
        border:0!important;
        border-radius:12px!important;
        box-shadow:none!important;
    }
    .us-wrap.studio-role-premium .us-device-switch .us-device-btn,
    .us-wrap.studio-role-royal .us-device-switch .us-device-btn,
    .us-wrap.studio-role-premium .us-view-modes .us-view-mode,
    .us-wrap.studio-role-royal .us-view-modes .us-view-mode{
        border:0!important;
        background:transparent!important;
        border-radius:9px!important;
        box-shadow:none!important;
    }
    .us-wrap.studio-role-premium .us-device-switch .us-device-btn.active,
    .us-wrap.studio-role-royal .us-device-switch .us-device-btn.active,
    .us-wrap.studio-role-premium .us-view-modes .us-view-mode.active,
    .us-wrap.studio-role-royal .us-view-modes .us-view-mode.active{
        background:#fff!important;
        color:var(--im-label)!important;
        box-shadow:0 1px 2px rgba(0,0,0,.055)!important;
    }

    /* Toolbars / bottom bars: thin translucent material, almost no elevation. */
    .us-wrap.studio-role-premium .us-contextbar,
    .us-wrap.studio-role-royal .us-contextbar,
    .us-wrap.studio-role-premium .us-pages-wrap,
    .us-wrap.studio-role-royal .us-pages-wrap,
    .us-wrap.studio-role-premium .us-work-bottom,
    .us-wrap.studio-role-royal .us-work-bottom{
        background:rgba(255,255,255,.88)!important;
        border-color:var(--im-line-soft)!important;
        box-shadow:none!important;
        -webkit-backdrop-filter:saturate(125%) blur(12px)!important;
        backdrop-filter:saturate(125%) blur(12px)!important;
    }

    /* Canvas/page chrome: subtle separation from workspace, no floating shadow. */
    .us-wrap.studio-role-premium .us-live-page,
    .us-wrap.studio-role-royal .us-live-page{
        background:transparent!important;
        border:1px solid var(--im-line)!important;
        border-radius:16px!important;
        box-shadow:none!important;
    }
    .us-wrap.studio-role-premium .us-live-page.active,
    .us-wrap.studio-role-royal .us-live-page.active{
        border-color:rgba(0,122,255,.36)!important;
        box-shadow:0 0 0 2px rgba(0,122,255,.06)!important;
    }
    .us-wrap.studio-role-premium .us-canvas-shell,
    .us-wrap.studio-role-royal .us-canvas-shell{
        border-radius:12px!important;
        box-shadow:none!important;
    }
    .us-wrap.studio-role-premium .us-page-chip,
    .us-wrap.studio-role-royal .us-page-chip{
        background:#fff!important;
        border:1px solid var(--im-line)!important;
        border-radius:12px!important;
        box-shadow:none!important;
    }

    /* Upload / media: calm background, border only. */
    .us-wrap.studio-role-premium .us-dropzone,
    .us-wrap.studio-role-royal .us-dropzone,
    .us-wrap.studio-role-premium .us-font-dropzone,
    .us-wrap.studio-role-royal .us-font-dropzone{
        background:var(--im-surface-soft)!important;
        border:1px dashed rgba(60,60,67,.22)!important;
        border-radius:14px!important;
        box-shadow:none!important;
    }
    .us-wrap.studio-role-premium .us-dropzone:hover,
    .us-wrap.studio-role-royal .us-dropzone:hover,
    .us-wrap.studio-role-premium .us-font-dropzone:hover,
    .us-wrap.studio-role-royal .us-font-dropzone:hover{background:#fff!important;border-color:rgba(0,122,255,.30)!important}
    .us-wrap.studio-role-premium .us-asset-card,
    .us-wrap.studio-role-royal .us-asset-card{
        border:1px solid var(--im-line-soft)!important;
        border-radius:13px!important;
        box-shadow:none!important;
        overflow:hidden!important;
    }
    .us-wrap.studio-role-premium .us-asset-delete,
    .us-wrap.studio-role-royal .us-asset-delete{
        box-shadow:none!important;
        border:1px solid rgba(60,60,67,.12)!important;
        background:rgba(255,255,255,.90)!important;
        color:var(--im-label)!important;
    }

    /* Popovers/toasts: light only, low shadow because they must detach visually. */
    .us-wrap.studio-role-premium .us-toast,
    .us-wrap.studio-role-royal .us-toast,
    .us-wrap.studio-role-premium .us-transition-popover,
    .us-wrap.studio-role-royal .us-transition-popover,
    .us-wrap.studio-role-premium .us-context-menu,
    .us-wrap.studio-role-royal .us-context-menu{
        background:rgba(255,255,255,.96)!important;
        color:var(--im-label)!important;
        border:1px solid var(--im-line)!important;
        border-radius:14px!important;
        box-shadow:0 6px 20px rgba(0,0,0,.07)!important;
        -webkit-backdrop-filter:blur(12px)!important;
        backdrop-filter:blur(12px)!important;
    }

    /* Delete Guard: iOS-style alert without big floating-card depth. */
    .us-dg2-overlay{
        background:rgba(0,0,0,.14)!important;
        -webkit-backdrop-filter:blur(4px)!important;
        backdrop-filter:blur(4px)!important;
    }
    .us-dg2-dialog{
        background:rgba(255,255,255,.98)!important;
        border:1px solid rgba(60,60,67,.10)!important;
        border-radius:20px!important;
        box-shadow:0 10px 30px rgba(0,0,0,.085)!important;
        padding:20px!important;
        color:#1D1D1F!important;
        -webkit-backdrop-filter:blur(14px)!important;
        backdrop-filter:blur(14px)!important;
    }
    .us-dg2-location{background:#F2F2F7!important;border:0!important;border-radius:11px!important;box-shadow:none!important}
    .us-dg2-btn{
        min-height:38px!important;
        border-radius:10px!important;
        border:1px solid rgba(60,60,67,.12)!important;
        background:#fff!important;
        color:#1D1D1F!important;
        box-shadow:none!important;
        transform:none!important;
    }
    .us-dg2-btn.primary{background:#007AFF!important;border-color:#007AFF!important;color:#fff!important;box-shadow:none!important}
    .us-dg2-btn:active{opacity:.62!important;transform:none!important}

    /* Keep focus crisp but quiet. */
    .us-wrap.studio-role-premium :focus-visible,
    .us-wrap.studio-role-royal :focus-visible{outline:2px solid rgba(0,122,255,.55)!important;outline-offset:2px!important}

    /* Remove old decorative elevation from previous visual patches. */
    .us-wrap.studio-role-premium [class*="card"],
    .us-wrap.studio-role-royal [class*="card"]{filter:none!important}

    @supports not ((-webkit-backdrop-filter:blur(1px)) or (backdrop-filter:blur(1px))){
        body:has(.us-wrap.studio-role-premium) .nav,
        body:has(.us-wrap.studio-role-royal) .nav,
        .us-wrap.studio-role-premium .us-top,.us-wrap.studio-role-royal .us-top,
        .us-wrap.studio-role-premium .us-rail,.us-wrap.studio-role-royal .us-rail,
        .us-wrap.studio-role-premium .us-library,.us-wrap.studio-role-royal .us-library,
        .us-wrap.studio-role-premium .us-contextbar,.us-wrap.studio-role-royal .us-contextbar,
        .us-wrap.studio-role-premium .us-pages-wrap,.us-wrap.studio-role-royal .us-pages-wrap,
        .us-wrap.studio-role-premium .us-work-bottom,.us-wrap.studio-role-royal .us-work-bottom{background:#FFFFFF!important}
    }

    @media (max-width:768px){
        .us-wrap.studio-role-premium .us-library,
        .us-wrap.studio-role-royal .us-library{background:#fff!important;border-radius:18px 18px 0 0!important;box-shadow:0 -6px 20px rgba(0,0,0,.055)!important}
        .us-dg2-dialog{border-radius:18px!important;box-shadow:0 8px 24px rgba(0,0,0,.08)!important}
    }

    @media (prefers-reduced-motion:reduce){
        .us-wrap.studio-role-premium *,
        .us-wrap.studio-role-royal *,
        .us-dg2-dialog,.us-dg2-btn{transition-duration:.001ms!important;animation-duration:.001ms!important;animation-iteration-count:1!important}
    }

</style>
@endif
<div class="us-wrap studio-role-{{ $studioUiRole }}" data-studio-plan="{{ $studioUiRole }}">
    <div class="us-top">
        <div class="us-top-left">
            <button id="homeStudio" class="us-dashboard-back" type="button" title="Kembali ke Dashboard"><span class="us-dashboard-arrow">◀</span><span>Dashboard</span></button>
        </div>
        <div class="us-top-center"><div class="us-top-title">{{ $isInstanceMode ? ($invitation->title . ' — UNDANGANTA Studio') : ($template->name . ' — UNDANGANTA Studio') }}</div></div>
        <div class="us-top-right">
            <span id="saveStatus" class="us-save-status"><i id="saveDot" class="us-autosave-dot"></i><span id="saveStatusText">Siap</span></span>
            <button id="undoBtn" class="us-btn" type="button">↶</button>
            <button id="redoBtn" class="us-btn" type="button">↷</button>
            <button id="mobileTopMore" class="us-btn" type="button" aria-label="Menu lainnya" title="Menu lainnya">•••</button>
            <button id="previewBtn" class="us-btn" type="button">Pratinjau</button>
            <button id="saveBtn" class="us-btn primary" type="button">Simpan</button>
        </div>
    </div>

    <div class="us-body library-collapsed properties-collapsed studio-role-{{ $studioUiRole }}">
        <nav class="us-rail" aria-label="Editor tools">
            <button class="us-rail-btn {{ $isInstanceMode ? 'active' : '' }}" data-panel-target="cover" type="button"><span class="ico">▣</span>Cover</button>
            <button class="us-rail-btn" data-panel-target="elements" type="button"><span class="ico">□</span>Elemen</button>
            <button class="us-rail-btn" data-panel-target="animation" type="button"><span class="ico">◌</span>Animasi</button>
            <button class="us-rail-btn" data-panel-target="text" type="button"><span class="ico">T</span>Teks</button>
            <button class="us-rail-btn" data-panel-target="fonts" type="button"><span class="ico">Aa</span>Font</button>
            <button class="us-rail-btn" data-panel-target="uploads" type="button"><span class="ico">▧</span>Unggahan</button>
            @if($isInstanceMode)
            <button class="us-rail-btn" data-panel-target="gallery" type="button"><span class="ico">▦</span>Galeri</button>
            @endif
            <button class="us-rail-btn" data-panel-target="components" type="button"><span class="ico">◇</span>Komponen</button>
            <button class="us-rail-btn" data-panel-target="layers" type="button"><span class="ico">☷</span>Layer</button>

            <button class="us-rail-btn {{ $isInstanceMode ? '' : 'active' }} us-admin-only-tool" data-panel-target="template" type="button" @if($isInstanceMode) style="display:none" @endif><span class="ico">▦</span>Template</button>
            <button class="us-rail-btn us-admin-only-tool" data-panel-target="customer-data" type="button" @if($isInstanceMode) style="display:none" @endif><span class="ico">≡</span>{{ $isInstanceMode ? 'Data Undangan' : 'Data Uji' }}</button>
            <div class="us-rail-spacer"></div>
        </nav>

        <button id="toggleLibrary" class="us-library-collapse" type="button" aria-label="Buka atau tutup panel alat">‹</button>

        <aside class="us-library us-mobile-sheet-surface">
            <div class="us-library-head"><span>UNDANGANTA Studio</span><button id="mobileCloseLibrary" class="us-mobile-sheet-close" type="button" aria-label="Tutup panel">✕</button></div>
            <div class="us-library-scroll">
                <section data-panel-section="template">
                    <div class="us-h">Template</div>
                    <div class="us-search"><input type="text" placeholder="Cari template..." disabled></div>
                    <div class="us-small" style="margin-bottom:12px">Editor visual tanpa coding. Semua elemen di template ini dapat diposisikan bebas.</div>
                    <button id="openCustomerDataPanel" class="us-tool" type="button" style="width:100%;margin-bottom:10px">{{ $isInstanceMode ? 'Data Undangan' : 'Buka Data Pelanggan Uji' }}</button>
                    <div class="us-meta us-template-meta" style="display:{{ $isInstanceMode ? 'none' : 'grid' }};gap:9px">
                        <div class="us-field"><label>Nama Template <span class="us-required">*</span></label><input id="tplName" class="us-name" value="{{ $template?->name ?? 'Blank Canvas' }}" required aria-required="true" placeholder="Contoh: Elegant Blue"></div>
                        <div class="us-field"><label>Slug Template <span class="us-required">*</span></label><input id="tplSlug" class="us-slug" value="{{ $template?->slug ?? 'blank-canvas' }}" required aria-required="true" placeholder="contoh-elegant-blue"></div>
                        <div class="us-field"><label>Paket Minimum <span class="us-required">*</span></label><select id="tplPlan" required aria-required="true"><option value="basic" @selected(in_array(($template?->min_plan ?? 'basic'),['free','basic'],true))>Basic</option><option value="premium" @selected(in_array(($template?->min_plan ?? 'premium'),['premium','intimate'],true))>Premium / Intimate</option><option value="royal" @selected(in_array(($template?->min_plan ?? 'premium'),['pro','royal'],true))>Royal</option></select></div>
                        <label class="us-check"><input id="tplEditable" type="checkbox" @checked($template?->is_customer_editable ?? true)> Pelanggan boleh mengedit</label>
                        

                        

                    </div>
                </section>

                <section data-panel-section="cover" hidden>
                    <div class="us-h">Cover</div>
                    <div class="us-small" style="margin-bottom:8px">
                        Opening Cover = layar sebelum undangan dibuka. Sticky Desktop = panel tetap khusus desktop.
                    </div>
                    <div class="us-cover-mode-tabs">
                        <button type="button" class="active" data-cover-mode="opening">Opening</button>
                        <button type="button" data-cover-mode="sticky">Sticky Desktop</button>
                        <button type="button" data-cover-mode="layout" hidden>Layout</button>
                    </div>
<div class="us-responsive-card us-cover-layout-card" data-cover-card="layout" hidden>
                            <div class="us-h">Layout Undangan</div>
                            <div class="us-field"><label>Desktop</label>
                                <select id="desktopLayout">
                                    <option value="cover-left">Sampul Tetap Kiri + Isi Scroll Kanan</option>
                                    <option value="cover-right">Isi Scroll Kiri + Sampul Tetap Kanan</option>
                                    <option value="centered">Single Column Tengah</option>
                                </select>
                            </div>
                            <div class="us-field us-tech-setting" hidden><div class="us-field"><label>Lebar sampul desktop</label><input id="desktopCoverWidth" type="range" min="40" max="70" step="1"></div></div>
                            <div class="us-field us-tech-setting" hidden><label>Breakpoint mobile</label><select id="mobileBreakpoint"><option value="640">640 px</option><option value="768" selected>768 px</option><option value="900">900 px</option></select></div>
                            <div class="us-small">Desktop Sticky Cover tetap diam di sisi desktop, sementara isi undangan di sebelahnya yang di-scroll.</div>
                        </div>

                        <div class="us-responsive-card us-opening-cover-card" data-cover-card="opening">
                            <div class="us-h">Opening Cover</div>
                            <div class="us-small" style="margin-bottom:8px">Layar pertama sebelum isi undangan. Media dan posisi teks diatur terpisah agar hasil Mobile, Tablet, dan Desktop konsisten.</div>
                            <label class="us-check"><input id="openingCoverEnabled" type="checkbox" checked> Aktifkan Opening Cover</label>

                            <div class="us-cover-subtitle">1. Media</div>
                            <div class="us-cover-device-tabs" id="openingMediaDeviceTabs" hidden>
                                <button type="button" class="active" data-opening-media-device="mobile">HP</button>
                                <button type="button" data-opening-media-device="tablet">Tablet</button>
                                <button type="button" data-opening-media-device="desktop">Desktop</button>
                            </div>
                            <div id="openingMediaPreview" class="us-opening-media-preview mobile">
                                <div class="us-opening-media-empty">Belum ada media Opening Cover</div>
                            </div>
                            <input id="openingCoverFile" type="file" accept="image/png,image/jpeg,image/webp,image/gif,video/mp4,video/webm" hidden>
                            <button id="uploadOpeningCover" class="us-tool" type="button" style="width:100%;margin:8px 0">Upload / Ganti Media</button>
<div class="us-cover-simple-note">Default otomatis memenuhi layar dan berada di tengah. Geser gambar langsung untuk mengatur fokus, scroll mouse / pinch untuk zoom.</div>

                            <div class="us-grid2">
                                <div class="us-field us-tech-setting" hidden><label>Fit</label>
                                    <select id="openingMediaFit">
                                        <option value="cover">Penuhi layar (crop)</option>
                                        <option value="contain">Tampilkan utuh</option>
                                    </select>
                                </div>
                                <div class="us-field us-tech-setting" hidden><label>Zoom</label><input id="openingMediaScale" type="range" min="1" max="2.5" step="0.05" value="1"></div>
                            </div>
                            <div class="us-field us-tech-setting" hidden><label>Fokus Horizontal</label><input id="openingMediaX" type="range" min="0" max="100" step="1" value="50"></div>
                            <div class="us-field us-tech-setting" hidden><label>Fokus Vertikal</label><input id="openingMediaY" type="range" min="0" max="100" step="1" value="50"></div>
                            <button id="resetOpeningMedia" class="us-mini-tool" type="button">Auto / Tengah</button>

                            <div class="us-cover-subtitle">2. Teks & Tombol</div>
                            <div class="us-field"><label>Teks Atas</label><input id="openingCoverEyebrow" type="text" value="The Wedding of"></div>
                            <div class="us-field"><label>Nama Mempelai <span class="us-required">*</span></label><input id="openingCoverNames" type="text" value="Nama & Nama" placeholder="Nama & Nama"></div>
                            <label class="us-check"><input id="openingCoverBindNames" type="checkbox" checked> Hubungkan otomatis ke Nama Pasangan</label>
                            @if($isInstanceMode)<div class="us-role-data-note">Nama pasangan mengikuti <b>Data Undangan</b>. Ubah nama dari menu Data Undangan, bukan dari Cover.</div>@endif
                            <div class="us-field"><label>Teks Tombol</label><input id="openingCoverButtonText" type="text" value="Buka Undangan"></div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Warna Teks</label><input id="openingCoverTextColor" type="color" value="#ffffff"></div>
                                <div class="us-field"><label>Warna Tombol</label><input id="openingCoverButtonColor" type="color" value="#2F6FED"></div>
                            </div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Ukuran Nama</label><input id="openingCoverNameSize" type="range" min="24" max="84" step="1" value="46"></div>
                                <div class="us-field"><label>Posisi Vertikal Teks</label><input id="openingCoverY" type="range" min="15" max="85" step="1" value="50"></div>
                            </div>
                            <button id="centerOpeningCover" class="us-mini-tool" type="button">Teks ke Tengah</button>

                            <div class="us-cover-subtitle">3. Animasi</div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Animasi Text</label>
                                    <select id="openingCoverAnimation">
                                        <option value="fade-up">Fade Up</option>
                                        <option value="fade">Fade</option>
                                        <option value="zoom">Zoom</option>
                                        <option value="soft-scale">Soft Scale</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div class="us-field"><label>Durasi Masuk</label><input id="openingCoverDuration" type="number" min="0.2" max="3" step="0.1" value="0.8"></div>
                            </div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Animasi Cover</label>
                                    <select id="openingCoverExitAnimation">
                                        <option value="fade">Fade Out</option>
                                        <option value="slide-up">Slide Up</option>
                                        <option value="zoom-out">Zoom Out</option>
                                        <option value="blur-out">Blur Out</option>
                                        <option value="curtain-up">Curtain Up</option>
                                        <option value="none">Tanpa Animasi</option>
                                    </select>
                                </div>
                                <div class="us-field"><label>Durasi Buka</label><input id="openingCoverExitDuration" type="number" min="0.15" max="2" step="0.05" value="0.55"></div>
                            </div>
                            <button id="previewOpeningCover" class="us-tool us-tool-primary" type="button">▶ Preview Opening Cover</button>
                        </div>

                        <div class="us-responsive-card us-desktop-cover-card" data-cover-card="sticky" hidden>
                            <div class="us-h">Desktop Sticky Cover</div>
                            <div id="desktopCoverPreview" class="us-desktop-cover-preview"><div class="us-desktop-cover-empty">Belum ada media Desktop Sticky Cover</div></div>
                            <input id="desktopCoverFile" type="file" accept="image/png,image/jpeg,video/mp4" hidden>
                            <div id="desktopCoverDropzone" class="us-cover-dropzone" tabindex="0">
                                <div class="us-cover-dropzone-icon">▣</div>
                                <b>Upload Desktop Sticky Cover</b>
                                <span>Klik atau tarik JPG, PNG, MP4</span>
                                <div id="desktopCoverUploadProgress" class="us-cover-progress"><i></i></div>
                            </div>
                            <div class="us-cover-adjust-note">
                                Drag foto/video langsung untuk menggeser. Scroll mouse / pinch 2 jari untuk zoom.
                            </div>
                            <div class="us-cover-actions">
                                <div class="us-field us-cover-fit-field"><label>Fit</label><select id="desktopCoverFit"><option value="cover">Cover</option><option value="contain">Contain</option></select></div>
                                <button id="resetDesktopCover" class="us-tool" type="button">Reset Posisi</button>
                                <button id="removeDesktopCover" class="us-tool danger" type="button">Kosongkan</button>
                            </div>
                            <div class="us-small" style="margin-top:7px">JPG / PNG / MP4. MP4 selalu autoplay, loop, dan tanpa suara.</div>
                        </div>
                </section>


                <section data-panel-section="customer-data" hidden>
                    <div class="us-h">{{ $isInstanceMode ? 'Data Undangan' : 'Data Pelanggan Uji' }}</div>
                    <div class="us-small" style="margin-bottom:10px">{{ $isInstanceMode ? 'Isi data undangan. Elemen template yang terhubung akan mengikuti data ini secara otomatis.' : 'Gunakan panel ini untuk menguji binding tanpa mengubah master template.' }}</div>
                    <div class="us-customer-preview-card">
                            <div class="us-customer-preview-head">
                                <div class="us-small">{{ $isInstanceMode ? 'Data tersimpan ke undangan ini, bukan ke master template.' : 'Instance pelanggan untuk preview admin.' }}</div>
                                <span id="customerInstanceState" class="us-instance-state">Belum dibuat</span>
                            </div>

                            <div class="us-customer-grid">
                                <div class="us-customer-field"><label>Nama pria</label><input data-customer-key="groom_name" type="text"></div>
                                <div class="us-customer-field"><label>Nama wanita</label><input data-customer-key="bride_name" type="text"></div>
                                <div class="us-customer-field wide"><label>Nama pasangan</label><input data-customer-key="couple_names" type="text" @readonly($isInstanceMode)><div class="us-small" @if(!$isInstanceMode) hidden @endif>Otomatis dari nama pria & wanita.</div></div>
                                <div class="us-customer-field"><label>Tanggal acara</label><input data-customer-key="event_date" type="date"></div>
                                <div class="us-customer-field"><label>Lokasi</label><input data-customer-key="venue_name" type="text"></div>
                                <div class="us-customer-field wide"><label>Alamat lokasi</label><textarea data-customer-key="venue_address"></textarea></div>
                                <div class="us-customer-field wide"><label>Google Maps URL</label><input data-customer-key="maps_url" type="url"></div>
                                <div class="us-customer-field wide"><label>Opening</label><textarea data-customer-key="opening_text"></textarea></div>
                                <div class="us-customer-field wide"><label>Quote</label><textarea data-customer-key="quote"></textarea></div>
                                <div class="us-customer-field wide"><label>Doa</label><textarea data-customer-key="prayer"></textarea></div>
                                <div class="us-customer-field wide"><label>Closing</label><textarea data-customer-key="closing_text"></textarea></div>
                                <div class="us-customer-field wide"><label>Love story</label><textarea data-customer-key="story"></textarea></div>
                                <div class="us-customer-field wide"><label>Music URL</label><input data-customer-key="music_url" type="url"></div>
                                <div class="us-customer-field"><label>Bank / e-wallet</label><input data-customer-key="gift_bank" type="text"></div>
                                <div class="us-customer-field"><label>Nomor rekening</label><input data-customer-key="gift_number" type="text"></div>
                                <div class="us-customer-field wide"><label>Nama pemilik rekening</label><input data-customer-key="gift_name" type="text"></div>

                                <div class="us-customer-field"><label>Foto pria</label><select data-customer-media="groom_photo"></select></div>
                                <div class="us-customer-field"><label>Foto wanita</label><select data-customer-media="bride_photo"></select></div>
                                <div class="us-customer-field wide"><label>Foto pasangan</label><select data-customer-media="couple_photo"></select></div>
                                <div id="customerGalleryFields" class="us-customer-gallery-fields"></div>
                            </div>

                            <div class="us-customer-actions">
                                <button id="createCustomerPreview" class="us-tool" type="button">Buat Instance Uji</button>
                                <button id="resetCustomerPreview" class="us-tool danger" type="button">Reset Data Uji</button>
                            </div>
                            <div class="us-instance-note">Data ini hanya simulasi pelanggan. Perubahan di sini tidak mengubah sample content atau master template.</div>
                        </div>
                </section>

                <section data-panel-section="elements" hidden>
                    <div class="us-h">Elemen</div>
                    <div class="us-mobile-panel-search"><input id="elementSearch" type="search" placeholder="Cari elemen" aria-label="Cari elemen"></div>
                    <div class="us-element-tabs">
                        <button class="us-element-tab active" type="button" data-element-tab="shapes">Bentuk</button>
                        <button class="us-element-tab" type="button" data-element-tab="frames">Bingkai</button>
                        <button class="us-element-tab" type="button" data-element-tab="grids">Kisi</button>
                        <button class="us-element-tab" type="button" data-element-tab="components">Komponen</button>
                    </div>

                    <div class="us-element-group" data-element-group="shapes">
                        <div class="us-element-grid">
                            <button class="us-element-card" type="button" data-shape-kind="rect"><span class="us-element-preview"></span><span>Persegi</span></button>
                            <button class="us-element-card" type="button" data-shape-kind="rounded"><span class="us-element-preview rounded"></span><span>Rounded</span></button>
                            <button class="us-element-card" type="button" data-shape-kind="circle"><span class="us-element-preview circle"></span><span>Lingkaran</span></button>
                            <button class="us-element-card" type="button" data-shape-kind="oval"><span class="us-element-preview oval"></span><span>Oval</span></button>
                            <button class="us-element-card" type="button" data-shape-kind="line"><span class="us-element-preview line"></span><span>Garis</span></button>
                            <button class="us-element-card" type="button" data-shape-kind="triangle"><span class="us-element-preview triangle"></span><span>Segitiga</span></button>
                            <button class="us-element-card" type="button" data-shape-kind="star"><span class="us-element-preview star"></span><span>Bintang</span></button>
                            <button class="us-element-card" type="button" data-shape-kind="heart"><span class="us-element-preview heart"></span><span>Hati</span></button>
                            <button class="us-element-card" type="button" data-shape-kind="hex"><span class="us-element-preview hex"></span><span>Polygon</span></button>
                        </div>
                    </div>

                    <div class="us-element-group" data-element-group="frames" hidden>
                        <div class="us-element-grid">
                            <button class="us-element-card" type="button" data-frame-kind="square"><span class="us-frame-preview"></span><span>Square</span></button>
                            <button class="us-element-card" type="button" data-frame-kind="rounded"><span class="us-frame-preview rounded"></span><span>Rounded</span></button>
                            <button class="us-element-card" type="button" data-frame-kind="circle"><span class="us-frame-preview circle"></span><span>Circle</span></button>
                            <button class="us-element-card" type="button" data-frame-kind="portrait"><span class="us-frame-preview"></span><span>Portrait</span></button>
                            <button class="us-element-card" type="button" data-frame-kind="landscape"><span class="us-frame-preview" style="width:52px;height:34px"></span><span>Landscape</span></button>
                            <button class="us-element-card" type="button" data-frame-kind="arch"><span class="us-frame-preview arch"></span><span>Arch</span></button>
                            <button class="us-element-card" type="button" data-frame-kind="heart"><span class="us-frame-preview heart"></span><span>Heart</span></button>
                            <button class="us-element-card" type="button" data-frame-kind="polaroid"><span class="us-frame-preview" style="border-width:5px 5px 11px"></span><span>Polaroid</span></button>
                        </div>
                        <div class="us-media-help">Klik bingkai lalu pilih foto dari Unggahan, atau drag foto langsung ke bingkai.</div>
                    </div>

                    <div class="us-element-group" data-element-group="grids" hidden>
                        <div class="us-element-grid">
                            <button class="us-element-card" type="button" data-grid-kind="2h"><span class="us-grid-preview" style="grid-template-columns:1fr 1fr"><span></span><span></span></span><span>2 Kolom</span></button>
                            <button class="us-element-card" type="button" data-grid-kind="2v"><span class="us-grid-preview" style="grid-template-rows:1fr 1fr"><span></span><span></span></span><span>2 Baris</span></button>
                            <button class="us-element-card" type="button" data-grid-kind="3"><span class="us-grid-preview" style="grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr"><span style="grid-row:1/3"></span><span></span><span></span></span><span>3 Foto</span></button>
                            <button class="us-element-card" type="button" data-grid-kind="4"><span class="us-grid-preview" style="grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr"><span></span><span></span><span></span><span></span></span><span>4 Foto</span></button>
                            <button class="us-element-card" type="button" data-grid-kind="6"><span class="us-grid-preview" style="grid-template-columns:repeat(3,1fr);grid-template-rows:1fr 1fr"><span></span><span></span><span></span><span></span><span></span><span></span></span><span>6 Foto</span></button>
                            <button class="us-element-card" type="button" data-grid-kind="mosaic"><span class="us-grid-preview" style="grid-template-columns:1.2fr .8fr;grid-template-rows:1fr 1fr"><span style="grid-row:1/3"></span><span></span><span></span></span><span>Mosaic</span></button>
                        </div>
                        <div class="us-media-help">Setiap cell bisa diisi foto berbeda. Klik cell untuk crop, zoom dan reposisi fotonya.</div>
                    </div>

                    <div class="us-element-group" data-element-group="components" hidden>
                        <div id="mobileElementComponents" class="us-mobile-element-components"></div>
                    </div>
                </section>

                
                <section data-panel-section="components" hidden>
                    <div class="us-h">Komponen Undangan</div>
                    <div class="us-small" style="margin-bottom:10px">Tambahkan blok siap pakai. Desain tetap bisa kamu atur visual tanpa coding.</div>
                    <div class="us-component-grid">
                        <button class="us-component-card" type="button" data-component="couple"><span class="us-component-icon">♡</span><b>Mempelai</b><span>Nama + foto pasangan</span></button>
                        <button class="us-component-card" type="button" data-component="quote"><span class="us-component-icon">“</span><b>Quote</b><span>Teks bawaan, pelanggan bisa ganti</span></button>
                        <button class="us-component-card" type="button" data-component="prayer"><span class="us-component-icon">✦</span><b>Doa</b><span>Konten doa editable</span></button>
                        <button class="us-component-card" type="button" data-component="event"><span class="us-component-icon">◷</span><b>Acara</b><span>Tanggal, jam, tempat</span></button>
                        <button class="us-component-card" type="button" data-component="gallery"><span class="us-component-icon">▦</span><b>Galeri</b><span>Kisi foto + animasi</span></button>
                        <button class="us-component-card" type="button" data-component="story"><span class="us-component-icon">⌁</span><b>Love Story</b><span>Judul + cerita</span></button>
                        <button class="us-component-card" type="button" data-component="rsvp"><span class="us-component-icon">✓</span><b>RSVP</b><span>Placeholder komponen RSVP</span></button>
                        <button class="us-component-card" type="button" data-component="gift"><span class="us-component-icon">□</span><b>Gift</b><span>Placeholder hadiah/rekening</span></button>
                        <button class="us-component-card" type="button" data-component="location"><span class="us-component-icon">⌖</span><b>Lokasi</b><span>Nama tempat + maps</span></button>
                        <button class="us-component-card" type="button" data-component="countdown"><span class="us-component-icon">◷</span><b>Countdown</b><span>Hitung mundur ke hari acara</span></button>
                        <button class="us-component-card" type="button" data-component="wishes"><span class="us-component-icon">♡</span><b>Wishes</b><span>Daftar ucapan tamu</span></button>
                        <button class="us-component-card" type="button" data-component="music"><span class="us-component-icon">♫</span><b>Music</b><span>Kontrol musik undangan</span></button>
                        <button class="us-component-card" type="button" data-component="guest_photo"><span class="us-component-icon">▣</span><b>Guest Photo</b><span>Upload momen dari tamu</span></button>
                        <button class="us-component-card" type="button" data-component="closing"><span class="us-component-icon">∞</span><b>Penutup</b><span>Ucapan akhir undangan</span></button>
                    </div>
                    <div class="us-component-note">Komponen fungsional memakai data production saat undangan terhubung: RSVP, Gift, Maps, Countdown, Wishes, Music dan Guest Photo.</div>
                </section>

<section data-panel-section="text" hidden>
                    <div class="us-h">Teks</div>
                    <div class="us-mobile-panel-search"><input id="textFontSearch" type="search" placeholder="Cari font" aria-label="Cari font"><div id="textFontResults" class="us-mobile-font-results" hidden></div></div>
                    <div class="us-mobile-text-presets">
                        <button class="us-tool" id="addText" type="button">Tambahkan teks</button>
                        <button class="us-tool" type="button" data-text-preset="title">Tambahkan judul</button>
                        <button class="us-tool" type="button" data-text-preset="subtitle">Tambahkan subjudul</button>
                        <button class="us-tool" type="button" data-text-preset="paragraph">Tambahkan paragraf</button>
                    </div>
                    <div id="textFields">
                        <div class="us-field"><label>Isi Teks</label><textarea id="pText" rows="3" placeholder="Tulis teks"></textarea></div>
                        <div class="us-field"><label>Font</label><select id="pFont"></select></div>
                        <div class="us-grid2">
                            <div class="us-field"><label>Ukuran Font</label><input id="pFontSize" type="number" min="6" max="240" step="1"></div>
                            <div class="us-field"><label>Warna</label><input id="pColor" type="color"><div class="us-color-live-note">Perubahan warna realtime.</div></div>
                        </div>
                        <div class="us-field"><label>Perataan</label><select id="pAlign"><option value="left">Kiri</option><option value="center">Tengah</option><option value="right">Kanan</option></select></div>
                        <div class="us-field">
                            <label>Posisi Otomatis</label>
                            <div class="us-position-tools">
                                <button id="centerTextXY" class="us-mini-tool" type="button" title="Posisikan tepat di tengah canvas">Tengah</button>
                                <button id="alignTextCenter" class="us-mini-tool" type="button" title="Sejajarkan posisi elemen ke garis tengah canvas">Sejajar</button>
                            </div>
                        </div>
                        <button id="openFontLibrary" class="us-tool" type="button" style="width:100%;margin-top:2px">Kelola Font</button>
                        <div id="textPanelHint" class="us-small" style="margin-top:8px">Pilih layer teks untuk mengubah font, ukuran, warna, dan isi.</div>
                    </div>
                </section>

                <section data-panel-section="uploads" hidden>
                    <div class="us-h">Media</div>
                    <div class="us-mobile-section-label">Upload file</div>
                    <div class="us-upload" style="padding:0;border:0;background:transparent">
                        <input id="assetFile" type="file" accept="image/png,image/jpeg,image/webp,image/gif,video/mp4,video/webm" hidden>
                        <div id="assetDropzone" class="us-dropzone" tabindex="0">
                            <div class="us-dropzone-icon">⇧</div>
                            <b>Upload foto atau video</b>
                            <span>JPG, PNG, WebP, GIF, MP4, WebM</span>
                            <div id="assetUploadProgress" class="us-upload-progress"><i></i></div>
                        </div>
                        <button id="assetUploadBtn" class="us-tool" style="width:100%;margin-top:8px;display:none" type="button">Upload</button>
                    </div>
                    <div class="us-mobile-section-label us-mobile-media-recent">Media terbaru</div>
                    <div id="assetGrid" class="us-asset-grid"></div>

                </section>

                @if($isInstanceMode)
                <section data-panel-section="gallery" hidden>
                    <div class="us-premium-gallery-panel-head">
                        <div>
                            <div class="us-h">Galeri Undangan</div>
                            <div class="us-small">Atur foto galeri yang digunakan oleh template undangan.</div>
                        </div>
                        <span id="premiumGalleryCount" class="us-gallery-count">0 / 30</span>
                    </div>

                    <div class="us-premium-gallery us-premium-gallery-standalone">
                        <div id="premiumGalleryList" class="us-premium-gallery-list"></div>

                        <button id="premiumGalleryPickerToggle" class="us-tool" type="button" style="width:100%;margin-top:10px">
                            + Tambah dari Unggahan
                        </button>

                        <div id="premiumGalleryPicker" class="us-premium-gallery-picker" hidden>
                            <div class="us-small">Pilih foto dari Unggahan. File yang sama tidak ditambahkan dua kali.</div>
                            <div id="premiumGalleryAssetGrid" class="us-premium-gallery-assets"></div>
                        </div>

                        <div id="premiumGallerySaveState" class="us-premium-gallery-state">Siap</div>
                    </div>
                </section>
                @endif

                <section data-panel-section="fonts" hidden>
                    <div class="us-h">Font</div>
                    <div id="fontCurrentPreview" class="us-font-current">Pilih layer teks untuk mengganti font secara realtime.</div>
                    <input id="fontFile" type="file" accept=".woff2,.woff,.ttf,.otf" hidden>
                    <input id="fontName" type="text" placeholder="Nama font (otomatis dari file)" style="margin-bottom:8px">
                    <div id="fontDropzone" class="us-font-dropzone" tabindex="0">
                        <div class="us-font-dropzone-icon">Aa</div>
                        <b>Upload Font</b>
                        <span>Klik atau tarik WOFF2, WOFF, TTF, OTF</span>
                        <div id="fontUploadProgress" class="us-font-upload-progress"><i></i></div>
                    </div>
                    <button id="fontUploadBtn" type="button" hidden>Upload</button>
                    <div class="us-small" style="margin-top:8px">Klik font di daftar untuk langsung menerapkannya ke teks yang sedang dipilih.</div>
                    <div id="fontList" class="us-font-list" style="margin-top:9px"></div>
                </section>

                <section data-panel-section="layers" hidden>
                    <div class="us-h">Susunan Elemen</div><div id="layerList" class="us-list"></div>
                </section>

                <section data-panel-section="animation" hidden>
                    <div class="us-h">Animasi</div>

                    <details class="us-animation-accordion" open>
                        <summary>
                            <span>Halaman</span>
                            <small id="canvasAnimationHint">Halaman aktif • None</small>
                        </summary>
                        <div class="us-animation-accordion-body">
                            <div class="us-animation-preset-grid">
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="none">None</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="fade">Fade</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="fade-soft">Fade Soft</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="slide-up">Slide Up</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="slide-down">Slide Down</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="slide-left">Slide Left</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="slide-right">Slide Right</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="zoom">Zoom</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="blur">Blur</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="focus-in">Focus In</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="wipe-up">Wipe Up</button>
                                <button class="us-animation-preset" type="button" data-canvas-animation-preset="wipe-left">Wipe Left</button>
                            </div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Durasi</label><input id="pageDuration" type="number" min="0.1" max="5" step="0.1"></div>
                                <div class="us-field"><label>Easing</label><select id="pageEasing"><option value="ease">Ease</option><option value="ease-in">Ease In</option><option value="ease-out">Ease Out</option><option value="ease-in-out">Ease In Out</option><option value="linear">Linear</option></select></div>
                            </div>
                            <div class="us-small">Canvas preview selalu berjalan sekali. Tidak ada loop untuk Canvas.</div>
                        </div>
                    </details>

                    <details class="us-animation-accordion" open>
                        <summary>
                            <span>Elemen</span>
                            <small id="animationSelectedHint">Pilih layer di canvas.</small>
                        </summary>
                        <div class="us-animation-accordion-body">
                            <div class="us-animation-preset-grid">
                                <button class="us-animation-preset" type="button" data-element-animation-preset="none">None</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="fade">Fade</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="fade-up">Fade Up</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="fade-down">Fade Down</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="fade-left">Fade Left</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="fade-right">Fade Right</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="zoom">Zoom</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="pop">Pop</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="soft-scale">Soft Scale</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="blur-in">Blur In</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="focus-in">Focus In</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="reveal-up">Reveal Up</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="wipe-up">Wipe Up</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="wipe-left">Wipe Left</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="flash">Flash</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="flicker">Flicker</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="breathe">Breathe</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="glow-pulse">Glow Pulse</button>
                                <button class="us-animation-preset" type="button" data-element-animation-preset="float">Float</button>
                            </div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Trigger</label><select id="animTrigger"><option value="on-enter">Saat masuk layar</option><option value="on-load">Saat halaman dibuka</option><option value="after-previous">Setelah layer sebelumnya</option><option value="with-previous">Bersamaan layer sebelumnya</option></select></div>
                                <div class="us-field"><label>Easing</label><select id="animEasing"><option value="ease-out">Ease Out</option><option value="ease-in-out">Ease In Out</option><option value="linear">Linear</option><option value="cubic-bezier(.2,.8,.2,1)">Smooth</option></select></div>
                            </div>
                            <label class="us-check us-element-loop-control"><input id="elementAnimationLoop" type="checkbox"> Loop animasi elemen di undangan publik</label>
                            <div class="us-small">Preview di editor tetap 1x meskipun Loop aktif.</div>
                        </div>
                    </details>

                    <input id="pageName" type="text" hidden>
                    <select id="pageRole" hidden><option value="hero">Hero</option><option value="section">Section</option></select>
                    <div id="pageDeviceNote" hidden></div>
                    <div class="us-field"><label>Warna Halaman</label><input id="canvasBg" type="color"></div>
                    <div class="us-responsive-card">
                        <div class="us-h">Ukuran Halaman</div>
                        <div class="us-grid2">
                            <div class="us-field"><label>Lebar desain</label><input id="pageWidth" type="number" min="280" max="1920" step="1"></div>
                            <div class="us-field"><label>Tinggi desain</label><input id="pageHeight" type="number" min="300" max="2200" step="1"></div>
                        </div>
                        <button id="applyRecommendedSize" class="us-tool" type="button" style="width:100%">Gunakan rasio HP 390×844</button>
                    </div>
                    <select id="pageTransition" hidden>
                        <option value="none">None</option><option value="fade">Fade</option><option value="fade-soft">Fade Soft</option>
                        <option value="slide-up">Slide Up</option><option value="slide-down">Slide Down</option>
                        <option value="slide-left">Slide Left</option><option value="slide-right">Slide Right</option>
                        <option value="zoom">Zoom</option><option value="blur">Blur</option><option value="focus-in">Focus In</option>
                        <option value="wipe-up">Wipe Up</option><option value="wipe-left">Wipe Left</option>
                    </select>
                    <button id="duplicatePage" type="button" hidden></button>
                    <button id="deletePage" type="button" hidden></button>
                </section>
            </div>
        </aside>

        <main class="us-workspace">
            <div class="us-contextbar">
                <button id="prevCanvas" class="us-btn" type="button">←</button>
                <span id="canvasCounter" class="us-small"></span>
                <button id="nextCanvas" class="us-btn" type="button">→</button>
                <button id="previewTransition" class="us-btn" type="button">▶ Transisi</button><button id="cropDoneBtn" class="us-btn us-crop-action" type="button">Selesai crop</button><button id="cropResetBtn" class="us-btn us-crop-action" type="button">Reset crop</button>
                <div class="us-view-modes" title="Cara melihat isi template">
                    <button class="us-view-mode active" type="button" data-view-mode="sample">Desain Template</button>
                    <button class="us-view-mode" type="button" data-view-mode="empty">Preview Kosong</button>
                    <button class="us-view-mode" type="button" data-view-mode="customer">Preview Data Uji</button>
                </div>
                <button id="toggleSnap" class="us-btn active" type="button" aria-pressed="true">Ratakan</button>
<button id="toggleSafeArea" class="us-btn active" type="button" aria-pressed="true">Batas Aman</button>
            <div class="us-device-switch us-editor-device-switch" title="Mode layout yang sedang diedit">
                    <button class="us-device-btn active" type="button" data-editor-device="mobile">HP</button>
                    <button class="us-device-btn" type="button" data-editor-device="tablet">Tablet</button>
                    <button class="us-device-btn" type="button" data-editor-device="desktop">Desktop</button>
                </div>
                
            </div>
            <div id="desktopContextToolbar" class="us-desktop-context-toolbar" hidden aria-label="Alat elemen terpilih"></div>
            <div id="canvasZone" class="us-canvas-zone">
                <div id="editorDocument" class="us-editor-document mobile" data-device="mobile">
                    <aside id="editorStickyCover" class="us-editor-sticky-cover" hidden></aside>
                    <div class="us-editor-document-main">
                        <div id="editorDeviceHint" class="us-device-stage-label">HP · 390 × 844</div>
                        <div id="livePages" class="us-live-pages" aria-label="Continuous canvas workspace"></div>
                    </div>
                </div>
            </div>
            <div id="pagesPanel" class="us-pages-wrap"><div id="pagesBar" class="us-pages"></div></div>
            <button id="togglePagesPanel" class="us-pages-toggle" type="button" aria-expanded="true" title="Buka atau tutup daftar halaman">Halaman ▾</button>
            <div class="us-work-bottom">
                <div class="us-zoom-simple" aria-label="Zoom editor">
                    <button id="zoomOutBtn" type="button" class="us-zoom-btn" title="Perkecil">−</button>
                    <button id="zoomFitBtn" type="button" class="us-zoom-fit" title="Sesuaikan halaman ke ruang editor">Fit</button>
                    <span id="zoomValue">68%</span>
                    <button id="zoomInBtn" type="button" class="us-zoom-btn" title="Perbesar">＋</button>
                    <input id="zoomRange" type="range" min="25" max="120" value="68" hidden>
                </div>
                <span>Halaman</span><span id="bottomCounter">—</span>
                <button id="pageGridBtn" class="us-grid-view-btn" type="button" aria-label="Lihat semua halaman">▦</button>
            </div>
        </main>

        <button id="toggleProperties" class="us-properties-collapse" type="button" aria-label="Buka atau tutup panel properti">›</button>

        <aside class="us-properties-panel us-mobile-sheet-surface">
            <div class="us-properties-head"><b>{{ $isInstanceMode ? 'Properti Desain' : 'Properti Admin' }}</b><span class="us-properties-role">{{ ucfirst($studioUiRole) }}</span><button id="mobileCloseProps" class="us-mobile-sheet-close" type="button" aria-label="Tutup properti">✕</button></div>
            <div id="propertiesEmpty" class="us-small">Pilih elemen di canvas untuk mengedit propertinya.</div>
            <div id="properties" hidden>
                <div class="us-field"><label>Nama layer</label><input id="pName" type="text"></div>
                <div class="us-rule-box">
                    <div id="multiTools" class="us-step7-tools" hidden>
                    <div class="us-h">Atur Bersama</div>
                    <div class="us-align-grid">
                        <button class="us-mini-tool" data-align-action="left" type="button">Kiri</button>
                        <button class="us-mini-tool" data-align-action="center-x" type="button">Tengah X</button>
                        <button class="us-mini-tool" data-align-action="right" type="button">Kanan</button>
                        <button class="us-mini-tool" data-align-action="top" type="button">Atas</button>
                        <button class="us-mini-tool" data-align-action="center-y" type="button">Tengah Y</button>
                        <button class="us-mini-tool" data-align-action="bottom" type="button">Bawah</button>
                    </div>
                    <div class="us-align-grid us-align-grid-2">
                        <button class="us-mini-tool" id="distributeX" type="button">Sebar Horizontal</button>
                        <button class="us-mini-tool" id="distributeY" type="button">Sebar Vertikal</button>
                    </div>
                    <div class="us-align-grid us-align-grid-2">
                        <button class="us-mini-tool" id="groupSelection" type="button">Group</button>
                        <button class="us-mini-tool" id="ungroupSelection" type="button">Ungroup</button>
                    </div>
                </div>
                <div id="responsiveRulesTools" class="us-step8-tools">
<div class="us-h">Responsif & Batas Aman</div>
<div class="us-check"><input id="safeAreaEnabled" type="checkbox" checked><span>Tampilkan Batas Aman</span></div>
<div class="us-grid2">
<div class="us-field"><label>Margin Aman</label><input id="safeAreaMargin" type="number" min="0" max="80" step="1" value="20"></div>
<div class="us-field"><label>Responsif</label><select id="responsiveMode"><option value="scale">Scale</option><option value="fixed">Fixed</option></select></div>
</div>
<div class="us-check"><input id="keepInsideCanvas" type="checkbox" checked><span>Jaga elemen di dalam halaman</span></div>
<div class="us-check"><input id="respectSafeArea" type="checkbox"><span>Batasi ke Batas Aman</span></div>
<button id="fitSelectionSafe" class="us-mini-tool" type="button">Masukkan Elemen ke Batas Aman</button>
</div>
<div id="layerArrangeTools" class="us-step7-tools">
                    <div class="us-h">Urutan Layer</div>
                    <div class="us-align-grid us-align-grid-2">
                        <button class="us-mini-tool" id="bringForward" type="button">Naik 1</button>
                        <button class="us-mini-tool" id="sendBackward" type="button">Turun 1</button>
                        <button class="us-mini-tool" id="bringFront" type="button">Paling Depan</button>
                        <button class="us-mini-tool" id="sendBack" type="button">Paling Belakang</button>
                    </div>
                </div>
                <div class="us-admin-template-properties">
                    <div class="us-h">Data & Permission Template</div>
                    <div id="layerBindingField" class="us-field"><label>Hubungkan ke Data</label><select id="pBinding"></select></div>
                    <div class="us-field"><label>Pelanggan boleh</label>
                        <select id="pCustomerEditPolicy">
                            <option value="full">Edit penuh</option>
                            <option value="content">Hanya ganti isi</option>
                            <option value="locked">Tidak bisa diedit</option>
                        </select>
                    </div>
                    <label class="us-check"><input id="pOptional" type="checkbox"> Bagian boleh disembunyikan pelanggan</label>
                    <label class="us-check"><input id="pHideWhenEmpty" type="checkbox"> Sembunyikan otomatis bila data kosong</label>
                    <div id="bindingHint" class="us-small" style="margin-top:7px"></div>
                </div>
                </div>

                
                <div id="mediaFields" hidden>
                    <div id="frameKindFields" class="us-field" hidden><label>Bentuk foto / frame</label>
                        <select id="pFrameKind">
                            <option value="square">Square</option>
                            <option value="rounded">Rounded</option>
                            <option value="circle">Circle</option>
                            <option value="portrait">Portrait</option>
                            <option value="landscape">Landscape</option>
                            <option value="arch">Arch</option>
                            <option value="heart">Heart</option>
                            <option value="polaroid">Polaroid</option>
                        </select>
                    </div>
                    <div class="us-field"><label>Kotak aktif</label><select id="pMediaCell"></select></div>
                    <div id="cellBindingField" class="us-field us-admin-template-properties" hidden>
                        <label>Hubungkan kotak ini ke data</label>
                        <select id="pCellBinding"></select>
                        <div id="cellBindingHint" class="us-small" style="margin-top:5px">Klik kotak pada kisi, lalu pilih foto pelanggan untuk kotak tersebut.</div>
                    </div>
                    <div class="us-grid2"><div class="us-field"><label>Zoom foto</label><input id="pMediaZoom" type="number" min="0.5" max="4" step="0.05"></div><div class="us-field"><label>Gap</label><input id="pGridGap" type="number" min="0" max="60" step="1"></div></div>
                    <div class="us-grid2"><div class="us-field"><label>Posisi X</label><input id="pMediaX" type="number" min="-100" max="100" step="1"></div><div class="us-field"><label>Posisi Y</label><input id="pMediaY" type="number" min="-100" max="100" step="1"></div></div>
                    <button id="clearMediaCell" class="us-btn" type="button" style="width:100%">Kosongkan cell</button>
                    <div id="gallerySettings" class="us-gallery-settings">
                        <div class="us-responsive-card">
                            <div class="us-h">Galeri Pelanggan</div>
                            <div class="us-field"><label>Animasi buka</label>
                                <select id="pGalleryAnimation">
                                    <option value="fade">Fade</option>
                                    <option value="zoom">Zoom</option>
                                    <option value="slide">Slide Up</option>
                                    <option value="blur">Blur Fade</option>
                                    <option value="pulse">Soft Scale</option>
                                    <option value="none">Tanpa animasi</option>
                                </select>
                            </div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Autoplay</label><select id="pGalleryAutoplay"><option value="0">Off</option><option value="1">On</option></select></div>
                                <div class="us-field"><label>Interval</label><input id="pGalleryInterval" type="number" min="2" max="12" step=".5"></div>
                            </div>
                            <label class="us-check"><input id="pGalleryLightbox" type="checkbox"> Klik foto membuka lightbox</label>
                            <label class="us-check"><input id="pGalleryThumbnails" type="checkbox"> Tampilkan thumbnail/navigasi</label>
                            <button id="previewGalleryAnimation" class="us-btn" type="button" style="width:100%;margin-top:7px">▶ Preview animasi galeri</button>
                        </div>
                    </div>
                    <div class="us-media-help">Klik kotak pada kisi untuk memilihnya. Isi foto manual dari Unggahan atau hubungkan tiap kotak ke data pelanggan yang berbeda.</div>
                </div>
                <div class="us-grid2"><div class="us-field"><label>X</label><input id="pX" type="number"></div><div class="us-field"><label>Y</label><input id="pY" type="number"></div></div>
                <div class="us-grid2"><div class="us-field"><label>Width</label><input id="pW" type="number"></div><div class="us-field"><label>Height</label><input id="pH" type="number"></div></div>
                <div class="us-grid2"><div class="us-field"><label>Rotate</label><input id="pRot" type="number"></div><div class="us-field"><label>Opacity</label><input id="pOpacity" type="number" min="0" max="1" step="0.05"></div></div>
                <div class="us-grid2"><div id="elementColorField" class="us-field"><label>Warna</label><input id="pBg" type="color"><div class="us-color-live-note">Warna elemen berubah realtime.</div></div><div class="us-field"><label>Radius</label><input id="pRadius" type="number"></div></div>
                <div class="us-field"><label>Animasi Layer</label><select id="pAnimation">
<option value="none">None</option><option value="fade">Fade</option><option value="fade-up">Fade Up</option><option value="fade-down">Fade Down</option><option value="fade-left">Fade Left</option><option value="fade-right">Fade Right</option>
<option value="zoom">Zoom</option><option value="pop">Pop</option><option value="soft-scale">Soft Scale</option><option value="blur-in">Blur In</option><option value="focus-in">Focus In</option><option value="reveal-up">Reveal Up</option>
<option value="wipe-up">Wipe Up</option><option value="wipe-left">Wipe Left</option><option value="flash">Flash</option><option value="flicker">Flicker</option><option value="breathe">Breathe</option><option value="glow-pulse">Glow Pulse</option><option value="float">Float</option>
</select></div>
                <div class="us-grid2"><div class="us-field"><label>Duration</label><input id="pDuration" type="number" step="0.1"></div><div class="us-field"><label>Delay</label><input id="pDelay" type="number" step="0.1"></div></div>
                <label class="us-check"><input id="pLoop" type="checkbox"> Loop animation</label><label class="us-check"><input id="pLocked" type="checkbox"> Lock</label><label class="us-check"><input id="pHidden" type="checkbox"> Hide</label>
                <div class="us-page-actions" style="margin-top:10px"><button id="bringFrontSecondary" class="us-btn" type="button">Ke Depan</button><button id="sendBackSecondary" class="us-btn" type="button">Ke Belakang</button><button id="duplicateLayer" class="us-btn" type="button">Duplicate</button><button id="clearSampleContent" class="us-btn" type="button">Kosongkan Contoh</button><button id="deleteLayer" class="us-btn danger" type="button">Delete</button></div>
            </div>
        </aside>

        
        <div id="responsivePreview" class="us-preview-overlay" aria-hidden="true">
            <div class="us-preview-top">
                <b>Pratinjau Perangkat</b>
                <span id="responsivePreviewLabel" class="us-small"></span>
                <span class="spacer"></span>
                <button id="previewMobileBtn" class="us-device-btn" type="button">HP</button>
                <button id="previewTabletBtn" class="us-device-btn" type="button">Tablet</button>
                <button id="previewDesktopBtn" class="us-device-btn" type="button">Desktop</button>
                <button id="closeResponsivePreview" class="us-preview-close" type="button">Tutup</button>
            </div>
            <div id="responsivePreviewStage" class="us-preview-stage"></div>
        </div>

<nav class="us-mobile-nav" aria-label="Tools mobile">
            <button data-panel-target="cover" type="button"><span class="ico">▣</span>Cover</button>
            <button data-panel-target="elements" type="button"><span class="ico">✦</span>Elemen</button>
            <button data-panel-target="animation" type="button"><span class="ico">◌</span>Animasi</button>
            <button data-panel-target="text" type="button"><span class="ico">T</span>Teks</button>
            <button data-panel-target="fonts" type="button"><span class="ico">Aa</span>Font</button>
            <button data-panel-target="uploads" type="button"><span class="ico">☁</span>Unggahan</button>
            @if($isInstanceMode)
            <button data-panel-target="gallery" type="button"><span class="ico">▦</span>Galeri</button>
            @endif
            <button data-panel-target="components" type="button"><span class="ico">◇</span>Komponen</button>
            <button data-panel-target="layers" type="button"><span class="ico">☷</span>Layer</button>

            <button data-panel-target="template" type="button" @if($isInstanceMode) style="display:none" @endif><span class="ico">▦</span>Template</button>
            <button data-panel-target="customer-data" type="button" @if($isInstanceMode) style="display:none" @endif><span class="ico">♙</span>{{ $isInstanceMode ? 'Data Undangan' : 'Data Uji' }}</button>
            <button id="mobileProps" type="button"><span class="ico">⚙</span>Properti</button>
        </nav>

        <div id="transitionPopover" class="us-transition-popover">
            <div class="us-transition-popover-title">Transisi antar canvas</div>
            <div class="us-transition-options">
                <button class="us-transition-option" data-quick-transition="none" type="button">None</button>
                <button class="us-transition-option" data-quick-transition="fade" type="button">Fade</button>
                <button class="us-transition-option" data-quick-transition="slide-up" type="button">Slide Up</button>
                <button class="us-transition-option" data-quick-transition="slide-left" type="button">Slide Left</button>
                <button class="us-transition-option" data-quick-transition="zoom" type="button">Zoom</button>
                <button class="us-transition-option" data-quick-transition="blur" type="button">Blur Fade</button>
            </div>
        </div>

        <div id="cellMediaContextMenu" class="us-context-menu us-cell-context-menu" role="menu">
            <button type="button" data-cell-menu-action="copy"><span>Salin gambar</span><kbd>Ctrl+C</kbd></button>
            <button type="button" data-cell-menu-action="paste"><span>Tempel gambar</span><kbd>Ctrl+V</kbd></button>
            <button type="button" data-cell-menu-action="duplicate"><span>Duplikat gambar</span><kbd>Ctrl+D</kbd></button>
            <hr>
            <button type="button" data-cell-menu-action="detach"><span>Pisahkan gambar</span><small>Keluarkan dari kisi</small></button>
            <button type="button" class="danger" data-cell-menu-action="delete"><span>Hapus gambar</span><kbd>Delete</kbd></button>
        </div>

        <div id="elementContextMenu" class="us-context-menu us-element-context-menu" role="menu" aria-label="Menu elemen">
            <button type="button" data-element-menu-action="copy"><span>Salin</span><kbd>Ctrl+C</kbd></button>
            <button type="button" data-element-menu-action="paste"><span>Tempel</span><kbd>Ctrl+V</kbd></button>
            <button type="button" data-element-menu-action="duplicate"><span>Duplikat</span><kbd>Ctrl+D</kbd></button>
            <hr>
            <div class="us-element-menu-label">Lapisan</div>
            <div class="us-element-menu-grid">
                <button type="button" data-element-layer="forward">Maju</button>
                <button type="button" data-element-layer="backward">Mundur</button>
                <button type="button" data-element-layer="front">Paling depan</button>
                <button type="button" data-element-layer="back">Paling belakang</button>
            </div>
            <div class="us-element-align-section" hidden>
                <hr>
                <div class="us-element-menu-label" data-element-align-label>Rata elemen</div>
                <div class="us-element-menu-grid">
                    <button type="button" data-element-align="left">Kiri</button>
                    <button type="button" data-element-align="center-x">Tengah H</button>
                    <button type="button" data-element-align="right">Kanan</button>
                    <button type="button" data-element-align="top">Atas</button>
                    <button type="button" data-element-align="center-y">Tengah V</button>
                    <button type="button" data-element-align="bottom">Bawah</button>
                </div>
            </div>
            <hr>
            <button type="button" data-element-menu-action="lock"><span data-element-lock-label>Kunci</span></button>
            <button type="button" class="danger" data-element-menu-action="delete"><span>Hapus</span><kbd>Delete</kbd></button>
        </div>

        <div id="pageContextMenu" class="us-context-menu" role="menu">
            <button type="button" data-page-action="copy"><span>Salin halaman</span><kbd>Ctrl+C</kbd></button>
            <button type="button" data-page-action="paste"><span>Tempel halaman</span><kbd>Ctrl+V</kbd></button>
            <button type="button" data-page-action="duplicate"><span>Duplikatkan halaman</span><kbd>Ctrl+D</kbd></button>
            <hr>
            <button type="button" data-page-action="add"><span>Tambah halaman</span><kbd>Ctrl+Enter</kbd></button><button type="button" data-page-action="add-desktop-cover"><span>Tambah Desktop Cover</span></button>
            <button type="button" data-page-action="rename"><span>Ganti nama halaman</span></button>
            <button type="button" class="danger" data-page-action="delete"><span>Hapus halaman</span><kbd>Delete</kbd></button>
        </div>

        <div id="pageGridOverlay" class="us-page-grid-overlay" aria-hidden="true">
            <div class="us-page-grid-panel">
                <div class="us-page-grid-head"><b>Semua halaman</b><button id="pageGridClose" class="us-page-grid-close" type="button">✕</button></div>
                <div id="pageGrid" class="us-page-grid"></div>
            </div>
        </div>
    </div>
</div>
<div id="toast" class="us-toast"></div>

<script>
(() => {
    const params = new URLSearchParams(location.search);
    if (params.get('studio_diag') !== '1') return;

    const startedAt = performance.now();
    const diag = window.__UNDANGANTA_DIAG__ = {
        version: 'runtime-audit-only-v1',
        url: location.href,
        userAgent: navigator.userAgent,
        viewport: {width: innerWidth, height: innerHeight, dpr: devicePixelRatio},
        startedAt: new Date().toISOString(),
        errors: [], rejections: [], consoleErrors: [], resources: [],
        events: [], mutations: [], snapshots: [], longTasks: [], notes: []
    };

    const slim = v => {
        try {
            if (v instanceof Error) return {name:v.name,message:v.message,stack:v.stack};
            if (typeof v === 'string') return v.slice(0,1000);
            return JSON.parse(JSON.stringify(v));
        } catch (_) { return String(v).slice(0,1000); }
    };

    window.addEventListener('error', e => {
        diag.errors.push({
            t: Math.round(performance.now()-startedAt),
            message:e.message, filename:e.filename, lineno:e.lineno, colno:e.colno,
            error:slim(e.error)
        });
    }, true);

    window.addEventListener('unhandledrejection', e => {
        diag.rejections.push({t:Math.round(performance.now()-startedAt),reason:slim(e.reason)});
    }, true);

    const originalConsoleError = console.error.bind(console);
    console.error = (...args) => {
        diag.consoleErrors.push({t:Math.round(performance.now()-startedAt),args:args.map(slim)});
        originalConsoleError(...args);
    };

    if ('PerformanceObserver' in window) {
        try {
            new PerformanceObserver(list => {
                for (const e of list.getEntries()) {
                    diag.resources.push({
                        name:e.name,initiatorType:e.initiatorType,
                        duration:Math.round(e.duration),transferSize:e.transferSize||0
                    });
                }
            }).observe({type:'resource',buffered:true});
        } catch (_) {}
        try {
            new PerformanceObserver(list => {
                for (const e of list.getEntries()) {
                    diag.longTasks.push({t:Math.round(e.startTime),duration:Math.round(e.duration)});
                }
            }).observe({type:'longtask',buffered:true});
        } catch (_) {}
    }

    let mutationCount=0, mutationWindowStart=performance.now();
    const mo=new MutationObserver(list=>{
        mutationCount+=list.length;
        const now=performance.now();
        if(now-mutationWindowStart>=1000){
            diag.mutations.push({t:Math.round(now-startedAt),perSecond:mutationCount});
            mutationCount=0;mutationWindowStart=now;
        }
    });
    mo.observe(document.documentElement,{subtree:true,childList:true,attributes:true});

    const eventNames=['click','pointerdown','pointermove','pointerup','wheel'];
    const eventCounts=Object.fromEntries(eventNames.map(n=>[n,0]));
    eventNames.forEach(type=>{
        document.addEventListener(type,e=>{
            eventCounts[type]++;
            if(type!=='pointermove'||eventCounts[type]%20===1){
                const target=e.target instanceof Element?e.target:null;
                diag.events.push({
                    t:Math.round(performance.now()-startedAt),
                    type,
                    target:target?(target.tagName+(target.id?'#'+target.id:'')+
                        (target.className&&typeof target.className==='string'?'.'+target.className.trim().replace(/\s+/g,'.').slice(0,180):'')):null,
                    pageId:target?.closest?.('[data-page-id]')?.getAttribute('data-page-id')||null,
                    defaultPrevented:e.defaultPrevented
                });
                if(diag.events.length>1200)diag.events.splice(0,200);
            }
        },true);
    });

    function cssSummary(el){
        if(!(el instanceof Element))return null;
        const cs=getComputedStyle(el),r=el.getBoundingClientRect();
        return {
            tag:el.tagName,id:el.id||null,
            className:typeof el.className==='string'?el.className.slice(0,250):null,
            position:cs.position,zIndex:cs.zIndex,pointerEvents:cs.pointerEvents,
            display:cs.display,visibility:cs.visibility,opacity:cs.opacity,
            rect:{x:Math.round(r.x),y:Math.round(r.y),w:Math.round(r.width),h:Math.round(r.height)}
        };
    }

    function snapshot(label='snapshot'){
        const allIds=[...document.querySelectorAll('[id]')].map(x=>x.id);
        const duplicates=allIds.filter((id,i)=>allIds.indexOf(id)!==i)
            .filter((id,i,a)=>a.indexOf(id)===i);

        const zone=document.querySelector('.us-canvas-zone');
        const canvases=[...document.querySelectorAll('.us-canvas[data-page-id]')];
        const zr=zone?.getBoundingClientRect();
        const x=zr?zr.left+zr.width/2:innerWidth/2;
        const y=zr?Math.min(innerHeight-80,zr.top+180):innerHeight/2;

        const overlays=[...document.querySelectorAll('*')].filter(el=>{
            const cs=getComputedStyle(el),r=el.getBoundingClientRect();
            if(cs.display==='none'||cs.visibility==='hidden'||Number(cs.opacity)===0)return false;
            if(cs.pointerEvents==='none')return false;
            const huge=r.width>=innerWidth*.85&&r.height>=innerHeight*.70;
            return huge&&(cs.position==='fixed'||cs.position==='absolute');
        }).slice(0,25).map(cssSummary);

        const data={
            t:Math.round(performance.now()-startedAt),label,readyState:document.readyState,
            duplicateIds:duplicates,
            canvasCount:canvases.length,
            pageIds:canvases.map(x=>x.dataset.pageId),
            activePages:[...document.querySelectorAll('.us-live-page.active')].map(x=>x.dataset.pageId),
            zone:cssSummary(zone),
            livePages:cssSummary(document.querySelector('#livePages')),
            library:cssSummary(document.querySelector('.us-library')),
            properties:cssSummary(document.querySelector('.us-properties-panel')),
            centerStack:document.elementsFromPoint(x,y).slice(0,10).map(cssSummary),
            blockingOverlays:overlays,
            eventCounts:{...eventCounts}
        };
        diag.snapshots.push(data);
        return data;
    }

    window.__UNDANGANTA_DIAG_SNAPSHOT__=snapshot;

    function exportDiag(){
        snapshot('export');
        diag.finishedAt=new Date().toISOString();
        const blob=new Blob([JSON.stringify(diag,null,2)],{type:'application/json'});
        const a=document.createElement('a');
        a.href=URL.createObjectURL(blob);
        a.download='UNDANGANTA_STUDIO_RUNTIME_AUDIT_'+new Date().toISOString().replace(/[:.]/g,'-')+'.json';
        document.body.appendChild(a);a.click();a.remove();
        setTimeout(()=>URL.revokeObjectURL(a.href),1000);
    }
    window.__UNDANGANTA_EXPORT_DIAG__=exportDiag;

    addEventListener('DOMContentLoaded',()=>{
        setTimeout(()=>{
            snapshot('dom-ready');

            const btn=document.createElement('button');
            btn.id='undangantaDiagExport';
            btn.type='button';
            btn.textContent='Export Runtime Audit';
            Object.assign(btn.style,{
                position:'fixed',right:'12px',bottom:'72px',zIndex:'10000',
                height:'38px',padding:'0 12px',border:'1px solid #ef4444',
                borderRadius:'9px',background:'#fff',color:'#991b1b',
                font:'700 11px system-ui',boxShadow:'0 4px 18px rgba(0,0,0,.18)',
                cursor:'pointer'
            });
            btn.addEventListener('click',exportDiag);
            document.body.appendChild(btn);

            const badge=document.createElement('div');
            badge.textContent='DIAGNOSTIC MODE';
            Object.assign(badge.style,{
                position:'fixed',right:'12px',bottom:'118px',zIndex:'10000',
                padding:'4px 7px',borderRadius:'6px',background:'#991b1b',
                color:'#fff',font:'800 9px system-ui',letterSpacing:'.05em',
                pointerEvents:'none'
            });
            document.body.appendChild(badge);
        },0);
    },{once:true});

    window.addEventListener('load',()=>setTimeout(()=>snapshot('window-load'),50),{once:true});
    setInterval(()=>{if(document.readyState==='complete'&&diag.snapshots.length<20)snapshot('periodic');},3000);
})();
</script>

<script>
(() => {
    const csrf = @json(csrf_token());
    const cumulativeInstanceFullUrl = @json($isInstanceMode ? route('studio.customer.full.update', $instance) : null);
    const isInstanceMode = @json($isInstanceMode);
    const updateUrl = @json($isInstanceMode ? route('studio.customer.full.update', $instance) : route('admin.studio.update', $template));
    const uploadAssetUrl = @json($isInstanceMode ? route('studio.customer.assets.upload', $instance) : route('admin.studio.assets.upload', $template));
    const uploadFontUrl = @json($isInstanceMode ? route('studio.customer.fonts.upload', $instance) : route('admin.studio.fonts.upload'));
    const ensurePreviewInstanceUrl = @json($isInstanceMode ? null : route('admin.studio.preview-instance.ensure', $template));
    const resetPreviewInstanceUrl = @json($isInstanceMode ? null : route('admin.studio.preview-instance.reset', $template));
    const initialPreviewInstance = @json($initialPreviewInstance);
    const instanceCustomerContent = @json($isInstanceMode ? ($instance->content ?? []) : []);
    const initialAssets = @json($initialAssets);
    const initialFonts = @json($initialFonts);
    let state = @json($canvasState);

    const $ = id => document.getElementById(id);
    const uid = p => p + '-' + Math.random().toString(36).slice(2,9);
    const clamp = (v,min,max) => Math.max(min,Math.min(max,v));
    const esc = value => String(value ?? '').replace(/[&<>'"]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[ch]));
    const deep = value => JSON.parse(JSON.stringify(value));

    function normalizeAnimationConfig(raw,legacy={}){
        const src=(raw&&typeof raw==='object'&&!Array.isArray(raw))?raw:{};
        return {
            type:String(src.type||legacy.type||'none').trim()||'none',
            duration:clamp(Number(src.duration??legacy.duration??.8),.1,5),
            delay:Math.max(0,Number(src.delay??legacy.delay??0)),
            easing:String(src.easing||legacy.easing||'ease-out'),
            trigger:String(src.trigger||legacy.trigger||'on-enter'),
            loop:!!(src.loop??legacy.loop??false)
        };
    }
    function elementAnimation(layer){
        if(!layer.animationConfig){
            layer.animationConfig=normalizeAnimationConfig(null,{type:typeof layer.animation==='string'?layer.animation:'none',duration:layer.duration,delay:layer.delay,easing:layer.animationEasing,trigger:layer.animationTrigger,loop:layer.loop});
        }
        return layer.animationConfig;
    }
    function syncElementAnimationLegacy(layer){
        const a=elementAnimation(layer);
        layer.animation=a.type;layer.duration=a.duration;layer.delay=a.delay;layer.animationEasing=a.easing;layer.animationTrigger=a.trigger;layer.loop=a.loop;
        return a;
    }
    function canvasAnimation(p=page()){
        if(!p.canvasAnimation){
            p.canvasAnimation=normalizeAnimationConfig(null,{type:p.transition?.type||'none',duration:p.transition?.duration??.8,easing:p.transition?.easing||'ease-in-out',delay:0,trigger:'on-enter',loop:false});
        }
        return p.canvasAnimation;
    }
    function syncCanvasAnimationLegacy(p){
        const a=canvasAnimation(p);
        p.transition={type:a.type,duration:a.duration,easing:a.easing};
        return a;
    }
    function normalizeLayer(l){
        const raw=l||{};
        const layer=Object.assign({id:uid('layer'),type:'shape',name:'Layer',x:50,y:50,width:180,height:120,rotation:0,opacity:1,zIndex:1,background:'#BFD8FF',borderRadius:0,locked:false,hidden:false,flipX:false,flipY:false,effects:null,animation:'none',animationConfig:null,duration:.8,delay:0,loop:false,shapeKind:'rect',frameKind:'square',gridKind:'4',gap:4,cells:[],binding:'none',customerEditPolicy:'full',optional:false,hideWhenEmpty:false,galleryAnimation:'fade',galleryAutoplay:false,galleryInterval:4,galleryLightbox:true,galleryThumbnails:true,animationTrigger:'on-enter',animationEasing:'ease-out',componentType:null},raw);
        layer.flipX=!!raw.flipX;layer.flipY=!!raw.flipY;
        layer.effects=normalizeMediaEffects(raw.effects);
        layer.animationConfig=normalizeAnimationConfig(raw.animationConfig,{type:typeof raw.animation==='string'?raw.animation:'none',duration:raw.duration,delay:raw.delay,easing:raw.animationEasing,trigger:raw.animationTrigger,loop:raw.loop});
        syncElementAnimationLegacy(layer);
        layer.cells=Array.isArray(layer.cells)?layer.cells.map(c=>Object.assign({src:null,posX:0,posY:0,scale:1,binding:'none'},c||{})):[];
        if(layer.type==='grid'&&layer.binding&&layer.binding!=='none'){
            if(!layer.cells.length)layer.cells.push({src:null,posX:0,posY:0,scale:1,binding:'none'});
            if(!layer.cells.some(c=>c.binding&&c.binding!=='none'))layer.cells[0].binding=layer.binding;
            layer.binding='none';
        }
        return layer;
    }
    /* UNDANGANTA_PHASE9_MEDIA_ADJUSTMENTS_V1 */
    function normalizeMediaEffects(raw){
        const value=raw&&typeof raw==='object'&&!Array.isArray(raw)?raw:{};
        return {
            brightness:clamp(Number(value.brightness??100),0,200),
            contrast:clamp(Number(value.contrast??100),0,200),
            saturation:clamp(Number(value.saturation??100),0,200),
            grayscale:clamp(Number(value.grayscale??0),0,100)
        };
    }
    function mediaFilterCss(l){
        const e=normalizeMediaEffects(l?.effects);
        return `brightness(${e.brightness}%) contrast(${e.contrast}%) saturate(${e.saturation}%) grayscale(${e.grayscale}%)`;
    }
    function mediaTransformCss(l,base=''){
        return `${base}${base?' ':''}scaleX(${l?.flipX?-1:1}) scaleY(${l?.flipY?-1:1})`;
    }
    function normalizePage(p,index){
        const raw=p||{};
        const page=Object.assign({id:uid('canvas'),name:'Canvas '+(index+1),background:'#ffffff',role:index===0?'hero':'section',width:390,height:844,guides:null,transition:{type:'none',duration:.8,easing:'ease-in-out'},canvasAnimation:null,layers:[]},raw);
        if(page.role==='desktop-cover')page.role=index===0?'hero':'section';
        if(!Array.isArray(page.guides)||page.guides.length<2){const h=Number(page.height||844);page.guides=[Math.round(h*.34),Math.round(h*.68)];}
        page.guides=page.guides.slice(0,2).map(v=>Math.max(70,Math.min(Number(page.height||844)-70,Number(v||0)))).sort((a,b)=>a-b);
        page.canvasAnimation=normalizeAnimationConfig(raw.canvasAnimation,{type:raw.transition?.type||'none',duration:raw.transition?.duration??.8,easing:raw.transition?.easing||'ease-in-out'});
        syncCanvasAnimationLegacy(page);
        page.layers=Array.isArray(page.layers)?page.layers.map(normalizeLayer):[];
        return page;
    }

    // Runtime recovery: helpers preserved in V4 but accidentally removed by V5 refactor.
    function clampAllLayersToCanvas(p){
        if(!p?.layers)return;
        const w=Number(p.width||390),h=Number(p.height||844);
        p.layers.forEach(l=>{
            l.width=Math.min(Number(l.width||1),w);
            l.height=Math.min(Number(l.height||1),h);
            l.x=clamp(Number(l.x||0),0,Math.max(0,w-l.width));
            l.y=clamp(Number(l.y||0),0,Math.max(0,h-l.height));
        });
    }

    function selectionBounds(ids=[...selectedIds]){
        const list=page().layers.filter(l=>ids.includes(l.id)&&!l.hidden);
        if(!list.length)return null;
        const left=Math.min(...list.map(l=>Number(l.x||0)));
        const top=Math.min(...list.map(l=>Number(l.y||0)));
        const right=Math.max(...list.map(l=>Number(l.x||0)+Number(l.width||0)));
        const bottom=Math.max(...list.map(l=>Number(l.y||0)+Number(l.height||0)));
        return {left,top,right,bottom,width:right-left,height:bottom-top,list};
    }

    function migrate(raw){
        raw = raw && typeof raw === 'object' ? deep(raw) : {};
        const settings=Object.assign({desktopLayout:'cover-left',desktopCoverWidth:56,mobileBreakpoint:768},raw.settings||{});
        const desktopCover=Object.assign({image:'',mediaType:'image',fit:'cover',scale:1,posX:0,posY:0,background:'#0f172a'},raw.desktopCover||{});
        const openingCover=Object.assign({enabled:true,eyebrow:'The Wedding of',names:'Nama & Nama',bindNames:true,buttonText:'Buka Undangan',textColor:'#ffffff',buttonColor:'#2F6FED',nameSize:46,posX:50,posY:50,animation:'fade-up',duration:.8},raw.openingCover||{});
        if(Array.isArray(raw.pages)){
            let pages=raw.pages.map(normalizePage);
            const legacyCover=pages.find(p=>p.role==='desktop-cover');
            if(legacyCover){
                if(!desktopCover.image){
                    const imageLayer=(legacyCover.layers||[]).find(l=>l.type==='image'&&l.src);
                    const frameLayer=(legacyCover.layers||[]).find(l=>l.type==='frame'&&l.cells?.[0]?.src);
                    desktopCover.image=imageLayer?.src||frameLayer?.cells?.[0]?.src||'';
                }
                desktopCover.legacyPage=desktopCover.legacyPage||legacyCover;
                pages=pages.filter(p=>p.role!=='desktop-cover');
            }
            if(pages.length && !pages.some(p=>p.role==='hero')) pages[0].role='hero';
            return {version:4,width:Number(raw.width||390),height:Number(raw.height||844),settings,desktopCover,openingCover,pages};
        }
        const oldLayers = Array.isArray(raw.layers) ? raw.layers : [];
        return {version:4,width:Number(raw.width||390),height:Number(raw.height||844),settings,desktopCover,openingCover,pages:[normalizePage({name:'Hero Mobile',role:'hero',background:raw.background||'#ffffff',layers:oldLayers},0)]};
    }
    state = migrate(state);
    if(!state.pages.length) state.pages.push(normalizePage({},0));
    state.pages.forEach(clampAllLayersToCanvas);

    let activePageId = state.pages[0].id;
    let selectedId = state.pages[0].layers[0]?.id || null;
    let assets = [...initialAssets];
    let fonts = [...initialFonts];
    let preview = false, history = [], future = [], drag = null, resize = null, rotateDrag = null, cropDrag = null, cropMode = null, dirty = false, pageDragId = null, activeCellIndex = 0, gesture = null;
    let selectedIds = new Set(selectedId ? [selectedId] : []);
    let lasso = null, groupDrag = null, groupResize = null, layerClipboard = [], layerClipboardPasteCount = 0, inlineTextEdit = null;
    let nudgeHistoryKey = null, nudgeHistoryTimer = null;
    let snapEnabled=true,snapGuides=[];
    let safeAreaEnabled=true,safeAreaMargin=20,keepInsideCanvas=true,respectSafeArea=false;
    let textClickArm={id:null,time:0};
    let editorViewMode=isInstanceMode?'customer':'sample';
    if(isInstanceMode){
        requestAnimationFrame(()=>document.querySelector('.us-body')?.classList.add('properties-collapsed'));
    }
    let customerPreviewInstance=initialPreviewInstance;
    const instanceCustomerFullUpdateUrl=@json($isInstanceMode ? route('studio.customer.full.update', $instance) : null);
    const customerInstanceDirtyKeys=new Set();
    const STUDIO_GALLERY_CAPACITY=@json(\App\Support\StudioBindingSchema::galleryCapacity());
    const galleryCustomerDefaults=Object.fromEntries(
        Array.from({length:STUDIO_GALLERY_CAPACITY},(_,i)=>[`gallery_${i+1}`,null])
    );
    let customerData=Object.assign({
        groom_name:'',bride_name:'',couple_names:'',event_date:'',
        venue_name:'',venue_address:'',maps_url:'',
        quote:'',prayer:'',opening_text:'',closing_text:'',story:'',music_url:'',
        gift_bank:'',gift_number:'',gift_name:'',
        groom_photo:null,bride_photo:null,couple_photo:null,gallery:[],
        ...galleryCustomerDefaults
    },isInstanceMode ? (instanceCustomerContent||{}) : (initialPreviewInstance?.content||{}));
    /* STALE_ASSET_REFERENCE_CLEANUP_V1 */
    function studioAssetIdFromUrl(url){
        const value=String(url||'');
        const match=value.match(/\/assets\/(\d+)\/(?:file|stream)(?:[?#]|$)/i);
        return match?Number(match[1]):null;
    }

    function referencedStudioAssetId(ref){
        if(ref===null||ref===undefined)return null;
        if(typeof ref==='object'){
            const raw=ref.assetId??ref.asset_id??ref.id??null;
            if(raw!==null&&raw!==undefined&&String(raw)!==''){
                const numeric=Number(raw);
                if(Number.isInteger(numeric)&&numeric>0)return numeric;
            }
            return studioAssetIdFromUrl(ref.url||ref.src||ref.image||'');
        }
        return studioAssetIdFromUrl(ref);
    }

    function scrubStaleStudioAssetReferences(){
        if(!isInstanceMode)return {changed:false,total:0};

        const available=new Set(
            assets
                .map(a=>Number(a?.id))
                .filter(id=>Number.isInteger(id)&&id>0)
        );

        const isStale=ref=>{
            const id=referencedStudioAssetId(ref);
            return Number.isInteger(id)&&id>0&&!available.has(id);
        };

        const report={
            covers:0,
            layers:0,
            cells:0,
            gallery:0,
            changed:false,
            total:0
        };

        const clearCover=cover=>{
            if(!cover||!cover.image||!isStale({assetId:cover.assetId,url:cover.image}))return;
            cover.image='';
            cover.assetId=null;
            cover.mediaType='image';
            report.covers++;
        };

        clearCover(state.desktopCover);
        clearCover(state.openingCover);

        (state.pages||[]).forEach(p=>{
            (p.layers||[]).forEach(l=>{
                if(['image','video'].includes(l.type)&&l.src&&isStale({assetId:l.assetId,url:l.src})){
                    l.src='';
                    l.assetId=null;
                    report.layers++;
                }

                if(l.type==='frame'||l.type==='grid'){
                    (l.cells||[]).forEach(c=>{
                        if(c?.src&&isStale({assetId:c.assetId,url:c.src})){
                            c.src=null;
                            c.assetId=null;
                            c.posX=0;
                            c.posY=0;
                            c.cropX=0;
                            c.cropY=0;
                            c.scale=1;
                            c.naturalWidth=null;
                            c.naturalHeight=null;
                            report.cells++;
                        }
                    });
                }
            });
        });

        if(Array.isArray(customerData.gallery)){
            const before=customerData.gallery.length;
            customerData.gallery=customerData.gallery.filter(item=>item&&!isStale(item));
            report.gallery=Math.max(0,before-customerData.gallery.length);

            for(let i=1;i<=STUDIO_GALLERY_CAPACITY;i++){
                customerData[`gallery_${i}`]=customerData.gallery[i-1]||null;
            }
        }

        report.total=report.covers+report.layers+report.cells+report.gallery;
        report.changed=report.total>0;

        if(report.changed){
            interactionDiag('STALE_ASSET_REFERENCE_CLEANUP',report);

            // Canvas/cover cleanup persists through the existing safe full-update route.
            dirty=true;
            setSaveStatus('Membersihkan referensi media lama...','saving');
            clearTimeout(autosaveTimer);
            autosaveTimer=setTimeout(()=>saveTemplate({silent:true}),250);

            // Gallery is stored in instance content and uses its established safe saver.
            if(report.gallery>0){
                clearTimeout(premiumGallerySaveTimer);
                premiumGallerySaveTimer=setTimeout(savePremiumGallery,450);
            }
        }

        return report;
    }
    let customerSaveTimer=null,customerSaveInFlight=false,customerSavePending=false;
    const activePointers=new Map();
    let lastTap={time:0,id:null,cell:null};
    let cellLongPress=null;
    let touchCellDelete=null;
    let cellMediaClipboard=null;
    let cellContextTarget=null;
    const naturalMediaSizeCache=new Map();

    let canvas = null;
    const livePages = $('livePages');
    const canvasZone = $('canvasZone');
    const editorDocument = $('editorDocument');
    const editorStickyCover = $('editorStickyCover');
    const layerList = $('layerList'), props = $('properties'), empty = $('propertiesEmpty'), toast = $('toast');

    fonts.forEach(f=>{const st=document.createElement('style');st.textContent=`@font-face{font-family:'${f.family}';src:url('${f.url}');font-weight:${f.weight};font-style:${f.style};font-display:swap}`;document.head.appendChild(st);});

    function page(){return state.pages.find(p=>p.id===activePageId)||state.pages[0];}
    function selected(){return page().layers.find(l=>l.id===selectedId)||null;}
    function selectedLayers(){return page().layers.filter(l=>selectedIds.has(l.id));}
    function syncPrimarySelection(){if(selectedId&&selectedIds.has(selectedId))return;selectedId=selectedLayers().at(-1)?.id||null;}
    function setSingleSelection(id){selectedId=id||null;selectedIds=new Set(id?[id]:[]);}
    function clearSelection(){selectedId=null;selectedIds.clear();}
    function snapshot(){return JSON.stringify({state,activePageId,selectedId,selectedIds:[...selectedIds]});}
    function restore(s){const data=JSON.parse(s);state=data.state;activePageId=data.activePageId;selectedId=data.selectedId;selectedIds=new Set(Array.isArray(data.selectedIds)?data.selectedIds:(selectedId?[selectedId]:[]));}
    function pushHistory(){history.push(snapshot());if(history.length>80)history.shift();future=[];}
    let autosaveTimer=null, saveInFlight=false, pendingSave=false, canvasRevision=0;
    function setSaveStatus(text,state=''){const t=$('saveStatusText');if(t)t.textContent=text;const d=$('saveDot');if(d)d.className='us-autosave-dot '+state;}
    function markDirty(){canvasRevision++;dirty=true;setSaveStatus('Belum disimpan','');clearTimeout(autosaveTimer);autosaveTimer=setTimeout(()=>saveTemplate({silent:true}),2000);}
    function notify(msg){toast.textContent=msg;toast.classList.add('show');setTimeout(()=>toast.classList.remove('show'),1800);}

    let interactionDiagLastMove=0;
    function interactionDiag(type,payload={}){
        const d=window.__UNDANGANTA_DIAG__;
        if(!d)return;
        if(type==='DRAG_MOVE'){
            const now=performance.now();
            if(now-interactionDiagLastMove<120)return;
            interactionDiagLastMove=now;
        }
        d.notes=d.notes||[];
        d.notes.push(Object.assign({
            kind:'INTERACTION',
            type,
            t:Math.round(performance.now()),
            device:editorDeviceMode,
            zoom:currentEditorZoom
        },payload));
        if(d.notes.length>800)d.notes.splice(0,100);
    }

    let animationInteractionDiagLast=0;
    function animationInteractionSnapshot(el,l,extra={}){
        const content=el?.querySelector?.('.us-layer-content')||null;
        const layerStyle=el?getComputedStyle(el):null;
        const contentStyle=content?getComputedStyle(content):null;
        return Object.assign({
            canvasId:activePageId,
            layerId:l?.id||null,
            elementAnimation:l?.animation||'none',
            globalPreview:!!preview,
            editorPreviewLayerId:[...elementAnimationPreviews.keys()][0]||null,
            dragActive:!!drag,
            resizeActive:!!resize,
            rotateActive:!!rotateDrag,
            cropActive:!!cropDrag,
            pointerCount:activePointers?.size||0,
            layerLeft:el?.style?.left||null,
            layerTop:el?.style?.top||null,
            layerTransform:layerStyle?.transform||null,
            contentTransform:contentStyle?.transform||null,
            animationName:contentStyle?.animationName||null,
            animationDuration:contentStyle?.animationDuration||null,
            animationIterationCount:contentStyle?.animationIterationCount||null,
            animationPlayState:contentStyle?.animationPlayState||null,
            transition:contentStyle?.transition||null
        },extra);
    }
    function animationInteractionDiag(type,el,l,extra={}){
        if(!window.__UNDANGANTA_DIAG__)return;
        interactionDiag(type,animationInteractionSnapshot(el,l,extra));
    }

    function animationCss(l,{forceOnce=false}={}){
        if(!l||!l.animation||l.animation==='none')return '';
        const map={
            'fade':'usFade','fade-up':'usFadeUp','fade-down':'usFadeDown','fade-left':'usFadeLeft','fade-right':'usFadeRight',
            'zoom':'usZoom','pop':'usPop','soft-scale':'usSoftScale','blur-in':'usBlurIn','focus-in':'usFocusIn','reveal-up':'usRevealUp',
            'wipe-up':'usWipeUp','wipe-left':'usWipeLeft','flash':'usFlash','flicker':'usFlicker','breathe':'usBreathe','glow-pulse':'usGlowPulse','float':'usFloat'
        };
        const name=map[l.animation];if(!name)return '';
        const easing=l.animationEasing||'ease-out';
        const iterations=forceOnce?'1':(l.loop?'infinite':'1');
        return `${name} ${Number(l.duration||.8)}s ${easing} ${Number(l.delay||0)}s ${iterations} both`;
    }

    function shapeStyle(l,el){
        const kind=l.shapeKind||'rect';
        el.style.background=l.background||'#BFD8FF';
        el.style.clipPath='';
        if(kind==='circle')el.style.borderRadius='50%';
        else if(kind==='oval')el.style.borderRadius='50%';
        else if(kind==='rounded')el.style.borderRadius=(l.borderRadius||18)+'px';
        else if(kind==='line'){el.style.height=Math.max(2,l.height||4)+'px';el.style.borderRadius='999px';}
        else if(kind==='triangle')el.style.clipPath='polygon(50% 0,100% 100%,0 100%)';
        else if(kind==='star')el.style.clipPath='polygon(50% 0,61% 35%,98% 35%,68% 57%,79% 94%,50% 72%,21% 94%,32% 57%,2% 35%,39% 35%)';
        else if(kind==='heart')el.style.clipPath='polygon(50% 92%,7% 50%,7% 26%,20% 9%,36% 8%,50% 22%,64% 8%,80% 9%,93% 26%,93% 50%)';
        else if(kind==='hex')el.style.clipPath='polygon(25% 7%,75% 7%,100% 50%,75% 93%,25% 93%,0 50%)';
    }
    function cellCountForGrid(kind){return ({'2h':2,'2v':2,'3':3,'4':4,'6':6,'mosaic':3})[kind]||4;}
    function ensureCells(l,count){
        l.cells=Array.isArray(l.cells)?l.cells:[];
        while(l.cells.length<count)l.cells.push({src:null,posX:0,posY:0,scale:1,binding:'none'});
        l.cells=l.cells.map(c=>Object.assign({src:null,posX:0,posY:0,scale:1,binding:'none'},c||{}));
        if(l.cells.length>count)l.cells=l.cells.slice(0,count);
    }
    function cellImageHtml(cell){
        if(!cell?.src)return '';
        return `<img data-cell-media-img="1" data-geometry-pending="1" src="${esc(cell.src)}" alt="" draggable="false" style="position:absolute;inset:0;width:100%;height:100%;max-width:none;max-height:none;opacity:1;object-fit:cover;object-position:center;transform:none;transform-origin:center center;pointer-events:none">`;
    }
    function renderFrameContent(l){
        ensureCells(l,1);
        const kind=l.frameKind||'square';
        const cls={circle:'us-frame-circle',rounded:'us-frame-rounded',arch:'us-frame-arch',heart:'us-frame-heart'}[kind]||'';
        const polaroid=kind==='polaroid'?'padding:8px 8px 24px;background:#fff;box-sizing:border-box':'';
        const extra=kind==='polaroid'?' us-frame-polaroid':'';
        return `<div class="us-frame-inner ${cls}${extra}" style="${polaroid}"><div class="us-media-cell ${l.cells[0]?.src?'':'empty'} ${activeCellIndex===0&&l.id===selectedId?'selected-cell':''}" style="width:100%;height:100%" data-cell-index="0">${cellImageHtml(l.cells[0])}</div></div>`;
    }
    function renderGridContent(l){
        const kind=l.gridKind||'4',count=cellCountForGrid(kind);ensureCells(l,count);
        const cls={'2h':'us-grid-2h','2v':'us-grid-2v','3':'us-grid-3','4':'us-grid-4','6':'us-grid-6','mosaic':'us-grid-mosaic'}[kind]||'us-grid-4';
        const cells=l.cells.map((c,i)=>{
            const showDelete=!!c?.src&&touchCellDelete?.layerId===l.id&&Number(touchCellDelete?.cellIndex)===i;
            return `<div class="us-media-cell ${c?.src?'':'empty'} ${activeCellIndex===i&&l.id===selectedId?'selected-cell':''}" data-cell-index="${i}">${cellImageHtml(c)}${showDelete?`<button type="button" class="us-cell-touch-delete" data-cell-delete="${i}" aria-label="Hapus foto">Hapus</button>`:''}</div>`;
        }).join('');
        return `<div class="us-grid-inner ${cls}" style="gap:${Math.max(0,Number(l.gap||0))}px;border-radius:${Math.max(0,Number(l.borderRadius||0))}px">${cells}</div>`;
    }


    const TEXT_BINDINGS=@json(\App\Support\StudioBindingSchema::textBindingOptions());
    function availableGalleryBindingCount(){
        const canonicalCount=Array.isArray(customerData?.gallery)
            ? Math.min(STUDIO_GALLERY_CAPACITY,customerData.gallery.length)
            : 0;

        let highestAlias=0;
        for(let i=1;i<=STUDIO_GALLERY_CAPACITY;i++){
            if(customerData?.[`gallery_${i}`])highestAlias=i;
        }

        return Math.max(
            3,
            Math.min(STUDIO_GALLERY_CAPACITY,Math.max(canonicalCount,highestAlias))
        );
    }
    function mediaBindings(){
        const gallery=Array.from(
            {length:availableGalleryBindingCount()},
            (_,i)=>[`gallery_${i+1}`,`Galeri foto ${i+1}`]
        );

        return [
            ['none','Tidak terhubung'],
            ['groom_photo','Foto mempelai pria'],
            ['bride_photo','Foto mempelai wanita'],
            ['couple_photo','Foto pasangan'],
            ...gallery
        ];
    }
    function bindingOptionsFor(l){
        if(l.type==='text')return TEXT_BINDINGS;
        if(l.type==='frame'||l.type==='image')return mediaBindings();
        if(l.type==='grid')return [['none','Gunakan binding per kotak']];
        return [['none','Tidak terhubung']];
    }
    function bindingLabel(value,l){
        return (bindingOptionsFor(l).find(x=>x[0]===value)||['',value||'Tidak terhubung'])[1];
    }
    function editPolicyLabel(v){return v==='content'?'Isi saja':v==='locked'?'Terkunci':'Edit penuh';}


    function groupId(){return 'grp_'+Date.now().toString(36)+Math.random().toString(36).slice(2,7);}
    function selectedGroupIds(){return [...new Set(selectedLayers().map(l=>l.groupId).filter(Boolean))];}
    function groupSelectedLayers(){
        const list=selectedLayers().filter(l=>!l.locked);if(list.length<2){notify('Pilih minimal 2 elemen');return;}
        pushHistory();const gid=groupId();list.forEach(l=>l.groupId=gid);markDirty();render();notify('Elemen digroup');
    }
    function ungroupSelectedLayers(){
        const groups=selectedGroupIds();if(!groups.length){notify('Selection belum berupa group');return;}
        pushHistory();page().layers.forEach(l=>{if(groups.includes(l.groupId))delete l.groupId;});markDirty();render();notify('Group dilepas');
    }
    function setLayerZ(list,mode){
        if(!list.length)return;
        const layers=page().layers,sel=new Set(list.map(l=>l.id));
        const order=[...layers].sort((a,b)=>((Number(a.zIndex)||0)-(Number(b.zIndex)||0))||(layers.indexOf(a)-layers.indexOf(b)));
        const before=order.map(l=>l.id).join('|');
        if(mode==='front'||mode==='back'){
            const chosen=order.filter(l=>sel.has(l.id)),rest=order.filter(l=>!sel.has(l.id));
            order.splice(0,order.length,...(mode==='front'?[...rest,...chosen]:[...chosen,...rest]));
        }else if(mode==='forward'){
            for(let i=order.length-2;i>=0;i--)if(sel.has(order[i].id)&&!sel.has(order[i+1].id))[order[i],order[i+1]]=[order[i+1],order[i]];
        }else if(mode==='backward'){
            for(let i=1;i<order.length;i++)if(sel.has(order[i].id)&&!sel.has(order[i-1].id))[order[i],order[i-1]]=[order[i-1],order[i]];
        }
        if(before===order.map(l=>l.id).join('|')){notify(mode==='forward'||mode==='front'?'Sudah paling depan':'Sudah paling belakang');return;}
        pushHistory();order.forEach((l,i)=>l.zIndex=i+1);markDirty();render();
    }
    function alignSelection(action){
        const list=selectedLayers().filter(l=>!l.locked);if(!list.length){notify('Pilih elemen dulu');return;}
        pushHistory();const b=list.length===1?pageBounds():selectionBounds();
        list.forEach(l=>{if(action==='left')l.x=b.left;if(action==='center-x')l.x=Math.round(b.left+(b.width-l.width)/2);if(action==='right')l.x=b.right-l.width;
            if(action==='top')l.y=b.top;if(action==='center-y')l.y=Math.round(b.top+(b.height-l.height)/2);if(action==='bottom')l.y=b.bottom-l.height;});
        markDirty();render();
    }
    function distributeSelection(axis){
        const list=selectedLayers().filter(l=>!l.locked);if(list.length<3){notify('Pilih minimal 3 elemen');return;}
        pushHistory();const sorted=[...list].sort((a,b)=>axis==='x'?a.x-b.x:a.y-b.y),first=sorted[0],last=sorted.at(-1);
        if(axis==='x'){const span=(last.x+last.width)-first.x,total=sorted.reduce((s,l)=>s+l.width,0),gap=(span-total)/(sorted.length-1);let c=first.x;sorted.forEach(l=>{l.x=Math.round(c);c+=l.width+gap;});}
        else{const span=(last.y+last.height)-first.y,total=sorted.reduce((s,l)=>s+l.height,0),gap=(span-total)/(sorted.length-1);let c=first.y;sorted.forEach(l=>{l.y=Math.round(c);c+=l.height+gap;});}
        markDirty();render();
    }
    function clearSnapGuides(){snapGuides=[];canvas?.querySelectorAll('.us-snap-guide').forEach(x=>x.remove());}
    function drawSnapGuides(guides=[]){
        clearSnapGuides();snapGuides=guides;const s=displayScaleFor(page());
        guides.forEach(g=>{const el=document.createElement('div');el.className='us-snap-guide '+g.axis;if(g.axis==='vertical')el.style.left=(g.pos*s.sx)+'px';else el.style.top=(g.pos*s.sy)+'px';canvas?.appendChild(el);});
    }
    function snapLayerPosition(l,x,y){
        if(!snapEnabled)return {x,y,guides:[]};const p=page(),threshold=6;let nx=x,ny=y,guides=[];
        const cx=[0,Number(p.width||390)/2,Number(p.width||390)],cy=[0,Number(p.height||844)/2,Number(p.height||844)];
        page().layers.forEach(o=>{if(o.id===l.id||selectedIds.has(o.id)||o.hidden)return;cx.push(o.x,o.x+o.width/2,o.x+o.width);cy.push(o.y,o.y+o.height/2,o.y+o.height);});
        const lx=[['left',nx],['center',nx+l.width/2],['right',nx+l.width]],ly=[['top',ny],['center',ny+l.height/2],['bottom',ny+l.height]];
        let bx=null,by=null;
        for(const c of cx)for(const [kind,v] of lx){const d=Math.abs(v-c);if(d<=threshold&&(!bx||d<bx.d))bx={c,kind,d};}
        for(const c of cy)for(const [kind,v] of ly){const d=Math.abs(v-c);if(d<=threshold&&(!by||d<by.d))by={c,kind,d};}
        if(bx){nx=bx.kind==='left'?bx.c:bx.kind==='center'?bx.c-l.width/2:bx.c-l.width;guides.push({axis:'vertical',pos:bx.c});}
        if(by){ny=by.kind==='top'?by.c:by.kind==='center'?by.c-l.height/2:by.c-l.height;guides.push({axis:'horizontal',pos:by.c});}
        const bounded=constrainLayerPosition(l,nx,ny);return {x:bounded.x,y:bounded.y,guides};
    }


    function pageBounds(){const p=page();return {left:0,top:0,right:Number(p.width||390),bottom:Number(p.height||844),width:Number(p.width||390),height:Number(p.height||844)};}
    function currentSafeBounds(){const p=page(),m=Math.max(0,Number(safeAreaMargin||0));return {left:m,top:m,right:Number(p.width||390)-m,bottom:Number(p.height||844)-m,width:Number(p.width||390)-m*2,height:Number(p.height||844)-m*2};}
    function constrainLayerPosition(l,x,y){if(!keepInsideCanvas&&!respectSafeArea)return{x:Math.round(x),y:Math.round(y)};const b=respectSafeArea?currentSafeBounds():pageBounds(),maxX=Math.max(b.left,b.right-Number(l.width||0)),maxY=Math.max(b.top,b.bottom-Number(l.height||0));return{x:Math.round(clamp(x,b.left,maxX)),y:Math.round(clamp(y,b.top,maxY))};}
    function fitLayerToSafeArea(l){const b=currentSafeBounds();let changed=false;if(l.width>b.width){l.width=Math.max(1,b.width);changed=true;}if(l.height>b.height){l.height=Math.max(1,b.height);changed=true;}const p=constrainLayerPosition(l,l.x,l.y);if(p.x!==l.x||p.y!==l.y){l.x=p.x;l.y=p.y;changed=true;}return changed;}
    function fitSelectionIntoSafeArea(){const list=selectedLayers().filter(l=>!l.locked);if(!list.length){notify('Pilih elemen dulu');return;}pushHistory();let changed=false;list.forEach(l=>{if(fitLayerToSafeArea(l))changed=true;});if(changed){markDirty();render();notify('Selection dimasukkan ke Safe Area');}else notify('Selection sudah aman');}
    function isLayerOutsideSafe(l){const b=currentSafeBounds();return l.x<b.left||l.y<b.top||(l.x+l.width)>b.right||(l.y+l.height)>b.bottom;}
    function renderSafeArea(){/* rendered per live canvas */}
    function appendGroupSelectionChrome(){if(canvas&&page())appendGroupSelectionChromeTo(canvas,page());}
    function canvasPoint(clientX,clientY){const r=canvas.getBoundingClientRect(),b=basePageSize(page());return {x:(clientX-r.left)*(b.width/Math.max(1,r.width)),y:(clientY-r.top)*(b.height/Math.max(1,r.height))};}
    function beginLasso(e){const pt=canvasPoint(e.clientX,e.clientY);lasso={startX:pt.x,startY:pt.y,x:pt.x,y:pt.y,base:e.shiftKey?new Set(selectedIds):new Set()};if(!e.shiftKey)clearSelection();renderCanvas();const box=document.createElement('div');box.className='us-lasso-box';box.id='usLassoBox';canvas.appendChild(box);updateLassoVisual();}
    function updateLassoVisual(){
        if(!lasso)return;const left=Math.min(lasso.startX,lasso.x),top=Math.min(lasso.startY,lasso.y),right=Math.max(lasso.startX,lasso.x),bottom=Math.max(lasso.startY,lasso.y);
        const box=$('usLassoBox');if(box){const s=displayScaleFor(page());box.style.left=(left*s.sx)+'px';box.style.top=(top*s.sy)+'px';box.style.width=((right-left)*s.sx)+'px';box.style.height=((bottom-top)*s.sy)+'px';}
        const next=new Set(lasso.base);page().layers.forEach(l=>{if(l.hidden||l.locked)return;const x=Number(l.x||0),y=Number(l.y||0),r=x+Number(l.width||0),b=y+Number(l.height||0);if(!(r<left||x>right||b<top||y>bottom))next.add(l.id);});
        selectedIds=next;syncPrimarySelection();canvas.querySelectorAll('.us-layer').forEach(el=>el.classList.toggle('multi-selected',selectedIds.has(el.dataset.id)));
    }
    function finishLasso(){if(!lasso)return;lasso=null;syncPrimarySelection();render();}
    function startGroupMove(e){const b=selectionBounds();if(!b)return false;if(b.list.some(l=>l.locked)){notify('Selection berisi elemen terkunci');return false;}interactionChanged=false;groupDrag={startX:e.clientX,startY:e.clientY,sx:canvasScale().sx,sy:canvasScale().sy,bounds:b,historyPushed:false,items:b.list.map(l=>({id:l.id,x:l.x,y:l.y}))};return true;}
    function applyGroupMove(e){
        if(!groupDrag)return;const g=groupDrag,rawDx=(e.clientX-g.startX)*g.sx,rawDy=(e.clientY-g.startY)*g.sy;
        const limit=respectSafeArea?currentSafeBounds():pageBounds();
        const dx=keepInsideCanvas||respectSafeArea?clamp(rawDx,limit.left-g.bounds.left,limit.right-g.bounds.right):rawDx;
        const dy=keepInsideCanvas||respectSafeArea?clamp(rawDy,limit.top-g.bounds.top,limit.bottom-g.bounds.bottom):rawDy;
        if(!g.historyPushed&&Math.hypot(dx,dy)>=1){pushHistory();g.historyPushed=true;}
        if(!g.historyPushed)return;
        g.items.forEach(i=>{const l=page().layers.find(x=>x.id===i.id);if(l){l.x=Math.round(i.x+dx);l.y=Math.round(i.y+dy);updateLiveLayerDom(l);}});touchInteraction();
    }
    function startGroupResize(e,handle){const b=selectionBounds();if(!b||b.width<=0||b.height<=0)return false;if(b.list.some(l=>l.locked)){notify('Selection berisi elemen terkunci');return false;}interactionChanged=false;groupResize={handle,startX:e.clientX,startY:e.clientY,sx:canvasScale().sx,sy:canvasScale().sy,bounds:b,historyPushed:false,items:b.list.map(l=>({id:l.id,x:l.x,y:l.y,width:l.width,height:l.height}))};return true;}
    function applyGroupResize(e){
        if(!groupResize)return;const g=groupResize,dx=(e.clientX-g.startX)*g.sx,dy=(e.clientY-g.startY)*g.sy;let L=g.bounds.left,T=g.bounds.top,R=g.bounds.right,B=g.bounds.bottom;
        if(g.handle.includes('e'))R=Math.max(L+20,g.bounds.right+dx);if(g.handle.includes('w'))L=Math.min(R-20,g.bounds.left+dx);if(g.handle.includes('s'))B=Math.max(T+20,g.bounds.bottom+dy);if(g.handle.includes('n'))T=Math.min(B-20,g.bounds.top+dy);
        if(!g.historyPushed&&Math.hypot(dx,dy)>=1){pushHistory();g.historyPushed=true;}if(!g.historyPushed)return;
        const sx=(R-L)/g.bounds.width,sy=(B-T)/g.bounds.height;g.items.forEach(i=>{const l=page().layers.find(x=>x.id===i.id);if(!l)return;l.x=Math.round(L+((i.x-g.bounds.left)/g.bounds.width)*(R-L));l.y=Math.round(T+((i.y-g.bounds.top)/g.bounds.height)*(B-T));l.width=Math.max(10,Math.round(i.width*sx));l.height=Math.max(10,Math.round(i.height*sy));updateLiveLayerDom(l);});touchInteraction();
    }
    function duplicateSelectedLayers(){const list=[...selectedLayers()].sort((a,b)=>(a.zIndex||0)-(b.zIndex||0));if(!list.length)return false;pushHistory();const maxZ=Math.max(0,...page().layers.map(x=>x.zIndex||0)),ids=[],groupMap=new Map();list.forEach((l,i)=>{const c=deep(l);c.id=uid(l.type);c.name=(l.name||l.type)+' Copy';c.x+=14;c.y+=14;c.zIndex=maxZ+i+1;if(c.groupId){if(!groupMap.has(c.groupId))groupMap.set(c.groupId,groupId());c.groupId=groupMap.get(c.groupId);}page().layers.push(c);ids.push(c.id);});selectedIds=new Set(ids);selectedId=ids.at(-1)||null;markDirty();render();return true;}
    function copySelectedLayers(){const list=[...selectedLayers()].sort((a,b)=>(a.zIndex||0)-(b.zIndex||0));if(!list.length)return false;layerClipboard=deep(list);layerClipboardPasteCount=0;notify(`${list.length} elemen disalin`);return true;}
    function pasteSelectedLayers(){if(!layerClipboard.length)return false;pushHistory();layerClipboardPasteCount++;const offset=18*layerClipboardPasteCount,maxZ=Math.max(0,...page().layers.map(x=>x.zIndex||0)),ids=[],groupMap=new Map();layerClipboard.forEach((l,i)=>{const c=deep(l);c.id=uid(l.type);c.name=(l.name||l.type)+' Copy';c.x+=offset;c.y+=offset;c.zIndex=maxZ+i+1;if(c.groupId){if(!groupMap.has(c.groupId))groupMap.set(c.groupId,groupId());c.groupId=groupMap.get(c.groupId);}page().layers.push(c);ids.push(c.id);});selectedIds=new Set(ids);selectedId=ids.at(-1)||null;markDirty();render();return true;}
    function deleteSelectedLayers(){const ids=[...selectedIds].filter(id=>!page().layers.find(l=>l.id===id)?.locked);if(!ids.length)return false;pushHistory();page().layers=page().layers.filter(l=>!ids.includes(l.id));clearSelection();markDirty();render();return true;}
    function requestDeleteSelectedLayers(){
        const locked=selectedLayers().find(l=>l.locked||(isInstanceMode&&l.customerEditPolicy==='locked'));
        if(locked){const payload={kind:'layer',reason:'locked-layer',pageId:activePageId,layerId:locked.id,message:'Elemen terkunci. Buka kunci sebelum menghapus.'};if(typeof window.showDeleteBlockedNotice==='function')window.showDeleteBlockedNotice(payload);else notify(payload.message);return false;}
        return deleteSelectedLayers();
    }
    function toggleSelectionLock(){
        const list=selectedLayers();if(!list.length)return false;
        pushHistory();const shouldLock=!list.every(l=>l.locked);list.forEach(l=>l.locked=shouldLock);
        markDirty();render();notify(shouldLock?'Elemen dikunci':'Kunci elemen dibuka');return true;
    }

    function appendSelectionChrome(el,l){
        if(l.id!==selectedId||selectedIds.size!==1)return;
        ['nw','n','ne','e','se','s','sw','w'].forEach(pos=>{
            const h=document.createElement('div');h.className='us-selection-handle';h.dataset.handle=pos;el.appendChild(h);
        });
        const stem=document.createElement('div');stem.className='us-rotate-stem';el.appendChild(stem);
        const rot=document.createElement('div');rot.className='us-rotate-handle';rot.dataset.handle='rotate';el.appendChild(rot);
        if(cropMode&&cropMode.layerId===l.id){
            el.classList.add('crop-active');
        }
    }


    const elementAnimationPreviews=new Map();
    const elementAnimationVersions=new Map();
    const canvasAnimationPreviews=new Map();
    const canvasAnimationVersions=new Map();
    function nextElementAnimationVersion(id){const v=(elementAnimationVersions.get(id)||0)+1;elementAnimationVersions.set(id,v);return v;}
    function nextCanvasAnimationVersion(id){const v=(canvasAnimationVersions.get(id)||0)+1;canvasAnimationVersions.set(id,v);return v;}
    function animationCss(l){const a=elementAnimation(l);if(a.type==='none')return '';const map={'fade':'usFade','fade-up':'usFadeUp','fade-down':'usFadeDown','fade-left':'usFadeLeft','fade-right':'usFadeRight','zoom':'usZoom','pop':'usPop','soft-scale':'usSoftScale','blur-in':'usBlurIn','focus-in':'usFocusIn','reveal-up':'usRevealUp','wipe-up':'usWipeUp','wipe-left':'usWipeLeft','flash':'usFlash','flicker':'usFlicker','breathe':'usBreathe','glow-pulse':'usGlowPulse','float':'usFloat'};const name=map[a.type];if(!name)return '';return `${name} ${a.duration}s ${a.easing||'ease-out'} ${a.delay}s 1 both`;}
    function cleanupElementAnimationPreview(layerId,reason='cleanup'){const p=elementAnimationPreviews.get(layerId);if(!p)return;elementAnimationPreviews.delete(layerId);clearTimeout(p.timer);p.content?.removeEventListener('animationend',p.onEnd);if(p.content){p.content.style.animation='';p.content.classList.remove('us-editor-animation-preview');}interactionDiag('ANIMATION_PREVIEW_CLEANUP',{scope:'element',layerId,canvasId:p.canvasId,animationType:p.animationType,reason});}
    function cancelElementAnimationPreview(layerId,reason='cancel'){if(!elementAnimationPreviews.has(layerId))return;nextElementAnimationVersion(layerId);interactionDiag('ANIMATION_PREVIEW_CANCEL',{scope:'element',layerId,canvasId:activePageId,animationType:elementAnimation(page().layers.find(x=>x.id===layerId)).type,reason});cleanupElementAnimationPreview(layerId,reason);}
    function startElementAnimationPreview(layerId){const l=page().layers.find(x=>x.id===layerId);if(!l)return;const a=elementAnimation(l);cancelElementAnimationPreview(layerId,'preset-change');if(a.type==='none')return;const el=liveLayerNode(layerId),content=el?.querySelector('.us-layer-content');if(!content)return;const version=nextElementAnimationVersion(layerId),css=animationCss(l);if(!css)return;const finish=()=>{if(elementAnimationVersions.get(layerId)!==version)return;interactionDiag('ANIMATION_PREVIEW_END',{scope:'element',layerId,canvasId:activePageId,animationType:a.type,reason:'animation-end'});cleanupElementAnimationPreview(layerId,'animation-end');};content.style.animation='none';void content.offsetWidth;content.classList.add('us-editor-animation-preview');content.style.animation=css;const timer=setTimeout(()=>{if(elementAnimationVersions.get(layerId)!==version)return;interactionDiag('ANIMATION_PREVIEW_END',{scope:'element',layerId,canvasId:activePageId,animationType:a.type,reason:'timeout'});cleanupElementAnimationPreview(layerId,'timeout');},Math.max(350,(a.delay+a.duration)*1000+250));elementAnimationPreviews.set(layerId,{canvasId:activePageId,content,onEnd:finish,timer,animationType:a.type});content.addEventListener('animationend',finish,{once:true});interactionDiag('ANIMATION_PREVIEW_START',{scope:'element',layerId,canvasId:activePageId,animationType:a.type,reason:'preset-click'});}
    function canvasAnimationNode(canvasId){return livePages?.querySelector(`.us-canvas-animation-shell[data-page-id="${CSS.escape(String(canvasId))}"]`)||null;}
    function cleanupCanvasAnimationPreview(canvasId,reason='cleanup'){const p=canvasAnimationPreviews.get(canvasId);if(!p)return;canvasAnimationPreviews.delete(canvasId);clearTimeout(p.timer);p.node?.removeEventListener('animationend',p.onEnd);if(p.node){p.node.style.animation='';p.node.classList.remove('us-editor-canvas-animation-preview');}interactionDiag('ANIMATION_PREVIEW_CLEANUP',{scope:'canvas',canvasId,animationType:p.animationType,reason});}
    function cancelCanvasAnimationPreview(canvasId,reason='cancel'){if(!canvasAnimationPreviews.has(canvasId))return;nextCanvasAnimationVersion(canvasId);interactionDiag('ANIMATION_PREVIEW_CANCEL',{scope:'canvas',canvasId,animationType:canvasAnimation(state.pages.find(x=>String(x.id)===String(canvasId))).type,reason});cleanupCanvasAnimationPreview(canvasId,reason);}
    function startCanvasAnimationPreview(canvasId=activePageId){const p=state.pages.find(x=>String(x.id)===String(canvasId));if(!p)return;const a=canvasAnimation(p);cancelCanvasAnimationPreview(canvasId,'preset-change');if(a.type==='none')return;const node=canvasAnimationNode(canvasId);if(!node)return;const version=nextCanvasAnimationVersion(canvasId),map={'fade':'pageFade','fade-soft':'pageFadeSoft','slide-up':'pageSlideUp','slide-down':'pageSlideDown','slide-left':'pageSlideLeft','slide-right':'pageSlideRight','zoom':'pageZoom','blur':'pageBlur','focus-in':'pageFocusIn','wipe-up':'pageWipeUp','wipe-left':'pageWipeLeft'},name=map[a.type];if(!name)return;const finish=()=>{if(canvasAnimationVersions.get(canvasId)!==version)return;interactionDiag('ANIMATION_PREVIEW_END',{scope:'canvas',canvasId,animationType:a.type,reason:'animation-end'});cleanupCanvasAnimationPreview(canvasId,'animation-end');};node.style.animation='none';void node.offsetWidth;node.classList.add('us-editor-canvas-animation-preview');node.style.animation=`${name} ${a.duration}s ${a.easing||'ease-in-out'} ${a.delay}s 1 both`;const timer=setTimeout(()=>{if(canvasAnimationVersions.get(canvasId)!==version)return;interactionDiag('ANIMATION_PREVIEW_END',{scope:'canvas',canvasId,animationType:a.type,reason:'timeout'});cleanupCanvasAnimationPreview(canvasId,'timeout');},Math.max(350,(a.delay+a.duration)*1000+250));canvasAnimationPreviews.set(canvasId,{node,onEnd:finish,timer,animationType:a.type});node.addEventListener('animationend',finish,{once:true});interactionDiag('ANIMATION_PREVIEW_START',{scope:'canvas',canvasId,animationType:a.type,reason:'preset-click'});}
    function cancelAllAnimationPreviews(reason='cancel-all'){[...elementAnimationPreviews.keys()].forEach(id=>cancelElementAnimationPreview(id,reason));[...canvasAnimationPreviews.keys()].forEach(id=>cancelCanvasAnimationPreview(id,reason));}
    function previewSingleLayer(id){startElementAnimationPreview(id);}

    function bindingPlaceholder(l){
        const label=bindingLabel(l.binding||'none',l);
        return label&&label!=='Tidak terhubung'?label:'Isi pelanggan';
    }
    function customerTextValue(binding){
        const raw=customerData?.[binding];
        if(raw===null||raw===undefined)return '';
        return typeof raw==='string'||typeof raw==='number'?String(raw):'';
    }
    function studioCustomerMediaUrlKey(value){
        const raw=String(value||'').trim();
        if(!raw)return '';
        try{
            const u=new URL(raw,window.location.origin);
            return decodeURIComponent(u.pathname).replace(/\/+$/,'');
        }catch(_){
            return raw.split('?')[0].split('#')[0].replace(/\/+$/,'');
        }
    }
    function studioCustomerAssetFor(raw){
        if(!raw)return null;
        const list=imageAssets();
        if(typeof raw==='object'){
            const rawId=raw.asset_id??raw.assetId??raw.id??null;
            if(rawId!==null&&rawId!==undefined&&String(rawId)!==''){
                const byId=list.find(a=>String(a.id)===String(rawId));
                if(byId)return byId;
                return null;
            }
            const rawUrl=raw.url||'';
            if(rawUrl){
                const key=studioCustomerMediaUrlKey(rawUrl);
                const byUrl=list.find(a=>studioCustomerMediaUrlKey(a.url)===key);
                return byUrl||null;
            }
            return null;
        }
        if(typeof raw==='string'){
            const key=studioCustomerMediaUrlKey(raw);
            return list.find(a=>studioCustomerMediaUrlKey(a.url)===key)||null;
        }
        return null;
    }
    function studioCustomerMediaIsStale(raw){
        if(!raw)return false;
        return !studioCustomerAssetFor(raw);
    }
    function customerMediaValue(binding){
        const raw=customerData?.[binding];
        if(!raw)return '';
        const asset=studioCustomerAssetFor(raw);
        return asset?.url||'';
    }    function effectiveCellSrc(cell){
        const binding=cell?.binding||'none';
        if(editorViewMode==='sample'||binding==='none')return cell?.src||'';
        if(editorViewMode==='empty')return '';
        return customerMediaValue(binding)||'';
    }
    function effectiveText(l){
        if(editorViewMode==='sample'||!l.binding||l.binding==='none')return l.text||'';
        if(editorViewMode==='empty')return '';
        return customerTextValue(l.binding);
    }
    function effectiveMediaSrc(l){
        if(!l.binding||l.binding==='none')return null;
        if(editorViewMode==='sample')return null;
        if(editorViewMode==='empty')return '';
        return customerMediaValue(l.binding);
    }
    function shouldHideLayerForMode(l){
        if(!l.binding||l.binding==='none'||!l.hideWhenEmpty)return false;
        if(editorViewMode==='sample')return false;
        if(l.type==='text')return !effectiveText(l);
        if(['image','frame'].includes(l.type))return !effectiveMediaSrc(l);
        if(l.type==='grid'){
            const count=cellCountForGrid(l.gridKind||'4');ensureCells(l,count);
            return !l.cells.some(c=>effectiveCellSrc(c));
        }
        return false;
    }

    function deviceProfile(device=editorDeviceMode){
        // Invitation content always keeps the HP design ratio on every preview device.
        // Tablet/Desktop change the surrounding editor context only.
        if(device==='tablet')return {key:'tablet',label:'Tablet',width:390,height:844};
        if(device==='desktop')return {key:'desktop',label:'Desktop',width:390,height:844};
        return {key:'mobile',label:'HP',width:390,height:844};
    }
    function basePageSize(p){
        return {width:Number(p?.width||state.width||390),height:Number(p?.height||state.height||844)};
    }
    function displayPageSize(p,device=editorDeviceMode){
        const d=deviceProfile(device);
        return {width:d.width,height:d.height};
    }
    function displayScaleFor(p,device=editorDeviceMode){
        const b=basePageSize(p),d=displayPageSize(p,device);
        return {sx:d.width/b.width,sy:d.height/b.height,fs:Math.min(d.width/b.width,d.height/b.height),width:d.width,height:d.height};
    }
    function displayLayerGeometry(l,p,device=editorDeviceMode){
        const s=displayScaleFor(p,device);

        // Device switching must NEVER stretch an element.
        // Position follows the responsive viewport via sx/sy, but the
        // element's own dimensions always use one uniform visual scale.
        const baseX=Number(l.x||0),baseY=Number(l.y||0);
        const baseW=Math.max(1,Number(l.width||1)),baseH=Math.max(1,Number(l.height||1));
        const cx=(baseX+baseW/2)*s.sx;
        const cy=(baseY+baseH/2)*s.sy;
        const width=baseW*s.fs;
        const height=baseH*s.fs;

        return {
            x:cx-width/2,
            y:cy-height/2,
            width,
            height,
            fontSize:Number(l.fontSize||16)*s.fs
        };
    }
    function baseToDisplayPoint(p,x,y,device=editorDeviceMode){
        const s=displayScaleFor(p,device);
        return {x:Number(x||0)*s.sx,y:Number(y||0)*s.sy};
    }
    function displaySafeBounds(p){
        const b={
            left:safeAreaMargin,
            top:safeAreaMargin,
            right:Number(p.width||390)-safeAreaMargin,
            bottom:Number(p.height||844)-safeAreaMargin
        };
        const tl=baseToDisplayPoint(p,b.left,b.top),br=baseToDisplayPoint(p,b.right,b.bottom);
        return {left:tl.x,top:tl.y,width:Math.max(0,br.x-tl.x),height:Math.max(0,br.y-tl.y)};
    }
    function canvasForPageId(id){
        return livePages?.querySelector(`.us-canvas[data-page-id="${CSS.escape(String(id))}"]`)||null;
    }
    function shellForPageId(id){
        return livePages?.querySelector(`.us-live-page[data-page-id="${CSS.escape(String(id))}"]`)||null;
    }
    function setCanvasContext(pageId,{clear=false}={}){
        if(!pageId)return false;
        const target=state.pages.find(p=>String(p.id)===String(pageId));
        if(!target)return false;
        if(String(activePageId)!==String(target.id)){
            activePageId=target.id;
            if(clear)clearSelection();
            cropMode=null;
        }
        canvas=canvasForPageId(activePageId);
        return !!canvas;
    }
    function setCanvasContextFromEvent(e,{clear=false}={}){
        const c=e.target?.closest?.('.us-canvas[data-page-id]');
        if(!c)return false;
        const ok=setCanvasContext(c.dataset.pageId,{clear});
        canvas=c;
        return ok;
    }

    function appendSafeAreaTo(target,p){
        if(!safeAreaEnabled)return;
        const b=displaySafeBounds(p);
        const el=document.createElement('div');
        el.className='us-safe-area';
        el.style.left=b.left+'px';el.style.top=b.top+'px';
        el.style.width=b.width+'px';el.style.height=b.height+'px';
        target.appendChild(el);
    }
    function appendGuidesTo(target,p){
        if(preview)return;
        const s=displayScaleFor(p);
        (p.guides||[]).slice(0,2).forEach((y,i)=>{
            const g=document.createElement('div');
            g.className='us-guide-line';
            g.dataset.guideIndex=String(i);
            g.dataset.label=i===0?'Batas 1':'Batas 2';
            g.style.top=(Number(y||0)*s.sy)+'px';
            const h=document.createElement('div');h.className='us-guide-handle';h.dataset.guideIndex=String(i);
            g.appendChild(h);target.appendChild(g);
        });
    }
    function appendGroupSelectionChromeTo(target,p){
        if(String(p.id)!==String(activePageId)||selectedIds.size<2)return;
        const b=selectionBounds();if(!b)return;
        const s=displayScaleFor(p);
        const box=document.createElement('div');box.className='us-group-box';box.dataset.groupSelection='1';
        box.style.left=(b.left*s.sx)+'px';box.style.top=(b.top*s.sy)+'px';
        box.style.width=(b.width*s.sx)+'px';box.style.height=(b.height*s.sy)+'px';
        ['nw','ne','se','sw'].forEach(pos=>{const h=document.createElement('div');h.className='us-group-handle';h.dataset.groupHandle=pos;box.appendChild(h);});
        const badge=document.createElement('div');badge.className='us-group-badge';badge.textContent=`${selectedIds.size} elemen`;box.appendChild(badge);
        target.appendChild(box);
    }
    function renderOneLivePage(p,index){
        const ds=displayPageSize(p);
        const scale=Math.max(.25,Math.min(1.2,currentEditorZoom/100));

        const section=document.createElement('section');
        section.className='us-live-page'+(String(p.id)===String(activePageId)?' active':'');
        section.dataset.pageId=p.id;
        section.style.setProperty('--page-display-width',(ds.width*scale)+'px');
        section.style.setProperty('--page-display-height',(ds.height*scale)+'px');

        const head=document.createElement('div');
        head.className='us-live-page-head';
        head.innerHTML=`
            <div class="us-live-page-title-wrap">
                <span class="us-live-page-number">${index+1}.</span>
                <input class="us-inline-page-name" data-page-name-id="${esc(p.id)}" type="text" value="${esc(p.name||'')}" placeholder="Nama halaman (opsional)" aria-label="Nama halaman">
            </div>
            <div class="us-live-page-head-actions">
                <button type="button" class="us-live-page-action add" data-live-page-action="add" data-page-id="${esc(p.id)}" title="Tambah halaman setelah ini">+</button>
                <button type="button" class="us-live-page-action delete" data-live-page-action="delete" data-page-id="${esc(p.id)}" title="Hapus halaman" ${state.pages.length<=1?'disabled':''}>−</button>
            </div>`;
        section.appendChild(head);

        const shell=document.createElement('div');
        shell.className='us-canvas-shell';
        shell.dataset.pageId=p.id;
        shell.style.width=(ds.width*scale)+'px';
        shell.style.height=(ds.height*scale)+'px';

        const target=document.createElement('div');
        target.className='us-canvas';
        target.dataset.pageId=p.id;
        target.style.width=ds.width+'px';
        target.style.height=ds.height+'px';
        target.style.transform=`scale(${scale})`;
        target.style.background=p.background||'#fff';

        const s=displayScaleFor(p);
        [...(p.layers||[])].sort((a,b)=>(a.zIndex||0)-(b.zIndex||0)).forEach(l=>{
            if(shouldHideLayerForMode(l))return;
            const active=String(p.id)===String(activePageId);
            const isMulti=active&&selectedIds.has(l.id);
            const g=displayLayerGeometry(l,p);

            const el=document.createElement('div');
            el.className='us-layer'
                +(active&&l.id===selectedId&&selectedIds.size===1?' selected':'')
                +(isMulti&&selectedIds.size>1?' multi-selected':'')
                +(l.groupId?' group-member':'')
                +(safeAreaEnabled&&active&&isLayerOutsideSafe(l)?' outside-safe':'')
                +(l.locked?' locked':'')
                +(l.hidden?' hidden':'');
            el.dataset.id=l.id;
            el.dataset.pageId=p.id;
            el.dataset.editPolicy=l.customerEditPolicy||'full';
            el.style.left=g.x+'px';el.style.top=g.y+'px';
            el.style.width=g.width+'px';el.style.height=g.height+'px';
            el.style.opacity=l.opacity;el.style.zIndex=l.zIndex;
            el.style.transform=`rotate(${l.rotation||0}deg)`;
            el.style.animation='none';

            const content=document.createElement('div');
            content.className='us-layer-content';
            content.style.borderRadius=(Number(l.borderRadius||0)*s.fs)+'px';

            if(l.type==='text'){
                content.classList.add('us-text');
                content.style.fontFamily=l.fontFamily||'Arial';
                content.style.fontSize=g.fontSize+'px';
                content.style.fontWeight=l.fontWeight||400;
                content.style.fontStyle=l.fontStyle||'normal';
                content.style.color=l.color||'#222';
                content.style.textAlign=l.textAlign||'left';
                content.style.justifyContent=l.textAlign==='center'?'center':(l.textAlign==='right'?'flex-end':'flex-start');
                content.style.background=l.background||'transparent';
                const shownText=effectiveText(l);
                if(shownText)content.textContent=shownText;
                else if(l.binding&&l.binding!=='none')content.innerHTML=`<div class="us-empty-binding">${esc(bindingPlaceholder(l))}</div>`;
                else content.textContent='';
            }else if(l.type==='image'){
                const override=effectiveMediaSrc(l);
                if(editorViewMode!=='sample'&&l.binding&&l.binding!=='none'){
                    content.innerHTML=override?`<img class="us-image" src="${esc(override)}" alt="">`:`<div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div>`;
                }else content.innerHTML=`<img class="us-image" src="${esc(l.src)}" alt="">`;
            }else if(l.type==='video'){
                content.innerHTML=editorVideoMarkup(l);
            }else if(l.type==='frame'){
                content.classList.add('us-frame-layer');content.style.background='transparent';
                const override=effectiveMediaSrc(l);
                if(editorViewMode!=='sample'&&l.binding&&l.binding!=='none'){
                    if(override){
                        ensureCells(l,1);
                        const clone=deep(l);clone.cells[0]=Object.assign({},clone.cells[0]||{posX:0,posY:0,scale:1},{src:override});
                        content.innerHTML=renderFrameContent(clone);
                    }else content.innerHTML=`<div class="us-frame-inner"><div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div></div>`;
                }else content.innerHTML=renderFrameContent(l);
            }else if(l.type==='grid'){
                content.classList.add('us-grid-layer');content.style.background='transparent';content.dataset.galleryAnimation=l.galleryAnimation||'fade';
                if(editorViewMode==='sample')content.innerHTML=renderGridContent(l);
                else{
                    const clone=deep(l),count=cellCountForGrid(clone.gridKind||'4');ensureCells(clone,count);
                    clone.cells=clone.cells.map(c=>Object.assign({},c,{src:effectiveCellSrc(c)}));
                    content.innerHTML=renderGridContent(clone);
                }
            }else{
                content.classList.add('us-shape');shapeStyle(l,content);
            }

            if(l.type==='image'){
                const media=content.querySelector('img,video');
                if(media){media.style.transform=mediaTransformCss(l,`scale(${Number(l.scale||1)})`);media.style.transformOrigin='center center';media.style.filter=mediaFilterCss(l);}
            }else if(l.type==='frame'){
                content.querySelectorAll('img,video').forEach(media=>media.style.filter=mediaFilterCss(l));
            }

            el.appendChild(content);
            if(l.locked){const lock=document.createElement('span');lock.className='us-lock-indicator';lock.setAttribute('aria-label','Terkunci');el.appendChild(lock);}
            if(active)appendSelectionChrome(el,l);
            target.appendChild(el);
        });

        if(String(p.id)===String(activePageId))appendGroupSelectionChromeTo(target,p);
        appendSafeAreaTo(target,p);
        appendGuidesTo(target,p);

        shell.appendChild(target);
        const canvasAnimShell=document.createElement('div');
        canvasAnimShell.className='us-canvas-animation-shell';
        canvasAnimShell.dataset.pageId=p.id;
        canvasAnimShell.style.width=(ds.width*scale)+'px';
        canvasAnimShell.style.height=(ds.height*scale)+'px';
        canvasAnimShell.style.minWidth=(ds.width*scale)+'px';
        canvasAnimShell.style.minHeight=(ds.height*scale)+'px';
        canvasAnimShell.appendChild(shell);
        section.appendChild(canvasAnimShell);
        return section;
    }

    let diagnosticRenderCanvasCount=0;
    function renderCanvas(){
        if(!livePages)return;
        if(window.__UNDANGANTA_DIAG__){
            diagnosticRenderCanvasCount++;
            interactionDiag('RENDER_CANVAS',{
                count:diagnosticRenderCanvasCount,
                canvasId:activePageId,
                selectedLayerId:selectedId,
                previewLayerId:[...elementAnimationPreviews.keys()][0]||null,
                dragActive:!!drag,
                resizeActive:!!resize,
                rotateActive:!!rotateDrag
            });
        }
        const oldScroll=canvasZone?.scrollTop||0;
        const oldLeft=canvasZone?.scrollLeft||0;

        livePages.innerHTML='';
        (state.pages||[]).filter(p=>p.role!=='desktop-cover').forEach((p,i)=>{
            livePages.appendChild(renderOneLivePage(p,i));
        });

        canvas=canvasForPageId(activePageId)||livePages.querySelector('.us-canvas');
        livePages.querySelectorAll('.us-live-page').forEach(s=>s.classList.toggle('active',String(s.dataset.pageId)===String(activePageId)));

        // Exact media geometry is applied before the browser's next paint.
        scheduleCellMediaGeometrySync('renderCanvas');
        requestAnimationFrame(hydrateEditorVideoPreviews);

        requestAnimationFrame(()=>{
            if(canvasZone){
                canvasZone.scrollTop=oldScroll;
                canvasZone.scrollLeft=oldLeft;
            }
            bindActiveCanvasObserver();
        });
    }
    function renderLayers(){
        layerList.innerHTML='';[...page().layers].sort((a,b)=>(b.zIndex||0)-(a.zIndex||0)).forEach(l=>{const row=document.createElement('div');row.className='us-layer-row'+(selectedIds.has(l.id)?' active':'');row.dataset.id=l.id;row.innerHTML=`<b>${l.hidden?'◌':(l.locked?'🔒':'◇')}</b><span>${esc(l.name||l.type)}${l.binding&&l.binding!=='none'?`<span class="us-binding-badge">${esc(bindingLabel(l.binding,l))}</span>`:''}${l.animation&&l.animation!=='none'?`<span class="us-layer-anim-badge">◌ ${esc(l.animation)}</span>`:''}</span><small>${l.zIndex}</small>`;layerList.appendChild(row);});
    }
    function rebuildFontSelect(){const s=$('pFont'),cur=selected()?.fontFamily||'Arial';s.innerHTML=['Arial','Georgia','Times New Roman','Verdana'].map(f=>`<option value="${f}">${f}</option>`).join('')+fonts.map(f=>`<option value="${esc(f.family)}">${esc(f.name)}</option>`).join('');s.value=cur;}
    function renderProps(){
        const p=page();$('pageName').value=p.name;$('canvasBg').value=(p.background&&p.background.startsWith('#'))?p.background:'#ffffff';const ca=canvasAnimation(p);$('pageTransition').value=ca.type;$('pageDuration').value=ca.duration;$('pageEasing').value=ca.easing||'ease-in-out';$('canvasAnimationHint').textContent=`${p.name||'Halaman'} • ${ca.type}`;document.querySelectorAll('[data-canvas-animation-preset]').forEach(b=>b.classList.toggle('active',b.dataset.canvasAnimationPreset===ca.type));$('pageRole').value=p.role||'section';$('pageWidth').value=Number(p.width||390);$('pageHeight').value=Number(p.height||844);syncPageTypeForDevice();$('desktopLayout').value=state.settings?.desktopLayout||'cover-left';$('desktopCoverWidth').value=Number(state.settings?.desktopCoverWidth||64);$('mobileBreakpoint').value=String(state.settings?.mobileBreakpoint||768);
        const l=selected();if(l&&$('responsiveMode'))$('responsiveMode').value=l.responsiveMode||'scale';
        const multiTools=$('multiTools');if(multiTools)multiTools.hidden=selectedIds.size<2;if(selectedIds.size>1){props.hidden=true;empty.hidden=false;empty.innerHTML=`<b>${selectedIds.size} elemen dipilih</b><div class="us-multi-note">Geser/resize bersama, align, distribute, atau group dari panel Properti.</div>`;return;}
        if(!l){props.hidden=true;empty.hidden=false;empty.textContent='Pilih elemen di canvas untuk mengedit propertinya.';return;}
        props.hidden=false;empty.hidden=true;
        $('pName').value=l.name||'';$('pX').value=l.x;$('pY').value=l.y;$('pW').value=l.width;$('pH').value=l.height;$('pRot').value=l.rotation;$('pOpacity').value=l.opacity;$('pBg').value=(l.background&&l.background.startsWith('#'))?l.background:'#BFD8FF';$('pRadius').value=l.borderRadius;$('elementColorField').hidden=l.type!=='shape';const ea=elementAnimation(l);$('pAnimation').value=ea.type;$('pDuration').value=ea.duration;$('pDelay').value=ea.delay;$('pLoop').checked=!!ea.loop;if($('elementAnimationLoop'))$('elementAnimationLoop').checked=!!ea.loop;$('pLocked').checked=!!l.locked;$('pHidden').checked=!!l.hidden;$('textFields').hidden=l.type!=='text';$('animTrigger').value=ea.trigger||'on-enter';$('animEasing').value=ea.easing||'ease-out';$('animationSelectedHint').textContent=`${l.name||l.type} • ${ea.type} • ${Number(ea.duration||.8).toFixed(1)}s`;document.querySelectorAll('[data-element-animation-preset]').forEach(b=>b.classList.toggle('active',b.dataset.elementAnimationPreset===ea.type));
        const bindingOptions=bindingOptionsFor(l);
        $('layerBindingField').hidden=l.type==='grid';
        $('pBinding').innerHTML=bindingOptions.map(([v,label])=>`<option value="${v}">${label}</option>`).join('');
        $('pBinding').value=bindingOptions.some(x=>x[0]===l.binding)?l.binding:'none';
        $('pCustomerEditPolicy').value=l.customerEditPolicy||'full';$('pOptional').checked=!!l.optional;$('pHideWhenEmpty').checked=!!l.hideWhenEmpty;
        const hint=l.binding&&l.binding!=='none'
            ? `${bindingLabel(l.binding,l)} • ${editPolicyLabel(l.customerEditPolicy||'full')}${l.binding==='quote'||l.binding==='prayer'?' • teks default tetap bisa diganti pelanggan':''}`
            : 'Tidak terhubung ke data undangan. Elemen tetap menjadi elemen desain biasa.';
        $('bindingHint').textContent=hint;
        const isMediaContainer=l.type==='frame'||l.type==='grid';$('mediaFields').hidden=!isMediaContainer;
        if(l.type==='text'){$('pText').value=l.text||'';$('pFontSize').value=l.fontSize||28;$('pColor').value=l.color||'#222';$('pAlign').value=l.textAlign||'left';rebuildFontSelect();if($('textPanelHint'))$('textPanelHint').textContent='Mengedit: '+(l.name||'Teks');}
        else if($('textPanelHint'))$('textPanelHint').textContent='Pilih layer teks untuk mengubah font, ukuran, warna, dan isi.';
        if(isMediaContainer){
            $('frameKindFields').hidden=l.type!=='frame';
            if(l.type==='frame')$('pFrameKind').value=l.frameKind||'square';
            const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');ensureCells(l,count);activeCellIndex=clamp(activeCellIndex,0,count-1);
            const cell=l.cells[activeCellIndex]||{src:null,posX:0,posY:0,scale:1};
            $('pMediaCell').innerHTML=Array.from({length:count},(_,i)=>`<option value="${i}">Kotak ${i+1}</option>`).join('');$('pMediaCell').value=String(activeCellIndex);
            $('cellBindingField').hidden=l.type!=='grid';
            if(l.type==='grid'){
                $('pCellBinding').innerHTML=mediaBindings().map(([v,label])=>`<option value="${v}">${label}</option>`).join('');
                $('pCellBinding').value=mediaBindings().some(x=>x[0]===(cell.binding||'none'))?(cell.binding||'none'):'none';
                $('cellBindingHint').textContent=(cell.binding&&cell.binding!=='none')
                    ? `Kotak ${activeCellIndex+1} → ${bindingLabel(cell.binding,{type:'grid'})}`
                    : `Kotak ${activeCellIndex+1} belum terhubung ke data pelanggan.`;
            }
            $('pMediaZoom').value=cell.scale||1;$('pMediaX').value=cell.posX||0;$('pMediaY').value=cell.posY||0;$('pGridGap').value=l.type==='grid'?(l.gap||0):0;$('pGridGap').disabled=l.type!=='grid';$('gallerySettings').hidden=l.type!=='grid';if(l.type==='grid'){$('pGalleryAnimation').value=l.galleryAnimation||'fade';$('pGalleryAutoplay').value=l.galleryAutoplay?'1':'0';$('pGalleryInterval').value=Number(l.galleryInterval||4);$('pGalleryLightbox').checked=l.galleryLightbox!==false;$('pGalleryThumbnails').checked=l.galleryThumbnails!==false;}
        }
    }
    function renderPages(){
        const bar=$('pagesBar');bar.innerHTML='';
        state.pages.forEach((p,i)=>{
            const chip=document.createElement('button');
            chip.type='button';
            chip.className='us-page-chip'+(p.id===activePageId?' active':'');
            chip.draggable=true;chip.dataset.id=p.id;
            const navSize=deviceProfile();
            chip.innerHTML=`<span class="us-page-thumb-preview" style="background:${esc(p.background||'#fff')}"><span>${i+1}</span></span><span class="us-page-chip-copy"><b>${i+1}. ${esc(p.name)} <span class="us-page-type-badge">${p.role==='desktop-cover'?'DESKTOP':p.role==='hero'?'HERO':'SECTION'}</span></b><small>${navSize.width}×${navSize.height}</small></span><span class="us-page-more" data-page-more="${p.id}" aria-label="Menu halaman">•••</span>`;
            bar.appendChild(chip);
            if(i<state.pages.length-1){const t=document.createElement('div');t.className='us-transition-chip';t.innerHTML=`<button type="button" class="us-transition-chip-btn" data-transition-page="${p.id}">↔ ${esc(p.transition?.type||'none')}</button>`;bar.appendChild(t);}
        });
        const add=document.createElement('button');add.type='button';add.className='us-add-page us-mobile-add-page';add.id='addPage';add.textContent='+';add.setAttribute('aria-label','Tambah halaman');add.title='Tambah halaman';bar.appendChild(add);
        const idx=state.pages.findIndex(p=>p.id===activePageId);$('canvasCounter').textContent=`Halaman ${idx+1} / ${state.pages.length}`;if($('bottomCounter'))$('bottomCounter').textContent=`${idx+1}/${state.pages.length}`;$('prevCanvas').disabled=idx<=0;$('nextCanvas').disabled=idx>=state.pages.length-1;$('deletePage').disabled=state.pages.length<=1;
        renderPageGrid();
    }
    let editorDeviceMode='mobile';
    let openingMediaDevice='mobile';

    let currentEditorZoom=68;

    function editorDeviceLabel(device){
        return device==='mobile'?'HP':(device==='tablet'?'Tablet':'Desktop');
    }
    function syncPageTypeForDevice(){/* automatic page role; no manual UI */}


    function syncEditorDocumentLayout(){
        if(!editorDocument)return;
        editorDocument.dataset.device=editorDeviceMode;
        editorDocument.classList.remove('mobile','tablet','desktop','sticky-left','sticky-right','centered');
        editorDocument.classList.add(editorDeviceMode);

        const stickyLayout=state.settings?.desktopLayout||'cover-left';
        const showSticky=editorDeviceMode==='desktop'&&stickyLayout!=='centered';
        if(showSticky)editorDocument.classList.add(stickyLayout==='cover-right'?'sticky-right':'sticky-left');
        else editorDocument.classList.add('centered');

        if(editorStickyCover){
            editorStickyCover.hidden=!showSticky;
            editorStickyCover.innerHTML='';
            if(showSticky)editorStickyCover.appendChild(buildDedicatedDesktopCover());
        }

        const meta=deviceProfile();
        if($('editorDeviceHint'))$('editorDeviceHint').textContent=`${meta.label} · ${meta.width} × ${meta.height}`;
    }

    function setEditorDeviceMode(device){
        editorDeviceMode=['mobile','tablet','desktop'].includes(device)?device:'mobile';
        document.querySelector('.us-workspace')?.setAttribute('data-editor-device',editorDeviceMode);
        document.querySelectorAll('[data-editor-device]').forEach(btn=>btn.classList.toggle('active',btn.dataset.editorDevice===editorDeviceMode));
        openingMediaDevice=editorDeviceMode;
        syncOpeningCoverControls();
        syncEditorDocumentLayout();
        syncPageTypeForDevice();
        renderCanvas();
        renderProps();
        renderPages();

        const ratioLayer=selected();
        if(ratioLayer){
            const rg=displayLayerGeometry(ratioLayer,page(),editorDeviceMode);
            interactionDiag('DEVICE_RATIO_CHECK',{
                pageId:activePageId,
                layerId:ratioLayer.id,
                layerType:ratioLayer.type,
                device:editorDeviceMode,
                baseRatio:Number(ratioLayer.width||1)/Math.max(1,Number(ratioLayer.height||1)),
                displayRatio:Number(rg.width||1)/Math.max(1,Number(rg.height||1)),
                displayWidth:rg.width,
                displayHeight:rg.height
            });
        }

        requestAnimationFrame(()=>{
            fitCanvasForViewport();
        });
    }

    function ensureStableCanvasState(){
        if(!Array.isArray(state.pages))state.pages=[];
        if(!state.pages.length){
            state.pages.push(normalizePage({
                name:'Canvas 1',
                role:'hero',
                width:390,
                height:844,
                background:'#ffffff',
                layers:[]
            },0));
        }
        if(!state.pages.some(p=>p.id===activePageId)){
            activePageId=state.pages[0].id;
            clearSelection();
        }
    }

    function syncAutomaticPageRoles(){
        state.pages.forEach((p,index)=>{p.role=index===0?'hero':'section';});
    }

    function render(){
        syncAutomaticPageRoles();
        ensureStableCanvasState();
        syncEditorDocumentLayout();
        renderCanvas();
        renderLayers();
        renderSafeArea();
        renderProps();
        renderPages();
        renderDesktopCoverPanel();
        syncOpeningCoverControls();
        renderMobileContextToolbar();
        renderDesktopContextToolbar();
        requestAnimationFrame(ensureSelectionChromeVisible);
    }

    function updateSelected(key,value){const l=selected();if(!l)return;pushHistory();l[key]=value;markDirty();render();}
    function selectedSupportsMediaAdjustments(l=selected()){return !!l&&['image','frame'].includes(l.type);}
    function toggleSelectedFlip(axis){
        const l=selected();if(!selectedSupportsMediaAdjustments(l)||l.locked)return false;
        pushHistory();if(axis==='horizontal')l.flipX=!l.flipX;else if(axis==='vertical')l.flipY=!l.flipY;else return false;
        markDirty();render();return true;
    }
    function updateSelectedMediaVisuals(l=selected()){
        if(!selectedSupportsMediaAdjustments(l))return;
        const node=liveLayerNode(l.id);if(!node)return;
        if(l.type==='image'){
            const media=node.querySelector('.us-image,video');
            if(media){media.style.transform=mediaTransformCss(l,`scale(${Number(l.scale||1)})`);media.style.filter=mediaFilterCss(l);}
        }else{
            node.querySelectorAll('.us-media-cell img').forEach(media=>media.style.filter=mediaFilterCss(l));
            syncLayerCellMediaGeometry(l);
        }
    }

    let mobileContextToolbar=null,mobileContextSheet=null,mobileContextSnapshot=null,mobileContextChanged=false;
    function commitMobileContextEdit({renderNow=false}={}){
        if(mobileContextSnapshot===null)return;
        if(mobileContextChanged){
            history.push(mobileContextSnapshot);if(history.length>80)history.shift();future=[];
            mobileContextSnapshot=null;mobileContextChanged=false;markDirty();
            if(renderNow)render();
            return;
        }
        mobileContextSnapshot=null;mobileContextChanged=false;
    }
    function closeMobileContextSheet(){
        if(!mobileContextSheet)return;
        commitMobileContextEdit({renderNow:true});
        mobileContextSheet.classList.remove('open');mobileContextSheet.setAttribute('aria-hidden','true');
    }
    function handleMobileContextSheetClick(event){
            const sheet=event.currentTarget;if(!sheet)return;
            const close=event.target.closest('[data-context-sheet-close]');if(close){closeMobileContextSheet();return;}
            const tab=event.target.closest('[data-position-tab]');if(tab){
                sheet.querySelectorAll('[data-position-tab]').forEach(x=>x.classList.toggle('active',x===tab));
                sheet.querySelectorAll('[data-position-panel]').forEach(x=>x.hidden=x.dataset.positionPanel!==tab.dataset.positionTab);return;
            }
            const layer=event.target.closest('[data-context-layer]');if(layer){setLayerZ(selectedLayers(),layer.dataset.contextLayer);return;}
            const align=event.target.closest('[data-context-align]');if(align){alignSelection(align.dataset.contextAlign);return;}
            const textStyle=event.target.closest('[data-context-text-style]');if(textStyle){
                const l=selected();if(!l||l.type!=='text')return;pushHistory();
                if(textStyle.dataset.contextTextStyle==='bold')l.fontWeight=(Number(l.fontWeight||400)>=600||String(l.fontWeight).toLowerCase()==='bold')?400:700;
                if(textStyle.dataset.contextTextStyle==='italic')l.fontStyle=String(l.fontStyle||'normal')==='italic'?'normal':'italic';
                markDirty();render();openMobileTextStyleControl();return;
            }
            const textAlign=event.target.closest('[data-context-text-align]');if(textAlign){
                const l=selected();if(!l||l.type!=='text'||l.textAlign===textAlign.dataset.contextTextAlign)return;
                pushHistory();l.textAlign=textAlign.dataset.contextTextAlign;markDirty();render();openMobileTextStyleControl();return;
            }
            const shapeKind=event.target.closest('[data-context-shape-kind]');if(shapeKind){
                const l=selected();if(!l||l.type!=='shape'||l.shapeKind===shapeKind.dataset.contextShapeKind)return;
                pushHistory();l.shapeKind=shapeKind.dataset.contextShapeKind;markDirty();render();openMobileShapeControl();return;
            }
            const flip=event.target.closest('[data-context-flip]');if(flip){toggleSelectedFlip(flip.dataset.contextFlip);openMobileFlipControl();return;}
            const effectReset=event.target.closest('[data-context-effect-reset]');if(effectReset){
                const l=selected();if(!selectedSupportsMediaAdjustments(l))return;pushHistory();l.effects=normalizeMediaEffects(null);markDirty();render();openMobileEffectsControl();return;
            }
            const advanced=event.target.closest('[data-context-advanced]');if(advanced){
                if(advanced.dataset.contextAdvanced==='lock'){if(toggleSelectionLock())closeMobileContextSheet();}
                if(advanced.dataset.contextAdvanced==='duplicate')duplicateSelectedLayers();
            }
    }
    function ensureMobileContextUi(){
        if(mobileContextToolbar&&mobileContextSheet)return;
        mobileContextToolbar=document.createElement('div');mobileContextToolbar.id='usMobileContextToolbar';mobileContextToolbar.setAttribute('aria-label','Alat elemen terpilih');
        mobileContextSheet=document.createElement('div');mobileContextSheet.id='usMobileContextSheet';mobileContextSheet.className='us-mobile-sheet-surface';mobileContextSheet.setAttribute('aria-hidden','true');
        document.querySelector('.us-workspace')?.appendChild(mobileContextToolbar);document.querySelector('.us-wrap')?.appendChild(mobileContextSheet);
        mobileContextToolbar.addEventListener('click',event=>{
            const button=event.target.closest('[data-mobile-context-action]');if(!button)return;
            runSelectionContextAction(button.dataset.mobileContextAction);
        });
        mobileContextSheet.onclick=handleMobileContextSheetClick;
        document.addEventListener('keydown',event=>{if(event.key==='Escape')closeMobileContextSheet();});
    }
    function contextSheetFrame(title,body){
        ensureMobileContextUi();commitMobileContextEdit();document.querySelector('.us-library')?.classList.remove('mobile-open');document.querySelector('.us-properties-panel')?.classList.remove('mobile-open');document.getElementById('usSimpleMobileSheet')?.classList.remove('open');
        mobileContextSheet.innerHTML=`<div class="us-mobile-context-sheet-head"><b>${esc(title)}</b><button type="button" data-context-sheet-close aria-label="Tutup">Selesai</button></div><div class="us-mobile-context-sheet-body">${body}</div>`;
        mobileContextSheet.onclick=handleMobileContextSheetClick;
        mobileContextSheet.querySelectorAll('[data-context-layer]').forEach(button=>{
            button.onclick=event=>{event.stopPropagation();setLayerZ(selectedLayers(),button.dataset.contextLayer);};
        });
        mobileContextSheet.classList.add('open');mobileContextSheet.setAttribute('aria-hidden','false');
    }
    function openMobileRangeControl(title,key,min,max,step,shown,suffix){
        const l=selected();if(!l)return;
        const raw=key==='opacity'?Number(l[key]??1):Number(l[key]||0),rangeValue=key==='opacity'?Math.round(raw*100):raw;
        const rangeMin=key==='opacity'?0:min,rangeMax=key==='opacity'?100:max,rangeStep=key==='opacity'?1:step;
        contextSheetFrame(title,`<div class="us-mobile-range-row"><input data-context-range type="range" min="${rangeMin}" max="${rangeMax}" step="${rangeStep}" value="${rangeValue}"><output>${shown}${suffix}</output></div>`);
        mobileContextSnapshot=snapshot();mobileContextChanged=false;
        const input=mobileContextSheet.querySelector('[data-context-range]'),output=mobileContextSheet.querySelector('output');
        input.addEventListener('input',()=>{const current=selected();if(!current)return;const value=key==='opacity'?Number(input.value)/100:Number(input.value);current[key]=value;mobileContextChanged=true;output.textContent=`${input.value}${suffix}`;const node=liveLayerNode(current.id);if(node){if(key==='opacity')node.style.opacity=value;if(key==='borderRadius')node.querySelector('.us-layer-content').style.borderRadius=(value*displayScaleFor(page()).fs)+'px';}});
        input.addEventListener('change',()=>commitMobileContextEdit({renderNow:true}));
    }
    function openMobileColorControl(title,key){
        const l=selected();if(!l)return;const value=String(l[key]||'#222222');
        contextSheetFrame(title,`<div class="us-mobile-color-row"><input data-context-color type="color" value="${esc(value.startsWith('#')?value:'#222222')}"><span>${esc(value)}</span></div>`);
        mobileContextSnapshot=snapshot();mobileContextChanged=false;
        const input=mobileContextSheet.querySelector('[data-context-color]'),label=mobileContextSheet.querySelector('.us-mobile-color-row span');
        input.addEventListener('input',()=>{const current=selected();if(!current)return;current[key]=input.value;mobileContextChanged=true;label.textContent=input.value;const content=liveLayerNode(current.id)?.querySelector('.us-layer-content');if(content){if(key==='color')content.style.color=input.value;else content.style.background=input.value;}});
        input.addEventListener('change',()=>commitMobileContextEdit({renderNow:true}));
    }
    function openMobileTextStyleControl(){
        const l=selected();if(!l||l.type!=='text')return;
        const bold=Number(l.fontWeight||400)>=600||String(l.fontWeight).toLowerCase()==='bold',italic=String(l.fontStyle||'normal')==='italic',align=l.textAlign||'left';
        contextSheetFrame('Gaya teks',`<div class="us-mobile-context-option-grid"><button class="${bold?'active':''}" type="button" data-context-text-style="bold">Tebal</button><button class="${italic?'active':''}" type="button" data-context-text-style="italic">Miring</button><button class="${align==='left'?'active':''}" type="button" data-context-text-align="left">Kiri</button><button class="${align==='center'?'active':''}" type="button" data-context-text-align="center">Tengah</button><button class="${align==='right'?'active':''}" type="button" data-context-text-align="right">Kanan</button></div>`);
    }
    function openMobileShapeControl(){
        const l=selected();if(!l||l.type!=='shape')return;
        const kinds=[['rect','Persegi'],['rounded','Rounded'],['circle','Lingkaran'],['oval','Oval'],['line','Garis'],['triangle','Segitiga'],['star','Bintang'],['heart','Hati'],['hex','Polygon']];
        contextSheetFrame('Bentuk',`<div class="us-mobile-context-option-grid">${kinds.map(([key,label])=>`<button class="${(l.shapeKind||'rect')===key?'active':''}" type="button" data-context-shape-kind="${key}">${label}</button>`).join('')}</div>`);
    }
    function openMobilePositionControl(){
        const l=selected();if(!l)return;
        contextSheetFrame('Posisi',`<div class="us-mobile-position-tabs"><button class="active" type="button" data-position-tab="arrange">Atur</button><button type="button" data-position-tab="align">Ratakan</button><button type="button" data-position-tab="advanced">Lanjutan</button></div><div class="us-mobile-position-grid" data-position-panel="arrange"><button type="button" data-context-layer="forward">Maju</button><button type="button" data-context-layer="backward">Mundur</button><button type="button" data-context-layer="front">Paling depan</button><button type="button" data-context-layer="back">Paling belakang</button></div><div class="us-mobile-position-grid" data-position-panel="align" hidden><button type="button" data-context-align="top">Atas</button><button type="button" data-context-align="center-y">Tengah vertikal</button><button type="button" data-context-align="bottom">Bawah</button><button type="button" data-context-align="left">Kiri</button><button type="button" data-context-align="center-x">Tengah horizontal</button><button type="button" data-context-align="right">Kanan</button></div><div class="us-mobile-position-grid" data-position-panel="advanced" hidden><button type="button" data-context-advanced="lock">${l.locked?'Buka kunci':'Kunci'}</button><button type="button" data-context-advanced="duplicate">Duplikat</button></div>`);
    }
    function openMobileFlipControl(){
        const l=selected();if(!selectedSupportsMediaAdjustments(l))return;
        contextSheetFrame('Balik',`<div class="us-mobile-context-option-grid"><button class="${l.flipX?'active':''}" type="button" data-context-flip="horizontal">Horizontal</button><button class="${l.flipY?'active':''}" type="button" data-context-flip="vertical">Vertikal</button></div>`);
    }
    function openMobileEffectsControl(){
        const l=selected();if(!selectedSupportsMediaAdjustments(l))return;const effects=normalizeMediaEffects(l.effects);
        const controls=[['brightness','Brightness',0,200],['contrast','Contrast',0,200],['saturation','Saturation',0,200],['grayscale','Grayscale',0,100]];
        contextSheetFrame('Efek',`${controls.map(([key,label,min,max])=>`<label class="us-mobile-effect-row"><span>${label}</span><input data-context-effect-range="${key}" type="range" min="${min}" max="${max}" step="1" value="${effects[key]}"><output>${effects[key]}%</output></label>`).join('')}<div class="us-mobile-context-option-grid"><button type="button" data-context-effect-reset>Reset</button></div>`);
        mobileContextSnapshot=null;mobileContextChanged=false;
        mobileContextSheet.querySelectorAll('[data-context-effect-range]').forEach(input=>{
            const output=input.parentElement.querySelector('output');
            input.addEventListener('input',()=>{
                const current=selected();if(!selectedSupportsMediaAdjustments(current))return;
                if(mobileContextSnapshot===null)mobileContextSnapshot=snapshot();
                current.effects=normalizeMediaEffects(current.effects);current.effects[input.dataset.contextEffectRange]=Number(input.value);
                mobileContextChanged=true;output.textContent=input.value+'%';updateSelectedMediaVisuals(current);
            });
            input.addEventListener('change',()=>commitMobileContextEdit());
        });
    }
    function openSelectedProperties(){
        if(window.innerWidth<=780){setPropertiesCollapsed(false);return;}
        setPropertiesCollapsed(false);requestAnimationFrame(()=>document.querySelector('.us-properties-panel')?.scrollTo({top:0,behavior:'smooth'}));
    }
    function runSelectionContextAction(action){
        const l=selected();if(!l)return;
        if(['replace','crop','edit-text','font','animation','edit-properties'].includes(action))closeMobileContextSheet();
        if(action==='replace'){beginReplaceSelectedMedia();return;}
        if(action==='crop'){enterCropMode(l.id,l.type==='frame'?0:activeCellIndex);return;}
        if(action==='edit-text'){startInlineTextEdit(l.id);return;}
        if(action==='font'){openPanel('text');setTimeout(()=>document.getElementById('textFontSearch')?.focus(),230);return;}
        if(action==='text-style'){openMobileTextStyleControl();return;}
        if(action==='shape-kind'){openMobileShapeControl();return;}
        if(action==='flip'){openMobileFlipControl();return;}
        if(action==='effects'){openMobileEffectsControl();return;}
        if(action==='animation'){openPanel('animation');return;}
        if(action==='edit-properties'){openSelectedProperties();return;}
        if(action==='opacity'){openMobileRangeControl('Transparansi','opacity',0,1,.01,Math.round(Number(l.opacity??1)*100),'%');return;}
        if(action==='font-size'){openMobileRangeControl('Ukuran font','fontSize',6,120,1,Number(l.fontSize||28),'');return;}
        if(action==='radius'){openMobileRangeControl('Sudut','borderRadius',0,100,1,Number(l.borderRadius||0),' px');return;}
        if(action==='color'){openMobileColorControl(l.type==='text'?'Warna teks':'Warna elemen',l.type==='text'?'color':'background');return;}
        if(action==='position'){openMobilePositionControl();return;}
    }
    function renderMobileContextToolbar(){
        ensureMobileContextUi();const root=document.querySelector('.us-wrap'),l=selected();
        if(window.innerWidth>780||!l||selectedIds.size!==1||cropMode){mobileContextToolbar.hidden=true;root?.classList.remove('has-mobile-context');if(!l)closeMobileContextSheet();return;}
        let actions=[];
        if(['image','video','frame','grid'].includes(l.type)){
            actions=[['replace','Ganti'],...((l.type==='frame'||l.type==='grid')?[['crop','Pangkas']]:[]),...((l.type==='image'||l.type==='frame')?[['flip','Balik'],['effects','Efek']]:[]),['opacity','Transparansi'],['position','Posisi']];
        }else if(l.type==='text'){
            actions=[['edit-text','Edit'],['font','Font'],['text-style','Gaya teks'],['font-size','Ukuran'],['color','Warna'],['opacity','Transparansi'],['position','Posisi'],['animation','Animasi']];
        }else if(l.type==='shape'){
            actions=[['shape-kind','Bentuk'],['color','Warna'],['radius','Sudut'],['opacity','Transparansi'],['position','Posisi'],['animation','Animasi']];
        }
        mobileContextToolbar.replaceChildren(...actions.map(([key,label])=>{const button=document.createElement('button');button.type='button';button.dataset.mobileContextAction=key;button.textContent=label;return button;}));
        mobileContextToolbar.hidden=!actions.length;root?.classList.toggle('has-mobile-context',!!actions.length);
    }
    function renderDesktopContextToolbar(){
        const toolbar=$('desktopContextToolbar');if(!toolbar)return;
        if(window.innerWidth<=780||cropMode||selectedIds.size===0){toolbar.hidden=true;toolbar.replaceChildren();return;}
        const button=(action,label)=>`<button type="button" data-desktop-context-action="${action}">${label}</button>`;
        let html='';
        if(selectedIds.size>1){
            html=`<span class="us-desktop-context-count">${selectedIds.size} elemen</span>${button('align-menu','Rata elemen')}${button('layer-menu','Lapisan')}${button('duplicate','Duplikat')}${button('delete','Hapus')}${button('lock',selectedLayers().every(l=>l.locked)?'Buka kunci':'Kunci')}`;
        }else{
            const l=selected();if(!l){toolbar.hidden=true;return;}
            const lockButton=button('lock',l.locked?'Buka kunci':'Kunci');
            if(['image','video','frame','grid'].includes(l.type))html=`<span class="us-desktop-context-kind">Media</span>${button('replace','Ganti')}${(l.type==='frame'||l.type==='grid')?button('crop','Pangkas'):''}${(l.type==='image'||l.type==='frame')?button('flip','Balik')+button('effects','Efek'):''}${button('opacity','Transparansi')}${button('position','Posisi')}${lockButton}`;
            else if(l.type==='text')html=`<span class="us-desktop-context-kind">Teks</span>${button('edit-text','Edit')}${button('font','Font')}${button('text-style','Gaya teks')}${button('font-size','Ukuran font')}${button('color','Warna')}${lockButton}`;
            else if(l.type==='shape')html=`<span class="us-desktop-context-kind">Bentuk</span>${button('shape-kind','Bentuk')}${button('edit-properties','Edit')}${button('color','Warna')}${button('edit-properties','Gaya')}${button('radius','Sudut')}${lockButton}`;
        }
        toolbar.innerHTML=html;toolbar.hidden=!html;
    }
    function hideElementContextMenu(){$('elementContextMenu')?.classList.remove('open');}
    function showElementContextMenu(x,y){
        const menu=$('elementContextMenu');if(!menu||!selectedIds.size)return;
        const multi=selectedIds.size>1,l=selected();
        const showAlign=multi||!!l&&(['image','video','frame','grid','shape'].includes(l.type));
        menu.querySelector('.us-element-align-section').hidden=!showAlign;
        const alignLabel=menu.querySelector('[data-element-align-label]');if(alignLabel)alignLabel.textContent=multi?'Rata elemen':'Posisi';
        const pasteButton=menu.querySelector('[data-element-menu-action="paste"]');if(pasteButton)pasteButton.disabled=!layerClipboard.length;
        const lockLabel=menu.querySelector('[data-element-lock-label]');if(lockLabel)lockLabel.textContent=selectedLayers().every(l=>l.locked)?'Buka kunci':'Kunci';
        menu.style.left='0px';menu.style.top='0px';menu.classList.add('open');
        const rect=menu.getBoundingClientRect();
        menu.style.left=Math.max(8,Math.min(x,window.innerWidth-rect.width-8))+'px';
        menu.style.top=Math.max(8,Math.min(y,window.innerHeight-rect.height-8))+'px';
    }
    $('desktopContextToolbar')?.addEventListener('click',event=>{
        const btn=event.target.closest('[data-desktop-context-action]');if(!btn)return;
        const action=btn.dataset.desktopContextAction;
        if(action==='align-menu'||action==='layer-menu'){
            const rect=btn.getBoundingClientRect();showElementContextMenu(rect.left,rect.bottom+6);return;
        }
        if(action==='duplicate'){duplicateSelectedLayers();return;}
        if(action==='delete'){requestDeleteSelectedLayers();return;}
        if(action==='lock'){toggleSelectionLock();return;}
        runSelectionContextAction(action);
    });
    $('elementContextMenu')?.addEventListener('click',event=>{
        const action=event.target.closest('[data-element-menu-action]')?.dataset.elementMenuAction;
        const layer=event.target.closest('[data-element-layer]')?.dataset.elementLayer;
        const align=event.target.closest('[data-element-align]')?.dataset.elementAlign;
        if(!action&&!layer&&!align)return;
        hideElementContextMenu();
        if(action==='copy')copySelectedLayers();
        if(action==='paste')pasteSelectedLayers();
        if(action==='duplicate')duplicateSelectedLayers();
        if(action==='delete')requestDeleteSelectedLayers();
        if(action==='lock')toggleSelectionLock();
        if(layer)setLayerZ(selectedLayers(),layer);
        if(align)alignSelection(align);
    });

    function nextZ(){return Math.max(0,...page().layers.map(l=>l.zIndex||0))+1;}
    function addComponent(type){
        pushHistory();const p=page(),pw=Number(p.width||390);let z=nextZ(),created=[];
        const push=l=>{const n=normalizeLayer(Object.assign({zIndex:z++},l));p.layers.push(n);created.push(n);return n;};
        if(type==='couple'){
            push({type:'frame',frameKind:'circle',componentType:'couple',name:'Foto Mempelai Pria',binding:'groom_photo',customerEditPolicy:'content',x:38,y:150,width:135,height:135,cells:[{src:null,posX:0,posY:0,scale:1}]});
            push({type:'frame',frameKind:'circle',componentType:'couple',name:'Foto Mempelai Wanita',binding:'bride_photo',customerEditPolicy:'content',x:217,y:150,width:135,height:135,cells:[{src:null,posX:0,posY:0,scale:1}]});
            push({type:'text',componentType:'couple',name:'Nama Pasangan',binding:'couple_names',customerEditPolicy:'content',text:'Nama Pria & Nama Wanita',x:35,y:305,width:320,height:65,fontFamily:'Georgia',fontSize:30,color:'#2d2926',textAlign:'center',background:'transparent',animation:'none'});
        }else if(type==='quote'||type==='prayer'){
            const isPrayer=type==='prayer';
            push({type:'text',componentType:type,name:isPrayer?'Doa':'Quote',binding:isPrayer?'prayer':'quote',customerEditPolicy:'content',optional:true,text:isPrayer?'Tuliskan doa untuk kedua mempelai di sini.':'“Tuliskan quote pilihan di sini.”',x:38,y:220,width:314,height:180,fontFamily:'Georgia',fontSize:isPrayer?21:24,color:'#332d28',textAlign:'center',background:'transparent',animation:'none'});
        }else if(type==='event'){
            push({type:'text',componentType:'event',name:'Judul Acara',text:'Akad & Resepsi',x:45,y:150,width:300,height:50,fontFamily:'Georgia',fontSize:28,color:'#2d2926',textAlign:'center',background:'transparent',animation:'none'});
            push({type:'text',componentType:'event',name:'Tanggal Acara',binding:'event_date',customerEditPolicy:'content',text:'Tanggal Acara',x:45,y:220,width:300,height:42,fontFamily:'Arial',fontSize:18,color:'#403a35',textAlign:'center',background:'transparent',animation:'none',delay:.15});
            push({type:'text',componentType:'event',name:'Lokasi Acara',binding:'venue_name',customerEditPolicy:'content',text:'Nama Lokasi',x:45,y:275,width:300,height:48,fontFamily:'Arial',fontSize:16,color:'#403a35',textAlign:'center',background:'transparent',animation:'none',delay:.25});
        }else if(type==='gallery'){
            push({type:'grid',gridKind:'4',componentType:'gallery',name:'Galeri Mempelai',binding:'none',customerEditPolicy:'content',x:30,y:130,width:330,height:420,gap:6,borderRadius:8,cells:[
                {src:null,posX:0,posY:0,scale:1,binding:'gallery_1'},
                {src:null,posX:0,posY:0,scale:1,binding:'gallery_2'},
                {src:null,posX:0,posY:0,scale:1,binding:'gallery_3'},
                {src:null,posX:0,posY:0,scale:1,binding:'none'}
            ],galleryAnimation:'fade',galleryLightbox:true,galleryThumbnails:true,animation:'none'});
        }else if(type==='story'){
            push({type:'text',componentType:'story',name:'Love Story — Judul',text:'Our Story',x:45,y:145,width:300,height:50,fontFamily:'Georgia',fontSize:28,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'none'});
            push({type:'text',componentType:'story',name:'Love Story — Isi',text:'Ceritakan perjalanan kalian di sini.',x:45,y:215,width:300,height:210,fontFamily:'Arial',fontSize:16,color:'#403a35',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'none',delay:.15});
        }else if(type==='location'){
            push({type:'text',componentType:'location',name:'Lokasi',binding:'venue_name',customerEditPolicy:'content',text:'Nama Lokasi Acara',x:45,y:180,width:300,height:60,fontFamily:'Georgia',fontSize:25,color:'#2d2926',textAlign:'center',background:'transparent',animation:'none'});
            push({type:'shape',shapeKind:'rounded',componentType:'location',name:'Tombol Maps',x:110,y:270,width:170,height:48,background:'#2F6FED',borderRadius:24,customerEditPolicy:'locked',animation:'none',delay:.2});
            push({type:'text',componentType:'location',name:'Label Maps',text:'Buka Google Maps',x:120,y:278,width:150,height:30,fontFamily:'Arial',fontSize:14,color:'#ffffff',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'none',delay:.2});
        }else if(type==='countdown'){
            push({type:'shape',shapeKind:'rounded',componentType:'countdown',name:'Countdown Card',x:35,y:170,width:320,height:180,background:'#EEF5FF',borderRadius:18,customerEditPolicy:'locked',animation:'none'});
            push({type:'text',componentType:'countdown',name:'Countdown Title',text:'Menuju Hari Bahagia',x:60,y:195,width:270,height:40,fontFamily:'Georgia',fontSize:24,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'none'});
            push({type:'text',componentType:'countdown',name:'Countdown Runtime',text:'00 Hari · 00 Jam · 00 Menit · 00 Detik',x:55,y:260,width:280,height:52,fontFamily:'Arial',fontSize:14,color:'#5e6673',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'none',delay:.15});
        }else if(type==='wishes'){
            push({type:'shape',shapeKind:'rounded',componentType:'wishes',name:'Wishes Card',x:30,y:125,width:330,height:430,background:'#F7FAFF',borderRadius:18,customerEditPolicy:'locked',animation:'none'});
            push({type:'text',componentType:'wishes',name:'Wishes Title',text:'Ucapan & Doa',x:55,y:155,width:280,height:45,fontFamily:'Georgia',fontSize:27,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'none'});
            push({type:'text',componentType:'wishes',name:'Wishes Runtime',text:'Ucapan tamu akan tampil otomatis di sini.',x:60,y:230,width:270,height:90,fontFamily:'Arial',fontSize:14,color:'#667085',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'none',delay:.15});
        }else if(type==='music'){
            push({type:'shape',shapeKind:'circle',componentType:'music',name:'Music Control',x:155,y:220,width:80,height:80,background:'#2F6FED',borderRadius:40,customerEditPolicy:'locked',animation:'none'});
            push({type:'text',componentType:'music',name:'Music Icon',text:'♫',x:170,y:235,width:50,height:48,fontFamily:'Arial',fontSize:28,color:'#ffffff',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'none'});
        }else if(type==='guest_photo'){
            push({type:'shape',shapeKind:'rounded',componentType:'guest_photo',name:'Guest Photo Card',x:30,y:125,width:330,height:430,background:'#F7FAFF',borderRadius:18,customerEditPolicy:'locked',animation:'none'});
            push({type:'text',componentType:'guest_photo',name:'Guest Photo Title',text:'Bagikan Momenmu',x:55,y:155,width:280,height:45,fontFamily:'Georgia',fontSize:27,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'none'});
            push({type:'text',componentType:'guest_photo',name:'Guest Photo Runtime',text:'Form upload foto tamu akan tampil di undangan publik.',x:60,y:230,width:270,height:90,fontFamily:'Arial',fontSize:14,color:'#667085',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'none',delay:.15});
        }else if(type==='closing'){
            push({type:'text',componentType:'closing',name:'Penutup',binding:'closing_text',customerEditPolicy:'content',text:'Terima kasih atas doa dan kehadirannya.',x:40,y:240,width:310,height:150,fontFamily:'Georgia',fontSize:23,color:'#2d2926',textAlign:'center',background:'transparent',animation:'none'});
        }else if(['rsvp','gift'].includes(type)){
            const label=type==='rsvp'?'RSVP':'Wedding Gift';
            push({type:'shape',shapeKind:'rounded',componentType:type,name:label+' Card',x:35,y:165,width:320,height:235,background:'#EEF5FF',borderRadius:18,customerEditPolicy:'locked',animation:'none'});
            push({type:'text',componentType:type,name:label+' Title',text:label,x:60,y:195,width:270,height:45,fontFamily:'Georgia',fontSize:27,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'none'});
            push({type:'text',componentType:type,name:label+' Placeholder',text:type==='rsvp'?'Form RSVP akan terhubung di renderer pelanggan.':'Data rekening/hadiah akan terhubung di renderer pelanggan.',x:65,y:265,width:260,height:80,fontFamily:'Arial',fontSize:14,color:'#6a625b',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'none',delay:.15});
        }
        if(created.length){selectedId=created[created.length-1].id;activeCellIndex=0;markDirty();render();notify('Komponen ditambahkan');}
    }

    function addText(){pushHistory();const z=Math.max(0,...page().layers.map(l=>l.zIndex||0))+1;const l=normalizeLayer({id:uid('text'),type:'text',name:'Text',text:'Teks baru',x:45,y:180,width:300,height:70,zIndex:z,fontFamily:'Georgia',fontSize:34,color:'#2d2926',textAlign:'center',background:'transparent'});page().layers.push(l);setSingleSelection(l.id);markDirty();render();}
    function addTextPreset(kind){
        addText();
        const l=selected();if(!l||l.type!=='text')return;
        const presets={
            title:{name:'Judul',text:'Tambahkan judul',fontSize:42,height:78,fontFamily:'Georgia'},
            subtitle:{name:'Subjudul',text:'Tambahkan subjudul',fontSize:28,height:62,fontFamily:'Georgia'},
            paragraph:{name:'Paragraf',text:'Tambahkan paragraf',fontSize:18,height:110,fontFamily:'Arial'}
        };
        Object.assign(l,presets[kind]||{});markDirty();render();
    }
    function addShape(kind='rect'){
        pushHistory();const z=Math.max(0,...page().layers.map(l=>l.zIndex||0))+1;
        const sizes={circle:[160,160],oval:[210,120],line:[230,4],triangle:[180,160],star:[170,170],heart:[180,160],hex:[190,165],rounded:[200,120],rect:[200,120]};
        const [w,h]=sizes[kind]||sizes.rect;
        const l=normalizeLayer({id:uid('shape'),type:'shape',shapeKind:kind,name:'Bentuk '+kind,x:Math.round((Number(page().width||state.width)-w)/2),y:220,width:w,height:h,zIndex:z,background:'#BFD8FF',borderRadius:kind==='rounded'?18:0});
        page().layers.push(l);setSingleSelection(l.id);activeCellIndex=0;markDirty();render();
    }
    function addFrame(kind='square'){
        pushHistory();const z=Math.max(0,...page().layers.map(l=>l.zIndex||0))+1;
        const size={portrait:[190,250],landscape:[280,180],circle:[190,190],heart:[200,190],arch:[190,250],polaroid:[210,270],rounded:[200,240],square:[210,210]}[kind]||[210,210];
        const l=normalizeLayer({id:uid('frame'),type:'frame',frameKind:kind,name:'Bingkai '+kind,x:Math.round((Number(page().width||state.width)-size[0])/2),y:150,width:size[0],height:size[1],zIndex:z,background:'transparent',borderRadius:kind==='rounded'?16:0,cells:[{src:null,posX:0,posY:0,scale:1}]});
        page().layers.push(l);setSingleSelection(l.id);activeCellIndex=0;markDirty();render();
    }
    function addGrid(kind='4'){
        pushHistory();const z=Math.max(0,...page().layers.map(l=>l.zIndex||0))+1,count=cellCountForGrid(kind);
        const l=normalizeLayer({id:uid('grid'),type:'grid',gridKind:kind,name:'Kisi '+kind,x:35,y:150,width:320,height:360,zIndex:z,background:'transparent',gap:4,borderRadius:8,cells:Array.from({length:count},()=>({src:null,posX:0,posY:0,scale:1,binding:'none'}))});
        page().layers.push(l);setSingleSelection(l.id);activeCellIndex=0;markDirty();render();
    }
    function fillSelectedMedia(a,cellIndex=activeCellIndex){
        const l=selected();if(!l||(l.type!=='frame'&&l.type!=='grid')||a.type==='video')return false;
        const binding=String(l.binding||'none');
        if(a.type==='image'&&binding!=='none'&&['groom_photo','bride_photo','couple_photo'].includes(binding)){
            customerData[binding]={asset_id:a.id,url:a.url,name:a.name};
            if(isInstanceMode)customerInstanceDirtyKeys.add(binding);
            queueCustomerSave();activeCellIndex=0;setSingleSelection(l.id);scheduleCustomerVisualRefresh(true);render();
            notify('Foto masuk ke '+(l.type==='frame'?'bingkai':'kisi'));
            return true;
        }
        const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');ensureCells(l,count);
        const idx=clamp(Number(cellIndex||0),0,count-1);
        pushHistory();
        l.cells[idx]=Object.assign({},l.cells[idx],{src:String(a.url||''),assetId:a.id??null,posX:0,posY:0,scale:1});
        activeCellIndex=idx;setSingleSelection(l.id);markDirty();render();
        requestAnimationFrame(()=>{
            const img=canvas.querySelector(`.us-layer[data-id="${l.id}"] [data-cell-index="${idx}"] img`);
            if(img&&!img.complete)img.addEventListener('error',()=>notify('File foto tidak bisa dimuat'),{once:true});
        });
        notify('Foto masuk ke '+(l.type==='frame'?'bingkai':'kisi'));
        return true;
    }
    const videoPosterCache=new Map();
    const videoPosterJobs=new Map();

    function editorVideoSourceFromAsset(asset){
        return String(asset?.stream_url||asset?.url||'');
    }

    function editorVideoSourceForLayer(l){
        const a=(l?.assetId!==undefined&&l?.assetId!==null)
            ? assets.find(x=>String(x.id)===String(l.assetId))
            : assets.find(x=>String(x.url)===String(l?.src));
        return editorVideoSourceFromAsset(a)||String(l?.src||'');
    }

    function editorVideoPoster(src){
        const v=videoPosterCache.get(String(src||''));
        return typeof v==='string'&&v.startsWith('data:image/')?v:null;
    }

    function editorVideoMarkup(l){
        const src=editorVideoSourceForLayer(l);
        const poster=editorVideoPoster(src);

        if(poster){
            return `<div class="us-video-editor-shell ready" data-video-preview-src="${esc(src)}">
                <img class="us-video us-video-poster" src="${poster}" alt="">
                <span class="us-video-editor-badge">VIDEO</span>
            </div>`;
        }

        return `<div class="us-video-editor-shell" data-video-preview-src="${esc(src)}">
            <div class="us-video-editor-placeholder">
                <span class="us-video-playmark">▶</span>
                <b>VIDEO</b>
                <small>Memuat preview…</small>
            </div>
            <span class="us-video-editor-badge">VIDEO</span>
        </div>`;
    }

    async function buildVideoPoster(src){
        src=String(src||'');
        if(!src)return null;
        if(editorVideoPoster(src))return editorVideoPoster(src);
        if(videoPosterJobs.has(src))return videoPosterJobs.get(src);

        const job=new Promise(resolve=>{
            const video=document.createElement('video');
            let settled=false;
            const done=(value)=>{
                if(settled)return;
                settled=true;
                clearTimeout(timer);
                try{video.pause();video.removeAttribute('src');video.load();}catch(_){}
                video.remove();
                if(value)videoPosterCache.set(src,value);
                else videoPosterCache.set(src,'error');
                videoPosterJobs.delete(src);
                resolve(value||null);
            };

            const capture=()=>{
                try{
                    const nw=Math.max(1,Number(video.videoWidth||16));
                    const nh=Math.max(1,Number(video.videoHeight||9));
                    // HQ poster for EDITOR only. Keep it static to avoid video flicker,
                    // but preserve enough detail for zoomed/device editor views.
                    const max=1440;
                    const ratio=Math.min(1,max/Math.max(nw,nh));
                    const w=Math.max(2,Math.round(nw*ratio));
                    const h=Math.max(2,Math.round(nh*ratio));
                    const c=document.createElement('canvas');
                    c.width=w;c.height=h;
                    const ctx=c.getContext('2d',{alpha:false,desynchronized:true});
                    ctx.imageSmoothingEnabled=true;
                    ctx.imageSmoothingQuality='high';
                    ctx.drawImage(video,0,0,w,h);
                    done(c.toDataURL('image/jpeg',.92));
                }catch(_){done(null);}
            };

            video.muted=true;
            video.playsInline=true;
            video.preload='metadata';
            video.crossOrigin='anonymous';

            video.addEventListener('loadedmetadata',()=>{
                const seek=Math.min(.15,Math.max(0,Number(video.duration||0)*.02));
                if(seek>0){
                    try{video.currentTime=seek;}catch(_){capture();}
                }
            },{once:true});
            video.addEventListener('seeked',capture,{once:true});
            video.addEventListener('loadeddata',()=>{
                if(!Number.isFinite(video.duration)||video.duration===0)capture();
            },{once:true});
            video.addEventListener('error',()=>done(null),{once:true});

            const timer=setTimeout(()=>done(null),8000);
            video.src=src;
            video.load();
        });

        videoPosterJobs.set(src,job);
        return job;
    }

    function applyVideoPosterToNodes(src,poster){
        if(!poster)return;
        livePages?.querySelectorAll('[data-video-preview-src]').forEach(node=>{
            if(String(node.dataset.videoPreviewSrc)!==String(src))return;
            node.classList.add('ready');
            node.innerHTML=`<img class="us-video us-video-poster" src="${poster}" alt=""><span class="us-video-editor-badge">VIDEO</span>`;
        });

        document.querySelectorAll('.us-asset[data-video-preview-src]').forEach(node=>{
            if(String(node.dataset.videoPreviewSrc)!==String(src))return;
            const media=node.querySelector('.us-asset-media');
            if(media)media.innerHTML=`<img src="${poster}" alt="" draggable="false"><span class="us-media-kind">VIDEO</span>`;
            node.classList.add('media-ok');
        });
    }

    function hydrateEditorVideoPreviews(){
        const sources=new Set();
        livePages?.querySelectorAll('[data-video-preview-src]').forEach(node=>{
            const src=String(node.dataset.videoPreviewSrc||'');
            if(src&&!editorVideoPoster(src))sources.add(src);
        });
        document.querySelectorAll('.us-asset[data-video-preview-src]').forEach(node=>{
            const src=String(node.dataset.videoPreviewSrc||'');
            if(src&&!editorVideoPoster(src))sources.add(src);
        });

        sources.forEach(src=>{
            buildVideoPoster(src).then(poster=>{
                if(poster)applyVideoPosterToNodes(src,poster);
                else{
                    document.querySelectorAll(`[data-video-preview-src="${CSS.escape(src)}"] .us-video-editor-placeholder small`)
                        .forEach(x=>x.textContent='Preview siap saat video diputar');
                }
            });
        });
    }

    let replaceTargetId=null;
    function beginReplaceSelectedMedia(){
        const l=selected();if(!l||!['image','video','frame','grid'].includes(l.type))return;
        replaceTargetId=l.id;openPanel('uploads');notify('Pilih media pengganti');
    }
    function replaceSelectedMedia(a){
        if(!replaceTargetId)return false;
        const l=page().layers.find(x=>String(x.id)===String(replaceTargetId));replaceTargetId=null;
        if(!l)return false;
        if((l.type==='frame'||l.type==='grid')&&a.type==='video'){notify('Bingkai hanya mendukung foto');return true;}
        const binding=String(l.binding||'none');
        if(a.type==='image'&&binding!=='none'&&['groom_photo','bride_photo','couple_photo'].includes(binding)){
            customerData[binding]={asset_id:a.id,url:a.url,name:a.name};
            if(isInstanceMode)customerInstanceDirtyKeys.add(binding);
            queueCustomerSave();setSingleSelection(l.id);scheduleCustomerVisualRefresh(true);render();closeMobileSheets();notify('Media diganti');return true;
        }
        pushHistory();
        if(l.type==='frame'||l.type==='grid'){
            const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');ensureCells(l,count);
            const idx=l.type==='frame'?0:clamp(Number(activeCellIndex||0),0,count-1);
            l.cells[idx]=Object.assign({},l.cells[idx],{src:String(a.url||''),assetId:a.id??null,naturalWidth:null,naturalHeight:null});
        }else{
            l.type=a.type==='video'?'video':'image';l.src=String(a.url||'');l.assetId=a.id??null;l.name=a.name||l.name;
        }
        setSingleSelection(l.id);markDirty();render();closeMobileSheets();notify('Media diganti');return true;
    }
    async function addAsset(a){
        if(replaceSelectedMedia(a))return;
        if(fillSelectedMedia(a))return;

        if(a.type==='image'){
            await createFreeImageLayer(a,{x:55,y:120,maxSide:280,name:a.name,history:true});
            return;
        }

        pushHistory();
        const z=Math.max(0,...page().layers.map(l=>l.zIndex||0))+1;
        const l=normalizeLayer({
            id:uid(a.type),type:a.type,name:a.name,src:a.url,assetId:a.id??null,
            x:55,y:120,width:280,height:300,zIndex:z,background:'transparent'
        });
        page().layers.push(l);setSingleSelection(l.id);markDirty();render();
    }

    let premiumGallerySaveTimer=null;
    let premiumGallerySaveInFlight=false;
    let premiumGallerySavePending=false;
    let premiumGalleryDragIndex=null;

    function premiumGalleryItems(){
        // Canonical Gallery only. A new/empty instance must start at 0 / 30.
        // Do not repopulate the user Gallery from legacy gallery_1..gallery_N aliases.
        const raw=Array.isArray(customerData?.gallery)?customerData.gallery:[];
        return raw
            .map(item=>item&&typeof item==='object'?item:null)
            .filter(Boolean)
            .slice(0,STUDIO_GALLERY_CAPACITY);
    }

    function normalizePremiumGalleryItem(item){
        if(!item||typeof item!=='object')return null;
        const rawId=item.asset_id??item.id??item.assetId??null;
        const asset=rawId!==null?assets.find(a=>String(a.id)===String(rawId)):null;
        return {
            asset_id:asset?.id??rawId,
            url:item.url||asset?.url||'',
            name:item.name||asset?.name||'Foto'
        };
    }

    function premiumGalleryAssetIds(){
        return premiumGalleryItems()
            .map(normalizePremiumGalleryItem)
            .map(x=>Number(x?.asset_id))
            .filter(Number.isInteger)
            .filter(id=>id>0)
            .slice(0,STUDIO_GALLERY_CAPACITY);
    }

    function setPremiumGalleryState(text,state=''){
        const el=$('premiumGallerySaveState');if(!el)return;
        el.textContent=text;
        el.className='us-premium-gallery-state'+(state?' '+state:'');
    }

    function syncPremiumGalleryAliases(){
        const gallery=premiumGalleryItems()
            .map(normalizePremiumGalleryItem)
            .filter(x=>x&&x.url);

        customerData.gallery=gallery;

        for(let i=1;i<=STUDIO_GALLERY_CAPACITY;i++){
            customerData[`gallery_${i}`]=gallery[i-1]||null;
        }
    }

    function premiumGalleryHasAsset(assetId){
        return premiumGalleryAssetIds().includes(Number(assetId));
    }

    async function savePremiumGallery(){
        if(!isInstanceMode)return;
        if(premiumGallerySaveInFlight){premiumGallerySavePending=true;return;}

        const ids=premiumGalleryAssetIds();

        // Every selected Gallery asset must be a persistent numeric StudioAsset.
        if(ids.length!==premiumGalleryItems().length){
            setPremiumGalleryState('Ada foto yang belum tersimpan sebagai asset','error');
            notify('Ada foto lama yang belum memiliki asset ID. Upload ulang foto itu sebelum memasukkannya ke Galeri.');
            return;
        }

        premiumGallerySaveInFlight=true;
        setPremiumGalleryState('Menyimpan...','saving');

        try{
            const r=await fetch(updateUrl,{
                method:'PUT',
                headers:{
                    'X-CSRF-TOKEN':csrf,
                    'Content-Type':'application/json',
                    'Accept':'application/json'
                },
                body:JSON.stringify({
                    canvas:state,
                    gallery_asset_ids:ids
                })
            });

            const j=await r.json();
            if(!r.ok)throw new Error(j.message||Object.values(j.errors||{})[0]?.[0]||'Gagal menyimpan galeri');

            if(Array.isArray(j.gallery)){
                customerData.gallery=j.gallery;
                syncPremiumGalleryAliases();
            }

            setPremiumGalleryState('Tersimpan','saved');
            renderPremiumGalleryManager();

            interactionDiag('PREMIUM_GALLERY_SAVE',{
                count:ids.length,
                route:'existing-full-update'
            });
        }catch(e){
            setPremiumGalleryState('Gagal menyimpan','error');
            notify(e.message);
        }finally{
            premiumGallerySaveInFlight=false;
            if(premiumGallerySavePending){
                premiumGallerySavePending=false;
                setTimeout(savePremiumGallery,100);
            }
        }
    }

    function queuePremiumGallerySave(){
        clearTimeout(premiumGallerySaveTimer);
        setPremiumGalleryState('Belum disimpan','');
        premiumGallerySaveTimer=setTimeout(savePremiumGallery,850);
    }

    function addAssetToPremiumGallery(asset){
        if(!isInstanceMode||asset?.type!=='image')return;

        const numericId=Number(asset.id);
        if(!Number.isInteger(numericId)||numericId<=0){
            notify('Foto ini belum memiliki asset ID permanen. Upload ulang foto terlebih dahulu.');
            return;
        }

        if(premiumGalleryHasAsset(numericId)){
            notify('Foto sudah ada di Galeri Undangan');
            return;
        }

        const gallery=premiumGalleryItems();
        if(gallery.length>=STUDIO_GALLERY_CAPACITY){
            notify(`Galeri maksimal ${STUDIO_GALLERY_CAPACITY} foto`);
            return;
        }

        gallery.push({
            asset_id:numericId,
            url:asset.url,
            name:asset.name||'Foto'
        });

        customerData.gallery=gallery;
        syncPremiumGalleryAliases();
        renderPremiumGalleryManager();
        queuePremiumGallerySave();

        interactionDiag('PREMIUM_GALLERY_ADD',{
            assetId:numericId,
            count:gallery.length
        });
    }

    function removePremiumGalleryItem(index){
        const gallery=premiumGalleryItems();
        if(index<0||index>=gallery.length)return;

        gallery.splice(index,1);
        customerData.gallery=gallery;
        syncPremiumGalleryAliases();
        renderPremiumGalleryManager();
        queuePremiumGallerySave();

        interactionDiag('PREMIUM_GALLERY_REMOVE',{
            index,count:gallery.length
        });
    }

    function movePremiumGalleryItem(from,to){
        const gallery=premiumGalleryItems();
        if(from===to||from<0||to<0||from>=gallery.length||to>=gallery.length)return;

        const [item]=gallery.splice(from,1);
        gallery.splice(to,0,item);

        customerData.gallery=gallery;
        syncPremiumGalleryAliases();
        renderPremiumGalleryManager();
        queuePremiumGallerySave();

        interactionDiag('PREMIUM_GALLERY_REORDER',{
            from,to,count:gallery.length
        });
    }

    function renderPremiumGalleryManager(){
        if(!isInstanceMode)return;

        const list=$('premiumGalleryList');
        const picker=$('premiumGalleryAssetGrid');
        const countEl=$('premiumGalleryCount');
        if(!list||!picker)return;

        syncPremiumGalleryAliases();
        const gallery=premiumGalleryItems();

        if(countEl)countEl.textContent=`${gallery.length} / ${STUDIO_GALLERY_CAPACITY}`;

        list.innerHTML=gallery.length
            ? gallery.map((item,i)=>{
                const m=normalizePremiumGalleryItem(item);
                return `
                    <div class="us-premium-gallery-item" draggable="true" data-gallery-index="${i}">
                        <img src="${esc(m?.url||'')}" alt="${esc(m?.name||`Foto ${i+1}`)}">
                        <span class="us-premium-gallery-order">${i+1}</span>
                        <button class="us-premium-gallery-remove" type="button" data-gallery-remove="${i}" title="Hapus dari galeri">×</button>
                    </div>
                `;
            }).join('')
            : '<div class="us-small" style="grid-column:1/-1">Belum ada foto Galeri Undangan.</div>';

        list.querySelectorAll('[data-gallery-remove]').forEach(btn=>{
            btn.addEventListener('click',e=>{
                e.preventDefault();e.stopPropagation();
                removePremiumGalleryItem(Number(btn.dataset.galleryRemove));
            });
        });

        list.querySelectorAll('.us-premium-gallery-item').forEach(card=>{
            card.addEventListener('dragstart',e=>{
                premiumGalleryDragIndex=Number(card.dataset.galleryIndex);
                card.classList.add('dragging');
                e.dataTransfer.effectAllowed='move';
                e.dataTransfer.setData('text/plain',String(premiumGalleryDragIndex));
            });
            card.addEventListener('dragend',()=>{
                premiumGalleryDragIndex=null;
                list.querySelectorAll('.dragging,.drag-over').forEach(x=>x.classList.remove('dragging','drag-over'));
            });
            card.addEventListener('dragover',e=>{
                e.preventDefault();
                card.classList.add('drag-over');
                e.dataTransfer.dropEffect='move';
            });
            card.addEventListener('dragleave',()=>card.classList.remove('drag-over'));
            card.addEventListener('drop',e=>{
                e.preventDefault();
                card.classList.remove('drag-over');
                const to=Number(card.dataset.galleryIndex);
                const from=Number.isFinite(premiumGalleryDragIndex)
                    ? premiumGalleryDragIndex
                    : Number(e.dataTransfer.getData('text/plain'));
                if(Number.isFinite(from)&&Number.isFinite(to))movePremiumGalleryItem(from,to);
            });
        });

        const imgs=imageAssets();
        picker.innerHTML=imgs.length
            ? imgs.map(a=>{
                const valid=Number.isInteger(Number(a.id))&&Number(a.id)>0;
                return `
                    <button type="button"
                        class="us-premium-gallery-asset ${premiumGalleryHasAsset(a.id)?'added':''} ${valid?'':'unsupported'}"
                        data-gallery-asset="${esc(a.id)}"
                        ${valid?'':'disabled'}
                        title="${esc(valid?(a.name||'Foto'):'Asset lama belum memiliki ID permanen')}">
                        <img src="${esc(a.url)}" alt="${esc(a.name||'Foto')}">
                    </button>
                `;
            }).join('')
            : '<div class="us-small" style="grid-column:1/-1">Belum ada foto di Unggahan.</div>';

        picker.querySelectorAll('[data-gallery-asset]:not([disabled])').forEach(btn=>{
            btn.addEventListener('click',()=>{
                const asset=assets.find(a=>String(a.id)===String(btn.dataset.galleryAsset));
                if(asset)addAssetToPremiumGallery(asset);
            });
        });
    }

    function assetDeleteUrl(a){
        if(isInstanceMode){
            return `${String(uploadAssetUrl).replace(/\/$/,'')}/${encodeURIComponent(a.id)}`;
        }
        return a?.delete_url||null;
    }

    function assetUsage(a){
        const id=String(a?.id??'');
        const url=String(a?.url||'');
        const numericId=Number(a?.id);
        let canvasUses=0;
        let coverUses=0;

        (state.pages||[]).forEach(p=>{
            (p.layers||[]).forEach(l=>{
                if(
                    String(l.assetId??'')===id
                    || String(l.src||'')===url
                    || studioAssetIdFromUrl(l.src)===numericId
                )canvasUses++;

                if(l.type==='frame'||l.type==='grid'){
                    (l.cells||[]).forEach(c=>{
                        if(
                            String(c?.assetId??'')===id
                            || String(c?.src||'')===url
                            || studioAssetIdFromUrl(c?.src)===numericId
                        )canvasUses++;
                    });
                }
            });
        });

        [state.desktopCover,state.openingCover].forEach(cover=>{
            if(!cover)return;
            const coverId=referencedStudioAssetId({assetId:cover.assetId,url:cover.image});
            if(
                String(cover.assetId??'')===id
                || String(cover.image||'')===url
                || coverId===numericId
            )coverUses++;
        });

        return {
            canvas:canvasUses,
            cover:coverUses,
            gallery:isInstanceMode&&premiumGalleryHasAsset(Number(a?.id))
        };
    }

    async function deleteUploadedAsset(a){
        const url=assetDeleteUrl(a);
        if(!url){
            notify('Endpoint hapus asset belum tersedia untuk mode ini.');
            return;
        }

        const usage=assetUsage(a);
        if(usage.gallery){
            notify('Hapus foto ini dari Galeri Undangan terlebih dahulu.');
            return;
        }
        if(usage.cover>0){
            interactionDiag('ASSET_DELETE_BLOCKED_COVER',{assetId:a.id,coverUses:usage.cover});
            notify('Media ini masih digunakan sebagai Opening Cover / Sticky Cover. Ganti atau kosongkan cover terlebih dahulu.');
            return;
        }
        if(usage.canvas>0){
            notify('Media ini masih digunakan di canvas. Hapus elemennya dari canvas terlebih dahulu.');
            return;
        }

        if(!confirm(`Hapus "${a.name||'media ini'}" dari Unggahan?`))return;

        try{
            const r=await fetch(url,{
                method:'DELETE',
                headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}
            });
            const j=await r.json().catch(()=>({}));
            if(!r.ok)throw new Error(j.message||'Gagal menghapus media');

            const index=assets.findIndex(x=>String(x.id)===String(a.id));
            if(index>=0)assets.splice(index,1);

            videoPosterCache.delete(editorVideoSourceFromAsset(a));
            renderAssets();
            renderPremiumGalleryManager();
            notify('Media dihapus');

            interactionDiag('ASSET_DELETE',{
                assetId:a.id,
                assetType:a.type
            });
        }catch(e){
            notify(e.message||'Gagal menghapus media');
        }
    }

    function renderAssets(){
        const g=$('assetGrid');g.innerHTML='';

        assets.forEach(a=>{
            const e=document.createElement('div');
            e.className='us-asset';
            e.title=a.name;
            e.draggable=true;
            e.dataset.assetId=String(a.id??'');
            e.dataset.assetUrl=a.url;
            e.dataset.assetType=a.type;

            const del=document.createElement('button');
            del.type='button';
            del.className='us-asset-delete';
            del.title='Hapus dari Unggahan';
            del.setAttribute('aria-label','Hapus media');
            del.textContent='×';

            const media=document.createElement('div');
            media.className='us-asset-media';

            if(a.type==='video'){
                const src=editorVideoSourceFromAsset(a);
                e.dataset.videoPreviewSrc=src;
                const poster=editorVideoPoster(src);
                media.innerHTML=poster
                    ? `<img src="${poster}" alt="" draggable="false"><span class="us-media-kind">VIDEO</span>`
                    : `<div class="us-video-library-placeholder"><span>▶</span><small>VIDEO</small></div><span class="us-media-kind">VIDEO</span>`;
            }else{
                media.innerHTML=`<img src="${esc(a.url)}" alt="${esc(a.name||'Foto')}" loading="lazy"><span class="us-media-kind">FOTO</span>`;
                const img=media.querySelector('img');
                img.addEventListener('load',()=>e.classList.add('media-ok'));
                img.addEventListener('error',()=>{
                    e.classList.add('media-error');
                    media.innerHTML=`<div class="us-media-fallback">Preview foto gagal</div><span class="us-media-kind">FOTO</span>`;
                });
            }

            del.addEventListener('pointerdown',ev=>ev.stopPropagation());
            del.addEventListener('click',ev=>{
                ev.preventDefault();ev.stopPropagation();
                deleteUploadedAsset(a);
            });

            e.appendChild(media);
            e.appendChild(del);

            e.addEventListener('click',ev=>{
                if(ev.target.closest('.us-asset-delete'))return;
                addAsset(a);
            });
            e.addEventListener('dragstart',ev=>{
                if(ev.target.closest('.us-asset-delete')){ev.preventDefault();return;}
                ev.dataTransfer.setData('application/x-undanganta-asset',JSON.stringify(a));
                ev.dataTransfer.effectAllowed='copy';
            });

            g.appendChild(e);
        });

        renderCustomerMediaSelects();
        renderPremiumGalleryManager();
        requestAnimationFrame(hydrateEditorVideoPreviews);
    }
    function renderFonts(){
        const g=$('fontList');g.innerHTML='';
        const current=$('fontCurrentPreview'),sel=selected();
        if(current){
            if(sel?.type==='text'){current.textContent=`Aa — ${sel.fontFamily||'Arial'}`;current.style.fontFamily=sel.fontFamily||'Arial';}
            else{current.textContent='Pilih layer teks untuk mengganti font secara realtime.';current.style.fontFamily='inherit';}
        }
        fonts.forEach(f=>{
            const e=document.createElement('div');e.className='us-font-item';
            e.innerHTML=`<span class="font-demo" style="font-family:'${esc(f.family)}'">Aa ${esc(f.name)}</span><span class="font-meta">${f.weight||400}</span>`;
            const l=selected();if(l?.type==='text'&&l.fontFamily===f.family)e.classList.add('active');
            e.addEventListener('click',()=>{
                const t=selected();if(!t||t.type!=='text'){notify('Pilih layer teks dulu');return;}
                pushHistory();t.fontFamily=f.family;markDirty();render();notify('Font diterapkan');
            });
            g.appendChild(e);
        });
        rebuildFontSelect();
    }


    let pageClipboard=null;
    let pageMenuTargetId=null;

    function copyActivePage(){
        pageClipboard=deep(page());
        notify('Halaman disalin');
    }
    function pastePage(){
        if(!pageClipboard){notify('Belum ada halaman yang disalin');return;}
        pushHistory();
        const src=deep(pageClipboard),idx=state.pages.findIndex(p=>p.id===activePageId);
        src.id=uid('canvas');
        src.name=(src.name||'Canvas')+' Copy';
        src.layers=(src.layers||[]).map(l=>Object.assign({},l,{id:uid(l.type)}));
        state.pages.splice(idx+1,0,src);
        activePageId=src.id;selectedId=null;markDirty();render();
    }
    function duplicateActivePage(){
        pushHistory();
        const src=deep(page()),idx=state.pages.findIndex(p=>p.id===activePageId);
        src.id=uid('canvas');src.name=(src.name||'Canvas')+' Copy';
        src.layers=(src.layers||[]).map(l=>Object.assign({},l,{id:uid(l.type)}));
        state.pages.splice(idx+1,0,src);
        activePageId=src.id;selectedId=null;markDirty();render();
    }
    function deleteActivePage(){deletePageById(activePageId);}
    function addNewPage(){addPageAfter(activePageId);}
    function addPageAfter(pageId){
        pushHistory();
        const idx=state.pages.findIndex(p=>String(p.id)===String(pageId));
        const insertAt=idx>=0?idx+1:state.pages.length;
        const p=normalizePage({name:'',role:'section',width:390,height:844},insertAt);
        state.pages.splice(insertAt,0,p);
        activePageId=p.id;clearSelection();syncAutomaticPageRoles();markDirty();render();
        requestAnimationFrame(()=>shellForPageId(p.id)?.scrollIntoView({behavior:'smooth',block:'center'}));
    }
    function deletePageById(pageId){
        if(state.pages.length<=1){notify('Minimal harus ada 1 canvas');return;}
        const idx=state.pages.findIndex(p=>String(p.id)===String(pageId));if(idx<0)return;
        pushHistory();
        state.pages.splice(idx,1);
        const next=state.pages[Math.min(idx,state.pages.length-1)];
        activePageId=next.id;clearSelection();syncAutomaticPageRoles();markDirty();render();
    }
    function renameActivePage(){
        const current=page();
        const next=window.prompt('Nama halaman',current.name||'Canvas');
        if(next===null)return;
        const clean=next.trim();
        if(!clean)return;
        pushHistory();current.name=clean;markDirty();render();
    }
    function showPageMenu(x,y,pageId=activePageId){
        if(pageId && pageId!==activePageId){activePageId=pageId;clearSelection();render();}
        const menu=$('pageContextMenu');pageMenuTargetId=activePageId;
        menu.classList.add('open');
        const pad=10;
        const rect=menu.getBoundingClientRect();
        menu.style.left=Math.min(x,window.innerWidth-rect.width-pad)+'px';
        menu.style.top=Math.min(y,window.innerHeight-rect.height-pad)+'px';
    }
    function hidePageMenu(){$('pageContextMenu')?.classList.remove('open');pageMenuTargetId=null;}
    function renderPageGrid(){
        const grid=$('pageGrid');if(!grid)return;
        grid.innerHTML='';
        state.pages.forEach((p,i)=>{
            const card=document.createElement('button');
            card.type='button';
            card.className='us-page-grid-card'+(p.id===activePageId?' active':'');
            card.dataset.id=p.id;
            card.innerHTML=`<div class="us-page-grid-thumb" style="background:${esc(p.background||'#fff')}"></div><b>${i+1}. ${esc(p.name||'Canvas')}</b><small>${(p.layers||[]).length} elemen</small>`;
            grid.appendChild(card);
        });
    }
    function openPageGrid(){renderPageGrid();$('pageGridOverlay').classList.add('open');$('pageGridOverlay').setAttribute('aria-hidden','false');}
    function closePageGrid(){$('pageGridOverlay').classList.remove('open');$('pageGridOverlay').setAttribute('aria-hidden','true');}

    let activeCanvasObserver=null;
    function bindActiveCanvasObserver(){
        if(activeCanvasObserver){activeCanvasObserver.disconnect();activeCanvasObserver=null;}
        if(!('IntersectionObserver' in window)||!canvasZone||!livePages)return;
        activeCanvasObserver=new IntersectionObserver(entries=>{
            if(drag||resize||rotateDrag||cropDrag||gesture||groupDrag||groupResize||lasso||inlineTextEdit)return;
            const visible=entries.filter(e=>e.isIntersecting).sort((a,b)=>b.intersectionRatio-a.intersectionRatio);
            const best=visible[0]?.target;if(!best)return;
            const id=best.dataset.pageId;if(!id||String(id)===String(activePageId))return;
            activePageId=id;canvas=canvasForPageId(id);
            livePages.querySelectorAll('.us-live-page').forEach(s=>s.classList.toggle('active',String(s.dataset.pageId)===String(id)));
            renderLayers();renderProps();renderPages();
        },{root:canvasZone,threshold:[.2,.45,.7]});
        livePages.querySelectorAll('.us-live-page').forEach(s=>activeCanvasObserver.observe(s));
    }

    function setActivePage(id,animate=false){
        const target=state.pages.find(p=>String(p.id)===String(id));if(!target)return;
        cropMode=null;document.querySelector('.us-contextbar')?.classList.remove('crop-mode');
        activePageId=target.id;clearSelection();
        render();
        requestAnimationFrame(()=>{
            shellForPageId(id)?.scrollIntoView({behavior:'smooth',block:'center'});
        });
        if(animate)playTransition();
    }
    function playTransition(){startCanvasAnimationPreview(activePageId);}

    function changePageBy(delta){const i=state.pages.findIndex(p=>p.id===activePageId),n=i+delta;if(n<0||n>=state.pages.length)return;setActivePage(state.pages[n].id,preview);}

    function canvasScale(){
        const c=canvas||canvasForPageId(activePageId);if(!c)return {sx:1,sy:1,rect:{left:0,top:0,width:390,height:844}};
        const rect=c.getBoundingClientRect(),b=basePageSize(page());
        return {sx:b.width/Math.max(1,rect.width),sy:b.height/Math.max(1,rect.height),rect};
    }
    let interactionChanged=false;
    function touchInteraction(){
        interactionChanged=true;
        dirty=true;
        setSaveStatus('Belum disimpan','');
    }
    function liveLayerNode(layerId,pageId=activePageId){
        return canvasForPageId(pageId)?.querySelector(`.us-layer[data-id="${CSS.escape(String(layerId))}"]`)||null;
    }
    function updateLiveLayerDom(l,p=page()){
        const el=liveLayerNode(l.id,p.id);if(!el)return;
        const g=displayLayerGeometry(l,p);
        el.style.left=g.x+'px';el.style.top=g.y+'px';
        el.style.width=g.width+'px';el.style.height=g.height+'px';
        el.style.transform=`rotate(${Number(l.rotation||0)}deg)`;
        el.style.opacity=l.opacity;
        const content=el.querySelector('.us-layer-content');
        if(content&&(l.type==='frame'||l.type==='grid')){
            /*
             * UNDANGANTA_MEDIA_INTERACTION_NODE_STABILITY_V1_3
             * Keep the existing IMG/cell nodes alive during pointer interaction.
             * State changes are applied in-place by crop geometry; full render is
             * reserved for gesture commit / explicit editor render.
             */
            requestAnimationFrame(()=>syncLayerCellMediaGeometry(l));
        }
    }
    function updateSelectedLiveDom(){
        selectedLayers().forEach(l=>updateLiveLayerDom(l));
    }
    function ensureSelectionChromeVisible(){
        if(window.innerWidth<=780||selectedIds.size!==1)return;
        const zone=$('canvasZone'),node=liveLayerNode(selectedId);if(!zone||!node)return;
        const zr=zone.getBoundingClientRect(),nr=node.getBoundingClientRect();
        const topGap=38,bottomGap=26;
        let delta=0;
        if(nr.bottom+bottomGap>zr.bottom)delta=nr.bottom+bottomGap-zr.bottom;
        else if(nr.top-topGap<zr.top)delta=nr.top-topGap-zr.top;
        if(Math.abs(delta)>1)zone.scrollTop+=delta;
    }
    function syncLiveSelectionChrome(){
        livePages?.querySelectorAll('.us-layer').forEach(node=>{
            node.classList.remove('selected','multi-selected');
            node.querySelectorAll(':scope > .us-selection-handle,:scope > .us-rotate-stem,:scope > .us-rotate-handle,:scope > .us-crop-badge,:scope > .us-crop-hint,:scope > .us-crop-toolbar').forEach(x=>x.remove());
        });
        livePages?.querySelectorAll('.us-group-box').forEach(x=>x.remove());
        const p=page(),c=canvasForPageId(activePageId);if(!c)return;
        selectedIds.forEach(id=>{
            const node=liveLayerNode(id,p.id);
            if(node)node.classList.add(selectedIds.size>1?'multi-selected':'selected');
        });
        if(selectedIds.size===1){
            const l=selected(),node=l?liveLayerNode(l.id,p.id):null;
            if(l&&node)appendSelectionChrome(node,l);
        }else if(selectedIds.size>1){
            appendGroupSelectionChromeTo(c,p);
        }
        renderLayers();renderProps();renderPages();renderMobileContextToolbar();renderDesktopContextToolbar();
        requestAnimationFrame(ensureSelectionChromeVisible);
    }

    function layerCenterOnScreen(l){
        const {rect}=canvasScale(),b=basePageSize(page()),zx=rect.width/b.width,zy=rect.height/b.height;
        return {x:rect.left+(l.x+l.width/2)*zx,y:rect.top+(l.y+l.height/2)*zy};
    }
    function startResize(e,l,handle){
        const {sx,sy}=canvasScale();
        resize={id:l.id,handle,startX:e.clientX,startY:e.clientY,x:l.x,y:l.y,w:l.width,h:l.height,sx,sy,aspect:l.width/Math.max(1,l.height)};
    }
    function applyResize(e){
        if(!resize)return;
        const l=page().layers.find(x=>x.id===resize.id);if(!l)return;
        let dx=(e.clientX-resize.startX)*resize.sx,dy=(e.clientY-resize.startY)*resize.sy;
        let x=resize.x,y=resize.y,w=resize.w,h=resize.h; const min=20,handle=resize.handle;
        if(handle.includes('e'))w=resize.w+dx;
        if(handle.includes('s'))h=resize.h+dy;
        if(handle.includes('w')){w=resize.w-dx;x=resize.x+dx;}
        if(handle.includes('n')){h=resize.h-dy;y=resize.y+dy;}
        const keepRatio=(l.type==='image'||l.type==='video')&&(handle.length===2);
        if(keepRatio){
            if(Math.abs(dx)>=Math.abs(dy)){h=w/resize.aspect;if(handle.includes('n'))y=resize.y+(resize.h-h);}
            else {w=h*resize.aspect;if(handle.includes('w'))x=resize.x+(resize.w-w);}
        }
        if(w<min){if(handle.includes('w'))x-=min-w;w=min}
        if(h<min){if(handle.includes('n'))y-=min-h;h=min}
        l.x=Math.round(x);l.y=Math.round(y);l.width=Math.round(w);l.height=Math.round(h);touchInteraction();updateLiveLayerDom(l);
    }
    function startRotate(e,l){
        const c=layerCenterOnScreen(l),angle=Math.atan2(e.clientY-c.y,e.clientX-c.x)*180/Math.PI;
        rotateDrag={id:l.id,startAngle:angle,startRotation:Number(l.rotation||0)};
    }
    function applyRotate(e){
        if(!rotateDrag)return;const l=page().layers.find(x=>x.id===rotateDrag.id);if(!l)return;
        const c=layerCenterOnScreen(l),angle=Math.atan2(e.clientY-c.y,e.clientX-c.x)*180/Math.PI;
        l.rotation=Math.round(rotateDrag.startRotation+(angle-rotateDrag.startAngle));touchInteraction();updateLiveLayerDom(l);
    }
    function cropCellMetrics(layerId,cellIndex){
        const layerNode=canvas?.querySelector(`.us-layer[data-id="${CSS.escape(String(layerId))}"]`);
        const cellNode=layerNode?.querySelector(`[data-cell-index="${Number(cellIndex)}"]`);
        if(!cellNode)return {width:0,height:0};
        return {
            width:Number(cellNode.clientWidth||cellNode.offsetWidth||0),
            height:Number(cellNode.clientHeight||cellNode.offsetHeight||0)
        };
    }

    function cropMediaGeometry(l,cellIndex){
        if(!l)return null;
        const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');
        ensureCells(l,count);
        const idx=clamp(Number(cellIndex||0),0,count-1);
        const c=l.cells[idx];
        const mediaSrc=resolvedCropMediaSrc(l,idx);if(!mediaSrc)return null;

        const layerNode=liveLayerNode(l.id);
        const cellNode=l.type==='frame'?layerNode?.querySelector('.us-media-cell'):layerNode?.querySelector(`[data-cell-index="${idx}"]`);
        const img=cellNode?.querySelector('[data-cell-media-img],img');
        const fallback=cropCellMetrics(l.id,idx);
        const cellW=Math.max(1,Number(cellNode?.clientWidth||fallback.width||1));
        const cellH=Math.max(1,Number(cellNode?.clientHeight||fallback.height||1));
        const naturalW=Math.max(1,Number(img?.naturalWidth||c.naturalWidth||1));
        const naturalH=Math.max(1,Number(img?.naturalHeight||c.naturalHeight||1));

        if(img?.naturalWidth&&img?.naturalHeight){
            c.naturalWidth=img.naturalWidth;
            c.naturalHeight=img.naturalHeight;
        }

        // Give populated media a two-pixel bleed on every side. An exact
        // edge-to-edge cover can expose the cell fallback through subpixel
        // antialiasing when the parent layer is rotated.
        const mediaBleed=2;
        const coverScale=Math.max(
            (cellW+(mediaBleed*2))/naturalW,
            (cellH+(mediaBleed*2))/naturalH
        );
        const baseW=naturalW*coverScale;
        const baseH=naturalH*coverScale;
        const zoom=clamp(Number(c.scale||1),1,4);
        const fullW=baseW*zoom;
        const fullH=baseH*zoom;
        const maxX=Math.max(0,(fullW-cellW)/2);
        const maxY=Math.max(0,(fullH-cellH)/2);

        return {idx,c,mediaSrc,layerNode,cellNode,img,cellW,cellH,naturalW,naturalH,coverScale,baseW,baseH,zoom,fullW,fullH,maxX,maxY};
    }

    function applyCellCropGeometry(l,cellIndex,{preferNormalized=true,persist=true}={}){
        const g=cropMediaGeometry(l,cellIndex);
        if(!g)return null;

        const {c,img,maxX,maxY}=g;
        c.scale=g.zoom;

        let posX=Number(c.posX||0);
        let posY=Number(c.posY||0);
        const hasCropX=Number.isFinite(Number(c.cropX));
        const hasCropY=Number.isFinite(Number(c.cropY));

        if(preferNormalized&&hasCropX)posX=clamp(Number(c.cropX),-1,1)*maxX;
        else posX=clamp(posX,-maxX,maxX);

        if(preferNormalized&&hasCropY)posY=clamp(Number(c.cropY),-1,1)*maxY;
        else posY=clamp(posY,-maxY,maxY);

        c.posX=posX;c.posY=posY;

        if(persist){
            c.cropX=maxX>0?clamp(posX/maxX,-1,1):0;
            c.cropY=maxY>0?clamp(posY/maxY,-1,1):0;
        }

        if(img){
            img.style.width=g.fullW+'px';
            img.style.height=g.fullH+'px';
            img.style.minWidth='0';
            img.style.minHeight='0';
            img.style.maxWidth='none';
            img.style.maxHeight='none';
            img.style.opacity='1';
            img.style.transform=mediaTransformCss(l,`translate(calc(-50% + ${posX}px),calc(-50% + ${posY}px))`);
            img.style.filter=mediaFilterCss(l);
            img.removeAttribute('data-geometry-pending');
        }

        return g;
    }

    function bindCellMediaLoad(img,l,cellIndex){
        if(!img||img.dataset.cropGeometryBound==='1')return;
        img.dataset.cropGeometryBound='1';
        img.addEventListener('load',()=>{
            const live=page().layers.find(x=>String(x.id)===String(l.id));
            if(!live)return;
            applyCellCropGeometry(live,cellIndex,{preferNormalized:true,persist:true});
            if(cropMode&&String(cropMode.layerId)===String(l.id)&&Number(cropMode.cellIndex)===Number(cellIndex)){
                requestAnimationFrame(syncCropGhost);
            }
        });
    }

    function syncLayerCellMediaGeometry(l){
        if(!l||(l.type!=='grid'&&l.type!=='frame'))return;
        const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');
        ensureCells(l,count);
        const node=liveLayerNode(l.id);
        if(!node)return;

        for(let i=0;i<count;i++){
            const img=l.type==='frame'
                ? node.querySelector('.us-media-cell [data-cell-media-img], .us-media-cell img')
                : node.querySelector(`[data-cell-index="${i}"] [data-cell-media-img], [data-cell-index="${i}"] img`);
            if(!img)continue;
            bindCellMediaLoad(img,l,i);
            if(img.complete&&img.naturalWidth>0){
                applyCellCropGeometry(l,i,{preferNormalized:true,persist:true});
            }
        }
    }

    function syncAllCellMediaGeometry(){
        page().layers.filter(l=>l.type==='grid'||l.type==='frame').forEach(syncLayerCellMediaGeometry);
    }

    let cellMediaRehydrateRaf=0;
    function scheduleCellMediaGeometrySync(reason='render'){
        // Apply exact crop geometry immediately in the same JS task.
        // This prevents the object-fit fallback from being painted first.
        syncAllCellMediaGeometry();
        if(cropMode)syncCropGhost();

        if(cellMediaRehydrateRaf)cancelAnimationFrame(cellMediaRehydrateRaf);
        cellMediaRehydrateRaf=requestAnimationFrame(()=>{
            cellMediaRehydrateRaf=0;

            // One post-layout verification only. No second delayed visual phase.
            syncAllCellMediaGeometry();
            if(cropMode)syncCropGhost();

            interactionDiag('CELL_MEDIA_REHYDRATE',{
                pageId:activePageId,
                device:editorDeviceMode,
                reason,
                mediaCount:canvasForPageId(activePageId)?.querySelectorAll('[data-cell-media-img]').length||0
            });
        });
    }

    function clampCropCell(l,cellIndex,{preferNormalized=false}={}){
        const g=applyCellCropGeometry(l,cellIndex,{preferNormalized,persist:true});
        if(!g)return null;
        interactionDiag('GRID_CROP_BOUNDS',{
            pageId:activePageId,layerId:l.id,cellIndex:g.idx,
            cell:{w:Math.round(g.cellW),h:Math.round(g.cellH)},
            natural:{w:Math.round(g.naturalW),h:Math.round(g.naturalH)},
            cover:{w:Math.round(g.baseW),h:Math.round(g.baseH)},
            zoom:Number(g.zoom.toFixed(3)),
            maxPan:{x:Math.round(g.maxX),y:Math.round(g.maxY)},
            pos:{x:Math.round(g.c.posX),y:Math.round(g.c.posY)},
            normalized:{x:Number(Number(g.c.cropX||0).toFixed(4)),y:Number(Number(g.c.cropY||0).toFixed(4))}
        });
        return g.c;
    }

    function removeCropGhost(){
        livePages?.querySelectorAll('.us-crop-source-ghost,.us-crop-preview-wrap').forEach(x=>x.remove());
    }
    let stableCropGhostRaf=0;
    function scheduleStableCropGhost(){
        if(stableCropGhostRaf)return;
        stableCropGhostRaf=requestAnimationFrame(()=>{
            stableCropGhostRaf=0;
            syncCropGhost();
        });
    }

    async function syncCropGhost(){
        if(!cropMode){
            removeCropGhost();
            return;
        }

        const l=page().layers.find(x=>String(x.id)===String(cropMode.layerId));
        if(!l||(l.type!=='grid'&&l.type!=='frame')){
            removeCropGhost();
            return;
        }

        const g=applyCellCropGeometry(l,cropMode.cellIndex,{preferNormalized:true,persist:true});
        if(!g||!g.mediaSrc||!g.layerNode||!g.cellNode){
            removeCropGhost();
            return;
        }

        const layerRect=g.layerNode.getBoundingClientRect();
        const cellRect=g.cellNode.getBoundingClientRect();
        const cellX=cellRect.left-layerRect.left;
        const cellY=cellRect.top-layerRect.top;

        const pad=Math.max(24,Math.min(96,Math.max(g.cellW,g.cellH)*0.45));
        const previewLeft=Math.max(-pad,cellX-pad);
        const previewTop=Math.max(-pad,cellY-pad);
        const previewRight=Math.min(layerRect.width+pad,cellX+g.cellW+pad);
        const previewBottom=Math.min(layerRect.height+pad,cellY+g.cellH+pad);
        const previewW=Math.max(g.cellW,previewRight-previewLeft);
        const previewH=Math.max(g.cellH,previewBottom-previewTop);

        let wrap=g.layerNode.querySelector(':scope > .us-crop-preview-wrap[data-stable-crop-ghost="1"]');
        let ghost=wrap?.querySelector('.us-crop-source-ghost')||null;
        let windowMask=wrap?.querySelector('.us-crop-window-mask')||null;

        if(!wrap){
            // Remove only stale legacy crop ghost once, never per pointermove.
            g.layerNode.querySelectorAll(':scope > .us-crop-preview-wrap').forEach(x=>x.remove());

            wrap=document.createElement('div');
            wrap.className='us-crop-preview-wrap';
            wrap.dataset.stableCropGhost='1';

            ghost=document.createElement('img');
            ghost.className='us-crop-source-ghost';
            ghost.alt='';
            ghost.draggable=false;
            wrap.appendChild(ghost);

            windowMask=document.createElement('div');
            windowMask.className='us-crop-window-mask';
            wrap.appendChild(windowMask);

            g.layerNode.insertBefore(wrap,g.layerNode.firstChild);
        }

        wrap.style.left=previewLeft+'px';
        wrap.style.top=previewTop+'px';
        wrap.style.width=previewW+'px';
        wrap.style.height=previewH+'px';

        if(ghost){
            if((ghost.getAttribute('src')||'')!==g.mediaSrc){
                ghost.setAttribute('src',g.mediaSrc);
            }
            ghost.style.width=g.fullW+'px';
            ghost.style.height=g.fullH+'px';
            ghost.style.transform=`scaleX(${l.flipX?-1:1}) scaleY(${l.flipY?-1:1})`;
            ghost.style.transformOrigin='center center';

            const centerX=(cellX-previewLeft)+(g.cellW/2)+Number(g.c.posX||0);
            const centerY=(cellY-previewTop)+(g.cellH/2)+Number(g.c.posY||0);
            ghost.style.left=(centerX-g.fullW/2)+'px';
            ghost.style.top=(centerY-g.fullH/2)+'px';
        }

        if(windowMask){
            windowMask.style.left=(cellX-previewLeft)+'px';
            windowMask.style.top=(cellY-previewTop)+'px';
            windowMask.style.width=g.cellW+'px';
            windowMask.style.height=g.cellH+'px';
        }
    }

    function finishCropInteraction({renderNow=true,reason='manual',deselect=false}={}){
        if(!cropMode)return;

        const finishing={layerId:cropMode.layerId,cellIndex:cropMode.cellIndex};
        const l=page().layers.find(x=>String(x.id)===String(finishing.layerId));
        if(l)clampCropCell(l,finishing.cellIndex);

        interactionDiag('GRID_CROP_FINISH',{
            pageId:activePageId,
            layerId:finishing.layerId,
            cellIndex:finishing.cellIndex,
            reason,
            deselect:!!deselect
        });

        cropMode=null;
        cropDrag=null;
        gesture=null;
        document.querySelector('.us-contextbar')?.classList.remove('crop-mode');
        removeCropGhost();

        if(deselect){
            clearSelection();
            activeCellIndex=0;
        }else if(l){
            setSingleSelection(l.id);
            activeCellIndex=finishing.cellIndex;
        }

        if(renderNow)render();
    }

    /* UNDANGANTA_MEMPELAI_BOUND_FRAME_REPAIR_V1 */
    function boundFrameRuntimeMediaSrc(l){
        if(!l||l.type!=='frame'||!l.binding||l.binding==='none')return '';
        if(editorViewMode==='sample')return '';
        return effectiveMediaSrc(l)||'';
    }

    /* UNDANGANTA_MEMPELAI_CROP_LIFECYCLE_REPAIR_V1_2 — superseded safely by V1.3 */
    /* UNDANGANTA_MEMPELAI_CROP_NO_FLICKER_V1_3 */
    function isMempelaiBoundFrame(l){
        return !!(
            l &&
            l.type==='frame' &&
            (l.binding==='groom_photo'||l.binding==='bride_photo')
        );
    }

    function resolvedCropMediaSrc(l,idx=0){
        if(!l)return '';

        // Binding is authoritative for bound frames.
        const bound=boundFrameRuntimeMediaSrc(l);
        if(bound)return String(bound);

        return String(l?.cells?.[idx]?.src||'');
    }

    function cropCellHasRenderableMedia(l,idx=0){
        return !!resolvedCropMediaSrc(l,idx);
    }

    function startCropDrag(e,l,cellIndex){
        const cell=l.cells?.[cellIndex];if(!cell||!cropCellHasRenderableMedia(l,cellIndex))return;
        const {sx,sy}=canvasScale();cropDrag={id:l.id,cellIndex,startX:e.clientX,startY:e.clientY,posX:Number(cell.posX||0),posY:Number(cell.posY||0),sx,sy};
        canvas.querySelector(`.us-layer[data-id="${l.id}"]`)?.classList.add('crop-moving');
    }
    function applyCropDrag(e){
        if(!cropDrag)return;
        const l=page().layers.find(x=>x.id===cropDrag.id);if(!l)return;
        ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));

        const cell=l.cells[cropDrag.cellIndex];
        cell.posX=Math.round(cropDrag.posX+(e.clientX-cropDrag.startX)*cropDrag.sx);
        cell.posY=Math.round(cropDrag.posY+(e.clientY-cropDrag.startY)*cropDrag.sy);

        clampCropCell(l,cropDrag.cellIndex,{preferNormalized:false});
        touchInteraction();

        // Exact IMG geometry was already applied by clampCropCell().
        // Do not rebuild media DOM while pointer is moving.
        scheduleStableCropGhost();
    }
    function imageNaturalSize(url){
        const key=String(url||'');
        if(!key)return Promise.resolve({width:1,height:1,ratio:1});
        if(naturalMediaSizeCache.has(key))return naturalMediaSizeCache.get(key);

        const promise=new Promise(resolve=>{
            const img=new Image();
            img.onload=()=>{
                const width=Math.max(1,Number(img.naturalWidth||1));
                const height=Math.max(1,Number(img.naturalHeight||1));
                resolve({width,height,ratio:width/height});
            };
            img.onerror=()=>resolve({width:1,height:1,ratio:1});
            img.src=key;
        });

        naturalMediaSizeCache.set(key,promise);
        return promise;
    }

    async function naturalCanvasImageGeometry(source,{maxSide=280,x=55,y=120}={}){
        const size=await imageNaturalSize(source?.url||source?.src||'');
        const longest=Math.max(size.width,size.height,1);
        const scale=maxSide/longest;
        return {
            x:Number(x),y:Number(y),
            width:Math.max(24,Math.round(size.width*scale)),
            height:Math.max(24,Math.round(size.height*scale)),
            ratio:size.ratio
        };
    }

    async function createFreeImageLayer(source,{x=55,y=120,maxSide=280,name=null,history=true}={}){
        const url=String(source?.url||source?.src||'');
        if(!url)return null;

        const g=await naturalCanvasImageGeometry({url},{maxSide,x,y});
        if(history)pushHistory();

        const z=Math.max(0,...page().layers.map(l=>l.zIndex||0))+1;
        const l=normalizeLayer({
            id:uid('image'),type:'image',
            name:name||source?.name||'Foto',
            src:url,assetId:source?.id??source?.assetId??null,
            x:g.x,y:g.y,width:g.width,height:g.height,
            zIndex:z,background:'transparent'
        });

        page().layers.push(l);
        setSingleSelection(l.id);
        markDirty();render();

        interactionDiag('IMAGE_FREE_LAYER_CREATE',{
            pageId:activePageId,layerId:l.id,
            width:l.width,height:l.height,
            ratio:Number((l.width/l.height).toFixed(6))
        });
        return l;
    }

    function mediaCellSource(layerId,cellIndex){
        const l=page().layers.find(x=>String(x.id)===String(layerId));
        if(!l||(l.type!=='grid'&&l.type!=='frame'))return null;

        const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');
        ensureCells(l,count);

        const idx=clamp(Number(cellIndex||0),0,count-1);
        const c=l.cells[idx];
        if(!c?.src)return null;

        const asset=assets.find(a=>String(a.id)===String(c.assetId));
        return {
            layer:l,cellIndex:idx,cell:c,
            url:c.src,src:c.src,
            id:c.assetId??asset?.id??null,
            assetId:c.assetId??asset?.id??null,
            name:asset?.name||'Foto'
        };
    }

    function copyMediaCell(layerId,cellIndex){
        const src=mediaCellSource(layerId,cellIndex);if(!src)return false;
        cellMediaClipboard=deep({
            url:src.url,id:src.id,assetId:src.assetId,name:src.name,
            posX:src.cell.posX||0,posY:src.cell.posY||0,scale:src.cell.scale||1
        });
        interactionDiag('GRID_CELL_COPY',{pageId:activePageId,layerId,cellIndex});
        notify('Gambar disalin');
        return true;
    }

    function pasteMediaCell(layerId,cellIndex){
        if(!cellMediaClipboard?.url){notify('Belum ada gambar yang disalin');return false;}
        const ok=fillMediaCellDirect(layerId,cellIndex,cellMediaClipboard,{history:true,reason:'cell-context-paste'});
        if(ok)interactionDiag('GRID_CELL_PASTE',{pageId:activePageId,layerId,cellIndex});
        return ok;
    }

    async function duplicateMediaCellAsFree(layerId,cellIndex){
        const src=mediaCellSource(layerId,cellIndex);if(!src)return false;
        const l=src.layer;
        const out=await createFreeImageLayer(src,{
            x:Number(l.x||0)+Number(l.width||0)+18,
            y:Number(l.y||0),
            maxSide:Math.min(280,Math.max(120,Number(l.width||280)*.7)),
            name:(src.name||'Foto')+' Copy',
            history:true
        });
        if(out)interactionDiag('GRID_CELL_DUPLICATE_FREE',{pageId:activePageId,sourceLayerId:layerId,cellIndex,newLayerId:out.id});
        return !!out;
    }

    async function detachMediaCell(layerId,cellIndex){
        const src=mediaCellSource(layerId,cellIndex);if(!src)return false;
        const l=src.layer;

        pushHistory();
        const out=await createFreeImageLayer(src,{
            x:Number(l.x||0)+18,
            y:Number(l.y||0)+18,
            maxSide:Math.min(280,Math.max(120,Number(l.width||280)*.7)),
            name:src.name,
            history:false
        });
        if(!out)return false;

        const still=page().layers.find(x=>String(x.id)===String(layerId));
        if(still){
            const count=still.type==='frame'?1:cellCountForGrid(still.gridKind||'4');
            ensureCells(still,count);
            still.cells[cellIndex]=Object.assign({},still.cells[cellIndex],{
                src:null,assetId:null,posX:0,posY:0,scale:1
            });
        }

        setSingleSelection(out.id);
        activeCellIndex=0;
        markDirty();render();
        interactionDiag('GRID_CELL_DETACH',{pageId:activePageId,sourceLayerId:layerId,cellIndex,newLayerId:out.id});
        notify('Gambar dikeluarkan dari kisi');
        return true;
    }

    function selectMediaCell(layerId,cellIndex=0){
        const l=page().layers.find(x=>String(x.id)===String(layerId));
        if(!l||(l.type!=='frame'&&l.type!=='grid'))return false;
        const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');
        ensureCells(l,count);
        const idx=clamp(Number(cellIndex||0),0,count-1);
        setSingleSelection(l.id);
        activeCellIndex=idx;
        touchCellDelete=null;
        syncLiveSelectionChrome();
        const layerNode=canvas?.querySelector(`.us-layer[data-id="${CSS.escape(String(l.id))}"]`);
        layerNode?.querySelectorAll('[data-cell-index]').forEach(cell=>{
            cell.classList.toggle('selected-cell',Number(cell.dataset.cellIndex||0)===idx);
        });
        renderLayers();renderProps();
        interactionDiag('GRID_CELL_SELECT',{pageId:activePageId,layerId:l.id,cellIndex:idx,hasPhoto:cropCellHasRenderableMedia(l,idx),mediaSource:l.cells[idx]?.src?'cell':'resolved-binding'});
        return true;
    }

    function clearMediaCell(layerId,cellIndex=0,{history=true,reason='manual'}={}){
        const l=page().layers.find(x=>String(x.id)===String(layerId));
        if(!l||(l.type!=='frame'&&l.type!=='grid'))return false;
        const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');
        ensureCells(l,count);
        const idx=clamp(Number(cellIndex||0),0,count-1);
        const c=l.cells[idx];
        if(!c?.src)return false;
        if(history)pushHistory();
        l.cells[idx]=Object.assign({},c,{src:null,assetId:null,posX:0,posY:0,cropX:0,cropY:0,scale:1,naturalWidth:null,naturalHeight:null});
        activeCellIndex=idx;
        selectedId=l.id;
        selectedIds=new Set([l.id]);
        cropMode=null;cropDrag=null;gesture=null;
        touchCellDelete=null;
        markDirty();render();
        interactionDiag('GRID_CELL_MEDIA_DELETE',{pageId:activePageId,layerId:l.id,cellIndex:idx,reason});
        return true;
    }

    function fillMediaCellDirect(layerId,cellIndex,source,{removeSourceLayerId=null,history=true,reason='direct'}={}){
        const l=page().layers.find(x=>String(x.id)===String(layerId));
        if(!l||(l.type!=='frame'&&l.type!=='grid')||!source?.url)return false;
        const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');
        ensureCells(l,count);
        const idx=clamp(Number(cellIndex||0),0,count-1);
        if(history)pushHistory();
        l.cells[idx]=Object.assign({},l.cells[idx],{
            src:String(source.url),
            assetId:source.id??source.assetId??null,
            posX:0,posY:0,cropX:0,cropY:0,scale:1,
            naturalWidth:null,naturalHeight:null
        });
        if(removeSourceLayerId){
            page().layers=page().layers.filter(x=>String(x.id)!==String(removeSourceLayerId));
        }
        selectedId=l.id;selectedIds=new Set([l.id]);activeCellIndex=idx;
        touchCellDelete=null;
        markDirty();render();
        requestAnimationFrame(()=>{
            const img=canvas?.querySelector(`.us-layer[data-id="${CSS.escape(String(l.id))}"] [data-cell-index="${idx}"] img`);
            if(img&&!img.complete)img.addEventListener('error',()=>notify('File foto tidak bisa dimuat'),{once:true});
        });
        interactionDiag('GRID_CELL_MEDIA_FILL',{pageId:activePageId,layerId:l.id,cellIndex:idx,reason,removedSourceLayerId:removeSourceLayerId||null});
        return true;
    }

    function gridCellUnderPoint(clientX,clientY,excludeLayerId=null){
        const stack=document.elementsFromPoint(clientX,clientY);
        for(const node of stack){
            const cell=node.closest?.('[data-cell-index]');
            const layerNode=cell?.closest?.('.us-layer');
            if(!cell||!layerNode)continue;
            if(excludeLayerId&&String(layerNode.dataset.id)===String(excludeLayerId))continue;
            const l=page().layers.find(x=>String(x.id)===String(layerNode.dataset.id));
            if(!l||(l.type!=='grid'&&l.type!=='frame'))continue;
            return {layer:l,cellIndex:Number(cell.dataset.cellIndex||0),cell};
        }
        return null;
    }

    function tryDropImageLayerIntoCell(sourceLayer,clientX,clientY){
        if(!sourceLayer||sourceLayer.type!=='image'||!sourceLayer.src)return false;
        const hit=gridCellUnderPoint(clientX,clientY,sourceLayer.id);
        if(!hit)return false;
        const ok=fillMediaCellDirect(hit.layer.id,hit.cellIndex,{
            url:sourceLayer.src,id:sourceLayer.assetId??null,assetId:sourceLayer.assetId??null
        },{removeSourceLayerId:sourceLayer.id,history:false,reason:'canvas-image-drop'});
        if(ok){
            interactionDiag('CANVAS_IMAGE_DROPPED_INTO_GRID',{
                pageId:activePageId,
                sourceLayerId:sourceLayer.id,
                targetLayerId:hit.layer.id,
                cellIndex:hit.cellIndex
            });
            notify('Foto masuk ke kotak kisi');
        }
        return ok;
    }

    function cancelCellLongPress(){
        if(cellLongPress?.timer)clearTimeout(cellLongPress.timer);
        cellLongPress=null;
    }

    function beginCellLongPress(e,l,cellIndex){
        cancelCellLongPress();
        cellLongPress={
            pointerId:e.pointerId,
            layerId:l.id,
            cellIndex,
            startX:e.clientX,startY:e.clientY,
            fired:false,
            timer:setTimeout(()=>{
                const current=page().layers.find(x=>String(x.id)===String(l.id));
                ensureCells(current,current.type==='frame'?1:cellCountForGrid(current.gridKind||'4'));
                if(!current?.cells?.[cellIndex]?.src){cancelCellLongPress();return;}
                cellLongPress.fired=true;
                touchCellDelete={layerId:l.id,cellIndex};
                lastTap={time:0,id:null,cell:null};
                interactionDiag('GRID_CELL_LONGPRESS_MENU',{pageId:activePageId,layerId:l.id,cellIndex});
                render();
            },560)
        };
    }

    function enterCropMode(layerId,cellIndex=0){
        const l=page().layers.find(x=>x.id===layerId);if(!l||(l.type!=='frame'&&l.type!=='grid'))return;
        ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));
        if(!cropCellHasRenderableMedia(l,cellIndex)){notify('Masukkan foto ke bingkai dulu');return;}

        setSingleSelection(layerId);
        activeCellIndex=cellIndex;
        cropMode={layerId,cellIndex};
        touchCellDelete=null;

        l.cells[cellIndex].scale=Math.max(1,Number(l.cells[cellIndex].scale||1));
        clampCropCell(l,cellIndex,{preferNormalized:true});

        interactionDiag('GRID_CROP_ENTER',{pageId:activePageId,layerId,cellIndex,pointerMode:'direct'});
        document.querySelector('.us-contextbar')?.classList.add('crop-mode');
        render();
        requestAnimationFrame(()=>{
            syncCropGhost();
            const node=liveLayerNode(layerId);
            const content=node?.querySelector(':scope > .us-layer-content');
            const cell=node?.querySelector(`[data-cell-index="${Number(cellIndex)}"]`);
            const lr=node?.getBoundingClientRect();
            const cr=content?.getBoundingClientRect();
            const xr=cell?.getBoundingClientRect();
            interactionDiag('GRID_CROP_GEOMETRY',{
                pageId:activePageId,
                layerId,
                cellIndex,
                layerRect:lr?{w:Math.round(lr.width),h:Math.round(lr.height)}:null,
                contentRect:cr?{w:Math.round(cr.width),h:Math.round(cr.height)}:null,
                cellRect:xr?{w:Math.round(xr.width),h:Math.round(xr.height)}:null
            });
        });
    }
    function exitCropMode(){
        finishCropInteraction({renderNow:true,reason:'manual-exit'});
    }
    function resetCrop(){
        if(!cropMode)return;const l=page().layers.find(x=>x.id===cropMode.layerId);if(!l)return;pushHistory();ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));l.cells[cropMode.cellIndex]=Object.assign({},l.cells[cropMode.cellIndex],{posX:0,posY:0,scale:1});markDirty();render();
    }


    let guideDrag=null;

    function activateCanvasFromEvent(e,{clearOnPageChange=true}={}){
        const targetCanvas=e.target?.closest?.('.us-canvas[data-page-id]');
        if(!targetCanvas)return false;
        const pageId=targetCanvas.dataset.pageId;
        const changed=String(activePageId)!==String(pageId);
        if(changed){
            activePageId=pageId;
            if(clearOnPageChange)clearSelection();
            cropMode=null;
        }
        canvas=targetCanvas;
        livePages?.querySelectorAll('.us-live-page').forEach(s=>s.classList.toggle('active',String(s.dataset.pageId)===String(activePageId)));
        renderLayers();renderProps();renderPages();
        return true;
    }

        livePages.addEventListener('input',e=>{
        const input=e.target.closest('.us-inline-page-name');if(!input)return;
        const p=state.pages.find(x=>String(x.id)===String(input.dataset.pageNameId));if(!p)return;
        p.name=input.value;
        dirty=true;setSaveStatus('Belum disimpan','');
        interactionDiag('CANVAS_NAME_EDIT',{canvasId:p.id,nameLength:p.name.length});
    });
    livePages.addEventListener('change',e=>{
        const input=e.target.closest('.us-inline-page-name');if(!input)return;
        const p=state.pages.find(x=>String(x.id)===String(input.dataset.pageNameId));if(!p)return;
        p.name=input.value.trim();markDirty();renderPages();
    });
    livePages.addEventListener('click',e=>{
        const action=e.target.closest('[data-live-page-action]');if(!action)return;
        e.preventDefault();e.stopPropagation();
        const pageId=action.dataset.pageId;
        if(action.dataset.livePageAction==='add'){interactionDiag('CANVAS_ADD_CLICK',{canvasId:pageId});addPageAfter(pageId);}
        if(action.dataset.livePageAction==='delete'){interactionDiag('CANVAS_DELETE_CLICK',{canvasId:pageId});deletePageById(pageId);}
    });

livePages.addEventListener('pointerdown',e=>{if(e.target.closest('.us-inline-page-name,[data-live-page-action]'))return;if(!activateCanvasFromEvent(e))return;
        const diagLayerEl=e.target.closest('.us-layer');
        const diagLayer=diagLayerEl?page().layers.find(x=>x.id===diagLayerEl.dataset.id):null;
        if(diagLayerEl)animationInteractionDiag('POINTERDOWN_BEFORE',diagLayerEl,diagLayer,{
            pointerId:e.pointerId,clientX:e.clientX,clientY:e.clientY,pointerType:e.pointerType
        });
        activePointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
        const guideHandle=e.target.closest('.us-guide-handle');
        if(guideHandle){pushHistory();guideDrag={index:Number(guideHandle.dataset.guideIndex||0)};e.preventDefault();e.stopPropagation();return;}
        const additiveSelection=e.shiftKey||e.ctrlKey||e.metaKey;
        const gh=e.target.closest('[data-group-handle]'),gb=e.target.closest('[data-group-selection]');
        if(gh){startGroupResize(e,gh.dataset.groupHandle);e.preventDefault();e.stopPropagation();return;}
        if(gb&&selectedIds.size>1){
            if(additiveSelection){
                const underlying=[...document.elementsFromPoint(e.clientX,e.clientY)]
                    .map(node=>node.closest?.('.us-layer'))
                    .find(node=>node&&selectedIds.has(node.dataset.id));
                if(underlying){selectedIds.delete(underlying.dataset.id);selectedId=underlying.dataset.id;syncPrimarySelection();render();e.preventDefault();e.stopPropagation();return;}
            }
            startGroupMove(e);e.preventDefault();e.stopPropagation();return;
        }
        const el=e.target.closest('.us-layer');
        if(!el){
            if(cropMode){
                finishCropInteraction({renderNow:false,reason:'outside-click',deselect:true});
                render();
                e.preventDefault();
                return;
            }

            if(!e.shiftKey){
                clearSelection();
                activeCellIndex=0;
                syncLiveSelectionChrome();
            }

            if(activePointers.size===1&&!e.target.closest('.us-guide-line')){
                beginLasso(e);e.preventDefault();
            }
            return;
        }
        const id=el.dataset.id;
        const hitLayer=page().layers.find(x=>x.id===id);

        // GRID CROP V3 RECOVERY:
        // While crop mode owns this layer, every pointer on the crop layer edits the photo.
        // Do not require [data-cell-index], because the gray crop ghost / layer shell can
        // legitimately become the event target.
        if(cropMode){
            if(String(cropMode.layerId)===String(id)){
                if(!hitLayer||(hitLayer.type!=='grid'&&hitLayer.type!=='frame'))return;

                const count=hitLayer.type==='frame'?1:cellCountForGrid(hitLayer.gridKind||'4');
                ensureCells(hitLayer,count);
                const cropIndex=clamp(Number(cropMode.cellIndex||0),0,count-1);

                setSingleSelection(hitLayer.id);
                activeCellIndex=cropIndex;
                syncLiveSelectionChrome();

                if(activePointers.size===1)pushHistory();
                try{el.setPointerCapture?.(e.pointerId)}catch(_e){}

                startCropDrag(e,hitLayer,cropIndex);
                interactionDiag('GRID_CROP_POINTER_ROUTED',{
                    pageId:activePageId,
                    layerId:hitLayer.id,
                    cellIndex:cropIndex,
                    target:e.target?.className||e.target?.tagName||''
                });

                e.preventDefault();
                e.stopPropagation();
                return;
            }

            // Clicking another layer ends crop first, then the same click continues
            // through the normal selection/drag pipeline for that other layer.
            finishCropInteraction({renderNow:false,reason:'other-layer-click',deselect:true});
        }

        if(hitLayer?.groupId&&!e.altKey&&!additiveSelection){selectedIds=new Set(page().layers.filter(x=>x.groupId===hitLayer.groupId).map(x=>x.id));selectedId=id;syncPrimarySelection();if(selectedIds.size>1){startGroupMove(e);e.preventDefault();return;}}
        const now=Date.now();
        if(!additiveSelection&&hitLayer?.type==='text' && textClickArm.id===id && now-textClickArm.time<420 && !preview){
            textClickArm={id:null,time:0};setSingleSelection(id);openPanel('text');startInlineTextEdit(id);e.preventDefault();e.stopPropagation();return;
        }
        textClickArm={id,time:now};
        if(additiveSelection){if(selectedIds.has(id))selectedIds.delete(id);else selectedIds.add(id);selectedId=id;syncPrimarySelection();render();e.preventDefault();return;}
        if(selectedIds.size>1&&selectedIds.has(id)){selectedId=id;startGroupMove(e);e.preventDefault();return;}
        cancelElementAnimationPreview(id,'pointerdown');cancelCanvasAnimationPreview(activePageId,'pointerdown');
        animationInteractionDiag('POINTERDOWN_AFTER_PREVIEW_CANCEL',el,hitLayer,{
            pointerId:e.pointerId,globalPreview:!!preview
        });
        const cellEl=e.target.closest('[data-cell-index]');
        if(cellEl&&(hitLayer?.type==='grid'||hitLayer?.type==='frame')&&!e.altKey){
            const idx=Number(cellEl.dataset.cellIndex||0);

            if(cropMode&&(String(cropMode.layerId)!==String(id)||Number(cropMode.cellIndex)!==idx)){
                finishCropInteraction({renderNow:false,reason:'select-other-cell'});
            }

            selectMediaCell(id,idx);
            const cellLayer=page().layers.find(x=>String(x.id)===String(id));

            if(e.pointerType==='touch'&&cellLayer?.type==='grid'){
                beginCellLongPress(e,cellLayer,idx);
            }

            // Crop mode: drag only the image inside the selected cell.
            if(cropMode&&String(cropMode.layerId)===String(id)){
                cropMode.cellIndex=idx;
                startCropDrag(e,cellLayer,idx);
                e.preventDefault();
                return;
            }

            // Normal mode intentionally continues into the standard layer-drag path.
            // A click selects the exact cell; a drag from that cell moves the whole grid.
        }
        setSingleSelection(id);if(cellEl)activeCellIndex=Number(cellEl.dataset.cellIndex||0);
        syncLiveSelectionChrome();
        const l=selected();
        if(!l||l.locked){
            animationInteractionDiag('POINTERDOWN_BLOCKED',el,l||hitLayer,{reason:!l?'no-layer':'locked'});
            return;
        }
        const handle=e.target.closest('[data-handle]')?.dataset.handle;if(activePointers.size===1)pushHistory();
        try{el.setPointerCapture?.(e.pointerId)}catch(_e){}
        if(handle==='rotate'){startRotate(e,l);e.preventDefault();return;}if(handle){startResize(e,l,handle);e.preventDefault();return;}

        if(activePointers.size===2){const pts=[...activePointers.values()];const dist=Math.hypot(pts[1].x-pts[0].x,pts[1].y-pts[0].y);const ang=Math.atan2(pts[1].y-pts[0].y,pts[1].x-pts[0].x)*180/Math.PI;if(cropMode&&cropMode.layerId===l.id){ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));gesture={kind:'crop',id:l.id,cellIndex:activeCellIndex,startDist:dist,startScale:Number(l.cells[activeCellIndex]?.scale||1)};}else gesture={kind:'layer',id:l.id,startDist:dist,startAngle:ang,w:l.width,h:l.height,rotation:Number(l.rotation||0)};drag=null;resize=null;rotateDrag=null;cropDrag=null;e.preventDefault();return;}
        const {sx,sy}=canvasScale();drag={id:l.id,startX:e.clientX,startY:e.clientY,x:l.x,y:l.y,sx,sy,pointerId:e.pointerId};
        interactionChanged=false;
        animationInteractionDiag('DRAG_START_COMPUTED',el,l,{
            pointerId:e.pointerId,clientX:e.clientX,clientY:e.clientY,
            designX:l.x,designY:l.y,canvasScale:{sx,sy}
        });
        interactionDiag('DRAG_START',{
            pageId:activePageId,layerId:l.id,pointerId:e.pointerId,
            clientX:e.clientX,clientY:e.clientY,x:l.x,y:l.y,
            canvasScale:{sx,sy},
            animationName:getComputedStyle(el.querySelector('.us-layer-content')||el).animationName,
            animationPlayState:getComputedStyle(el.querySelector('.us-layer-content')||el).animationPlayState,
            computedTransform:getComputedStyle(el).transform
        });
        e.preventDefault();
    });

    window.addEventListener('pointermove',e=>{if(cellLongPress&&cellLongPress.pointerId===e.pointerId&&!cellLongPress.fired){const d=Math.hypot(e.clientX-cellLongPress.startX,e.clientY-cellLongPress.startY);if(d>9)cancelCellLongPress();}
        if(activePointers.has(e.pointerId))activePointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
        if(guideDrag){const pt=canvasPoint(e.clientX,e.clientY),p=page(),h=Number(p.height||844);if(!Array.isArray(p.guides))p.guides=[Math.round(h*.34),Math.round(h*.68)];p.guides[guideDrag.index]=Math.round(clamp(pt.y,24,h-24));dirty=true;setSaveStatus('Belum disimpan','');renderCanvas();return;}
        if(lasso){const pt=canvasPoint(e.clientX,e.clientY);lasso.x=pt.x;lasso.y=pt.y;updateLassoVisual();return;}
        if(groupResize){applyGroupResize(e);return;}
        if(groupDrag){applyGroupMove(e);return;}
        if(gesture&&activePointers.size>=2){
            const pts=[...activePointers.values()].slice(0,2),dist=Math.max(1,Math.hypot(pts[1].x-pts[0].x,pts[1].y-pts[0].y));
            const l=page().layers.find(x=>x.id===gesture.id);if(!l)return;
            if(gesture.kind==='crop'){
                ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));
                l.cells[gesture.cellIndex].scale=clamp(gesture.startScale*(dist/gesture.startDist),1,4);
                clampCropCell(l,gesture.cellIndex,{preferNormalized:true});
                requestAnimationFrame(syncCropGhost);
            }else{
                const ratio=dist/gesture.startDist,ang=Math.atan2(pts[1].y-pts[0].y,pts[1].x-pts[0].x)*180/Math.PI;
                l.width=Math.max(20,Math.round(gesture.w*ratio));l.height=Math.max(20,Math.round(gesture.h*ratio));l.rotation=Math.round(gesture.rotation+(ang-gesture.startAngle));
            }
            touchInteraction();updateLiveLayerDom(l);return;
        }
        if(cropDrag){applyCropDrag(e);return;}
        if(rotateDrag){applyRotate(e);return;}
        if(resize){applyResize(e);return;}
        if(drag){
            const l=page().layers.find(x=>x.id===drag.id);if(!l)return;
            const deltaScreenX=e.clientX-drag.startX,deltaScreenY=e.clientY-drag.startY;
            const deltaDesignX=deltaScreenX*drag.sx,deltaDesignY=deltaScreenY*drag.sy;
            const rawX=drag.x+deltaDesignX,rawY=drag.y+deltaDesignY;
            const snapped=snapLayerPosition(l,rawX,rawY);
            l.x=snapped.x;l.y=snapped.y;
            touchInteraction();updateLiveLayerDom(l);drawSnapGuides(snapped.guides);
            const nowDiag=performance.now();
            if(nowDiag-animationInteractionDiagLast>140){
                animationInteractionDiagLast=nowDiag;
                animationInteractionDiag('DRAG_MOVE_COMPUTED',liveLayerNode(l.id),l,{
                    pointerId:e.pointerId,
                    deltaScreenX:Math.round(deltaScreenX),deltaScreenY:Math.round(deltaScreenY),
                    deltaDesignX:Math.round(deltaDesignX),deltaDesignY:Math.round(deltaDesignY),
                    nextX:l.x,nextY:l.y
                });
            }
            interactionDiag('DRAG_MOVE',{
                pageId:activePageId,layerId:l.id,
                deltaScreenX:Math.round(deltaScreenX),deltaScreenY:Math.round(deltaScreenY),
                deltaDesignX:Math.round(deltaDesignX),deltaDesignY:Math.round(deltaDesignY),
                nextX:l.x,nextY:l.y
            });
        }
    });

    window.addEventListener('pointerup',e=>{
        activePointers.delete(e.pointerId);
        if(activePointers.size<2)gesture=null;
        if(activePointers.size===0){
            if(lasso){finishLasso();return;}

            const hadCropInteraction=!!cropDrag||gesture?.kind==='crop';
            const cropFinishTarget=hadCropInteraction&&cropMode
                ? {layerId:cropMode.layerId,cellIndex:cropMode.cellIndex}
                : null;

            const committedDrag=drag?{id:drag.id,startX:drag.x,startY:drag.y}:null;
            const committedLayer=committedDrag?page().layers.find(x=>x.id===committedDrag.id):null;
            let absorbedImage=false;
            if(committedLayer?.type==='image'){
                absorbedImage=tryDropImageLayerIntoCell(committedLayer,e.clientX,e.clientY);
            }
            if(committedLayer&&!absorbedImage){
                animationInteractionDiag('DRAG_COMMIT_COMPUTED',liveLayerNode(committedLayer.id),committedLayer,{
                    previousX:committedDrag.startX,previousY:committedDrag.startY,
                    newX:committedLayer.x,newY:committedLayer.y
                });
                interactionDiag('DRAG_COMMIT',{
                    pageId:activePageId,layerId:committedLayer.id,
                    previousX:committedDrag.startX,previousY:committedDrag.startY,
                    newX:committedLayer.x,newY:committedLayer.y,
                    stateX:committedLayer.x,stateY:committedLayer.y
                });
            }
            drag=null;resize=null;rotateDrag=null;cropDrag=null;groupDrag=null;groupResize=null;
            if(guideDrag){guideDrag=null;interactionChanged=true;}
            canvas?.querySelectorAll('.crop-moving').forEach(x=>x.classList.remove('crop-moving'));clearSnapGuides();

            if(cropFinishTarget&&cropMode){
                const cropLayer=page().layers.find(x=>String(x.id)===String(cropFinishTarget.layerId));
                if(cropLayer)clampCropCell(cropLayer,cropFinishTarget.cellIndex);

                cropDrag=null;
                gesture=null;

                if(interactionChanged){
                    interactionChanged=false;
                    markDirty();
                }

                requestAnimationFrame(syncCropGhost);
                interactionDiag('GRID_CROP_EDIT_COMMIT',{
                    pageId:activePageId,
                    layerId:cropFinishTarget.layerId,
                    cellIndex:cropFinishTarget.cellIndex
                });
                return;
            }

            if(absorbedImage){
                interactionChanged=false;
            }else if(interactionChanged){
                interactionChanged=false;
                markDirty();
                render();
            }
        }
    });
    window.addEventListener('pointercancel',e=>{if(cellLongPress?.pointerId===e.pointerId)cancelCellLongPress();activePointers.delete(e.pointerId);if(cropDrag){cropDrag=null;canvas?.querySelectorAll('.crop-moving').forEach(x=>x.classList.remove('crop-moving'));requestAnimationFrame(syncCropGhost);}if(activePointers.size===0){drag=null;resize=null;rotateDrag=null;cropDrag=null;groupDrag=null;groupResize=null;guideDrag=null;gesture=null;if(lasso)finishLasso();if(interactionChanged){interactionChanged=false;markDirty();render();}}});



    function startInlineTextEdit(layerId){
        const l=page().layers.find(x=>x.id===layerId);if(!l||l.type!=='text'||l.locked||(!isInstanceMode&&editorViewMode!=='sample'))return;
        if(isInstanceMode&&editorViewMode==='customer'&&l.binding&&l.binding!=='none'){
            const key=l.binding==='couple_names'?'groom_name':l.binding;
            setSingleSelection(layerId);openPanel('customer-data');
            requestAnimationFrame(()=>{const control=document.querySelector(`[data-customer-key="${CSS.escape(String(key))}"]`);control?.closest('.us-customer-field')?.scrollIntoView({behavior:'smooth',block:'center'});control?.focus();});
            return;
        }
        setSingleSelection(layerId);render();
        requestAnimationFrame(()=>{const el=canvas.querySelector(`.us-layer[data-id="${layerId}"]`),content=el?.querySelector('.us-text');if(!el||!content)return;pushHistory();inlineTextEdit={id:layerId,original:l.text||''};el.classList.add('inline-text-edit');content.contentEditable='true';content.spellcheck=true;content.focus();const range=document.createRange();range.selectNodeContents(content);range.collapse(false);const sel=window.getSelection();sel.removeAllRanges();sel.addRange(range);
        const commit=()=>{if(!inlineTextEdit||inlineTextEdit.id!==layerId)return;l.text=content.innerText.replace(/\n{3,}/g,'\n\n');inlineTextEdit=null;markDirty();render();};content.addEventListener('blur',commit,{once:true});content.addEventListener('keydown',ev=>{if(ev.key==='Escape'){ev.preventDefault();ev.stopPropagation();l.text=inlineTextEdit?.original??l.text;inlineTextEdit=null;render();return;}if((ev.ctrlKey||ev.metaKey)&&ev.key==='Enter'){ev.preventDefault();ev.stopPropagation();content.blur();return;}ev.stopPropagation();});content.addEventListener('input',()=>{l.text=content.innerText;if($('pText'))$('pText').value=l.text;dirty=true;setSaveStatus('Belum disimpan','');});});
    }
    const cellMediaContextMenu=$('cellMediaContextMenu');

    function closeCellMediaContextMenu(){
        cellMediaContextMenu?.classList.remove('open');
        cellContextTarget=null;
    }

    function openCellMediaContextMenu(e,layerId,cellIndex){
        const src=mediaCellSource(layerId,cellIndex);
        if(!src)return false;

        e.preventDefault();
        e.stopPropagation();
        selectMediaCell(layerId,cellIndex);
        cellContextTarget={layerId,cellIndex};

        const pasteBtn=cellMediaContextMenu?.querySelector('[data-cell-menu-action="paste"]');
        if(pasteBtn)pasteBtn.disabled=!cellMediaClipboard?.url;

        if(cellMediaContextMenu){
            cellMediaContextMenu.style.left='0px';
            cellMediaContextMenu.style.top='0px';
            cellMediaContextMenu.classList.add('open');

            const rect=cellMediaContextMenu.getBoundingClientRect();
            const x=Math.min(e.clientX,window.innerWidth-rect.width-8);
            const y=Math.min(e.clientY,window.innerHeight-rect.height-8);

            cellMediaContextMenu.style.left=Math.max(8,x)+'px';
            cellMediaContextMenu.style.top=Math.max(8,y)+'px';
        }

        interactionDiag('GRID_CELL_CONTEXT_OPEN',{pageId:activePageId,layerId,cellIndex});
        return true;
    }

    livePages.addEventListener('contextmenu',e=>{
        const cell=e.target.closest('[data-cell-index]');
        const layerEl=cell?.closest('.us-layer');
        if(!e.altKey||!cell||!layerEl)return;

        const l=page().layers.find(x=>String(x.id)===String(layerEl.dataset.id));
        if(!l||(l.type!=='grid'&&l.type!=='frame'))return;

        const idx=Number(cell.dataset.cellIndex||0);
        ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));
        if(!l.cells[idx]?.src)return;

        if(openCellMediaContextMenu(e,l.id,idx))e.stopImmediatePropagation();
    });

    livePages.addEventListener('contextmenu',e=>{
        const groupBox=e.target.closest('[data-group-selection]');
        if(groupBox&&selectedIds.size>1){
            if(!activateCanvasFromEvent(e,{clearOnPageChange:false}))return;
            e.preventDefault();e.stopImmediatePropagation();closeCellMediaContextMenu();hidePageMenu();
            showElementContextMenu(e.clientX,e.clientY);
            return;
        }
        const layerEl=e.target.closest('.us-layer')||[...document.elementsFromPoint(e.clientX,e.clientY)]
            .map(node=>node.closest?.('.us-layer')).find(Boolean);
        if(!layerEl)return;
        if(!activateCanvasFromEvent(e,{clearOnPageChange:false}))return;
        e.preventDefault();e.stopImmediatePropagation();closeCellMediaContextMenu();hidePageMenu();
        const id=layerEl.dataset.id;
        if(!selectedIds.has(id)){setSingleSelection(id);activeCellIndex=Number(e.target.closest('[data-cell-index]')?.dataset.cellIndex||0);render();}
        else{selectedId=id;syncPrimarySelection();renderDesktopContextToolbar();}
        showElementContextMenu(e.clientX,e.clientY);
    });

    cellMediaContextMenu?.addEventListener('click',async e=>{
        const btn=e.target.closest('[data-cell-menu-action]');
        if(!btn||!cellContextTarget)return;

        const {layerId,cellIndex}=cellContextTarget;
        const action=btn.dataset.cellMenuAction;
        closeCellMediaContextMenu();

        if(action==='copy'){copyMediaCell(layerId,cellIndex);return;}
        if(action==='paste'){pasteMediaCell(layerId,cellIndex);return;}
        if(action==='delete'){clearMediaCell(layerId,cellIndex,{history:true,reason:'context-delete'});return;}
        if(action==='duplicate'){await duplicateMediaCellAsFree(layerId,cellIndex);return;}
        if(action==='detach'){await detachMediaCell(layerId,cellIndex);return;}
    });

    document.addEventListener('pointerdown',e=>{
        if(cellMediaContextMenu?.classList.contains('open')&&!e.target.closest('#cellMediaContextMenu')){
            closeCellMediaContextMenu();
        }
        if($('elementContextMenu')?.classList.contains('open')&&!e.target.closest('#elementContextMenu'))hideElementContextMenu();
    },true);

    window.addEventListener('blur',()=>{closeCellMediaContextMenu();hideElementContextMenu();});
    window.addEventListener('resize',()=>{closeCellMediaContextMenu();hideElementContextMenu();});

    livePages.addEventListener('dblclick',e=>{if(!activateCanvasFromEvent(e,{clearOnPageChange:false})||selectedIds.size>1)return;const el=e.target.closest('.us-layer');if(!el)return;const l=page().layers.find(x=>x.id===el.dataset.id);if(l?.type==='text'){e.preventDefault();e.stopImmediatePropagation();startInlineTextEdit(l.id);}});

    /* GRID_CROP_ENTRY_RECOVERY_V8 */
    let gridCropClickArm={time:0,layerId:null,cellIndex:null};

    livePages.addEventListener('click',e=>{if(!activateCanvasFromEvent(e,{clearOnPageChange:false}))return;
        const deleteCellBtn=e.target.closest('[data-cell-delete]');
        if(deleteCellBtn){
            e.preventDefault();e.stopPropagation();
            const layerEl=deleteCellBtn.closest('.us-layer');
            if(layerEl)clearMediaCell(layerEl.dataset.id,Number(deleteCellBtn.dataset.cellDelete||0),{history:true,reason:'touch-longpress'});
            return;
        }

        // Pointer capture can retarget click from .us-media-cell to .us-layer.
        // Recover the intended cell from activeCellIndex, which was set on pointerdown.
        if(!cropMode){
            const layerEl=e.target.closest('.us-layer');
            const l=layerEl?page().layers.find(x=>String(x.id)===String(layerEl.dataset.id)):null;

            if(l&&(l.type==='grid'||l.type==='frame')){
                const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');
                ensureCells(l,count);

                const directCell=e.target.closest('[data-cell-index]');
                const idx=directCell
                    ? clamp(Number(directCell.dataset.cellIndex||0),0,count-1)
                    : clamp(Number(activeCellIndex||0),0,count-1);

                if(l.cells[idx]?.src){
                    const now=Date.now();
                    const same=
                        String(gridCropClickArm.layerId)===String(l.id)
                        && Number(gridCropClickArm.cellIndex)===Number(idx)
                        && (now-gridCropClickArm.time)<430;

                    if(same){
                        gridCropClickArm={time:0,layerId:null,cellIndex:null};
                        enterCropMode(l.id,idx);
                        interactionDiag('GRID_CROP_DOUBLE_ACTIVATE',{
                            pageId:activePageId,
                            layerId:l.id,
                            cellIndex:idx,
                            recoveredFromPointerCapture:!directCell
                        });
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }

                    gridCropClickArm={time:now,layerId:l.id,cellIndex:idx};
                }else{
                    gridCropClickArm={time:0,layerId:null,cellIndex:null};
                }
            }else{
                gridCropClickArm={time:0,layerId:null,cellIndex:null};
            }
        }

        if(!cropMode)return;
    });

    livePages.addEventListener('dblclick',e=>{if(!activateCanvasFromEvent(e,{clearOnPageChange:false})||selectedIds.size>1)return;
        const el=e.target.closest('.us-layer');if(!el)return;
        const l=page().layers.find(x=>x.id===el.dataset.id);if(!l)return;
        const cell=e.target.closest('[data-cell-index]');

        if(l.type==='frame'){
            if(!(cropMode&&String(cropMode.layerId)===String(l.id)&&Number(cropMode.cellIndex)===0)){
                enterCropMode(l.id,0);
            }
            e.preventDefault();return;
        }

        if(l.type==='grid'){
            const count=cellCountForGrid(l.gridKind||'4');
            ensureCells(l,count);
            const idx=cell
                ? clamp(Number(cell.dataset.cellIndex||0),0,count-1)
                : clamp(Number(activeCellIndex||0),0,count-1);

            if(l.cells[idx]?.src&&!(cropMode&&String(cropMode.layerId)===String(l.id)&&Number(cropMode.cellIndex)===idx)){
                enterCropMode(l.id,idx);
            }
            e.preventDefault();return;
        }

        if(l.type==='text'){
            e.preventDefault();
            startInlineTextEdit(l.id);
        }
    });
    livePages.addEventListener('pointerup',e=>{if(!activateCanvasFromEvent(e,{clearOnPageChange:false}))return;
        if(e.pointerType!=='touch')return;
        const longPressFired=cellLongPress?.pointerId===e.pointerId&&cellLongPress?.fired;
        if(cellLongPress?.pointerId===e.pointerId)cancelCellLongPress();
        if(longPressFired){lastTap={time:0,id:null,cell:null};return;}
        const el=e.target.closest('.us-layer');if(!el)return;
        const now=Date.now();
        const l=page().layers.find(x=>x.id===el.dataset.id);
        const cell=e.target.closest('[data-cell-index]');
        let cellIndex=cell?Number(cell.dataset.cellIndex||0):null;

        if(!cell&&(l?.type==='grid'||l?.type==='frame')){
            const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');
            ensureCells(l,count);
            cellIndex=l.type==='frame'?0:clamp(Number(activeCellIndex||0),0,count-1);
        }

        if(selectedIds.size>1){lastTap={time:0,id:null,cell:null};return;}
        if(lastTap.id===el.dataset.id&&lastTap.cell===cellIndex&&(now-lastTap.time)<360){
            if(l&&l.type==='frame')enterCropMode(l.id,0);
            else if(l&&l.type==='grid'&&cellIndex!==null&&l.cells?.[cellIndex]?.src)enterCropMode(l.id,cellIndex);
            else if(l&&l.type==='text')startInlineTextEdit(l.id);
            lastTap={time:0,id:null,cell:null};
        }else lastTap={time:now,id:el.dataset.id,cell:cellIndex};
    });
    livePages.addEventListener('wheel',e=>{if(!activateCanvasFromEvent(e,{clearOnPageChange:false}))return;
        if(!cropMode)return;
        const el=e.target.closest('.us-layer');if(!el||el.dataset.id!==cropMode.layerId)return;
        const l=selected();if(!l)return;
        ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));
        const c=l.cells[cropMode.cellIndex];if(!cropCellHasRenderableMedia(l,cropMode.cellIndex))return;e.preventDefault();
        c.scale=clamp(Number(c.scale||1)+(e.deltaY<0?.08:-.08),1,4);
        clampCropCell(l,cropMode.cellIndex,{preferNormalized:true});
        markDirty();
        render();
        requestAnimationFrame(syncCropGhost);
    },{passive:false});


    livePages.addEventListener('dragover',e=>{if(!activateCanvasFromEvent(e,{clearOnPageChange:false}))return;
        if(e.dataTransfer.types.includes('application/x-undanganta-asset')){e.preventDefault();e.dataTransfer.dropEffect='copy';}
    });
    livePages.addEventListener('drop',e=>{if(!activateCanvasFromEvent(e,{clearOnPageChange:false}))return;
        const raw=e.dataTransfer.getData('application/x-undanganta-asset');if(!raw)return;
        e.preventDefault();
        let a;try{a=JSON.parse(raw)}catch(err){return;}
        const layerEl=e.target.closest('.us-layer');
        const cellEl=e.target.closest('[data-cell-index]');
        if(layerEl){
            selectedId=layerEl.dataset.id;
            const l=selected();
            if(l&&(l.type==='frame'||l.type==='grid')&&a.type!=='video'){
                activeCellIndex=cellEl?Number(cellEl.dataset.cellIndex||0):0;
                fillMediaCellDirect(l.id,activeCellIndex,a,{history:true,reason:'asset-drag-drop'});
                return;
            }
        }
        addAsset(a);
    });

    layerList.addEventListener('click',e=>{const r=e.target.closest('.us-layer-row');if(!r)return;const id=r.dataset.id,l=page().layers.find(x=>x.id===id),additive=e.shiftKey||e.ctrlKey||e.metaKey;if(additive){if(selectedIds.has(id))selectedIds.delete(id);else selectedIds.add(id);selectedId=id;syncPrimarySelection();}else if(l?.groupId&&!e.altKey){selectedIds=new Set(page().layers.filter(x=>x.groupId===l.groupId).map(x=>x.id));selectedId=id;}else setSingleSelection(id);activeCellIndex=0;render();});

    const bind=(id,key,convert=v=>v)=>{
        const el=$(id);if(!el)return;
        if(el.tagName==='SELECT'){el.addEventListener('change',e=>updateSelected(key,convert(e.target.value)));return;}
        let interactionSnapshot=null;
        const begin=()=>{if(interactionSnapshot===null)interactionSnapshot=snapshot();};
        el.addEventListener('focus',begin);el.addEventListener('pointerdown',begin);
        el.addEventListener('input',e=>{
            const l=selected();if(!l)return;
            l[key]=convert(e.target.value);dirty=true;setSaveStatus('Belum disimpan','');render();
        });
        const commit=()=>{
            if(interactionSnapshot!==null){history.push(interactionSnapshot);if(history.length>80)history.shift();future=[];interactionSnapshot=null;markDirty();}
        };
        el.addEventListener('change',commit);el.addEventListener('blur',commit);
    };
    bind('pName','name');bind('pX','x',Number);bind('pY','y',Number);bind('pW','width',v=>Math.max(10,Number(v)));bind('pH','height',v=>Math.max(10,Number(v)));bind('pRot','rotation',Number);bind('pOpacity','opacity',v=>clamp(Number(v),0,1));bind('pRadius','borderRadius',v=>Math.max(0,Number(v)));bind('pAnimation','animation');bind('pDuration','duration',Number);bind('pDelay','delay',Number);bind('pText','text');bind('pFont','fontFamily');bind('pFontSize','fontSize',Number);bind('pAlign','textAlign');
    $('pBinding')?.addEventListener('focus',()=>interactionDiag('DATA_BIND_OPEN',{canvasId:activePageId,layerId:selectedId,galleryOptions:availableGalleryBindingCount()}));
    $('pBinding')?.addEventListener('change',e=>{const l=selected();if(!l)return;pushHistory();l.binding=e.target.value;interactionDiag('DATA_BIND_SET',{canvasId:activePageId,layerId:l.id,binding:l.binding});markDirty();render();});
    bind('pCustomerEditPolicy','customerEditPolicy');
    $('pOptional').onchange=e=>updateSelected('optional',e.target.checked);
    $('pHideWhenEmpty').onchange=e=>updateSelected('hideWhenEmpty',e.target.checked);

    let liveColorSnapshot=null;
    function beginLiveColor(){if(liveColorSnapshot===null)liveColorSnapshot=snapshot();}
    function finishLiveColor(){
        if(liveColorSnapshot!==null){history.push(liveColorSnapshot);if(history.length>80)history.shift();future=[];liveColorSnapshot=null;}
        markDirty();
    }
    function liveLayerColor(id,key){
        const el=$(id);if(!el)return;
        el.addEventListener('pointerdown',beginLiveColor);
        el.addEventListener('focus',beginLiveColor);
        el.addEventListener('input',e=>{
            const l=selected();if(!l)return;
            l[key]=e.target.value;dirty=true;setSaveStatus('Belum disimpan','');render();
        });
        el.addEventListener('change',finishLiveColor);
        el.addEventListener('blur',()=>{if(liveColorSnapshot!==null)finishLiveColor();});
    }
    liveLayerColor('pBg','background');
    liveLayerColor('pColor','color');
    const canvasBgInput=$('canvasBg');
    canvasBgInput?.addEventListener('pointerdown',beginLiveColor);
    canvasBgInput?.addEventListener('focus',beginLiveColor);
    canvasBgInput?.addEventListener('input',e=>{page().background=e.target.value;dirty=true;setSaveStatus('Belum disimpan','');renderCanvas();});
    canvasBgInput?.addEventListener('change',finishLiveColor);
    canvasBgInput?.addEventListener('blur',()=>{if(liveColorSnapshot!==null)finishLiveColor();});

        $('pLoop').onchange=e=>updateSelected('loop',e.target.checked);$('pLocked').onchange=e=>updateSelected('locked',e.target.checked);$('pHidden').onchange=e=>updateSelected('hidden',e.target.checked);

    $('elementAnimationLoop')?.addEventListener('change',e=>{
        const l=selected();if(!l){e.target.checked=false;notify('Pilih layer dulu');return;}
        pushHistory();
        elementAnimation(l).loop=!!e.target.checked;
        syncElementAnimationLegacy(l);
        if($('pLoop'))$('pLoop').checked=!!e.target.checked;
        markDirty();render();
        interactionDiag('ELEMENT_ANIMATION_LOOP_SET',{canvasId:activePageId,layerId:l.id,loop:!!e.target.checked,animationType:elementAnimation(l).type});
    });
    $('animTrigger').addEventListener('change',e=>updateSelected('animationTrigger',e.target.value));
    $('animEasing').addEventListener('change',e=>updateSelected('animationEasing',e.target.value));
    document.querySelectorAll('[data-element-animation-preset]').forEach(btn=>btn.addEventListener('click',()=>{
        const l=selected();if(!l){notify('Pilih layer dulu');return;}
        const a=elementAnimation(l),clicked=btn.dataset.elementAnimationPreset||'none';
        const next=(clicked!=='none'&&a.type===clicked)?'none':clicked;
        interactionDiag('ANIMATION_PRESET_CLICK',{scope:'element',layerId:l.id,canvasId:activePageId,animationType:clicked});
        cancelElementAnimationPreview(l.id,next==='none'?'preset-toggle-none':'preset-change');
        pushHistory();a.type=next;syncElementAnimationLegacy(l);markDirty();render();
        if(next==='none')interactionDiag('ANIMATION_NONE',{scope:'element',layerId:l.id,canvasId:activePageId,animationType:'none'});
        else{interactionDiag('ANIMATION_SET',{scope:'element',layerId:l.id,canvasId:activePageId,animationType:next});requestAnimationFrame(()=>startElementAnimationPreview(l.id));}
    }));
    document.querySelectorAll('[data-canvas-animation-preset]').forEach(btn=>btn.addEventListener('click',()=>{
        const p=page(),a=canvasAnimation(p),clicked=btn.dataset.canvasAnimationPreset||'none';
        const next=(clicked!=='none'&&a.type===clicked)?'none':clicked;
        interactionDiag('ANIMATION_PRESET_CLICK',{scope:'canvas',canvasId:p.id,animationType:clicked});
        cancelCanvasAnimationPreview(p.id,next==='none'?'preset-toggle-none':'preset-change');
        pushHistory();a.type=next;syncCanvasAnimationLegacy(p);markDirty();render();
        if(next==='none')interactionDiag('ANIMATION_NONE',{scope:'canvas',canvasId:p.id,animationType:'none'});
        else{interactionDiag('ANIMATION_SET',{scope:'canvas',canvasId:p.id,animationType:next});requestAnimationFrame(()=>startCanvasAnimationPreview(p.id));}
    }));

    $('pFrameKind').addEventListener('change',e=>{
        const l=selected();if(!l||l.type!=='frame')return;
        pushHistory();l.frameKind=e.target.value;
        const preset={portrait:[190,250],landscape:[280,180],circle:[190,190],heart:[200,190],arch:[190,250],polaroid:[210,270],rounded:[200,240],square:[210,210]}[l.frameKind];
        if(preset){l.width=preset[0];l.height=preset[1];}
        markDirty();render();
    });

        $('pMediaCell').addEventListener('change',e=>{activeCellIndex=Number(e.target.value||0);render();});
    $('pCellBinding').addEventListener('change',e=>{
        const l=selected();if(!l||l.type!=='grid')return;
        const count=cellCountForGrid(l.gridKind||'4');ensureCells(l,count);pushHistory();
        l.cells[activeCellIndex].binding=e.target.value||'none';markDirty();render();
    });
    function updateMediaCell(key,value){
        const l=selected();if(!l||(l.type!=='frame'&&l.type!=='grid'))return;
        const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');ensureCells(l,count);pushHistory();l.cells[activeCellIndex][key]=value;markDirty();render();
    }
    $('pMediaZoom').addEventListener('change',e=>updateMediaCell('scale',clamp(Number(e.target.value||1),.5,4)));
    $('pMediaX').addEventListener('change',e=>updateMediaCell('posX',clamp(Number(e.target.value||0),-100,100)));
    $('pMediaY').addEventListener('change',e=>updateMediaCell('posY',clamp(Number(e.target.value||0),-100,100)));
    $('pGridGap').addEventListener('change',e=>{const l=selected();if(!l||l.type!=='grid')return;pushHistory();l.gap=clamp(Number(e.target.value||0),0,60);markDirty();render();});
    $('pGalleryAnimation').addEventListener('change',e=>updateSelected('galleryAnimation',e.target.value));
    $('pGalleryAutoplay').addEventListener('change',e=>updateSelected('galleryAutoplay',e.target.value==='1'));
    $('pGalleryInterval').addEventListener('change',e=>updateSelected('galleryInterval',clamp(Number(e.target.value||4),2,12)));
    $('pGalleryLightbox').addEventListener('change',e=>updateSelected('galleryLightbox',e.target.checked));
    $('pGalleryThumbnails').addEventListener('change',e=>updateSelected('galleryThumbnails',e.target.checked));
    $('previewGalleryAnimation').addEventListener('click',()=>{
        const l=selected();if(!l||l.type!=='grid')return;const el=canvas.querySelector(`.us-layer[data-id="${l.id}"] .us-grid-layer`);if(!el)return;
        ['pulse','fade','slide','zoom','blur'].forEach(x=>el.classList.remove('us-gallery-preview-'+x));
        const a=l.galleryAnimation||'fade';if(a!=='none'){void el.offsetWidth;el.classList.add('us-gallery-preview-'+a);setTimeout(()=>el.classList.remove('us-gallery-preview-'+a),1250);}
    });

    $('clearMediaCell').addEventListener('click',()=>{const l=selected();if(!l||(l.type!=='frame'&&l.type!=='grid'))return;const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');ensureCells(l,count);pushHistory();const binding=l.cells[activeCellIndex]?.binding||'none';l.cells[activeCellIndex]={src:null,posX:0,posY:0,scale:1,binding};markDirty();render();});
    $('cropDoneBtn')?.addEventListener('click',exitCropMode);
    $('cropResetBtn')?.addEventListener('click',resetCrop);


    ['tplName','tplSlug','tplPlan','tplEditable'].forEach(id=>{const el=$(id);if(!el)return;el.addEventListener(el.type==='checkbox'||el.tagName==='SELECT'?'change':'input',()=>markDirty());});

    let pageNameSnapshot=null,pageDurationSnapshot=null;
    $('pageName').addEventListener('focus',()=>{if(pageNameSnapshot===null)pageNameSnapshot=snapshot();});
    $('pageName').addEventListener('input',e=>{page().name=e.target.value||'Canvas';dirty=true;setSaveStatus('Belum disimpan','');renderPages();});
    $('pageName').addEventListener('blur',()=>{page().name=$('pageName').value.trim()||'Canvas';if(pageNameSnapshot!==null){history.push(pageNameSnapshot);pageNameSnapshot=null;markDirty();render();}});
    $('pageTransition')?.addEventListener('change',e=>{const p=page(),a=canvasAnimation(p);pushHistory();a.type=e.target.value||'none';syncCanvasAnimationLegacy(p);markDirty();render();if(a.type!=='none')requestAnimationFrame(()=>startCanvasAnimationPreview(p.id));});
    $('pageDuration').addEventListener('focus',()=>{if(pageDurationSnapshot===null)pageDurationSnapshot=snapshot();});
    $('pageDuration').addEventListener('input',e=>{const p=page(),a=canvasAnimation(p);a.duration=clamp(Number(e.target.value||.8),.1,5);syncCanvasAnimationLegacy(p);dirty=true;setSaveStatus('Belum disimpan','');});
    $('pageDuration').addEventListener('change',()=>{if(pageDurationSnapshot!==null){history.push(pageDurationSnapshot);pageDurationSnapshot=null;markDirty();}});
    $('pageEasing').addEventListener('change',e=>{const p=page(),a=canvasAnimation(p);pushHistory();a.easing=e.target.value;syncCanvasAnimationLegacy(p);markDirty();render();});

    $('addText').onclick=()=>{addText();openPanel('text');};
    document.querySelectorAll('[data-text-preset]').forEach(btn=>btn.addEventListener('click',()=>{addTextPreset(btn.dataset.textPreset);openPanel('text');}));
    document.querySelectorAll('[data-shape-kind]').forEach(btn=>btn.addEventListener('click',()=>addShape(btn.dataset.shapeKind)));
    document.querySelectorAll('[data-frame-kind]').forEach(btn=>btn.addEventListener('click',()=>addFrame(btn.dataset.frameKind)));
    document.querySelectorAll('[data-grid-kind]').forEach(btn=>btn.addEventListener('click',()=>addGrid(btn.dataset.gridKind)));
    document.querySelectorAll('[data-component]').forEach(btn=>btn.addEventListener('click',()=>addComponent(btn.dataset.component)));
    document.querySelectorAll('[data-element-tab]').forEach(btn=>btn.addEventListener('click',()=>{
        document.querySelectorAll('[data-element-tab]').forEach(x=>x.classList.toggle('active',x===btn));
        document.querySelectorAll('[data-element-group]').forEach(g=>g.hidden=g.dataset.elementGroup!==btn.dataset.elementTab);
    }));

    $('clearSampleContent')?.addEventListener('click',()=>{
        const l=selected();if(!l)return;
        pushHistory();
        if(l.type==='text')l.text='';
        else if(l.type==='image')l.src='';
        else if(l.type==='frame'||l.type==='grid'){
            const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');ensureCells(l,count);
            l.cells=l.cells.map(()=>({src:null,posX:0,posY:0,scale:1}));
        }
        markDirty();render();notify('Isi contoh dikosongkan — desain tetap ada');
    });

    
    document.querySelectorAll('[data-align-action]').forEach(btn=>btn.addEventListener('click',()=>alignSelection(btn.dataset.alignAction)));
    $('distributeX')?.addEventListener('click',()=>distributeSelection('x'));
    $('distributeY')?.addEventListener('click',()=>distributeSelection('y'));
    $('groupSelection')?.addEventListener('click',groupSelectedLayers);
    $('ungroupSelection')?.addEventListener('click',ungroupSelectedLayers);
    $('bringForward')?.addEventListener('click',()=>setLayerZ(selectedLayers(),'forward'));
    $('sendBackward')?.addEventListener('click',()=>setLayerZ(selectedLayers(),'backward'));
    $('bringFront')?.addEventListener('click',()=>setLayerZ(selectedLayers(),'front'));
    $('sendBack')?.addEventListener('click',()=>setLayerZ(selectedLayers(),'back'));
    $('toggleSnap')?.addEventListener('click',()=>{snapEnabled=!snapEnabled;$('toggleSnap').classList.toggle('active',snapEnabled);$('toggleSnap').setAttribute('aria-pressed',snapEnabled?'true':'false');if(!snapEnabled)clearSnapGuides();notify(snapEnabled?'Perataan otomatis aktif':'Perataan otomatis nonaktif');});

    $('toggleSafeArea')?.addEventListener('click',()=>{safeAreaEnabled=!safeAreaEnabled;$('toggleSafeArea').classList.toggle('active',safeAreaEnabled);$('toggleSafeArea').setAttribute('aria-pressed',safeAreaEnabled?'true':'false');$('safeAreaEnabled').checked=safeAreaEnabled;render();});
    $('safeAreaEnabled')?.addEventListener('change',e=>{safeAreaEnabled=!!e.target.checked;$('toggleSafeArea').classList.toggle('active',safeAreaEnabled);$('toggleSafeArea').setAttribute('aria-pressed',safeAreaEnabled?'true':'false');render();});
    $('safeAreaMargin')?.addEventListener('input',e=>{safeAreaMargin=clamp(Number(e.target.value||0),0,80);render();});
    $('keepInsideCanvas')?.addEventListener('change',e=>{keepInsideCanvas=!!e.target.checked;});
    $('respectSafeArea')?.addEventListener('change',e=>{respectSafeArea=!!e.target.checked;});
    $('fitSelectionSafe')?.addEventListener('click',fitSelectionIntoSafeArea);
    $('responsiveMode')?.addEventListener('change',e=>{const l=selected();if(!l)return;pushHistory();l.responsiveMode=e.target.value;markDirty();render();});

    $('duplicateLayer').onclick=()=>duplicateSelectedLayers();
    $('deleteLayer').onclick=()=>deleteSelectedLayers();
    $('bringFrontSecondary')?.addEventListener('click',()=>setLayerZ(selectedLayers(),'front'));
    $('sendBackSecondary')?.addEventListener('click',()=>setLayerZ(selectedLayers(),'back'));

    $('bringFront').onclick=()=>{const l=selected();if(l)updateSelected('zIndex',Math.max(0,...page().layers.map(x=>x.zIndex||0))+1);};
    $('sendBack').onclick=()=>{const l=selected();if(l)updateSelected('zIndex',Math.min(0,...page().layers.map(x=>x.zIndex||0))-1);};

    $('pagesBar').addEventListener('click',e=>{
        if(e.target.id==='addPage'){addNewPage();return;}
        const more=e.target.closest('[data-page-more]');
        if(more){e.preventDefault();e.stopPropagation();const chip=e.target.closest('.us-page-chip');const r=more.getBoundingClientRect();showPageMenu(r.right,r.bottom+4,chip?.dataset.id);return;}
        const chip=e.target.closest('.us-page-chip');if(chip)setActivePage(chip.dataset.id,preview);
    });

    let transitionTargetPageId=null;
    function hideTransitionPopover(){$('transitionPopover')?.classList.remove('open');transitionTargetPageId=null;}
    $('pagesBar').addEventListener('click',e=>{const btn=e.target.closest('[data-transition-page]');if(!btn)return;e.preventDefault();e.stopPropagation();transitionTargetPageId=btn.dataset.transitionPage;const p=state.pages.find(x=>x.id===transitionTargetPageId);if(!p)return;document.querySelectorAll('[data-quick-transition]').forEach(x=>x.classList.toggle('active',x.dataset.quickTransition===(p.transition?.type||'none')));const pop=$('transitionPopover');pop.classList.add('open');const r=btn.getBoundingClientRect(),pr=pop.getBoundingClientRect();pop.style.left=Math.max(8,Math.min(window.innerWidth-pr.width-8,r.left+r.width/2-pr.width/2))+'px';pop.style.top=Math.max(8,r.top-pr.height-8)+'px';});
    $('transitionPopover')?.addEventListener('click',e=>{const o=e.target.closest('[data-quick-transition]');if(!o||!transitionTargetPageId)return;const p=state.pages.find(x=>x.id===transitionTargetPageId);if(!p)return;pushHistory();p.transition.type=o.dataset.quickTransition;markDirty();render();hideTransitionPopover();});
    document.addEventListener('click',e=>{if(!e.target.closest('#transitionPopover')&&!e.target.closest('[data-transition-page]'))hideTransitionPopover();});

    $('pagesBar').addEventListener('dragstart',e=>{if(e.target.closest('[data-page-more]')){e.preventDefault();return;}const chip=e.target.closest('.us-page-chip');if(!chip)return;pageDragId=chip.dataset.id;chip.classList.add('dragging');e.dataTransfer.effectAllowed='move';});
    $('pagesBar').addEventListener('dragend',e=>{e.target.closest('.us-page-chip')?.classList.remove('dragging');pageDragId=null;});
    $('pagesBar').addEventListener('dragover',e=>{if(pageDragId)e.preventDefault();});
    $('pagesBar').addEventListener('drop',e=>{const target=e.target.closest('.us-page-chip');if(!target||!pageDragId||target.dataset.id===pageDragId)return;e.preventDefault();pushHistory();const from=state.pages.findIndex(p=>p.id===pageDragId),to=state.pages.findIndex(p=>p.id===target.dataset.id),moved=state.pages.splice(from,1)[0];state.pages.splice(to,0,moved);markDirty();render();});
    $('applyRecommendedSize')?.addEventListener('click',()=>{pushHistory();const p=page();p.width=390;p.height=844;state.width=390;state.height=844;markDirty();render();});
        $('duplicatePage').onclick=duplicateActivePage;
    $('deletePage').onclick=deleteActivePage;
    $('prevCanvas')?.addEventListener('click',()=>changePageBy(-1));$('nextCanvas')?.addEventListener('click',()=>changePageBy(1));$('previewTransition')?.addEventListener('click',playTransition);$('previewTransitionSide')?.addEventListener('click',playTransition);

    $('undoBtn').onclick=()=>{if(!history.length)return;future.push(snapshot());restore(history.pop());markDirty();render();};
    $('redoBtn').onclick=()=>{if(!future.length)return;history.push(snapshot());restore(future.pop());markDirty();render();};
    $('previewBtn').onclick=()=>openResponsivePreview(window.innerWidth>=900?'desktop':'mobile');

    $('premiumGalleryPickerToggle')?.addEventListener('click',()=>{
        const p=$('premiumGalleryPicker');if(!p)return;
        p.hidden=!p.hidden;
        renderPremiumGalleryManager();
    });

    async function fileSha256(file){
        if(!file||!window.crypto?.subtle)return null;
        try{
            const buffer=await file.arrayBuffer();
            const digest=await crypto.subtle.digest('SHA-256',buffer);
            return Array.from(new Uint8Array(digest))
                .map(b=>b.toString(16).padStart(2,'0'))
                .join('');
        }catch(_){return null;}
    }

    function existingAssetForFile(file,sha256=null){
        if(sha256){
            const byHash=assets.find(a=>a.sha256&&String(a.sha256)===String(sha256));
            if(byHash)return byHash;
        }

        return assets.find(a=>
            String(a.name||'')===String(file.name||'')
            && Number(a.size||0)>0
            && Number(a.size||0)===Number(file.size||0)
        )||null;
    }

    async function uploadAssetFile(file){
        if(!file)return;

        const allowed=['image/png','image/jpeg','image/webp','image/gif','video/mp4','video/webm'];
        if(!allowed.includes(file.type)){notify('Format file belum didukung');return;}

        const progress=$('assetUploadProgress'),bar=progress?.querySelector('i');
        if(progress)progress.style.display='block';
        if(bar)bar.style.width='12%';

        try{
            const isVideo=file.type.startsWith('video/');
            // Avoid reading a 20–25 MB video fully into memory before upload.
            // Video duplicate fallback still uses exact name + byte size locally;
            // server-side upload keeps its own hash validation.
            const sha256=isVideo?null:await fileSha256(file);
            if(bar)bar.style.width='25%';

            const localExisting=existingAssetForFile(file,sha256);
            if(localExisting){
                interactionDiag('ASSET_UPLOAD_DEDUPED_CLIENT',{assetId:localExisting.id,name:file.name,size:file.size});
                await addAsset(localExisting);
                $('assetFile').value='';
                if(bar)bar.style.width='100%';
                notify('File sudah ada — memakai unggahan yang sama');
                return;
            }

            const fd=new FormData();
            fd.append('file',file);

            const r=await fetch(uploadAssetUrl,{
                method:'POST',
                headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                body:fd
            });

            if(bar)bar.style.width='82%';
            const j=await r.json();
            if(!r.ok)throw new Error(j.message||'Upload gagal');

            j.asset=Object.assign({},j.asset,{
                size:Number(j.asset?.size||file.size||0),
                sha256:j.asset?.sha256||sha256||null,
                stream_url:(j.asset?.type==='video'&&isInstanceMode)
                    ? `${String(uploadAssetUrl).replace(/\/$/,'')}/${encodeURIComponent(j.asset.id)}/stream`
                    : (j.asset?.stream_url||null)
            });

            const existingIndex=assets.findIndex(a=>String(a.id)===String(j.asset.id));
            if(existingIndex>=0){
                assets[existingIndex]=Object.assign({},assets[existingIndex],j.asset);
            }else{
                assets.unshift(j.asset);
            }

            renderAssets();
            await addAsset(j.asset);
            $('assetFile').value='';

            if(bar)bar.style.width='100%';

            if(j.duplicate){
                interactionDiag('ASSET_UPLOAD_DEDUPED_SERVER',{assetId:j.asset.id,name:file.name,size:file.size});
                notify('File sudah ada — tidak diduplikat di Unggahan');
            }else{
                interactionDiag('ASSET_UPLOAD_NEW',{assetId:j.asset.id,name:file.name,size:file.size});
                notify('Media berhasil diupload');
            }
        }catch(e){
            notify(e.message);
        }finally{
            setTimeout(()=>{
                if(progress)progress.style.display='none';
                if(bar)bar.style.width='0';
            },450);
        }
    }
    $('assetUploadBtn').onclick=()=>uploadAssetFile($('assetFile').files[0]);
    const dz=$('assetDropzone'),af=$('assetFile');dz?.addEventListener('click',()=>af.click());dz?.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();af.click();}});af?.addEventListener('change',()=>uploadAssetFile(af.files[0]));
    ['dragenter','dragover'].forEach(ev=>dz?.addEventListener(ev,e=>{e.preventDefault();dz.classList.add('dragover');}));['dragleave','drop'].forEach(ev=>dz?.addEventListener(ev,e=>{e.preventDefault();dz.classList.remove('dragover');}));dz?.addEventListener('drop',e=>uploadAssetFile(e.dataTransfer.files?.[0]));


    function coverObjectPosition(c){
        const x=clamp(50+Number(c.posX||0),0,100);
        const y=clamp(50+Number(c.posY||0),0,100);
        return `${x}% ${y}%`;
    }
    function applyCoverMediaStyle(media,c){
        media.style.objectFit=c.fit||'cover';
        media.style.objectPosition=coverObjectPosition(c);
        media.style.transform=`scale(${clamp(Number(c.scale||1),1,3)})`;
        media.style.transformOrigin=coverObjectPosition(c);
    }

    function desktopCoverState(){
        if(!state.desktopCover)state.desktopCover={image:'',mediaType:'image',fit:'cover',scale:1,posX:0,posY:0,background:'#0f172a'};
        return state.desktopCover;
    }
    function renderDesktopCoverPanel(){
        const c=desktopCoverState(),host=$('desktopCoverPreview');
        if(!host)return;
        host.innerHTML='';host.style.background=c.background||'#0f172a';
        if(c.image){
            const isVideo=c.mediaType==='video'||/\.mp4(?:\?|$)/i.test(c.image);
            const media=document.createElement(isVideo?'video':'img');
            media.src=c.image;applyCoverMediaStyle(media,c);
            if(isVideo){media.muted=true;media.defaultMuted=true;media.autoplay=true;media.loop=true;media.playsInline=true;media.setAttribute('muted','');media.setAttribute('playsinline','');}
            else media.alt='Sampul desktop';
            media.onerror=()=>{host.innerHTML='<div class="us-desktop-cover-empty">Sampul gagal dimuat</div>';};
            host.appendChild(media);
        }else host.innerHTML='<div class="us-desktop-cover-empty">Belum ada sampul desktop</div>';
        $('desktopCoverFit').value=c.fit||'cover';
    }
    async function uploadDesktopCoverFile(file){
        if(!file)return;
        if(!['image/png','image/jpeg','video/mp4'].includes(file.type)){notify('Sampul desktop harus JPG, PNG, atau MP4');return;}
        const progress=$('desktopCoverUploadProgress'),bar=progress?.querySelector('i');if(progress)progress.style.display='block';if(bar)bar.style.width='25%';
        const fd=new FormData();fd.append('file',file);
        try{
            const r=await fetch(uploadAssetUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:fd});if(bar)bar.style.width='80%';
            const j=await r.json();if(!r.ok)throw new Error(j.message||'Upload sampul gagal');
            assets.unshift(j.asset);pushHistory();Object.assign(desktopCoverState(),{image:j.asset.url,assetId:j.asset.id??null,mediaType:file.type==='video/mp4'?'video':'image',fit:'cover',scale:1,posX:0,posY:0});
            markDirty();renderAssets();renderDesktopCoverPanel();if(bar)bar.style.width='100%';notify('Sampul desktop diperbarui');
        }catch(e){notify(e.message)}
        finally{setTimeout(()=>{if(progress)progress.style.display='none';if(bar)bar.style.width='0';},450);}
    }

    function bindOpeningCoverControl(id,key,cast=v=>v){
        const el=$(id);if(!el)return;const event=(el.type==='checkbox'||el.tagName==='SELECT')?'change':'input';
        el.addEventListener(event,e=>{const c=openingCoverState();c[key]=el.type==='checkbox'?el.checked:cast(e.target.value);markDirty();syncOpeningCoverControls();});
    }
    bindOpeningCoverControl('openingCoverEnabled','enabled',Boolean);
    bindOpeningCoverControl('openingCoverEyebrow','eyebrow',String);
    bindOpeningCoverControl('openingCoverNames','names',String);
    bindOpeningCoverControl('openingCoverBindNames','bindNames',Boolean);
    bindOpeningCoverControl('openingCoverButtonText','buttonText',String);
    bindOpeningCoverControl('openingCoverTextColor','textColor',String);
    bindOpeningCoverControl('openingCoverButtonColor','buttonColor',String);
    bindOpeningCoverControl('openingCoverNameSize','nameSize',Number);
    bindOpeningCoverControl('openingCoverY','contentY',Number);
    bindOpeningCoverControl('openingCoverAnimation','animation',String);
    bindOpeningCoverControl('openingCoverDuration','duration',Number);
    bindOpeningCoverControl('openingCoverExitAnimation','exitAnimation',String);
    bindOpeningCoverControl('openingCoverExitDuration','exitDuration',Number);
    $('centerOpeningCover')?.addEventListener('click',()=>{
        const c=openingCoverState();
        c.contentX=50;
        c.contentY=50;
        markDirty();syncOpeningCoverControls();notify('Teks Opening Cover diposisikan ke tengah');
    });
    document.querySelectorAll('[data-opening-media-device]').forEach(btn=>btn.addEventListener('click',()=>{
        openingMediaDevice=btn.dataset.openingMediaDevice||'mobile';
        syncOpeningCoverControls();
    }));
    [['openingMediaFit','fit',String],['openingMediaScale','scale',Number],['openingMediaX','x',Number],['openingMediaY','y',Number]].forEach(([id,key,cast])=>{
        const el=$(id);if(!el)return;
        const evt=el.tagName==='SELECT'?'change':'input';
        el.addEventListener(evt,e=>{const m=openingMediaSettings();m[key]=cast(e.target.value);markDirty();renderOpeningMediaPreview();});
    });
    $('resetOpeningMedia')?.addEventListener('click',()=>{
        const m=openingMediaSettings();m.fit='cover';m.scale=1;m.x=50;m.y=50;markDirty();syncOpeningCoverControls();notify('Media '+openingMediaDevice+' kembali ke Auto / Tengah');
    });
    $('previewOpeningCover')?.addEventListener('click',previewOpeningCoverStandalone);
    document.querySelectorAll('[data-cover-mode]').forEach(btn=>btn.addEventListener('click',()=>{
        const mode=btn.dataset.coverMode||'opening';
        document.querySelectorAll('[data-cover-mode]').forEach(x=>x.classList.toggle('active',x===btn));
        document.querySelectorAll('[data-cover-card]').forEach(card=>card.hidden=card.dataset.coverCard!==mode);
        if(mode==='opening')syncOpeningCoverControls();
        if(mode==='sticky')renderDesktopCoverPanel();
    }));

    
    async function uploadOpeningCoverFile(file){
        if(!file)return;
        const fd=new FormData();fd.append('file',file);
        try{
            const r=await fetch(uploadAssetUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:fd});
            const j=await r.json();if(!r.ok)throw new Error(j.message||'Upload gagal');
            openingCoverState().image=j.asset.url;
            openingCoverState().assetId=j.asset.id??null;
            openingCoverState().mediaType=j.asset.type;
            markDirty();
            syncOpeningCoverControls();
            notify('Media Opening Cover diperbarui');
        }catch(e){notify(e.message)}
    }
    $('uploadOpeningCover')?.addEventListener('click',()=>$('openingCoverFile')?.click());
    $('openingCoverFile')?.addEventListener('change',()=>uploadOpeningCoverFile($('openingCoverFile').files?.[0]));

    const coverPointers=new Map();
    let coverAdjustGesture=null;
    function coverPreviewPoint(e){
        const host=$('desktopCoverPreview'),r=host?.getBoundingClientRect();
        return r?{x:e.clientX,y:e.clientY,w:r.width,h:r.height}:null;
    }
    function beginCoverAdjust(e){
        const host=$('desktopCoverPreview');
        if(!host||!desktopCoverState().image)return;
        if(e.target.closest('button,select,input'))return;
        host.setPointerCapture?.(e.pointerId);
        coverPointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
        const c=desktopCoverState();
        if(coverPointers.size===1){
            pushHistory();
            coverAdjustGesture={kind:'drag',startX:e.clientX,startY:e.clientY,posX:Number(c.posX||0),posY:Number(c.posY||0)};
        }else if(coverPointers.size===2){
            const pts=[...coverPointers.values()],dist=Math.hypot(pts[1].x-pts[0].x,pts[1].y-pts[0].y);
            coverAdjustGesture={kind:'pinch',startDist:dist,startScale:Number(c.scale||1)};
        }
        host.classList.add('is-adjusting');e.preventDefault();
    }
    function moveCoverAdjust(e){
        if(!coverPointers.has(e.pointerId)||!coverAdjustGesture)return;
        coverPointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
        const c=desktopCoverState(),host=$('desktopCoverPreview'),r=host?.getBoundingClientRect();
        if(!r)return;
        if(coverPointers.size>=2){
            const pts=[...coverPointers.values()],dist=Math.hypot(pts[1].x-pts[0].x,pts[1].y-pts[0].y);
            if(coverAdjustGesture.kind!=='pinch')coverAdjustGesture={kind:'pinch',startDist:dist,startScale:Number(c.scale||1)};
            c.scale=clamp(coverAdjustGesture.startScale*(dist/Math.max(1,coverAdjustGesture.startDist)),1,3);
        }else if(coverAdjustGesture.kind==='drag'){
            const dx=((e.clientX-coverAdjustGesture.startX)/Math.max(1,r.width))*100;
            const dy=((e.clientY-coverAdjustGesture.startY)/Math.max(1,r.height))*100;
            const limit=c.fit==='contain'?0:50;
            // object-position moves the crop anchor, so invert pointer delta
            // so the visible photo follows the user's mouse/finger naturally.
            c.posX=clamp(coverAdjustGesture.posX-dx,-limit,limit);
            c.posY=clamp(coverAdjustGesture.posY-dy,-limit,limit);
        }
        dirty=true;setSaveStatus('Belum disimpan','');renderDesktopCoverPanel();e.preventDefault();
    }
    function endCoverAdjust(e){
        coverPointers.delete(e.pointerId);
        if(coverPointers.size===0){
            coverAdjustGesture=null;$('desktopCoverPreview')?.classList.remove('is-adjusting');markDirty();
        }else if(coverPointers.size===1){
            const p=[...coverPointers.values()][0],c=desktopCoverState();
            coverAdjustGesture={kind:'drag',startX:p.x,startY:p.y,posX:Number(c.posX||0),posY:Number(c.posY||0)};
        }
    }
    $('desktopCoverPreview')?.addEventListener('pointerdown',beginCoverAdjust);
    $('desktopCoverPreview')?.addEventListener('pointermove',moveCoverAdjust);
    $('desktopCoverPreview')?.addEventListener('pointerup',endCoverAdjust);
    $('desktopCoverPreview')?.addEventListener('pointercancel',endCoverAdjust);
    $('desktopCoverPreview')?.addEventListener('wheel',e=>{
        if(!desktopCoverState().image)return;
        e.preventDefault();pushHistory();
        const c=desktopCoverState();c.scale=clamp(Number(c.scale||1)+(e.deltaY<0?.08:-.08),1,3);
        markDirty();renderDesktopCoverPanel();
    },{passive:false});

        const desktopCoverFile=$('desktopCoverFile'),desktopCoverDropzone=$('desktopCoverDropzone');
    desktopCoverDropzone?.addEventListener('click',()=>desktopCoverFile.click());
    desktopCoverDropzone?.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();desktopCoverFile.click();}});
    desktopCoverFile?.addEventListener('change',()=>uploadDesktopCoverFile(desktopCoverFile.files?.[0]));
    ['dragenter','dragover'].forEach(ev=>desktopCoverDropzone?.addEventListener(ev,e=>{e.preventDefault();desktopCoverDropzone.classList.add('dragover');}));
    ['dragleave','drop'].forEach(ev=>desktopCoverDropzone?.addEventListener(ev,e=>{e.preventDefault();desktopCoverDropzone.classList.remove('dragover');}));
    desktopCoverDropzone?.addEventListener('drop',e=>uploadDesktopCoverFile(e.dataTransfer.files?.[0]));
    function bindDesktopCoverControl(id,key,cast=v=>v){
        const el=$(id);if(!el)return;
        el.addEventListener('input',e=>{desktopCoverState()[key]=cast(e.target.value);markDirty();renderDesktopCoverPanel();});
    }
    bindDesktopCoverControl('desktopCoverFit','fit',String);
    $('desktopCoverFit')?.addEventListener('change',()=>{
        const c=desktopCoverState();
        if(c.fit==='contain'){c.posX=0;c.posY=0;c.scale=1;}
        markDirty();renderDesktopCoverPanel();
    });
    $('resetDesktopCover')?.addEventListener('click',()=>{pushHistory();Object.assign(desktopCoverState(),{fit:'cover',scale:1,posX:0,posY:0});markDirty();renderDesktopCoverPanel();});
    $('removeDesktopCover')?.addEventListener('click',()=>{pushHistory();desktopCoverState().image='';markDirty();renderDesktopCoverPanel();notify('Foto sampul desktop dikosongkan');});

async function uploadFontFile(file){
        if(!file)return;
        const ext=(file.name.split('.').pop()||'').toLowerCase();
        if(!['woff2','woff','ttf','otf'].includes(ext)){notify('Format font harus WOFF2, WOFF, TTF, atau OTF');return;}
        const inputName=$('fontName');if(!inputName.value.trim())inputName.value=file.name.replace(/\.[^.]+$/,'').replace(/[-_]+/g,' ');
        const name=inputName.value.trim();if(!name){notify('Nama font belum ada');return;}
        const progress=$('fontUploadProgress'),bar=progress?.querySelector('i');if(progress)progress.style.display='block';if(bar)bar.style.width='25%';
        const fd=new FormData();fd.append('file',file);fd.append('name',name);
        try{
            const r=await fetch(uploadFontUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:fd});
            if(bar)bar.style.width='80%';
            const j=await r.json();if(!r.ok)throw new Error(j.message||Object.values(j.errors||{})[0]?.[0]||'Upload font gagal');
            fonts.push(j.font);
            const st=document.createElement('style');st.dataset.studioFont=String(j.font.id);st.textContent=`@font-face{font-family:'${j.font.family}';src:url('${j.font.url}');font-weight:${j.font.weight};font-style:${j.font.style};font-display:swap}`;document.head.appendChild(st);
            if(bar)bar.style.width='100%';renderFonts();$('fontFile').value='';$('fontName').value='';
            notify('Font berhasil ditambahkan');
        }catch(e){notify(e.message)}
        finally{setTimeout(()=>{if(progress)progress.style.display='none';if(bar)bar.style.width='0';},450);}
    }
    $('fontUploadBtn').onclick=()=>uploadFontFile($('fontFile').files[0]);
    const fontDz=$('fontDropzone'),fontInput=$('fontFile');
    fontDz?.addEventListener('click',()=>fontInput.click());
    fontDz?.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();fontInput.click();}});
    fontInput?.addEventListener('change',()=>uploadFontFile(fontInput.files?.[0]));
    ['dragenter','dragover'].forEach(ev=>fontDz?.addEventListener(ev,e=>{e.preventDefault();fontDz.classList.add('dragover');}));
    ['dragleave','drop'].forEach(ev=>fontDz?.addEventListener(ev,e=>{e.preventDefault();fontDz.classList.remove('dragover');}));
    fontDz?.addEventListener('drop',e=>uploadFontFile(e.dataTransfer.files?.[0]));

    async function saveTemplate({silent=false}={}){
        const requiredName=$('tplName')?.value.trim(),requiredSlug=$('tplSlug')?.value.trim();
        if(!isInstanceMode&&!requiredName){setSaveStatus('Nama template wajib diisi','error');if(!silent)notify('Nama Template wajib diisi');return;}
        if(!isInstanceMode&&!requiredSlug){setSaveStatus('Slug template wajib diisi','error');if(!silent)notify('Slug Template wajib diisi');return;}
        if(saveInFlight){pendingSave=true;return;}
        if(!dirty&&silent)return;
        const savingRevision=canvasRevision;
        const body=isInstanceMode
            ? {canvas:state}
            : {name:$('tplName').value.trim(),slug:$('tplSlug').value.trim(),min_plan:$('tplPlan').value,is_customer_editable:$('tplEditable').checked,canvas:state};
        saveInFlight=true;$('saveBtn').disabled=true;setSaveStatus('Menyimpan...','saving');
        try{
            const r=await fetch(updateUrl,{method:'PUT',headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(body)});
            const j=await r.json();if(!r.ok)throw new Error(j.message||Object.values(j.errors||{})[0]?.[0]||'Save gagal');
            dirty=canvasRevision!==savingRevision;
            setSaveStatus(dirty?'Belum disimpan':'Tersimpan',dirty?'':'saved');
            if(dirty)pendingSave=true;
            if(!silent)notify(isInstanceMode?'Desain undangan tersimpan':'Template tersimpan');
        }catch(e){dirty=true;setSaveStatus('Gagal menyimpan','error');if(!silent)notify(e.message);}
        finally{saveInFlight=false;$('saveBtn').disabled=false;if(pendingSave){pendingSave=false;setTimeout(()=>saveTemplate({silent:true}),350);}}
    }
    $('saveBtn').onclick=()=>saveTemplate({silent:false});

    function resetNudgeHistory(){clearTimeout(nudgeHistoryTimer);nudgeHistoryTimer=null;nudgeHistoryKey=null;}
    function nudgeSelectedLayers(key,step){
        const all=selectedLayers();if(!all.length)return false;
        if(all.some(l=>l.locked)){notify('Selection berisi elemen terkunci');return false;}
        const list=all,b={left:Math.min(...list.map(l=>l.x)),top:Math.min(...list.map(l=>l.y)),right:Math.max(...list.map(l=>l.x+l.width)),bottom:Math.max(...list.map(l=>l.y+l.height))};
        let dx=key==='ArrowLeft'?-step:key==='ArrowRight'?step:0,dy=key==='ArrowUp'?-step:key==='ArrowDown'?step:0;
        if(keepInsideCanvas||respectSafeArea){const limit=respectSafeArea?currentSafeBounds():pageBounds();dx=clamp(dx,limit.left-b.left,limit.right-b.right);dy=clamp(dy,limit.top-b.top,limit.bottom-b.bottom);}
        if(!dx&&!dy)return false;
        const signature=[...selectedIds].sort().join('|');
        if(nudgeHistoryKey!==signature){pushHistory();nudgeHistoryKey=signature;}
        clearTimeout(nudgeHistoryTimer);nudgeHistoryTimer=setTimeout(resetNudgeHistory,350);
        list.forEach(l=>{l.x=Math.round(l.x+dx);l.y=Math.round(l.y+dy);});markDirty();render();return true;
    }
    function handleEditorEscape(){
        if(document.querySelector('.us-delete-guard-backdrop')?.offsetParent||$('responsivePreview')?.classList.contains('open'))return false;
        if($('elementContextMenu')?.classList.contains('open')){hideElementContextMenu();return true;}
        if(cellMediaContextMenu?.classList.contains('open')){closeCellMediaContextMenu();return true;}
        if($('pageContextMenu')?.classList.contains('open')){hidePageMenu();return true;}
        if($('transitionPopover')?.classList.contains('open')){hideTransitionPopover();return true;}
        if(mobileContextSheet?.classList.contains('open')){closeMobileContextSheet();return true;}
        if(cropMode){finishCropInteraction({renderNow:true,reason:'escape',deselect:false});return true;}
        if(selectedIds.size){clearSelection();activeCellIndex=0;render();return true;}
        return false;
    }

    window.addEventListener('keydown',e=>{
        const tag=document.activeElement?.tagName,typing=['INPUT','TEXTAREA','SELECT'].includes(tag)||document.activeElement?.isContentEditable,mod=e.ctrlKey||e.metaKey;
        const nudgeKey=['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(e.key);
        if(!nudgeKey)resetNudgeHistory();
        if(mod&&e.key.toLowerCase()==='s'){e.preventDefault();saveTemplate({silent:false});return;}
        if(mod&&e.key.toLowerCase()==='z'&&!typing){e.preventDefault();if(e.shiftKey){if(future.length){history.push(snapshot());restore(future.pop());markDirty();render();}}else{if(history.length){future.push(snapshot());restore(history.pop());markDirty();render();}}return;}
        if(mod&&e.key.toLowerCase()==='y'&&!typing){e.preventDefault();if(future.length){history.push(snapshot());restore(future.pop());markDirty();render();}return;}
        if(e.key==='Escape'&&!typing){if(handleEditorEscape()){e.preventDefault();e.stopPropagation();}return;}
        if(typing)return;const hasLayers=selectedIds.size>0;
        if(mod&&e.key.toLowerCase()==='c'){e.preventDefault();if(hasLayers)copySelectedLayers();else copyActivePage();return;}
        if(mod&&e.key.toLowerCase()==='v'){e.preventDefault();if(layerClipboard.length)pasteSelectedLayers();else pastePage();return;}
        if(mod&&e.key.toLowerCase()==='d'){e.preventDefault();if(hasLayers)duplicateSelectedLayers();else duplicateActivePage();return;}
        if(mod&&e.key.toLowerCase()==='g'){e.preventDefault();if(e.shiftKey)ungroupSelectedLayers();else groupSelectedLayers();return;}
        if(mod&&e.key==='Enter'){e.preventDefault();addNewPage();return;}
        if(e.key==='Delete'||e.key==='Backspace'){
            e.preventDefault();
            if(hasLayers)requestDeleteSelectedLayers();else deleteActivePage();return;
        }
        if(nudgeKey&&nudgeSelectedLayers(e.key,e.shiftKey?10:1))e.preventDefault();
    });

    livePages.addEventListener('contextmenu',e=>{if(!activateCanvasFromEvent(e,{clearOnPageChange:false}))return;e.preventDefault();clearSelection();render();showPageMenu(e.clientX,e.clientY,activePageId);});
    document.addEventListener('click',e=>{if(!e.target.closest('#pageContextMenu')&&!e.target.closest('[data-page-more]'))hidePageMenu();});
    window.addEventListener('resize',hidePageMenu);
    $('pageContextMenu').addEventListener('click',e=>{
        const btn=e.target.closest('[data-page-action]');if(!btn)return;
        const action=btn.dataset.pageAction;hidePageMenu();
        if(action==='copy')copyActivePage();
        if(action==='paste')pastePage();
        if(action==='duplicate')duplicateActivePage();
        if(action==='add')addNewPage();if(action==='add-desktop-cover'){const existing=state.pages.find(p=>p.role==='desktop-cover');if(existing){setActivePage(existing.id,false);notify('Desktop Cover sudah ada');}else{pushHistory();const p=normalizePage({name:'Desktop Cover',role:'desktop-cover',width:1200,height:844},state.pages.length);state.pages.push(p);activePageId=p.id;clearSelection();markDirty();render();}}
        if(action==='rename')renameActivePage();
        if(action==='delete')deleteActivePage();
    });
    $('pageGridBtn')?.addEventListener('click',openPageGrid);
    $('pageGridClose')?.addEventListener('click',closePageGrid);
    $('pageGridOverlay')?.addEventListener('click',e=>{if(e.target===$('pageGridOverlay'))closePageGrid();});
    $('pageGrid')?.addEventListener('click',e=>{const card=e.target.closest('.us-page-grid-card');if(!card)return;setActivePage(card.dataset.id,false);closePageGrid();});



    function previewLayerAnimationCss(l){
        if(!l||!l.animation||l.animation==='none')return '';
        const map={
            'fade':'usFade','fade-up':'usFadeUp','fade-down':'usFadeDown','fade-left':'usFadeLeft','fade-right':'usFadeRight',
            'zoom':'usZoom','pop':'usPop','soft-scale':'usSoftScale','blur-in':'usBlurIn','reveal-up':'usRevealUp','float':'usFloat'
        };
        const name=map[l.animation];if(!name)return '';
        return `${name} ${Number(l.duration||.8)}s ${l.animationEasing||'ease-out'} ${Number(l.delay||0)}s ${l.loop?'infinite':'1'} both`;
    }
    function activateStaticLayerAnimations(root){
        if(!root)return;
        root.querySelectorAll('.us-static-layer.us-preview-animated').forEach(el=>{
            const css=el.dataset.previewAnimation||'';
            if(!css){el.style.visibility='visible';return;}
            el.classList.remove('is-running');el.style.animation='none';void el.offsetWidth;
            el.classList.add('is-running');el.style.animation=css;
        });
    }

    function staticCellImage(cell,layer=null){
        if(!cell?.src)return '';
        const cropX=Number.isFinite(Number(cell.cropX))?Number(cell.cropX):'';
        const cropY=Number.isFinite(Number(cell.cropY))?Number(cell.cropY):'';
        const effects=normalizeMediaEffects(layer?.effects);
        return `<img data-static-cell-media="1" data-scale="${Math.max(1,Number(cell.scale||1))}" data-pos-x="${Number(cell.posX||0)}" data-pos-y="${Number(cell.posY||0)}" data-crop-x="${cropX}" data-crop-y="${cropY}" data-flip-x="${layer?.flipX?'1':'0'}" data-flip-y="${layer?.flipY?'1':'0'}" data-brightness="${effects.brightness}" data-contrast="${effects.contrast}" data-saturation="${effects.saturation}" data-grayscale="${effects.grayscale}" src="${esc(cell.src)}" alt="" decoding="async" style="position:absolute;left:50%;top:50%;width:calc(100% + 4px);height:calc(100% + 4px);max-width:none;max-height:none;object-fit:cover;transform:translate(-50%,-50%)">`;
    }
    function applyStaticCellCrop(img){
        const cell=img?.closest?.('.us-media-cell');if(!cell)return;
        const sync=()=>{
            const cellW=Math.max(1,Number(cell.clientWidth||1));
            const cellH=Math.max(1,Number(cell.clientHeight||1));
            const naturalW=Math.max(1,Number(img.naturalWidth||1));
            const naturalH=Math.max(1,Number(img.naturalHeight||1));
            const mediaBleed=2;
            const coverScale=Math.max(
                (cellW+(mediaBleed*2))/naturalW,
                (cellH+(mediaBleed*2))/naturalH
            );
            const zoom=clamp(Number(img.dataset.scale||1),1,4);
            const fullW=naturalW*coverScale*zoom;
            const fullH=naturalH*coverScale*zoom;
            const maxX=Math.max(0,(fullW-cellW)/2);
            const maxY=Math.max(0,(fullH-cellH)/2);
            const rawCropX=img.dataset.cropX;
            const rawCropY=img.dataset.cropY;
            const legacyX=maxX>0?clamp(Number(img.dataset.posX||0)/maxX,-1,1):0;
            const legacyY=maxY>0?clamp(Number(img.dataset.posY||0)/maxY,-1,1):0;
            const cropX=rawCropX!==''?clamp(Number(rawCropX),-1,1):legacyX;
            const cropY=rawCropY!==''?clamp(Number(rawCropY),-1,1):legacyY;
            img.style.position='absolute';img.style.left='50%';img.style.top='50%';
            img.style.width=fullW+'px';img.style.height=fullH+'px';
            img.style.minWidth='0';img.style.minHeight='0';img.style.maxWidth='none';img.style.maxHeight='none';
            img.style.objectFit='fill';
            const flipX=img.dataset.flipX==='1'?-1:1,flipY=img.dataset.flipY==='1'?-1:1;
            img.style.transform=`translate(calc(-50% + ${cropX*maxX}px),calc(-50% + ${cropY*maxY}px)) scaleX(${flipX}) scaleY(${flipY})`;
            img.style.filter=`brightness(${Number(img.dataset.brightness||100)}%) contrast(${Number(img.dataset.contrast||100)}%) saturate(${Number(img.dataset.saturation||100)}%) grayscale(${Number(img.dataset.grayscale||0)}%)`;
            img.style.transformOrigin='center center';
        };
        if(img.complete&&img.naturalWidth>0)sync();
        else img.addEventListener('load',sync,{once:true});
        if(window.ResizeObserver){const observer=new ResizeObserver(sync);observer.observe(cell);}
    }
    function hydrateStaticCellMedia(root){
        root?.querySelectorAll?.('[data-static-cell-media]').forEach(applyStaticCellCrop);
    }
    const responsivePreviewVideoState=new Map();

    function previewVideoSourceForLayer(l){
        // Prefer the editor Range endpoint for customer instances.
        // Fall back to the original source for Admin/sample mode.
        return editorVideoSourceForLayer(l)||String(l?.src||'');
    }

    function previewVideoMarkup(l){
        const src=previewVideoSourceForLayer(l);
        const poster=editorVideoPoster(src);
        const id=String(l?.id||'');

        return `<video
            class="us-video us-responsive-preview-video"
            data-preview-video-layer="${esc(id)}"
            src="${esc(src)}"
            ${poster?`poster="${poster}"`:''}
            preload="metadata"
            autoplay
            muted
            loop
            playsinline
            controls
            controlslist="nodownload"
        ></video>`;
    }

    function captureResponsivePreviewVideoState(){
        $('responsivePreviewStage')?.querySelectorAll('video[data-preview-video-layer]').forEach(video=>{
            const id=String(video.dataset.previewVideoLayer||'');
            if(!id)return;
            responsivePreviewVideoState.set(id,{
                currentTime:Number.isFinite(video.currentTime)?video.currentTime:0,
                paused:video.paused,
                volume:video.volume,
                muted:video.muted
            });
        });
    }

    function hydrateResponsivePreviewVideos(root){
        root?.querySelectorAll('video[data-preview-video-layer]').forEach(video=>{
            if(video.dataset.previewPlaybackBound==='1')return;
            video.dataset.previewPlaybackBound='1';

            const id=String(video.dataset.previewVideoLayer||'');
            const saved=responsivePreviewVideoState.get(id)||null;

            const restore=()=>{
                if(saved&&Number.isFinite(saved.currentTime)&&video.duration){
                    const maxTime=Math.max(0,video.duration-.05);
                    try{video.currentTime=Math.min(saved.currentTime,maxTime);}catch(_){}
                }

                video.muted=saved?.muted??true;
                video.volume=Number.isFinite(saved?.volume)?saved.volume:1;

                // Preview must autoplay every time it is opened or re-rendered.
                // We still preserve playback time, but we do not restore a paused state here.
                const playPromise=video.play();
                if(playPromise&&typeof playPromise.catch==='function'){
                    playPromise.catch(()=>{
                        // Browser can reject autoplay; native controls remain available.
                    });
                }
            };

            if(video.readyState>=1)restore();
            else video.addEventListener('loadedmetadata',restore,{once:true});

            video.addEventListener('play',()=>{
                const state=responsivePreviewVideoState.get(id)||{};
                state.paused=false;
                responsivePreviewVideoState.set(id,state);
            });

            video.addEventListener('pause',()=>{
                const state=responsivePreviewVideoState.get(id)||{};
                state.paused=true;
                state.currentTime=Number.isFinite(video.currentTime)?video.currentTime:0;
                responsivePreviewVideoState.set(id,state);
            });

            video.addEventListener('timeupdate',()=>{
                const state=responsivePreviewVideoState.get(id)||{};
                state.currentTime=Number.isFinite(video.currentTime)?video.currentTime:0;
                state.paused=video.paused;
                responsivePreviewVideoState.set(id,state);
            });

            video.addEventListener('error',()=>{
                interactionDiag('RESPONSIVE_PREVIEW_VIDEO_ERROR',{
                    layerId:id,
                    src:video.currentSrc||video.src||''
                });
            },{once:true});
        });
    }

    function buildStaticPage(p){
        const pw=Number(p.width||390),ph=Number(p.height||844);
        const host=document.createElement('div');host.className='us-preview-page-host';host.style.width=pw+'px';host.style.height=ph+'px';host.style.background=p.background||'#fff';
        [...(p.layers||[])].sort((a,b)=>(a.zIndex||0)-(b.zIndex||0)).forEach(l=>{
            if(l.hidden||shouldHideLayerForMode(l))return;const el=document.createElement('div');el.className='us-static-layer';const previewAnim=previewLayerAnimationCss(l);if(previewAnim){el.classList.add('us-preview-animated');el.dataset.previewAnimation=previewAnim;}el.style.left=l.x+'px';el.style.top=l.y+'px';el.style.width=l.width+'px';el.style.height=l.height+'px';el.style.opacity=l.opacity;el.style.zIndex=l.zIndex;el.style.transform=`rotate(${l.rotation||0}deg)`;el.style.borderRadius=(l.borderRadius||0)+'px';
            if(l.type==='text'){
                el.classList.add('us-static-text');el.style.fontFamily=l.fontFamily||'Arial';el.style.fontSize=(l.fontSize||28)+'px';el.style.fontWeight=l.fontWeight||400;el.style.color=l.color||'#222';el.style.textAlign=l.textAlign||'left';el.style.justifyContent=l.textAlign==='center'?'center':(l.textAlign==='right'?'flex-end':'flex-start');el.style.background=l.background||'transparent';
                const value=effectiveText(l);el.textContent=value||((editorViewMode!=='sample'&&l.binding&&l.binding!=='none')?bindingPlaceholder(l):'');
            }
            else if(l.type==='image'){
                const override=effectiveMediaSrc(l),src=(editorViewMode!=='sample'&&l.binding&&l.binding!=='none')?override:l.src;
                el.innerHTML=src?`<img src="${esc(src)}" alt="">`:`<div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div>`;
                const media=el.querySelector('img');if(media){media.style.transform=mediaTransformCss(l,`scale(${Number(l.scale||1)})`);media.style.transformOrigin='center center';media.style.filter=mediaFilterCss(l);}
            }
            else if(l.type==='video')el.innerHTML=previewVideoMarkup(l);
            else if(l.type==='frame'){
                const copy=deep(l);ensureCells(copy,1);
                const override=effectiveMediaSrc(l);
                if(editorViewMode!=='sample'&&l.binding&&l.binding!=='none')copy.cells[0]=Object.assign({},copy.cells[0]||{posX:0,posY:0,scale:1},{src:override||null});
                const kind=copy.frameKind||'square',cls={circle:'us-frame-circle',rounded:'us-frame-rounded',arch:'us-frame-arch',heart:'us-frame-heart'}[kind]||'';
                el.innerHTML=`<div class="us-frame-inner ${cls}" style="width:100%;height:100%;${kind==='polaroid'?'padding:8px 8px 24px;background:#fff;box-sizing:border-box':''}"><div class="us-media-cell ${copy.cells[0]?.src?'':'empty'}" style="width:100%;height:100%">${copy.cells[0]?.src?staticCellImage(copy.cells[0],l):`<div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div>`}</div></div>`;
            }
            else if(l.type==='grid'){
                const copy=deep(l),kind=copy.gridKind||'4',count=cellCountForGrid(kind);ensureCells(copy,count);
                const override=effectiveMediaSrc(l);if(editorViewMode!=='sample'&&l.binding&&l.binding!=='none')copy.cells[0]=Object.assign({},copy.cells[0]||{posX:0,posY:0,scale:1},{src:override||null});
                const cls={'2h':'us-grid-2h','2v':'us-grid-2v','3':'us-grid-3','4':'us-grid-4','6':'us-grid-6','mosaic':'us-grid-mosaic'}[kind]||'us-grid-4';
                el.innerHTML=`<div class="us-grid-inner ${cls}" style="width:100%;height:100%;gap:${Number(copy.gap||0)}px;border-radius:${Number(copy.borderRadius||0)}px">${copy.cells.map((c,i)=>`<div class="us-media-cell ${c?.src?'':'empty'}">${c?.src?staticCellImage(c):(i===0&&l.binding&&l.binding!=='none'?`<div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div>`:'')}</div>`).join('')}</div>`;
            }
            else {el.style.background=l.background||'#BFD8FF';const k=l.shapeKind||'rect';if(k==='circle'||k==='oval')el.style.borderRadius='50%';if(k==='triangle')el.style.clipPath='polygon(50% 0,100% 100%,0 100%)';if(k==='star')el.style.clipPath='polygon(50% 0,61% 35%,98% 35%,68% 57%,79% 94%,50% 72%,21% 94%,32% 57%,2% 35%,39% 35%)';if(k==='heart')el.style.clipPath='polygon(50% 92%,7% 50%,7% 26%,20% 9%,36% 8%,50% 22%,64% 8%,80% 9%,93% 26%,93% 50%)';if(k==='hex')el.style.clipPath='polygon(25% 7%,75% 7%,100% 50%,75% 93%,25% 93%,0 50%)';}
            host.appendChild(el);
        });
        return host;
    }
    function buildResponsiveStaticPage(p,device){
        const d=deviceProfile(device),s=displayScaleFor(p,device);
        const host=document.createElement('div');host.className='us-preview-page-host';
        host.style.width=d.width+'px';host.style.height=d.height+'px';host.style.background=p.background||'#fff';

        [...(p.layers||[])].sort((a,b)=>(a.zIndex||0)-(b.zIndex||0)).forEach(l=>{
            if(l.hidden||shouldHideLayerForMode(l))return;
            const g=displayLayerGeometry(l,p,device);
            const el=document.createElement('div');el.className='us-static-layer';
            const previewAnim=previewLayerAnimationCss(l);if(previewAnim){el.classList.add('us-preview-animated');el.dataset.previewAnimation=previewAnim;}
            el.style.left=g.x+'px';el.style.top=g.y+'px';el.style.width=g.width+'px';el.style.height=g.height+'px';
            el.style.opacity=l.opacity;el.style.zIndex=l.zIndex;el.style.transform=`rotate(${l.rotation||0}deg)`;el.style.borderRadius=(Number(l.borderRadius||0)*s.fs)+'px';

            if(l.type==='text'){
                el.classList.add('us-static-text');el.style.fontFamily=l.fontFamily||'Arial';el.style.fontSize=g.fontSize+'px';el.style.fontWeight=l.fontWeight||400;el.style.color=l.color||'#222';el.style.textAlign=l.textAlign||'left';el.style.justifyContent=l.textAlign==='center'?'center':(l.textAlign==='right'?'flex-end':'flex-start');el.style.background=l.background||'transparent';
                const value=effectiveText(l);el.textContent=value||((editorViewMode!=='sample'&&l.binding&&l.binding!=='none')?bindingPlaceholder(l):'');
            }else if(l.type==='image'){
                const override=effectiveMediaSrc(l),src=(editorViewMode!=='sample'&&l.binding&&l.binding!=='none')?override:l.src;
                el.innerHTML=src?`<img src="${esc(src)}" alt="">`:`<div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div>`;
                const media=el.querySelector('img');if(media){media.style.transform=mediaTransformCss(l,`scale(${Number(l.scale||1)})`);media.style.transformOrigin='center center';media.style.filter=mediaFilterCss(l);}
            }else if(l.type==='video')el.innerHTML=previewVideoMarkup(l);
            else if(l.type==='frame'){
                ensureCells(l,1);
                const override=effectiveMediaSrc(l);

                /* UNDANGANTA_MEMPELAI_BOUND_FRAME_REPAIR_V1_1
                 * Superseded by V1.3 resolved-source architecture.
                 * Never copy binding URLs into l.cells[].src during render.
                 */

                const copy=deep(l);ensureCells(copy,1);
                if(editorViewMode!=='sample'&&l.binding&&l.binding!=='none')copy.cells[0]=Object.assign({},copy.cells[0]||{posX:0,posY:0,scale:1},{src:override||null});
                const kind=copy.frameKind||'square',cls={circle:'us-frame-circle',rounded:'us-frame-rounded',arch:'us-frame-arch',heart:'us-frame-heart'}[kind]||'';
                el.innerHTML=`<div class="us-frame-inner ${cls}" style="width:100%;height:100%"><div class="us-media-cell ${copy.cells[0]?.src?'':'empty'}" style="width:100%;height:100%">${copy.cells[0]?.src?staticCellImage(copy.cells[0],l):`<div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div>`}</div></div>`;
            }else if(l.type==='grid'){
                const copy=deep(l),kind=copy.gridKind||'4',count=cellCountForGrid(kind);ensureCells(copy,count);
                copy.cells=copy.cells.map(c=>Object.assign({},c,{src:effectiveCellSrc(c)}));
                const cls={'2h':'us-grid-2h','2v':'us-grid-2v','3':'us-grid-3','4':'us-grid-4','6':'us-grid-6','mosaic':'us-grid-mosaic'}[kind]||'us-grid-4';
                el.innerHTML=`<div class="us-grid-inner ${cls}" style="width:100%;height:100%;gap:${Number(copy.gap||0)*s.fs}px">${copy.cells.map(c=>`<div class="us-media-cell ${c?.src?'':'empty'}">${c?.src?staticCellImage(c):''}</div>`).join('')}</div>`;
            }else{
                el.style.background=l.background||'#BFD8FF';const k=l.shapeKind||'rect';
                if(k==='circle'||k==='oval')el.style.borderRadius='50%';
                if(k==='triangle')el.style.clipPath='polygon(50% 0,100% 100%,0 100%)';
                if(k==='star')el.style.clipPath='polygon(50% 0,61% 35%,98% 35%,68% 57%,79% 94%,50% 72%,21% 94%,32% 57%,2% 35%,39% 35%)';
                if(k==='heart')el.style.clipPath='polygon(50% 92%,7% 50%,7% 26%,20% 9%,36% 8%,50% 22%,64% 8%,80% 9%,93% 26%,93% 50%)';
            }
            host.appendChild(el);
        });
        return host;
    }
    function scaledStaticPage(p,targetWidth,entryTransition=null,device=editorDeviceMode){
        const d=deviceProfile(device);
        const scale=Math.min(1,targetWidth/d.width);
        const wrap=document.createElement('div');wrap.className='us-preview-section';
        wrap.style.width=(d.width*scale)+'px';wrap.style.height=(d.height*scale)+'px';wrap.style.margin='0 auto';
        const host=buildResponsiveStaticPage(p,device);host.style.transform=`scale(${scale})`;host.style.transformOrigin='top left';wrap.appendChild(host);
        requestAnimationFrame(()=>hydrateStaticCellMedia(host));
        if(entryTransition)decoratePreviewTransition(wrap,entryTransition);
        return wrap;
    }

    function buildDedicatedDesktopCover(){
        const c=desktopCoverState(),media=document.createElement('div');media.className='us-preview-cover-media';media.style.background=c.background||'#0f172a';
        if(c.image){
            const isVideo=c.mediaType==='video'||/\.mp4(?:\?|$)/i.test(c.image);
            const item=document.createElement(isVideo?'video':'img');item.src=c.image;applyCoverMediaStyle(item,c);
            if(isVideo){item.muted=true;item.defaultMuted=true;item.autoplay=true;item.loop=true;item.playsInline=true;item.setAttribute('muted','');item.setAttribute('playsinline','');}
            else item.alt='Sampul desktop';
            media.appendChild(item);
        }else{
            const empty=document.createElement('div');empty.className='us-preview-cover-placeholder';empty.textContent='Sampul Desktop belum diisi';media.appendChild(empty);
        }
        return media;
    }
    const previewTransitionMap={fade:'pageFade','slide-up':'pageSlideUp','slide-down':'pageSlideDown','slide-left':'pageSlideLeft','slide-right':'pageSlideRight',zoom:'pageZoom',blur:'pageBlur'};
    function decoratePreviewTransition(section,transition){
        const t=Object.assign({type:'none',duration:.8,easing:'ease-in-out'},transition||{});
        section.dataset.transitionType=t.type||'none';section.dataset.transitionDuration=String(Number(t.duration||.8));section.dataset.transitionEasing=t.easing||'ease-in-out';
        if(t.type&&t.type!=='none')section.classList.add('us-transition-pending');
    }
    function activatePreviewTransitions(root){
        const sections=[...root.querySelectorAll('.us-preview-section')];
        const runSection=section=>{
            if(section.classList.contains('us-transition-pending')){
                const type=section.dataset.transitionType||'none',anim=previewTransitionMap[type];
                section.classList.remove('us-transition-pending');
                if(anim){
                    section.classList.add('us-transition-running');section.style.animation='none';void section.offsetWidth;
                    section.style.animation=`${anim} ${Number(section.dataset.transitionDuration||.8)}s ${section.dataset.transitionEasing||'ease-in-out'} both`;
                    section.addEventListener('animationend',()=>{section.classList.remove('us-transition-running');section.style.animation='';},{once:true});
                }
            }
            activateStaticLayerAnimations(section);
        };
        if(!('IntersectionObserver' in window)){sections.forEach(runSection);return;}
        const io=new IntersectionObserver(entries=>entries.forEach(entry=>{
            if(entry.isIntersecting){runSection(entry.target);io.unobserve(entry.target);}
        }),{root,threshold:.16,rootMargin:'0px 0px -6% 0px'});
        sections.forEach(section=>io.observe(section));
    }


    function openingCoverState(){
        if(!state.openingCover)state.openingCover={};
        const c=state.openingCover;
        c.enabled=c.enabled!==false;
        c.eyebrow=c.eyebrow??'The Wedding of';
        c.names=c.names??'Nama & Nama';
        c.bindNames=c.bindNames!==false;
        c.buttonText=c.buttonText??'Buka Undangan';
        c.textColor=c.textColor??'#ffffff';
        c.buttonColor=c.buttonColor??'#2F6FED';
        c.nameSize=Number(c.nameSize||46);
        c.contentX=Number(c.contentX??c.posX??50);
        c.contentY=Number(c.contentY??c.posY??50);
        c.animation=c.animation||'fade-up';
        c.duration=Number(c.duration||.8);
        c.exitAnimation=c.exitAnimation||'fade';
        c.exitDuration=Number(c.exitDuration||.55);
        c.mediaByDevice=c.mediaByDevice||{};
        ['mobile','tablet','desktop'].forEach(device=>{
            c.mediaByDevice[device]=Object.assign(
                {fit:c.fit||'cover',scale:1,x:50,y:50},
                c.mediaByDevice[device]||{}
            );
            c.mediaByDevice[device].scale=clamp(Number(c.mediaByDevice[device].scale||1),1,2.5);
            c.mediaByDevice[device].x=clamp(Number(c.mediaByDevice[device].x??50),0,100);
            c.mediaByDevice[device].y=clamp(Number(c.mediaByDevice[device].y??50),0,100);
        });
        return c;
    }
    function openingMediaSettings(device=openingMediaDevice){
        return openingCoverState().mediaByDevice[device]||openingCoverState().mediaByDevice.mobile;
    }
    function applyOpeningMediaStyle(media,c,device){
        const m=openingMediaSettings(device);
        media.style.objectFit=m.fit||'cover';
        media.style.objectPosition=`${m.x}% ${m.y}%`;
        media.style.transform=`scale(${m.scale||1})`;
        media.style.transformOrigin=`${m.x}% ${m.y}%`;
    }
    function openingCoverNames(){
        const c=openingCoverState();
        if(c.bindNames&&editorViewMode==='customer'){const v=customerTextValue('couple_names');if(v)return v;}
        if(editorViewMode==='empty'&&c.bindNames)return '';
        return c.names||'Nama & Nama';
    }
    function runOpeningExit(wrap,c,done){
        const type=c.exitAnimation||'fade',duration=Math.max(.15,Number(c.exitDuration||.55));
        if(type==='none'){done();return;}
        const map={fade:'usOpeningExitFade','slide-up':'usOpeningExitSlideUp','zoom-out':'usOpeningExitZoom','blur-out':'usOpeningExitBlur','curtain-up':'usOpeningExitCurtain'};
        const anim=map[type]||map.fade;
        wrap.classList.add('is-exiting');
        wrap.style.animation=`${anim} ${duration}s cubic-bezier(.2,.7,.2,1) both`;
        let finished=false;
        const finish=()=>{if(finished)return;finished=true;wrap.removeEventListener('animationend',finish);done();};
        wrap.addEventListener('animationend',finish,{once:true});
        setTimeout(finish,Math.ceil(duration*1000)+120);
    }
    function buildOpeningCover(device='desktop'){
        const c=openingCoverState();if(!c.enabled)return null;
        const wrap=document.createElement('div');wrap.className='us-opening-cover-preview';

        if(c.image){
            const isVideo=c.mediaType==='video'||/\.mp4(?:\?|$)/i.test(c.image);
            const media=document.createElement(isVideo?'video':'img');
            media.className='us-opening-cover-bg'+(isVideo?' video':'');
            media.src=c.image;
            applyOpeningMediaStyle(media,c,device);
            if(isVideo){
                media.muted=true;media.defaultMuted=true;media.autoplay=true;media.loop=true;media.playsInline=true;
                media.setAttribute('muted','');media.setAttribute('playsinline','');
            }
            wrap.appendChild(media);
        }

        const shade=document.createElement('div');shade.className='us-opening-cover-shade';wrap.appendChild(shade);
        const content=document.createElement('div');content.className='us-opening-cover-content';content.dataset.previewDevice=device;
        content.style.left=clamp(Number(c.contentX??50),10,90)+'%';
        content.style.top=clamp(Number(c.contentY??50),10,90)+'%';
        content.style.transform='translate(-50%,-50%)';content.style.textAlign='center';content.style.color=c.textColor||'#fff';

        const inner=document.createElement('div');inner.className='us-opening-cover-inner';
        const deviceScale=device==='mobile'?.72:(device==='tablet'?.86:1);
        const coverAnimMap={'fade':'usFade','fade-up':'usFadeUp','zoom':'usZoom','soft-scale':'usSoftScale'};
        if(c.animation&&c.animation!=='none'&&coverAnimMap[c.animation])inner.style.animation=`${coverAnimMap[c.animation]} ${Number(c.duration||.8)}s ease-out both`;

        const eyebrow=document.createElement('div');eyebrow.className='us-opening-cover-eyebrow';eyebrow.textContent=c.eyebrow||'';eyebrow.style.fontSize=Math.max(9,14*deviceScale)+'px';
        const names=document.createElement('div');names.className='us-opening-cover-names';
        const coverNames=openingCoverNames(),baseSize=Number(c.nameSize||46),len=Math.max(1,coverNames.length),lengthScale=len>28?.72:(len>20?.84:1);
        names.style.fontSize=Math.max(device==='mobile'?21:24,baseSize*lengthScale*deviceScale)+'px';names.textContent=coverNames;
        const btn=document.createElement('button');btn.className='us-opening-cover-button';btn.type='button';btn.style.background=c.buttonColor||'#2F6FED';btn.style.fontSize=Math.max(8,11*deviceScale)+'px';btn.textContent=c.buttonText||'Buka Undangan';
        btn.addEventListener('click',()=>runOpeningExit(wrap,c,()=>wrap.classList.add('hidden')));
        inner.append(eyebrow,names,btn);content.appendChild(inner);wrap.appendChild(content);
        return wrap;
    }
    function renderOpeningMediaPreview(){
        const host=$('openingMediaPreview');if(!host)return;
        const c=openingCoverState(),m=openingMediaSettings();
        host.className='us-opening-media-preview '+openingMediaDevice;host.innerHTML='';
        if(c.image){
            const isVideo=c.mediaType==='video'||/\.mp4(?:\?|$)/i.test(c.image),media=document.createElement(isVideo?'video':'img');
            media.src=c.image;applyOpeningMediaStyle(media,c,openingMediaDevice);
            if(isVideo){media.muted=true;media.autoplay=true;media.loop=true;media.playsInline=true;}
            host.appendChild(media);
        }else{
            const e=document.createElement('div');e.className='us-opening-media-empty';e.textContent='Belum ada media Opening Cover';host.appendChild(e);
        }
        if($('openingMediaFit'))$('openingMediaFit').value=m.fit||'cover';
        if($('openingMediaScale'))$('openingMediaScale').value=Number(m.scale||1);
        if($('openingMediaX'))$('openingMediaX').value=Number(m.x??50);
        if($('openingMediaY'))$('openingMediaY').value=Number(m.y??50);
        document.querySelectorAll('[data-opening-media-device]').forEach(b=>b.classList.toggle('active',b.dataset.openingMediaDevice===openingMediaDevice));
        bindOpeningDirectAdjust();
    }
    const openingDirectPointers=new Map();
    let openingDirectGesture=null;
    function bindOpeningDirectAdjust(){
        const host=$('openingMediaPreview');
        if(!host||host.dataset.directAdjustBound==='1')return;
        host.dataset.directAdjustBound='1';
        host.style.touchAction='none';

        host.addEventListener('pointerdown',e=>{
            const c=openingCoverState();
            if(!c.image)return;
            host.setPointerCapture?.(e.pointerId);
            openingDirectPointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
            const m=openingMediaSettings();
            if(openingDirectPointers.size===1){
                openingDirectGesture={
                    kind:'drag',
                    startX:e.clientX,startY:e.clientY,
                    x:Number(m.x??50),y:Number(m.y??50)
                };
            }else if(openingDirectPointers.size===2){
                const pts=[...openingDirectPointers.values()];
                openingDirectGesture={
                    kind:'pinch',
                    startDist:Math.hypot(pts[1].x-pts[0].x,pts[1].y-pts[0].y),
                    startScale:Number(m.scale||1)
                };
            }
            host.classList.add('is-adjusting');
            e.preventDefault();
        });

        host.addEventListener('pointermove',e=>{
            if(!openingDirectPointers.has(e.pointerId)||!openingDirectGesture)return;
            openingDirectPointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
            const m=openingMediaSettings(),rect=host.getBoundingClientRect();

            if(openingDirectPointers.size>=2){
                const pts=[...openingDirectPointers.values()];
                const dist=Math.hypot(pts[1].x-pts[0].x,pts[1].y-pts[0].y);
                if(openingDirectGesture.kind!=='pinch'){
                    openingDirectGesture={kind:'pinch',startDist:dist,startScale:Number(m.scale||1)};
                }
                m.scale=clamp(
                    openingDirectGesture.startScale*(dist/Math.max(1,openingDirectGesture.startDist)),
                    1,2.5
                );
            }else if(openingDirectGesture.kind==='drag'){
                const dx=(e.clientX-openingDirectGesture.startX)/Math.max(1,rect.width)*100;
                const dy=(e.clientY-openingDirectGesture.startY)/Math.max(1,rect.height)*100;
                m.x=clamp(openingDirectGesture.x-dx,0,100);
                m.y=clamp(openingDirectGesture.y-dy,0,100);
            }

            dirty=true;
            setSaveStatus('Belum disimpan','');
            renderOpeningMediaPreview();
            e.preventDefault();
        });

        const finish=e=>{
            openingDirectPointers.delete(e.pointerId);
            if(openingDirectPointers.size===0){
                openingDirectGesture=null;
                host.classList.remove('is-adjusting');
                markDirty();
            }
        };
        host.addEventListener('pointerup',finish);
        host.addEventListener('pointercancel',finish);

        host.addEventListener('wheel',e=>{
            if(!openingCoverState().image)return;
            const m=openingMediaSettings();
            m.scale=clamp(Number(m.scale||1)+(e.deltaY<0?0.08:-0.08),1,2.5);
            markDirty();
            renderOpeningMediaPreview();
            e.preventDefault();
        },{passive:false});
    }

    function syncOpeningCoverControls(){
        const c=openingCoverState();
        if($('openingCoverEnabled'))$('openingCoverEnabled').checked=!!c.enabled;
        if($('openingCoverBindNames'))$('openingCoverBindNames').checked=!!c.bindNames;
        [['openingCoverEyebrow','eyebrow'],['openingCoverNames','names'],['openingCoverButtonText','buttonText'],['openingCoverTextColor','textColor'],['openingCoverButtonColor','buttonColor'],['openingCoverNameSize','nameSize'],['openingCoverY','contentY'],['openingCoverAnimation','animation'],['openingCoverDuration','duration'],['openingCoverExitAnimation','exitAnimation'],['openingCoverExitDuration','exitDuration']]
          .forEach(([id,key])=>{if($(id))$(id).value=c[key]??'';});
        renderOpeningMediaPreview();
    }
    function previewOpeningCoverStandalone(){
        openResponsivePreview(openingMediaDevice);
        requestAnimationFrame(()=>{const box=$('responsivePreviewStage')?.querySelector('.us-preview-device');if(!box)return;box.querySelector('.us-opening-cover-preview')?.remove();const cover=buildOpeningCover(openingMediaDevice);if(cover)box.appendChild(cover);});
    }

    function renderResponsivePreview(device='desktop'){
        captureResponsivePreviewVideoState();
        const stage=$('responsivePreviewStage');stage.innerHTML='';
        const box=document.createElement('div');box.className='us-preview-device '+device;
        const pages=(state.pages||[]).filter(p=>p.role!=='desktop-cover');
        const layout=state.settings?.desktopLayout||'cover-left';

        const appendPages=(root,width)=>{
            pages.forEach((p,i)=>{
                const entry=i===0?null:(pages[i-1]?.transition||null);
                root.appendChild(scaledStaticPage(p,width,entry,device));
            });
            requestAnimationFrame(()=>{
                activatePreviewTransitions(root);
                hydrateResponsivePreviewVideos(root);
            });
        };

        if(device==='desktop'&&layout!=='centered'){
            box.style.setProperty('--cover-width',(state.settings?.desktopCoverWidth||56)+'%');
            const cover=document.createElement('div');cover.className='us-preview-cover';cover.appendChild(buildDedicatedDesktopCover());
            const scroll=document.createElement('div');scroll.className='us-preview-scroll';
            if(layout==='cover-right')box.append(scroll,cover);else box.append(cover,scroll);
            requestAnimationFrame(()=>appendPages(scroll,deviceProfile('desktop').width));
        }else{
            const stack=document.createElement('div');stack.className='us-preview-mobile-stack';box.appendChild(stack);
            requestAnimationFrame(()=>{
                const logical=deviceProfile(device).width;
                appendPages(stack,logical);
            });
        }

        if(!isInstanceMode){
            const note=document.createElement('div');note.className='us-preview-note';
            note.textContent=device==='desktop'
                ? (layout==='centered'?'Desktop: satu kolom':'Desktop: sampul tetap + isi undangan scroll')
                : 'HP/Tablet: satu kolom scroll';
            box.appendChild(note);
        }
        stage.appendChild(box);const opening=buildOpeningCover(device);if(opening)box.appendChild(opening);$('responsivePreviewLabel').textContent=device==='mobile'?'HP':device.toUpperCase();
        ['previewMobileBtn','previewTabletBtn','previewDesktopBtn'].forEach(id=>$(id)?.classList.toggle('active',id.toLowerCase().includes(device)));
        requestAnimationFrame(()=>interactionDiag('RESPONSIVE_PREVIEW_GEOMETRY',{
            device,
            previewWidth:box.getBoundingClientRect().width,
            previewHeight:box.getBoundingClientRect().height,
            contentWidth:(box.querySelector('.us-preview-scroll')||box.querySelector('.us-preview-mobile-stack'))?.getBoundingClientRect().width||0,
            canvasCount:box.querySelectorAll('.us-preview-section').length
        }));
    }
    function openResponsivePreview(device){
        // Force preview session back to autoplay while preserving the latest playback position.
        responsivePreviewVideoState.forEach((state,key)=>{
            if(state&&typeof state==='object'){
                state.paused=false;
                responsivePreviewVideoState.set(key,state);
            }
        });
        $('responsivePreview').classList.add('open');$('responsivePreview').setAttribute('aria-hidden','false');renderResponsivePreview(device);
    }
    function closeResponsivePreview(){
        captureResponsivePreviewVideoState();
        $('responsivePreviewStage')?.querySelectorAll('video').forEach(v=>{try{v.pause();}catch(_){}});
        $('responsivePreview').classList.remove('open');
        $('responsivePreview').setAttribute('aria-hidden','true');
    }


    function setCustomerInstanceState(text,state=''){
        const el=$('customerInstanceState');if(!el)return;
        el.textContent=text;el.className='us-instance-state'+(state?' '+state:'');
    }
    function imageAssets(){
        return assets.filter(a=>a.type==='image');
    }
    function renderCustomerMediaSelects(){
        document.querySelectorAll('[data-customer-media]').forEach(select=>{
            const key=select.dataset.customerMedia;
            const current=customerData?.[key];
            const resolved=studioCustomerAssetFor(current);
            const currentId=resolved?String(resolved.id):'';
            select.innerHTML='<option value="">— Kosong —</option>'+imageAssets().map(a=>`<option value="${a.id}">${esc(a.name)}</option>`).join('');
            select.value=currentId;

            const field=select.closest('.us-customer-field');
            if(!field)return;
            let note=field.querySelector(':scope > .us-cumulative-stale-media-note');
            if(studioCustomerMediaIsStale(current)){
                field.dataset.usStaleCustomerMedia='1';
                if(!note){
                    note=document.createElement('div');
                    note.className='us-cumulative-stale-media-note';
                    field.appendChild(note);
                }
                note.replaceChildren();
                const text=document.createElement('span');
                text.textContent='Referensi foto lama tidak tersedia di Media. Foto ini tidak akan dipakai di canvas sampai dipilih ulang.';
                const clear=document.createElement('button');
                clear.type='button';
                clear.className='us-cumulative-stale-clear';
                clear.textContent='Hapus referensi lama';
                clear.addEventListener('click',()=>{
                    customerData[key]=null;
                    select.value='';
                    delete field.dataset.usStaleCustomerMedia;
                    note?.remove();
                    try{interactionDiag('CUSTOMER_MEDIA_STALE_CLEAR',{key});}catch(_){ }
                    if(typeof queueCustomerSave==='function')queueCustomerSave();scheduleCustomerVisualRefresh();
                    renderCustomerMediaSelects();
                },{once:true});
                note.append(text,clear);
                try{interactionDiag('CUSTOMER_MEDIA_STALE_BLOCKED',{key});}catch(_){ }
            }else{
                delete field.dataset.usStaleCustomerMedia;
                note?.remove();
            }
        });
    }
    let customerVisualRaf=0;
    function scheduleCustomerVisualRefresh(force=false){
        if(!force&&editorViewMode!=='customer')return;
        if(customerVisualRaf)return;
        customerVisualRaf=requestAnimationFrame(()=>{
            customerVisualRaf=0;
            if(typeof renderCanvas==='function')renderCanvas();
            else if(typeof render==='function')render();
        });
    }
    function syncCustomerForm(){
        document.querySelectorAll('[data-customer-key]').forEach(el=>{el.value=customerData?.[el.dataset.customerKey]??'';});
        renderCustomerMediaSelects();
        setCustomerInstanceState(isInstanceMode?'Tersimpan':(customerPreviewInstance?'Instance Uji aktif':'Belum dibuat'),isInstanceMode?'saved':(customerPreviewInstance?'saved':''));
    }
    async function ensureCustomerPreviewInstance(){
        if(customerPreviewInstance)return customerPreviewInstance;
        setCustomerInstanceState('Membuat...','saving');
        const r=await fetch(ensurePreviewInstanceUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
        const j=await r.json();if(!r.ok)throw new Error(j.message||'Gagal membuat instance uji');
        customerPreviewInstance=j.instance;customerData=Object.assign({},customerData,j.instance.content||{});
        syncCustomerForm();return customerPreviewInstance;
    }
    /* UNDANGANTA_DATA_BINDING_CORE_V1 */
    function syncDerivedCoupleName(){
        if(!isInstanceMode)return;
        const groom=String(customerData.groom_name||'').trim();
        const bride=String(customerData.bride_name||'').trim();
        customerData.couple_names=[groom,bride].filter(Boolean).join(' & ');
        const field=document.querySelector('[data-customer-key="couple_names"]');
        if(field)field.value=customerData.couple_names;
        customerInstanceDirtyKeys.add('couple_names');
    }

    async function saveInstanceCustomerData(){
        if(!isInstanceMode||!instanceCustomerFullUpdateUrl||!customerInstanceDirtyKeys.size)return;
        if(customerSaveInFlight){customerSavePending=true;return;}

        const keys=[...customerInstanceDirtyKeys];
        const payload={};
        keys.forEach(key=>payload[key]=customerData[key]??null);

        try{
            customerSaveInFlight=true;
            setCustomerInstanceState('Menyimpan...','saving');

            const r=await fetch(instanceCustomerFullUpdateUrl,{
                method:'PUT',
                headers:{
                    'X-CSRF-TOKEN':csrf,
                    'Content-Type':'application/json',
                    'Accept':'application/json'
                },
                body:JSON.stringify({content:payload})
            });

            const text=await r.text();
            let j={};
            try{j=text?JSON.parse(text):{};}catch(_){ }
            if(!r.ok)throw new Error(Object.values(j.errors||{})[0]?.[0]||j.message||`Gagal menyimpan Data Undangan (${r.status})`);

            keys.forEach(key=>{
                if(JSON.stringify(customerData[key]??null)===JSON.stringify(payload[key]??null)){
                    customerInstanceDirtyKeys.delete(key);
                }
            });

            if(j.content&&typeof j.content==='object'){
                Object.entries(j.content).forEach(([key,value])=>{
                    if(!customerInstanceDirtyKeys.has(key))customerData[key]=value;
                });
            }

            setCustomerInstanceState(customerInstanceDirtyKeys.size?'Belum disimpan':'Tersimpan '+(j.saved_at||''),customerInstanceDirtyKeys.size?'':'saved');
            scheduleCustomerVisualRefresh(true);
            interactionDiag('CUSTOMER_DATA_SAVE',{
                keys,
                remainingDirty:customerInstanceDirtyKeys.size
            });
            customerSavePending=customerSavePending||customerInstanceDirtyKeys.size>0;
        }catch(e){
            setCustomerInstanceState('Gagal simpan','error');
            notify(e.message);
        }finally{
            customerSaveInFlight=false;
            if(customerSavePending&&customerInstanceDirtyKeys.size){
                customerSavePending=false;
                customerSaveTimer=setTimeout(saveInstanceCustomerData,450);
            }
        }
    }
    async function saveCustomerPreview(){
        if(customerSaveInFlight){customerSavePending=true;return;}
        const saveSnapshot=JSON.parse(JSON.stringify(customerData||{}));
        try{
            customerSaveInFlight=true;
            setCustomerInstanceState('Menyimpan...','saving');

            let r;
            if(isInstanceMode){
                r=await fetch(cumulativeInstanceFullUrl,{
                    method:'PUT',
                    headers:{
                        'X-CSRF-TOKEN':csrf,
                        'Content-Type':'application/json',
                        'Accept':'application/json'
                    },
                    body:JSON.stringify({
                        canvas:state,
                        content:saveSnapshot
                    })
                });
            }else{
                const instance=await ensureCustomerPreviewInstance();
                r=await fetch(`/admin/studio/instances/${instance.id}`,{
                    method:'PUT',
                    headers:{
                        'X-CSRF-TOKEN':csrf,
                        'Content-Type':'application/json',
                        'Accept':'application/json'
                    },
                    body:JSON.stringify({content:saveSnapshot})
                });
            }

            const text=await r.text();
            let j={};
            try{j=text?JSON.parse(text):{};}catch(_){ }
            if(!r.ok)throw new Error(j.message||Object.values(j.errors||{})[0]?.[0]||`Gagal menyimpan (${r.status})`);

            if(!isInstanceMode){
                customerPreviewInstance=j.instance||customerPreviewInstance;
            }
            const savedContent=isInstanceMode?j.content:j.instance?.content;
            if(savedContent&&typeof savedContent==='object'){
                Object.entries(savedContent).forEach(([key,value])=>{
                    if(JSON.stringify(customerData[key]??null)===JSON.stringify(saveSnapshot[key]??null))customerData[key]=value;
                    else customerSavePending=true;
                });
            }
            setCustomerInstanceState(customerSavePending?'Belum disimpan':'Tersimpan',customerSavePending?'':'saved');
            scheduleCustomerVisualRefresh(true);
            try{interactionDiag('CUSTOMER_DATA_SAVE_OK',{mode:isInstanceMode?'instance':'admin-preview'});}catch(_){ }
        }catch(e){
            setCustomerInstanceState('Gagal menyimpan','error');
            notify(e?.message||'Data Undangan gagal disimpan');
            try{interactionDiag('CUSTOMER_DATA_SAVE_ERROR',{message:String(e?.message||e)});}catch(_){ }
        }finally{
            customerSaveInFlight=false;
            if(customerSavePending){
                customerSavePending=false;
                clearTimeout(customerSaveTimer);
                customerSaveTimer=setTimeout(saveCustomerPreview,450);
            }
        }
    }
    function queueCustomerSave(){
        clearTimeout(customerSaveTimer);
        if(customerSaveInFlight)customerSavePending=true;
        setCustomerInstanceState('Belum disimpan','');
        customerSaveTimer=setTimeout(isInstanceMode?saveInstanceCustomerData:saveCustomerPreview,1200);
    }
$('createCustomerPreview')?.addEventListener('click',async()=>{try{await ensureCustomerPreviewInstance();notify('Instance pelanggan uji siap');render();}catch(e){notify(e.message);}});
    $('resetCustomerPreview')?.addEventListener('click',async()=>{
        if(!window.confirm('Reset semua data pelanggan uji? Master template tidak akan berubah.'))return;
        try{
            setCustomerInstanceState('Mereset...','saving');
            const r=await fetch(resetPreviewInstanceUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
            const j=await r.json();if(!r.ok)throw new Error(j.message||'Reset gagal');
            customerPreviewInstance=j.instance;customerData=Object.assign({},j.instance.content||{});syncCustomerForm();render();notify('Data pelanggan uji direset');
        }catch(e){setCustomerInstanceState('Reset gagal','error');notify(e.message);}
    });
    document.querySelectorAll('[data-customer-key]').forEach(el=>{
        el.addEventListener('input',()=>{
            const key=el.dataset.customerKey;
            customerData[key]=el.value;

            if(isInstanceMode){
                customerInstanceDirtyKeys.add(key);
                if(key==='groom_name'||key==='bride_name')syncDerivedCoupleName();
            }

            queueCustomerSave();scheduleCustomerVisualRefresh();
        });
    });
    document.querySelectorAll('[data-customer-media]').forEach(el=>{
        el.addEventListener('change',()=>{
            const key=el.dataset.customerMedia;
            const asset=assets.find(a=>String(a.id)===String(el.value));
            customerData[key]=asset?{asset_id:asset.id,url:asset.url,name:asset.name}:null;
            if(isInstanceMode)customerInstanceDirtyKeys.add(key);
            queueCustomerSave();scheduleCustomerVisualRefresh();
        });
    });

        document.querySelectorAll('[data-view-mode]').forEach(btn=>btn.addEventListener('click',()=>{
        
        if(isInstanceMode){editorViewMode='customer';return;}
        editorViewMode=btn.dataset.viewMode;
        document.querySelectorAll('[data-view-mode]').forEach(x=>x.classList.toggle('active',x===btn));
        if(editorViewMode!=='sample'&&cropMode)exitCropMode();
        if(editorViewMode==='customer'&&!isInstanceMode){syncCustomerForm();openPanel('customer-data');}
        render();
        if(isInstanceMode){
            notify(editorViewMode==='sample'?'Mode Desain':editorViewMode==='empty'?'Mode Tanpa Data':'Mode Data Saya');
        }else{
            notify(editorViewMode==='sample'?'Mode Contoh':editorViewMode==='empty'?'Mode Kosong':'Preview Pelanggan');
        }
    }));

        document.querySelectorAll('[data-editor-device]').forEach(b=>b.addEventListener('click',()=>setEditorDeviceMode(b.dataset.editorDevice)));
    $('previewMobileBtn')?.addEventListener('click',()=>renderResponsivePreview('mobile'));
    $('previewTabletBtn')?.addEventListener('click',()=>renderResponsivePreview('tablet'));
    $('previewDesktopBtn')?.addEventListener('click',()=>renderResponsivePreview('desktop'));
    document.addEventListener('undanganta:open-responsive-preview',event=>openResponsivePreview(event.detail?.device||'mobile'));
    $('closeResponsivePreview')?.addEventListener('click',closeResponsivePreview);
    window.addEventListener('keydown',e=>{if(e.key==='Escape'&&$('responsivePreview')?.classList.contains('open'))closeResponsivePreview();});

    window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue='';}});
    function auditStudioControls(){
        const report={
            buttonIds:[...document.querySelectorAll('button[id]')].map(b=>b.id),
            panelButtons:document.querySelectorAll('[data-panel-target]').length,
            shapes:document.querySelectorAll('[data-shape-kind]').length,
            frames:document.querySelectorAll('[data-frame-kind]').length,
            grids:document.querySelectorAll('[data-grid-kind]').length,
            components:document.querySelectorAll('[data-component]').length,
            animationPresets:document.querySelectorAll('[data-animation-preset]').length
        };
        report.mobile={width:window.innerWidth,studioPosition:getComputedStyle(document.querySelector('.us-wrap')).position,libraryPosition:getComputedStyle(document.querySelector('.us-library')).position,propertiesPosition:getComputedStyle(document.querySelector('.us-properties-panel')).position,mobileNav:document.querySelectorAll('.us-mobile-nav button').length};
        console.info('[UNDANGANTA Studio control audit]',report);return report;
    }




    // Canva-like workspace controls
    const railButtons = Array.from(document.querySelectorAll('[data-panel-target]'));
    const panelSections = Array.from(document.querySelectorAll('[data-panel-section]'));
    const studioBody = document.querySelector('.us-body');
    function setLibraryCollapsed(collapsed){
        const library=document.querySelector('.us-library'),toggle=$('toggleLibrary');
        if(window.innerWidth<=760){
            library?.classList.toggle('mobile-open',!collapsed);
            if(collapsed)document.querySelectorAll('.us-mobile-nav button').forEach(b=>b.classList.remove('active'));
        }else{
            studioBody?.classList.toggle('library-collapsed',!!collapsed);
        }
        if(toggle){
            toggle.setAttribute('aria-expanded',collapsed?'false':'true');
            toggle.textContent=collapsed?'›':'‹';
        }
        requestAnimationFrame(()=>{});
        try{sessionStorage.setItem('undangantaStudioLibraryCollapsed',collapsed?'1':'0')}catch(e){}
    }
    function setPropertiesCollapsed(collapsed){
        const panel=document.querySelector('.us-properties-panel'),toggle=$('toggleProperties');
        if(window.innerWidth<=760){
            panel?.classList.toggle('mobile-open',!collapsed);
            if(collapsed)document.querySelectorAll('.us-mobile-nav button').forEach(b=>b.classList.remove('active'));
        }else{
            studioBody?.classList.toggle('properties-collapsed',!!collapsed);
        }
        if(toggle){
            toggle.setAttribute('aria-expanded',collapsed?'false':'true');
            toggle.textContent=collapsed?'‹':'›';
        }
        requestAnimationFrame(()=>{});
        try{sessionStorage.setItem('undangantaStudioPropsCollapsed',collapsed?'1':'0')}catch(e){}
    }
    function closeMobileSheets(){
        document.querySelector('.us-library')?.classList.remove('mobile-open');
        document.querySelector('.us-properties-panel')?.classList.remove('mobile-open');
        document.querySelectorAll('.us-mobile-nav button').forEach(b=>b.classList.remove('active'));
    }
    function openPanel(name,{toggleMobile=false}={}){
        const library=document.querySelector('.us-library'),propsPanel=document.querySelector('.us-properties-panel');
        const isMobile=window.innerWidth<=780;
        const targetSection=panelSections.find(s=>s.dataset.panelSection===name);
        if(!targetSection)return;

        const panelTitle={template:'Template',cover:'Cover','customer-data':isInstanceMode?'Data Undangan':'Data Uji',components:'Tambah Bagian',text:'Teks',elements:'Elemen',uploads:'Media',gallery:'Galeri',fonts:'Huruf',animation:'Gerak',layers:'Susunan'}[name];
        const panelTitleNode=library?.querySelector('.us-library-head span');if(panelTitle&&panelTitleNode)panelTitleNode.textContent=panelTitle;

        const alreadyOpen=isMobile&&library?.classList.contains('mobile-open')&&!targetSection.hidden;
        panelSections.forEach(s=>s.hidden=s!==targetSection);
        railButtons.forEach(b=>b.classList.toggle('active',b.dataset.panelTarget===name));

        if(isMobile){
            propsPanel?.classList.remove('mobile-open');
            if(toggleMobile&&alreadyOpen){
                library?.classList.remove('mobile-open');
                document.querySelectorAll('.us-mobile-nav button').forEach(b=>b.classList.remove('active'));
                return;
            }
            library?.classList.add('mobile-open');
            document.querySelectorAll('.us-mobile-nav button').forEach(b=>b.classList.toggle('active',b.dataset.panelTarget===name));
        }else{
            setLibraryCollapsed(false);
        }
    }
    railButtons.forEach(btn=>btn.addEventListener('click',()=>openPanel(btn.dataset.panelTarget,{toggleMobile:!!btn.closest('.us-mobile-nav')})));
    $('openCustomerDataPanel')?.addEventListener('click',()=>openPanel('customer-data'));
    /* UNDANGANTA_MEMPELAI_SECOND_PRESS_CROP_V1_2 — superseded safely by V1.3 */
    /* UNDANGANTA_MEMPELAI_CROP_GESTURE_OWNER_V1_3 */
    let mempelaiCropPressArm={layerId:null,time:0};

    document.addEventListener('pointerdown',e=>{
        if(!isInstanceMode)return;

        const targetCanvas=e.target?.closest?.('.us-canvas[data-page-id]');
        const mediaCell=e.target?.closest?.('.us-media-cell');
        const layerNode=mediaCell?.closest?.('.us-layer[data-id]');
        if(!targetCanvas||!mediaCell||!layerNode)return;

        const layerId=layerNode.dataset.id||'';
        const l=page().layers.find(x=>String(x.id)===String(layerId));
        if(!isMempelaiBoundFrame(l))return;

        const now=Date.now();
        const same=
            String(selectedId||'')===String(l.id) &&
            String(mempelaiCropPressArm.layerId||'')===String(l.id) &&
            (now-mempelaiCropPressArm.time)<430;

        mempelaiCropPressArm={layerId:l.id,time:now};

        if(!same||!cropCellHasRenderableMedia(l,0))return;

        ensureCells(l,1);
        setSingleSelection(l.id);
        activeCellIndex=0;
        cropMode={layerId:l.id,cellIndex:0};
        touchCellDelete=null;

        l.cells[0].scale=Math.max(1,Number(l.cells[0].scale||1));
        clampCropCell(l,0,{preferNormalized:true});

        document.querySelector('.us-contextbar')?.classList.add('crop-mode');
        layerNode.classList.add('crop-active','selected');

        interactionDiag('MEMPELAI_CROP_GESTURE_OWNER',{
            pageId:activePageId,
            layerId:l.id,
            binding:l.binding,
            cellIndex:0,
            noRender:true,
            samePointerGesture:true
        });

        // Do not stop propagation.
        // Existing V7/V8 pointerdown sees cropMode and starts cropDrag
        // with pointer capture on this exact second press.
    },true);


    $('openFontLibrary')?.addEventListener('click',()=>openPanel('fonts'));

    function centerSelectedText(){
        const l=selected();if(!l||l.type!=='text'){notify('Pilih layer teks dulu');return;}
        pushHistory();const p=page();
        l.x=Math.round((Number(p.width||390)-Number(l.width||0))/2);
        l.y=Math.round((Number(p.height||844)-Number(l.height||0))/2);
        markDirty();render();
    }
    function alignSelectedTextCenter(){
        const l=selected();if(!l||l.type!=='text'){notify('Pilih layer teks dulu');return;}
        pushHistory();const p=page();
        l.x=Math.round((Number(p.width||390)-Number(l.width||0))/2);
        markDirty();render();
    }
    $('centerTextXY')?.addEventListener('click',centerSelectedText);
    $('alignTextCenter')?.addEventListener('click',alignSelectedTextCenter);

    document.getElementById('toggleLibrary')?.addEventListener('click',()=>{
        if(window.innerWidth<=760){
            const open=document.querySelector('.us-library')?.classList.contains('mobile-open');
            setLibraryCollapsed(!!open);
        }else{
            setLibraryCollapsed(!studioBody?.classList.contains('library-collapsed'));
        }
    });
    document.getElementById('toggleProperties')?.addEventListener('click',()=>{
        if(window.innerWidth<=760){
            const open=document.querySelector('.us-properties-panel')?.classList.contains('mobile-open');
            setPropertiesCollapsed(!!open);
        }else{
            setPropertiesCollapsed(!studioBody?.classList.contains('properties-collapsed'));
        }
    });

    const workspaceEl=document.querySelector('.us-workspace');
    function setPagesCollapsed(collapsed){
        const panel=$('pagesPanel');
        workspaceEl?.classList.toggle('pages-collapsed',!!collapsed);
        if(panel)panel.hidden=!!collapsed;

        const btn=$('togglePagesPanel');
        if(btn){
            btn.textContent=collapsed?'Tampilkan Halaman':'Sembunyikan Halaman';
            btn.setAttribute('aria-expanded',collapsed?'false':'true');
            btn.setAttribute('aria-pressed',collapsed?'true':'false');
        }

        try{sessionStorage.setItem('undangantaStudioPagesCollapsed',collapsed?'1':'0')}catch(e){}
    }
    $('togglePagesPanel')?.addEventListener('click',()=>setPagesCollapsed(!workspaceEl?.classList.contains('pages-collapsed')));
    try{
        const savedPages=sessionStorage.getItem('undangantaStudioPagesCollapsed');
        setPagesCollapsed(savedPages==='1');
    }catch(e){setPagesCollapsed(false)}

    document.getElementById('mobileProps')?.addEventListener('click',()=>{
        const props=document.querySelector('.us-properties-panel'),lib=document.querySelector('.us-library');
        lib?.classList.remove('mobile-open');
        const willOpen=!props?.classList.contains('mobile-open');
        props?.classList.toggle('mobile-open',willOpen);
        document.querySelectorAll('.us-mobile-nav button').forEach(b=>b.classList.toggle('active',b.id==='mobileProps'&&willOpen));
    });
    $('mobileCloseLibrary')?.addEventListener('click',()=>closeMobileSheets());
    $('mobileCloseProps')?.addEventListener('click',()=>closeMobileSheets());

    document.querySelector('.us-canvas-zone')?.addEventListener('pointerdown',e=>{
        if(window.innerWidth>760)return;
        if(e.target.closest('.us-layer,.us-guide-handle,.us-contextbar,.us-pages-wrap,.us-pages-toggle'))return;
        closeMobileSheets();
    },{capture:false});

    
    document.getElementById('homeStudio')?.addEventListener('click',()=>window.location.href=@json($isInstanceMode ? route('dashboard') : route('admin.studio.index')));

    const zoomRange = document.getElementById('zoomRange');
    const zoomValue = document.getElementById('zoomValue');
    function applyZoom(v){
        v=Math.max(25,Math.min(120,Number(v||68)));
        currentEditorZoom=v;
        if(zoomValue)zoomValue.textContent=v+'%';
        if(zoomRange)zoomRange.value=v;
        renderCanvas();
    }
    function fitCanvasForViewport(){
        const d=deviceProfile(editorDeviceMode);
        const zoneWidth=Math.max(260,(canvasZone?.clientWidth||window.innerWidth)-56);
        const preferred=Math.min(100,Math.max(32,(zoneWidth/d.width)*100));
        applyZoom(preferred);
    }
    $('zoomOutBtn')?.addEventListener('click',()=>applyZoom(currentEditorZoom-10));
    $('zoomInBtn')?.addEventListener('click',()=>applyZoom(currentEditorZoom+10));
    $('zoomFitBtn')?.addEventListener('click',fitCanvasForViewport);
    zoomRange?.addEventListener('input',e=>applyZoom(e.target.value));
    fitCanvasForViewport();
    window.addEventListener('resize',()=>{
        if(window.innerWidth<=760)fitCanvasForViewport();
    });
    if(window.innerWidth<=760){
        panelSections.forEach(s=>s.hidden=s.dataset.panelSection!=='template');
        railButtons.forEach(b=>b.classList.toggle('active',b.dataset.panelTarget==='template'&&!b.closest('.us-mobile-nav')));
        closeMobileSheets();
    }else openPanel(isInstanceMode?'cover':'template');
    if(window.innerWidth>760){
        let libCollapsed=true, propsCollapsed=true;
        try{
            const l=sessionStorage.getItem('undangantaStudioLibraryCollapsed'),p=sessionStorage.getItem('undangantaStudioPropsCollapsed');
            libCollapsed=l===null?true:l==='1';propsCollapsed=p===null?true:p==='1';
        }catch(e){}
        setLibraryCollapsed(libCollapsed);setPropertiesCollapsed(propsCollapsed);
    }

    scrubStaleStudioAssetReferences();renderAssets();renderFonts();syncCustomerForm();render();setEditorDeviceMode('mobile');auditStudioControls();if(!dirty)setSaveStatus('Tersimpan','saved');
    renderPremiumGalleryManager();
    if(window.__UNDANGANTA_DIAG__){
        interactionDiag('ANIMATION_ARCHITECTURE_SNAPSHOT',{
            globalPreview:!!preview,
            elementPresetCount:document.querySelectorAll('[data-element-animation-preset]').length,
            canvasPresetCount:document.querySelectorAll('[data-canvas-animation-preset]').length,
            hasPreviewButton:!!$('previewSelectedAnimation'),
            canvasAnimation:page()?.canvasAnimation?.type||page()?.transition?.type||'none',
            pageHasSeparateAnimation:!!page()?.canvasAnimation,
            selectedElementAnimation:selected()?.animationConfig?.type||selected()?.animation||'none',
            componentFactoryContainsAnimatedDefaults:false
        });
    }
})();

    /* UNDANGANTA_DELETE_GUARD_GUIDED_FOCUS_V1 */
    const studioDeleteGuardV1={
        lastIntent:null,
        modal:null,
        lastShownAt:0
    };

    function deleteGuardNow(){return Date.now();}

    function deleteGuardEsc(v){
        return String(v??'')
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'",'&#039;');
    }

    function deleteGuardNormalizeUrl(v){
        if(!v)return '';
        try{
            return new URL(String(v),location.href).pathname.replace(/\/+$/,'');
        }catch(_){
            return String(v).split('?')[0].replace(/\/+$/,'');
        }
    }

    function deleteGuardSameMedia(a,b){
        if(!a||!b)return false;
        const aa=deleteGuardNormalizeUrl(a);
        const bb=deleteGuardNormalizeUrl(b);
        return aa!==''&&aa===bb;
    }

    function deleteGuardPulse(el){
        if(!el||!el.classList)return;
        try{el.scrollIntoView({behavior:'smooth',block:'center',inline:'center'});}catch(_){}
        el.classList.remove('us-guided-problem-pulse');
        void el.offsetWidth;
        el.classList.add('us-guided-problem-pulse');
        setTimeout(()=>el.classList.remove('us-guided-problem-pulse'),2100);
    }

    function deleteGuardClose(){
        studioDeleteGuardV1.modal?.remove();
        studioDeleteGuardV1.modal=null;
    }

    function deleteGuardFocus(payload={}){
        deleteGuardClose();

        const focusSelector=payload.focusSelector;
        const original=payload.triggerEl?.isConnected?payload.triggerEl:null;

        if(payload.kind==='layer'&&payload.pageId&&payload.layerId){
            try{
                activePageId=payload.pageId;
                setSingleSelection(payload.layerId);
                render();
                requestAnimationFrame(()=>{
                    const canvas=document.querySelector(`.us-canvas[data-page-id="${CSS.escape(String(payload.pageId))}"]`);
                    const layer=canvas?.querySelector(`.us-layer[data-id="${CSS.escape(String(payload.layerId))}"]`);
                    deleteGuardPulse(layer||canvas||original);
                });
                return;
            }catch(_){}
        }

        if(payload.kind==='customer-data'&&payload.key){
            try{openPanel('customer-data');}catch(_){}
            requestAnimationFrame(()=>{
                const key=CSS.escape(String(payload.key));
                const target=
                    document.querySelector(`[data-customer-media="${key}"]`)||
                    document.querySelector(`[data-customer-key="${key}"]`)||
                    document.querySelector('[data-panel="customer-data"]')||
                    original;
                deleteGuardPulse(target);
            });
            return;
        }

        if(payload.kind==='gallery'){
            try{openPanel('gallery');}catch(_){}
            requestAnimationFrame(()=>{
                const id=payload.assetId!=null?CSS.escape(String(payload.assetId)):'';
                const target=
                    (id&&document.querySelector(`[data-asset-id="${id}"]`))||
                    document.querySelector('#premiumGalleryList')||
                    document.querySelector('[data-panel="gallery"]')||
                    original;
                deleteGuardPulse(target);
            });
            return;
        }

        if(payload.kind==='cover'){
            try{openPanel('cover');}catch(_){}
            requestAnimationFrame(()=>{
                const target=
                    (focusSelector&&document.querySelector(focusSelector))||
                    document.querySelector('[data-panel="cover"]')||
                    original;
                deleteGuardPulse(target);
            });
            return;
        }

        if(focusSelector){
            try{
                const target=document.querySelector(focusSelector);
                if(target){deleteGuardPulse(target);return;}
            }catch(_){}
        }

        deleteGuardPulse(original);
    }

    function showDeleteBlockedNotice(payload={}){
        deleteGuardClose();

        const backdrop=document.createElement('div');
        backdrop.className='us-delete-guard-backdrop';
        backdrop.setAttribute('role','dialog');
        backdrop.setAttribute('aria-modal','true');
        backdrop.setAttribute('aria-label','Tidak bisa dihapus');

        const title=payload.title||'Tidak bisa dihapus';
        const message=payload.message||'Item ini belum bisa dihapus karena masih memiliki keterkaitan di Studio.';

        backdrop.innerHTML=`
            <div class="us-delete-guard-card">
                <div class="us-delete-guard-eyebrow">Perlu diperiksa</div>
                <h3 class="us-delete-guard-title">${deleteGuardEsc(title)}</h3>
                <p class="us-delete-guard-message">${deleteGuardEsc(message)}</p>
                <div class="us-delete-guard-actions">
                    <button type="button" class="us-delete-guard-btn" data-delete-guard-close>Tutup</button>
                    <button type="button" class="us-delete-guard-btn primary" data-delete-guard-focus>Tunjukkan lokasi</button>
                </div>
            </div>
        `;

        backdrop.addEventListener('click',e=>{
            if(e.target===backdrop||e.target.closest('[data-delete-guard-close]')){
                deleteGuardClose();
                return;
            }
            if(e.target.closest('[data-delete-guard-focus]')){
                deleteGuardFocus(payload);
            }
        });

        document.body.appendChild(backdrop);
        studioDeleteGuardV1.modal=backdrop;
        studioDeleteGuardV1.lastShownAt=deleteGuardNow();

        requestAnimationFrame(()=>{
            backdrop.querySelector('[data-delete-guard-focus]')?.focus();
        });

        try{
            interactionDiag('DELETE_GUARD_BLOCKED',{
                reason:payload.reason||'unknown',
                kind:payload.kind||'generic',
                assetId:payload.assetId??null,
                pageId:payload.pageId??null,
                layerId:payload.layerId??null,
                key:payload.key??null
            });
        }catch(_){}
    }

    window.showDeleteBlockedNotice=showDeleteBlockedNotice;
    window.focusDeleteBlockedProblem=deleteGuardFocus;

    function deleteGuardAssetFromControl(control){
        if(!control)return null;

        const ids=[];
        const urls=[];
        const addId=value=>{
            const n=Number(value);
            if(Number.isInteger(n)&&n>0&&!ids.includes(n))ids.push(n);
        };
        const addUrl=value=>{
            const raw=String(value||'').trim();
            if(!raw)return;
            if(!urls.includes(raw))urls.push(raw);
            const match=raw.match(/\/assets\/(\d+)(?:\/(?:file|stream))?(?:[/?#]|$)/);
            if(match)addId(match[1]);
        };

        addId(control.dataset?.assetId);
        addId(control.dataset?.id);
        addId(control.value);
        addUrl(control.dataset?.url);
        addUrl(control.getAttribute?.('href'));
        addUrl(control.getAttribute?.('formaction'));

        const card=control.closest(
            '[data-asset-id],[data-id],.us-asset,.us-asset-card,.us-asset-item,.us-library-item,.us-upload-card,.us-upload-item,.us-media-card,article,li'
        );

        if(card){
            addId(card.dataset?.assetId);
            addId(card.dataset?.id);
            addUrl(card.dataset?.url);

            card.querySelectorAll('img[src],video[src],source[src],a[href],[data-url],[data-asset-id],[data-id]').forEach(node=>{
                addId(node.dataset?.assetId);
                addId(node.dataset?.id);
                addUrl(node.getAttribute?.('src'));
                addUrl(node.getAttribute?.('href'));
                addUrl(node.getAttribute?.('data-url'));
            });

            const html=card.outerHTML||'';
            for(const match of html.matchAll(/\/assets\/(\d+)(?:\/(?:file|stream))?(?:[/?#"' ]|$)/g)){
                addId(match[1]);
            }
        }

        const id=ids[0]||null;
        const url=urls[0]||'';
        if(!id&&!url)return null;

        return {
            id,
            url,
            type:card?.dataset?.type||card?.dataset?.assetType||'',
            name:
                card?.querySelector?.('[data-asset-name]')?.textContent?.trim()||
                card?.querySelector?.('.us-asset-name')?.textContent?.trim()||
                control.getAttribute?.('aria-label')||''
        };
    }

    function deleteGuardBindingLabel(key){
        if(/^gallery_\d+$/.test(String(key||'')))return 'Gallery Undangan';
        return ({
            groom_photo:'Foto Mempelai Pria',
            bride_photo:'Foto Mempelai Wanita',
            couple_photo:'Foto Bersama Mempelai',
            opening_cover_media:'Opening Cover',
            desktop_cover_media:'Desktop Sticky Cover',
            groom_name:'Nama Mempelai Pria',
            bride_name:'Nama Mempelai Wanita',
            couple_names:'Nama Mempelai',
            event_date:'Tanggal Acara',
            venue_name:'Nama Lokasi',
            venue_address:'Alamat Lokasi',
            maps_url:'Link Google Maps',
            opening_text:'Kalimat Pembuka',
            quote:'Kutipan / Quote',
            prayer:'Doa',
            story:'Cerita',
            closing_text:'Kalimat Penutup',
            music_url:'Musik Undangan',
            gift_bank:'Nama Bank / Dompet Digital',
            gift_number:'Nomor Rekening',
            gift_name:'Nama Pemilik Rekening'
        })[key]||'bagian undangan terkait';
    }

    function deleteGuardValueUsesAsset(value,asset){
        if(value==null||!asset)return false;

        const assetId=Number(asset.id||0);
        const assetUrl=asset.url||'';

        if(typeof value==='string'){
            return assetUrl?deleteGuardSameMedia(value,assetUrl):false;
        }

        if(Array.isArray(value)){
            return value.some(v=>deleteGuardValueUsesAsset(v,asset));
        }

        if(typeof value==='object'){
            const objectId=Number(value.asset_id??value.assetId??value.media_id??0);
            if(assetId&&objectId===assetId)return true;

            for(const [k,v] of Object.entries(value)){
                const key=String(k).toLowerCase();
                if(
                    typeof v==='string' &&
                    /(url|src|path|photo|image|media|cover)/.test(key) &&
                    assetUrl &&
                    deleteGuardSameMedia(v,assetUrl)
                )return true;
            }
        }

        return false;
    }

    function deleteGuardFindCustomerUse(asset){
        if(!customerData||typeof customerData!=='object')return null;

        for(const [key,value] of Object.entries(customerData)){
            if(!deleteGuardValueUsesAsset(value,asset))continue;

            if(key==='gallery'||/^gallery_\d+$/.test(key)){
                return {
                    kind:'gallery',
                    reason:'gallery-use',
                    assetId:asset.id,
                    message:'Foto ini masih digunakan di Galeri Undangan. Lepaskan dari galeri terlebih dahulu.',
                    key
                };
            }

            return {
                kind:'customer-data',
                reason:'data-binding-use',
                assetId:asset.id,
                key,
                message:`Foto ini masih digunakan sebagai ${deleteGuardBindingLabel(key)} di Data Undangan. Kosongkan atau ganti fotonya terlebih dahulu.`
            };
        }

        return null;
    }

    function deleteGuardLayerUsesAsset(layer,asset){
        if(!layer||!asset)return null;

        if(deleteGuardValueUsesAsset(layer.src,asset)){
            return {cellIndex:null};
        }

        if(Array.isArray(layer.cells)){
            for(let i=0;i<layer.cells.length;i++){
                if(deleteGuardValueUsesAsset(layer.cells[i],asset))return {cellIndex:i};
            }
        }

        if(layer.binding&&layer.binding!=='none'){
            const bound=customerData?.[layer.binding];
            if(deleteGuardValueUsesAsset(bound,asset)){
                return {cellIndex:0,binding:layer.binding};
            }
        }

        return null;
    }

    function deleteGuardFindLayerUse(asset){
        for(const p of (state?.pages||[])){
            for(const l of (p?.layers||[])){
                const hit=deleteGuardLayerUsesAsset(l,asset);
                if(!hit)continue;

                return {
                    kind:'layer',
                    reason:'canvas-use',
                    assetId:asset.id,
                    pageId:p.id,
                    layerId:l.id,
                    cellIndex:hit.cellIndex,
                    binding:hit.binding||null,
                    message:`Media ini masih dipakai pada elemen "${l.name||'tanpa nama'}" di canvas. Lepaskan media dari elemen tersebut terlebih dahulu.`
                };
            }
        }
        return null;
    }

    function deleteGuardFindAssetUse(asset){
        return deleteGuardFindLayerUse(asset)||deleteGuardFindCustomerUse(asset);
    }

    function deleteGuardToken(control){
        if(!control)return '';
        return [
            control.id,
            control.className,
            control.getAttribute?.('aria-label'),
            control.getAttribute?.('title'),
            control.dataset?.action,
            control.dataset?.cmd,
            control.textContent
        ].filter(Boolean).join(' ').toLowerCase();
    }

    function deleteGuardIsDeleteControl(el){
        const control=el?.closest?.('button,a,[role="button"]');
        if(!control)return null;
        const token=deleteGuardToken(control);
        if(
            control.matches('.us-asset-delete')||
            /\b(delete|remove|hapus|trash)\b/i.test(token)
        )return control;
        return null;
    }

    function deleteGuardSelectedLocked(){
        try{
            const l=selected();
            if(!l)return null;
            if(isInstanceMode&&(l.customerEditPolicy==='locked'||l.locked===true)){
                return {
                    kind:'layer',
                    reason:'locked-layer',
                    pageId:activePageId,
                    layerId:l.id,
                    message:`Elemen "${l.name||'ini'}" dikunci oleh template dan tidak dapat dihapus dari mode pelanggan.`
                };
            }
        }catch(_){}
        return null;
    }

    function deleteGuardLastCanvas(control){
        const token=deleteGuardToken(control);
        if(!/(canvas|page|halaman)/.test(token))return null;
        if((state?.pages?.length||0)>1)return null;

        return {
            kind:'generic',
            reason:'last-canvas',
            triggerEl:control,
            message:'Canvas terakhir tidak dapat dihapus. Studio harus memiliki minimal satu canvas.'
        };
    }

    document.addEventListener('click',e=>{
        const control=deleteGuardIsDeleteControl(e.target);
        if(!control)return;

        studioDeleteGuardV1.lastIntent={
            triggerEl:control,
            time:deleteGuardNow(),
            token:deleteGuardToken(control)
        };

        /*
         * Asset deletion is the strongest known ambiguous flow in runtime QA.
         * Guard it BEFORE the old handler / network request when a real reference
         * can be proven from current design/customer state.
         */
        if(control.matches('.us-asset-delete')){
            const asset=deleteGuardAssetFromControl(control);
            if(asset){
                const use=deleteGuardFindAssetUse(asset);
                if(use){
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    showDeleteBlockedNotice({...use,triggerEl:control});
                    return;
                }
            }
        }

        const lastCanvas=deleteGuardLastCanvas(control);
        if(lastCanvas){
            e.preventDefault();
            e.stopImmediatePropagation();
            showDeleteBlockedNotice(lastCanvas);
            return;
        }

        const locked=deleteGuardSelectedLocked();
        if(locked&&/(layer|element|elemen|selected|hapus|delete|trash)/.test(studioDeleteGuardV1.lastIntent.token)){
            e.preventDefault();
            e.stopImmediatePropagation();
            showDeleteBlockedNotice({...locked,triggerEl:control});
        }
    },true);

    /*
     * Existing delete code already owns server requests / permissions.
     * If it emits an error notice, upgrade that notice into the centered guided
     * blocker instead of duplicating request logic or monkey-patching fetch().
     */
    const deleteGuardFailurePattern=/(tidak bisa|tidak dapat|gagal|masih digunakan|masih dipakai|sedang digunakan|hanya media|tidak memiliki izin|cannot|in use|permission)/i;

    const deleteGuardObserver=new MutationObserver(records=>{
        const intent=studioDeleteGuardV1.lastIntent;
        if(!intent||deleteGuardNow()-intent.time>4500)return;
        if(deleteGuardNow()-studioDeleteGuardV1.lastShownAt<350)return;

        for(const record of records){
            for(const node of record.addedNodes){
                const el=node.nodeType===1?node:node.parentElement;
                if(!el||el.closest?.('.us-delete-guard-backdrop'))continue;

                const text=String(node.textContent||'').trim().replace(/\s+/g,' ');
                if(text.length<4||text.length>260||!deleteGuardFailurePattern.test(text))continue;

                const asset=intent.triggerEl?.matches?.('.us-asset-delete')
                    ? deleteGuardAssetFromControl(intent.triggerEl)
                    : null;
                const use=asset?deleteGuardFindAssetUse(asset):null;

                showDeleteBlockedNotice({
                    ...(use||{}),
                    kind:use?.kind||'generic',
                    reason:use?.reason||'delete-error',
                    triggerEl:intent.triggerEl,
                    assetId:asset?.id??use?.assetId??null,
                    message:text
                });
                return;
            }
        }
    });

    deleteGuardObserver.observe(document.body,{childList:true,subtree:true});

    document.addEventListener('keydown',e=>{
        if(e.key==='Escape'&&studioDeleteGuardV1.modal)deleteGuardClose();
    });

    /* UNDANGANTA_STUDIO_CUSTOMER_UX_CLARITY_V1_SAFE_RUNTIME */
    const studioCustomerUxCouplePhotoUsed=false;
    const studioCustomerUxGalleryDedicated=true;
    let studioCustomerUxClarityQueued=false;

    function studioCustomerUxRoot(){
        return document.querySelector('.studio-role-premium,.studio-role-royal');
    }
    function studioCustomerUxField(key){
        const escaped=CSS.escape(String(key));
        const control=
            document.querySelector(`[data-customer-media="${escaped}"]`)||
            document.querySelector(`[data-customer-key="${escaped}"]`);
        return control?.closest?.('.us-customer-field')||null;
    }
    function studioCustomerUxSetField(key,label,note=''){
        const field=studioCustomerUxField(key);
        if(!field)return null;
        const labelEl=field.querySelector(':scope > label')||field.querySelector('label');
        if(labelEl&&labelEl.textContent.trim()!==label)labelEl.textContent=label;

        let noteEl=field.querySelector(':scope > .us-customer-field-note');
        if(note){
            if(!noteEl){
                noteEl=document.createElement('small');
                noteEl.className='us-customer-field-note';
                field.appendChild(noteEl);
            }
            if(noteEl.textContent!==note)noteEl.textContent=note;
        }else if(noteEl){
            noteEl.remove();
        }
        return field;
    }
    function studioCustomerUxHeading(grid,title,first=false){
        const key='ux-'+String(title).toLowerCase().replace(/[^a-z0-9]+/g,'-');
        let el=grid.querySelector(`:scope > [data-us-section="${key}"]`);
        if(!el){
            el=document.createElement('div');
            el.className='us-customer-section-heading';
            el.dataset.usSection=key;
        }
        el.classList.toggle('first',first);
        el.textContent=title;
        return el;
    }
    function studioCustomerUxMoveGroup(grid,title,keys,first=false){
        const fields=keys.map(studioCustomerUxField).filter(Boolean).filter(field=>field.parentElement===grid);
        if(!fields.length)return;
        const heading=studioCustomerUxHeading(grid,title,first);
        grid.appendChild(heading);
        for(const field of fields)grid.appendChild(field);
    }
    function studioCustomerUxApplyLanguage(root){
        const exactMap=new Map([
            ['Data binding','Terhubung ke Data Undangan'],
            ['Data Binding','Terhubung ke Data Undangan'],
            ['Customer data','Data Undangan'],
            ['Customer Data','Data Undangan'],
            ['Upload asset','Unggah Foto / Video'],
            ['Upload Asset','Unggah Foto / Video'],
            ['Unggah asset','Unggah Foto / Video'],
            ['Clear media','Hapus foto dari elemen'],
            ['Clear Media','Hapus foto dari elemen'],
            ['Layer','Elemen'],
            ['Layers','Elemen']
        ]);
        root.querySelectorAll('label,.us-meta,.us-hint,.us-instance-note,button,option').forEach(el=>{
            if(el.matches('button')&&el.querySelector('*'))return;
            const current=el.textContent.trim();
            const next=exactMap.get(current);
            if(next)el.textContent=next;
        });
        ['bindingHint','cellBindingHint','textPanelHint'].forEach(id=>{
            const el=document.getElementById(id);
            if(!el)return;
            let text=el.textContent;
            text=text.replace(/data pelanggan/gi,'Data Undangan');
            text=text.replace(/\blayer\b/gi,'elemen');
            text=text.replace(/\bgrid cell\b/gi,'bagian foto');
            text=text.replace(/Kotak\s+\d+/gi,'Bagian foto');
            if(text!==el.textContent)el.textContent=text;
        });
        root.querySelectorAll('option').forEach(option=>{
            const text=option.textContent.trim();
            if(text==='Foto pasangan')option.textContent='Foto Bersama Mempelai';
            if(text==='Nama pasangan')option.textContent='Nama Mempelai';
        });
    }
    function applyStudioCustomerUxClarityV1(){
        studioCustomerUxClarityQueued=false;
        const root=studioCustomerUxRoot();
        if(!root)return;

        const groom=studioCustomerUxSetField(
            'groom_photo',
            'Foto Mempelai Pria',
            'Digunakan pada elemen foto mempelai pria di undangan.'
        );
        const bride=studioCustomerUxSetField(
            'bride_photo',
            'Foto Mempelai Wanita',
            'Digunakan pada elemen foto mempelai wanita di undangan.'
        );
        const couple=studioCustomerUxSetField(
            'couple_photo',
            'Foto Bersama Mempelai',
            'Foto berdua yang dapat digunakan pada bagian undangan tertentu.'
        );
        if(couple&&!studioCustomerUxCouplePhotoUsed){
            couple.dataset.usCustomerHiddenClarityV1='1';
        }else if(couple){
            delete couple.dataset.usCustomerHiddenClarityV1;
        }

        studioCustomerUxSetField('groom_name','Nama Mempelai Pria');
        studioCustomerUxSetField('bride_name','Nama Mempelai Wanita');
        studioCustomerUxSetField('event_date','Tanggal Acara');
        studioCustomerUxSetField('venue_name','Nama Lokasi');
        studioCustomerUxSetField('venue_address','Alamat Lokasi');
        studioCustomerUxSetField('maps_url','Link Google Maps');
        studioCustomerUxSetField('opening_text','Kalimat Pembuka');
        studioCustomerUxSetField('quote','Kutipan / Quote');
        studioCustomerUxSetField('prayer','Doa');
        studioCustomerUxSetField('story','Cerita');
        studioCustomerUxSetField('closing_text','Kalimat Penutup');
        studioCustomerUxSetField('music_url','Musik Undangan');
        studioCustomerUxSetField('gift_bank','Nama Bank / Dompet Digital');
        studioCustomerUxSetField('gift_number','Nomor Rekening');
        studioCustomerUxSetField('gift_name','Nama Pemilik Rekening');

        /* couple_names remains intact for bindings/save compatibility, but is derived UX. */
        const coupleNames=studioCustomerUxField('couple_names');
        if(coupleNames)coupleNames.dataset.usCustomerHiddenClarityV1='1';

        const firstField=groom||bride||studioCustomerUxField('groom_name');
        const grid=firstField?.parentElement||null;
        if(grid){
            grid.querySelectorAll(':scope > .us-customer-section-heading').forEach(el=>el.remove());
            studioCustomerUxMoveGroup(
                grid,
                'FOTO MEMPELAI',
                studioCustomerUxCouplePhotoUsed?['groom_photo','bride_photo','couple_photo']:['groom_photo','bride_photo'],
                true
            );
            studioCustomerUxMoveGroup(grid,'MEMPELAI',['groom_name','bride_name']);
            studioCustomerUxMoveGroup(grid,'ACARA',['event_date','venue_name','venue_address','maps_url']);
            studioCustomerUxMoveGroup(grid,'ISI UNDANGAN',['opening_text','quote','prayer','story','closing_text']);
            studioCustomerUxMoveGroup(grid,'MUSIK',['music_url']);
            studioCustomerUxMoveGroup(grid,'HADIAH',['gift_bank','gift_number','gift_name']);
        }

        if(studioCustomerUxGalleryDedicated){
            const gallery=document.getElementById('customerGalleryFields');
            if(gallery)gallery.hidden=true;
        }

        studioCustomerUxApplyLanguage(root);
        try{
            interactionDiag('STUDIO_CUSTOMER_UX_CLARITY_APPLIED',{
                couplePhotoVisible:!!(couple&&studioCustomerUxCouplePhotoUsed),
                photoFirst:!!(groom&&bride),
                galleryDedicated:studioCustomerUxGalleryDedicated
            });
        }catch(_){ }
    }
    function scheduleStudioCustomerUxClarityV1(){
        if(studioCustomerUxClarityQueued)return;
        studioCustomerUxClarityQueued=true;
        requestAnimationFrame(applyStudioCustomerUxClarityV1);
    }

    scheduleStudioCustomerUxClarityV1();
    document.addEventListener('click',event=>{
        const trigger=event.target?.closest?.('[data-panel-target="customer-data"],#openCustomerDataPanel,[data-panel-target="gallery"],[data-panel-target="uploads"]');
        if(trigger)setTimeout(scheduleStudioCustomerUxClarityV1,0);
    },true);

    /* UNDANGANTA_STUDIO_DELETE_GUARD_SCOPE_FIX_V2_SAFE_RUNTIME */
    (()=>{
        const DG2={use:null,asset:null};

        function dg2Text(v){return String(v??'').trim()}
        function dg2Esc(v){return String(v??'').replace(/[&<>'"]/g,ch=>({"&":"&amp;","<":"&lt;",">":"&gt;","'":"&#039;",'"':'&quot;'}[ch]))}
        function dg2AssetIdFromUrl(raw){
            const m=dg2Text(raw).match(/\/assets\/(\d+)(?:\/(?:file|stream))?(?:[/?#"' )]|$)/i);
            return m?Number(m[1]):null;
        }
        function dg2AssetFromControl(control){
            if(!control)return null;
            const ids=[];const urls=[];
            const addId=v=>{const n=Number(v);if(Number.isInteger(n)&&n>0&&!ids.includes(n))ids.push(n)};
            const addUrl=v=>{const s=dg2Text(v);if(!s)return;if(!urls.includes(s))urls.push(s);addId(dg2AssetIdFromUrl(s))};

            addId(control.dataset?.assetId);addId(control.dataset?.id);addId(control.value);
            addUrl(control.dataset?.url);addUrl(control.getAttribute?.('href'));addUrl(control.getAttribute?.('formaction'));

            const card=control.closest?.('[data-asset-id],[data-id],.us-asset,.us-asset-card,.us-asset-item,.us-upload-card,.us-upload-item,.us-media-card,article,li');
            if(card){
                addId(card.dataset?.assetId);addId(card.dataset?.id);addUrl(card.dataset?.url);
                card.querySelectorAll?.('img[src],video[src],source[src],a[href],[data-url],[data-src]').forEach(node=>{
                    addUrl(node.currentSrc||node.getAttribute('src')||node.getAttribute('href')||node.getAttribute('data-url')||node.getAttribute('data-src'));
                });
            }
            const id=ids.find(Boolean)||null;
            const url=urls[0]||'';
            if(!id&&!url)return null;
            return {id,url,card,control};
        }
        function dg2MatchesAsset(raw,asset){
            const s=dg2Text(raw);if(!s||!asset)return false;
            const id=dg2AssetIdFromUrl(s);
            if(asset.id&&id===Number(asset.id))return true;
            if(asset.url){
                try{
                    const a=new URL(asset.url,location.href);const b=new URL(s,location.href);
                    if(a.pathname===b.pathname)return true;
                }catch(_){
                    if(s===asset.url)return true;
                }
            }
            return false;
        }
        function dg2NodeHasAsset(root,asset){
            if(!root||!asset)return false;
            const attrs=['src','poster','href','data-url','data-src'];
            if(root.matches?.('img[src],video[src],source[src],a[href],[data-url],[data-src]')){
                for(const a of attrs){if(dg2MatchesAsset(root.getAttribute?.(a),asset))return true}
                if(dg2MatchesAsset(root.currentSrc,asset))return true;
            }
            const style=root.getAttribute?.('style')||'';
            if(dg2MatchesAsset(style,asset)||(asset.id&&style.includes(`/assets/${asset.id}/`)))return true;
            for(const node of root.querySelectorAll?.('img[src],video[src],source[src],a[href],[data-url],[data-src],[style]')||[]){
                if(dg2MatchesAsset(node.currentSrc,asset))return true;
                for(const a of attrs){if(dg2MatchesAsset(node.getAttribute?.(a),asset))return true}
                const st=node.getAttribute?.('style')||'';
                if(asset.id&&st.includes(`/assets/${asset.id}/`))return true;
            }
            return false;
        }
        function dg2PageId(node){
            const page=node?.closest?.('[data-page-id],.us-live-page,[data-id^="canvas-"]');
            return page?.dataset?.pageId||page?.dataset?.id||page?.getAttribute?.('data-page-id')||page?.getAttribute?.('data-id')||null;
        }
        function dg2LayerLabel(node){
            return dg2Text(node?.dataset?.name)||dg2Text(node?.getAttribute?.('aria-label'))||'Elemen pada canvas';
        }
        function dg2FindCanvasUse(asset){
            const selectors=['#livePages .us-layer','#canvasZone .us-layer','.us-live-page .us-layer','.us-canvas .us-layer'];
            const seen=new Set();
            for(const sel of selectors){
                for(const layer of document.querySelectorAll(sel)){
                    if(seen.has(layer))continue;seen.add(layer);
                    if(dg2NodeHasAsset(layer,asset)){
                        return {kind:'canvas',label:dg2LayerLabel(layer),node:layer,pageId:dg2PageId(layer)};
                    }
                }
            }
            return null;
        }
        function dg2BindingLabel(key){
            const map={
                groom_photo:'Foto Mempelai Pria',bride_photo:'Foto Mempelai Wanita',couple_photo:'Foto Bersama Mempelai',
                opening_cover_media:'Cover Pembuka',desktop_cover_media:'Cover Desktop'
            };
            try{if(typeof deleteGuardBindingLabel==='function')return deleteGuardBindingLabel(key)}catch(_){}
            return map[key]||'Data Undangan';
        }
        function dg2FindDataUse(asset){
            if(!asset?.id)return null;
            for(const select of document.querySelectorAll('select[data-customer-media]')){
                if(String(select.value)===String(asset.id)){
                    const key=select.dataset.customerMedia||'';
                    return {kind:'data',label:dg2BindingLabel(key),node:select.closest('.us-customer-field')||select,key,panel:'customer-data'};
                }
            }
            return null;
        }
        function dg2VisibleActualMediaUse(root,asset,kind,label,panel){
            if(!root)return null;
            for(const node of root.querySelectorAll('img[src],video[src],source[src],[style]')){
                const rect=node.getBoundingClientRect?.();
                const cs=getComputedStyle(node);
                const visible=rect&&rect.width>0&&rect.height>0&&cs.display!=='none'&&cs.visibility!=='hidden';
                if(visible&&dg2NodeHasAsset(node,asset))return {kind,label,node,panel};
            }
            return null;
        }
        function dg2FindCoverUse(asset){
            const roots=[...document.querySelectorAll('[data-panel-section="cover"],#coverPanel,.us-cover-panel')];
            for(const root of roots){
                const use=dg2VisibleActualMediaUse(root,asset,'cover','Cover undangan','cover');
                if(use)return use;
            }
            return null;
        }
        function dg2FindGalleryUse(asset){
            const roots=[...document.querySelectorAll('[data-panel-section="gallery"],#premiumGalleryPicker,.us-gallery-panel,.us-premium-gallery')];
            for(const root of roots){
                if(asset?.id){
                    const selected=root.querySelector(`[data-asset-id="${CSS.escape(String(asset.id))}"].selected,[data-asset-id="${CSS.escape(String(asset.id))}"].active,[data-asset-id="${CSS.escape(String(asset.id))}"][aria-pressed="true"],input[value="${CSS.escape(String(asset.id))}"]:checked`);
                    if(selected)return {kind:'gallery',label:'Galeri',node:selected,panel:'gallery'};
                }
                const use=dg2VisibleActualMediaUse(root,asset,'gallery','Galeri','gallery');
                if(use)return use;
            }
            return null;
        }
        function dg2FindUse(asset){
            return dg2FindDataUse(asset)||dg2FindCanvasUse(asset)||dg2FindCoverUse(asset)||dg2FindGalleryUse(asset)||null;
        }
        function dg2OpenPanel(panel){
            if(!panel)return;
            const names={cover:'Cover','customer-data':'Data Undangan',gallery:'Galeri',uploads:'Unggahan'};
            const selectors=[`[data-panel-target="${panel}"]`,`[data-panel="${panel}"]`,`[data-section="${panel}"]`];
            let btn=null;
            for(const sel of selectors){btn=document.querySelector(sel);if(btn)break}
            if(!btn){
                const wanted=(names[panel]||'').toLowerCase();
                btn=[...document.querySelectorAll('.us-rail-btn,button')].find(x=>dg2Text(x.textContent).toLowerCase().includes(wanted))||null;
            }
            try{btn?.click?.()}catch(_){}
        }
        function dg2EnsureModal(){
            let overlay=document.getElementById('usDeleteGuardScopeV2Modal');
            if(overlay)return overlay;
            overlay=document.createElement('div');
            overlay.id='usDeleteGuardScopeV2Modal';
            overlay.className='us-dg2-overlay';
            overlay.hidden=true;
            overlay.innerHTML=`<div class="us-dg2-dialog" role="dialog" aria-modal="true" aria-labelledby="usDg2Title">
                <h3 id="usDg2Title" class="us-dg2-title">Foto / Media masih digunakan</h3>
                <p class="us-dg2-text">Media ini belum dapat dihapus karena masih dipakai di undangan. Lepaskan dari lokasi tersebut terlebih dahulu, lalu hapus kembali dari Unggahan.</p>
                <div class="us-dg2-location" data-dg2-location></div>
                <div class="us-dg2-actions"><button type="button" class="us-dg2-btn" data-dg2-close>Tutup</button><button type="button" class="us-dg2-btn primary" data-dg2-focus>Tunjukkan lokasi</button></div>
            </div>`;
            document.body.appendChild(overlay);
            overlay.addEventListener('click',e=>{
                if(e.target===overlay||e.target.closest('[data-dg2-close]')){overlay.hidden=true;return}
                if(e.target.closest('[data-dg2-focus]')){overlay.hidden=true;dg2FocusCurrent()}
            });
            document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!overlay.hidden)overlay.hidden=true});
            return overlay;
        }
        function dg2Show(asset,use){
            DG2.asset=asset;DG2.use=use;
            const overlay=dg2EnsureModal();
            const loc=overlay.querySelector('[data-dg2-location]');
            const where=use?.kind==='canvas'?(use.label||'Elemen pada canvas'):(use?.label||'bagian undangan');
            loc.innerHTML=`Lokasi terdeteksi: <strong>${dg2Esc(where)}</strong>`;
            overlay.hidden=false;
            setTimeout(()=>overlay.querySelector('[data-dg2-focus]')?.focus?.(),0);
        }
        function dg2Relocate(use,asset){
            if(!use)return dg2FindUse(asset);
            if(use.kind==='canvas')return dg2FindCanvasUse(asset)||use;
            if(use.kind==='data')return dg2FindDataUse(asset)||use;
            if(use.kind==='cover')return dg2FindCoverUse(asset)||use;
            if(use.kind==='gallery')return dg2FindGalleryUse(asset)||use;
            return use;
        }
        function dg2FocusCurrent(){
            const asset=DG2.asset;let use=DG2.use;if(!asset||!use)return;
            dg2OpenPanel(use.panel||null);
            setTimeout(()=>{
                use=dg2Relocate(use,asset)||use;
                const node=use.node;
                if(!node)return;
                try{node.scrollIntoView({behavior:'smooth',block:'center',inline:'center'})}catch(_){node.scrollIntoView?.()}
                node.classList?.remove('us-dg2-pulse');
                void node.offsetWidth;
                node.classList?.add('us-dg2-pulse');
                setTimeout(()=>node.classList?.remove('us-dg2-pulse'),1800);
                if(use.kind==='canvas'){
                    setTimeout(()=>{try{node.dispatchEvent(new MouseEvent('click',{bubbles:true,cancelable:true,view:window}))}catch(_){}},140);
                }
            },use.panel?180:40);
        }

        /*
         * Disable only the legacy LOOKUP functions that leak engine-private
         * variables. Existing modal/delete/backend code stays intact, but these
         * lookups can no longer throw from document click or MutationObserver.
         */
        const safeNullLookup=function(){return null};
        try{deleteGuardFindLayerUse=safeNullLookup}catch(_){window.deleteGuardFindLayerUse=safeNullLookup}
        try{deleteGuardFindAssetUse=safeNullLookup}catch(_){window.deleteGuardFindAssetUse=safeNullLookup}

        /*
         * WINDOW capture runs before the older document-level handler.
         * Only used media is intercepted. Unused media continues through the
         * original delete flow unchanged.
         */
        window.addEventListener('click',event=>{
            const control=event.target?.closest?.('.us-asset-delete');
            if(!control)return;
            const asset=dg2AssetFromControl(control);
            if(!asset)return;
            const use=dg2FindUse(asset);
            if(!use)return;
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            dg2Show(asset,use);
            try{
                if(typeof interactionDiag==='function')interactionDiag('DELETE_GUARD_SCOPE_V2_BLOCKED',{assetId:asset.id||null,kind:use.kind||null,label:use.label||null});
            }catch(_){}
        },true);

        try{
            if(typeof interactionDiag==='function')interactionDiag('DELETE_GUARD_SCOPE_V2_READY',{scopeSafe:true});
        }catch(_){}
    })();
</script>

<style id="undangantaSimpleWorkflowIosV3Style">


/* UNDANGANTA_STUDIO_SIMPLE_WORKFLOW_IOS_V3_SAFE */
/*
 * UX simplification layer for Admin + Premium/Royal Full Studio.
 * Visual direction: iOS light/minimal, flat hierarchy, no dark mode,
 * fewer visible choices, progressive disclosure, no logic changes.
 */
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin){
    --sw-bg:#F5F5F7;
    --sw-surface:#FFFFFF;
    --sw-fill:#F2F2F7;
    --sw-fill-2:#EDEDF2;
    --sw-text:#1D1D1F;
    --sw-secondary:#6E6E73;
    --sw-tertiary:#8E8E93;
    --sw-line:rgba(60,60,67,.12);
    --sw-line-soft:rgba(60,60,67,.075);
    --sw-blue:#007AFF;
    --sw-blue-soft:rgba(0,122,255,.09);
    color-scheme:light!important;
}

/* ---------- PANEL TITLES / COPY ---------- */
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-panel-title{
    margin:0 0 12px!important;
    color:var(--sw-text)!important;
    font-size:18px!important;
    line-height:1.2!important;
    font-weight:650!important;
    letter-spacing:-.025em!important;
    text-transform:none!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) [data-panel-section="components"] > .us-small,
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) [data-panel-section="components"] > .us-component-note,
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) [data-panel-section="customer-data"] > .us-small,
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) [data-panel-section="customer-data"] .us-instance-note,
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) [data-panel-section="customer-data"] .us-customer-field-note{
    display:none!important;
}

/* ---------- DATA UNDANGAN: 4 clear groups, one-column ---------- */
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) [data-panel-section="customer-data"] .us-customer-preview-card{
    margin:0!important;
    padding:0!important;
    border:0!important;
    background:transparent!important;
    box-shadow:none!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) [data-panel-section="customer-data"] .us-customer-preview-head{
    min-height:30px!important;
    margin:0 0 10px!important;
    justify-content:flex-end!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) [data-panel-section="customer-data"] .us-customer-preview-head .us-small{
    display:none!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-customer-grid.us-simple-data-grid{
    display:block!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-customer-grid.us-simple-data-grid > .us-customer-section-heading{
    display:none!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-group{
    overflow:hidden;
    margin:0 0 10px;
    border:1px solid var(--sw-line-soft);
    border-radius:14px;
    background:var(--sw-surface);
    box-shadow:none;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-header{
    width:100%;
    min-height:48px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:0 13px;
    border:0;
    background:#fff;
    color:var(--sw-text);
    font:600 13px/1.2 -apple-system,BlinkMacSystemFont,"SF Pro Text","Segoe UI",sans-serif;
    text-align:left;
    cursor:pointer;
    box-shadow:none;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-header:active{background:var(--sw-fill)!important}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-chevron{
    color:var(--sw-tertiary);
    font-size:15px;
    line-height:1;
    transition:transform .16s ease;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-group.open .us-simple-data-chevron{transform:rotate(90deg)}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body{
    display:none;
    padding:2px 12px 12px;
    border-top:1px solid var(--sw-line-soft);
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-group.open .us-simple-data-body{display:block}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body .us-customer-field{
    width:100%!important;
    margin:10px 0 0!important;
    grid-column:auto!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body .us-customer-field label{
    margin:0 0 6px!important;
    color:#3A3A3C!important;
    font-size:11px!important;
    line-height:1.2!important;
    font-weight:550!important;
    letter-spacing:0!important;
    text-transform:none!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body .us-customer-field input,
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body .us-customer-field select,
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body .us-customer-field textarea{
    width:100%!important;
    min-height:42px!important;
    padding:9px 11px!important;
    border:0!important;
    border-radius:11px!important;
    background:var(--sw-fill)!important;
    color:var(--sw-text)!important;
    font-size:12px!important;
    box-shadow:none!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body .us-customer-field textarea{min-height:74px!important;resize:vertical!important}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body .us-customer-field input:focus,
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body .us-customer-field select:focus,
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body .us-customer-field textarea:focus{
    outline:2px solid rgba(0,122,255,.30)!important;
    outline-offset:0!important;
}

/* ---------- COMPONENTS -> TAMBAH BAGIAN ---------- */
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-component-grid.us-simple-component-grid{
    display:flex!important;
    flex-direction:column!important;
    gap:15px!important;
    margin:0!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-group{display:block!important}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-group-title{
    margin:0 0 6px;
    padding:0 2px;
    color:var(--sw-secondary);
    font-size:11px;
    line-height:1.2;
    font-weight:600;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-list{
    overflow:hidden;
    border:1px solid var(--sw-line-soft);
    border-radius:14px;
    background:#fff;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-list .us-component-card{
    width:100%!important;
    min-height:50px!important;
    height:auto!important;
    aspect-ratio:auto!important;
    display:grid!important;
    grid-template-columns:30px minmax(0,1fr) 22px!important;
    align-items:center!important;
    gap:9px!important;
    padding:8px 11px!important;
    margin:0!important;
    border:0!important;
    border-bottom:1px solid var(--sw-line-soft)!important;
    border-radius:0!important;
    background:#fff!important;
    color:var(--sw-text)!important;
    text-align:left!important;
    box-shadow:none!important;
    transform:none!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-list .us-component-card:last-child{border-bottom:0!important}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-list .us-component-card:hover{background:var(--sw-fill)!important}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-list .us-component-card:active{background:var(--sw-fill-2)!important;opacity:.72!important}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-list .us-component-card .us-component-icon{
    width:28px!important;
    height:28px!important;
    display:grid!important;
    place-items:center!important;
    margin:0!important;
    border-radius:8px!important;
    background:var(--sw-fill)!important;
    color:#3A3A3C!important;
    font-size:13px!important;
    line-height:1!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-list .us-component-card b{
    min-width:0!important;
    color:var(--sw-text)!important;
    font-size:12.5px!important;
    line-height:1.25!important;
    font-weight:600!important;
    white-space:nowrap!important;
    overflow:hidden!important;
    text-overflow:ellipsis!important;
}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-list .us-component-card > span:not(.us-component-icon){display:none!important}
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-component-list .us-component-card::after{
    content:'+';
    display:grid;
    place-items:center;
    width:22px;
    height:22px;
    border-radius:50%;
    background:var(--sw-blue-soft);
    color:var(--sw-blue);
    font-size:16px;
    font-weight:500;
    line-height:1;
}

/* ---------- DESKTOP RAIL: simpler words ---------- */
:where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-rail-btn{
    min-height:54px!important;
    gap:3px!important;
    font-size:9px!important;
    font-weight:550!important;
    line-height:1.15!important;
}

/* ---------- SIMPLE MOBILE NAV / SHEET ---------- */
#usSimpleMobileNav,#usSimpleMobileSheet{display:none}

@media(max-width:780px){
    html:has(#usSimpleMobileNav),body:has(#usSimpleMobileNav){overflow:hidden!important;background:#F5F5F7!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin){height:calc(100dvh - 60px)!important;min-height:0!important}

    /* top: only navigation + Preview + Save */
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-top{
        height:50px!important;
        min-height:50px!important;
        padding:6px 8px!important;
        gap:5px!important;
        background:rgba(255,255,255,.96)!important;
        border-bottom:1px solid var(--sw-line-soft)!important;
        box-shadow:none!important;
        backdrop-filter:none!important;
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-top-center,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-save-status,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #undoBtn,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #redoBtn{display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-top-left{flex:1!important;min-width:0!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-top-right{gap:5px!important;margin-left:auto!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #previewBtn,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #saveBtn{
        height:34px!important;min-height:34px!important;padding:0 11px!important;border-radius:10px!important;font-size:10px!important;font-weight:600!important;box-shadow:none!important
    }

    /* actual mobile workspace: no desktop rail, no giant device selector */
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-body{height:calc(100% - 50px)!important;display:block!important;position:relative!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-rail,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-mobile-nav,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-library-collapse,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-properties-collapse{display:none!important}

    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-workspace{
        height:100%!important;
        width:100%!important;
        padding-bottom:64px!important;
        background:#F5F5F7!important;
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar:not(.crop-mode){display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar.crop-mode{
        height:42px!important;min-height:42px!important;padding:0 8px!important;justify-content:center!important;overflow-x:auto!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar .us-device-switch,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar .us-view-modes,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar #toggleSnap,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar #toggleSafeArea,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar #previewTransition,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar #prevCanvas,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar #nextCanvas,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-contextbar #canvasCounter{display:none!important}

    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-canvas-zone{
        height:100%!important;min-height:0!important;padding:12px 5px 76px!important;gap:10px!important;overflow:auto!important;align-items:center!important;justify-content:flex-start!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-pages-wrap,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-pages-toggle{display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-work-bottom{
        display:flex!important;position:absolute!important;right:8px!important;bottom:72px!important;width:auto!important;height:36px!important;padding:0!important;border:0!important;background:transparent!important;z-index:410!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-work-bottom>span,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-work-bottom>input{display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-grid-view-btn{
        width:36px!important;height:36px!important;border:1px solid var(--sw-line)!important;border-radius:11px!important;background:#fff!important;box-shadow:none!important
    }

    /* panels become clean bottom sheets, never desktop columns */
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-library{
        position:absolute!important;left:0!important;right:0!important;bottom:64px!important;top:auto!important;
        width:100%!important;height:min(76dvh,650px)!important;max-height:calc(100% - 8px)!important;
        display:flex!important;opacity:1!important;pointer-events:auto!important;
        border:0!important;border-top:1px solid var(--sw-line-soft)!important;border-radius:18px 18px 0 0!important;
        background:#fff!important;box-shadow:0 -3px 16px rgba(0,0,0,.055)!important;
        transform:translateY(calc(100% + 72px))!important;transition:transform .20s ease!important;z-index:520!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-library.mobile-open{transform:translateY(0)!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-library-head{
        height:48px!important;min-height:48px!important;padding:0 12px 0 15px!important;border-bottom:1px solid var(--sw-line-soft)!important;background:#fff!important;font-size:15px!important;font-weight:650!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-library-scroll{padding:14px!important;overflow:auto!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-panel-title{display:none!important}

    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-properties-panel{
        position:absolute!important;left:0!important;right:0!important;bottom:64px!important;top:auto!important;
        width:100%!important;height:min(76dvh,650px)!important;max-height:calc(100% - 8px)!important;
        border:0!important;border-top:1px solid var(--sw-line-soft)!important;border-radius:18px 18px 0 0!important;
        background:#fff!important;box-shadow:0 -3px 16px rgba(0,0,0,.055)!important;
        transform:translateY(calc(100% + 72px))!important;transition:transform .20s ease!important;z-index:530!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-properties-panel.mobile-open{transform:translateY(0)!important}

    /* one simple 5-item nav */
    #usSimpleMobileNav{
        position:absolute;left:0;right:0;bottom:0;z-index:700;height:64px;
        display:grid;grid-template-columns:repeat(5,minmax(0,1fr));
        padding:3px 6px max(3px,env(safe-area-inset-bottom));
        border-top:1px solid rgba(60,60,67,.10);background:rgba(255,255,255,.98);box-shadow:none
    }
    #usSimpleMobileNav button{
        min-width:0;border:0;background:transparent;color:#6E6E73;border-radius:10px;padding:4px 2px 3px;
        font:550 9px/1.1 -apple-system,BlinkMacSystemFont,"SF Pro Text","Segoe UI",sans-serif;cursor:pointer
    }
    #usSimpleMobileNav button .sw-icon{display:block;height:22px;font-size:18px;line-height:22px;margin-bottom:2px;color:#3A3A3C}
    #usSimpleMobileNav button.active{color:#007AFF;background:rgba(0,122,255,.07)}
    #usSimpleMobileNav button.active .sw-icon{color:#007AFF}

    #usSimpleMobileSheet{
        position:absolute;left:10px;right:10px;bottom:72px;z-index:720;display:none;
        overflow:hidden;border:1px solid rgba(60,60,67,.10);border-radius:16px;background:#fff;
        box-shadow:0 8px 28px rgba(0,0,0,.10);padding:6px
    }
    #usSimpleMobileSheet.open{display:block}
    #usSimpleMobileSheet button{
        width:100%;height:46px;display:flex;align-items:center;gap:10px;padding:0 11px;
        border:0;border-bottom:1px solid rgba(60,60,67,.075);border-radius:9px;background:#fff;color:#1D1D1F;
        font:550 13px/1.2 -apple-system,BlinkMacSystemFont,"SF Pro Text","Segoe UI",sans-serif;text-align:left;cursor:pointer
    }
    #usSimpleMobileSheet button:last-child{border-bottom:0}
    #usSimpleMobileSheet button:active{background:#F2F2F7}
    #usSimpleMobileSheet .sw-sheet-icon{width:24px;text-align:center;color:#3A3A3C;font-size:15px}

    /* data sheet: fewer words, easy scan */
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-header{min-height:50px!important;font-size:14px!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-simple-data-body{padding:2px 12px 13px!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-customer-actions{grid-template-columns:1fr!important}

    /* Diagnostic controls must not cover the working canvas on phone. */
    #undangantaDiagExport{
        position:fixed!important;right:8px!important;bottom:74px!important;z-index:760!important;
        width:auto!important;min-width:0!important;height:32px!important;min-height:32px!important;
        padding:0 9px!important;border-radius:10px!important;font-size:9px!important;box-shadow:none!important
    }
}

</style>
<script id="undangantaSimpleWorkflowIosV3Script">

/* UNDANGANTA_STUDIO_SIMPLE_WORKFLOW_IOS_V3_SAFE_RUNTIME */
(()=>{
    'use strict';
    const root=document.querySelector('.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin');
    if(!root||root.dataset.simpleWorkflowIosV3==='1')return;
    root.dataset.simpleWorkflowIosV3='1';
    const isAdmin=root.classList.contains('studio-role-admin');
    const $=(sel,ctx=root)=>ctx.querySelector(sel);
    const $$=(sel,ctx=root)=>Array.from(ctx.querySelectorAll(sel));

    function setPlainButtonLabel(btn,label){
        if(!btn)return;
        Array.from(btn.childNodes).forEach(node=>{
            if(node.nodeType===Node.TEXT_NODE)node.remove();
        });
        btn.appendChild(document.createTextNode(label));
        btn.setAttribute('aria-label',label);
    }
    function setPanelNavLabels(){
        const labels={
            'template':'Template',
            'cover':'Cover',
            'customer-data':isAdmin?'Data Uji':'Data',
            'components':'Tambah',
            'text':'Teks',
            'elements':'Bentuk',
            'uploads':'Media',
            'gallery':'Galeri',
            'fonts':'Huruf',
            'animation':'Gerak',
            'layers':'Susunan'
        };
        Object.entries(labels).forEach(([target,label])=>{
            $$(`[data-panel-target="${target}"]`).forEach(btn=>setPlainButtonLabel(btn,label));
        });
    }
    function updateLibraryHeader(target){
        const span=$('.us-library-head > span');
        if(!span)return;
        const labels={
            'template':'Template',
            'cover':'Cover',
            'customer-data':isAdmin?'Data Uji':'Data Undangan',
            'components':'Tambah Bagian',
            'text':'Teks',
            'elements':'Elemen',
            'uploads':'Media',
            'gallery':'Galeri',
            'fonts':'Huruf',
            'animation':'Gerak',
            'layers':'Susunan'
        };
        if(labels[target])span.textContent=labels[target];
    }
    function originalPanelButton(target){
        return root.querySelector(`.us-rail [data-panel-target="${CSS.escape(target)}"]`)
            || root.querySelector(`.us-mobile-nav [data-panel-target="${CSS.escape(target)}"]`)
            || root.querySelector(`[data-panel-target="${CSS.escape(target)}"]`);
    }
    function triggerPanel(target){
        closeQuickSheet();
        const btn=originalPanelButton(target);
        if(btn){
            btn.click();
            updateLibraryHeader(target);
            updateSimpleNavActive(target);
        }
    }

    function simplifyComponents(){
        const section=$('[data-panel-section="components"]');
        if(!section||section.dataset.simpleComponentsV3==='1')return;
        section.dataset.simpleComponentsV3='1';
        const title=section.querySelector(':scope > .us-h');
        if(title){title.textContent='Tambah Bagian';title.classList.add('us-simple-panel-title');}
        const grid=section.querySelector('.us-component-grid');
        if(!grid)return;
        grid.classList.add('us-simple-component-grid');
        const cards=new Map($$('.us-component-card',grid).map(card=>[card.dataset.component,card]));
        const groups=[
            {title:'Utama',items:[['couple','Mempelai'],['event','Acara'],['gallery','Galeri'],['location','Lokasi'],['gift','Hadiah'],['rsvp','RSVP']]},
            {title:'Cerita',items:[['quote','Kutipan'],['prayer','Doa'],['story','Cerita'],['closing','Penutup']]},
            {title:'Interaksi',items:[['countdown','Hitung Mundur'],['wishes','Ucapan'],['music','Musik'],['guest_photo','Foto Tamu']]}
        ];
        grid.replaceChildren();
        groups.forEach(group=>{
            const available=group.items.filter(([key])=>cards.has(key));
            if(!available.length)return;
            const wrap=document.createElement('div');wrap.className='us-simple-component-group';
            const heading=document.createElement('div');heading.className='us-simple-group-title';heading.textContent=group.title;
            const list=document.createElement('div');list.className='us-simple-component-list';
            available.forEach(([key,label])=>{
                const card=cards.get(key);
                const b=card.querySelector('b');if(b)b.textContent=label;
                card.setAttribute('aria-label',`Tambahkan ${label}`);
                list.appendChild(card);
            });
            wrap.append(heading,list);grid.appendChild(wrap);
        });
        // Keep any future/unknown component reachable at the end without technical descriptions.
        const known=new Set(groups.flatMap(g=>g.items.map(([key])=>key)));
        const extra=[...cards.entries()].filter(([key])=>!known.has(key));
        if(extra.length){
            const wrap=document.createElement('div');wrap.className='us-simple-component-group';
            const heading=document.createElement('div');heading.className='us-simple-group-title';heading.textContent='Lainnya';
            const list=document.createElement('div');list.className='us-simple-component-list';
            extra.forEach(([,card])=>list.appendChild(card));
            wrap.append(heading,list);grid.appendChild(wrap);
        }
    }

    function customerField(key){
        const esc=CSS.escape(String(key));
        return root.querySelector(`[data-customer-key="${esc}"]`)?.closest('.us-customer-field')
            || root.querySelector(`[data-customer-media="${esc}"]`)?.closest('.us-customer-field')
            || null;
    }
    function simplifyCustomerData(){
        const section=$('[data-panel-section="customer-data"]');
        if(!section||section.dataset.simpleDataV3==='1')return;
        const grid=section.querySelector('.us-customer-grid');
        if(!grid)return;
        section.dataset.simpleDataV3='1';
        const title=section.querySelector(':scope > .us-h');
        if(title){title.textContent=isAdmin?'Data Uji':'Data Undangan';title.classList.add('us-simple-panel-title');}
        grid.classList.add('us-simple-data-grid');
        grid.querySelectorAll(':scope > .us-customer-section-heading').forEach(el=>el.remove());

        const groups=[
            {id:'couple',title:'Mempelai',open:true,keys:['groom_name','bride_name','groom_photo','bride_photo','couple_photo']},
            {id:'event',title:'Acara',open:true,keys:['event_date','venue_name','venue_address','maps_url']},
            {id:'content',title:'Isi Undangan',open:false,keys:['opening_text','quote','prayer','story','closing_text']},
            {id:'more',title:'Hadiah & Musik',open:false,keys:['gift_bank','gift_number','gift_name','music_url']}
        ];
        groups.forEach(group=>{
            const fields=group.keys.map(customerField).filter(Boolean).filter((el,i,a)=>a.indexOf(el)===i);
            if(!fields.length)return;
            const wrapper=document.createElement('section');
            wrapper.className='us-simple-data-group'+(group.open?' open':'');
            wrapper.dataset.simpleDataGroup=group.id;
            const header=document.createElement('button');
            header.type='button';header.className='us-simple-data-header';header.setAttribute('aria-expanded',group.open?'true':'false');
            header.innerHTML=`<span>${group.title}</span><span class="us-simple-data-chevron" aria-hidden="true">›</span>`;
            const body=document.createElement('div');body.className='us-simple-data-body';
            fields.forEach(field=>body.appendChild(field));
            header.addEventListener('click',()=>{
                const open=!wrapper.classList.contains('open');
                wrapper.classList.toggle('open',open);header.setAttribute('aria-expanded',open?'true':'false');
            });
            wrapper.append(header,body);grid.appendChild(wrapper);
        });
    }

    let mobileSheet=null;
    function closeQuickSheet(){if(mobileSheet){mobileSheet.classList.remove('open');mobileSheet.setAttribute('aria-hidden','true');}}
    function updateSimpleNavActive(target){
        const nav=document.getElementById('usSimpleMobileNav');if(!nav)return;
        let key=target;
        if(['components','text','elements'].includes(target))key='add';
        if(target==='uploads')key='media';
        if(['gallery','fonts','animation','layers'].includes(target))key='more';
        nav.querySelectorAll('button').forEach(btn=>btn.classList.toggle('active',btn.dataset.simpleNav===key));
    }
    function openQuickSheet(mode){
        const library=$('.us-library');const props=$('.us-properties-panel');
        library?.classList.remove('mobile-open');props?.classList.remove('mobile-open');
        if(!mobileSheet)return;
        if(mobileSheet.classList.contains('open')&&mobileSheet.dataset.mode===mode){closeQuickSheet();return;}
        mobileSheet.dataset.mode=mode;mobileSheet.replaceChildren();
        const options=mode==='add'
            ? [['elements','✦','Elemen'],['text','T','Teks']]
            : [...(isAdmin?[['template','▦','Template']]:[]),['gallery','▦','Galeri'],['fonts','Aa','Huruf'],['animation','◌','Gerak'],['layers','☷','Susunan']];
        options.forEach(([target,icon,label])=>{
            if(!originalPanelButton(target))return;
            const btn=document.createElement('button');btn.type='button';
            btn.innerHTML=`<span class="sw-sheet-icon">${icon}</span><span>${label}</span>`;
            btn.addEventListener('click',()=>triggerPanel(target));mobileSheet.appendChild(btn);
        });
        mobileSheet.classList.add('open');mobileSheet.setAttribute('aria-hidden','false');updateSimpleNavActive(mode);
    }
    function buildSimpleMobileNav(){
        if(document.getElementById('usSimpleMobileNav'))return;
        const nav=document.createElement('nav');nav.id='usSimpleMobileNav';nav.setAttribute('aria-label','Menu editor');
        const items=[
            ['cover','▣','Cover'],
            ['customer-data','≡',isAdmin?'Data Uji':'Data'],
            ['add','＋','Tambah'],
            ['media','▧','Media'],
            ['more','•••','Lainnya']
        ];
        items.forEach(([key,icon,label])=>{
            const btn=document.createElement('button');btn.type='button';btn.dataset.simpleNav=key;
            btn.innerHTML=`<span class="sw-icon">${icon}</span><span>${label}</span>`;
            btn.addEventListener('click',()=>{
                if(key==='add'||key==='more'){openQuickSheet(key);return;}
                if(key==='media'){triggerPanel('uploads');return;}
                triggerPanel(key);
            });
            nav.appendChild(btn);
        });
        mobileSheet=document.createElement('div');mobileSheet.id='usSimpleMobileSheet';mobileSheet.className='us-mobile-sheet-surface';mobileSheet.setAttribute('role','menu');mobileSheet.setAttribute('aria-hidden','true');
        root.append(mobileSheet,nav);
        document.addEventListener('pointerdown',e=>{
            if(!mobileSheet?.classList.contains('open'))return;
            if(mobileSheet.contains(e.target)||nav.contains(e.target))return;
            closeQuickSheet();
        },true);
        updateSimpleNavActive(isAdmin?'':'cover');
    }

    function observeOriginalNavigation(){
        root.addEventListener('click',e=>{
            const btn=e.target.closest?.('[data-panel-target]');
            if(!btn)return;
            const target=btn.dataset.panelTarget;
            updateLibraryHeader(target);updateSimpleNavActive(target);closeQuickSheet();
        },true);
    }

    function init(){
        setPanelNavLabels();
        simplifyComponents();
        simplifyCustomerData();
        buildSimpleMobileNav();
        observeOriginalNavigation();
        if(!isAdmin&&window.innerWidth>780)triggerPanel('cover');
        if(window.innerWidth<=780){$('.us-library')?.classList.remove('mobile-open');$('.us-properties-panel')?.classList.remove('mobile-open');}
        updateLibraryHeader(root.querySelector('.us-rail-btn.active')?.dataset.panelTarget||'template');
        try{window.dispatchEvent(new CustomEvent('undanganta:studio-simple-workflow-v3-ready'));}catch(_){ }
    }
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>setTimeout(init,30),{once:true});
    else setTimeout(init,30);
})();

</script>

<style id="undangantaCumulativeRepairV1Style">
/* UNDANGANTA_STUDIO_CUMULATIVE_REPAIR_V1_4_SAFE */
:where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-cumulative-essential-note{
    display:block!important;
    margin:6px 1px 0!important;
    color:#6E6E73!important;
    font-size:10.5px!important;
    line-height:1.35!important;
    font-weight:400!important;
}
:where(.studio-role-premium,.studio-role-royal) .us-simple-data-body [data-customer-media="couple_photo"]{
    display:none!important;
}
:where(.studio-role-premium,.studio-role-royal) .us-simple-data-body [data-customer-key="couple_names"]{
    display:none!important;
}
.us-cumulative-stale-media-note{
    display:flex!important;
    flex-direction:column!important;
    align-items:flex-start!important;
    gap:7px!important;
    margin:8px 0 0!important;
    padding:9px 10px!important;
    border:1px solid #E7C8C5!important;
    border-radius:10px!important;
    background:#FFFAF9!important;
    color:#713F3F!important;
    font-size:10.5px!important;
    line-height:1.4!important;
}
.us-cumulative-stale-clear{
    min-height:30px!important;
    padding:0 10px!important;
    border:1px solid #E7C8C5!important;
    border-radius:8px!important;
    background:#fff!important;
    color:#8F3F3F!important;
    font-size:10px!important;
    font-weight:600!important;
    cursor:pointer!important;
}
.us-cumulative-stale-clear:hover{background:#FFF5F4!important}
.us-cumulative-admin-note{
    display:block!important;
    margin:0 0 10px!important;
    padding:8px 10px!important;
    border:1px solid rgba(60,60,67,.10)!important;
    border-radius:10px!important;
    background:#F7F7F9!important;
    color:#6E6E73!important;
    font-size:10.5px!important;
    line-height:1.35!important;
}
@media(max-width:780px){
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-contextbar.crop-mode>#cropDoneBtn,
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-contextbar.crop-mode>#cropResetBtn{
        display:inline-flex!important;
        align-items:center!important;
        min-height:32px!important;
    }
    .us-wrap.studio-role-premium .us-pages-toggle,
    .us-wrap.studio-role-royal .us-pages-toggle,
    .us-wrap.studio-role-admin .us-pages-toggle{
        display:none!important;
    }
    .us-wrap.studio-role-premium .us-library.mobile-open~.us-workspace .us-pages-toggle,
    .us-wrap.studio-role-royal .us-library.mobile-open~.us-workspace .us-pages-toggle,
    .us-wrap.studio-role-admin .us-library.mobile-open~.us-workspace .us-pages-toggle,
    .us-wrap.studio-role-premium .us-properties-panel.mobile-open~.us-workspace .us-pages-toggle,
    .us-wrap.studio-role-royal .us-properties-panel.mobile-open~.us-workspace .us-pages-toggle,
    .us-wrap.studio-role-admin .us-properties-panel.mobile-open~.us-workspace .us-pages-toggle{
        display:none!important;
    }
}
#usSimpleMobileSheet .us-cumulative-sheet-heading{
    grid-column:1/-1!important;
    padding:3px 2px 0!important;
    color:#8E8E93!important;
    font-size:10px!important;
    font-weight:600!important;
}
</style>
<script id="undangantaCumulativeRepairV1Script">
/* UNDANGANTA_STUDIO_CUMULATIVE_REPAIR_V1_4_SAFE_RUNTIME */
(()=>{
    const root=document.querySelector('.us-wrap');
    if(!root)return;
    const isAdmin=root.classList.contains('studio-role-admin');
    const isPremium=root.classList.contains('studio-role-premium')||root.classList.contains('studio-role-royal');
    const $=(sel,scope=root)=>scope.querySelector(sel);

    function fieldFor(key){
        const escaped=CSS.escape(String(key));
        return $(`[data-customer-key="${escaped}"]`)?.closest('.us-customer-field')
            || $(`[data-customer-media="${escaped}"]`)?.closest('.us-customer-field')
            || null;
    }
    function setLabel(key,label){
        const field=fieldFor(key);if(!field)return null;
        const el=field.querySelector(':scope > label')||field.querySelector('label');
        if(el)el.textContent=label;
        return field;
    }
    function setEssentialNote(key,text){
        const field=fieldFor(key);if(!field)return;
        let note=field.querySelector(':scope > .us-cumulative-essential-note');
        if(!note){note=document.createElement('small');note.className='us-cumulative-essential-note';field.appendChild(note);}
        note.textContent=text;
    }
    function move(body,key){const field=fieldFor(key);if(field&&body)body.appendChild(field);}
    function repairDataOrder(){
        const group=$('[data-simple-data-group="couple"] .us-simple-data-body');
        if(!group)return;
        setLabel('groom_photo','Foto Mempelai Pria');
        setLabel('bride_photo','Foto Mempelai Wanita');
        setLabel('groom_name','Nama Mempelai Pria');
        setLabel('bride_name','Nama Mempelai Wanita');
        setLabel('couple_photo','Foto Bersama Mempelai');
        setLabel('couple_names','Nama Mempelai');
        setEssentialNote('groom_photo','Digunakan pada elemen foto mempelai pria di undangan.');
        setEssentialNote('bride_photo','Digunakan pada elemen foto mempelai wanita di undangan.');

        if(isPremium){
            const couplePhoto=fieldFor('couple_photo');if(couplePhoto)couplePhoto.dataset.usCustomerHiddenClarityV1='1';
            const coupleNames=fieldFor('couple_names');if(coupleNames)coupleNames.dataset.usCustomerHiddenClarityV1='1';
            ['groom_photo','bride_photo','groom_name','bride_name','couple_photo','couple_names'].forEach(key=>move(group,key));
        }else{
            ['groom_photo','bride_photo','groom_name','bride_name','couple_photo','couple_names'].forEach(key=>move(group,key));
        }
        try{if(typeof renderCustomerMediaSelects==='function')renderCustomerMediaSelects();}catch(_){ }
        try{interactionDiag('CUMULATIVE_DATA_ORDER_APPLIED',{role:isAdmin?'admin':'premium',photoFirst:true});}catch(_){ }
    }
    function repairAdminDataNote(){
        if(!isAdmin)return;
        const section=$('[data-panel-section="customer-data"]');if(!section)return;
        let note=section.querySelector(':scope > .us-cumulative-admin-note');
        if(!note){
            note=document.createElement('div');note.className='us-cumulative-admin-note';
            note.textContent='Data Uji hanya dipakai untuk mengecek binding dan pratinjau. Template master tidak berubah.';
            const card=section.querySelector('.us-customer-preview-card');
            section.insertBefore(note,card||section.firstChild);
        }
    }
    function repairLabels(){
        const map={components:'Komponen',text:'Teks',elements:'Elemen',uploads:'Media',gallery:'Galeri',fonts:'Font',animation:'Animasi',layers:'Susunan'};
        Object.entries(map).forEach(([target,label])=>{
            root.querySelectorAll(`[data-panel-target="${target}"]`).forEach(btn=>{
                if(btn.querySelector('*')){
                    [...btn.childNodes].filter(n=>n.nodeType===Node.TEXT_NODE).forEach(n=>n.remove());
                    btn.appendChild(document.createTextNode(label));
                }else btn.textContent=label;
                btn.setAttribute('aria-label',label);
            });
        });
    }
    function clickOriginal(selector){
        const el=document.querySelector(selector);if(el){el.click();return true;}return false;
    }
    function openDevicePreview(device){
        document.dispatchEvent(new CustomEvent('undanganta:open-responsive-preview',{detail:{device}}));
    }
    function augmentMobileMoreSheet(){
        if(window.innerWidth>780)return;
        const sheet=document.getElementById('usSimpleMobileSheet');
        if(!sheet||!sheet.classList.contains('open')||sheet.dataset.mode!=='more')return;
        if(sheet.querySelector('.us-cumulative-sheet-heading'))return;
        const heading=document.createElement('div');heading.className='us-cumulative-sheet-heading';heading.textContent='Pratinjau & alat halaman';sheet.appendChild(heading);
        const actions=[
            ['▱','Pratinjau HP',()=>openDevicePreview('mobile')],
            ['▭','Pratinjau Tablet',()=>openDevicePreview('tablet')],
            ['▰','Pratinjau Desktop',()=>openDevicePreview('desktop')],
            ['⌁',isAdmin?'Snap':'Ratakan otomatis',()=>clickOriginal('#toggleSnap')],
            ['□',isAdmin?'Safe Area':'Batas aman',()=>clickOriginal('#toggleSafeArea')],
            ['←','Halaman Sebelumnya',()=>clickOriginal('#prevCanvas')],
            ['→','Halaman Berikutnya',()=>clickOriginal('#nextCanvas')]
        ];
        actions.forEach(([icon,label,fn])=>{
            const btn=document.createElement('button');btn.type='button';
            btn.innerHTML=`<span class="sw-sheet-icon">${icon}</span><span>${label}</span>`;
            btn.addEventListener('click',()=>{fn();sheet.classList.remove('open');});
            sheet.appendChild(btn);
        });
    }
    function hookMobileMore(){
        const nav=document.getElementById('usSimpleMobileNav');if(!nav)return;
        const more=nav.querySelector('[data-simple-nav="more"]');
        if(!more||more.dataset.cumulativeHookV1==='1')return;
        more.dataset.cumulativeHookV1='1';
        more.addEventListener('click',()=>setTimeout(augmentMobileMoreSheet,0));
    }
    function apply(){
        repairLabels();repairDataOrder();repairAdminDataNote();hookMobileMore();
    }
    window.addEventListener('undanganta:studio-simple-workflow-v3-ready',()=>setTimeout(apply,0));
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>setTimeout(apply,90),{once:true});
    else setTimeout(apply,90);
    document.addEventListener('click',event=>{
        if(event.target?.closest?.('[data-panel-target="customer-data"],#openCustomerDataPanel'))setTimeout(apply,0);
    },true);
})();
</script>

<style>
/* UNDANGANTA_STUDIO_CUMULATIVE_REPAIR_V1_5_1_SAFE */
:root,
html,
body,
.us-wrap,
.us-responsive-preview,
.us-preview-overlay{
    color-scheme:light!important;
}
.us-frame-inner,
.us-static-layer .us-frame-inner,
.us-static-layer .us-media-cell:not(.empty),
.us-preview-page-host .us-media-cell:not(.empty){
    background:#fff!important;
}
.us-media-cell.empty{
    background:#F5F5F7!important;
}
button,
select,
input,
textarea,
.us-tool,
.us-rail-btn,
.us-device-btn,
.us-view-mode,
.us-simple-data-header{
    -webkit-tap-highlight-color:transparent!important;
}
select,
select:focus,
select:active{
    background-color:#F2F2F7!important;
    color:#1C1C1E!important;
}
select option{
    background:#fff!important;
    color:#1C1C1E!important;
}
button:active,
.us-tool:active,
.us-rail-btn:active,
.us-device-btn:active,
.us-view-mode:active,
.us-simple-data-header:active{
    filter:none!important;
    transform:none!important;
}
:where(.studio-role-premium,.studio-role-royal,.studio-role-admin) button:focus-visible,
:where(.studio-role-premium,.studio-role-royal,.studio-role-admin) select:focus-visible,
:where(.studio-role-premium,.studio-role-royal,.studio-role-admin) input:focus-visible,
:where(.studio-role-premium,.studio-role-royal,.studio-role-admin) textarea:focus-visible{
    outline:2px solid rgba(0,122,255,.24)!important;
    outline-offset:1px!important;
}
</style>

<style id="undangantaMobileShellPhase3Style">
/* UNDANGANTA MOBILE EDITOR SHELL — PHASE 3 */
#mobileTopMore,.us-page-thumb-preview,.us-mobile-add-page{display:none}

@media(max-width:780px){
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin){
        --phase3-top:48px;
        --phase3-pages:70px;
        --phase3-nav:60px;
    }

    /* Compact shared top bar. */
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-top{
        height:var(--phase3-top)!important;min-height:var(--phase3-top)!important;
        padding:6px 7px!important;gap:4px!important;background:#fff!important;
        border-bottom:1px solid rgba(60,60,67,.10)!important;box-shadow:none!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-top-center,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-save-status{display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-top-left{flex:0 0 auto!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #homeStudio{
        width:34px!important;min-width:34px!important;height:34px!important;padding:0!important;
        display:grid!important;place-items:center!important;border-radius:9px!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #homeStudio span:last-child{display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-top-right{
        flex:1 1 auto!important;min-width:0!important;justify-content:flex-end!important;gap:4px!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #undoBtn,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #redoBtn,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #mobileTopMore{
        display:inline-flex!important;align-items:center!important;justify-content:center!important;
        width:32px!important;min-width:32px!important;height:34px!important;padding:0!important;
        border:1px solid rgba(60,60,67,.10)!important;border-radius:9px!important;background:#fff!important;
        color:#3A3A3C!important;font-size:15px!important;box-shadow:none!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #previewBtn,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #saveBtn{
        height:34px!important;min-height:34px!important;padding:0 9px!important;border-radius:9px!important;
        font-size:9.5px!important;white-space:nowrap!important;box-shadow:none!important
    }

    /* Canvas keeps the remaining height and stays centered. */
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-body{
        height:calc(100% - var(--phase3-top))!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-workspace{
        height:100%!important;padding:0 0 var(--phase3-nav)!important;box-sizing:border-box!important;
        display:flex!important;flex-direction:column!important;background:#F5F5F7!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-canvas-zone{
        flex:1 1 auto!important;height:auto!important;min-height:0!important;
        display:flex!important;align-items:center!important;justify-content:flex-start!important;
        flex-direction:column!important;padding:10px 5px!important;overflow:auto!important;
        overscroll-behavior:contain!important;scrollbar-width:thin!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-editor-document{
        width:100%!important;min-height:0!important;margin:0 auto!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-live-pages{
        width:100%!important;align-items:center!important
    }

    /* Compact page strip: thumbnail, add, and page menu. */
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-pages-wrap{
        position:relative!important;inset:auto!important;display:block!important;visibility:visible!important;
        flex:0 0 var(--phase3-pages)!important;width:100%!important;height:var(--phase3-pages)!important;
        min-height:var(--phase3-pages)!important;max-height:var(--phase3-pages)!important;
        padding:7px 8px!important;box-sizing:border-box!important;overflow:hidden!important;
        border:0!important;border-top:1px solid rgba(60,60,67,.10)!important;border-radius:0!important;
        background:#fff!important;box-shadow:none!important;z-index:430!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-workspace.pages-collapsed .us-pages-wrap{display:block!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-pages{
        height:56px!important;min-height:56px!important;display:flex!important;align-items:center!important;
        gap:7px!important;overflow-x:auto!important;overflow-y:hidden!important;scrollbar-width:none!important;
        -webkit-overflow-scrolling:touch!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-pages::-webkit-scrollbar{display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-page-chip{
        position:relative!important;flex:0 0 116px!important;width:116px!important;height:54px!important;
        display:grid!important;grid-template-columns:31px minmax(0,1fr)!important;align-items:center!important;gap:7px!important;
        padding:4px 25px 4px 5px!important;border:1px solid rgba(60,60,67,.13)!important;
        border-radius:10px!important;background:#fff!important;box-shadow:none!important;text-align:left!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-page-chip.active{
        border-color:#007AFF!important;box-shadow:0 0 0 1px rgba(0,122,255,.16)!important;background:#fff!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-page-thumb-preview{
        width:29px!important;height:44px!important;display:grid!important;place-items:center!important;
        overflow:hidden!important;border:1px solid rgba(60,60,67,.12)!important;border-radius:5px!important;
        color:#6E6E73!important;font-size:9px!important;font-weight:650!important;background-size:cover!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-page-chip-copy{min-width:0!important;display:block!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-page-chip b{
        margin:0 0 3px!important;font-size:9px!important;font-weight:650!important;color:#1D1D1F!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-page-chip small{font-size:8px!important;color:#8E8E93!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-page-chip .us-page-type-badge{display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-page-more{
        right:3px!important;top:16px!important;width:22px!important;height:22px!important;border-radius:7px!important;
        background:transparent!important;color:#6E6E73!important;font-size:14px!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-transition-chip{display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-mobile-add-page{
        flex:0 0 44px!important;width:44px!important;height:54px!important;display:grid!important;place-items:center!important;
        padding:0!important;border:1px dashed rgba(60,60,67,.22)!important;border-radius:10px!important;
        background:#F8F8FA!important;color:#007AFF!important;font-size:22px!important;font-weight:400!important
    }

    /* Keep zoom usable without covering the strip. */
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-work-bottom{
        right:8px!important;bottom:calc(var(--phase3-nav) + var(--phase3-pages) + 8px)!important;
        width:auto!important;height:34px!important;z-index:420!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #zoomValue,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) #pageGridBtn{display:none!important}
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-zoom-btn,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-zoom-fit{
        min-width:30px!important;height:30px!important;border-radius:8px!important;box-shadow:none!important
    }

    /* Shared bottom-sheet surface for current and future panels. */
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-mobile-sheet-surface{
        overflow:hidden!important;border:0!important;border-top:1px solid rgba(60,60,67,.10)!important;
        border-radius:18px 18px 0 0!important;background:#fff!important;
        box-shadow:0 -4px 18px rgba(0,0,0,.07)!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-mobile-sheet-surface::before{
        content:'';position:absolute;left:50%;top:7px;z-index:2;width:34px;height:4px;
        transform:translateX(-50%);border-radius:999px;background:#D1D1D6;pointer-events:none
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-library,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-properties-panel{
        position:fixed!important;left:0!important;right:0!important;top:auto!important;
        bottom:var(--phase3-nav)!important;height:min(58dvh,480px)!important;max-height:calc(100% - 44px)!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-library-head,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-properties-head{
        height:54px!important;min-height:54px!important;padding-top:9px!important;box-sizing:border-box!important
    }
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-library-scroll,
    :where(.us-wrap.studio-role-premium,.us-wrap.studio-role-royal,.us-wrap.studio-role-admin) .us-properties-panel{
        overscroll-behavior:contain!important;scrollbar-width:thin!important
    }
    #usSimpleMobileSheet{
        left:0!important;right:0!important;bottom:var(--phase3-nav)!important;
        width:100%!important;max-height:min(44dvh,380px)!important;padding:20px 8px 8px!important;
        box-sizing:border-box!important;overflow-y:auto!important;border-radius:18px 18px 0 0!important;
        box-shadow:0 -4px 18px rgba(0,0,0,.07)!important
    }

    /* Five predictable primary actions. */
    #usSimpleMobileNav{
        height:var(--phase3-nav)!important;padding:2px 6px max(2px,env(safe-area-inset-bottom))!important;
        background:#fff!important;border-top:1px solid rgba(60,60,67,.10)!important;box-shadow:none!important
    }
    #usSimpleMobileNav button{height:56px!important;border-radius:0!important;background:transparent!important;padding:4px 2px 3px!important}
    #usSimpleMobileNav button.active{background:transparent!important;color:#007AFF!important}
    #usSimpleMobileNav button.active::before{
        content:'';display:block;width:18px;height:2px;margin:0 auto 2px;border-radius:2px;background:#007AFF
    }
    #usSimpleMobileNav button.active .sw-icon{height:18px!important;line-height:18px!important;margin-bottom:1px!important}

    #undangantaDiagExport{bottom:calc(var(--phase3-nav) + var(--phase3-pages) + 8px)!important}
}
</style>

<script id="undangantaMobileShellPhase3Script">
(()=>{
    const resetShellScroll=()=>{
        if(window.innerWidth>780)return;
        const body=document.querySelector('.us-body');
        if(body&&body.scrollTop!==0)body.scrollTop=0;
    };
    const bind=()=>{
        const more=document.getElementById('mobileTopMore');
        if(!more||more.dataset.phase3Bound==='1')return;
        more.dataset.phase3Bound='1';
        more.addEventListener('click',()=>{document.querySelector('#usSimpleMobileNav [data-simple-nav="more"]')?.click();resetShellScroll();});
        document.addEventListener('keydown',event=>{
            if(event.key!=='Escape')return;
            const sheet=document.getElementById('usSimpleMobileSheet');
            if(sheet?.classList.contains('open')){sheet.classList.remove('open');sheet.setAttribute('aria-hidden','true');}
            resetShellScroll();
        });
        document.addEventListener('click',event=>{
            if(event.target.closest?.('.us-panel-close,#usSimpleMobileNav,.us-mobile-sheet-surface'))requestAnimationFrame(resetShellScroll);
        });
        resetShellScroll();
    };
    window.addEventListener('undanganta:studio-simple-workflow-v3-ready',bind);
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>setTimeout(bind,80),{once:true});
    else setTimeout(bind,80);
})();
</script>

<style id="undangantaMobilePanelsPhase4Style">
/* UNDANGANTA MOBILE TEXT / ELEMENT / MEDIA PANELS — PHASE 4 */
.us-mobile-panel-search,.us-mobile-section-label,.us-mobile-element-components{display:none}
@media(max-width:780px){
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-panel-search{
        position:relative;display:block;margin:0 0 10px
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-panel-search>input{
        width:100%;height:38px;box-sizing:border-box;padding:0 11px;border:1px solid rgba(60,60,67,.16);
        border-radius:9px;background:#F5F5F7;color:#1D1D1F;font-size:12px;outline:none
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-panel-search>input:focus{
        border-color:#8AB8F8;background:#fff
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-element-tabs{
        display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:5px!important;margin-bottom:10px!important
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-element-tab{
        min-width:0!important;height:34px!important;padding:0 3px!important;border-radius:8px!important;font-size:9px!important
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-element-grid,
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-element-components{
        display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:7px!important
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-element-card,
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-element-components .us-component-card{
        min-width:0!important;min-height:82px!important;padding:7px 4px!important;border:1px solid rgba(60,60,67,.13)!important;
        border-radius:9px!important;background:#fff!important;box-shadow:none!important;font-size:9px!important
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-element-components .us-component-card>span:last-child{display:none!important}
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-element-components .us-component-icon{font-size:21px!important}
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-media-help{display:none!important}

    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-text-presets{
        display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-text-presets .us-tool{
        width:100%!important;min-height:42px!important;margin:0!important;border-radius:9px!important;background:#fff!important;
        border:1px solid rgba(60,60,67,.13)!important;box-shadow:none!important
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) [data-panel-section="text"] #textFields{display:none!important}
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-font-results{
        display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:5px;margin-top:6px;max-height:150px;overflow:auto
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-font-results[hidden]{display:none!important}
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-font-results button{
        height:34px;overflow:hidden;border:1px solid rgba(60,60,67,.13);border-radius:8px;background:#fff;
        color:#1D1D1F;font-size:10px;text-align:left;text-overflow:ellipsis;white-space:nowrap
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-section-label{
        display:block;margin:1px 0 7px;color:#6E6E73;font-size:10px;font-weight:650
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) .us-mobile-media-recent{margin-top:14px}
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) [data-panel-section="uploads"] .us-dropzone{
        min-height:84px!important;padding:11px!important;border-radius:10px!important;background:#F7F7F9!important
    }
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) [data-panel-section="uploads"] .us-dropzone span{font-size:9px!important}
    :where(.studio-role-premium,.studio-role-royal,.studio-role-admin) [data-panel-section="uploads"] .us-asset-grid{
        grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:7px!important
    }
}
</style>

<script id="undangantaMobilePanelsPhase4Script">
(()=>{
    const setup=()=>{
        const root=document.querySelector('.us-wrap');if(!root||root.dataset.phase4Panels==='1')return;
        root.dataset.phase4Panels='1';

        const componentHost=document.getElementById('mobileElementComponents');
        if(componentHost){
            document.querySelectorAll('[data-panel-section="components"] [data-component]').forEach(source=>{
                const clone=source.cloneNode(true);clone.removeAttribute('id');
                clone.addEventListener('click',()=>source.click());componentHost.appendChild(clone);
            });
        }

        const search=document.getElementById('elementSearch');
        const filterElements=()=>{
            const query=String(search?.value||'').trim().toLocaleLowerCase('id');
            const active=root.querySelector('[data-panel-section="elements"] [data-element-group]:not([hidden])');
            active?.querySelectorAll('button').forEach(button=>{button.hidden=!!query&&!button.textContent.toLocaleLowerCase('id').includes(query);});
        };
        search?.addEventListener('input',filterElements);
        root.querySelectorAll('[data-element-tab]').forEach(button=>button.addEventListener('click',filterElements));

        const fontSearch=document.getElementById('textFontSearch');
        const fontResults=document.getElementById('textFontResults');
        const fontSelect=document.getElementById('pFont');
        fontSearch?.addEventListener('input',()=>{
            const query=fontSearch.value.trim().toLocaleLowerCase('id');fontResults.replaceChildren();
            if(!query){fontResults.hidden=true;return;}
            [...fontSelect.options].filter(option=>option.textContent.toLocaleLowerCase('id').includes(query)).slice(0,12).forEach(option=>{
                const button=document.createElement('button');button.type='button';button.textContent=option.textContent;
                button.style.fontFamily=option.value;
                button.addEventListener('click',()=>{fontSelect.value=option.value;fontSelect.dispatchEvent(new Event('change',{bubbles:true}));fontSearch.value=option.textContent;fontResults.hidden=true;});
                fontResults.appendChild(button);
            });
            fontResults.hidden=!fontResults.children.length;
        });
    };
    window.addEventListener('undanganta:studio-simple-workflow-v3-ready',setup);
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>setTimeout(setup,90),{once:true});
    else setTimeout(setup,90);
})();
</script>
<style id="undangantaMobileContextPhase5Style">
/* UNDANGANTA MOBILE CONTEXTUAL TOOLBARS — PHASE 5 */
#usMobileContextToolbar,#usMobileContextSheet{display:none}
@media(max-width:780px){
    #usMobileContextToolbar{
        position:absolute;left:8px;right:8px;bottom:calc(var(--phase3-nav) + var(--phase3-pages) + 8px);z-index:426;
        height:46px;display:flex;align-items:center;gap:4px;padding:4px;box-sizing:border-box;overflow-x:auto;overflow-y:hidden;
        border:1px solid rgba(60,60,67,.12);border-radius:11px;background:#fff;box-shadow:0 3px 12px rgba(0,0,0,.06);scrollbar-width:none
    }
    #usMobileContextToolbar[hidden]{display:none!important}
    #usMobileContextToolbar::-webkit-scrollbar{display:none}
    #usMobileContextToolbar button{
        flex:0 0 auto;height:36px;padding:0 10px;border:0;border-radius:8px;background:transparent;color:#2C2C2E;
        font-size:10px;font-weight:650;white-space:nowrap
    }
    #usMobileContextToolbar button:active{background:#F2F2F7!important}
    .has-mobile-context .us-work-bottom{display:none!important}
    #usMobileContextSheet{
        position:fixed;left:0;right:0;bottom:var(--phase3-nav);z-index:760;display:block;max-height:min(42dvh,360px);
        overflow:hidden;border:0;border-top:1px solid rgba(60,60,67,.10);border-radius:18px 18px 0 0;background:#fff;
        box-shadow:0 -4px 18px rgba(0,0,0,.07);transform:translateY(calc(100% + 70px));transition:transform .2s ease
    }
    #usMobileContextSheet::before{content:'';position:absolute;left:50%;top:7px;width:34px;height:4px;transform:translateX(-50%);border-radius:99px;background:#D1D1D6}
    #usMobileContextSheet.open{transform:translateY(0)}
    .us-mobile-context-sheet-head{height:52px;display:flex;align-items:center;justify-content:space-between;padding:8px 12px 0;border-bottom:1px solid rgba(60,60,67,.09);box-sizing:border-box}
    .us-mobile-context-sheet-head b{font-size:13px;font-weight:650}
    .us-mobile-context-sheet-head button{height:32px;padding:0 8px;border:0;background:transparent;color:#007AFF;font-size:11px;font-weight:650}
    .us-mobile-context-sheet-body{max-height:calc(min(42dvh,360px) - 52px);padding:12px;box-sizing:border-box;overflow:auto}
    .us-mobile-range-row{display:grid;grid-template-columns:minmax(0,1fr) 54px;align-items:center;gap:10px}
    .us-mobile-range-row input{width:100%}
    .us-mobile-range-row output{height:34px;display:grid;place-items:center;border:1px solid rgba(60,60,67,.13);border-radius:8px;background:#F5F5F7;font-size:10px}
    .us-mobile-effect-row{display:grid;grid-template-columns:76px minmax(0,1fr) 48px;align-items:center;gap:8px;margin-bottom:10px;font-size:10px;color:#4b5563}
    .us-mobile-effect-row input{width:100%;min-width:0}
    .us-mobile-effect-row output{height:30px;display:grid;place-items:center;border:1px solid rgba(60,60,67,.13);border-radius:8px;background:#F5F5F7;font-size:10px;color:#2c2c2e}
    .us-mobile-color-row{display:flex;align-items:center;gap:10px}
    .us-mobile-color-row input{width:52px;height:40px;padding:3px;border:1px solid rgba(60,60,67,.14);border-radius:8px;background:#fff}
    .us-mobile-color-row span{font-size:11px;color:#6E6E73}
    .us-mobile-position-tabs{display:grid;grid-template-columns:repeat(3,1fr);gap:4px;margin-bottom:10px;padding:3px;border-radius:9px;background:#F2F2F7}
    .us-mobile-position-tabs button{height:32px;border:0;border-radius:7px;background:transparent;color:#636366;font-size:10px}
    .us-mobile-position-tabs button.active{background:#fff;color:#1D1D1F;box-shadow:0 1px 2px rgba(0,0,0,.06)}
    .us-mobile-position-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}
    .us-mobile-position-grid[hidden]{display:none!important}
    .us-mobile-position-grid button{min-height:38px;border:1px solid rgba(60,60,67,.13);border-radius:8px;background:#fff;color:#2C2C2E;font-size:10px}
    .us-mobile-context-option-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:7px}
    .us-mobile-context-option-grid button{min-height:38px;padding:6px;border:1px solid rgba(60,60,67,.13);border-radius:8px;background:#fff;color:#2C2C2E;font-size:10px}
    .us-mobile-context-option-grid button.active{border-color:#8AB8F8;background:#EEF5FF;color:#1959A6;font-weight:650}
}
</style>
<style id="undangantaDesktopProductivityPhase6Style">
/* UNDANGANTA DESKTOP PRODUCTIVITY — PHASE 6 */
.us-desktop-context-toolbar{
    min-height:44px;display:flex;align-items:center;justify-content:center;gap:4px;padding:5px 10px;box-sizing:border-box;
    overflow-x:auto;border-bottom:1px solid rgba(60,60,67,.10);background:#fff;scrollbar-width:none
}
.us-desktop-context-toolbar[hidden]{display:none!important}
.us-desktop-context-toolbar::-webkit-scrollbar{display:none}
.us-desktop-context-toolbar button{
    flex:0 0 auto;height:32px;padding:0 10px;border:1px solid transparent;border-radius:7px;background:transparent;
    color:#2c2c2e;font-size:11px;font-weight:650;cursor:pointer;white-space:nowrap
}
.us-desktop-context-toolbar button:hover{border-color:#dedfe3;background:#f5f5f7}
.us-desktop-context-kind,.us-desktop-context-count{flex:0 0 auto;margin-right:4px;color:#6e6e73;font-size:10px;font-weight:700}
.us-element-context-menu{width:252px}
.us-element-context-menu .us-element-menu-label{padding:3px 9px 5px;color:#777;font-size:9px;font-weight:750;text-transform:uppercase;letter-spacing:.04em}
.us-element-context-menu .us-element-menu-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:3px;padding:0 3px}
.us-element-context-menu .us-element-menu-grid button{height:32px;padding:0 7px;justify-content:flex-start;font-size:10px}
@media(min-width:781px){
    #usMobileContextSheet.open{
        position:fixed;right:22px;bottom:22px;z-index:10020;display:block;width:min(360px,calc(100vw - 44px));max-height:380px;
        overflow:hidden;border:1px solid rgba(60,60,67,.12);border-radius:12px;background:#fff;box-shadow:0 10px 32px rgba(0,0,0,.14)
    }
    #usMobileContextSheet .us-mobile-context-sheet-head{height:48px;display:flex;align-items:center;justify-content:space-between;padding:0 12px;border-bottom:1px solid rgba(60,60,67,.09)}
    #usMobileContextSheet .us-mobile-context-sheet-head b{font-size:12px}
    #usMobileContextSheet .us-mobile-context-sheet-head button{height:30px;border:0;background:transparent;color:#2563eb;font-size:11px;cursor:pointer}
    #usMobileContextSheet .us-mobile-context-sheet-body{max-height:330px;padding:12px;overflow:auto}
    #usMobileContextSheet .us-mobile-range-row{display:grid;grid-template-columns:minmax(0,1fr) 54px;align-items:center;gap:10px}
    #usMobileContextSheet .us-mobile-range-row input{width:100%}
    #usMobileContextSheet .us-mobile-range-row output{height:32px;display:grid;place-items:center;border:1px solid #dedfe3;border-radius:7px;background:#f5f5f7;font-size:10px}
    #usMobileContextSheet .us-mobile-effect-row{display:grid;grid-template-columns:76px minmax(0,1fr) 48px;align-items:center;gap:8px;margin-bottom:10px;font-size:10px;color:#4b5563}
    #usMobileContextSheet .us-mobile-effect-row input{width:100%;min-width:0}
    #usMobileContextSheet .us-mobile-effect-row output{height:30px;display:grid;place-items:center;border:1px solid #dedfe3;border-radius:7px;background:#f5f5f7;font-size:10px;color:#2c2c2e}
    #usMobileContextSheet .us-mobile-color-row{display:flex;align-items:center;gap:10px}
    #usMobileContextSheet .us-mobile-color-row input{width:52px;height:38px;padding:3px;border:1px solid #dedfe3;border-radius:7px;background:#fff}
    #usMobileContextSheet .us-mobile-color-row span{font-size:11px;color:#6e6e73}
    #usMobileContextSheet .us-mobile-position-tabs{display:grid;grid-template-columns:repeat(3,1fr);gap:3px;margin-bottom:9px;padding:3px;border-radius:8px;background:#f2f2f7}
    #usMobileContextSheet .us-mobile-position-tabs button,#usMobileContextSheet .us-mobile-position-grid button,#usMobileContextSheet .us-mobile-context-option-grid button{min-height:34px;border:1px solid #dedfe3;border-radius:7px;background:#fff;color:#2c2c2e;font-size:10px;cursor:pointer}
    #usMobileContextSheet .us-mobile-position-tabs button.active,#usMobileContextSheet .us-mobile-context-option-grid button.active{border-color:#8ab8f8;background:#eef5ff;color:#1959a6}
    #usMobileContextSheet .us-mobile-position-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px}
    #usMobileContextSheet .us-mobile-position-grid[hidden]{display:none!important}
    #usMobileContextSheet .us-mobile-context-option-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}
}
@media(max-width:780px){.us-desktop-context-toolbar,.us-element-context-menu{display:none!important}}
</style>
@endsection
