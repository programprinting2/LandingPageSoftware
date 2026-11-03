    <div id="admin-transactions" class="dash-page">
      <div class="dash-header"><h1>Transaksi</h1><p>Laporan transaksi berhasil dan invoice payment gateway (Komerce)</p></div>

      <div class="dash-tabs" style="margin-bottom:18px;">
        <button type="button" class="dash-tab active" data-admin-trx-tab="ledger" onclick="switchAdminTransactionsTab('ledger', this)">Transaksi Berhasil</button>
        <button type="button" class="dash-tab" data-admin-trx-tab="invoices" onclick="switchAdminTransactionsTab('invoices', this)">Invoice Gateway</button>
      </div>

      <div id="adminTransactionsLedgerPanel">
        <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:18px;">
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(56,126,245,0.1)">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
            </div>
            <div>
              <div class="stat-label">Total Transaksi</div>
              <div class="stat-value" id="adminTransactionsTotalCount">—</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,0.1)">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
              <div class="stat-label">Total Pendapatan</div>
              <div class="stat-value" id="adminTransactionsTotalAmount">—</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,166,35,0.1)">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div>
              <div class="stat-label">Transaksi Bulan Ini</div>
              <div class="stat-value" id="adminTransactionsMonthCount">—</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(6,191,212,0.1)">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-2)" stroke-width="1.8" stroke-linecap="round"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
            </div>
            <div>
              <div class="stat-label">Pendapatan Bulan Ini</div>
              <div class="stat-value" id="adminTransactionsMonthAmount">—</div>
            </div>
          </div>
        </div>

        <div class="dash-table-wrap">
          <div class="dash-table-header">
            <h3 id="adminTransactionsCount">Memuat…</h3>
          </div>
          <div class="admin-affiliate-trx-toolbar admin-trx-page-toolbar">
            <div class="admin-affiliate-trx-filters" id="adminTransactionsLedgerFilters" role="group" aria-label="Filter status">
              <button type="button" class="admin-affiliate-trx-filter is-active" data-status="all" onclick="setAdminTransactionsLedgerStatusFilter('all')">Semua</button>
              <button type="button" class="admin-affiliate-trx-filter" data-status="paid" onclick="setAdminTransactionsLedgerStatusFilter('paid')">Paid</button>
              <button type="button" class="admin-affiliate-trx-filter" data-status="expired" onclick="setAdminTransactionsLedgerStatusFilter('expired')">Expired</button>
            </div>
            <span id="adminTransactionsLedgerMeta" class="admin-affiliate-trx-meta"></span>
          </div>
          <table class="dash-table">
            <thead>
              <tr>
                <th>Referensi</th>
                <th>Client</th>
                <th>Tipe</th>
                <th>Deskripsi</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Kode voucher</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Gateway</th>
              </tr>
            </thead>
            <tbody id="adminTransactionsTableBody">
              <tr><td colspan="10" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>
            </tbody>
          </table>
          <div id="adminTransactionsLedgerPagination" class="dash-pagination admin-trx-page-pagination" hidden></div>
        </div>
      </div>

      <div id="adminTransactionsInvoicesPanel" hidden>
        <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:18px;">
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(56,126,245,0.1)">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
            </div>
            <div>
              <div class="stat-label">Total Invoice</div>
              <div class="stat-value" id="adminPaymentOrdersTotalCount">—</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,166,35,0.1)">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <div>
              <div class="stat-label">Pending</div>
              <div class="stat-value" id="adminPaymentOrdersPendingCount">—</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,0.1)">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div>
              <div class="stat-label">Paid</div>
              <div class="stat-value" id="adminPaymentOrdersPaidCount">—</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(240,104,104,0.1)">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--rose)" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
            </div>
            <div>
              <div class="stat-label">Expired</div>
              <div class="stat-value" id="adminPaymentOrdersExpiredCount">—</div>
            </div>
          </div>
        </div>

        <div class="dash-table-wrap">
          <div class="dash-table-header">
            <h3 id="adminPaymentOrdersCount">Memuat…</h3>
            <div class="admin-invoice-toolbar">
              <input type="search" id="adminPaymentOrdersSearch" class="admin-invoice-search" placeholder="Payment ID / Nama Customer" oninput="debounceAdminPaymentOrdersSearch()">
            </div>
          </div>
          <div class="admin-affiliate-trx-toolbar admin-trx-page-toolbar">
            <div class="admin-affiliate-trx-filters" id="adminPaymentOrdersStatusFilters" role="group" aria-label="Filter status">
              <button type="button" class="admin-affiliate-trx-filter is-active" data-status="all" onclick="setAdminPaymentOrdersStatusFilter('all')">Semua</button>
              <button type="button" class="admin-affiliate-trx-filter" data-status="paid" onclick="setAdminPaymentOrdersStatusFilter('paid')">Paid</button>
              <button type="button" class="admin-affiliate-trx-filter" data-status="expired" onclick="setAdminPaymentOrdersStatusFilter('expired')">Expired</button>
            </div>
            <span id="adminPaymentOrdersTableMeta" class="admin-affiliate-trx-meta"></span>
          </div>
          <table class="dash-table admin-invoice-table">
            <thead>
              <tr>
                <th>Payment ID</th>
                <th>Customer Name</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Kode voucher</th>
                <th>Due Date</th>
                <th>Detail</th>
              </tr>
            </thead>
            <tbody id="adminPaymentOrdersTableBody">
              <tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>
            </tbody>
          </table>
          <div id="adminPaymentOrdersPagination" class="dash-pagination admin-trx-page-pagination" hidden></div>
        </div>
      </div>
    </div>
