-- database/seed.sql — dữ liệu mẫu cho CSDL ltweb_nhom06 (chạy sau schema.sql)
-- Mật khẩu của mọi tài khoản thử: matkhau123
-- Chuỗi băm tạo bằng: php -r "echo password_hash('matkhau123', PASSWORD_DEFAULT);"
-- Chạy: mysql -u root ltweb_nhom06 < database/seed.sql

SET NAMES utf8mb4;
USE ltweb_nhom06;

-- 1. Danh mục ngọc quý (6 danh mục; danh mục 6 'ngoc-tho' chưa có sản phẩm để kiểm thử LEFT JOIN / danh mục trống)
INSERT INTO danh_muc (id, ten, slug) VALUES
  (1, 'Ngọc phỉ thúy', 'phi-thuy'),
  (2, 'Ngọc cẩm thạch', 'cam-thach'),
  (3, 'Ngọc bích Hòa Điền', 'ngoc-bich'),
  (4, 'Bạch ngọc & Hoàng ngọc', 'bach-ngoc'),
  (5, 'Ấn chương & Tượng ngọc', 'an-chuong'),
  (6, 'Ngọc thô nguyên bản', 'ngoc-tho');

-- 2. Sản phẩm ngọc quý (34 dòng thuộc 5 danh mục 1..5; có mục hết hàng ton_kho = 0; có mục chứa dấu nháy đơn Levi's và 50%)
INSERT INTO san_pham (id, danh_muc_id, ma, ten, loai, gia, ton_kho, xuat_xu, kiem_dinh, trong_luong, do_cung, chiet_suat, ty_trong, anh, anh_chung_thu, mo_ta, ngay_tao) VALUES
  (1,  1, 'JADE-01', 'Nhẫn ngọc phỉ thúy Lam Thiên Không', 'Phỉ thúy Type A - Băng chủng', 18500000, 5, 'Myanmar', 'Đã thẩm định (PNJ Lab)', '2.22 g', '6.5 - 7.0', '1.66', '3.34 g/cm³', 'nhan-phi-thuy-01.jpg', 'giay-kiem-dinh-01.jpg', 'Nhẫn phỉ thúy Type A băng chủng, nước ngọc xanh lam, độ thấu quang cao.', '2026-10-01 08:00:00'),
  (2,  2, 'JADE-02', 'Vòng tay cẩm thạch lý huyết lục bảo', 'Cẩm thạch tự nhiên - Jadeite', 45000000, 3, 'Myanmar', 'Đã thẩm định (LiuLab)', '48.5 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-02.jpg', 'giay-kiem-dinh-02.jpg', 'Vòng tay jadeite tự nhiên, sắc xanh lục bảo, vân ngọc rõ và bề mặt bóng.', '2026-10-01 09:00:00'),
  (3,  3, 'JADE-03', 'Mặt dây chuyền Như Ý ngọc bích Hòa Điền', 'Ngọc bích Nephrite', 9200000, 8, 'Tân Cương', 'Đã thẩm định (DOJI Lab)', '8.4 g', '6.0 - 6.5', '1.61', '2.95 g/cm³', 'JADE-03.jpg', 'giay-kiem-dinh-03.jpg', 'Mặt dây Như Ý từ nephrite Hòa Điền, màu xanh dịu, cấu trúc mịn.', '2026-10-02 08:30:00'),
  (4,  1, 'JADE-04', 'Chuỗi hạt ngọc phỉ thúy đậu chủng', 'Phỉ thúy Type A', 12000000, 6, 'Myanmar', 'Chờ thẩm định bổ sung', '32.0 g', '6.5 - 7.0', '1.66', '3.32 g/cm³', 'JADE-04.jpg', NULL, 'Chuỗi hạt phỉ thúy Type A đậu chủng, màu xanh tự nhiên, hạt tròn đồng đều.', '2026-10-02 10:15:00'),
  (5,  2, 'JADE-05', 'Nhẫn cẩm thạch xanh rêu An Nhiên', 'Cẩm thạch Jadeite', 15800000, 4, 'Myanmar', 'Đã thẩm định (PNJ Lab)', '2.05 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-05.jpg', 'giay-kiem-dinh-05.jpg', 'Nhẫn jadeite xanh rêu tự nhiên, kiểu dáng tối giản.', '2026-10-03 08:00:00'),
  (6,  1, 'JADE-06', 'Vòng tay phỉ thúy Bạch Nguyệt', 'Phỉ thúy Type A', 26800000, 2, 'Myanmar', 'Đã thẩm định (LiuLab)', '42.1 g', '6.5 - 7.0', '1.66', '3.34 g/cm³', 'JADE-06.jpg', 'giay-kiem-dinh-06.jpg', 'Vòng phỉ thúy nền sáng, sắc xanh nhẹ, bề mặt đánh bóng.', '2026-10-03 14:20:00'),
  (7,  3, 'JADE-07', 'Mặt ngọc bình an Hòa Điền', 'Ngọc bích Nephrite', 7600000, 10, 'Tân Cương', 'Đã thẩm định (DOJI Lab)', '7.8 g', '6.0 - 6.5', '1.61', '2.96 g/cm³', 'JADE-07.jpg', 'giay-kiem-dinh-07.jpg', 'Mặt ngọc bình an nephrite, màu xanh ô-liu và kết cấu chắc.', '2026-10-04 09:00:00'),
  (8,  1, 'JADE-08', 'Chuỗi hạt phỉ thúy Thanh Vân', 'Phỉ thúy Type A', 14500000, 7, 'Myanmar', 'Đã thẩm định (PNJ Lab)', '30.7 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-08.jpg', 'giay-kiem-dinh-08.jpg', 'Chuỗi hạt phỉ thúy xanh thanh, phù hợp đeo hằng ngày.', '2026-10-04 11:30:00'),
  (9,  1, 'JADE-09', 'Nhẫn phỉ thúy Lục Bảo', 'Phỉ thúy Type A', 32500000, 0, 'Myanmar', 'Đã thẩm định (SJC Lab)', '2.60 g', '6.5 - 7.0', '1.66', '3.34 g/cm³', 'JADE-09.jpg', 'giay-kiem-dinh-09.jpg', 'Nhẫn phỉ thúy sắc lục đậm, cabochon tròn và độ bóng cao (tạm hết hàng).', '2026-10-05 08:45:00'),
  (10, 2, 'JADE-10', 'Vòng cẩm thạch Xuân Sắc', 'Cẩm thạch Jadeite', 38900000, 3, 'Myanmar', 'Đã thẩm định (LiuLab)', '46.3 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-10.jpg', 'giay-kiem-dinh-10.jpg', 'Vòng jadeite phối sắc xanh tím nhẹ, vân tự nhiên.', '2026-10-05 15:10:00'),
  (11, 3, 'JADE-11', 'Mặt dây ngọc bích Trường An', 'Ngọc bích Nephrite', 10800000, 6, 'Tân Cương', 'Đã thẩm định (DOJI Lab)', '9.1 g', '6.0 - 6.5', '1.61', '2.95 g/cm³', 'JADE-11.jpg', 'giay-kiem-dinh-11.jpg', 'Mặt dây nephrite chạm tối giản, màu xanh ấm.', '2026-10-06 09:20:00'),
  (12, 1, 'JADE-12', 'Chuỗi hạt phỉ thúy Minh Châu', 'Phỉ thúy Type A', 19800000, 5, 'Myanmar', 'Đã thẩm định (PNJ Lab)', '35.4 g', '6.5 - 7.0', '1.66', '3.34 g/cm³', 'JADE-12.jpg', 'giay-kiem-dinh-12.jpg', 'Chuỗi phỉ thúy hạt tròn đồng đều, sắc xanh trong.', '2026-10-06 16:00:00'),
  (13, 3, 'JADE-13', 'Chuỗi hạt ngọc bích Levi''s Emerald 50%', 'Ngọc bích Nephrite cao cấp', 21500000, 4, 'Canada', 'Đã thẩm định (GIA Lab)', '34.2 g', '6.0 - 6.5', '1.61', '2.96 g/cm³', 'JADE-08.jpg', 'giay-kiem-dinh-08.jpg', 'Phiên bản sưu tầm hợp tác Levi''s với độ trong 50% ngọc bích tự nhiên.', '2026-10-07 08:00:00'),
  (14, 1, 'JADE-14', 'Vòng tay phỉ thúy băng chủng Tuyết Lục', 'Phỉ thúy Type A - Băng chủng', 52000000, 2, 'Myanmar', 'Đã thẩm định (PNJ Lab)', '44.0 g', '6.5 - 7.0', '1.66', '3.34 g/cm³', 'JADE-06.jpg', 'giay-kiem-dinh-06.jpg', 'Vòng tay phỉ thúy băng chủng trong suốt kèm dải lục tươi.', '2026-10-07 10:00:00'),
  (15, 2, 'JADE-15', 'Mặt Phật Di Lặc cẩm thạch Hoàng Phỉ', 'Cẩm thạch Jadeite', 16500000, 5, 'Myanmar', 'Đã thẩm định (LiuLab)', '14.5 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-03.jpg', 'giay-kiem-dinh-03.jpg', 'Mặt dây chạm Phật Di Lặc từ cẩm thạch sắc vàng mật ong tự nhiên.', '2026-10-08 09:00:00'),
  (16, 4, 'JADE-16', 'Bội bạch ngọc Hòa Điền Dương Chi', 'Bạch ngọc Nephrite', 29000000, 3, 'Tân Cương', 'Đã thẩm định (DOJI Lab)', '18.2 g', '6.0 - 6.5', '1.61', '2.95 g/cm³', 'JADE-07.jpg', 'giay-kiem-dinh-07.jpg', 'Bội bạch ngọc trắng mịn ánh mỡ cừu, chạm vân mây cổ điển.', '2026-10-08 11:00:00'),
  (17, 4, 'JADE-17', 'Nhẫn hoàng ngọc Kim Quang bảo giám', 'Hoàng ngọc tự nhiên', 13200000, 6, 'Myanmar', 'Đã thẩm định (PNJ Lab)', '3.1 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-05.jpg', 'giay-kiem-dinh-05.jpg', 'Nhẫn hoàng ngọc sắc vàng ấm, nước ngọc mịn và sáng.', '2026-10-08 15:00:00'),
  (18, 5, 'JADE-18', 'Ấn chương rồng ngọc bích Thanh Long', 'Ngọc bích Nephrite', 42000000, 1, 'Tân Cương', 'Đã thẩm định (SJC Lab)', '120.5 g', '6.0 - 6.5', '1.61', '2.96 g/cm³', 'JADE-11.jpg', 'giay-kiem-dinh-11.jpg', 'Ấn chương chạm khắc hình rồng nguyên khối từ ngọc bích Hòa Điền.', '2026-10-09 08:30:00'),
  (19, 5, 'JADE-19', 'Tượng Tỳ Hưu cẩm thạch Chiêu Tài', 'Cẩm thạch Jadeite', 35000000, 2, 'Myanmar', 'Đã thẩm định (LiuLab)', '95.0 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-02.jpg', 'giay-kiem-dinh-02.jpg', 'Tượng Tỳ Hưu để bàn chạm từ cẩm thạch xanh lý tự nhiên.', '2026-10-09 10:45:00'),
  (20, 1, 'JADE-20', 'Hoa tai phỉ thúy giọt sương Mai', 'Phỉ thúy Type A', 11500000, 9, 'Myanmar', 'Đã thẩm định (PNJ Lab)', '4.2 g', '6.5 - 7.0', '1.66', '3.34 g/cm³', 'nhan-phi-thuy-01.jpg', 'giay-kiem-dinh-01.jpg', 'Đôi hoa tai phỉ thúy dáng giọt nước trong trẻo.', '2026-10-10 08:00:00'),
  (21, 2, 'JADE-21', 'Vòng tay cẩm thạch الترا Tử La Lan', 'Cẩm thạch Jadeite tím', 28400000, 4, 'Myanmar', 'Đã thẩm định (DOJI Lab)', '45.8 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-10.jpg', 'giay-kiem-dinh-10.jpg', 'Vòng tay cẩm thạch sắc tím hoa cà dịu mắt, bản tròn.', '2026-10-10 11:20:00'),
  (22, 3, 'JADE-22', 'Chuỗi vòng tay ngọc bích 18 hạt Bích Thủy', 'Ngọc bích Nephrite', 8900000, 12, 'Nga', 'Đã thẩm định (PNJ Lab)', '28.0 g', '6.0 - 6.5', '1.61', '2.95 g/cm³', 'JADE-04.jpg', NULL, 'Chuỗi vòng đeo tay 18 hạt ngọc bích xanh đậm tự nhiên.', '2026-10-10 15:00:00'),
  (23, 4, 'JADE-23', 'Mặt dây bạch ngọc Quan Âm Tịnh Thủy', 'Bạch ngọc Nephrite', 22000000, 3, 'Tân Cương', 'Đã thẩm định (LiuLab)', '16.0 g', '6.0 - 6.5', '1.61', '2.95 g/cm³', 'JADE-07.jpg', 'giay-kiem-dinh-07.jpg', 'Mặt dây chạm Quan Âm trên nền bạch ngọc trắng mịn.', '2026-10-11 08:30:00'),
  (24, 5, 'JADE-24', 'Ấn chương bạch ngọc Văn Xương Đế Quân', 'Bạch ngọc Nephrite', 48000000, 0, 'Tân Cương', 'Đã thẩm định (SJC Lab)', '110.0 g', '6.0 - 6.5', '1.61', '2.96 g/cm³', 'JADE-11.jpg', 'giay-kiem-dinh-11.jpg', 'Ấn chương bạch ngọc dành cho người cầu học vấn, hiện đã hết hàng.', '2026-10-11 13:15:00'),
  (25, 1, 'JADE-25', 'Mặt dây phỉ thúy đồng điếu Cát Tường', 'Phỉ thúy Type A', 9800000, 11, 'Myanmar', 'Đã thẩm định (PNJ Lab)', '6.5 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-03.jpg', 'giay-kiem-dinh-03.jpg', 'Đồng điếu phỉ thúy biểu trưng bình an và may mắn.', '2026-10-12 08:00:00'),
  (26, 2, 'JADE-26', 'Nhẫn bản vuông cẩm thạch Sơn Thủy', 'Cẩm thạch Jadeite', 14200000, 5, 'Myanmar', 'Đã thẩm định (DOJI Lab)', '4.8 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-05.jpg', 'giay-kiem-dinh-05.jpg', 'Nhẫn nam bản vuông cẩm thạch vân xanh rêu cổ điển.', '2026-10-12 10:30:00'),
  (27, 3, 'JADE-27', 'Mặt dây ngọc bích Hồ Lô Phúc Lộc', 'Ngọc bích Nephrite', 8400000, 9, 'Tân Cương', 'Đã thẩm định (PNJ Lab)', '7.2 g', '6.0 - 6.5', '1.61', '2.95 g/cm³', 'JADE-11.jpg', 'giay-kiem-dinh-11.jpg', 'Mặt dây dáng hồ lô chạm từ ngọc bích Hòa Điền tự nhiên.', '2026-10-13 09:00:00'),
  (28, 4, 'JADE-28', 'Vòng tay bạch ngọc Thanh Liên', 'Bạch ngọc Nephrite', 34000000, 2, 'Tân Cương', 'Đã thẩm định (LiuLab)', '43.5 g', '6.0 - 6.5', '1.61', '2.95 g/cm³', 'JADE-06.jpg', 'giay-kiem-dinh-06.jpg', 'Vòng tay bạch ngọc nguyên khối, sắc trắng ngà thanh tao.', '2026-10-13 14:00:00'),
  (29, 5, 'JADE-29', 'Tượng Thiềm Thừ ngọc bích trấn trạch', 'Ngọc bích Nephrite', 39500000, 2, 'Canada', 'Đã thẩm định (SJC Lab)', '145.0 g', '6.0 - 6.5', '1.61', '2.96 g/cm³', 'JADE-02.jpg', 'giay-kiem-dinh-02.jpg', 'Tượng Thiềm Thừ 3 chân ngậm đồng xu bằng ngọc bích tự nhiên.', '2026-10-14 08:45:00'),
  (30, 1, 'JADE-30', 'Lắc tay hạt phỉ thúy Lục Châu', 'Phỉ thúy Type A', 16200000, 6, 'Myanmar', 'Đã thẩm định (PNJ Lab)', '19.5 g', '6.5 - 7.0', '1.66', '3.34 g/cm³', 'JADE-12.jpg', 'giay-kiem-dinh-12.jpg', 'Lắc tay phỉ thúy hạt tròn 8mm sắc xanh tươi tắn.', '2026-10-14 11:15:00'),
  (31, 2, 'JADE-31', 'Mặt dây cẩm thạch Lá Ngọc Cành Vàng', 'Cẩm thạch Jadeite', 12900000, 7, 'Myanmar', 'Đã thẩm định (LiuLab)', '5.9 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-03.jpg', 'giay-kiem-dinh-03.jpg', 'Mặt dây chiếc lá chạm mảnh tinh xảo từ ngọc jadeite.', '2026-10-15 08:20:00'),
  (32, 4, 'JADE-32', 'Chuỗi hạt hoàng ngọc Thu Vũ', 'Hoàng ngọc tự nhiên', 17600000, 4, 'Myanmar', 'Đã thẩm định (DOJI Lab)', '31.2 g', '6.5 - 7.0', '1.66', '3.33 g/cm³', 'JADE-08.jpg', 'giay-kiem-dinh-08.jpg', 'Chuỗi hạt hoàng ngọc sắc vàng thu ấm áp, 108 hạt.', '2026-10-15 10:50:00'),
  (33, 5, 'JADE-33', 'Tượng cá chép hóa rồng cẩm thạch', 'Cẩm thạch Jadeite', 46000000, 1, 'Myanmar', 'Đã thẩm định (SJC Lab)', '132.0 g', '6.5 - 7.0', '1.66', '3.34 g/cm³', 'JADE-10.jpg', 'giay-kiem-dinh-10.jpg', 'Tác phẩm điêu khắc cá chép vượt vũ môn trên nền ngọc tam sắc.', '2026-10-16 09:00:00'),
  (34, 1, 'JADE-34', 'Nhẫn phỉ thúy Đế Vương Lục bảo truyền', 'Phỉ thúy Type A - Kính chủng', 68000000, 1, 'Myanmar', 'Đã thẩm định (GIA Lab)', '3.45 g', '6.5 - 7.0', '1.66', '3.34 g/cm³', 'JADE-09.jpg', 'giay-kiem-dinh-09.jpg', 'Nhẫn phỉ thúy kính chủng sắc lục bảo cao cấp nhất của sàn JadeHub.', '2026-10-16 14:30:00');

-- 3. Tài khoản người dùng (mật khẩu của mọi tài khoản: matkhau123)
INSERT INTO nguoi_dung (id, email, ho_ten, mat_khau, vai_tro, ngay_tao) VALUES
  (1, 'admin@ued.vn',   'Quản trị viên JadeHub',    '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'quan_tri',   '2026-10-01 07:00:00'),
  (2, 'lan@ued.vn',     'Nguyễn Thị Lan',           '$2y$12$u.0T5HsnlVJWcPLCbRUsIO9EXccEweLCYOD7R46J.TJ9bH4ju5eGC', 'khach_hang', '2026-10-02 08:00:00'),
  (3, 'an@ued.vn',      'Trần Văn An',              '$2y$12$3ZoP8Zw46/1SUlmMu/unR.ZIvgTjCuo2NGWs2F9ttQBE.FmFo83bO', 'khach_hang', '2026-10-02 09:00:00'),
  (4, 'thanh@ued.vn',   'Trần Thị Cẩm Thanh',       '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'quan_tri',   '2026-10-03 08:00:00'),
  (5, 'hanh@ued.vn',    'Nguyễn Thị Bích Hạnh',     '$2y$12$u.0T5HsnlVJWcPLCbRUsIO9EXccEweLCYOD7R46J.TJ9bH4ju5eGC', 'khach_hang', '2026-10-03 08:10:00'),
  (6, 'hong@ued.vn',    'Nguyễn Nhật Ánh Hồng',     '$2y$12$3ZoP8Zw46/1SUlmMu/unR.ZIvgTjCuo2NGWs2F9ttQBE.FmFo83bO', 'khach_hang', '2026-10-03 08:20:00'),
  (7, 'trinh@ued.vn',   'Huỳnh Trần Phương Trinh',  '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'khach_hang', '2026-10-03 08:30:00'),
  (8, 'bouakeo@ued.vn', 'Ladisa Bouakeo',           '$2y$12$u.0T5HsnlVJWcPLCbRUsIO9EXccEweLCYOD7R46J.TJ9bH4ju5eGC', 'khach_hang', '2026-10-03 08:40:00');

-- 4. Đơn hàng mẫu (mỗi khách hàng thử lan@ued.vn và an@ued.vn có ít nhất 1 đơn hàng)
INSERT INTO don_hang (id, nguoi_dung_id, trang_thai, ghi_chu, ngay_dat) VALUES
  (1, 2, 'hoan_thanh',   'Giao giờ hành chính kèm hộp nhung và chứng thư gốc.', '2026-10-12 09:30:00'),
  (2, 3, 'dang_giao',    'Kiểm tra niêm phong chứng thư trước khi nhận.',       '2026-10-14 14:15:00'),
  (3, 2, 'cho_xac_nhan', 'Đặt thêm mặt dây tặng người thân.',                   '2026-10-16 10:20:00'),
  (4, 5, 'hoan_thanh',   'Nhận trực tiếp tại phòng thẩm định Đà Nẵng.',         '2026-10-17 16:00:00');

-- 5. Chi tiết đơn hàng (don_gia lưu giá tại thời điểm đặt)
INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia) VALUES
  (1, 1,  1, 18500000),
  (1, 3,  2,  9200000),
  (2, 2,  1, 45000000),
  (2, 7,  1,  7600000),
  (3, 5,  1, 15800000),
  (4, 8,  1, 14500000),
  (4, 11, 1, 10800000);

-- 6. Liên hệ / đăng ký thẩm định mẫu (chuyển từ storage/lien-he.jsonl của Bài tập 5 sang bảng)
INSERT INTO lien_he (id, ho_ten, email, sdt, loai_ngoc, so_luong, ngay_hen, ghi_chu, anh, thoi_gian) VALUES
  (1, 'Trần Thị Cẩm Thanh', 'thanh.khach@example.com', '0794782276', 'camthach', 1, '2026-11-05', 'Cần kiểm tra xem vòng cẩm thạch có tẩm keo polymer hay nhuộm màu không.', 'c4cee1d42a0eee3d4ed6efcacade488d.jpg', '2026-10-16 16:21:51'),
  (2, 'Nguyễn Văn Minh',    'minh.ngoc@example.com',   '0905123456', 'phithuy',  2, '2026-11-10', 'Đăng ký giám định 2 nhẫn phỉ thúy Type A mua tại Myanmar.',               '7b4eb3550b0d807122e10970d162516d.jpg', '2026-10-16 17:49:00'),
  (3, 'Lê Hoàng Nam',       'nam.le@example.com',      '0913456789', 'hoadien',  1, '2026-11-12', 'Kiểm tra tỷ trọng và phổ hồng ngoại cho mặt ngọc bích Hòa Điền.',         NULL,                                   '2026-10-16 18:15:00'),
  (4, 'Phạm Thu Hà',        'ha.pham@example.com',     '0987654321', 'phithuy',  1, '2026-11-15', 'Nhờ chuyên gia tư vấn định giá chuỗi hạt phỉ thúy gia truyền.',           'cbf26157fee965e8dcabea71b9ea4092.jpg', '2026-10-16 19:39:22');

-- =========================================================================
-- PHẦN B: DỮ LIỆU MẪU TRANG CÁ NHÂN CỦA 5 THÀNH VIÊN NHÓM 06
-- =========================================================================

INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id, anh_dai_dien) VALUES
  ('3120224133', 'Trần Thị Cẩm Thanh',      4, 'chan-dung-thanh.jpg'),
  ('3120224050', 'Nguyễn Thị Bích Hạnh',    5, 'chan-dung-hanh.jpg'),
  ('3120224063', 'Nguyễn Nhật Ánh Hồng',    6, 'chan-dung-hong.jpg'),
  ('3120124031', 'Huỳnh Trần Phương Trinh', 7, 'chan-dung-trinh.jpg'),
  ('3120124038', 'Ladisa Bouakeo',          8, 'chan-dung-bouakeo.jpg');

-- Bảng tv_diem_hoc_phan của Trần Thị Cẩm Thanh (3120224133)
INSERT INTO tv_diem_hoc_phan (mssv, ten_mon, so_tin, diem_a1, diem_a2, diem_a3, thoi_gian) VALUES
  ('3120224133', 'Thiết kế và Lập trình Web',      3, 9.50, 9.00, 9.50, '2026-10-10 08:00:00'),
  ('3120224133', 'Hệ quản trị Cơ sở dữ liệu',     3, 9.00, 8.50, 9.00, '2026-10-11 09:00:00'),
  ('3120224133', 'An toàn thông tin',              3, 8.50, 8.00, 8.50, '2026-10-12 10:00:00'),
  ('3120224133', 'Khai phá dữ liệu',               3, 9.00, 9.00, 8.50, '2026-10-13 14:00:00'),
  ('3120224133', 'Công nghệ phần mềm',             3, 8.50, 9.00, 9.00, '2026-10-14 15:00:00');

-- Bảng tv_luu_but và tv_luot_xem của Nguyễn Thị Bích Hạnh (3120224050)
INSERT INTO tv_luu_but (mssv, ten_khach, loi_nhan, thoi_gian) VALUES
  ('3120224050', 'Trần Thị Cẩm Thanh',      'Cảm ơn Hạnh đã phụ trách kiểm thử rất kỹ cho nhóm mình nhé!', '2026-10-12 08:30:00'),
  ('3120224050', 'Nguyễn Nhật Ánh Hồng',    'Giao diện trang cá nhân của Hạnh gọn gàng và dễ nhìn lắm.',   '2026-10-13 09:15:00'),
  ('3120224050', 'Huỳnh Trần Phương Trinh', 'Chúc cậu hoàn thành xuất sắc học phần Thiết kế Web!',         '2026-10-14 10:20:00'),
  ('3120224050', 'Ladisa Bouakeo',          'Bộ lọc tìm kiếm sản phẩm Hạnh làm chạy rất mượt.',            '2026-10-15 11:00:00'),
  ('3120224050', 'Nguyễn Thị Lan',          'Mình rất ấn tượng với dự án sàn ngọc quý JadeHub của nhóm.',   '2026-10-16 14:40:00'),
  ('3120224050', 'Trần Văn An',             'Chúc nhóm 06 đạt điểm cao ở Bài tập thực hành số 6!',         '2026-10-17 16:10:00');

INSERT INTO tv_luot_xem (mssv, ngay, so_luot) VALUES
  ('3120224050', '2026-10-23', 5),
  ('3120224050', '2026-10-24', 8),
  ('3120224050', '2026-10-25', 6),
  ('3120224050', '2026-10-26', 11),
  ('3120224050', '2026-10-27', 9),
  ('3120224050', '2026-10-28', 14),
  ('3120224050', '2026-10-29', 7);

-- Bảng tv_thoi_khoa_bieu của Nguyễn Nhật Ánh Hồng (3120224063)
INSERT INTO tv_thoi_khoa_bieu (mssv, thu, buoi, hoc_phan, phong) VALUES
  ('3120224063', 'hai', 'Sáng',  'Tự học & ôn tập SQL',                    'Thư viện'),
  ('3120224063', 'hai', 'Chiều', 'Hệ quản trị cơ sở dữ liệu',              'Phòng B1.204'),
  ('3120224063', 'ba',  'Sáng',  'Phân tích và thiết kế hệ thống',         'Phòng A5.302'),
  ('3120224063', 'ba',  'Chiều', 'Thực hành Cơ sở dữ liệu',                'Phòng máy 3'),
  ('3120224063', 'tu',  'Sáng',  'Học trên trường: Công nghệ phần mềm',    'Phòng A5.401'),
  ('3120224063', 'tu',  'Chiều', 'Làm bài nhóm dự án JadeHub',             'Phòng tự học'),
  ('3120224063', 'nam', 'Sáng',  'Lịch sử Đảng Cộng sản Việt Nam',         'Phòng A6.502'),
  ('3120224063', 'nam', 'Chiều', 'Kiểm thử chức năng Quản trị & Đơn hàng', 'Phòng máy 2'),
  ('3120224063', 'sau', 'Sáng',  'Lập trình mạng máy tính',                'Phòng B3.105'),
  ('3120224063', 'sau', 'Chiều', 'Sinh hoạt nhóm Khoa Toán - Tin',         'Hội trường B'),
  ('3120224063', 'bay', 'Sáng',  'Thiết kế và Lập trình Web',              'Phòng máy 4'),
  ('3120224063', 'bay', 'Chiều', 'Tổng hợp báo cáo Bài tập nhóm 6',        'Trực tuyến');

-- Bảng tv_ky_nang và tv_viec_can_lam của Huỳnh Trần Phương Trinh (3120124031)
INSERT INTO tv_ky_nang (mssv, nhom, ten, muc_do) VALUES
  ('3120124031', 'web',      'Lập trình Web động với PHP 8 & PDO',             90),
  ('3120124031', 'web',      'Thiết kế giao diện HTML5, CSS3 Responsive',      88),
  ('3120124031', 'csdl',     'Thiết kế CSDL quan hệ & tối ưu truy vấn MySQL',  85),
  ('3120124031', 'cong-cu',  'Quản lý mã nguồn nhóm với Git & GitHub',         85),
  ('3120124031', 'cong-cu',  'Kiểm thử REST API bằng curl & VS Code',          82),
  ('3120124031', 'he-thong', 'Quản trị máy chủ Linux & cấu hình Apache/XAMPP', 78);

INSERT INTO tv_viec_can_lam (mssv, noi_dung, da_xong, ngay_tao) VALUES
  ('3120124031', 'Thiết kế bảng nguoi_dung và luồng đăng ký tài khoản', 1, '2026-10-15 08:00:00'),
  ('3120124031', 'Cài đặt băm mật khẩu bcrypt và kiểm tra lỗi 1062',    1, '2026-10-16 09:30:00'),
  ('3120124031', 'Viết chức năng ghi nhớ đăng nhập bằng selector:validator', 1, '2026-10-17 14:00:00'),
  ('3120124031', 'Kiểm thử W3C HTML Validator cho trang cá nhân',       0, '2026-10-18 10:00:00');

-- Bảng tv_danh_gia của Ladisa Bouakeo (3120124038)
INSERT INTO tv_danh_gia (mssv, ten_khach, so_sao, nhan_xet, thoi_gian) VALUES
  ('3120124038', 'Trần Thị Cẩm Thanh',   5, 'Phần giao dịch đặt hàng Bouakeo xử lý rollback rất chuẩn!', '2026-10-14 09:00:00'),
  ('3120124038', 'Nguyễn Thị Bích Hạnh', 5, 'Trang hồ sơ rõ ràng, định hướng AI và lập trình game rất thú vị.', '2026-10-15 10:30:00'),
  ('3120124038', 'Nguyễn Nhật Ánh Hồng', 4, 'Biểu đồ phân bố đánh giá trực quan và dễ theo dõi.', '2026-10-16 15:20:00'),
  ('3120124038', 'Trần Văn An',          5, 'Chức năng đặt hàng và xem chi tiết đơn hàng hoạt động mượt mà.', '2026-10-17 11:45:00');
