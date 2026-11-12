-- database/schema.sql — xóa và tạo lại CSDL ltweb_nhom06 từ đầu (chạy lại được nhiều lần)
-- Nhóm 06: JadeHub – Sàn giao dịch và thẩm định ngọc quý
-- Chạy: mysql -u root < database/schema.sql

SET NAMES utf8mb4;
DROP DATABASE IF EXISTS ltweb_nhom06;
CREATE DATABASE ltweb_nhom06
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ltweb_nhom06;

-- =========================================================================
-- PHẦN A: CÁC BẢNG CỦA ĐỒ ÁN JADEHUB (7 bảng)
-- =========================================================================

-- 1. Bảng danh_muc: các danh mục ngọc quý (quan hệ 1 - n với san_pham)
CREATE TABLE danh_muc (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten  VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng san_pham: thực thể chính (viên ngọc / vật phẩm ngọc quý niêm yết)
-- Không ghi ON DELETE cho danh_muc_id: mặc định RESTRICT (cấm xóa danh mục còn sản phẩm, lỗi 1451)
CREATE TABLE san_pham (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  danh_muc_id   INT UNSIGNED NOT NULL,
  ma            VARCHAR(30)  NOT NULL UNIQUE,
  ten           VARCHAR(200) NOT NULL,
  loai          VARCHAR(150) NOT NULL DEFAULT 'Ngọc tự nhiên',
  gia           INT UNSIGNED NOT NULL CHECK (gia >= 1000),
  ton_kho       INT UNSIGNED NOT NULL DEFAULT 0,
  xuat_xu       VARCHAR(100) NOT NULL DEFAULT 'Myanmar',
  kiem_dinh     VARCHAR(150) NOT NULL DEFAULT 'Đã thẩm định',
  trong_luong   VARCHAR(50)  NOT NULL DEFAULT '—',
  do_cung       VARCHAR(50)  NOT NULL DEFAULT '6.5 - 7.0',
  chiet_suat    VARCHAR(50)  NOT NULL DEFAULT '1.66',
  ty_trong      VARCHAR(50)  NOT NULL DEFAULT '3.33 g/cm³',
  anh           VARCHAR(255) NULL,
  anh_chung_thu VARCHAR(255) NULL,
  mo_ta         TEXT         NULL,
  ngay_tao      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_san_pham_danh_muc FOREIGN KEY (danh_muc_id) REFERENCES danh_muc(id),
  INDEX idx_san_pham_dm_gia (danh_muc_id, gia),
  INDEX idx_san_pham_gia (gia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng nguoi_dung: tài khoản khách hàng và quản trị viên
CREATE TABLE nguoi_dung (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email    VARCHAR(150) NOT NULL UNIQUE,
  ho_ten   VARCHAR(100) NOT NULL,
  mat_khau VARCHAR(255) NOT NULL,
  vai_tro  ENUM('khach_hang', 'quan_tri') NOT NULL DEFAULT 'khach_hang',
  ngay_tao DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng don_hang: đơn đặt mua ngọc của khách hàng (quan hệ 1 - n từ nguoi_dung)
CREATE TABLE don_hang (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nguoi_dung_id INT UNSIGNED NOT NULL,
  trang_thai    ENUM('cho_xac_nhan', 'dang_giao', 'hoan_thanh', 'da_huy') NOT NULL DEFAULT 'cho_xac_nhan',
  ghi_chu       VARCHAR(500) NULL,
  ngay_dat      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_don_hang_nguoi_dung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id),
  INDEX idx_don_hang_nguoi_dung (nguoi_dung_id, ngay_dat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng chi_tiet_don: bảng trung gian nối n - n giữa don_hang và san_pham
-- Khóa chính ghép (don_hang_id, san_pham_id); don_gia lưu lặp có chủ đích giá tại thời điểm đặt
-- FK san_pham_id không ghi ON DELETE (mặc định RESTRICT): cấm xóa sản phẩm đã có trong đơn hàng (lỗi 1451)
CREATE TABLE chi_tiet_don (
  don_hang_id INT UNSIGNED      NOT NULL,
  san_pham_id INT UNSIGNED      NOT NULL,
  so_luong    SMALLINT UNSIGNED NOT NULL CHECK (so_luong >= 1 AND so_luong <= 99),
  don_gia     INT UNSIGNED      NOT NULL CHECK (don_gia >= 1000),
  PRIMARY KEY (don_hang_id, san_pham_id),
  CONSTRAINT fk_ctd_don_hang FOREIGN KEY (don_hang_id) REFERENCES don_hang(id) ON DELETE CASCADE,
  CONSTRAINT fk_ctd_san_pham FOREIGN KEY (san_pham_id) REFERENCES san_pham(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng lien_he: yêu cầu thẩm định ngọc và liên hệ từ khách
CREATE TABLE lien_he (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ho_ten    VARCHAR(100) NOT NULL,
  email     VARCHAR(150) NOT NULL,
  sdt       VARCHAR(20)  NOT NULL,
  loai_ngoc VARCHAR(50)  NOT NULL DEFAULT 'phithuy',
  so_luong  TINYINT UNSIGNED NOT NULL DEFAULT 1 CHECK (so_luong >= 1 AND so_luong <= 20),
  ngay_hen  DATE         NOT NULL,
  ghi_chu   TEXT         NULL,
  anh       VARCHAR(255) NULL,
  thoi_gian DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_lien_he_thoi_gian (thoi_gian)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng ghi_nho: mã ghi nhớ đăng nhập (selector + băm SHA-256 của validator)
CREATE TABLE ghi_nho (
  selector       CHAR(16)     PRIMARY KEY,
  validator_hash CHAR(64)     NOT NULL,
  nguoi_dung_id  INT UNSIGNED NOT NULL,
  het_han        DATETIME     NOT NULL,
  CONSTRAINT fk_ghi_nho_nguoi_dung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- PHẦN B: BẢNG THÀNH VIÊN VÀ CÁC BẢNG RIÊNG (tv_...) CỦA 5 THÀNH VIÊN (6 bảng)
-- =========================================================================

-- 8. Bảng thanh_vien: 5 thành viên Nhóm 06, liên kết tài khoản chủ trang
CREATE TABLE thanh_vien (
  mssv          CHAR(10)     PRIMARY KEY,
  ho_ten        VARCHAR(100) NOT NULL,
  nguoi_dung_id INT UNSIGNED NULL UNIQUE,
  anh_dai_dien  VARCHAR(255) NULL,
  CONSTRAINT fk_thanh_vien_nguoi_dung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bảng riêng của Trần Thị Cẩm Thanh (3120224133): tv_diem_hoc_phan
CREATE TABLE tv_diem_hoc_phan (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10)      NOT NULL,
  ten_mon   VARCHAR(120)  NOT NULL,
  so_tin    TINYINT UNSIGNED NOT NULL DEFAULT 3 CHECK (so_tin >= 1 AND so_tin <= 10),
  diem_a1   DECIMAL(4,2)  NOT NULL CHECK (diem_a1 >= 0 AND diem_a1 <= 10),
  diem_a2   DECIMAL(4,2)  NOT NULL CHECK (diem_a2 >= 0 AND diem_a2 <= 10),
  diem_a3   DECIMAL(4,2)  NOT NULL CHECK (diem_a3 >= 0 AND diem_a3 <= 10),
  thoi_gian DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tv_diem_tv FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Bảng riêng của Nguyễn Thị Bích Hạnh (3120224050): tv_luu_but và tv_luot_xem
CREATE TABLE tv_luu_but (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10)     NOT NULL,
  ten_khach VARCHAR(50)  NOT NULL,
  loi_nhan  VARCHAR(500) NOT NULL,
  thoi_gian DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tv_luu_but_tv FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tv_luot_xem (
  mssv      CHAR(10)     NOT NULL,
  ngay      DATE         NOT NULL,
  so_luot   INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (mssv, ngay),
  CONSTRAINT fk_tv_luot_xem_tv FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Bảng riêng của Nguyễn Nhật Ánh Hồng (3120224063): tv_thoi_khoa_bieu
CREATE TABLE tv_thoi_khoa_bieu (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10)     NOT NULL,
  thu       ENUM('hai', 'ba', 'tu', 'nam', 'sau', 'bay') NOT NULL,
  buoi      ENUM('Sáng', 'Chiều') NOT NULL,
  hoc_phan  VARCHAR(150) NOT NULL,
  phong     VARCHAR(50)  NOT NULL DEFAULT 'Tự học',
  CONSTRAINT fk_tv_tkb_tv FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Bảng riêng của Huỳnh Trần Phương Trinh (3120124031): tv_ky_nang và tv_viec_can_lam
CREATE TABLE tv_ky_nang (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv    CHAR(10)     NOT NULL,
  nhom    ENUM('web', 'csdl', 'cong-cu', 'he-thong') NOT NULL DEFAULT 'web',
  ten     VARCHAR(150) NOT NULL,
  muc_do  TINYINT UNSIGNED NOT NULL DEFAULT 80 CHECK (muc_do >= 10 AND muc_do <= 100),
  CONSTRAINT fk_tv_ky_nang_tv FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tv_viec_can_lam (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10)     NOT NULL,
  noi_dung  VARCHAR(150) NOT NULL,
  da_xong   BOOLEAN      NOT NULL DEFAULT 0,
  ngay_tao  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tv_viec_tv FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Bảng riêng của Ladisa Bouakeo (3120124038): tv_danh_gia
CREATE TABLE tv_danh_gia (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10)     NOT NULL,
  ten_khach VARCHAR(50)  NOT NULL,
  so_sao    TINYINT UNSIGNED NOT NULL CHECK (so_sao >= 1 AND so_sao <= 5),
  nhan_xet  VARCHAR(300) NOT NULL DEFAULT '',
  thoi_gian DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tv_danh_gia_tv FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
