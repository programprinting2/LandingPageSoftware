<div class="modal-overlay" id="appDialogModal">
  <div class="glass modal-box" style="max-width:420px;">
    <h3 id="appDialogTitle" style="margin-bottom:10px;font-size:18px;">Konfirmasi</h3>
    <p id="appDialogMessage" style="font-size:14px;color:var(--text-2);line-height:1.55;margin-bottom:22px;">—</p>
    <div style="display:flex;justify-content:flex-end;gap:10px;">
      <button type="button" class="btn btn-secondary" id="appDialogCancelBtn" onclick="closeAppDialog(false)">Batal</button>
      <button type="button" class="btn btn-primary" id="appDialogOkBtn" onclick="closeAppDialog(true)">OK</button>
    </div>
  </div>
</div>
