    <div id="admin-promo-vouchers" class="dash-page">
      <div class="dash-header"><h1>Voucher Promo</h1><p>Kode diskon admin tanpa komisi affiliator</p></div>
      <div class="dash-table-wrap">
        <div class="dash-table-header">
          <h3 id="adminPromoVouchersCount">— voucher</h3>
          <button type="button" class="btn btn-primary" style="padding:8px 16px;font-size:12px;" onclick="openAdminPromoVoucherModal()">Tambah voucher</button>
        </div>
        <table class="dash-table">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Label</th>
              <th>Aplikasi</th>
              <th>Diskon</th>
              <th>Dipakai (paid)</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="adminPromoVouchersTableBody">
            <tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-overlay admin-app-overlay" id="adminPromoVoucherModal">
      <div class="admin-app-modal">
        <div class="admin-app-modal-header">
          <div>
            <h3 id="adminPromoVoucherModalTitle">Tambah Voucher</h3>
            <p>Kode 6 karakter, diskon tanpa komisi affiliator</p>
          </div>
          <button type="button" class="admin-app-modal-close" onclick="closeAdminPromoVoucherModal()" aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <div id="adminPromoVoucherAlert" class="admin-app-alert" hidden></div>
        <form id="adminPromoVoucherForm" onsubmit="submitAdminPromoVoucherForm(event)" novalidate>
          <input type="hidden" id="adminPromoVoucherId" value="">
          <div class="admin-app-panel-body" style="padding:0 24px 8px;">
            <div class="form-group">
              <label class="admin-app-label" for="adminPromoVoucherCode">Kode voucher</label>
              <input class="admin-app-input" id="adminPromoVoucherCode" type="text" maxlength="6" required placeholder="PROMO1" style="font-family:var(--mono);text-transform:uppercase;">
            </div>
            <div class="form-group">
              <label class="admin-app-label" for="adminPromoVoucherLabel">Label <span class="admin-app-label-hint">(opsional, internal)</span></label>
              <input class="admin-app-input" id="adminPromoVoucherLabel" type="text" placeholder="Promo launch">
            </div>
            <div class="admin-app-form-grid">
              <div class="form-group">
                <label class="admin-app-label" for="adminPromoVoucherApplication">Aplikasi</label>
                <select class="admin-app-input" id="adminPromoVoucherApplication">
                  <option value="">Semua aplikasi</option>
                </select>
              </div>
              <div class="form-group">
                <label class="admin-app-label" for="adminPromoVoucherDiscount">Diskon (%)</label>
                <input class="admin-app-input" id="adminPromoVoucherDiscount" type="number" min="0" max="100" step="0.01" required placeholder="10">
              </div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="admin-app-label" for="adminPromoVoucherStatus">Status</label>
              <select class="admin-app-input" id="adminPromoVoucherStatus">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="admin-app-footer">
            <button type="button" class="admin-app-btn-cancel" onclick="closeAdminPromoVoucherModal()">Batal</button>
            <button type="submit" class="admin-app-btn-save" id="adminPromoVoucherSubmitBtn">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <div class="modal-overlay admin-app-overlay" id="adminPromoVoucherUsagesModal">
      <div class="admin-app-modal admin-affiliate-trx-modal">
        <div class="admin-app-modal-header">
          <div>
            <h3 id="adminPromoVoucherUsagesTitle">Pemakaian Voucher</h3>
            <p id="adminPromoVoucherUsagesSubtitle">Daftar checkout yang memakai kode ini</p>
          </div>
          <button type="button" class="admin-app-modal-close" onclick="closeAdminPromoVoucherUsagesModal()" aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="admin-affiliate-trx-summary">
          <div class="admin-affiliate-trx-stat">
            <span class="admin-affiliate-trx-stat-label">Total</span>
            <strong id="adminPromoVoucherUsagesTotal">—</strong>
          </div>
          <div class="admin-affiliate-trx-stat">
            <span class="admin-affiliate-trx-stat-label">Paid</span>
            <strong id="adminPromoVoucherUsagesPaid">—</strong>
          </div>
          <div class="admin-affiliate-trx-stat">
            <span class="admin-affiliate-trx-stat-label">Pending</span>
            <strong id="adminPromoVoucherUsagesPending">—</strong>
          </div>
          <div class="admin-affiliate-trx-stat">
            <span class="admin-affiliate-trx-stat-label">Total Paid</span>
            <strong id="adminPromoVoucherUsagesPaidAmount" class="td-green">—</strong>
          </div>
        </div>
        <div class="admin-affiliate-trx-toolbar">
          <div class="admin-affiliate-trx-filters" id="adminPromoVoucherUsagesFilters" role="group" aria-label="Filter status">
            <button type="button" class="admin-affiliate-trx-filter is-active" data-status="all" onclick="setAdminPromoVoucherUsagesStatusFilter('all')">Semua</button>
            <button type="button" class="admin-affiliate-trx-filter" data-status="paid" onclick="setAdminPromoVoucherUsagesStatusFilter('paid')">Paid</button>
            <button type="button" class="admin-affiliate-trx-filter" data-status="expired" onclick="setAdminPromoVoucherUsagesStatusFilter('expired')">Expired</button>
          </div>
          <span id="adminPromoVoucherUsagesTableMeta" class="admin-affiliate-trx-meta"></span>
        </div>
        <div class="admin-affiliate-trx-table-wrap">
          <table class="dash-table">
            <thead>
              <tr>
                <th>Referensi</th>
                <th>Kode voucher</th>
                <th>Client</th>
                <th>Deskripsi</th>
                <th>Jumlah</th>
                <th>Potongan</th>
                <th>Status</th>
                <th>Tanggal</th>
              </tr>
            </thead>
            <tbody id="adminPromoVoucherUsagesBody">
              <tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>
            </tbody>
          </table>
        </div>
        <div id="adminPromoVoucherUsagesPagination" class="dash-pagination admin-affiliate-trx-pagination" hidden></div>
        <div class="admin-app-footer">
          <button type="button" class="admin-app-btn-cancel" onclick="closeAdminPromoVoucherUsagesModal()">Tutup</button>
        </div>
      </div>
    </div>
