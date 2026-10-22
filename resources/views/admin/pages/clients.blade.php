    <div id="admin-users" class="dash-page">
      <div class="dash-header"><h1>Manajemen User</h1><p>Semua akun — client mengaktifkan affiliate sendiri dari portal client</p></div>
      <div class="dash-table-wrap">
        <div class="dash-table-header">
          <h3 id="adminUsersCount">{{ ($users ?? collect())->count() }} user terdaftar</h3>
          <button type="button" class="btn btn-primary" style="padding:8px 16px;font-size:12px;" onclick="openAdminClientModal()">+ Tambah Client</button>
        </div>
        <table class="dash-table admin-users-table">
          <thead>
            <tr>
              <th>Nama akun</th>
              <th>Email</th>
              <th>Role</th>
              <th>Aplikasi</th>
              <th>WhatsApp</th>
              <th>Status Client</th>
              <th>Affiliate</th>
              <th>Terdaftar</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="adminUsersTableBody">
            @forelse ($users ?? [] as $user)
              @php
                $client = $user->client;
                $statusBadge = match ($client?->status) {
                    'active' => 'badge-active',
                    'inactive' => 'badge-expired',
                    default => 'badge-pending',
                };
                $statusLabel = $client?->status === 'inactive' ? 'Nonaktif' : ($client ? 'Aktif' : '—');
                $roleLabel = match ($user->role) {
                    'super_admin' => 'Super Admin',
                    'admin' => 'Admin',
                    'affiliate' => 'Affiliate',
                    default => 'Client',
                };
                $roleBadge = match ($user->role) {
                    'super_admin' => 'badge-active',
                    'admin' => 'badge-pending',
                    'affiliate' => 'badge-pending',
                    default => 'badge-active',
                };
                $appNames = $client?->applications?->pluck('name')->values() ?? collect();
                $appCount = $appNames->count();
              @endphp
              <tr data-user-id="{{ $user->id }}" data-client-id="{{ $client?->id }}" @if($appCount > 0) data-application-names="{{ rawurlencode($appNames->toJson()) }}" @endif>
                <td class="td-bold">{{ $user->name }}</td>
                <td>
                  {{ $user->email }}
                  @if ($user->email_verified_at)
                    <span class="badge badge-active" style="font-size:10px;padding:2px 6px;margin-left:4px;">Verified</span>
                  @endif
                </td>
                <td><span class="badge {{ $roleBadge }}">{{ $roleLabel }}</span></td>
                <td>
                  @if ($appCount === 0)
                    —
                  @else
                    <button type="button" class="btn btn-secondary" style="padding:2px 10px;font-size:11px;" onclick="showAdminUserApplications({{ $user->id }})">{{ $appCount }} aplikasi</button>
                  @endif
                </td>
                <td>{{ $client?->whatsapp ?? '—' }}</td>
                <td>@if($client)<span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>@else — @endif</td>
                <td>
                  @if ($client)
                    @if ($user->affiliate && $user->affiliate->status === 'active')
                      <span class="badge badge-active">Aktif</span>
                    @else
                      <span class="badge badge-expired">Nonaktif</span>
                    @endif
                  @else
                    —
                  @endif
                </td>
                <td>{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                <td class="admin-users-actions-cell">
                  @if ($client)
                    <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="openAdminClientModal({{ $client->id }})">Edit</button>
                  @elseif (in_array($user->role, ['admin', 'super_admin'], true))
                    <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-right:4px;" onclick="openAdminUserEditModal({{ $user->id }})">Edit</button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" style="text-align:center; color:var(--text-3); padding:28px;">
                  Belum ada user terdaftar.
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
          <input type="hidden" id="adminClientUserId" value="">

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
                <button type="button" class="admin-app-nav-item" data-panel="affiliate" onclick="switchAdminClientPanel('affiliate', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                  Affiliate
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
                    <label class="admin-app-label" for="adminClientName">Nama akun</label>
                    <input class="admin-app-input" id="adminClientName" type="text" required placeholder="Nama akun">
                  </div>
                  <div class="admin-app-form-grid">
                    <div class="form-group">
                      <label class="admin-app-label" for="adminClientEmail">Email</label>
                      <div class="admin-client-email-row">
                        <input class="admin-app-input" id="adminClientEmail" type="email" required placeholder="email@example.com">
                        <label class="admin-client-email-verify" for="adminClientEmailVerified" title="Wajib untuk bisa login">
                          <input type="checkbox" id="adminClientEmailVerified">
                          <span>Terverifikasi</span>
                        </label>
                      </div>
                      <p class="admin-app-label-hint" style="margin-top:4px;">Centang jika email sudah dikonfirmasi — wajib agar client bisa login.</p>
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
                  <div class="form-group" id="adminClientUserRoleGroup" hidden>
                    <label class="admin-app-label" for="adminClientUserRole">Role akun</label>
                    <select class="admin-app-input admin-user-role-select" id="adminClientUserRole">
                      <option value="client">Client</option>
                      <option value="admin">Admin</option>
                      <option value="super_admin">Super Admin</option>
                    </select>
                    <p class="admin-app-label-hint" style="margin-top:6px;">Hanya super admin. Client wajib punya profil client.</p>
                  </div>
                  <input type="hidden" id="adminClientStatus" value="active">
                  <div id="adminClientAccountZone" class="admin-client-account-zone" hidden>
                    <div class="admin-client-account-zone-title">Kelola akun client</div>
                    <p class="admin-app-label-hint" style="margin:0 0 12px;">Nonaktifkan untuk memblokir login. Lisensi per aplikasi tidak berubah.</p>
                    <div class="admin-client-account-status-row">
                      <span class="badge badge-active" id="adminClientStatusBadge">Aktif</span>
                      <button type="button" class="btn btn-secondary" id="adminClientToggleStatusBtn" onclick="toggleAdminClientAccountStatus()">Nonaktifkan akun</button>
                    </div>
                    <div class="admin-client-account-danger">
                      <p class="admin-app-label-hint" style="margin:0 0 10px;">Menghapus client akan menghapus user, lisensi, dan data terkait.</p>
                      <button type="button" class="btn btn-secondary admin-client-delete-btn" onclick="deleteAdminClientFromModal()">Hapus client</button>
                    </div>
                  </div>
                </div>
              </section>

              <section class="admin-app-panel" data-panel="applications">
                <div class="admin-app-panel-head">
                  <h4>Aplikasi & Lisensi</h4>
                  <p>Pilih aplikasi dan paket lisensi per produk</p>
                </div>
                <div class="admin-app-panel-body">
                  <div class="form-group">
                    <label class="admin-app-label">Aplikasi yang dibeli</label>
                    <div id="adminClientApplications" class="admin-app-checklist admin-app-checklist-light">Memuat daftar aplikasi…</div>
                    <p class="admin-app-label-hint" style="margin-top:8px;">Ganti paket di sini (tanpa pembayaran client). Masa berlaku dihitung ulang saat paket berubah. Perpanjang/revoke lisensi lewat menu Lisensi (Renew/Revoke).</p>
                  </div>
                </div>
              </section>

              <section class="admin-app-panel" data-panel="affiliate">
                <div class="admin-app-panel-head">
                  <h4>Program Affiliate</h4>
                  <p>Override admin — client biasanya mengaktifkan sendiri di portal</p>
                </div>
                <div class="admin-app-panel-body">
                  <label class="admin-client-email-verify" for="adminClientAffiliateEnabled" style="margin-bottom:16px;">
                    <input type="checkbox" id="adminClientAffiliateEnabled">
                    <span>Paksa aktif/nonaktif (override)</span>
                  </label>
                  <div class="admin-app-form-grid">
                    <div class="form-group">
                      <label class="admin-app-label" for="adminClientAffiliateCode">Kode referral</label>
                      <input class="admin-app-input" id="adminClientAffiliateCode" type="text" placeholder="Auto (AA0001)" maxlength="32">
                      <p class="admin-app-label-hint" style="margin-top:4px;">Kosongkan untuk generate otomatis saat pertama kali diaktifkan.</p>
                    </div>
                    <div class="form-group">
                      <label class="admin-app-label" for="adminClientAffiliateStatus">Status affiliate</label>
                      <select class="admin-app-input" id="adminClientAffiliateStatus">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="suspended">Suspended</option>
                      </select>
                    </div>
                  </div>
                  <p class="admin-app-label-hint" id="adminClientAffiliateLinkHint" hidden style="margin-top:8px;"></p>
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
