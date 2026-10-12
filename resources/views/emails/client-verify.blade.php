<x-mail::message>
# Konfirmasi Email Anda

Halo {{ $name }},

Terima kasih telah mendaftar di **{{ config('softsale.name', 'Quick Spot Color') }}**. Klik tombol di bawah untuk memverifikasi alamat email dan mengaktifkan akun client Anda.

<x-mail::button :url="$url">
Verifikasi Email
</x-mail::button>

Link ini berlaku selama **{{ $expireMinutes }} menit**. Jika tombol tidak berfungsi, salin dan tempel URL berikut ke browser:

{{ $url }}

Jika Anda tidak mendaftar, abaikan email ini.

Salam,<br>
{{ config('softsale.name', 'Quick Spot Color') }}
</x-mail::message>
