    <div id="admin-affiliates" class="dash-page">
      <div class="dash-header"><h1>Manajemen Affiliate</h1><p>Kelola dan approve pendaftaran affiliate</p></div>
      <div class="dash-table-wrap">
        <div class="dash-table-header">
          <h3 id="adminAffiliatesCount">{{ $affiliates->count() }} affiliate terdaftar</h3>
          <button type="button" class="btn btn-primary" style="padding:8px 16px;font-size:12px;" onclick="openAdminAffiliateModal()">+ Tambah Affiliate</button>
        </div>
        <table class="dash-table">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Sales</th>
              <th>Total Komisi</th>
              <th>Pending</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="adminAffiliatesTableBody">
            @forelse ($affiliates as $affiliate)
              @php
                $statusBadge = match ($affiliate->status) {
                    'active' => 'badge-active',
                    'pending' => 'badge-pending',
                    'suspended' => 'badge-expired',
                    default => 'badge-pending',
                };
                $statusLabel = match ($affiliate->status) {
                    'active' => 'Active',
                    'pending' => 'Pending',
                    'suspended' => 'Suspended',
                    default => ucfirst($affiliate->status),
                };
              @endphp
              <tr data-affiliate-id="{{ $affiliate->id }}">
                <td class="td-mono">{{ $affiliate->code }}</td>
                <td class="td-bold">{{ $affiliate->user?->name ?? '—' }}</td>
                <td>{{ $affiliate->user?->email ?? '—' }}</td>
                <td>{{ number_format($affiliate->sales_count) }}</td>
                <td class="td-green">Rp {{ number_format((float) $affiliate->total_commission, 0, ',', '.') }}</td>
                <td class="td-amber">Rp {{ number_format((float) $affiliate->pending_commission, 0, ',', '.') }}</td>
                <td><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                <td>
                  <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-right:4px;" onclick="openAdminAffiliateTransactionsModal({{ $affiliate->id }})">Detail</button>
                  <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-right:4px;" onclick="openAdminAffiliateModal({{ $affiliate->id }})">Edit</button>
                  <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;color:var(--rose);" onclick="deleteAdminAffiliate({{ $affiliate->id }})">Hapus</button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" style="text-align:center;color:var(--text-3);padding:28px;">
                  Belum ada affiliate terdaftar.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-overlay admin-app-overlay" id="adminAffiliateModal">
      <div class="admin-app-modal">
        <div class="admin-app-modal-header">
          <div>
            <h3 id="adminAffiliateModalTitle">Tambah Affiliate</h3>
            <p>Kelola profil affiliate, kode referral, dan rekening komisi</p>
          </div>
          <button type="button" class="admin-app-modal-close" onclick="closeAdminAffiliateModal()" aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>
        </div>

        <div id="adminAffiliateAlert" class="admin-app-alert" hidden></div>

        <form id="adminAffiliateForm" onsubmit="submitAdminAffiliateForm(event)" novalidate>
          <input type="hidden" id="adminAffiliateId" value="">

          <div class="admin-app-shell">
            <aside class="admin-app-side">
              <nav class="admin-app-nav">
                <button type="button" class="admin-app-nav-item active" data-panel="general" onclick="switchAdminAffiliatePanel('general', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  Informasi Umum
                </button>
                <button type="button" class="admin-app-nav-item" data-panel="payout" onclick="switchAdminAffiliatePanel('payout', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                  Rekening & Status
                </button>
              </nav>
            </aside>

            <div class="admin-app-main">
              <section class="admin-app-panel active" data-panel="general">
                <div class="admin-app-panel-head">
                  <h4>Informasi Umum</h4>
                  <p>Data kontak dan akun login affiliate</p>
                </div>
                <div class="admin-app-panel-body">
                  <div class="form-group">
                    <label class="admin-app-label" for="adminAffiliateName">Nama</label>
                    <input class="admin-app-input" id="adminAffiliateName" type="text" required placeholder="Nama affiliate">
                  </div>
                  <div class="admin-app-form-grid">
                    <div class="form-group">
                      <label class="admin-app-label" for="adminAffiliateEmail">Email</label>
                      <input class="admin-app-input" id="adminAffiliateEmail" type="email" required placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                      <label class="admin-app-label" for="adminAffiliateWhatsapp">No. WhatsApp</label>
                      <input class="admin-app-input" id="adminAffiliateWhatsapp" type="text" placeholder="08xxxxxxxxxx">
                    </div>
                  </div>
                    <div class="form-group">
                    <label class="admin-app-label" for="adminAffiliateCode">Kode Affiliate <span class="admin-app-label-hint">(kosongkan untuk auto: AA0001)</span></label>
                    <input class="admin-app-input" id="adminAffiliateCode" type="text" placeholder="AA0001" maxlength="6" style="font-family:var(--mono);text-transform:uppercase;">
                    <p class="admin-app-label-hint" style="margin-top:8px;">Format: <strong>A</strong> (Affiliate) + <strong>huruf nama depan</strong> + <strong>nomor urut 4 digit</strong>. Contoh: Andi → AA0001, Budi → AB0001, Andi ke-2 → AA0002.</p>
                  </div>
                  <div class="form-group" style="margin-bottom:0;">
                    <label class="admin-app-label" for="adminAffiliatePassword">Password <span class="admin-app-label-hint" id="adminAffiliatePasswordHint">(wajib)</span></label>
                    <input class="admin-app-input" id="adminAffiliatePassword" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter">
                  </div>
                </div>
              </section>

              <section class="admin-app-panel" data-panel="payout">
                <div class="admin-app-panel-head">
                  <h4>Rekening & Status</h4>
                  <p>Status affiliate dan rekening pencairan komisi</p>
                </div>
                <div class="admin-app-panel-body">
                  <div class="form-group">
                    <label class="admin-app-label" for="adminAffiliateStatus">Status</label>
                    <select class="admin-app-input" id="adminAffiliateStatus">
                      <option value="pending">Pending</option>
                      <option value="active">Active</option>
                      <option value="suspended">Suspended</option>
                    </select>
                    <p class="admin-app-label-hint" style="margin-top:8px;">Pending = menunggu approval. Active = dapat referral. Suspended = dinonaktifkan.</p>
                  </div>
                  <div class="form-group">
                    <label class="admin-app-label" for="adminAffiliateBankName">Nama Bank</label>
                    <input class="admin-app-input" id="adminAffiliateBankName" type="text" placeholder="BCA, Mandiri, dll">
                  </div>
                  <div class="admin-app-form-grid">
                    <div class="form-group">
                      <label class="admin-app-label" for="adminAffiliateBankAccount">Nomor Rekening</label>
                      <input class="admin-app-input" id="adminAffiliateBankAccount" type="text" placeholder="1234567890">
                    </div>
                    <div class="form-group">
                      <label class="admin-app-label" for="adminAffiliateBankAccountName">Atas Nama</label>
                      <input class="admin-app-input" id="adminAffiliateBankAccountName" type="text" placeholder="Nama pemilik rekening">
                    </div>
                  </div>
                </div>
              </section>
            </div>
          </div>

          <div class="admin-app-footer">
            <button type="button" class="admin-app-btn-cancel" onclick="closeAdminAffiliateModal()">Batal</button>
            <button type="submit" class="admin-app-btn-save" id="adminAffiliateSubmitBtn">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <div class="modal-overlay admin-app-overlay" id="adminAffiliateTransactionsModal">
      <div class="admin-app-modal admin-affiliate-trx-modal">
        <div class="admin-app-modal-header">
          <div>
            <h3 id="adminAffiliateTransactionsTitle">Transaksi Referral</h3>
            <p id="adminAffiliateTransactionsSubtitle">Daftar transaksi dari kode affiliate</p>
          </div>
          <button type="button" class="admin-app-modal-close" onclick="closeAdminAffiliateTransactionsModal()" aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>
        </div>

        <div class="admin-affiliate-trx-summary">
          <div class="admin-affiliate-trx-stat">
            <span class="admin-affiliate-trx-stat-label">Total Transaksi</span>
            <strong id="adminAffiliateTrxTotalCount">—</strong>
          </div>
          <div class="admin-affiliate-trx-stat">
            <span class="admin-affiliate-trx-stat-label">Paid</span>
            <strong id="adminAffiliateTrxPaidCount">—</strong>
          </div>
          <div class="admin-affiliate-trx-stat">
            <span class="admin-affiliate-trx-stat-label">Pending</span>
            <strong id="adminAffiliateTrxPendingCount">—</strong>
          </div>
          <div class="admin-affiliate-trx-stat">
            <span class="admin-affiliate-trx-stat-label">Total Paid</span>
            <strong id="adminAffiliateTrxPaidAmount" class="td-green">—</strong>
          </div>
        </div>

        <div class="admin-affiliate-trx-table-wrap">
          <table class="dash-table">
            <thead>
              <tr>
                <th>Referensi</th>
                <th>Customer</th>
                <th>Paket</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Komisi</th>
                <th>Status</th>
                <th>Tanggal</th>
              </tr>
            </thead>
            <tbody id="adminAffiliateTransactionsBody">
              <tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>
            </tbody>
          </table>
        </div>

        <div class="admin-app-footer">
          <button type="button" class="admin-app-btn-cancel" onclick="closeAdminAffiliateTransactionsModal()">Tutup</button>
        </div>
      </div>
    </div>
