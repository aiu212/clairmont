-- MIGRASI ROLE CLAIRMONT MENJADI 2 ROLE LOGIN: sales dan customer
-- Jalankan hanya setelah backup database db_desert selesai.
-- Migrasi ini mempertahankan semua akun dan data transaksi.

-- Tahap 1: izinkan nilai role baru sebelum mengonversi akun lama.
ALTER TABLE tb_users
MODIFY role ENUM('admin','owner','sales','customer') NOT NULL;

-- Tahap 2: akun Admin dan Owner lama dialihkan menjadi akun Sales.
UPDATE tb_users
SET role = 'sales'
WHERE role IN ('admin','owner');

-- Tahap 3: batasi role yang digunakan sistem menjadi dua saja.
ALTER TABLE tb_users
MODIFY role ENUM('sales','customer') NOT NULL;

-- Pemeriksaan hasil migrasi.
SELECT id_user, nama, email, role, status
FROM tb_users
ORDER BY id_user;
