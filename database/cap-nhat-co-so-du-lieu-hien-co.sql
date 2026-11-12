-- Chọn cơ sở dữ liệu JadeHub đang chạy trước khi nhập.
SET NAMES utf8mb4;


-- Bài 7: khóa đăng nhập sai theo email, không lưu mật khẩu.
CREATE TABLE IF NOT EXISTS dang_nhap_sai (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 dinh_danh CHAR(64) NOT NULL,
 thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_dang_nhap_sai (dinh_danh, thoi_gian)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Thanh toán mô phỏng: KHÔNG lưu số thẻ, KHÔNG tự xác nhận chuyển khoản.
CREATE TABLE IF NOT EXISTS thanh_toan (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 don_hang_id INT UNSIGNED NOT NULL UNIQUE,
 phuong_thuc ENUM('cod','chuyen_khoan') NOT NULL,
 trang_thai ENUM('chua_thanh_toan','cho_doi_soat','da_xac_nhan') NOT NULL DEFAULT 'chua_thanh_toan',
 ma_tham_chieu VARCHAR(100) NULL,
 ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_thanh_toan_don FOREIGN KEY (don_hang_id) REFERENCES don_hang(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ho_so_nguoi_ban (
 nguoi_dung_id INT UNSIGNED PRIMARY KEY,
 ten_gian_hang VARCHAR(120) NOT NULL,
 gioi_thieu VARCHAR(1000) NOT NULL DEFAULT '',
 trang_thai ENUM('cho_duyet','da_duyet','tu_choi') NOT NULL DEFAULT 'cho_duyet',
 ngay_dang_ky DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS tin_dang_ngoc (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nguoi_ban_id INT UNSIGNED NOT NULL,
 danh_muc_id INT UNSIGNED NOT NULL,
 ten VARCHAR(200) NOT NULL,
 mo_ta TEXT NOT NULL,
 gia INT UNSIGNED NOT NULL,
 ton_kho INT UNSIGNED NOT NULL,
 xuat_xu VARCHAR(100) NOT NULL,
 anh VARCHAR(255) NOT NULL,
 anh_chung_thu VARCHAR(255) NOT NULL,
 don_vi_kiem_dinh VARCHAR(150) NOT NULL,
 so_chung_thu VARCHAR(100) NOT NULL,
 trang_thai ENUM('cho_duyet','da_duyet','tu_choi') NOT NULL DEFAULT 'cho_duyet',
 san_pham_id INT UNSIGNED UNIQUE NULL,
 ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(nguoi_ban_id) REFERENCES nguoi_dung(id),
 FOREIGN KEY(danh_muc_id) REFERENCES danh_muc(id),
 FOREIGN KEY(san_pham_id) REFERENCES san_pham(id),
 CHECK (gia >= 1000), INDEX idx_tin_duyet(trang_thai,ngay_tao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS kiem_dinh_ngoc (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tin_dang_id INT UNSIGNED NOT NULL UNIQUE,
 nguoi_duyet_id INT UNSIGNED NULL,
 trang_thai ENUM('cho_kiem_tra','tai_lieu_hop_le','can_bo_sung','khong_hop_le') NOT NULL DEFAULT 'cho_kiem_tra',
 ghi_chu VARCHAR(1000) NOT NULL DEFAULT '',
 ngay_duyet DATETIME NULL,
 FOREIGN KEY(tin_dang_id) REFERENCES tin_dang_ngoc(id) ON DELETE CASCADE,
 FOREIGN KEY(nguoi_duyet_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS bai_viet_cong_dong (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tac_gia_id INT UNSIGNED NOT NULL,
 tieu_de VARCHAR(180) NOT NULL,
 noi_dung TEXT NOT NULL,
 loai ENUM('thao_luan','nho_tham_dinh') NOT NULL DEFAULT 'thao_luan',
 ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(tac_gia_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
 INDEX idx_bai_ngay(ngay_tao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS binh_luan_cong_dong (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 bai_viet_id INT UNSIGNED NOT NULL,
 tac_gia_id INT UNSIGNED NOT NULL,
 noi_dung VARCHAR(2000) NOT NULL,
 ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(bai_viet_id) REFERENCES bai_viet_cong_dong(id) ON DELETE CASCADE,
 FOREIGN KEY(tac_gia_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
 INDEX idx_bl_bai(bai_viet_id,ngay_tao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS theo_doi (
 nguoi_theo_doi_id INT UNSIGNED NOT NULL,
 nguoi_duoc_theo_doi_id INT UNSIGNED NOT NULL,
 ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(nguoi_theo_doi_id,nguoi_duoc_theo_doi_id),
 FOREIGN KEY(nguoi_theo_doi_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
 FOREIGN KEY(nguoi_duoc_theo_doi_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
 CHECK(nguoi_theo_doi_id <> nguoi_duoc_theo_doi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS tin_nhan (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nguoi_gui_id INT UNSIGNED NOT NULL,
 nguoi_nhan_id INT UNSIGNED NOT NULL,
 noi_dung VARCHAR(2000) NOT NULL,
 ngay_gui DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 da_doc TINYINT(1) NOT NULL DEFAULT 0,
 FOREIGN KEY(nguoi_gui_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
 FOREIGN KEY(nguoi_nhan_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
 CHECK(nguoi_gui_id <> nguoi_nhan_id),
 INDEX idx_tin_gui(nguoi_gui_id,nguoi_nhan_id,id),
 INDEX idx_tin_nhan(nguoi_nhan_id,nguoi_gui_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chọn CSDL JadeHub hiện tại trước khi nhập. Có thể chạy lại nếu lần trước bị dở dang.

SET @jadehub_ddl = IF(
  EXISTS (SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nguoi_dung' AND COLUMN_NAME = 'tieu_su'),
  'SELECT 1', 'ALTER TABLE nguoi_dung ADD COLUMN tieu_su VARCHAR(500) NOT NULL DEFAULT '''''
);
PREPARE jadehub_stmt FROM @jadehub_ddl;
EXECUTE jadehub_stmt;
DEALLOCATE PREPARE jadehub_stmt;

SET @jadehub_ddl = IF(
  EXISTS (SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nguoi_dung' AND COLUMN_NAME = 'anh_dai_dien'),
  'SELECT 1', 'ALTER TABLE nguoi_dung ADD COLUMN anh_dai_dien MEDIUMBLOB NULL'
);
PREPARE jadehub_stmt FROM @jadehub_ddl;
EXECUTE jadehub_stmt;
DEALLOCATE PREPARE jadehub_stmt;

SET @jadehub_ddl = IF(
  EXISTS (SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nguoi_dung' AND COLUMN_NAME = 'anh_bia'),
  'SELECT 1', 'ALTER TABLE nguoi_dung ADD COLUMN anh_bia MEDIUMBLOB NULL'
);
PREPARE jadehub_stmt FROM @jadehub_ddl;
EXECUTE jadehub_stmt;
DEALLOCATE PREPARE jadehub_stmt;

SET @jadehub_ddl = IF(
  EXISTS (SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bai_viet_cong_dong' AND COLUMN_NAME = 'anh'),
  'SELECT 1', 'ALTER TABLE bai_viet_cong_dong ADD COLUMN anh MEDIUMBLOB NULL'
);
PREPARE jadehub_stmt FROM @jadehub_ddl;
EXECUTE jadehub_stmt;
DEALLOCATE PREPARE jadehub_stmt;

SET @jadehub_ddl = IF(
  EXISTS (SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tin_nhan' AND COLUMN_NAME = 'anh'),
  'SELECT 1', 'ALTER TABLE tin_nhan ADD COLUMN anh MEDIUMBLOB NULL'
);
PREPARE jadehub_stmt FROM @jadehub_ddl;
EXECUTE jadehub_stmt;
DEALLOCATE PREPARE jadehub_stmt;

SET @jadehub_ddl = IF(
  EXISTS (SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tin_nhan' AND COLUMN_NAME = 'bai_chia_se_id'),
  'SELECT 1', 'ALTER TABLE tin_nhan ADD COLUMN bai_chia_se_id INT UNSIGNED NULL'
);
PREPARE jadehub_stmt FROM @jadehub_ddl;
EXECUTE jadehub_stmt;
DEALLOCATE PREPARE jadehub_stmt;

CREATE TABLE IF NOT EXISTS yeu_thich_bai_viet (
  nguoi_dung_id INT UNSIGNED NOT NULL,
  bai_viet_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (nguoi_dung_id, bai_viet_id),
  FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
  FOREIGN KEY (bai_viet_id) REFERENCES bai_viet_cong_dong(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS thong_bao_xa_hoi (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nguoi_nhan_id INT UNSIGNED NOT NULL,
  tac_nhan_id INT UNSIGNED NOT NULL,
  loai ENUM('theo_doi','binh_luan','yeu_thich') NOT NULL,
  doi_tuong_id INT UNSIGNED NOT NULL,
  da_doc BOOLEAN NOT NULL DEFAULT FALSE,
  ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (nguoi_nhan_id, da_doc, id),
  FOREIGN KEY (nguoi_nhan_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
  FOREIGN KEY (tac_nhan_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


SELECT DATABASE() AS co_so_du_lieu,
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
    AND ((TABLE_NAME='nguoi_dung' AND COLUMN_NAME IN ('tieu_su','anh_dai_dien','anh_bia'))
      OR (TABLE_NAME='bai_viet_cong_dong' AND COLUMN_NAME='anh')
      OR (TABLE_NAME='tin_nhan' AND COLUMN_NAME IN ('anh','bai_chia_se_id')))) AS so_cot_xa_hoi,
  (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME IN ('yeu_thich_bai_viet','thong_bao_xa_hoi')) AS so_bang_xa_hoi;

-- Chọn cơ sở dữ liệu JadeHub hiện tại trước khi nhập.
START TRANSACTION;
UPDATE san_pham SET ten = 'Nhẫn ngọc phỉ thúy Lam Thiên Không', loai = 'Phỉ thúy Type A - Băng chủng', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '2.22 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.34 g/cm³', anh = 'nhan-phi-thuy-01.jpg', anh_chung_thu = 'giay-kiem-dinh-01.jpg' WHERE ma = 'JADE-01';
UPDATE san_pham SET ten = 'Vòng tay cẩm thạch lý huyết lục bảo', loai = 'Cẩm thạch tự nhiên - Jadeite', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (LiuLab)', trong_luong = '48.5 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-02.jpg', anh_chung_thu = 'giay-kiem-dinh-02.jpg' WHERE ma = 'JADE-02';
UPDATE san_pham SET ten = 'Mặt dây chuyền Như Ý ngọc bích Hòa Điền', loai = 'Ngọc bích Nephrite', xuat_xu = 'Tân Cương', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (DOJI Lab)', trong_luong = '8.4 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.95 g/cm³', anh = 'JADE-03.jpg', anh_chung_thu = 'giay-kiem-dinh-03.jpg' WHERE ma = 'JADE-03';
UPDATE san_pham SET ten = 'Chuỗi hạt ngọc phỉ thúy đậu chủng', loai = 'Phỉ thúy Type A', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Chờ thẩm định bổ sung', trong_luong = '32.0 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.32 g/cm³', anh = 'JADE-04.jpg', anh_chung_thu = 'giay-kiem-dinh-04.jpg' WHERE ma = 'JADE-04';
UPDATE san_pham SET ten = 'Nhẫn cẩm thạch xanh rêu An Nhiên', loai = 'Cẩm thạch Jadeite', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '2.05 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-05.jpg', anh_chung_thu = 'giay-kiem-dinh-05.jpg' WHERE ma = 'JADE-05';
UPDATE san_pham SET ten = 'Vòng tay phỉ thúy Bạch Nguyệt', loai = 'Phỉ thúy Type A', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (LiuLab)', trong_luong = '42.1 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.34 g/cm³', anh = 'JADE-06.jpg', anh_chung_thu = 'giay-kiem-dinh-06.jpg' WHERE ma = 'JADE-06';
UPDATE san_pham SET ten = 'Mặt ngọc bình an Hòa Điền', loai = 'Ngọc bích Nephrite', xuat_xu = 'Tân Cương', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (DOJI Lab)', trong_luong = '7.8 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.96 g/cm³', anh = 'JADE-07.jpg', anh_chung_thu = 'giay-kiem-dinh-07.jpg' WHERE ma = 'JADE-07';
UPDATE san_pham SET ten = 'Chuỗi hạt phỉ thúy Thanh Vân', loai = 'Phỉ thúy Type A', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '30.7 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-08.jpg', anh_chung_thu = 'giay-kiem-dinh-08.jpg' WHERE ma = 'JADE-08';
UPDATE san_pham SET ten = 'Nhẫn phỉ thúy Lục Bảo', loai = 'Phỉ thúy Type A', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (SJC Lab)', trong_luong = '2.60 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.34 g/cm³', anh = 'JADE-09.jpg', anh_chung_thu = 'giay-kiem-dinh-09.jpg' WHERE ma = 'JADE-09';
UPDATE san_pham SET ten = 'Vòng cẩm thạch Xuân Sắc', loai = 'Cẩm thạch Jadeite', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (LiuLab)', trong_luong = '46.3 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-10.jpg', anh_chung_thu = 'giay-kiem-dinh-10.jpg' WHERE ma = 'JADE-10';
UPDATE san_pham SET ten = 'Mặt dây ngọc bích Trường An', loai = 'Ngọc bích Nephrite', xuat_xu = 'Tân Cương', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (DOJI Lab)', trong_luong = '9.1 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.95 g/cm³', anh = 'JADE-11.jpg', anh_chung_thu = 'giay-kiem-dinh-11.jpg' WHERE ma = 'JADE-11';
UPDATE san_pham SET ten = 'Chuỗi hạt phỉ thúy Minh Châu', loai = 'Phỉ thúy Type A', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '35.4 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.34 g/cm³', anh = 'JADE-12.jpg', anh_chung_thu = 'giay-kiem-dinh-12.jpg' WHERE ma = 'JADE-12';
UPDATE san_pham SET ten = 'Chuỗi hạt ngọc bích Levi''s Emerald 50%', loai = 'Ngọc bích Nephrite cao cấp', xuat_xu = 'Canada', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (GIA Lab)', trong_luong = '34.2 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.96 g/cm³', anh = 'JADE-13.jpg', anh_chung_thu = 'giay-kiem-dinh-13.jpg' WHERE ma = 'JADE-13';
UPDATE san_pham SET ten = 'Vòng tay phỉ thúy băng chủng Tuyết Lục', loai = 'Phỉ thúy Type A - Băng chủng', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '44.0 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.34 g/cm³', anh = 'JADE-14.jpg', anh_chung_thu = 'giay-kiem-dinh-14.jpg' WHERE ma = 'JADE-14';
UPDATE san_pham SET ten = 'Mặt Phật Di Lặc cẩm thạch Hoàng Phỉ', loai = 'Cẩm thạch Jadeite', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (LiuLab)', trong_luong = '14.5 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-15.jpg', anh_chung_thu = 'giay-kiem-dinh-15.jpg' WHERE ma = 'JADE-15';
UPDATE san_pham SET ten = 'Bội bạch ngọc Hòa Điền Dương Chi', loai = 'Bạch ngọc Nephrite', xuat_xu = 'Tân Cương', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (DOJI Lab)', trong_luong = '18.2 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.95 g/cm³', anh = 'JADE-16.jpg', anh_chung_thu = 'giay-kiem-dinh-16.jpg' WHERE ma = 'JADE-16';
UPDATE san_pham SET ten = 'Nhẫn hoàng ngọc Kim Quang bảo giám', loai = 'Hoàng ngọc tự nhiên', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '3.1 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-17.jpg', anh_chung_thu = 'giay-kiem-dinh-17.jpg' WHERE ma = 'JADE-17';
UPDATE san_pham SET ten = 'Ấn chương rồng ngọc bích Thanh Long', loai = 'Ngọc bích Nephrite', xuat_xu = 'Tân Cương', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (SJC Lab)', trong_luong = '120.5 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.96 g/cm³', anh = 'JADE-18.jpg', anh_chung_thu = 'giay-kiem-dinh-18.jpg' WHERE ma = 'JADE-18';
UPDATE san_pham SET ten = 'Tượng Tỳ Hưu cẩm thạch Chiêu Tài', loai = 'Cẩm thạch Jadeite', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (LiuLab)', trong_luong = '95.0 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-19.jpg', anh_chung_thu = 'giay-kiem-dinh-19.jpg' WHERE ma = 'JADE-19';
UPDATE san_pham SET ten = 'Hoa tai phỉ thúy giọt sương Mai', loai = 'Phỉ thúy Type A', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '4.2 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.34 g/cm³', anh = 'JADE-20.jpg', anh_chung_thu = 'giay-kiem-dinh-20.jpg' WHERE ma = 'JADE-20';
UPDATE san_pham SET ten = 'Vòng tay cẩm thạch Tử La Lan', loai = 'Cẩm thạch Jadeite tím', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (DOJI Lab)', trong_luong = '45.8 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-21.jpg', anh_chung_thu = 'giay-kiem-dinh-21.jpg' WHERE ma = 'JADE-21';
UPDATE san_pham SET ten = 'Chuỗi vòng tay ngọc bích 18 hạt Bích Thủy', loai = 'Ngọc bích Nephrite', xuat_xu = 'Nga', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '28.0 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.95 g/cm³', anh = 'JADE-22.jpg', anh_chung_thu = 'giay-kiem-dinh-22.jpg' WHERE ma = 'JADE-22';
UPDATE san_pham SET ten = 'Mặt dây bạch ngọc Quan Âm Tịnh Thủy', loai = 'Bạch ngọc Nephrite', xuat_xu = 'Tân Cương', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (LiuLab)', trong_luong = '16.0 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.95 g/cm³', anh = 'JADE-23.jpg', anh_chung_thu = 'giay-kiem-dinh-23.jpg' WHERE ma = 'JADE-23';
UPDATE san_pham SET ten = 'Ấn chương bạch ngọc Văn Xương Đế Quân', loai = 'Bạch ngọc Nephrite', xuat_xu = 'Tân Cương', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (SJC Lab)', trong_luong = '110.0 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.96 g/cm³', anh = 'JADE-24.jpg', anh_chung_thu = 'giay-kiem-dinh-24.jpg' WHERE ma = 'JADE-24';
UPDATE san_pham SET ten = 'Mặt dây phỉ thúy đồng điếu Cát Tường', loai = 'Phỉ thúy Type A', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '6.5 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-25.jpg', anh_chung_thu = 'giay-kiem-dinh-25.jpg' WHERE ma = 'JADE-25';
UPDATE san_pham SET ten = 'Nhẫn bản vuông cẩm thạch Sơn Thủy', loai = 'Cẩm thạch Jadeite', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (DOJI Lab)', trong_luong = '4.8 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-26.jpg', anh_chung_thu = 'giay-kiem-dinh-26.jpg' WHERE ma = 'JADE-26';
UPDATE san_pham SET ten = 'Mặt dây ngọc bích Hồ Lô Phúc Lộc', loai = 'Ngọc bích Nephrite', xuat_xu = 'Tân Cương', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '7.2 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.95 g/cm³', anh = 'JADE-27.jpg', anh_chung_thu = 'giay-kiem-dinh-27.jpg' WHERE ma = 'JADE-27';
UPDATE san_pham SET ten = 'Vòng tay bạch ngọc Thanh Liên', loai = 'Bạch ngọc Nephrite', xuat_xu = 'Tân Cương', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (LiuLab)', trong_luong = '43.5 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.95 g/cm³', anh = 'JADE-28.jpg', anh_chung_thu = 'giay-kiem-dinh-28.jpg' WHERE ma = 'JADE-28';
UPDATE san_pham SET ten = 'Tượng Thiềm Thừ ngọc bích trấn trạch', loai = 'Ngọc bích Nephrite', xuat_xu = 'Canada', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (SJC Lab)', trong_luong = '145.0 g', do_cung = '6.0 - 6.5', chiet_suat = '1.61', ty_trong = '2.96 g/cm³', anh = 'JADE-29.jpg', anh_chung_thu = 'giay-kiem-dinh-29.jpg' WHERE ma = 'JADE-29';
UPDATE san_pham SET ten = 'Lắc tay hạt phỉ thúy Lục Châu', loai = 'Phỉ thúy Type A', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (PNJ Lab)', trong_luong = '19.5 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.34 g/cm³', anh = 'JADE-30.jpg', anh_chung_thu = 'giay-kiem-dinh-30.jpg' WHERE ma = 'JADE-30';
UPDATE san_pham SET ten = 'Mặt dây cẩm thạch Lá Ngọc Cành Vàng', loai = 'Cẩm thạch Jadeite', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (LiuLab)', trong_luong = '5.9 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-31.jpg', anh_chung_thu = 'giay-kiem-dinh-31.jpg' WHERE ma = 'JADE-31';
UPDATE san_pham SET ten = 'Chuỗi hạt hoàng ngọc Thu Vũ', loai = 'Hoàng ngọc tự nhiên', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (DOJI Lab)', trong_luong = '31.2 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.33 g/cm³', anh = 'JADE-32.jpg', anh_chung_thu = 'giay-kiem-dinh-32.jpg' WHERE ma = 'JADE-32';
UPDATE san_pham SET ten = 'Tượng cá chép hóa rồng cẩm thạch', loai = 'Cẩm thạch Jadeite', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (SJC Lab)', trong_luong = '132.0 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.34 g/cm³', anh = 'JADE-33.jpg', anh_chung_thu = 'giay-kiem-dinh-33.jpg' WHERE ma = 'JADE-33';
UPDATE san_pham SET ten = 'Nhẫn phỉ thúy Đế Vương Lục bảo truyền', loai = 'Phỉ thúy Type A - Kính chủng', xuat_xu = 'Myanmar', kiem_dinh = 'Hồ sơ minh họa · Đã thẩm định (GIA Lab)', trong_luong = '3.45 g', do_cung = '6.5 - 7.0', chiet_suat = '1.66', ty_trong = '3.34 g/cm³', anh = 'JADE-34.jpg', anh_chung_thu = 'giay-kiem-dinh-34.jpg' WHERE ma = 'JADE-34';
COMMIT;


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
