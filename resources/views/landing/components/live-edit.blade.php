<div id="liveEditBar" class="live-edit-bar">
  <div class="live-edit-bar-inner">
    <span class="live-edit-bar-badge">Mode Edit Live</span>
    <span class="live-edit-bar-hint">Klik <strong>Edit</strong> di setiap bagian → ubah konten → <strong>Simpan</strong></span>
    <a href="{{ route('landing') }}" target="_blank" rel="noopener" class="live-edit-bar-link">Lihat tanpa edit</a>
  </div>
</div>

<div id="liveEditOverlay" class="live-edit-overlay" hidden>
  <div class="live-edit-modal" role="dialog" aria-modal="true" aria-labelledby="liveEditModalTitle">
    <div class="live-edit-modal-head">
      <div>
        <h3 id="liveEditModalTitle">Edit Section</h3>
        <p id="liveEditModalDesc">Perubahan langsung tersimpan ke database saat Anda klik Simpan.</p>
      </div>
      <button type="button" class="live-edit-modal-close" onclick="closeLiveEditModal()" aria-label="Tutup">&times;</button>
    </div>
    <div id="liveEditAlert" class="live-edit-alert" hidden></div>
    <div id="liveEditFormBody" class="live-edit-modal-body"></div>
    <div class="live-edit-modal-foot">
      <button type="button" class="btn btn-secondary" onclick="closeLiveEditModal()">Batal</button>
      <button type="button" class="btn btn-primary" id="liveEditSaveBtn" onclick="saveLiveEditSection()">Simpan</button>
    </div>
  </div>
</div>
