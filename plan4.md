- [x] Perbaikan Invoice (Client Area & PDF)
--> [x] Audit sumber item invoice: prioritaskan `invoice->items` dengan fallback `order` (template, domain, add-ons)
--> [x] Tampilkan template yang dipakai pada baris subscription (client view + PDF)
--> [x] Tambahkan item Domain (jika `order->domain_amount > 0`) di client view + PDF
--> [x] Tambahkan item Add-ons dari `order->orderAddons` saat `invoice->items` kosong (nama, qty, harga)
--> [x] Hilangkan baris “Biaya Admin (Merchant/Tripay/Customer)” dari tampilan total invoice klien
--> [x] Konsolidasikan subtotal/tax/total: gunakan `invoice->amount`, `invoice->tax_amount`, `invoice->total_amount` saja

- [ ] Siklus Billing Add-ons di Orders Detail
--> [x] Ganti tampilan label add-on di admin orders detail ke `$addon->billing_cycle_label` (“Per Bulan”/“Sekali”)
--> [ ] Normalisasi set `billing_cycle` add-on saat checkout menjadi `'monthly'` untuk recurring add-ons
--> [ ] Pastikan `next_due_date` add-ons dihitung +1 bulan (sederhana & konsisten)

- [ ] Validasi & Pengujian
--> [ ] Verifikasi client invoice detail: baris subscription (dengan template), domain, add-ons muncul saat relevan
--> [ ] Konfirmasi admin orders detail menampilkan “Per Bulan” untuk add-ons dan “Per Tahun” hanya untuk subscription
--> [ ] Uji PDF invoice: itemisasi sinkron dengan client view, tanpa baris biaya admin merchant
--> [ ] Jalankan unit/feature test terkait checkout/invoice; sesuaikan ekspektasi bila perlu