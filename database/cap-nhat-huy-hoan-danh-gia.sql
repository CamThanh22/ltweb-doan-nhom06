-- Chạy một lần bằng tài khoản quản trị CSDL trên database JadeHub đang dùng.
-- Có thể chạy lại; không xóa đơn hàng, tài khoản, đánh giá hoặc ảnh.
SET NAMES utf8mb4;
ALTER TABLE san_pham ADD COLUMN IF NOT EXISTS nguoi_ban_id INT UNSIGNED NULL;
ALTER TABLE san_pham ADD INDEX IF NOT EXISTS idx_sp_nguoi_ban (nguoi_ban_id);
ALTER TABLE chi_tiet_don ADD COLUMN IF NOT EXISTS nguoi_ban_id INT UNSIGNED NULL;
ALTER TABLE chi_tiet_don ADD INDEX IF NOT EXISTS idx_ct_nguoi_ban (nguoi_ban_id);
ALTER TABLE don_hang ADD COLUMN IF NOT EXISTS ly_do_huy VARCHAR(500) NULL;
ALTER TABLE don_hang ADD COLUMN IF NOT EXISTS huy_boi INT UNSIGNED NULL;
ALTER TABLE don_hang ADD COLUMN IF NOT EXISTS huy_luc DATETIME NULL;
-- Tin đã duyệt xác định người bán thực tế; sản phẩm mẫu thuộc shop vận hành JadeHub.
UPDATE san_pham sp JOIN tin_dang_ngoc t ON t.san_pham_id=sp.id
SET sp.nguoi_ban_id=t.nguoi_ban_id WHERE sp.nguoi_ban_id IS NULL;
SET @shop_jadehub = (SELECT MIN(id) FROM nguoi_dung WHERE vai_tro='quan_tri');
INSERT IGNORE INTO ho_so_nguoi_ban(nguoi_dung_id,ten_gian_hang,gioi_thieu,trang_thai)
SELECT id,'JadeHub','Gian hàng do quản trị JadeHub vận hành.','da_duyet' FROM nguoi_dung WHERE id=@shop_jadehub;
UPDATE san_pham SET nguoi_ban_id=@shop_jadehub WHERE nguoi_ban_id IS NULL;
UPDATE chi_tiet_don ct JOIN san_pham sp ON sp.id=ct.san_pham_id
SET ct.nguoi_ban_id=sp.nguoi_ban_id WHERE ct.nguoi_ban_id IS NULL;

CREATE TABLE IF NOT EXISTS hoan_tien (
 don_hang_id INT UNSIGNED PRIMARY KEY,
 so_tien BIGINT UNSIGNED NOT NULL,
 trang_thai ENUM('can_doi_soat','cho_hoan','da_hoan','khong_phat_sinh') NOT NULL,
 tai_khoan_nhan VARCHAR(500) NOT NULL DEFAULT '',
 ma_giao_dich VARCHAR(100) NULL UNIQUE,
 ghi_chu VARCHAR(1000) NOT NULL DEFAULT '',
 xu_ly_boi INT UNSIGNED NULL,
 ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 ngay_hoan DATETIME NULL,
 FOREIGN KEY(don_hang_id) REFERENCES don_hang(id) ON DELETE CASCADE,
 FOREIGN KEY(xu_ly_boi) REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS danh_gia_giao_dich (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 don_hang_id INT UNSIGNED NOT NULL,
 san_pham_id INT UNSIGNED NOT NULL,
 nguoi_viet_id INT UNSIGNED NOT NULL,
 nguoi_nhan_id INT UNSIGNED NOT NULL,
 loai ENUM('san_pham','nguoi_mua') NOT NULL,
 sao TINYINT UNSIGNED NOT NULL CHECK(sao BETWEEN 1 AND 5),
 sao_doi_tac TINYINT UNSIGNED NOT NULL CHECK(sao_doi_tac BETWEEN 1 AND 5),
 noi_dung VARCHAR(2000) NOT NULL,
 phan_hoi VARCHAR(1000) NOT NULL DEFAULT '',
 ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 ngay_phan_hoi DATETIME NULL,
 UNIQUE KEY uq_danh_gia (don_hang_id,san_pham_id,nguoi_viet_id,loai),
 FOREIGN KEY(don_hang_id,san_pham_id) REFERENCES chi_tiet_don(don_hang_id,san_pham_id) ON DELETE CASCADE,
 FOREIGN KEY(nguoi_viet_id) REFERENCES nguoi_dung(id),
 FOREIGN KEY(nguoi_nhan_id) REFERENCES nguoi_dung(id),
 INDEX idx_dg_sp (san_pham_id,loai),
 INDEX idx_dg_nguoi (nguoi_nhan_id,loai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
