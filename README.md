# Web Perpustakaan

Prototipe antarmuka perpustakaan digital yang terdiri dari portal pengguna (`/user`) dan panel administrator (`/admin`). Seluruh halaman statis menggunakan HTML & CSS sehingga mudah diintegrasikan ke backend pilihan Anda (PHP/Laravel, Node, dsb).

## Struktur Halaman

- `user/index.html` – Beranda: hero, pencarian, daftar buku terbaru, kategori.
- `user/login.html` – Form login & registrasi dalam satu layar.
- `user/dashboard.html` – Ringkasan pinjaman aktif, jatuh tempo, riwayat.
- `user/books.php` – Daftar buku lengkap dengan filter kategori & status.
- `user/book-detail.html` – Detail buku (cover, metadata, stok, CTA pinjam).
- `user/my-loans.php` – Daftar peminjaman aktif & riwayat dengan tombol pengembalian.
- `admin/dashboard.html` – KPI admin: total buku, anggota, peminjaman aktif, tabel pinjaman berjalan.
- `admin/books.php` – Tabel kelola buku + akses ke `admin/add-book.html` untuk form tambah baru.
- `admin/members.html` – Manajemen anggota (edit/hapus).
- `admin/loans.php` – Persetujuan peminjaman (setujui/tolak).
- `admin/returns.html` – Pengelolaan pengembalian termasuk perhitungan denda.
- `admin/reports.html` – Ringkasan laporan bulanan, buku terpopuler, anggota aktif, tombol cetak PDF.

Setiap folder memiliki file CSS sendiri (`user/css/style.css` dan `admin/css/admin.css`) agar gaya mudah dikembangkan.

## Struktur Database (Ringkas)

### Tabel `users`

| Field   | Tipe             | Keterangan                  |
| ------- | ---------------- | --------------------------- |
| id      | INT (PK, AI)     | ID unik                     |
| nama    | VARCHAR(100)     | Nama lengkap                |
| username| VARCHAR(60)      | Username login (unik)       |
| password| VARCHAR(255)     | Password terenkripsi        |
| role    | ENUM('admin','user') | Hak akses aplikasi   |

### Tabel `books`

| Field   | Tipe         | Keterangan                |
| ------- | ------------ | ------------------------- |
| id      | INT (PK, AI) | ID unik buku              |
| judul   | VARCHAR(150) | Judul buku                |
| penulis | VARCHAR(120) | Nama penulis              |
| kategori| VARCHAR(80)  | Kategori utama            |
| tahun   | INT          | Tahun terbit              |
| stok    | INT          | Jumlah eksemplar tersedia |
| cover   | VARCHAR(255) | Path/URL cover buku       |

> Tabel tambahan (mis. `loans`, `loan_items`, `returns`) bisa ditambahkan sesuai kebutuhan backend untuk menyimpan transaksi peminjaman dan pengembalian.

## Pengembangan Lanjut

- Hubungkan tiap form ke backend (PHP/MySQL) menggunakan routing framework favorit Anda.
- Tambahkan validasi sisi server & client.
- Implementasikan autentikasi sesungguhnya, termasuk middleware pembeda admin-user.
- Gunakan tabel relasional tambahan untuk peminjaman, pengembalian, serta laporan agregasi.

Selamat membangun sistem perpustakaan digital Anda! Silakan kembangkan gaya visual maupun komponennya sesuai kebutuhan instansi.*** End Patch}*** End Patch

