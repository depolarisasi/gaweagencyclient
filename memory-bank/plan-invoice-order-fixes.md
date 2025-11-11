# Rencana Perbaikan: Invoice Detail, PDF, Billing Cycle Add-ons, dan Cancel Add-ons

Status: Draft untuk review Big Pappa  
Tujuan: Menyamakan tampilan invoice dan PDF dengan nominal yang dibayar, memperbaiki siklus add-ons, dan menuntaskan error pembatalan add-ons.

## Ringkasan Temuan

1) Invoice detail (client area) tidak mengitemisasi template, domain, dan add-ons secara konsisten:
- View `client/invoices/show.blade.php` hanya menampilkan subscription + addons dari `order_details['addons']`, domain tidak ditampilkan, dan menampilkan “Biaya Admin (Merchant)” (yang tidak termasuk dalam perhitungan total).
- Sumber kebenaran untuk item seharusnya `invoice.items` (tabel `invoice_items`) jika tersedia, fallback ke data order untuk invoice awal.

2) Billing cycle add-ons tampil tahunan (“Per Tahun”), padahal add-ons hanya bulanan:
- `CheckoutController` menyetel `OrderAddon.billing_cycle` mengikuti `subscriptionPlan.billing_cycle` (baris 720).
- `OrderAddon::getBillingCycleLabelAttribute` memetakan nilai enum (termasuk `annually`) ke label “Per Tahun”.

3) Error saat cancel add-ons: kolom `cancel_at_period_end` tidak ditemukan:
- Terdapat migration `2025_11_10_110001_update_order_addons_for_renewal_fields.php` menambahkan kolom ini, namun belum dijalankan (error 42S22).

4) Invoice PDF minimal dan tidak profesional:
- `pdf/invoice.blade.php` hanya menampilkan satu baris layanan, pajak, dan total.
- Tidak menampilkan add-ons, domain, atau info template. Layout kurang rapi.
- `InvoicePdfService` hanya load `user`, `order`, `order.product` (belum load `items`, `order.template`, `order.orderAddons.productAddon`).

## Tujuan dan Prinsip
- KISS: Perubahan minimal, fokus pada sumber data yang sudah ada (`Invoice.items` + fallback `order_details`).
- Konsistensi: UI dan PDF menampilkan item yang sama; total sesuai `invoice.total_amount`.
- Transparansi: Hilangkan “Biaya Admin (Merchant)”. Jika perlu, tampilkan `fee_customer` sebagai informasi, bukan komponen perhitungan.
- Siklus Add-ons: Pastikan hanya bulanan dan due date bulanan.

## Cakupan Perubahan

- UI Invoice Detail
  - Sumber item: gunakan `invoice.items` (jika ada), fallback ke `order_details` (template, domain, addons).
  - Tampilkan “Template: [nama]” pada baris subscription.
  - Tampilkan Domain (jika ada) sebagai item terpisah.
  - Hilangkan “Biaya Admin (Merchant)”.
  - Subtotal = `invoice.amount`, Pajak = `invoice.tax_amount`, Total = `invoice.total_amount`.

- Billing Cycle Add-ons
  - Saat membuat `OrderAddon` di `CheckoutController`, set `billing_cycle` = `'monthly'` untuk add-on recurring.
  - Perhitungan `next_due_date` add-ons: selalu +1 bulan.
  - Label: jika `billing_cycle` kosong => “Sekali”; jika ada => “Per Bulan”.

- Cancel Add-ons
  - Jalankan migrasi yang menambahkan kolom `cancel_at_period_end`.
  - Validasi flow: recurring add-ons akan diset `cancel_at_period_end=true`; one-time dibatalkan segera.

- PDF Invoice
  - Tata letak profesional sesuai contoh Big Pappa:
    - Header perusahaan (logo/nama, alamat, kontak, website).
    - Status invoice.
    - Bill To dan rincian invoice (nomor, terbit, jatuh tempo).
    - Tabel item: subscription (dengan template), domain (jika ada), add-ons (jika ada).
    - Subtotal, Pajak (PPN 11%), Diskon (opsional, default Rp 0), Total Tagihan.
    - Terbilang (opsional, jika diset).
    - Informasi pembayaran dan catatan.
  - Sumber item: `invoice.items` jika ada, fallback ke `order_details`.

## File yang Disentuh
- `resources/views/client/invoices/show.blade.php`
- `app/Http/Controllers/Client/DashboardController.php` (load relasi `items`, template, addons untuk view)
- `app/Http/Controllers/CheckoutController.php` (set `billing_cycle` add-ons jadi `'monthly'`; `calculateAddonNextDueDate` +1 bulan)
- `app/Models/OrderAddon.php` (update label “Sekali” dan “Per Bulan”)
- `app/Services/InvoicePdfService.php` (load relasi tambahan)
- `resources/views/pdf/invoice.blade.php` (layout baru profesional)
- (Opsional) `app/Helpers/NumberToBahasa.php` atau fungsi helper `terbilang()` sederhana

## Detail Implementasi (Snippet)

- Fix penetapan billing cycle add-ons dan next_due_date

```php:c%3A%5Cxampp%5Chtdocs%5Cgaweagencyclient%5Capp%5CHttp%5CControllers%5CCheckoutController.php
private function calculateAddonNextDueDate(?\App\Models\SubscriptionPlan $plan, $baseDate)
{
    // ... existing code ...
    // Ubah: add-ons selalu bulanan
    return \Carbon\Carbon::parse($baseDate)->copy()->addMonth()->toDateString();
    // ... existing code ...
}

// Di blok create OrderAddon
OrderAddon::create([
    // ... existing code ...
    'billing_cycle' => 'monthly',
    // ... existing code ...
    'next_due_date' => $this->calculateAddonNextDueDate(null, now()),
    // ... existing code ...
]);
```

- Perbaikan label billing cycle add-ons

```php:c%3A%5Cxampp%5Chtdocs%5Cgaweagencyclient%5Capp%5CModels%5COrderAddon.php
public function getBillingCycleLabelAttribute(): string
{
    // ... existing code ...
    if (!$this->billing_cycle) {
        return 'Sekali';
    }
    // Add-ons recurring disederhanakan ke bulanan
    return 'Per Bulan';
    // ... existing code ...
}
```

- Load relasi untuk invoice detail (client area)

```php:c%3A%5Cxampp%5Chtdocs%5Cgaweagencyclient%5Capp%5CHttp%5CControllers%5CClient%5CDashboardController.php
public function showInvoice(Invoice $invoice)
{
    // ... existing code ...
    $invoice->loadMissing(['items', 'order.template', 'order.orderAddons.productAddon']);
    return view('client.invoices.show', compact('invoice'));
}
```

- Hilangkan “Biaya Admin (Merchant)” dari UI invoice detail dan gunakan itemisasi yang konsisten (menggunakan `invoice.items`)

```blade:c%3A%5Cxampp%5Chtdocs%5Cgaweagencyclient%5Cresources%5Cviews%5Cclient%5Cinvoices%5Cshow.blade.php
<tbody>
    {{-- Prefer invoice->items jika tersedia --}}
    @if($invoice->items && $invoice->items->count() > 0)
        @foreach($invoice->items as $item)
            <tr>
                <td>
                    <div class="font-medium">{{ $item->description }}</div>
                </td>
                <td>
                    {{ $item->billing_cycle ? ucfirst($item->billing_cycle) : '-' }}
                </td>
                <td class="text-right font-medium">
                    Rp {{ number_format($item->amount, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
    @else
        {{-- Fallback ke order subscription + template --}}
        @if($invoice->order)
            <tr>
                <td>
                    <div class="font-medium">
                        {{ $invoice->order->product->name ?? 'Service' }}
                        @if($invoice->order->template)
                            — Template: {{ $invoice->order->template->name }}
                        @endif
                    </div>
                </td>
                <td>
                    @if($invoice->billing_period_start && $invoice->billing_period_end)
                        {{ $invoice->billing_period_start->format('d M Y') }} - {{ $invoice->billing_period_end->format('d M Y') }}
                    @else
                        {{ ucfirst($invoice->order->billing_cycle) }}
                    @endif
                </td>
                <td class="text-right font-medium">
                    Rp {{ number_format($invoice->order->subscription_amount ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            @if(($invoice->order->domain_amount ?? 0) > 0)
                <tr>
                    <td><div class="font-medium">Domain Registration</div></td>
                    <td>-</td>
                    <td class="text-right font-medium">
                        Rp {{ number_format($invoice->order->domain_amount, 0, ',', '.') }}
                    </td>
                </tr>
            @endif
            @foreach($invoice->order->orderAddons as $addon)
                <tr>
                    <td><div class="font-medium">{{ $addon->productAddon->name ?? 'Add-on' }}</div></td>
                    <td>{{ $addon->billing_cycle_label }}</td>
                    <td class="text-right font-medium">
                        Rp {{ number_format($addon->total_price, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        @endif
    @endif
</tbody>

{{-- Bagian total: hilangkan Biaya Admin (Merchant) --}}
@if(false && $invoice->fee_merchant && $invoice->fee_merchant > 0)
    {{-- Dihapus sesuai instruksi Big Pappa --}}
@endif
```

- Update layanan PDF untuk load relasi items + template

```php:c%3A%5Cxampp%5Chtdocs%5Cgaweagencyclient%5Capp%5CServices%5CInvoicePdfService.php
public function generate(Invoice $invoice): ?string
{
    // ... existing code ...
    $invoice->loadMissing(['user', 'order', 'order.product', 'order.template', 'order.orderAddons.productAddon', 'items']);
    // ... existing code ...
}
```

- Template PDF baru (layout profesional sesuai contoh Big Pappa) — akan menggantikan isi `resources/views/pdf/invoice.blade.php`:
  - Header perusahaan, status invoice, Bill To, Rincian Invoice (No, terbit, jatuh tempo)
  - Tabel: Deskripsi, Qty, Harga Satuan, Jumlah
  - Subtotal, PPN 11%, Diskon (0), Total Tagihan
  - Terbilang, Informasi Pembayaran & Catatan

(Penerapan detail HTML akan dilakukan saat eksekusi setelah konfirmasi data header perusahaan dan preferensi tampilan diskon/terbilang.)

## Migrasi & Data
- Menjalankan migrasi untuk menambahkan `cancel_at_period_end` agar cancel add-ons tidak error:
  - `php artisan migrate`
- (Opsional, jika ada data add-ons yang sudah telanjur tahunan): normalisasi data
  - Set `billing_cycle='monthly'` pada baris `order_addons` aktif yang tidak sesuai.
  - Rehitung `next_due_date` add-ons ke +1 bulan.

## Pengujian
- UI: Verifikasi invoice detail menampilkan subscription + template, domain (jika ada), add-ons, tanpa “Biaya Admin (Merchant)”.
- PDF: Unduh invoice PDF dan pastikan layout profesional, itemisasi sesuai, total sama dengan yang dibayar.
- Add-ons: Di order detail, label “Per Bulan”; tombol “Cancel at end of term” tidak error; recurring menandai `cancel_at_period_end=true`.
- Recurring addons invoice: Pastikan command `invoices:generate-recurring-addons` menghasilkan `invoice.items` per add-on.

## Risiko & Mitigasi
- Risiko: Itemisasi invoice awal belum menyimpan `invoice.items`. Mitigasi: fallback `order` + `orderAddons`.
- Risiko: Perubahan label add-ons berdampak ke tampilan lain. Mitigasi: perubahan label dibuat eksplisit “Per Bulan”.
- Risiko: Data lama add-ons tahunan. Mitigasi: jalankan skrip normalisasi (opsional) dan catat di progress.

## Estimasi
- Fase 1 (Migrasi & normalisasi): 0.5 hari
- Fase 2 (Invoice detail UI): 0.5 hari
- Fase 3 (Billing cycle add-ons): 0.5 hari
- Fase 4 (PDF): 1 hari
- Fase 5 (Verifikasi & regression): 0.5 hari

## Next Steps
1) Konfirmasi jawaban atas pertanyaan klarifikasi di atas.  
2) Setelah disetujui, eksekusi fase per fase dan update memory bank (`activeContext.md`, `progress.md`) sesuai perubahan.