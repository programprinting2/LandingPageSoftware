<div class="modal-overlay" id="clientReportArchiveModal" hidden aria-hidden="true">
  <div class="modal-box client-report-archive-modal-box">
    <button class="modal-close" type="button" onclick="closeClientReportArchiveModal()" aria-label="Tutup">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <div class="client-report-archive-head">
      <h2 id="clientReportArchiveTitle">Password arsip laporan</h2>
      <p id="clientReportArchiveSubtitle">—</p>
    </div>
    <div id="clientReportArchiveFeedback" class="client-report-archive-feedback" hidden role="status" aria-live="polite"></div>
    <div class="form-group">
      <label class="form-label" for="clientReportArchivePassword">Password arsip baru</label>
      <div class="form-input-wrap">
        <input class="form-input" id="clientReportArchivePassword" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label" for="clientReportArchivePasswordConfirm">Konfirmasi password</label>
      <div class="form-input-wrap">
        <input class="form-input" id="clientReportArchivePasswordConfirm" type="password" autocomplete="new-password" placeholder="Ulangi password">
      </div>
    </div>
    <button type="button" class="btn btn-primary" id="clientReportArchiveSaveBtn" style="margin-top:8px;">Simpan password arsip</button>
  </div>
</div>
