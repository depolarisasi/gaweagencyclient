# Playwright Testing Guide

## Overview
Playwright telah dikonfigurasi untuk menjalankan end-to-end testing pada aplikasi Laravel ini. Konfigurasi telah diperbaiki untuk otomatis menjalankan Laravel development server sebelum menjalankan test.

## Instalasi
Playwright sudah terinstall sebagai dev dependency. Jika perlu install ulang:
```bash
npm install @playwright/test --save-dev
npx playwright install
```

## Menjalankan Test

### Semua Test
```bash
npm run test
# atau
npx playwright test
```

### Test Spesifik
```bash
npm run test -- e2e/example.spec.js
npm run test -- e2e/checkout-flow.spec.js
```

### Test dengan Browser Spesifik
```bash
npm run test -- --project=chromium
npm run test -- --project=firefox
npm run test -- --project=webkit
```

### Test dengan UI Mode
```bash
npm run test:ui
```

### Melihat Report
```bash
npm run test:report
```

## Konfigurasi

### Automatic Server Startup
Playwright dikonfigurasi untuk otomatis menjalankan `php artisan serve` sebelum test dimulai. Server akan berjalan di `http://localhost:8000`.

### Browser Support
- Chromium (Chrome)
- Firefox
- WebKit (Safari)

### Test Directory
Test files berada di folder `e2e/`

## File Test yang Tersedia

1. **example.spec.js** - Test dasar untuk memverifikasi Playwright berfungsi
2. **checkout-flow.spec.js** - Test lengkap untuk flow checkout aplikasi

## Tips Debugging

1. **Jika test timeout**: Pastikan Laravel server berjalan dengan baik
2. **Jika test gagal**: Gunakan `--debug` flag untuk debugging
3. **Untuk melihat browser**: Gunakan `--headed` flag
4. **Untuk slow motion**: Gunakan `--slow-mo=1000`

## Contoh Command Debugging
```bash
npx playwright test --debug
npx playwright test --headed
npx playwright test --slow-mo=1000
```

## Troubleshooting

### Error "Connection refused"
- Pastikan tidak ada aplikasi lain yang menggunakan port 8000
- Cek apakah PHP dan Laravel terinstall dengan benar

### Test timeout
- Increase timeout di playwright.config.js
- Pastikan aplikasi Laravel load dengan cepat

### Browser tidak terinstall
```bash
npx playwright install
```