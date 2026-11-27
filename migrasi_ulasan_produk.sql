-- Jalankan file ini sekali melalui phpMyAdmin pada database db_desert.
CREATE TABLE IF NOT EXISTS tb_ulasan_produk (
  id_ulasan INT AUTO_INCREMENT PRIMARY KEY,
  id_produk INT NOT NULL,
  id_customer INT NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  komentar TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unik_ulasan_member_produk (id_produk, id_customer),
  KEY idx_ulasan_produk (id_produk),
  CONSTRAINT fk_ulasan_produk FOREIGN KEY (id_produk) REFERENCES tb_produk(id_produk) ON DELETE CASCADE,
  CONSTRAINT fk_ulasan_customer FOREIGN KEY (id_customer) REFERENCES tb_customer(id_customer) ON DELETE CASCADE,
  CONSTRAINT chk_ulasan_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
