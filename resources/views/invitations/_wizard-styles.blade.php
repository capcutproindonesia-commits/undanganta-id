<style>
/* =========================================================
   UNDANGANTA.ID — ORDER/WIZARD SHARED UI
   Clean, compact, blue, no glass/gradient.
========================================================= */

:root{
    --ua-bg:#f6f8fb;
    --ua-card:#ffffff;
    --ua-line:#dbe5f3;
    --ua-line-strong:#c8d8ec;
    --ua-text:#172033;
    --ua-muted:#68758b;
    --ua-soft:#eef5ff;
    --ua-primary:#2f6fed;
    --ua-primary-dark:#2359c5;
    --ua-danger:#a63a3a;
    --ua-danger-bg:#fff7f7;
    --ua-success:#247a4b;
    --ua-success-bg:#f1fbf5;
}

.ua-flow,
.order-flow{
    width:min(1080px,calc(100% - 32px));
    margin:0 auto;
    padding:34px 0 56px;
    color:var(--ua-text);
}

.ua-flow-head,
.flow-top{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:18px;
    margin-bottom:20px;
}

.ua-eyebrow,
.flow-eyebrow{
    margin-bottom:6px;
    color:#61708a;
    font-size:11px;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.ua-title,
.flow-title{
    margin:0;
    color:var(--ua-text);
    font-size:32px;
    line-height:1.1;
    letter-spacing:-.035em;
}

.ua-copy,
.flow-copy{
    max-width:660px;
    margin:8px 0 0;
    color:var(--ua-muted);
    font-size:13px;
    line-height:1.6;
}

.ua-back{
    color:#315fca;
    text-decoration:none;
    font-size:12px;
    font-weight:750;
}

.ua-steps,
.flow-steps{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:8px;
    margin:0 0 22px;
    padding:0;
    list-style:none;
}

.ua-step,
.flow-step{
    min-width:0;
    padding:10px 12px;
    border:1px solid var(--ua-line);
    border-radius:10px;
    background:#fff;
    color:#758299;
    font-size:10px;
    font-weight:800;
    text-align:center;
}

.ua-step.active,
.flow-step.active{
    border-color:#8db6ff;
    background:var(--ua-soft);
    color:#285fc6;
}

.ua-panel,
.flow-panel{
    min-width:0;
    padding:20px;
    border:1px solid var(--ua-line);
    border-radius:16px;
    background:var(--ua-card);
}

.flow-summary{
    display:grid;
    grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);
    gap:16px;
    align-items:start;
}

.flow-card-name{
    margin-bottom:14px;
    color:var(--ua-text);
    font-size:14px;
    font-weight:850;
    letter-spacing:-.01em;
}

.summary-list{
    display:grid;
    gap:0;
    margin:0;
}

.summary-row{
    display:grid;
    grid-template-columns:minmax(90px,.7fr) minmax(0,1.3fr);
    gap:16px;
    padding:11px 0;
    border-bottom:1px solid #edf1f6;
}

.summary-row:first-child{padding-top:0}
.summary-row:last-child{padding-bottom:0;border-bottom:0}

.summary-row dt{
    margin:0;
    color:#7a879a;
    font-size:10px;
    font-weight:750;
}

.summary-row dd{
    min-width:0;
    margin:0;
    color:#233049;
    font-size:12px;
    font-weight:700;
    word-break:break-word;
}

.flow-button,
.flow-button-secondary,
.ua-btn{
    display:inline-flex;
    min-height:40px;
    align-items:center;
    justify-content:center;
    box-sizing:border-box;
    padding:0 14px;
    border:1px solid transparent;
    border-radius:10px;
    background:var(--ua-primary);
    color:#fff;
    text-decoration:none;
    font-size:11px;
    font-weight:800;
    line-height:1;
    cursor:pointer;
}

.flow-button:hover,
.ua-btn:hover{
    background:var(--ua-primary-dark);
}

.flow-button:disabled{
    cursor:not-allowed;
    opacity:.55;
}

.flow-button-secondary{
    border-color:var(--ua-line);
    background:#fff;
    color:#34425a;
}

.flow-button-secondary:hover{
    border-color:#bfcde0;
    background:#f8fafc;
}

.flow-alert,
.flow-error,
.ua-error{
    margin:0 0 16px;
    padding:12px 14px;
    border-radius:10px;
    font-size:11px;
    line-height:1.5;
}

.flow-alert{
    border:1px solid #cde9d8;
    background:var(--ua-success-bg);
    color:var(--ua-success);
}

.flow-error,
.ua-error{
    border:1px solid #f0caca;
    background:var(--ua-danger-bg);
    color:var(--ua-danger);
}

.field{
    display:grid;
    gap:6px;
    margin-top:14px;
}

.field label{
    color:#4d5a70;
    font-size:10px;
    font-weight:800;
}

.field input,
.field select,
.field textarea{
    width:100%;
    max-width:100%;
    box-sizing:border-box;
    border:1px solid #cfdbec;
    border-radius:10px;
    background:#fff;
    color:var(--ua-text);
    font:inherit;
    font-size:12px;
    outline:none;
}

.field input,
.field select{
    min-height:42px;
    padding:0 12px;
}

.field textarea{
    min-height:92px;
    padding:10px 12px;
    resize:vertical;
}

.field input:focus,
.field select:focus,
.field textarea:focus{
    border-color:#78a5f7;
    box-shadow:0 0 0 3px rgba(47,111,237,.08);
}

.field input[type="file"]{
    min-height:46px;
    padding:6px 8px;
    background:#fbfcfe;
    cursor:pointer;
}

.field input[type="file"]::file-selector-button{
    height:32px;
    margin-right:10px;
    padding:0 12px;
    border:1px solid #cfdbea;
    border-radius:8px;
    background:#fff;
    color:#34425a;
    font-size:10px;
    font-weight:800;
    cursor:pointer;
}

.payment-note{
    margin:10px 0 0;
    color:#6f7c91;
    font-size:10px;
    line-height:1.55;
}

/* New wizard screens */
.ua-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:14px;
}

.ua-card{
    display:flex;
    min-height:285px;
    flex-direction:column;
    padding:18px;
    border:1px solid var(--ua-line);
    border-radius:14px;
    background:#fff;
}

.ua-card.locked{opacity:.55}
.ua-card-title{font-size:18px;font-weight:850}
.ua-card-copy{margin-top:6px;color:#6e7b91;font-size:12px;line-height:1.55}
.ua-badge{display:inline-flex;width:max-content;margin-bottom:12px;padding:5px 8px;border-radius:999px;background:var(--ua-soft);color:#315fca;font-size:9px;font-weight:850;text-transform:uppercase;letter-spacing:.06em}
.ua-actions{display:flex;gap:8px;margin-top:auto;padding-top:18px}
.ua-btn.light{border-color:var(--ua-line);background:#fff;color:#34425a}
.ua-btn.disabled{pointer-events:none;background:#eef1f5;color:#9aa4b3}
.ua-form{padding:20px}
.ua-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.ua-field{display:grid;gap:6px}
.ua-field.full{grid-column:1/-1}
.ua-field label{font-size:10px;font-weight:800;color:#4d5a70}
.ua-input,.ua-textarea{width:100%;box-sizing:border-box;border:1px solid #cfdbec;border-radius:10px;background:#fff;color:#172033;font:inherit;font-size:13px;outline:none}
.ua-input{height:42px;padding:0 12px}.ua-textarea{min-height:90px;padding:10px 12px;resize:vertical}
.ua-input:focus,.ua-textarea:focus{border-color:#78a5f7;box-shadow:0 0 0 3px rgba(47,111,237,.08)}
.ua-summary{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px;padding:14px 16px;border:1px solid var(--ua-line);border-radius:12px;background:#f9fbfe}
.ua-summary strong{font-size:13px}.ua-summary span{color:#748198;font-size:11px}
.ua-submit{display:flex;justify-content:flex-end;margin-top:18px}

/* keep checkout payment blocks clean */
.payment-section{margin-top:16px}
.payment-method-card{
    border-color:#dbe5f3 !important;
    border-radius:12px !important;
    box-shadow:none !important;
}
.payment-method-card::before{
    background:var(--ua-primary) !important;
}
.payment-warning{
    background:#f7f9fc !important;
    color:#657289 !important;
}
.copy-payment{
    border-color:#d6e0ec !important;
}
.copy-payment:hover{
    border-color:var(--ua-primary) !important;
    background:var(--ua-primary) !important;
}

@media(max-width:820px){
    .ua-grid{grid-template-columns:1fr}
    .ua-fields{grid-template-columns:1fr}
    .ua-field.full{grid-column:auto}
    .ua-flow-head,.flow-top{align-items:flex-start;flex-direction:column}
    .ua-steps,.flow-steps{grid-template-columns:repeat(2,minmax(0,1fr))}
    .ua-title,.flow-title{font-size:27px}
    .flow-summary{grid-template-columns:1fr}
}

@media(max-width:560px){
    .ua-flow,.order-flow{
        width:min(100% - 24px,1080px);
        padding:24px 0 38px;
    }
    .ua-panel,.flow-panel{padding:16px}
    .summary-row{grid-template-columns:1fr;gap:4px}
    .ua-summary{align-items:flex-start;flex-direction:column}
}
</style>