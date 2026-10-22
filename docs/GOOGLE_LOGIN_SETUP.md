# Login dengan Google (Client Portal)

Panduan mengaktifkan **Masuk / Daftar dengan Google** di landing page & hub.

## Yang sudah disiapkan di codebase

- Paket **Laravel Socialite** (`laravel/socialite`)
- Route OAuth:
  - `GET /auth/google/redirect` — mulai login Google
  - `GET /auth/google/callback` — callback dari Google
- Kolom `users.google_id`, password boleh kosong untuk akun Google-only
- Tombol **Masuk dengan akun Google** di modal Masuk/Daftar (muncul jika env Google terisi)
- Setelah login Google, intent **Beli paket / Trial** dari landing tetap dilanjutkan (checkout/trial)

## Yang perlu Anda siapkan

### 1. Akun Google Cloud

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project (atau pilih project yang ada)
3. **APIs & Services → OAuth consent screen**
   - User type: **External** (kecuali organisasi Google Workspace internal)
   - Isi app name, support email, developer contact
   - Scopes: cukup default + `email`, `profile`, `openid` (Socialite memintanya)
   - Tambahkan **Test users** jika app masih *Testing* (hanya email tersebut yang bisa login)

4. **APIs & Services → Credentials → Create Credentials → OAuth client ID**
   - Application type: **Web application**
   - **Authorized JavaScript origins** (sesuaikan environment):
     - Dev: `http://127.0.0.1:8000`, `http://localhost:8000`
     - Production: `https://domain-anda.com`
   - **Authorized redirect URIs** (harus **exact match**):
     - Dev: `http://127.0.0.1:8000/auth/google/callback`
     - Production: `https://domain-anda.com/auth/google/callback`

5. Salin **Client ID** dan **Client secret**

### 2. Environment (`.env`)

```env
APP_URL=http://127.0.0.1:8000   # production: https://domain-anda.com

GOOGLE_CLIENT_ID=....apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-...
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
# Windows dev jika muncul cURL error 60 / SSL certificate:
GOOGLE_VERIFY_SSL=false
```

Setelah mengubah env:

```bash
php artisan config:clear
```

### 3. Database

Jalankan migration:

```bash
php artisan migrate
```

Migration: `2026_10_20_000002_add_google_oauth_to_users_table.php`  
(menambah `google_id`, `password` nullable)

### 4. Session & HTTPS (production)

- Login Google memakai **session cookie** Laravel (`SESSION_DRIVER=database` sudah cocok)
- Production: `APP_URL` harus HTTPS, `SESSION_SECURE_COOKIE=true` (disarankan)
- Pastikan domain production ada di Google **origins** dan **redirect URI**

### 5. Publish app (opsional)

Selama OAuth consent masih **Testing**, hanya test users yang bisa login.  
Untuk publik: submit consent screen untuk **Production** di Google Cloud.

## Perilaku akun

| Situasi | Perilaku |
|--------|----------|
| Email Google baru | Buat user `client` + profil `clients`, email langsung dianggap terverifikasi |
| Email sudah terdaftar (daftar manual) | Hubungkan `google_id` ke akun yang sama (email harus cocok) |
| Email dipakai akun admin | Ditolak — Google hanya untuk **client** |
| Akun client nonaktif | Ditolak |
| Login password tapi akun Google-only | Pesan: gunakan tombol Google |

## Cek cepat

1. Isi `GOOGLE_CLIENT_ID` & `GOOGLE_CLIENT_SECRET`, migrate, `config:clear`
2. Buka landing → Masuk → tombol Google harus tampil
3. Klik → consent Google → kembali ke landing, status login client aktif
4. Uji alur: **Beli paket** (guest) → login Google → modal checkout terbuka

## Troubleshooting

| Gejala | Kemungkinan |
|--------|-------------|
| Tombol Google tidak muncul | `GOOGLE_CLIENT_*` kosong atau config cache lama |
| `redirect_uri_mismatch` | `GOOGLE_REDIRECT_URI` / `APP_URL` tidak sama dengan URI di Google Console |
| `access_denied` / 403 consent | User belum ada di Test users (app masih Testing) |
| Login sukses tapi session hilang | `APP_URL` salah, cookie domain, atau campur http/https |

## SpotColor (desktop)

1. Set `SPOTCOLOR_LICENSE_SERVER_URL` di `.env` SpotColor (sama dengan `APP_URL` Laravel).
2. Restart aplikasi Electron (protocol `spotcolor://` didaftarkan saat start).
3. Login modal → **Masuk dengan akun Google** → browser → setelah sukses app menerima deep link dan token API.

Route Laravel: `GET /auth/google/desktop` (OAuth) → halaman sukses + `spotcolor://auth/callback?code=...`  
API: `POST /api/auth/google/exchange` (kode sekali pakai → Bearer token, sama seperti login password).

**Google Console:** tidak perlu URI baru untuk `spotcolor://` — redirect OAuth tetap `{APP_URL}/auth/google/callback`.

Installer production: build ulang dengan `electron-builder` agar protocol handler terdaftar di Windows.

## Tidak termasuk

- Login Google untuk **admin panel**
- Apple / Facebook OAuth
