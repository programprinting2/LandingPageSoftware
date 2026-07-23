    <div id="admin-clients" class="dash-page">
      <div class="dash-header"><h1>Manajemen Client</h1><p>Kelola semua client terdaftar</p></div>
      <div class="dash-table-wrap">
        <div class="dash-table-header">
          <h3 id="adminClientsCount">{{ $clients->count() }} client terdaftar</h3>
          <button type="button" class="btn btn-primary" style="padding:8px 16px;font-size:12px;" onclick="openAdminClientModal()">+ Tambah Client</button>
        </div>
        <table class="dash-table">
          <thead>
            <tr>
              <th>Nama Perusahaan</th>
              <th>Kontak</th>
              <th>Email</th>
              <th>Aplikasi</th>
              <th>WhatsApp</th>
              <th>Status</th>
              <th>Terdaftar</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="adminClientsTableBody">
            @forelse ($clients as $client)
              @php
                $statusBadge = match ($client->status) {
                    'active' => 'badge-active',
                    'trial' => 'badge-trial',
                    'expired' => 'badge-expired',
                    default => 'badge-pending',
                };
                $statusLabel = ucfirst($client->status);
              @endphp
              <tr data-client-id="{{ $client->id }}">
                <td class="td-bold">{{ $client->company_name }}</td>
                <td>{{ $client->user?->name ?? '—' }}</td>
                <td>{{ $client->user?->email ?? '—' }}</td>
                <td>{{ $client->applications->pluck('name')->join(', ') ?: '—' }}</td>
                <td>{{ $client->whatsapp }}</td>
                <td><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                <td>{{ $client->created_at?->format('d M Y') ?? '—' }}</td>
                <td>
                  <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-right:4px;" onclick="openAdminClientModal({{ $client->id }})">Edit</button>
                  <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;color:var(--rose);" onclick="deleteAdminClient({{ $client->id }})">Hapus</button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" style="text-align:center; color:var(--text-3); padding:28px;">
                  Belum ada client terdaftar.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-overlay admin-app-overlay" id="adminClientModal">
      <div class="admin-app-modal">
        <div class="admin-app-modal-header">
          <div>
            <h3 id="adminClientModalTitle">Tambah Client</h3>
            <p>Kelola data client dan aplikasi yang dibeli</p>
          </div>
          <button type="button" class="admin-app-modal-close" onclick="closeAdminClientModal()" aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>
        </div>

        <div id="adminClientAlert" class="admin-app-alert" hidden></div>

        <form id="adminClientForm" onsubmit="submitAdminClientForm(event)" novalidate>
          <input type="hidden" id="adminClientId" value="">

          <div class="admin-app-shell">
            <aside class="admin-app-side">
              <nav class="admin-app-nav">
                <button type="button" class="admin-app-nav-item active" data-panel="general" onclick="switchAdminClientPanel('general', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  Informasi Umum
                </button>
                <button type="button" class="admin-app-nav-item" data-panel="applications" onclick="switchAdminClientPanel('applications', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/><rect x="15" y="15" width="6" height="6" rx="1"/></svg>
                  Aplikasi & Status
                </button>
              </nav>
            </aside>

            <div class="admin-app-main">
              <section class="admin-app-panel active" data-panel="general">
                <div class="admin-app-panel-head">
                  <h4>Informasi Umum</h4>
                  <p>Data kontak dan akun login client</p>
                </div>
                <div class="admin-app-panel-body">
                  <div class="form-group">
                    <label class="admin-app-label" for="adminClientName">Nama Lengkap</label>
                    <input class="admin-app-input" id="adminClientName" type="text" required placeholder="Nama kontak">
                  </div>
                  <div class="form-group">
                    <label class="admin-app-label" for="adminClientCompany">Nama Perusahaan</label>
                    <input class="admin-app-input" id="adminClientCompany" type="text" required placeholder="Nama perusahaan">
                  </div>
                  <div class="admin-app-form-grid">
                    <div class="form-group">
                      <label class="admin-app-label" for="adminClientEmail">Email</label>
                      <input class="admin-app-input" id="adminClientEmail" type="email" required placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                      <label class="admin-app-label" for="adminClientWhatsapp">No. WhatsApp</label>
                      <input class="admin-app-input" id="adminClientWhatsapp" type="text" placeholder="08xxxxxxxxxx" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="admin-app-label" for="adminClientPassword">Password <span class="admin-app-label-hint" id="adminClientPasswordHint">(wajib)</span></label>
                    <input class="admin-app-input" id="adminClientPassword" type="password" minlength="8" placeholder="Minimal 8 karakter">
                  </div>
                </div>
              </section>

              <section class="admin-app-panel" data-panel="applications">
                <div class="admin-app-panel-head">
                  <h4>Aplikasi & Status</h4>
                  <p>Pilih aplikasi yang dibeli dan status akun client</p>
                </div>
                <div class="admin-app-panel-body">
                  <div class="form-group">
                    <label class="admin-app-label">Aplikasi yang dibeli</label>
                    <div id="adminClientApplications" class="admin-app-checklist admin-app-checklist-light">Memuat daftar aplikasi…</div>
                    <p class="admin-app-label-hint" style="margin-top:8px;">Centang aplikasi, lalu pilih paket (Trial, Basic, Pro, dll.) sesuai Master Aplikasi.</p>
                  </div>
                  <div class="form-group" style="margin-bottom:0;">
                    <label class="admin-app-label" for="adminClientStatus">Status</label>
                    <select class="admin-app-input" id="adminClientStatus" required>
                      <option value="active">Active</option>
                      <option value="trial">Trial</option>
                      <option value="expired">Expired</option>
                      <option value="pending">Pending</option>
                    </select>
                  </div>
                </div>
              </section>
            </div>
          </div>

          <div class="admin-app-footer">
            <button type="button" class="admin-app-btn-cancel" onclick="closeAdminClientModal()">Batal</button>
            <button type="submit" class="admin-app-btn-save" id="adminClientSubmitBtn">Simpan</button>
          </div>
        </form>
      </div>
    </div>
