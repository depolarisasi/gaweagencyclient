# Active Context

Fokus Saat Ini:
- Gabungkan UI Add-ons ke halaman `configure` dalam satu form.
- `POST /checkout/configure` menyimpan paket, billing cycle, dan add-ons; redirect ke `summary`.
- Auto-isi `customer_info` ketika user login saat migrasi cart.
 - Konsolidasikan penamaan: halaman Template menggunakan `checkout.template` (rename dari `step1`).
 - Prefill pilihan Template dari `cart`/`cookie`/`session` saat kembali ke langkah ini.

Perubahan Terbaru:
- Controller `CheckoutController@configure` meng-handle GET+POST dengan integrasi add-ons.
- View `checkout/configure.blade.php` menampilkan paket + add-ons, tombol "Lanjut ke Ringkasan".
- `CartService::syncAddons` digunakan untuk sinkronisasi add-ons dari POST.
 - Controller `CheckoutController@template` kini me-render `checkout.template` dan mengirim `selectedTemplateId` dari `cart/cookie/session/query`.
 - View `checkout/template.blade.php` menambahkan tombol Back ke langkah Domain dan autoselect kartu Template.
 - Link Back di Configure diarahkan ke `route('checkout.template')` (sebelumnya `checkout.step1`).
 - Memperbaiki ParseError di `checkout/billing.blade.php` dengan mengganti `@php(...)` menjadi blok `@php ... @endphp`.
 - Menonaktifkan fallback pengambilan keranjang tamu lain di `CartService::getOrCreateCart` agar guest baru selalu mulai dari `/checkout/` (Domain), mencegah redirect ke `/checkout/personal-info` saat Cart diklik.

Next Steps:
- Audit referensi lama ke halaman `addon` dan sesuaikan tests yang bergantung pada flow lama.
- Tambahkan e2e variasi untuk domain baru vs existing (opsional).

Pertimbangan Aktif:
- Jaga KISS: form sederhana, tanpa micro-interactions kompleks yang tidak perlu.
- Pastikan preselect add-ons dari cart berjalan agar UX konsisten.