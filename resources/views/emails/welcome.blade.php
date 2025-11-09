@component('mail::message')
# Selamat Datang, {{ $user->name }}

Terima kasih telah mendaftar di Gawe Agency. Akun Anda sudah aktif dan siap digunakan.

@component('mail::button', ['url' => url('/client/')])
Masuk ke Dashboard
@endcomponent

Jika Anda membutuhkan bantuan, balas email ini atau buat tiket dukungan dari dashboard.

Terima kasih,
Tim Gawe Agency
@endcomponent