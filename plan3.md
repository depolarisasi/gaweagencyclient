# Investigasi & Rencana Perbaikan — Ticket Attachments (Untuk Big Pappa)

Temuan cepat:
- Model `TicketReply` mendukung `attachments` (cast array) dan `SupportTicket::addReply()` menerima parameter lampiran.
- Client: `client/tickets/show.blade.php` sudah menampilkan lampiran per reply menggunakan `Storage::url(...)` jika `attachments` ada.
- Admin: `admin/tickets/show.blade.php` belum menampilkan lampiran pada thread dan form balasan admin belum memiliki input file; controller `Admin\SupportTicketController@reply` juga belum memproses lampiran.
- Upload client saat create/reply menyimpan ke `public` disk dengan path `ticket_attachments/{ticket_id}` — lampiran akan terlihat jika symbolic link `storage:link` tersedia dan permission benar.

Rekomendasi ringkas:
- Tambahkan input file di form balasan admin dan prosesnya di controller.
- Tampilkan lampiran di thread admin, mirror pola tampilan di client.
- Verifikasi end-to-end termasuk ketersediaan `storage:link` dan izin folder di Windows.

- [X] Investigasi & Validasi Alur Lampiran (Client)
  --> [X] Verifikasi `TicketReply.attachments` cast dan penyimpanan array metadata (path, name, mime, size)
  --> [X] Telaah `Client\\SupportTicketController@store` dan `reply` untuk upload ke `public/ticket_attachments/{ticket_id}`
  --> [X] Konfirmasi `client/tickets/show.blade.php` menampilkan lampiran dengan `Storage::url` dan filter `is_internal=false`
  --> [X] Identifikasi hambatan lingkungan: cek `php artisan storage:link` dan permission `storage/app/public` di Windows

- [X] Perbaikan UI/UX Admin (Reply & Thread)
  --> [X] Tambahkan input `attachments[]` di form reply admin `admin/tickets/show.blade.php` (multiple, accept: .pdf,.png,.jpg,.jpeg,.gif, set `enctype="multipart/form-data"`)
  --> [X] Tampilkan lampiran per reply di thread admin (blok “Attachments” serupa client, pakai `Storage::url($file['path'])`)
  --> [X] Pertahankan larangan upload via Trix (event `trix-file-accept` diblok)
  --> [X] Preview UI untuk memastikan input dan daftar lampiran tampil benar

- [X] Perbaikan Handler Balasan Admin
  --> [X] Update `Admin\\SupportTicketController@reply`: validasi `attachments` array dan `attachments.*` (`mimes:pdf,png,jpg,jpeg,gif|max:10240`)
  --> [X] Simpan file ke `ticket_attachments/{ticket_id}` pada disk `public` dan kumpulkan metadata `$uploaded`
  --> [X] Panggil `$ticket->addReply($sanitizedMessage, auth()->id(), $isInternal, !empty($uploaded)?$uploaded:null)`
  --> [X] Pastikan notifikasi `SupportTicketRepliedNotification` tetap dikirim untuk balasan non-internal

- [ ] Verifikasi End-to-End
  --> [ ] Buat tiket dari client dengan lampiran; pastikan lampiran muncul di thread client & admin
  --> [ ] Balas dari admin dengan lampiran; pastikan lampiran muncul di kedua sisi
  --> [ ] Uji link unduhan: jika 404, lakukan `php artisan storage:link` dan cek permission

- [X] Keamanan & Sanitasi
  --> [X] Pastikan sanitasi HTML message whitelist tag aman pada kedua controller
  --> [X] Validasi tipe/mime ukuran file; jangan pakai input path dari user untuk akses langsung

- [X] Risiko & Mitigasi
  --> [X] Windows/XAMPP permission & symbolic link: dokumentasikan langkah `storage:link` alternatif (junction) bila symlink gagal
  --> [X] Monitoring error: tambah logging pada kegagalan upload/akses `Storage::url`

Catatan Windows (symlink alternatif junction):
- Jika `php artisan storage:link` gagal pada Windows tanpa hak admin, hapus `public/storage` lalu buat junction: jalankan PowerShell sebagai Administrator, kemudian:
  - `cd c:\xampp\htdocs\gaweagencyclient\public`
  - `rmdir storage` (jika ada)
  - `cmd /c mklink /J storage ..\storage\app\public`
Ini akan membuat junction sebagai pengganti symbolic link.