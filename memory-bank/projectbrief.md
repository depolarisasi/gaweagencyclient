# Project Brief: GaweClient

## Overview
GaweClient adalah platform Website as a Service (WaaS) untuk Gawe Agency yang berfungsi sebagai portal klien terpusat untuk mengotomatisasi dan menyederhanakan seluruh siklus hidup klien.

## Core Requirements
Berdasarkan PRD yang ada, sistem ini harus memiliki:

### 1. Manajemen Penagihan (Billing)
- CRUD Produk dengan siklus penagihan (Bulanan, Triwulanan, Tahunan, dll)
- CRUD Add-on dengan tipe penagihan (Sekali Bayar/Berulang)
- Invoice otomatis dan manual
- Notifikasi email untuk invoice dan pengingat pembayaran

### 2. Manajemen Proyek
- Auto-create proyek dari pesanan yang lunas
- Status proyek: Pending, Aktif, Suspended, Dibatalkan
- Detail akses website untuk klien
- Catatan internal untuk admin/staff

### 3. Sistem Tiket Dukungan
- Klien dapat membuat tiket
- Prioritas dan departemen
- Sistem balasan untuk klien, staff, dan admin
- Status: Open, In Progress, Closed

### 4. Manajemen Pengguna
- 3 role: user (klien), staff, admin
- Registrasi klien saat pemesanan
- CRUD pengguna oleh admin

### 5. Manajemen Template
- Template website dengan demo dan thumbnail
- Template email sistem

### 6. Integrasi Pembayaran
- Tripay integration
- Konfigurasi pajak
- Log transaksi

## Technology Stack
- Laravel (Backend Framework)

- Tailwind CSS + DaisyUI (Styling)
- MySQL (Database)

## User Roles
1. **Client**: Memesan layanan, melihat proyek, membayar tagihan, buat tiket
2. **Staff**: Mengelola proyek yang ditugaskan, balas tiket support
3. **Admin**: Full access, kelola produk, pengguna, konfigurasi sistem

## Current Status
Proyek sudah memiliki struktur dasar Laravel dengan beberapa views dan models, namun masih perlu perbaikan dan kelengkapan sesuai PRD.