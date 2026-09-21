@extends('layouts.app')

@section('content')
@php
    $canvasState = $template->canvas ?: [];

    $initialAssets = $assets
        ->map(function ($asset) {
            return [
                'id' => $asset->id,
                'type' => $asset->type,
                'name' => $asset->name,
                'url' => route('admin.studio.assets.file', $asset),
            ];
        })
        ->values()
        ->all();

    $initialFonts = $fonts
        ->map(function ($font) {
            return [
                'id' => $font->id,
                'name' => $font->name,
                'family' => $font->family,
                'url' => route('admin.studio.fonts.file', $font),
                'weight' => $font->weight,
                'style' => $font->style,
            ];
        })
        ->values()
        ->all();

    $initialPreviewInstance = $previewInstance
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
    .us-preview-device.desktop{width:min(1180px,96vw);height:min(720px,86vh);display:grid;grid-template-columns:minmax(0,var(--cover-width,64%)) minmax(320px,1fr)}
    .us-preview-device.tablet{width:min(760px,90vw);height:min(900px,86vh);border-radius:16px}
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
        .us-preview-device.tablet,.us-preview-device.desktop{width:calc(100vw - 12px)!important;height:calc(100dvh - 62px)!important;max-width:none!important;border-radius:10px!important}
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

</style>

<div class="us-wrap">
    <div class="us-top">
        <div class="us-top-left">
            <button id="homeStudio" class="us-icon-btn" type="button" title="Kembali">⌂</button>
        </div>
        <div class="us-top-center"><div class="us-top-title">{{ $template->name }} — UNDANGANTA Studio</div></div>
        <div class="us-top-right">
            <span id="saveStatus" class="us-save-status"><i id="saveDot" class="us-autosave-dot"></i><span id="saveStatusText">Siap</span></span>
            <button id="undoBtn" class="us-btn" type="button">↶</button>
            <button id="redoBtn" class="us-btn" type="button">↷</button>
            <button id="previewBtn" class="us-btn" type="button">Preview</button>
            <button id="saveBtn" class="us-btn primary" type="button">Simpan</button>
        </div>
    </div>

    <div class="us-body library-collapsed properties-collapsed">
        <nav class="us-rail" aria-label="Editor tools">
            <button class="us-rail-btn active" data-panel-target="template" type="button"><span class="ico">▦</span>Template</button>
            <button class="us-rail-btn" data-panel-target="customer-data" type="button"><span class="ico">♙</span>Data Uji</button>
            <button class="us-rail-btn" data-panel-target="components" type="button"><span class="ico">◇</span>Komponen</button>
            <button class="us-rail-btn" data-panel-target="text" type="button"><span class="ico">T</span>Teks</button>
            <button class="us-rail-btn" data-panel-target="elements" type="button"><span class="ico">✦</span>Elemen</button>
            <button class="us-rail-btn" data-panel-target="uploads" type="button"><span class="ico">☁</span>Unggahan</button>
            <button class="us-rail-btn" data-panel-target="fonts" type="button"><span class="ico">Aa</span>Font</button>
            <button class="us-rail-btn" data-panel-target="animation" type="button"><span class="ico">◌</span>Animasi</button>
            <button class="us-rail-btn" data-panel-target="layers" type="button"><span class="ico">☷</span>Layer</button>
            <div class="us-rail-spacer"></div>
        </nav>

        <button id="toggleLibrary" class="us-library-collapse" type="button" aria-label="Buka atau tutup panel alat">‹</button>

        <aside class="us-library">
            <div class="us-library-head"><span>UNDANGANTA Studio</span><button id="mobileCloseLibrary" class="us-mobile-sheet-close" type="button" aria-label="Tutup panel">✕</button></div>
            <div class="us-library-scroll">
                <section data-panel-section="template">
                    <div class="us-h">Template</div>
                    <div class="us-search"><input type="text" placeholder="Cari template..." disabled></div>
                    <div class="us-small" style="margin-bottom:12px">Editor visual tanpa coding. Semua elemen di template ini dapat diposisikan bebas.</div>
                    <button id="openCustomerDataPanel" class="us-tool" type="button" style="width:100%;margin-bottom:10px">Buka Data Pelanggan Uji</button>
                    <div class="us-meta us-template-meta" style="display:grid;gap:9px">
                        <div class="us-field"><label>Nama Template <span class="us-required">*</span></label><input id="tplName" class="us-name" value="{{ $template->name }}" required aria-required="true" placeholder="Contoh: Elegant Blue"></div>
                        <div class="us-field"><label>Slug Template <span class="us-required">*</span></label><input id="tplSlug" class="us-slug" value="{{ $template->slug }}" required aria-required="true" placeholder="contoh-elegant-blue"></div>
                        <div class="us-field"><label>Paket Minimum <span class="us-required">*</span></label><select id="tplPlan" required aria-required="true"><option value="free" @selected($template->min_plan==='free')>Free</option><option value="premium" @selected($template->min_plan==='premium')>Premium</option><option value="pro" @selected($template->min_plan==='pro')>Pro</option></select></div>
                        <label class="us-check"><input id="tplEditable" type="checkbox" @checked($template->is_customer_editable)> Pelanggan boleh mengedit</label>
                        <div class="us-responsive-card">
                            <div class="us-h">Layout Undangan</div>
                            <div class="us-field"><label>Desktop</label>
                                <select id="desktopLayout">
                                    <option value="cover-left">Sampul Tetap Kiri + Isi Scroll Kanan</option>
                                    <option value="cover-right">Isi Scroll Kiri + Sampul Tetap Kanan</option>
                                    <option value="centered">Single Column Tengah</option>
                                </select>
                            </div>
                            <div class="us-field"><label>Lebar sampul desktop</label><input id="desktopCoverWidth" type="range" min="40" max="70" step="1"></div>
                            <div class="us-field"><label>Breakpoint mobile</label><select id="mobileBreakpoint"><option value="640">640 px</option><option value="768">768 px</option><option value="900">900 px</option></select></div>
                            <div class="us-small">Sampul desktop adalah area khusus yang tetap diam. Hanya isi undangan yang di-scroll.</div>
                        </div>

                        <div class="us-responsive-card us-opening-cover-card">
                            <div class="us-h">Sampul Pembuka</div>
                            <div class="us-small" style="margin-bottom:8px">Layar pertama sebelum isi undangan. Tombol membuka undangan.</div>
                            <label class="us-check"><input id="openingCoverEnabled" type="checkbox" checked> Tampilkan Sampul Pembuka</label>
                            <div class="us-field"><label>Teks Atas</label><input id="openingCoverEyebrow" type="text" value="The Wedding of"></div>
                            <div class="us-field"><label>Nama Mempelai <span class="us-required">*</span></label><input id="openingCoverNames" type="text" value="Nama & Nama" placeholder="Nama & Nama"></div>
                            <label class="us-check"><input id="openingCoverBindNames" type="checkbox" checked> Hubungkan otomatis ke Nama Pasangan</label>
                            <div class="us-field"><label>Teks Tombol</label><input id="openingCoverButtonText" type="text" value="Buka Undangan"></div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Warna Teks</label><input id="openingCoverTextColor" type="color" value="#ffffff"></div>
                                <div class="us-field"><label>Warna Tombol</label><input id="openingCoverButtonColor" type="color" value="#2F6FED"></div>
                            </div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Ukuran Nama</label><input id="openingCoverNameSize" type="range" min="24" max="84" step="1" value="46"></div>
                                <div class="us-field"><label>Posisi Vertikal</label><input id="openingCoverY" type="range" min="20" max="80" step="1" value="50"></div>
                            </div>
                            <div class="us-opening-position-row">
                                <button id="centerOpeningCover" class="us-mini-tool" type="button" title="Posisikan konten sampul tepat di tengah">Posisi Tengah</button>
                            </div>
                            <div class="us-grid2">
                                <div class="us-field"><label>Animasi Masuk</label>
                                    <select id="openingCoverAnimation">
                                        <option value="fade-up">Fade Up</option>
                                        <option value="fade">Fade</option>
                                        <option value="zoom">Zoom</option>
                                        <option value="soft-scale">Soft Scale</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div class="us-field"><label>Durasi</label><input id="openingCoverDuration" type="number" min="0.2" max="3" step="0.1" value="0.8"></div>
                            </div>
                            <button id="previewOpeningCover" class="us-tool us-tool-primary" type="button">▶ Preview Sampul Pembuka</button>
                            <div class="us-small" style="margin-top:7px">Media latar memakai JPG / PNG / MP4 dari Sampul Desktop di bawah.</div>
                        </div>

                        <div class="us-responsive-card us-desktop-cover-card">
                            <div class="us-h">Sampul Desktop</div>
                            <div id="desktopCoverPreview" class="us-desktop-cover-preview"><div class="us-desktop-cover-empty">Belum ada foto sampul desktop</div></div>
                            <input id="desktopCoverFile" type="file" accept="image/png,image/jpeg,video/mp4" hidden>
                            <div id="desktopCoverDropzone" class="us-cover-dropzone" tabindex="0">
                                <div class="us-cover-dropzone-icon">▣</div>
                                <b>Upload Sampul Desktop</b>
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

                        

                    </div>
                </section>

                <section data-panel-section="customer-data" hidden>
                    <div class="us-h">Data Pelanggan Uji</div>
                    <div class="us-small" style="margin-bottom:10px">Gunakan panel ini untuk menguji binding tanpa mengubah master template.</div>
                    <div class="us-customer-preview-card">
                            <div class="us-customer-preview-head">
                                <div class="us-small">Instance pelanggan untuk preview admin.</div>
                                <span id="customerInstanceState" class="us-instance-state">Belum dibuat</span>
                            </div>

                            <div class="us-customer-grid">
                                <div class="us-customer-field"><label>Nama pria</label><input data-customer-key="groom_name" type="text"></div>
                                <div class="us-customer-field"><label>Nama wanita</label><input data-customer-key="bride_name" type="text"></div>
                                <div class="us-customer-field wide"><label>Nama pasangan</label><input data-customer-key="couple_names" type="text"></div>
                                <div class="us-customer-field"><label>Tanggal acara</label><input data-customer-key="event_date" type="date"></div>
                                <div class="us-customer-field"><label>Lokasi</label><input data-customer-key="venue_name" type="text"></div>
                                <div class="us-customer-field wide"><label>Opening</label><textarea data-customer-key="opening_text"></textarea></div>
                                <div class="us-customer-field wide"><label>Quote</label><textarea data-customer-key="quote"></textarea></div>
                                <div class="us-customer-field wide"><label>Doa</label><textarea data-customer-key="prayer"></textarea></div>
                                <div class="us-customer-field wide"><label>Closing</label><textarea data-customer-key="closing_text"></textarea></div>

                                <div class="us-customer-field"><label>Foto pria</label><select data-customer-media="groom_photo"></select></div>
                                <div class="us-customer-field"><label>Foto wanita</label><select data-customer-media="bride_photo"></select></div>
                                <div class="us-customer-field wide"><label>Foto pasangan</label><select data-customer-media="couple_photo"></select></div>
                                <div class="us-customer-field"><label>Galeri 1</label><select data-customer-media="gallery_1"></select></div>
                                <div class="us-customer-field"><label>Galeri 2</label><select data-customer-media="gallery_2"></select></div>
                                <div class="us-customer-field"><label>Galeri 3</label><select data-customer-media="gallery_3"></select></div>
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
                    <div class="us-element-tabs">
                        <button class="us-element-tab active" type="button" data-element-tab="shapes">Bentuk</button>
                        <button class="us-element-tab" type="button" data-element-tab="frames">Bingkai</button>
                        <button class="us-element-tab" type="button" data-element-tab="grids">Kisi</button>
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
                    <button class="us-tool" id="addText" type="button" style="width:100%;margin-bottom:10px">+ Tambahkan kotak teks</button>
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
                    <div class="us-h">Unggahan</div>
                    <div class="us-upload" style="padding:0;border:0;background:transparent">
                        <input id="assetFile" type="file" accept="image/png,image/jpeg,image/webp,image/gif,video/mp4,video/webm" hidden>
                        <div id="assetDropzone" class="us-dropzone" tabindex="0">
                            <div class="us-dropzone-icon">⇧</div>
                            <b>Upload Foto / Video</b>
                            <span>Klik atau tarik file ke sini · JPG, PNG, WebP, GIF, MP4, WebM</span>
                            <div id="assetUploadProgress" class="us-upload-progress"><i></i></div>
                        </div>
                        <button id="assetUploadBtn" class="us-tool" style="width:100%;margin-top:8px;display:none" type="button">Upload</button>
                    </div>
                    <div id="assetGrid" class="us-asset-grid"></div>
                </section>

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
                    <div class="us-h">Layers Canvas Aktif</div><div id="layerList" class="us-list"></div>
                </section>

                <section data-panel-section="animation" hidden>
                    <div class="us-h">Animasi & Transisi</div>
                    <div class="us-responsive-card">
                        <div class="us-h">Animasi Layer Terpilih</div>
                        <div id="animationSelectedHint" class="us-small">Pilih layer di canvas atau panel Layer.</div>
                        <div class="us-animation-preset-grid">
                            <button class="us-animation-preset" type="button" data-animation-preset="none">None</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="fade">Fade</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="fade-up">Fade Up</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="fade-down">Fade Down</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="fade-left">Fade Left</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="fade-right">Fade Right</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="zoom">Zoom</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="pop">Pop</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="soft-scale">Soft Scale</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="blur-in">Blur In</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="reveal-up">Reveal Up</button>
                            <button class="us-animation-preset" type="button" data-animation-preset="float">Float</button>
                        </div>
                        <div class="us-grid2">
                            <div class="us-field"><label>Trigger</label><select id="animTrigger"><option value="on-enter">Saat masuk layar</option><option value="on-load">Saat halaman dibuka</option><option value="after-previous">Setelah layer sebelumnya</option><option value="with-previous">Bersamaan layer sebelumnya</option></select></div>
                            <div class="us-field"><label>Easing</label><select id="animEasing"><option value="ease-out">Ease Out</option><option value="ease-in-out">Ease In Out</option><option value="linear">Linear</option><option value="cubic-bezier(.2,.8,.2,1)">Smooth</option></select></div>
                        </div>
                        <button id="previewSelectedAnimation" class="us-btn" type="button" style="width:100%">▶ Preview layer</button>
                    </div>

                    <div class="us-field"><label>Nama Canvas <span class="us-required">*</span></label><input id="pageName" type="text" required></div>
                    <div class="us-field"><label>Warna Canvas</label><input id="canvasBg" type="color"></div>
                    <div class="us-responsive-card">
                        <div class="us-h">Jenis Halaman</div>
                        <div class="us-field"><label>Peran</label>
                            <select id="pageRole">
                                <option value="hero">Hero / Cover Mobile</option>
                                <option value="section">Section Scroll</option>
                                
                            </select>
                        </div>
                        <div class="us-grid2">
                            <div class="us-field"><label>Lebar desain</label><input id="pageWidth" type="number" min="280" max="1920" step="1"></div>
                            <div class="us-field"><label>Tinggi desain</label><input id="pageHeight" type="number" min="300" max="2200" step="1"></div>
                        </div>
                        <button id="applyRecommendedSize" class="us-tool" type="button" style="width:100%">Gunakan ukuran rekomendasi</button>
                        <div class="us-small" style="margin-top:7px">Canvas undangan: rekomendasi 390×844. Sampul desktop diatur terpisah dan tidak ikut scroll.</div>
                    </div>

                    <details class="us-advanced-folder">
                        <summary>Transisi Canvas — Pengaturan Lanjutan</summary>
                        <div class="us-advanced-folder-body">
                            <div class="us-field"><label>Jenis</label><select id="pageTransition"><option value="none">None</option><option value="fade">Fade</option><option value="slide-up">Slide Up</option><option value="slide-down">Slide Down</option><option value="slide-left">Slide Left</option><option value="slide-right">Slide Right</option><option value="zoom">Zoom</option><option value="blur">Blur Fade</option></select></div>
                            <div class="us-grid2"><div class="us-field"><label>Durasi</label><input id="pageDuration" type="number" min="0.1" max="5" step="0.1"></div><div class="us-field"><label>Easing</label><select id="pageEasing"><option value="ease">Ease</option><option value="ease-in">Ease In</option><option value="ease-out">Ease Out</option><option value="ease-in-out">Ease In Out</option><option value="linear">Linear</option></select></div></div>
                            <button id="previewTransitionSide" class="us-tool" type="button" style="width:100%">▶ Preview Transition</button>
                        </div>
                    </details>
                    <div class="us-page-actions" style="margin-top:8px"><button id="duplicatePage" class="us-tool" type="button">Duplicate</button><button id="deletePage" class="us-tool" type="button">Hapus Canvas</button></div>
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
                    <button class="us-view-mode active" type="button" data-view-mode="sample">Contoh</button>
                    <button class="us-view-mode" type="button" data-view-mode="empty">Kosong</button>
                    <button class="us-view-mode" type="button" data-view-mode="customer">Pelanggan</button>
                </div>
                <button id="toggleSnap" class="us-btn active" type="button" aria-pressed="true">Snap</button>
<button id="toggleSafeArea" class="us-btn active" type="button" aria-pressed="true">Safe Area</button>
            <div class="us-device-switch">
                    <button class="us-device-btn" type="button" data-preview-device="mobile">📱 Mobile</button>
                    <button class="us-device-btn" type="button" data-preview-device="tablet">▯ Tablet</button>
                    <button class="us-device-btn" type="button" data-preview-device="desktop">🖥 Desktop</button>
                </div>
                
            </div>
            <div class="us-canvas-zone">
                <div id="canvasShell" class="us-canvas-shell"><div id="canvas" class="us-canvas"></div></div>
                <button id="addPageMain" class="us-add-page-main" type="button">＋ Tambah halaman</button>
            </div>
            <div id="pagesPanel" class="us-pages-wrap"><div id="pagesBar" class="us-pages"></div></div>
            <button id="togglePagesPanel" class="us-pages-toggle" type="button" aria-expanded="true" title="Buka/tutup daftar canvas">Canvas ▾</button>
            <div class="us-work-bottom"><span id="zoomValue">68%</span><input id="zoomRange" type="range" min="25" max="120" value="68"><span>Halaman</span><span id="bottomCounter">—</span><button id="pageGridBtn" class="us-grid-view-btn" type="button" aria-label="Lihat semua halaman">▦</button></div>
        </main>

        <button id="toggleProperties" class="us-properties-collapse" type="button" aria-label="Buka atau tutup panel properti">›</button>

        <aside class="us-properties-panel">
            <div class="us-properties-head"><b>Properti</b><button id="mobileCloseProps" class="us-mobile-sheet-close" type="button" aria-label="Tutup properti">✕</button></div>
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
<div class="us-h">Responsive & Safe Area</div>
<div class="us-check"><input id="safeAreaEnabled" type="checkbox" checked><span>Tampilkan Safe Area</span></div>
<div class="us-grid2">
<div class="us-field"><label>Margin Aman</label><input id="safeAreaMargin" type="number" min="0" max="80" step="1" value="20"></div>
<div class="us-field"><label>Responsif</label><select id="responsiveMode"><option value="scale">Scale</option><option value="fixed">Fixed</option></select></div>
</div>
<div class="us-check"><input id="keepInsideCanvas" type="checkbox" checked><span>Jaga elemen di dalam canvas</span></div>
<div class="us-check"><input id="respectSafeArea" type="checkbox"><span>Batasi ke Safe Area</span></div>
<button id="fitSelectionSafe" class="us-mini-tool" type="button">Masukkan Selection ke Safe Area</button>
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
                <div class="us-h">Hubungkan ke Data</div>
                    <div class="us-field"><label>Hubungkan ke Data</label><select id="pBinding"></select></div>
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
                    <div class="us-field"><label>Cell aktif</label><select id="pMediaCell"></select></div>
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
                    <div class="us-media-help">Pilih foto dari panel Unggahan untuk mengisi cell aktif. Foto tetap berada di dalam bingkai/kisi saat template dipakai pelanggan.</div>
                </div>
                <div class="us-grid2"><div class="us-field"><label>X</label><input id="pX" type="number"></div><div class="us-field"><label>Y</label><input id="pY" type="number"></div></div>
                <div class="us-grid2"><div class="us-field"><label>Width</label><input id="pW" type="number"></div><div class="us-field"><label>Height</label><input id="pH" type="number"></div></div>
                <div class="us-grid2"><div class="us-field"><label>Rotate</label><input id="pRot" type="number"></div><div class="us-field"><label>Opacity</label><input id="pOpacity" type="number" min="0" max="1" step="0.05"></div></div>
                <div class="us-grid2"><div id="elementColorField" class="us-field"><label>Warna</label><input id="pBg" type="color"><div class="us-color-live-note">Warna elemen berubah realtime.</div></div><div class="us-field"><label>Radius</label><input id="pRadius" type="number"></div></div>
                <div class="us-field"><label>Animasi Layer</label><select id="pAnimation"><option value="none">None</option><option value="fade">Fade</option><option value="fade-up">Fade Up</option><option value="fade-down">Fade Down</option><option value="fade-left">Fade Left</option><option value="fade-right">Fade Right</option><option value="zoom">Zoom</option><option value="pop">Pop</option><option value="soft-scale">Soft Scale</option><option value="blur-in">Blur In</option><option value="reveal-up">Reveal Up</option><option value="float">Float</option></select></div>
                <div class="us-grid2"><div class="us-field"><label>Duration</label><input id="pDuration" type="number" step="0.1"></div><div class="us-field"><label>Delay</label><input id="pDelay" type="number" step="0.1"></div></div>
                <label class="us-check"><input id="pLoop" type="checkbox"> Loop animation</label><label class="us-check"><input id="pLocked" type="checkbox"> Lock</label><label class="us-check"><input id="pHidden" type="checkbox"> Hide</label>
                <div class="us-page-actions" style="margin-top:10px"><button id="bringFront" class="us-btn" type="button">Ke Depan</button><button id="sendBack" class="us-btn" type="button">Ke Belakang</button><button id="duplicateLayer" class="us-btn" type="button">Duplicate</button><button id="clearSampleContent" class="us-btn" type="button">Kosongkan Contoh</button><button id="deleteLayer" class="us-btn danger" type="button">Delete</button></div>
            </div>
        </aside>

        
        <div id="responsivePreview" class="us-preview-overlay" aria-hidden="true">
            <div class="us-preview-top">
                <b>Responsive Preview</b>
                <span id="responsivePreviewLabel" class="us-small"></span>
                <span class="spacer"></span>
                <button id="previewMobileBtn" class="us-device-btn" type="button">📱 Mobile</button>
                <button id="previewTabletBtn" class="us-device-btn" type="button">▯ Tablet</button>
                <button id="previewDesktopBtn" class="us-device-btn" type="button">🖥 Desktop</button>
                <button id="closeResponsivePreview" class="us-preview-close" type="button">Tutup</button>
            </div>
            <div id="responsivePreviewStage" class="us-preview-stage"></div>
        </div>

<nav class="us-mobile-nav" aria-label="Tools mobile">
            <button data-panel-target="template" type="button"><span class="ico">▦</span>Template</button>
            <button data-panel-target="customer-data" type="button"><span class="ico">♙</span>Data Uji</button>
            <button data-panel-target="components" type="button"><span class="ico">◇</span>Komponen</button>
            <button data-panel-target="text" type="button"><span class="ico">T</span>Teks</button>
            <button data-panel-target="elements" type="button"><span class="ico">✦</span>Elemen</button>
            <button data-panel-target="uploads" type="button"><span class="ico">☁</span>Unggahan</button>
            <button data-panel-target="fonts" type="button"><span class="ico">Aa</span>Font</button>
            <button data-panel-target="animation" type="button"><span class="ico">◌</span>Animasi</button>
            <button data-panel-target="layers" type="button"><span class="ico">☷</span>Layer</button>
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
    const csrf = @json(csrf_token());
    const updateUrl = @json(route('admin.studio.update', $template));
    const uploadAssetUrl = @json(route('admin.studio.assets.upload', $template));
    const uploadFontUrl = @json(route('admin.studio.fonts.upload'));
    const ensurePreviewInstanceUrl = @json(route('admin.studio.preview-instance.ensure', $template));
    const resetPreviewInstanceUrl = @json(route('admin.studio.preview-instance.reset', $template));
    const initialPreviewInstance = @json($initialPreviewInstance);
    const initialAssets = @json($initialAssets);
    const initialFonts = @json($initialFonts);
    let state = @json($canvasState);

    const $ = id => document.getElementById(id);
    const uid = p => p + '-' + Math.random().toString(36).slice(2,9);
    const clamp = (v,min,max) => Math.max(min,Math.min(max,v));
    const esc = value => String(value ?? '').replace(/[&<>'"]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[ch]));
    const deep = value => JSON.parse(JSON.stringify(value));

    function normalizeLayer(l){
        const layer=Object.assign({id:uid('layer'),type:'shape',name:'Layer',x:50,y:50,width:180,height:120,rotation:0,opacity:1,zIndex:1,background:'#BFD8FF',borderRadius:0,locked:false,hidden:false,animation:'none',duration:.8,delay:0,loop:false,shapeKind:'rect',frameKind:'square',gridKind:'4',gap:4,cells:[],binding:'none',customerEditPolicy:'full',optional:false,hideWhenEmpty:false,galleryAnimation:'fade',galleryAutoplay:false,galleryInterval:4,galleryLightbox:true,galleryThumbnails:true,animationTrigger:'on-enter',animationEasing:'ease-out',componentType:null},l||{});
        layer.cells=Array.isArray(layer.cells)?layer.cells.map(c=>Object.assign({src:null,posX:0,posY:0,scale:1},c||{})):[];
        return layer;
    }
    function normalizePage(p,index){
        const page = Object.assign({id:uid('canvas'),name:'Canvas '+(index+1),background:'#ffffff',role:index===0?'hero':'section',width:390,height:844,guides:null,transition:{type:'fade',duration:.8,easing:'ease-in-out'},layers:[]},p||{});
        if(page.role==='desktop-cover')page.role=index===0?'hero':'section';
        if(!Array.isArray(page.guides)||page.guides.length<2){const h=Number(page.height||844);page.guides=[Math.round(h*.34),Math.round(h*.68)];}
        page.guides=page.guides.slice(0,2).map(v=>Math.max(70,Math.min(Number(page.height||844)-70,Number(v||0)))).sort((a,b)=>a-b);
        page.transition = Object.assign({type:'fade',duration:.8,easing:'ease-in-out'},page.transition||{});
        page.layers = Array.isArray(page.layers) ? page.layers.map(normalizeLayer) : [];
        return page;
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
    let lasso = null, groupDrag = null, groupResize = null, layerClipboard = [], inlineTextEdit = null;
    let snapEnabled=true,snapGuides=[];
    let safeAreaEnabled=true,safeAreaMargin=20,keepInsideCanvas=true,respectSafeArea=false;
    let textClickArm={id:null,time:0};
    let editorViewMode='sample';
    let customerPreviewInstance=initialPreviewInstance;
    let customerData=Object.assign({
        groom_name:'',bride_name:'',couple_names:'',event_date:'',venue_name:'',
        quote:'',prayer:'',opening_text:'',closing_text:'',
        groom_photo:null,bride_photo:null,couple_photo:null,gallery_1:null,gallery_2:null,gallery_3:null
    },initialPreviewInstance?.content||{});
    let customerSaveTimer=null,customerSaveInFlight=false,customerSavePending=false;
    const activePointers=new Map(); let lastTap={time:0,id:null,cell:null};

    const canvas = $('canvas'), layerList = $('layerList'), props = $('properties'), empty = $('propertiesEmpty'), toast = $('toast');

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
    let autosaveTimer=null, saveInFlight=false, pendingSave=false;
    function setSaveStatus(text,state=''){const t=$('saveStatusText');if(t)t.textContent=text;const d=$('saveDot');if(d)d.className='us-autosave-dot '+state;}
    function markDirty(){dirty=true;setSaveStatus('Belum disimpan','');clearTimeout(autosaveTimer);autosaveTimer=setTimeout(()=>saveTemplate({silent:true}),1200);}
    function notify(msg){toast.textContent=msg;toast.classList.add('show');setTimeout(()=>toast.classList.remove('show'),1800);}

    function animationCss(l){
        if(!preview||l.animation==='none')return '';
        const map={
            'fade':'usFade','fade-up':'usFadeUp','fade-down':'usFadeDown','fade-left':'usFadeLeft','fade-right':'usFadeRight',
            'zoom':'usZoom','pop':'usPop','soft-scale':'usSoftScale','blur-in':'usBlurIn','reveal-up':'usRevealUp','float':'usFloat'
        };
        const name=map[l.animation];if(!name)return '';
        const easing=l.animationEasing||'ease-out';
        return `${name} ${Number(l.duration||.8)}s ${easing} ${Number(l.delay||0)}s ${l.loop?'infinite':'1'} both`;
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
        while(l.cells.length<count)l.cells.push({src:null,posX:0,posY:0,scale:1});
        if(l.cells.length>count)l.cells=l.cells.slice(0,count);
    }
    function cellImageHtml(cell){
        if(!cell?.src)return '';
        const tx=Number(cell.posX||0),ty=Number(cell.posY||0),sc=Math.max(.5,Number(cell.scale||1));
        return `<img src="${esc(cell.src)}" alt="" draggable="false" style="position:absolute;left:50%;top:50%;width:100%;height:100%;object-fit:cover;transform:translate(calc(-50% + ${tx}px),calc(-50% + ${ty}px)) scale(${sc});transform-origin:center center">`;
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
        const cells=l.cells.map((c,i)=>`<div class="us-media-cell ${c?.src?'':'empty'} ${activeCellIndex===i&&l.id===selectedId?'selected-cell':''}" data-cell-index="${i}">${cellImageHtml(c)}</div>`).join('');
        return `<div class="us-grid-inner ${cls}" style="gap:${Math.max(0,Number(l.gap||0))}px;border-radius:${Math.max(0,Number(l.borderRadius||0))}px">${cells}</div>`;
    }


    const TEXT_BINDINGS=[
        ['none','Tidak terhubung'],
        ['groom_name','Nama mempelai pria'],
        ['bride_name','Nama mempelai wanita'],
        ['couple_names','Nama pasangan'],
        ['event_date','Tanggal acara'],
        ['venue_name','Nama lokasi'],
        ['quote','Quote'],
        ['prayer','Doa'],
        ['opening_text','Kalimat pembuka'],
        ['closing_text','Kalimat penutup']
    ];
    const MEDIA_BINDINGS=[
        ['none','Tidak terhubung'],
        ['groom_photo','Foto mempelai pria'],
        ['bride_photo','Foto mempelai wanita'],
        ['couple_photo','Foto pasangan'],
        ['gallery_1','Galeri foto 1'],
        ['gallery_2','Galeri foto 2'],
        ['gallery_3','Galeri foto 3']
    ];
    function bindingOptionsFor(l){
        if(l.type==='text')return TEXT_BINDINGS;
        if(l.type==='frame'||l.type==='image'||l.type==='grid')return MEDIA_BINDINGS;
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
        if(!list.length)return;pushHistory();const layers=page().layers,sel=new Set(list.map(l=>l.id)),sorted=[...layers].sort((a,b)=>(a.zIndex||0)-(b.zIndex||0));
        if(mode==='front'){const base=Math.max(0,...layers.map(l=>Number(l.zIndex||0)))+1;list.forEach((l,i)=>l.zIndex=base+i);}
        else if(mode==='back'){const base=Math.min(0,...layers.map(l=>Number(l.zIndex||0)))-list.length;list.forEach((l,i)=>l.zIndex=base+i);}
        else{const order=sorted.map(l=>l.id);
            if(mode==='forward')for(let i=order.length-2;i>=0;i--)if(sel.has(order[i])&&!sel.has(order[i+1]))[order[i],order[i+1]]=[order[i+1],order[i]];
            if(mode==='backward')for(let i=1;i<order.length;i++)if(sel.has(order[i])&&!sel.has(order[i-1]))[order[i],order[i-1]]=[order[i-1],order[i]];
            order.forEach((id,i)=>{const l=layers.find(x=>x.id===id);if(l)l.zIndex=i+1;});
        }markDirty();render();
    }
    function alignSelection(action){
        const list=selectedLayers().filter(l=>!l.locked);if(list.length<2){notify('Pilih minimal 2 elemen');return;}
        pushHistory();const b=selectionBounds();
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
    function clearSnapGuides(){snapGuides=[];canvas.querySelectorAll('.us-snap-guide').forEach(x=>x.remove());}
    function drawSnapGuides(guides=[]){
        clearSnapGuides();snapGuides=guides;guides.forEach(g=>{const el=document.createElement('div');el.className='us-snap-guide '+g.axis;if(g.axis==='vertical')el.style.left=g.pos+'px';else el.style.top=g.pos+'px';canvas.appendChild(el);});
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
    function renderSafeArea(){canvas.querySelectorAll('.us-safe-area').forEach(x=>x.remove());if(!safeAreaEnabled)return;const b=currentSafeBounds(),el=document.createElement('div');el.className='us-safe-area';el.style.left=b.left+'px';el.style.top=b.top+'px';el.style.width=Math.max(0,b.width)+'px';el.style.height=Math.max(0,b.height)+'px';canvas.appendChild(el);}
    function clampAllLayersToCanvas(p){if(!p?.layers)return;const w=Number(p.width||390),h=Number(p.height||844);p.layers.forEach(l=>{l.width=Math.min(Number(l.width||1),w);l.height=Math.min(Number(l.height||1),h);l.x=clamp(Number(l.x||0),0,Math.max(0,w-l.width));l.y=clamp(Number(l.y||0),0,Math.max(0,h-l.height));});}

    function selectionBounds(ids=[...selectedIds]){
        const list=page().layers.filter(l=>ids.includes(l.id)&&!l.hidden);if(!list.length)return null;
        const left=Math.min(...list.map(l=>Number(l.x||0))),top=Math.min(...list.map(l=>Number(l.y||0)));
        const right=Math.max(...list.map(l=>Number(l.x||0)+Number(l.width||0))),bottom=Math.max(...list.map(l=>Number(l.y||0)+Number(l.height||0)));
        return {left,top,right,bottom,width:right-left,height:bottom-top,list};
    }
    function appendGroupSelectionChrome(){
        if(preview||selectedIds.size<2)return;const b=selectionBounds();if(!b)return;
        const box=document.createElement('div');box.className='us-group-box';box.dataset.groupSelection='1';box.style.left=b.left+'px';box.style.top=b.top+'px';box.style.width=b.width+'px';box.style.height=b.height+'px';
        ['nw','ne','se','sw'].forEach(pos=>{const h=document.createElement('div');h.className='us-group-handle';h.dataset.groupHandle=pos;box.appendChild(h);});
        const badge=document.createElement('div');badge.className='us-group-badge';badge.textContent=`${selectedIds.size} elemen`;box.appendChild(badge);canvas.appendChild(box);
    }
    function canvasPoint(clientX,clientY){const r=canvas.getBoundingClientRect();return {x:(clientX-r.left)*(Number(page().width||390)/r.width),y:(clientY-r.top)*(Number(page().height||844)/r.height)};}
    function beginLasso(e){const pt=canvasPoint(e.clientX,e.clientY);lasso={startX:pt.x,startY:pt.y,x:pt.x,y:pt.y,base:e.shiftKey?new Set(selectedIds):new Set()};if(!e.shiftKey)clearSelection();renderCanvas();const box=document.createElement('div');box.className='us-lasso-box';box.id='usLassoBox';canvas.appendChild(box);updateLassoVisual();}
    function updateLassoVisual(){
        if(!lasso)return;const left=Math.min(lasso.startX,lasso.x),top=Math.min(lasso.startY,lasso.y),right=Math.max(lasso.startX,lasso.x),bottom=Math.max(lasso.startY,lasso.y);
        const box=$('usLassoBox');if(box){box.style.left=left+'px';box.style.top=top+'px';box.style.width=(right-left)+'px';box.style.height=(bottom-top)+'px';}
        const next=new Set(lasso.base);page().layers.forEach(l=>{if(l.hidden||l.locked)return;const x=Number(l.x||0),y=Number(l.y||0),r=x+Number(l.width||0),b=y+Number(l.height||0);if(!(r<left||x>right||b<top||y>bottom))next.add(l.id);});
        selectedIds=next;syncPrimarySelection();canvas.querySelectorAll('.us-layer').forEach(el=>el.classList.toggle('multi-selected',selectedIds.has(el.dataset.id)));
    }
    function finishLasso(){if(!lasso)return;lasso=null;syncPrimarySelection();render();}
    function startGroupMove(e){const b=selectionBounds();if(!b)return;pushHistory();groupDrag={startX:e.clientX,startY:e.clientY,sx:canvasScale().sx,sy:canvasScale().sy,items:b.list.map(l=>({id:l.id,x:l.x,y:l.y}))};}
    function applyGroupMove(e){if(!groupDrag)return;const dx=(e.clientX-groupDrag.startX)*groupDrag.sx,dy=(e.clientY-groupDrag.startY)*groupDrag.sy;groupDrag.items.forEach(i=>{const l=page().layers.find(x=>x.id===i.id);if(l&&!l.locked){const p=constrainLayerPosition(l,i.x+dx,i.y+dy);l.x=p.x;l.y=p.y;}});markDirty();render();}
    function startGroupResize(e,handle){const b=selectionBounds();if(!b||b.width<=0||b.height<=0)return;pushHistory();groupResize={handle,startX:e.clientX,startY:e.clientY,sx:canvasScale().sx,sy:canvasScale().sy,bounds:b,items:b.list.map(l=>({id:l.id,x:l.x,y:l.y,width:l.width,height:l.height}))};}
    function applyGroupResize(e){
        if(!groupResize)return;const g=groupResize,dx=(e.clientX-g.startX)*g.sx,dy=(e.clientY-g.startY)*g.sy;let L=g.bounds.left,T=g.bounds.top,R=g.bounds.right,B=g.bounds.bottom;
        if(g.handle.includes('e'))R=Math.max(L+20,g.bounds.right+dx);if(g.handle.includes('w'))L=Math.min(R-20,g.bounds.left+dx);if(g.handle.includes('s'))B=Math.max(T+20,g.bounds.bottom+dy);if(g.handle.includes('n'))T=Math.min(B-20,g.bounds.top+dy);
        const sx=(R-L)/g.bounds.width,sy=(B-T)/g.bounds.height;g.items.forEach(i=>{const l=page().layers.find(x=>x.id===i.id);if(!l||l.locked)return;l.x=Math.round(L+((i.x-g.bounds.left)/g.bounds.width)*(R-L));l.y=Math.round(T+((i.y-g.bounds.top)/g.bounds.height)*(B-T));l.width=Math.max(10,Math.round(i.width*sx));l.height=Math.max(10,Math.round(i.height*sy));});markDirty();render();
    }
    function duplicateSelectedLayers(){const list=selectedLayers();if(!list.length)return false;pushHistory();const maxZ=Math.max(0,...page().layers.map(x=>x.zIndex||0)),ids=[],groupMap=new Map();list.forEach((l,i)=>{if(l.locked)return;const c=deep(l);c.id=uid(l.type);c.name=(l.name||l.type)+' Copy';c.x+=14;c.y+=14;c.zIndex=maxZ+i+1;if(c.groupId){if(!groupMap.has(c.groupId))groupMap.set(c.groupId,groupId());c.groupId=groupMap.get(c.groupId);}page().layers.push(c);ids.push(c.id);});selectedIds=new Set(ids);selectedId=ids.at(-1)||null;markDirty();render();return true;}
    function copySelectedLayers(){const list=selectedLayers();if(!list.length)return false;layerClipboard=deep(list);notify(`${list.length} elemen disalin`);return true;}
    function pasteSelectedLayers(){if(!layerClipboard.length)return false;pushHistory();const maxZ=Math.max(0,...page().layers.map(x=>x.zIndex||0)),ids=[],groupMap=new Map();layerClipboard.forEach((l,i)=>{const c=deep(l);c.id=uid(l.type);c.name=(l.name||l.type)+' Copy';c.x+=18;c.y+=18;c.zIndex=maxZ+i+1;if(c.groupId){if(!groupMap.has(c.groupId))groupMap.set(c.groupId,groupId());c.groupId=groupMap.get(c.groupId);}page().layers.push(c);ids.push(c.id);});selectedIds=new Set(ids);selectedId=ids.at(-1)||null;markDirty();render();return true;}
    function deleteSelectedLayers(){const ids=[...selectedIds].filter(id=>!page().layers.find(l=>l.id===id)?.locked);if(!ids.length)return false;pushHistory();page().layers=page().layers.filter(l=>!ids.includes(l.id));clearSelection();markDirty();render();return true;}

    function appendSelectionChrome(el,l){
        if(l.id!==selectedId||selectedIds.size!==1||preview)return;
        ['nw','n','ne','e','se','s','sw','w'].forEach(pos=>{
            const h=document.createElement('div');h.className='us-selection-handle';h.dataset.handle=pos;el.appendChild(h);
        });
        const stem=document.createElement('div');stem.className='us-rotate-stem';el.appendChild(stem);
        const rot=document.createElement('div');rot.className='us-rotate-handle';rot.dataset.handle='rotate';el.appendChild(rot);
        if(cropMode&&cropMode.layerId===l.id){
            el.classList.add('crop-active');
            const b=document.createElement('div');b.className='us-crop-badge';b.textContent='Crop foto';el.appendChild(b);
            const hint=document.createElement('div');hint.className='us-crop-hint';hint.textContent='Geser foto • scroll / pinch untuk zoom';el.appendChild(hint);
            const tools=document.createElement('div');tools.className='us-crop-toolbar';
            tools.innerHTML=`<button type="button" data-crop-action="zoom-out">−</button><button type="button" data-crop-action="zoom-in">＋</button><button type="button" data-crop-action="reset">Reset</button><button type="button" class="done" data-crop-action="done">Selesai</button>`;
            el.appendChild(tools);
        }
    }


    function previewSingleLayer(id){
        const l=page().layers.find(x=>x.id===id);if(!l||!l.animation||l.animation==='none'){notify('Layer belum punya animasi');return;}
        const el=canvas.querySelector(`.us-layer[data-id="${id}"]`);if(!el)return;
        const wasPreview=preview;preview=true;el.style.animation='none';void el.offsetWidth;el.style.animation=animationCss(l);
        setTimeout(()=>{preview=wasPreview;render();},Math.max(900,(Number(l.duration||.8)+Number(l.delay||0))*1000+250));
    }


    function bindingPlaceholder(l){
        const label=bindingLabel(l.binding||'none',l);
        return label&&label!=='Tidak terhubung'?label:'Isi pelanggan';
    }
    function customerTextValue(binding){
        const raw=customerData?.[binding];
        if(raw===null||raw===undefined)return '';
        return typeof raw==='string'||typeof raw==='number'?String(raw):'';
    }
    function customerMediaValue(binding){
        const raw=customerData?.[binding];
        if(!raw)return '';
        if(typeof raw==='string')return raw;
        if(typeof raw==='object')return raw.url||'';
        return '';
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
        if(['image','frame','grid'].includes(l.type))return !effectiveMediaSrc(l);
        return false;
    }

    function renderCanvas(){
        const p=page();const pw=Number(p.width||state.width||390),ph=Number(p.height||state.height||844);canvas.style.background=p.background;canvas.style.width=pw+'px';canvas.style.height=ph+'px';$('canvasShell').style.width=`calc(${pw}px * var(--us-zoom))`;$('canvasShell').style.height=`calc(${ph}px * var(--us-zoom))`;canvas.innerHTML='';
        [...p.layers].sort((a,b)=>(a.zIndex||0)-(b.zIndex||0)).forEach(l=>{
            if(shouldHideLayerForMode(l))return;
            const isMulti=selectedIds.has(l.id);const el=document.createElement('div');el.className='us-layer'+(l.id===selectedId&&selectedIds.size===1?' selected':'')+(isMulti&&selectedIds.size>1?' multi-selected':'')+(l.groupId?' group-member':'')+(safeAreaEnabled&&isLayerOutsideSafe(l)?' outside-safe':'')+(l.locked?' locked':'')+(l.hidden?' hidden':'');el.dataset.id=l.id;el.dataset.editPolicy=l.customerEditPolicy||'full';
            el.style.left=l.x+'px';el.style.top=l.y+'px';el.style.width=l.width+'px';el.style.height=l.height+'px';el.style.opacity=l.opacity;el.style.zIndex=l.zIndex;el.style.transform=`rotate(${l.rotation||0}deg)`;el.style.animation=animationCss(l);
            const content=document.createElement('div');content.className='us-layer-content';content.style.borderRadius=(l.borderRadius||0)+'px';
            if(l.type==='text'){
                content.classList.add('us-text');content.style.fontFamily=l.fontFamily||'Arial';content.style.fontSize=(l.fontSize||28)+'px';content.style.fontWeight=l.fontWeight||400;content.style.fontStyle=l.fontStyle||'normal';content.style.color=l.color||'#222';content.style.textAlign=l.textAlign||'left';content.style.justifyContent=l.textAlign==='center'?'center':(l.textAlign==='right'?'flex-end':'flex-start');content.style.background=l.background||'transparent';
                const shownText=effectiveText(l);
                if(shownText){content.textContent=shownText;}
                else if(l.binding&&l.binding!=='none'){content.innerHTML=`<div class="us-empty-binding">${esc(bindingPlaceholder(l))}</div>`;}
                else content.textContent='';
            } else if(l.type==='image'){
                const override=effectiveMediaSrc(l);
                if(editorViewMode!=='sample'&&l.binding&&l.binding!=='none'){
                    content.innerHTML=override?`<img class="us-image" src="${esc(override)}" alt="">`:`<div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div>`;
                }else content.innerHTML=`<img class="us-image" src="${esc(l.src)}" alt="">`;
            }
            else if(l.type==='video') content.innerHTML=`<video class="us-video" src="${esc(l.src)}" autoplay muted loop playsinline></video>`;
            else if(l.type==='frame'){
                content.classList.add('us-frame-layer');content.style.background='transparent';
                const override=effectiveMediaSrc(l);
                if(editorViewMode!=='sample'&&l.binding&&l.binding!=='none'){
                    if(override){
                        ensureCells(l,1);
                        const clone=deep(l);clone.cells[0]=Object.assign({},clone.cells[0]||{posX:0,posY:0,scale:1},{src:override});
                        content.innerHTML=renderFrameContent(clone);
                    }else content.innerHTML=`<div class="us-frame-inner"><div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div></div>`;
                }else content.innerHTML=renderFrameContent(l);
            }
            else if(l.type==='grid'){
                content.classList.add('us-grid-layer');content.style.background='transparent';content.dataset.galleryAnimation=l.galleryAnimation||'fade';
                const override=effectiveMediaSrc(l);
                if(editorViewMode!=='sample'&&l.binding&&l.binding!=='none'){
                    if(override){
                        const clone=deep(l),count=cellCountForGrid(clone.gridKind||'4');ensureCells(clone,count);
                        clone.cells[0]=Object.assign({},clone.cells[0]||{posX:0,posY:0,scale:1},{src:override});
                        content.innerHTML=renderGridContent(clone);
                    }else content.innerHTML=`<div class="us-grid-inner us-grid-4"><div class="us-empty-binding media" style="grid-column:1/-1;grid-row:1/-1">${esc(bindingPlaceholder(l))}</div></div>`;
                }else content.innerHTML=renderGridContent(l);
            }
            else {content.classList.add('us-shape');shapeStyle(l,content);}
            el.appendChild(content);appendSelectionChrome(el,l);canvas.appendChild(el);
        });
        appendGroupSelectionChrome();
        if(!preview){
            (p.guides||[]).slice(0,2).forEach((y,i)=>{
                const g=document.createElement('div');g.className='us-guide-line';g.dataset.guideIndex=String(i);g.dataset.label=i===0?'Batas 1':'Batas 2';g.style.top=y+'px';
                const h=document.createElement('div');h.className='us-guide-handle';h.dataset.guideIndex=String(i);g.appendChild(h);canvas.appendChild(g);
            });
        }
    }
    function renderLayers(){
        layerList.innerHTML='';[...page().layers].sort((a,b)=>(b.zIndex||0)-(a.zIndex||0)).forEach(l=>{const row=document.createElement('div');row.className='us-layer-row'+(selectedIds.has(l.id)?' active':'');row.dataset.id=l.id;row.innerHTML=`<b>${l.hidden?'◌':(l.locked?'🔒':'◇')}</b><span>${esc(l.name||l.type)}${l.binding&&l.binding!=='none'?`<span class="us-binding-badge">${esc(bindingLabel(l.binding,l))}</span>`:''}${l.animation&&l.animation!=='none'?`<span class="us-layer-anim-badge">◌ ${esc(l.animation)}</span>`:''}</span><small>${l.zIndex}</small>`;layerList.appendChild(row);});
    }
    function rebuildFontSelect(){const s=$('pFont'),cur=selected()?.fontFamily||'Arial';s.innerHTML=['Arial','Georgia','Times New Roman','Verdana'].map(f=>`<option value="${f}">${f}</option>`).join('')+fonts.map(f=>`<option value="${esc(f.family)}">${esc(f.name)}</option>`).join('');s.value=cur;}
    function renderProps(){
        const p=page();$('pageName').value=p.name;$('canvasBg').value=(p.background&&p.background.startsWith('#'))?p.background:'#ffffff';$('pageTransition').value=p.transition.type||'fade';$('pageDuration').value=p.transition.duration||.8;$('pageEasing').value=p.transition.easing||'ease-in-out';$('pageRole').value=p.role||'section';$('pageWidth').value=Number(p.width||390);$('pageHeight').value=Number(p.height||844);$('desktopLayout').value=state.settings?.desktopLayout||'cover-left';$('desktopCoverWidth').value=Number(state.settings?.desktopCoverWidth||64);$('mobileBreakpoint').value=String(state.settings?.mobileBreakpoint||768);
        const l=selected();if(l&&$('responsiveMode'))$('responsiveMode').value=l.responsiveMode||'scale';
        const multiTools=$('multiTools');if(multiTools)multiTools.hidden=selectedIds.size<2;if(selectedIds.size>1){props.hidden=true;empty.hidden=false;empty.innerHTML=`<b>${selectedIds.size} elemen dipilih</b><div class="us-multi-note">Geser/resize bersama, align, distribute, atau group dari panel Properti.</div>`;return;}
        if(!l){props.hidden=true;empty.hidden=false;empty.textContent='Pilih elemen di canvas untuk mengedit propertinya.';return;}
        props.hidden=false;empty.hidden=true;
        $('pName').value=l.name||'';$('pX').value=l.x;$('pY').value=l.y;$('pW').value=l.width;$('pH').value=l.height;$('pRot').value=l.rotation;$('pOpacity').value=l.opacity;$('pBg').value=(l.background&&l.background.startsWith('#'))?l.background:'#BFD8FF';$('pRadius').value=l.borderRadius;$('elementColorField').hidden=l.type!=='shape';$('pAnimation').value=l.animation;$('pDuration').value=l.duration;$('pDelay').value=l.delay;$('pLoop').checked=!!l.loop;$('pLocked').checked=!!l.locked;$('pHidden').checked=!!l.hidden;$('textFields').hidden=l.type!=='text';$('animTrigger').value=l.animationTrigger||'on-enter';$('animEasing').value=l.animationEasing||'ease-out';$('animationSelectedHint').textContent=`${l.name||l.type} • ${l.animation||'none'} • ${Number(l.duration||.8).toFixed(1)}s`;document.querySelectorAll('[data-animation-preset]').forEach(b=>b.classList.toggle('active',b.dataset.animationPreset===(l.animation||'none')));
        const bindingOptions=bindingOptionsFor(l);$('pBinding').innerHTML=bindingOptions.map(([v,label])=>`<option value="${v}">${label}</option>`).join('');$('pBinding').value=bindingOptions.some(x=>x[0]===l.binding)?l.binding:'none';
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
            $('pMediaCell').innerHTML=Array.from({length:count},(_,i)=>`<option value="${i}">Cell ${i+1}</option>`).join('');$('pMediaCell').value=String(activeCellIndex);
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
            chip.innerHTML=`<b>${i+1}. ${esc(p.name)} <span class="us-page-type-badge">${p.role==='desktop-cover'?'DESKTOP':p.role==='hero'?'HERO':'SECTION'}</span></b><small>${p.width||390}×${p.height||844}</small><span class="us-page-more" data-page-more="${p.id}" aria-label="Menu halaman">•••</span>`;
            bar.appendChild(chip);
            if(i<state.pages.length-1){const t=document.createElement('div');t.className='us-transition-chip';t.innerHTML=`<button type="button" class="us-transition-chip-btn" data-transition-page="${p.id}">↔ ${esc(p.transition?.type||'none')}</button>`;bar.appendChild(t);}
        });
        const add=document.createElement('button');add.type='button';add.className='us-add-page';add.id='addPage';add.textContent='+ Tambah Canvas';bar.appendChild(add);
        const idx=state.pages.findIndex(p=>p.id===activePageId);$('canvasCounter').textContent=`Canvas ${idx+1} / ${state.pages.length}`;if($('bottomCounter'))$('bottomCounter').textContent=`${idx+1}/${state.pages.length}`;$('prevCanvas').disabled=idx<=0;$('nextCanvas').disabled=idx>=state.pages.length-1;$('deletePage').disabled=state.pages.length<=1;
        renderPageGrid();
    }
    function render(){renderCanvas();renderLayers();renderSafeArea();renderProps();renderPages();renderDesktopCoverPanel();syncOpeningCoverControls();}

    function updateSelected(key,value){const l=selected();if(!l)return;pushHistory();l[key]=value;markDirty();render();}

    function nextZ(){return Math.max(0,...page().layers.map(l=>l.zIndex||0))+1;}
    function addComponent(type){
        pushHistory();const p=page(),pw=Number(p.width||390);let z=nextZ(),created=[];
        const push=l=>{const n=normalizeLayer(Object.assign({zIndex:z++},l));p.layers.push(n);created.push(n);return n;};
        if(type==='couple'){
            push({type:'frame',frameKind:'circle',componentType:'couple',name:'Foto Mempelai Pria',binding:'groom_photo',customerEditPolicy:'content',x:38,y:150,width:135,height:135,cells:[{src:null,posX:0,posY:0,scale:1}]});
            push({type:'frame',frameKind:'circle',componentType:'couple',name:'Foto Mempelai Wanita',binding:'bride_photo',customerEditPolicy:'content',x:217,y:150,width:135,height:135,cells:[{src:null,posX:0,posY:0,scale:1}]});
            push({type:'text',componentType:'couple',name:'Nama Pasangan',binding:'couple_names',customerEditPolicy:'content',text:'Nama Pria & Nama Wanita',x:35,y:305,width:320,height:65,fontFamily:'Georgia',fontSize:30,color:'#2d2926',textAlign:'center',background:'transparent',animation:'fade-up'});
        }else if(type==='quote'||type==='prayer'){
            const isPrayer=type==='prayer';
            push({type:'text',componentType:type,name:isPrayer?'Doa':'Quote',binding:isPrayer?'prayer':'quote',customerEditPolicy:'content',optional:true,text:isPrayer?'Tuliskan doa untuk kedua mempelai di sini.':'“Tuliskan quote pilihan di sini.”',x:38,y:220,width:314,height:180,fontFamily:'Georgia',fontSize:isPrayer?21:24,color:'#332d28',textAlign:'center',background:'transparent',animation:'fade-up'});
        }else if(type==='event'){
            push({type:'text',componentType:'event',name:'Judul Acara',text:'Akad & Resepsi',x:45,y:150,width:300,height:50,fontFamily:'Georgia',fontSize:28,color:'#2d2926',textAlign:'center',background:'transparent',animation:'fade-up'});
            push({type:'text',componentType:'event',name:'Tanggal Acara',binding:'event_date',customerEditPolicy:'content',text:'Tanggal Acara',x:45,y:220,width:300,height:42,fontFamily:'Arial',fontSize:18,color:'#403a35',textAlign:'center',background:'transparent',animation:'fade',delay:.15});
            push({type:'text',componentType:'event',name:'Lokasi Acara',binding:'venue_name',customerEditPolicy:'content',text:'Nama Lokasi',x:45,y:275,width:300,height:48,fontFamily:'Arial',fontSize:16,color:'#403a35',textAlign:'center',background:'transparent',animation:'fade',delay:.25});
        }else if(type==='gallery'){
            push({type:'grid',gridKind:'4',componentType:'gallery',name:'Galeri Mempelai',binding:'gallery_1',customerEditPolicy:'content',x:30,y:130,width:330,height:420,gap:6,borderRadius:8,cells:Array.from({length:4},()=>({src:null,posX:0,posY:0,scale:1})),galleryAnimation:'fade',galleryLightbox:true,galleryThumbnails:true,animation:'fade-up'});
        }else if(type==='story'){
            push({type:'text',componentType:'story',name:'Love Story — Judul',text:'Our Story',x:45,y:145,width:300,height:50,fontFamily:'Georgia',fontSize:28,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'fade-up'});
            push({type:'text',componentType:'story',name:'Love Story — Isi',text:'Ceritakan perjalanan kalian di sini.',x:45,y:215,width:300,height:210,fontFamily:'Arial',fontSize:16,color:'#403a35',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'fade',delay:.15});
        }else if(type==='location'){
            push({type:'text',componentType:'location',name:'Lokasi',binding:'venue_name',customerEditPolicy:'content',text:'Nama Lokasi Acara',x:45,y:180,width:300,height:60,fontFamily:'Georgia',fontSize:25,color:'#2d2926',textAlign:'center',background:'transparent',animation:'fade-up'});
            push({type:'shape',shapeKind:'rounded',componentType:'location',name:'Tombol Maps',x:110,y:270,width:170,height:48,background:'#2F6FED',borderRadius:24,customerEditPolicy:'locked',animation:'fade',delay:.2});
            push({type:'text',componentType:'location',name:'Label Maps',text:'Buka Google Maps',x:120,y:278,width:150,height:30,fontFamily:'Arial',fontSize:14,color:'#ffffff',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'fade',delay:.2});
        }else if(type==='countdown'){
            push({type:'shape',shapeKind:'rounded',componentType:'countdown',name:'Countdown Card',x:35,y:170,width:320,height:180,background:'#EEF5FF',borderRadius:18,customerEditPolicy:'locked',animation:'fade-up'});
            push({type:'text',componentType:'countdown',name:'Countdown Title',text:'Menuju Hari Bahagia',x:60,y:195,width:270,height:40,fontFamily:'Georgia',fontSize:24,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'fade-up'});
            push({type:'text',componentType:'countdown',name:'Countdown Runtime',text:'00 Hari · 00 Jam · 00 Menit · 00 Detik',x:55,y:260,width:280,height:52,fontFamily:'Arial',fontSize:14,color:'#5e6673',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'fade',delay:.15});
        }else if(type==='wishes'){
            push({type:'shape',shapeKind:'rounded',componentType:'wishes',name:'Wishes Card',x:30,y:125,width:330,height:430,background:'#F7FAFF',borderRadius:18,customerEditPolicy:'locked',animation:'fade-up'});
            push({type:'text',componentType:'wishes',name:'Wishes Title',text:'Ucapan & Doa',x:55,y:155,width:280,height:45,fontFamily:'Georgia',fontSize:27,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'fade-up'});
            push({type:'text',componentType:'wishes',name:'Wishes Runtime',text:'Ucapan tamu akan tampil otomatis di sini.',x:60,y:230,width:270,height:90,fontFamily:'Arial',fontSize:14,color:'#667085',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'fade',delay:.15});
        }else if(type==='music'){
            push({type:'shape',shapeKind:'circle',componentType:'music',name:'Music Control',x:155,y:220,width:80,height:80,background:'#2F6FED',borderRadius:40,customerEditPolicy:'locked',animation:'fade-up'});
            push({type:'text',componentType:'music',name:'Music Icon',text:'♫',x:170,y:235,width:50,height:48,fontFamily:'Arial',fontSize:28,color:'#ffffff',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'fade'});
        }else if(type==='guest_photo'){
            push({type:'shape',shapeKind:'rounded',componentType:'guest_photo',name:'Guest Photo Card',x:30,y:125,width:330,height:430,background:'#F7FAFF',borderRadius:18,customerEditPolicy:'locked',animation:'fade-up'});
            push({type:'text',componentType:'guest_photo',name:'Guest Photo Title',text:'Bagikan Momenmu',x:55,y:155,width:280,height:45,fontFamily:'Georgia',fontSize:27,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'fade-up'});
            push({type:'text',componentType:'guest_photo',name:'Guest Photo Runtime',text:'Form upload foto tamu akan tampil di undangan publik.',x:60,y:230,width:270,height:90,fontFamily:'Arial',fontSize:14,color:'#667085',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'fade',delay:.15});
        }else if(type==='closing'){
            push({type:'text',componentType:'closing',name:'Penutup',binding:'closing_text',customerEditPolicy:'content',text:'Terima kasih atas doa dan kehadirannya.',x:40,y:240,width:310,height:150,fontFamily:'Georgia',fontSize:23,color:'#2d2926',textAlign:'center',background:'transparent',animation:'fade-up'});
        }else if(['rsvp','gift'].includes(type)){
            const label=type==='rsvp'?'RSVP':'Wedding Gift';
            push({type:'shape',shapeKind:'rounded',componentType:type,name:label+' Card',x:35,y:165,width:320,height:235,background:'#EEF5FF',borderRadius:18,customerEditPolicy:'locked',animation:'fade-up'});
            push({type:'text',componentType:type,name:label+' Title',text:label,x:60,y:195,width:270,height:45,fontFamily:'Georgia',fontSize:27,color:'#2d2926',textAlign:'center',background:'transparent',customerEditPolicy:'content',animation:'fade-up'});
            push({type:'text',componentType:type,name:label+' Placeholder',text:type==='rsvp'?'Form RSVP akan terhubung di renderer pelanggan.':'Data rekening/hadiah akan terhubung di renderer pelanggan.',x:65,y:265,width:260,height:80,fontFamily:'Arial',fontSize:14,color:'#6a625b',textAlign:'center',background:'transparent',customerEditPolicy:'locked',animation:'fade',delay:.15});
        }
        if(created.length){selectedId=created[created.length-1].id;activeCellIndex=0;markDirty();render();notify('Komponen ditambahkan');}
    }

    function addText(){pushHistory();const z=Math.max(0,...page().layers.map(l=>l.zIndex||0))+1;const l=normalizeLayer({id:uid('text'),type:'text',name:'Text',text:'Teks baru',x:45,y:180,width:300,height:70,zIndex:z,fontFamily:'Georgia',fontSize:34,color:'#2d2926',textAlign:'center',background:'transparent'});page().layers.push(l);setSingleSelection(l.id);markDirty();render();}
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
        const l=normalizeLayer({id:uid('grid'),type:'grid',gridKind:kind,name:'Kisi '+kind,x:35,y:150,width:320,height:360,zIndex:z,background:'transparent',gap:4,borderRadius:8,cells:Array.from({length:count},()=>({src:null,posX:0,posY:0,scale:1}))});
        page().layers.push(l);setSingleSelection(l.id);activeCellIndex=0;markDirty();render();
    }
    function fillSelectedMedia(a,cellIndex=activeCellIndex){
        const l=selected();if(!l||(l.type!=='frame'&&l.type!=='grid')||a.type==='video')return false;
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
    function addAsset(a){
        if(fillSelectedMedia(a))return;
        pushHistory();const z=Math.max(0,...page().layers.map(l=>l.zIndex||0))+1;const l=normalizeLayer({id:uid(a.type),type:a.type,name:a.name,src:a.url,x:55,y:120,width:280,height:a.type==='video'?300:280,zIndex:z,background:'transparent'});page().layers.push(l);setSingleSelection(l.id);markDirty();render();
    }

    function renderAssets(){
        const g=$('assetGrid');g.innerHTML='';
        assets.forEach(a=>{
            const e=document.createElement('div');e.className='us-asset';e.title=a.name;e.draggable=true;
            e.dataset.assetId=String(a.id??'');e.dataset.assetUrl=a.url;e.dataset.assetType=a.type;
            if(a.type==='video'){
                e.innerHTML=`<video src="${esc(a.url)}" muted playsinline preload="metadata"></video><span class="us-media-kind">VIDEO</span>`;
                const v=e.querySelector('video');
                v.addEventListener('loadedmetadata',()=>e.classList.add('media-ok'));
                v.addEventListener('error',()=>{e.classList.add('media-error');e.innerHTML=`<div class="us-media-fallback">Video tidak bisa dipreview</div><span class="us-media-kind">VIDEO</span>`;});
            }else{
                e.innerHTML=`<img src="${esc(a.url)}" alt="${esc(a.name||'Foto')}" loading="lazy"><span class="us-media-kind">FOTO</span>`;
                const img=e.querySelector('img');
                img.addEventListener('load',()=>e.classList.add('media-ok'));
                img.addEventListener('error',()=>{e.classList.add('media-error');e.innerHTML=`<div class="us-media-fallback">Preview foto gagal</div><span class="us-media-kind">FOTO</span>`;});
            }
            e.onclick=()=>addAsset(a);
            e.addEventListener('dragstart',ev=>{ev.dataTransfer.setData('application/x-undanganta-asset',JSON.stringify(a));ev.dataTransfer.effectAllowed='copy';});
            g.appendChild(e);
        });
        renderCustomerMediaSelects();
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
    function deleteActivePage(){
        if(state.pages.length<=1){notify('Minimal harus ada 1 halaman');return;}
        pushHistory();
        const idx=state.pages.findIndex(p=>p.id===activePageId);
        state.pages.splice(idx,1);
        const next=state.pages[Math.min(idx,state.pages.length-1)];
        activePageId=next.id;clearSelection();markDirty();render();
    }
    function addNewPage(){
        pushHistory();
        const p=normalizePage({name:'Section '+(state.pages.length+1),role:'section',width:390,height:844},state.pages.length);
        state.pages.push(p);activePageId=p.id;clearSelection();markDirty();render();
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

    function setActivePage(id, animate=false){
        const target=state.pages.find(p=>p.id===id);if(!target)return;cropMode=null;document.querySelector('.us-contextbar')?.classList.remove('crop-mode');activePageId=id;selectedId=target.layers[0]?.id||null;render();if(animate)playTransition();
    }
    function playTransition(){
        const p=page(), type=p.transition.type||'none';if(type==='none')return;
        const map={fade:'pageFade','slide-up':'pageSlideUp','slide-down':'pageSlideDown','slide-left':'pageSlideLeft','slide-right':'pageSlideRight',zoom:'pageZoom',blur:'pageBlur'};const anim=map[type];if(!anim)return;
        canvas.style.animation='none';void canvas.offsetWidth;canvas.classList.add('transitioning');canvas.style.animation=`${anim} ${Number(p.transition.duration||.8)}s ${p.transition.easing||'ease-in-out'} both`;setTimeout(()=>{canvas.style.animation='';canvas.classList.remove('transitioning');},(Number(p.transition.duration||.8)*1000)+80);
    }
    function changePageBy(delta){const i=state.pages.findIndex(p=>p.id===activePageId),n=i+delta;if(n<0||n>=state.pages.length)return;setActivePage(state.pages[n].id,preview);}

    function canvasScale(){
        const rect=canvas.getBoundingClientRect();
        return {sx:state.width/rect.width,sy:state.height/rect.height,rect};
    }
    function layerCenterOnScreen(l){
        const {rect}=canvasScale(),zx=rect.width/state.width,zy=rect.height/state.height;
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
        l.x=Math.round(x);l.y=Math.round(y);l.width=Math.round(w);l.height=Math.round(h);markDirty();render();
    }
    function startRotate(e,l){
        const c=layerCenterOnScreen(l),angle=Math.atan2(e.clientY-c.y,e.clientX-c.x)*180/Math.PI;
        rotateDrag={id:l.id,startAngle:angle,startRotation:Number(l.rotation||0)};
    }
    function applyRotate(e){
        if(!rotateDrag)return;const l=page().layers.find(x=>x.id===rotateDrag.id);if(!l)return;
        const c=layerCenterOnScreen(l),angle=Math.atan2(e.clientY-c.y,e.clientX-c.x)*180/Math.PI;
        l.rotation=Math.round(rotateDrag.startRotation+(angle-rotateDrag.startAngle));markDirty();render();
    }
    function startCropDrag(e,l,cellIndex){
        const cell=l.cells?.[cellIndex];if(!cell||!cell.src)return;
        const {sx,sy}=canvasScale();cropDrag={id:l.id,cellIndex,startX:e.clientX,startY:e.clientY,posX:Number(cell.posX||0),posY:Number(cell.posY||0),sx,sy};
        canvas.querySelector(`.us-layer[data-id="${l.id}"]`)?.classList.add('crop-moving');
    }
    function applyCropDrag(e){
        if(!cropDrag)return;const l=page().layers.find(x=>x.id===cropDrag.id);if(!l)return;ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));
        const cell=l.cells[cropDrag.cellIndex];cell.posX=Math.round(cropDrag.posX+(e.clientX-cropDrag.startX)*cropDrag.sx);cell.posY=Math.round(cropDrag.posY+(e.clientY-cropDrag.startY)*cropDrag.sy);markDirty();render();
    }
    function enterCropMode(layerId,cellIndex=0){
        const l=page().layers.find(x=>x.id===layerId);if(!l||(l.type!=='frame'&&l.type!=='grid'))return;
        ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));
        if(!l.cells[cellIndex]?.src){notify('Masukkan foto ke bingkai dulu');return;}
        selectedId=layerId;activeCellIndex=cellIndex;cropMode={layerId,cellIndex};
        document.querySelector('.us-contextbar')?.classList.add('crop-mode');
        render();
        notify('Crop aktif — geser foto di dalam bingkai');
    }
    function exitCropMode(){cropMode=null;cropDrag=null;gesture=null;document.querySelector('.us-contextbar')?.classList.remove('crop-mode');render();}
    function resetCrop(){
        if(!cropMode)return;const l=page().layers.find(x=>x.id===cropMode.layerId);if(!l)return;pushHistory();ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));l.cells[cropMode.cellIndex]=Object.assign({},l.cells[cropMode.cellIndex],{posX:0,posY:0,scale:1});markDirty();render();
    }


    let guideDrag=null;
    canvas.addEventListener('pointerdown',e=>{
        activePointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
        const guideHandle=e.target.closest('.us-guide-handle');
        if(guideHandle){pushHistory();guideDrag={index:Number(guideHandle.dataset.guideIndex||0)};e.preventDefault();e.stopPropagation();return;}
        const gh=e.target.closest('[data-group-handle]'),gb=e.target.closest('[data-group-selection]');
        if(gh){startGroupResize(e,gh.dataset.groupHandle);e.preventDefault();e.stopPropagation();return;}
        if(gb&&selectedIds.size>1){startGroupMove(e);e.preventDefault();e.stopPropagation();return;}
        const el=e.target.closest('.us-layer');
        if(!el){if(activePointers.size===1&&!preview&&!e.target.closest('.us-guide-line')){cropMode=null;document.querySelector('.us-contextbar')?.classList.remove('crop-mode');beginLasso(e);e.preventDefault();}return;}
        const id=el.dataset.id;
        const hitLayer=page().layers.find(x=>x.id===id);
        if(hitLayer?.groupId&&!e.altKey&&!e.shiftKey){selectedIds=new Set(page().layers.filter(x=>x.groupId===hitLayer.groupId).map(x=>x.id));selectedId=id;syncPrimarySelection();if(selectedIds.size>1){startGroupMove(e);e.preventDefault();return;}}
        const now=Date.now();
        if(hitLayer?.type==='text' && textClickArm.id===id && now-textClickArm.time<420 && !preview && editorViewMode==='sample'){
            textClickArm={id:null,time:0};setSingleSelection(id);openPanel('text');startInlineTextEdit(id);e.preventDefault();e.stopPropagation();return;
        }
        textClickArm={id,time:now};
        if(e.shiftKey){if(selectedIds.has(id))selectedIds.delete(id);else selectedIds.add(id);selectedId=id;syncPrimarySelection();render();e.preventDefault();return;}
        if(selectedIds.size>1&&selectedIds.has(id)){selectedId=id;startGroupMove(e);e.preventDefault();return;}
        setSingleSelection(id);const cellEl=e.target.closest('[data-cell-index]');if(cellEl)activeCellIndex=Number(cellEl.dataset.cellIndex||0);
        const l=selected();if(!l||l.locked||preview){render();return;}const handle=e.target.closest('[data-handle]')?.dataset.handle;if(activePointers.size===1)pushHistory();
        if(handle==='rotate'){startRotate(e,l);e.preventDefault();return;}if(handle){startResize(e,l,handle);e.preventDefault();return;}
        if(cropMode&&cropMode.layerId===l.id&&cellEl){cropMode.cellIndex=activeCellIndex;startCropDrag(e,l,activeCellIndex);e.preventDefault();return;}
        if(activePointers.size===2){const pts=[...activePointers.values()];const dist=Math.hypot(pts[1].x-pts[0].x,pts[1].y-pts[0].y);const ang=Math.atan2(pts[1].y-pts[0].y,pts[1].x-pts[0].x)*180/Math.PI;if(cropMode&&cropMode.layerId===l.id){ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));gesture={kind:'crop',id:l.id,cellIndex:activeCellIndex,startDist:dist,startScale:Number(l.cells[activeCellIndex]?.scale||1)};}else gesture={kind:'layer',id:l.id,startDist:dist,startAngle:ang,w:l.width,h:l.height,rotation:Number(l.rotation||0)};drag=null;resize=null;rotateDrag=null;cropDrag=null;e.preventDefault();return;}
        const {sx,sy}=canvasScale();drag={id:l.id,startX:e.clientX,startY:e.clientY,x:l.x,y:l.y,sx,sy};render();e.preventDefault();
    });

    window.addEventListener('pointermove',e=>{
        if(activePointers.has(e.pointerId))activePointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
        if(guideDrag){const pt=canvasPoint(e.clientX,e.clientY),p=page(),h=Number(p.height||844);if(!Array.isArray(p.guides))p.guides=[Math.round(h*.34),Math.round(h*.68)];p.guides[guideDrag.index]=Math.round(clamp(pt.y,24,h-24));dirty=true;setSaveStatus('Belum disimpan','');renderCanvas();return;}
        if(lasso){const pt=canvasPoint(e.clientX,e.clientY);lasso.x=pt.x;lasso.y=pt.y;updateLassoVisual();return;}
        if(groupResize){applyGroupResize(e);return;}
        if(groupDrag){applyGroupMove(e);return;}
        if(gesture&&activePointers.size>=2){
            const pts=[...activePointers.values()].slice(0,2),dist=Math.max(1,Math.hypot(pts[1].x-pts[0].x,pts[1].y-pts[0].y));
            const l=page().layers.find(x=>x.id===gesture.id);if(!l)return;
            if(gesture.kind==='crop'){
                ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));l.cells[gesture.cellIndex].scale=clamp(gesture.startScale*(dist/gesture.startDist),.5,4);
            }else{
                const ratio=dist/gesture.startDist,ang=Math.atan2(pts[1].y-pts[0].y,pts[1].x-pts[0].x)*180/Math.PI;
                l.width=Math.max(20,Math.round(gesture.w*ratio));l.height=Math.max(20,Math.round(gesture.h*ratio));l.rotation=Math.round(gesture.rotation+(ang-gesture.startAngle));
            }
            markDirty();render();return;
        }
        if(cropDrag){applyCropDrag(e);return;}
        if(rotateDrag){applyRotate(e);return;}
        if(resize){applyResize(e);return;}
        if(drag){const l=page().layers.find(x=>x.id===drag.id);if(!l)return;const rawX=drag.x+(e.clientX-drag.startX)*drag.sx,rawY=drag.y+(e.clientY-drag.startY)*drag.sy;const snapped=snapLayerPosition(l,rawX,rawY);l.x=snapped.x;l.y=snapped.y;markDirty();render();drawSnapGuides(snapped.guides);}
    });

    window.addEventListener('pointerup',e=>{
        activePointers.delete(e.pointerId);
        if(activePointers.size<2)gesture=null;
        if(activePointers.size===0){
            if(lasso){finishLasso();return;}
            drag=null;resize=null;rotateDrag=null;cropDrag=null;groupDrag=null;groupResize=null;
            if(guideDrag){guideDrag=null;markDirty();}
            canvas.querySelectorAll('.crop-moving').forEach(x=>x.classList.remove('crop-moving'));clearSnapGuides();
        }
    });
    window.addEventListener('pointercancel',e=>{activePointers.delete(e.pointerId);if(activePointers.size===0){drag=null;resize=null;rotateDrag=null;cropDrag=null;groupDrag=null;groupResize=null;guideDrag=null;gesture=null;if(lasso)finishLasso();}});



    function startInlineTextEdit(layerId){
        const l=page().layers.find(x=>x.id===layerId);if(!l||l.type!=='text'||l.locked||preview||editorViewMode!=='sample')return;setSingleSelection(layerId);render();
        requestAnimationFrame(()=>{const el=canvas.querySelector(`.us-layer[data-id="${layerId}"]`),content=el?.querySelector('.us-text');if(!el||!content)return;pushHistory();inlineTextEdit={id:layerId,original:l.text||''};el.classList.add('inline-text-edit');content.contentEditable='true';content.spellcheck=true;content.focus();const range=document.createRange();range.selectNodeContents(content);range.collapse(false);const sel=window.getSelection();sel.removeAllRanges();sel.addRange(range);
        const commit=()=>{if(!inlineTextEdit||inlineTextEdit.id!==layerId)return;l.text=content.innerText.replace(/\n{3,}/g,'\n\n');inlineTextEdit=null;markDirty();render();};content.addEventListener('blur',commit,{once:true});content.addEventListener('keydown',ev=>{if(ev.key==='Escape'){ev.preventDefault();l.text=inlineTextEdit?.original??l.text;inlineTextEdit=null;render();return;}if((ev.ctrlKey||ev.metaKey)&&ev.key==='Enter'){ev.preventDefault();content.blur();return;}ev.stopPropagation();});content.addEventListener('input',()=>{l.text=content.innerText;if($('pText'))$('pText').value=l.text;dirty=true;setSaveStatus('Belum disimpan','');});});
    }
    canvas.addEventListener('dblclick',e=>{const el=e.target.closest('.us-layer');if(!el)return;const l=page().layers.find(x=>x.id===el.dataset.id);if(l?.type==='text'){e.preventDefault();e.stopPropagation();setSingleSelection(l.id);openPanel('text');startInlineTextEdit(l.id);}});

    canvas.addEventListener('click',e=>{
        const btn=e.target.closest('[data-crop-action]');if(!btn||!cropMode)return;
        e.preventDefault();e.stopPropagation();
        const l=page().layers.find(x=>x.id===cropMode.layerId);if(!l)return;
        ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));
        const c=l.cells[cropMode.cellIndex];if(!c)return;
        const action=btn.dataset.cropAction;
        if(action==='done'){exitCropMode();return;}
        pushHistory();
        if(action==='reset'){c.posX=0;c.posY=0;c.scale=1;}
        if(action==='zoom-in')c.scale=clamp(Number(c.scale||1)+.1,.5,4);
        if(action==='zoom-out')c.scale=clamp(Number(c.scale||1)-.1,.5,4);
        markDirty();render();
    });

    canvas.addEventListener('dblclick',e=>{
        const el=e.target.closest('.us-layer');if(!el)return;
        const l=page().layers.find(x=>x.id===el.dataset.id);if(!l)return;
        const cell=e.target.closest('[data-cell-index]');
        if(l.type==='frame'){enterCropMode(l.id,0);e.preventDefault();return;}
        if(l.type==='grid'&&cell){enterCropMode(l.id,Number(cell.dataset.cellIndex||0));e.preventDefault();return;}
        if(l.type==='text'){
            const next=window.prompt('Edit teks',l.text||'');if(next!==null){pushHistory();l.text=next;markDirty();render();}
        }
    });
    canvas.addEventListener('pointerup',e=>{
        if(e.pointerType!=='touch')return;const el=e.target.closest('.us-layer');if(!el)return;const now=Date.now();const cell=e.target.closest('[data-cell-index]');const cellIndex=cell?Number(cell.dataset.cellIndex||0):null;
        if(lastTap.id===el.dataset.id&&lastTap.cell===cellIndex&&(now-lastTap.time)<360){
            const l=page().layers.find(x=>x.id===el.dataset.id);
            if(l&&l.type==='frame')enterCropMode(l.id,0);
            else if(l&&l.type==='grid'&&cell)enterCropMode(l.id,cellIndex);
            else if(l&&l.type==='text'){const next=window.prompt('Edit teks',l.text||'');if(next!==null){pushHistory();l.text=next;markDirty();render();}}
            lastTap={time:0,id:null,cell:null};
        }else lastTap={time:now,id:el.dataset.id,cell:cellIndex};
    });
    canvas.addEventListener('wheel',e=>{
        if(!cropMode)return;const el=e.target.closest('.us-layer');if(!el||el.dataset.id!==cropMode.layerId)return;const l=selected();if(!l)return;ensureCells(l,l.type==='frame'?1:cellCountForGrid(l.gridKind||'4'));const c=l.cells[cropMode.cellIndex];if(!c?.src)return;e.preventDefault();c.scale=clamp(Number(c.scale||1)+(e.deltaY<0?.08:-.08),.5,4);markDirty();render();
    },{passive:false});


    canvas.addEventListener('dragover',e=>{
        if(e.dataTransfer.types.includes('application/x-undanganta-asset')){e.preventDefault();e.dataTransfer.dropEffect='copy';}
    });
    canvas.addEventListener('drop',e=>{
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
                fillSelectedMedia(a,activeCellIndex);
                return;
            }
        }
        addAsset(a);
    });

    layerList.addEventListener('click',e=>{const r=e.target.closest('.us-layer-row');if(!r)return;const id=r.dataset.id,l=page().layers.find(x=>x.id===id);if(e.shiftKey){if(selectedIds.has(id))selectedIds.delete(id);else selectedIds.add(id);selectedId=id;syncPrimarySelection();}else if(l?.groupId&&!e.altKey){selectedIds=new Set(page().layers.filter(x=>x.groupId===l.groupId).map(x=>x.id));selectedId=id;}else setSingleSelection(id);activeCellIndex=0;render();});

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
    bind('pBinding','binding');bind('pCustomerEditPolicy','customerEditPolicy');
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
    $('animTrigger').addEventListener('change',e=>updateSelected('animationTrigger',e.target.value));
    $('animEasing').addEventListener('change',e=>updateSelected('animationEasing',e.target.value));
    document.querySelectorAll('[data-animation-preset]').forEach(btn=>btn.addEventListener('click',()=>{
        const l=selected();if(!l){notify('Pilih layer dulu');return;}pushHistory();l.animation=btn.dataset.animationPreset;if(['image','video','frame','grid'].includes(l.type))l.loop=false;
        if(l.animation!=='none'&&!Number(l.duration))l.duration=.8;markDirty();render();previewSingleLayer(l.id);
    }));


    $('pFrameKind').addEventListener('change',e=>{
        const l=selected();if(!l||l.type!=='frame')return;
        pushHistory();l.frameKind=e.target.value;
        const preset={portrait:[190,250],landscape:[280,180],circle:[190,190],heart:[200,190],arch:[190,250],polaroid:[210,270],rounded:[200,240],square:[210,210]}[l.frameKind];
        if(preset){l.width=preset[0];l.height=preset[1];}
        markDirty();render();
    });

        $('pMediaCell').addEventListener('change',e=>{activeCellIndex=Number(e.target.value||0);render();});
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

    $('clearMediaCell').addEventListener('click',()=>{const l=selected();if(!l||(l.type!=='frame'&&l.type!=='grid'))return;const count=l.type==='frame'?1:cellCountForGrid(l.gridKind||'4');ensureCells(l,count);pushHistory();l.cells[activeCellIndex]={src:null,posX:0,posY:0,scale:1};markDirty();render();});



    $('previewSelectedAnimation')?.addEventListener('click',()=>{const l=selected();if(!l){notify('Pilih layer dulu');return;}previewSingleLayer(l.id);});
    $('cropDoneBtn')?.addEventListener('click',exitCropMode);
    $('cropResetBtn')?.addEventListener('click',resetCrop);


    ['tplName','tplSlug','tplPlan','tplEditable'].forEach(id=>{const el=$(id);if(!el)return;el.addEventListener(el.type==='checkbox'||el.tagName==='SELECT'?'change':'input',()=>markDirty());});

    let pageNameSnapshot=null,pageDurationSnapshot=null;
    $('pageName').addEventListener('focus',()=>{if(pageNameSnapshot===null)pageNameSnapshot=snapshot();});
    $('pageName').addEventListener('input',e=>{page().name=e.target.value||'Canvas';dirty=true;setSaveStatus('Belum disimpan','');renderPages();});
    $('pageName').addEventListener('blur',()=>{page().name=$('pageName').value.trim()||'Canvas';if(pageNameSnapshot!==null){history.push(pageNameSnapshot);pageNameSnapshot=null;markDirty();render();}});
    $('pageTransition').addEventListener('change',e=>{pushHistory();page().transition.type=e.target.value;markDirty();render();});
    $('pageDuration').addEventListener('focus',()=>{if(pageDurationSnapshot===null)pageDurationSnapshot=snapshot();});
    $('pageDuration').addEventListener('input',e=>{page().transition.duration=clamp(Number(e.target.value||.8),.1,5);dirty=true;setSaveStatus('Belum disimpan','');});
    $('pageDuration').addEventListener('change',()=>{if(pageDurationSnapshot!==null){history.push(pageDurationSnapshot);pageDurationSnapshot=null;markDirty();}});
    $('pageEasing').addEventListener('change',e=>{pushHistory();page().transition.easing=e.target.value;markDirty();render();});

    $('addText').onclick=()=>{addText();openPanel('text');};
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
    $('toggleSnap')?.addEventListener('click',()=>{snapEnabled=!snapEnabled;$('toggleSnap').classList.toggle('active',snapEnabled);$('toggleSnap').setAttribute('aria-pressed',snapEnabled?'true':'false');if(!snapEnabled)clearSnapGuides();notify(snapEnabled?'Snap aktif':'Snap nonaktif');});

    $('toggleSafeArea')?.addEventListener('click',()=>{safeAreaEnabled=!safeAreaEnabled;$('toggleSafeArea').classList.toggle('active',safeAreaEnabled);$('toggleSafeArea').setAttribute('aria-pressed',safeAreaEnabled?'true':'false');$('safeAreaEnabled').checked=safeAreaEnabled;render();});
    $('safeAreaEnabled')?.addEventListener('change',e=>{safeAreaEnabled=!!e.target.checked;$('toggleSafeArea').classList.toggle('active',safeAreaEnabled);$('toggleSafeArea').setAttribute('aria-pressed',safeAreaEnabled?'true':'false');render();});
    $('safeAreaMargin')?.addEventListener('input',e=>{safeAreaMargin=clamp(Number(e.target.value||0),0,80);render();});
    $('keepInsideCanvas')?.addEventListener('change',e=>{keepInsideCanvas=!!e.target.checked;});
    $('respectSafeArea')?.addEventListener('change',e=>{respectSafeArea=!!e.target.checked;});
    $('fitSelectionSafe')?.addEventListener('click',fitSelectionIntoSafeArea);
    $('responsiveMode')?.addEventListener('change',e=>{const l=selected();if(!l)return;pushHistory();l.responsiveMode=e.target.value;markDirty();render();});

    $('duplicateLayer').onclick=()=>duplicateSelectedLayers();
    $('deleteLayer').onclick=()=>deleteSelectedLayers();
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
    $('applyRecommendedSize')?.addEventListener('click',()=>{const p=page();if(!p)return;pushHistory();p.width=390;p.height=844;if(!Array.isArray(p.guides)||p.guides.length<2)p.guides=[287,574];markDirty();render();notify('Ukuran canvas 390 × 844 diterapkan');});
        $('duplicatePage').onclick=duplicateActivePage;
    $('deletePage').onclick=deleteActivePage;
    $('prevCanvas').onclick=()=>changePageBy(-1);$('nextCanvas').onclick=()=>changePageBy(1);$('previewTransition').onclick=playTransition;$('previewTransitionSide').onclick=playTransition;

    $('undoBtn').onclick=()=>{if(!history.length)return;future.push(snapshot());restore(history.pop());markDirty();render();};
    $('redoBtn').onclick=()=>{if(!future.length)return;history.push(snapshot());restore(future.pop());markDirty();render();};
    $('previewBtn').onclick=()=>openResponsivePreview(window.innerWidth>=900?'desktop':'mobile');

    async function uploadAssetFile(file){
        if(!file)return;
        const allowed=['image/png','image/jpeg','image/webp','image/gif','video/mp4','video/webm'];if(!allowed.includes(file.type)){notify('Format file belum didukung');return;}
        const fd=new FormData();fd.append('file',file);const progress=$('assetUploadProgress'),bar=progress?.querySelector('i');if(progress)progress.style.display='block';if(bar)bar.style.width='28%';
        try{const r=await fetch(uploadAssetUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:fd});if(bar)bar.style.width='82%';const j=await r.json();if(!r.ok)throw new Error(j.message||'Upload gagal');assets.unshift(j.asset);renderAssets();addAsset(j.asset);$('assetFile').value='';if(bar)bar.style.width='100%';notify('Media berhasil diupload');}
        catch(e){notify(e.message)}finally{setTimeout(()=>{if(progress)progress.style.display='none';if(bar)bar.style.width='0';},450);}
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
            assets.unshift(j.asset);pushHistory();Object.assign(desktopCoverState(),{image:j.asset.url,mediaType:file.type==='video/mp4'?'video':'image',fit:'cover',scale:1,posX:0,posY:0});
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
    bindOpeningCoverControl('openingCoverY','posY',Number);
    bindOpeningCoverControl('openingCoverAnimation','animation',String);
    bindOpeningCoverControl('openingCoverDuration','duration',Number);
    $('centerOpeningCover')?.addEventListener('click',()=>{
        const c=openingCoverState();
        c.posX=50;
        c.posY=50;
        markDirty();
        syncOpeningCoverControls();
        notify('Konten sampul diposisikan tepat di tengah frame');
    });
    $('previewOpeningCover')?.addEventListener('click',previewOpeningCoverStandalone);

    
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
        if(!requiredName){setSaveStatus('Nama template wajib diisi','error');if(!silent)notify('Nama Template wajib diisi');return;}
        if(!requiredSlug){setSaveStatus('Slug template wajib diisi','error');if(!silent)notify('Slug Template wajib diisi');return;}
        if(saveInFlight){pendingSave=true;return;}
        if(!dirty&&silent)return;
        const body={name:$('tplName').value.trim(),slug:$('tplSlug').value.trim(),min_plan:$('tplPlan').value,is_customer_editable:$('tplEditable').checked,canvas:state};
        saveInFlight=true;$('saveBtn').disabled=true;setSaveStatus('Menyimpan...','saving');
        try{
            const r=await fetch(updateUrl,{method:'PUT',headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(body)});
            const j=await r.json();if(!r.ok)throw new Error(j.message||Object.values(j.errors||{})[0]?.[0]||'Save gagal');
            dirty=false;setSaveStatus('Tersimpan','saved');if(!silent)notify('Template tersimpan');
        }catch(e){dirty=true;setSaveStatus('Gagal menyimpan','error');if(!silent)notify(e.message);}
        finally{saveInFlight=false;$('saveBtn').disabled=false;if(pendingSave){pendingSave=false;setTimeout(()=>saveTemplate({silent:true}),150);}}
    }
    $('saveBtn').onclick=()=>saveTemplate({silent:false});

    window.addEventListener('keydown',e=>{
        const tag=document.activeElement?.tagName,typing=['INPUT','TEXTAREA','SELECT'].includes(tag)||document.activeElement?.isContentEditable,mod=e.ctrlKey||e.metaKey;
        if(mod&&e.key.toLowerCase()==='s'){e.preventDefault();saveTemplate({silent:false});return;}
        if(mod&&e.key.toLowerCase()==='z'&&!typing){e.preventDefault();if(e.shiftKey){if(future.length){history.push(snapshot());restore(future.pop());markDirty();render();}}else{if(history.length){future.push(snapshot());restore(history.pop());markDirty();render();}}return;}
        if(typing||preview)return;const hasLayers=selectedIds.size>0;
        if(mod&&e.key.toLowerCase()==='c'){e.preventDefault();if(hasLayers)copySelectedLayers();else copyActivePage();return;}
        if(mod&&e.key.toLowerCase()==='v'){e.preventDefault();if(layerClipboard.length)pasteSelectedLayers();else pastePage();return;}
        if(mod&&e.key.toLowerCase()==='d'){e.preventDefault();if(hasLayers)duplicateSelectedLayers();else duplicateActivePage();return;}
        if(mod&&e.key.toLowerCase()==='g'){e.preventDefault();if(e.shiftKey)ungroupSelectedLayers();else groupSelectedLayers();return;}
        if(mod&&e.key==='Enter'){e.preventDefault();addNewPage();return;}
        if(e.key==='Delete'||e.key==='Backspace'){e.preventDefault();if(hasLayers)deleteSelectedLayers();else deleteActivePage();return;}
        const list=selectedLayers().filter(l=>!l.locked);if(list.length&&['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(e.key)){pushHistory();const step=e.shiftKey?10:1;list.forEach(l=>{if(e.key==='ArrowLeft')l.x-=step;if(e.key==='ArrowRight')l.x+=step;if(e.key==='ArrowUp')l.y-=step;if(e.key==='ArrowDown')l.y+=step;});markDirty();render();e.preventDefault();}
    });

    canvas.addEventListener('contextmenu',e=>{e.preventDefault();clearSelection();render();showPageMenu(e.clientX,e.clientY,activePageId);});
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

    function staticCellImage(cell){
        if(!cell?.src)return '';
        const tx=Number(cell.posX||0),ty=Number(cell.posY||0),sc=Math.max(.5,Number(cell.scale||1));
        return `<img src="${esc(cell.src)}" alt="" style="position:absolute;left:50%;top:50%;width:100%;height:100%;object-fit:cover;transform:translate(calc(-50% + ${tx}px),calc(-50% + ${ty}px)) scale(${sc})">`;
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
            }
            else if(l.type==='video')el.innerHTML=`<video src="${esc(l.src)}" autoplay muted loop playsinline></video>`;
            else if(l.type==='frame'){
                const copy=deep(l);ensureCells(copy,1);
                const override=effectiveMediaSrc(l);
                if(editorViewMode!=='sample'&&l.binding&&l.binding!=='none')copy.cells[0]=Object.assign({},copy.cells[0]||{posX:0,posY:0,scale:1},{src:override||null});
                const kind=copy.frameKind||'square',cls={circle:'us-frame-circle',rounded:'us-frame-rounded',arch:'us-frame-arch',heart:'us-frame-heart'}[kind]||'';
                el.innerHTML=`<div class="us-frame-inner ${cls}" style="width:100%;height:100%;${kind==='polaroid'?'padding:8px 8px 24px;background:#fff;box-sizing:border-box':''}"><div class="us-media-cell ${copy.cells[0]?.src?'':'empty'}" style="width:100%;height:100%">${copy.cells[0]?.src?staticCellImage(copy.cells[0]):`<div class="us-empty-binding media">${esc(bindingPlaceholder(l))}</div>`}</div></div>`;
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
    function scaledStaticPage(p,targetWidth,entryTransition=null){
        const pw=Number(p.width||390),ph=Number(p.height||844);
        // Never enlarge the logical 390px invitation above 1:1.
        // Small devices scale down; larger devices preserve authored typography.
        const scale=Math.min(1,targetWidth/pw);
        const renderedWidth=pw*scale;
        const wrap=document.createElement('div');wrap.className='us-preview-section';wrap.style.width=renderedWidth+'px';wrap.style.height=(ph*scale)+'px';wrap.style.margin='0 auto';
        const host=buildStaticPage(p);host.style.transform=`scale(${scale})`;host.style.transformOrigin='top left';wrap.appendChild(host);
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
        if(!state.openingCover)state.openingCover={enabled:true,eyebrow:'The Wedding of',names:'Nama & Nama',bindNames:true,buttonText:'Buka Undangan',textColor:'#ffffff',buttonColor:'#2F6FED',nameSize:46,posX:50,posY:50,animation:'fade-up',duration:.8};
        return state.openingCover;
    }
    function openingCoverNames(){
        const c=openingCoverState();
        if(c.bindNames&&editorViewMode==='customer'){const v=customerTextValue('couple_names');if(v)return v;}
        if(editorViewMode==='empty'&&c.bindNames)return '';
        return c.names||'Nama & Nama';
    }
    function buildOpeningCover(device='desktop'){
        const c=openingCoverState();if(!c.enabled)return null;
        const wrap=document.createElement('div');wrap.className='us-opening-cover-preview';

        const dc=desktopCoverState();
        if(dc.image){
            const isVideo=dc.mediaType==='video'||/\.mp4(?:\?|$)/i.test(dc.image);
            const media=document.createElement(isVideo?'video':'img');
            media.className='us-opening-cover-bg'+(isVideo?' video':'');
            media.src=dc.image;
            applyCoverMediaStyle(media,dc);
            if(isVideo){
                media.muted=true;media.defaultMuted=true;media.autoplay=true;media.loop=true;media.playsInline=true;
                media.setAttribute('muted','');media.setAttribute('playsinline','');
            }
            wrap.appendChild(media);
        }

        const shade=document.createElement('div');
        shade.className='us-opening-cover-shade';
        wrap.appendChild(shade);

        // OUTER = positioning only. Never animate this element.
        const content=document.createElement('div');
        content.className='us-opening-cover-content';
        content.dataset.previewDevice=device;
        content.style.left=clamp(Number(c.posX??50),10,90)+'%';
        content.style.top=clamp(Number(c.posY??50),10,90)+'%';
        content.style.transform='translate(-50%,-50%)';
        content.style.textAlign='center';
        content.style.color=c.textColor||'#fff';

        // INNER = animation only. This prevents animation transforms
        // from breaking the permanent center transform of OUTER.
        const inner=document.createElement('div');
        inner.className='us-opening-cover-inner';

        const deviceScale=device==='mobile'?.72:(device==='tablet'?.86:1);
        const coverAnimMap={'fade':'usFade','fade-up':'usFadeUp','zoom':'usZoom','soft-scale':'usSoftScale'};
        if(c.animation&&c.animation!=='none'&&coverAnimMap[c.animation]){
            inner.style.animation=`${coverAnimMap[c.animation]} ${Number(c.duration||.8)}s ease-out both`;
        }

        const eyebrow=document.createElement('div');
        eyebrow.className='us-opening-cover-eyebrow';
        eyebrow.textContent=c.eyebrow||'';
        eyebrow.style.fontSize=Math.max(9,14*deviceScale)+'px';

        const names=document.createElement('div');
        names.className='us-opening-cover-names';
        const coverNames=openingCoverNames(),baseSize=Number(c.nameSize||46),len=Math.max(1,coverNames.length);
        const lengthScale=len>28?.72:(len>20?.84:1);
        const safeSize=Math.max(device==='mobile'?21:24,baseSize*lengthScale*deviceScale);
        names.style.fontSize=safeSize+'px';
        names.textContent=coverNames;

        const btn=document.createElement('button');
        btn.className='us-opening-cover-button';
        btn.type='button';
        btn.style.background=c.buttonColor||'#2F6FED';
        btn.style.fontSize=Math.max(8,11*deviceScale)+'px';
        btn.textContent=c.buttonText||'Buka Undangan';
        btn.addEventListener('click',()=>wrap.classList.add('hidden'));

        inner.append(eyebrow,names,btn);
        content.appendChild(inner);
        wrap.appendChild(content);
        return wrap;
    }
    function syncOpeningCoverControls(){
        const c=openingCoverState();
        if(c.posX===undefined||c.posX===null)c.posX=50;
        if($('openingCoverEnabled'))$('openingCoverEnabled').checked=!!c.enabled;
        if($('openingCoverBindNames'))$('openingCoverBindNames').checked=!!c.bindNames;
        [['openingCoverEyebrow','eyebrow'],['openingCoverNames','names'],['openingCoverButtonText','buttonText'],['openingCoverTextColor','textColor'],['openingCoverButtonColor','buttonColor'],['openingCoverNameSize','nameSize'],['openingCoverY','posY'],['openingCoverAnimation','animation'],['openingCoverDuration','duration']]
          .forEach(([id,key])=>{if($(id))$(id).value=c[key]??'';});
    }
    function previewOpeningCoverStandalone(){
        openResponsivePreview(window.innerWidth>=900?'desktop':'mobile');
        requestAnimationFrame(()=>{const box=$('responsivePreviewStage')?.querySelector('.us-preview-device');if(!box)return;box.querySelector('.us-opening-cover-preview')?.remove();const cover=buildOpeningCover(window.innerWidth>=900?'desktop':'mobile');if(cover)box.appendChild(cover);});
    }

    function renderResponsivePreview(device='desktop'){
        const stage=$('responsivePreviewStage');stage.innerHTML='';
        const box=document.createElement('div');box.className='us-preview-device '+device;
        const pages=(state.pages||[]).filter(p=>p.role!=='desktop-cover');
        const layout=state.settings?.desktopLayout||'cover-left';

        const appendPages=(root,width)=>{
            pages.forEach((p,i)=>{
                const entry=i===0?null:(pages[i-1]?.transition||null);
                root.appendChild(scaledStaticPage(p,width,entry));
            });
            requestAnimationFrame(()=>activatePreviewTransitions(root));
        };

        if(device==='desktop'&&layout!=='centered'){
            box.style.setProperty('--cover-width',(state.settings?.desktopCoverWidth||56)+'%');
            const cover=document.createElement('div');cover.className='us-preview-cover';cover.appendChild(buildDedicatedDesktopCover());
            const scroll=document.createElement('div');scroll.className='us-preview-scroll';
            if(layout==='cover-right')box.append(scroll,cover);else box.append(cover,scroll);
            requestAnimationFrame(()=>appendPages(scroll,Math.max(220,Math.min(390,scroll.clientWidth))));
        }else{
            const stack=document.createElement('div');stack.className='us-preview-mobile-stack';box.appendChild(stack);
            requestAnimationFrame(()=>{
                const logical=device==='mobile'?390:(device==='tablet'?600:390);
                const available=Math.max(220,stack.clientWidth||logical);
                appendPages(stack,Math.min(logical,available));
            });
        }

        const note=document.createElement('div');note.className='us-preview-note';
        note.textContent=device==='desktop'
            ? (layout==='centered'?'Desktop: single column':'Desktop: sampul tetap + isi undangan scroll')
            : 'Mobile/Tablet: satu kolom scroll';
        box.appendChild(note);stage.appendChild(box);const opening=buildOpeningCover(device);if(opening)box.appendChild(opening);$('responsivePreviewLabel').textContent=device.toUpperCase();
        ['previewMobileBtn','previewTabletBtn','previewDesktopBtn'].forEach(id=>$(id)?.classList.toggle('active',id.toLowerCase().includes(device)));
    }
    function openResponsivePreview(device){
        $('responsivePreview').classList.add('open');$('responsivePreview').setAttribute('aria-hidden','false');renderResponsivePreview(device);
    }
    function closeResponsivePreview(){$('responsivePreview').classList.remove('open');$('responsivePreview').setAttribute('aria-hidden','true');}


    function setCustomerInstanceState(text,state=''){
        const el=$('customerInstanceState');if(!el)return;
        el.textContent=text;el.className='us-instance-state'+(state?' '+state:'');
    }
    function imageAssets(){
        return assets.filter(a=>a.type==='image');
    }
    function renderCustomerMediaSelects(){
        document.querySelectorAll('[data-customer-media]').forEach(select=>{
            const key=select.dataset.customerMedia,current=customerData?.[key];
            const currentId=typeof current==='object'&&current?String(current.asset_id||''):'';
            select.innerHTML='<option value="">— Kosong —</option>'+imageAssets().map(a=>`<option value="${a.id}">${esc(a.name)}</option>`).join('');
            select.value=currentId;
        });
    }
    function syncCustomerForm(){
        document.querySelectorAll('[data-customer-key]').forEach(el=>{el.value=customerData?.[el.dataset.customerKey]??'';});
        renderCustomerMediaSelects();
        setCustomerInstanceState(customerPreviewInstance?'Instance Uji aktif':'Belum dibuat',customerPreviewInstance?'saved':'');
    }
    async function ensureCustomerPreviewInstance(){
        if(customerPreviewInstance)return customerPreviewInstance;
        setCustomerInstanceState('Membuat...','saving');
        const r=await fetch(ensurePreviewInstanceUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
        const j=await r.json();if(!r.ok)throw new Error(j.message||'Gagal membuat instance uji');
        customerPreviewInstance=j.instance;customerData=Object.assign({},customerData,j.instance.content||{});
        syncCustomerForm();return customerPreviewInstance;
    }
    async function saveCustomerPreview(){
        if(customerSaveInFlight){customerSavePending=true;return;}
        try{
            const instance=await ensureCustomerPreviewInstance();
            customerSaveInFlight=true;setCustomerInstanceState('Menyimpan...','saving');
            const r=await fetch(`/admin/studio/instances/${instance.id}`,{
                method:'PUT',headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json','Accept':'application/json'},
                body:JSON.stringify({content:customerData})
            });
            const j=await r.json();if(!r.ok)throw new Error(j.message||'Gagal menyimpan data pelanggan uji');
            customerPreviewInstance=j.instance;customerData=Object.assign({},customerData,j.instance.content||{});
            setCustomerInstanceState('Tersimpan '+(j.saved_at||''),'saved');render();
        }catch(e){setCustomerInstanceState('Gagal simpan','error');notify(e.message);}
        finally{
            customerSaveInFlight=false;
            if(customerSavePending){customerSavePending=false;setTimeout(saveCustomerPreview,100);}
        }
    }
    function queueCustomerSave(){clearTimeout(customerSaveTimer);setCustomerInstanceState('Belum disimpan','');customerSaveTimer=setTimeout(saveCustomerPreview,650);}
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
        el.addEventListener('input',()=>{customerData[el.dataset.customerKey]=el.value;queueCustomerSave();if(editorViewMode==='customer')render();});
    });
    document.querySelectorAll('[data-customer-media]').forEach(el=>{
        el.addEventListener('change',()=>{
            const asset=assets.find(a=>String(a.id)===String(el.value));
            customerData[el.dataset.customerMedia]=asset?{asset_id:asset.id,url:asset.url,name:asset.name}:null;
            queueCustomerSave();if(editorViewMode==='customer')render();
        });
    });

        document.querySelectorAll('[data-view-mode]').forEach(btn=>btn.addEventListener('click',()=>{
        editorViewMode=btn.dataset.viewMode;
        document.querySelectorAll('[data-view-mode]').forEach(x=>x.classList.toggle('active',x===btn));
        if(editorViewMode!=='sample'&&cropMode)exitCropMode();
        if(editorViewMode==='customer'){syncCustomerForm();openPanel('customer-data');}
        render();
        notify(editorViewMode==='sample'?'Mode Contoh':editorViewMode==='empty'?'Mode Kosong':'Preview Pelanggan');
    }));

        document.querySelectorAll('[data-preview-device]').forEach(b=>b.addEventListener('click',()=>openResponsivePreview(b.dataset.previewDevice)));
    $('previewMobileBtn')?.addEventListener('click',()=>renderResponsivePreview('mobile'));
    $('previewTabletBtn')?.addEventListener('click',()=>renderResponsivePreview('tablet'));
    $('previewDesktopBtn')?.addEventListener('click',()=>renderResponsivePreview('desktop'));
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
        if(window.innerWidth<=760) return;
        studioBody?.classList.toggle('library-collapsed', collapsed);
        const toggle=document.getElementById('toggleLibrary');
        if(toggle){toggle.textContent=collapsed?'›':'‹';toggle.setAttribute('aria-expanded',collapsed?'false':'true');}
        try{sessionStorage.setItem('undangantaStudioLibraryCollapsed',collapsed?'1':'0')}catch(e){}
        setTimeout(()=>document.querySelector('.us-canvas-zone')?.scrollTo({left:0,behavior:'smooth'}),190);
    }
    function setPropertiesCollapsed(collapsed){
        if(window.innerWidth<=760) return;
        studioBody?.classList.toggle('properties-collapsed', collapsed);
        const toggle=document.getElementById('toggleProperties');
        if(toggle){toggle.textContent=collapsed?'‹':'›';toggle.setAttribute('aria-expanded',collapsed?'false':'true');}
        try{sessionStorage.setItem('undangantaStudioPropsCollapsed',collapsed?'1':'0')}catch(e){}
    }
    function closeMobileSheets(){
        document.querySelector('.us-library')?.classList.remove('mobile-open');
        document.querySelector('.us-properties-panel')?.classList.remove('mobile-open');
        document.querySelectorAll('.us-mobile-nav button').forEach(b=>b.classList.remove('active'));
    }
    function openPanel(name,{toggleMobile=false}={}){
        const library=document.querySelector('.us-library');
        const props=document.querySelector('.us-properties-panel');
        const alreadyOpen=window.innerWidth<=760 && library?.classList.contains('mobile-open') && panelSections.some(s=>!s.hidden && s.dataset.panelSection===name);
        railButtons.forEach(b=>b.classList.toggle('active', b.dataset.panelTarget===name));
        panelSections.forEach(s=>s.hidden = s.dataset.panelSection!==name);
        document.body.classList.add('us-panel-open');
        if(window.innerWidth<=760){
            props?.classList.remove('mobile-open');
            if(toggleMobile && alreadyOpen){library?.classList.remove('mobile-open');document.querySelectorAll('.us-mobile-nav button').forEach(b=>b.classList.remove('active'));return;}
            library?.classList.add('mobile-open');
        }else setLibraryCollapsed(false);
    }
    railButtons.forEach(btn=>btn.addEventListener('click',()=>openPanel(btn.dataset.panelTarget,{toggleMobile:!!btn.closest('.us-mobile-nav')})));
    $('openCustomerDataPanel')?.addEventListener('click',()=>openPanel('customer-data'));
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

    document.getElementById('toggleLibrary')?.addEventListener('click',()=>setLibraryCollapsed(!studioBody?.classList.contains('library-collapsed')));
    document.getElementById('toggleProperties')?.addEventListener('click',()=>setPropertiesCollapsed(!studioBody?.classList.contains('properties-collapsed')));

    const workspaceEl=document.querySelector('.us-workspace');
    function setPagesCollapsed(collapsed){
        workspaceEl?.classList.toggle('pages-collapsed',collapsed);
        const btn=$('togglePagesPanel');
        if(btn){btn.textContent=collapsed?'▴ Canvas':'Canvas ▾';btn.setAttribute('aria-expanded',collapsed?'false':'true');}
        try{sessionStorage.setItem('undangantaStudioPagesCollapsed',collapsed?'1':'0')}catch(e){}
    }
    $('togglePagesPanel')?.addEventListener('click',()=>setPagesCollapsed(!workspaceEl?.classList.contains('pages-collapsed')));
    try{
        const savedPages=sessionStorage.getItem('undangantaStudioPagesCollapsed');
        setPagesCollapsed(savedPages===null ? window.innerWidth<=760 : savedPages==='1');
    }catch(e){setPagesCollapsed(window.innerWidth<=760)}

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

    document.getElementById('addPageMain')?.addEventListener('click',()=>document.getElementById('addPage')?.click());
    document.getElementById('homeStudio')?.addEventListener('click',()=>window.location.href=@json(route('admin.studio.index')));

    const zoomRange = document.getElementById('zoomRange');
    const zoomValue = document.getElementById('zoomValue');
    function applyZoom(v){
        v = Math.max(25,Math.min(120,Number(v||70)));
        const shell=document.getElementById('canvasShell');
        if(shell){shell.style.setProperty('--us-zoom', v/100);}
        if(zoomValue) zoomValue.textContent=v+'%';
        if(zoomRange) zoomRange.value=v;
    }
    zoomRange?.addEventListener('input',e=>applyZoom(e.target.value));
    function fitCanvasForViewport(){
        if(window.innerWidth<=760){
            const available=Math.max(260,window.innerWidth-24);
            applyZoom(Math.min(88,Math.max(58,(available/390)*100)));
        }else applyZoom(Number(zoomRange?.value||68));
    }
    fitCanvasForViewport();
    window.addEventListener('resize',()=>{if(window.innerWidth<=760)fitCanvasForViewport();});
    if(window.innerWidth<=760){
        panelSections.forEach(s=>s.hidden=s.dataset.panelSection!=='template');
        railButtons.forEach(b=>b.classList.toggle('active',b.dataset.panelTarget==='template'&&!b.closest('.us-mobile-nav')));
        closeMobileSheets();
    }else openPanel('template');
    if(window.innerWidth>760){
        let libCollapsed=true, propsCollapsed=true;
        try{
            const l=sessionStorage.getItem('undangantaStudioLibraryCollapsed'),p=sessionStorage.getItem('undangantaStudioPropsCollapsed');
            libCollapsed=l===null?true:l==='1';propsCollapsed=p===null?true:p==='1';
        }catch(e){}
        setLibraryCollapsed(libCollapsed);setPropertiesCollapsed(propsCollapsed);
    }

    renderAssets();renderFonts();syncCustomerForm();render();auditStudioControls();setSaveStatus('Tersimpan','saved');
})();
</script>
@endsection
