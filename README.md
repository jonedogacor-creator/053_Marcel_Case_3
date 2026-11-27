# KeranjangKu

KeranjangKu adalah aplikasi inventaris produk untuk mencatat barang, memantau persediaan, dan mengelola sampah produk. Setiap akun hanya dapat melihat dan mengubah produknya sendiri.

## Menjalankan aplikasi

Siapkan `.env` dan database, lalu jalankan dari folder proyek:

```powershell
composer install
npm.cmd install
php artisan migrate --seed
npm.cmd run dev
```

Di terminal kedua, jalankan:

```powershell
php artisan serve
```

Buka `http://127.0.0.1:8000`. Untuk penggunaan lokal tanpa Vite development server, jalankan `npm.cmd run build` lalu buka aplikasi seperti biasa.

> Migrasi tidak menghapus data lama. Jangan gunakan `php artisan migrate:fresh` pada database yang berisi data.

## Port forwarding dengan VS Code Dev Tunnels

Untuk tunnel HTTPS, Laravel harus menggunakan aset hasil build dari `public/build/manifest.json`, bukan alamat Vite lokal:

1. Hentikan Vite development server dengan `Ctrl+C`.
2. Jalankan `npm.cmd run build`.
3. Jalankan Laravel, lalu teruskan port aplikasi melalui tab **Ports** VS Code dan pilih visibilitas **Public** hanya jika memang dibutuhkan.
4. Buka URL HTTPS dari tunnel. Proxy loopback sudah dipercaya secara default. Jika URL aset masih dihasilkan sebagai HTTP, atur `APP_FORCE_HTTPS=true` di `.env`, lalu jalankan `php artisan config:clear`.

Setelah selesai menggunakan tunnel lokal, kembalikan `APP_FORCE_HTTPS=false`. Jika tunnel menggunakan alamat proxy lain, tambahkan alamat/CIDR proxy yang tepat pada daftar `trustProxies` di `bootstrap/app.php`. Jangan gunakan `*` pada aplikasi yang dapat diakses publik.

## Akun demo

- Email: `demo@example.com`
- Kata sandi: `password`

Akun beserta lima produk contoh dibuat oleh seeder. Ganti kata sandi demo sebelum aplikasi dipakai di luar lingkungan lokal.

## Fitur

- Login dan registrasi mandiri memakai Livewire 4, `Auth::attempt()`, `Hash::make()`, dan pembatasan percobaan masuk.
- CRUD produk dengan SKU, jumlah stok, harga, dan catatan.
- Soft-delete dengan tab stok, sampah, pemulihan, serta hapus permanen.
- Pencarian langsung, ringkasan statistik, dan cache produk/statistik yang dibersihkan ketika data berubah.
- UI responsif berbasis Tailwind CSS v4 dan komponen Flux UI 2.
- Notifikasi otomatis hilang setelah 3,5 detik dan menu info sistem berbasis Alpine.js.
- Produk dipisahkan per akun. Data mahasiswa lama tetap tersimpan di database dan tidak dihapus oleh perubahan ini.

## Pengujian

```powershell
php artisan test
vendor\bin\pint --test
npm.cmd run build
```
