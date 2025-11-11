# Investigasi & Rencana Aksi — Admin Order Pricing, Admin Invoice Detail, Cancel Add-ons, Info Orders Client, Aktivasi Project
 
Tujuan: Membetulkan perhitungan/label pricing di admin order, menyamakan layout halaman invoice detail admin dan menambah aksi PDF/Email, memperbaiki error cancel add-ons, memperkaya informasi order di halaman client, serta memastikan pembuatan project otomatis saat order diaktifkan/dibayar. 

# Investigasi & Rencana Aksi — Support Ticket Summary & Emails (Draft untuk Big Pappa)

- [ ] Sanitasi ringkasan tiket di halaman `/client/tickets`
--> [x] Ganti render ringkasan jadi plain text: `strip_tags($ticket->description)` + `Str::limit(...)`
--> [ ] Tambahkan unit test untuk input `description` yang mengandung tag HTML (pastikan ringkasan tanpa tag)
--> [x] Pastikan halaman detail tetap merender HTML aman dengan `{!! $ticket->description !!}`

- [x] Email notifikasi pada siklus tiket
--> [x] Buat `SupportTicketCreatedNotification` (markdown email): kirim ke client saat tiket dibuat (oleh client maupun admin)
--> [x] Integrasi: panggil notifikasi di `Client\SupportTicketController@store` dan `Admin\SupportTicketController@store`
--> [x] Buat `SupportTicketRepliedNotification`: kirim ke client saat admin/staff membalas tiket
--> [x] Integrasi: panggil di `Admin\SupportTicketController@reply` untuk balasan non-internal
--> [x] Buat `SupportTicketStatusUpdatedNotification`: kirim ke client saat status berubah (`in_progress`, `resolved`, `closed`)
--> [x] Integrasi: panggil di `Admin\SupportTicketController@markInProgress`, `resolve`, dan `close`
--> [ ] (Opsional) Tambah preview dev di `/debug/email/...` untuk ketiga template

- [ ] Verifikasi & pengujian email
--> [ ] Pastikan konfigurasi mailer `.env` (driver `smtp`/`log`) dan `config/mail.php` benar
--> [x] Tambahkan try/catch saat kirim notifikasi agar UI tidak gagal jika SMTP error, serta logging penerima dan `ticket_number`
--> [x] Tambahkan test dengan `Notification::fake()` untuk jalur: create, reply (admin → client), dan status update
--> [ ] UAT manual: buat tiket, balas sebagai admin/staff, ubah status—verifikasi email terkirim

- [ ] Dokumentasi & Memory Bank
--> [x] Update `activeContext.md` dengan keputusan sanitasi ringkasan dan alur notifikasi tiket
--> [x] Update `progress.md` setelah tiap fase selesai dan dicapai
--> [ ] (Opsional) Tambah `memory-bank/emails-support-ticket.md` berisi spesifikasi konten email (subject, CTA, payload)
 
Catatan:
- KISS: gunakan `Notification` berbasis markdown email (selaras dengan fitur invoice/payment yang sudah ada).
- Tidak mengubah sanitasi konten penuh tiket di halaman detail (tetap memakai HTML aman).
- Setelah approve, saya eksekusi rencana fase demi fase dan memperbarui Memory Bank (`activeContext.md` dan `progress.md`) setelah tiap fase selesai.