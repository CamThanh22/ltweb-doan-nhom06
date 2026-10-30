#!/usr/bin/env bash
# tests/kiem-thu-http.sh - Kiểm thử tự động các ca của Bảng 3 (Chương 6 - PHP & MySQL) bằng curl
# Dùng:  1) php -S localhost:8000     2) bash tests/kiem-thu-http.sh http://localhost:8000
BASE="${1:-http://localhost:8000}"
GOC="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$GOC/kiemtra/ket-qua-curl.txt"
TMP="$(mktemp -d)"
: > "$OUT"
PASS=0; FAIL=0

ghi() { echo "$*" | tee -a "$OUT"; }
kq()  {
  local ten="$1"; shift
  if "$@"; then PASS=$((PASS+1)); ghi "PASS  $ten"; else FAIL=$((FAIL+1)); ghi "FAIL  $ten"; fi
}
co()    { grep -Fq -- "$2" <<<"$1"; }
khong() { ! grep -Fq -- "$2" <<<"$1"; }
gui()   { curl -s "$@"; }

ghi "== Kiểm thử máy chủ Chương 6 $(date '+%F %T') - $BASE =="

# Ca 1: Tìm có dấu, không dấu, chữ hoa, Levi's và 50%
r1=$(gui -G --data-urlencode "q=nhẫn ngọc" "$BASE/danh-sach.php")
r2=$(gui -G --data-urlencode "q=NHẪN NGỌC" "$BASE/danh-sach.php")
r3=$(gui -G --data-urlencode "q=nhan ngoc" "$BASE/danh-sach.php")
kq "Ca1: Tìm có dấu / HOA / không dấu không lỗi SQL" co "$r1" "Tìm thấy"
r4=$(gui -G --data-urlencode "q=Levi's" "$BASE/danh-sach.php")
kq "Ca1: Tìm \"Levi's\" thấy đúng mục có dấu nháy, giữ chữ đã gõ" bash -c 'grep -Fq "Levi&#039;s Emerald 50%" <<<"$0" && grep -Fq "value=\"Levi&#039;s\"" <<<"$0"' "$r4"
r5=$(gui -G --data-urlencode "q=50%" "$BASE/danh-sach.php")
kq "Ca1: Tìm \"50%\" chỉ khớp mục có chuỗi 50%" co "$r5" "Tìm thấy <strong>1</strong> sản phẩm"

# Ca 2: Tham số lạ trên danh-sach.php
for u in "danh-sach.php?sx=xyz&trang=abc" "danh-sach.php?trang=0" "danh-sach.php?trang=-5" "danh-sach.php?trang=9999" "danh-sach.php?dm=999" "danh-sach.php?gia=abc"; do
  r=$(gui "$BASE/$u")
  kq "Ca2: $u không Warning, không lỗi SQL" bash -c '! grep -Eq "(Warning|Notice|Deprecated|Fatal error|SQLSTATE)" <<<"$0"' "$r"
done
r_dm999=$(gui "$BASE/danh-sach.php?dm=999")
kq "Ca2: ?dm=999 báo không có kết quả" co "$r_dm999" "Không có kết quả phù hợp"

# Ca 3: Lọc danh mục, sắp xếp giá tăng, sang trang 2
r_p2=$(gui "$BASE/danh-sach.php?dm=1&sx=gia-tang&trang=2")
kq "Ca3: Trang 2 giữ đủ tham số lọc trên liên kết phân trang" co "$r_p2" "dm=1&amp;sx=gia-tang&amp;trang=1"

# Ca 4: Quản trị thêm sản phẩm với dữ liệu sai
JAR_ADMIN="$TMP/admin.txt"
gui -o /dev/null -c "$JAR_ADMIN" -b "$JAR_ADMIN" -d "email=admin@ued.vn" -d "mat_khau=matkhau123" "$BASE/dang-nhap.php"
r_add=$(gui -c "$JAR_ADMIN" -b "$JAR_ADMIN" -d "ma=JADE-99" -d "ten=" -d "gia=0" -d "ton_kho=-1" -d "danh_muc_id=999" "$BASE/them-san-pham.php")
kq "Ca4: Thêm sản phẩm sai báo lỗi cạnh từng ô, không 500" bash -c 'grep -Fq "Tên sản phẩm phải dài" <<<"$0" && grep -Fq "Giá niêm yết phải là số nguyên từ 1.000" <<<"$0" && grep -Fq "Số lượng tồn kho phải là số nguyên từ 0" <<<"$0" && grep -Fq "Vui lòng chọn một danh mục có sẵn" <<<"$0"' "$r_add"

# Ca 5: Xóa mục đã có trong đơn hàng (id=1) và GET xoa-san-pham.php
ma_get=$(gui -o /dev/null -w '%{http_code}' -c "$JAR_ADMIN" -b "$JAR_ADMIN" "$BASE/xoa-san-pham.php?id=1")
kq "Ca5: GET xoa-san-pham.php?id=1 nhận mã 405" [ "$ma_get" = "405" ]
gui -o /dev/null -c "$JAR_ADMIN" -b "$JAR_ADMIN" -d "id=1" "$BASE/xoa-san-pham.php"
r_qt=$(gui -c "$JAR_ADMIN" -b "$JAR_ADMIN" "$BASE/quan-tri.php")
kq "Ca5: Xóa sản phẩm đã có trong đơn (id=1) bắt lỗi 1451 và báo không xóa được" co "$r_qt" "đã có trong đơn hàng nên không xóa được"

# Ca 6: Đăng ký tài khoản
r_dk1=$(gui -d "ho_ten=Nguyen Van Thu" -d "email=abc" -d "mat_khau=1234567" "$BASE/dang-ky.php")
kq "Ca6: Đăng ký email 'abc', mật khẩu 7 ký tự báo đúng lỗi" bash -c 'grep -Fq "Email không hợp lệ" <<<"$0" && grep -Fq "Mật khẩu tối thiểu 8 ký tự" <<<"$0"' "$r_dk1"
r_dk2=$(gui -d "ho_ten=Nguyen Thi Lan" -d "email=LAN@UED.VN" -d "mat_khau=matkhau123" "$BASE/dang-ky.php")
kq "Ca6: Đăng ký email trùng viết HOA báo đã được đăng ký (1062)" co "$r_dk2" "Email này đã được đăng ký"

# Ca 7: Đăng nhập sai mật khẩu / email chưa đăng ký / đăng nhập đúng đổi PHPSESSID
r_dn1=$(gui -d "email=lan@ued.vn" -d "mat_khau=sai123456" "$BASE/dang-nhap.php")
r_dn2=$(gui -d "email=chua-co@ued.vn" -d "mat_khau=matkhau123" "$BASE/dang-nhap.php")
kq "Ca7: Hai lần đăng nhập sai nhận cùng thông báo chung" bash -c 'grep -Fq "Sai email hoặc mật khẩu." <<<"$0" && grep -Fq "Sai email hoặc mật khẩu." <<<"$1"' "$r_dn1" "$r_dn2"

# Ca 8: Phân quyền vai trò và chủ sở hữu
ma_guest_qt=$(gui -o /dev/null -w '%{http_code}' "$BASE/quan-tri.php")
ma_guest_dh=$(gui -o /dev/null -w '%{http_code}' "$BASE/don-hang.php")
kq "Ca8: Chưa đăng nhập mở quan-tri.php, don-hang.php -> 302 về dang-nhap.php" [ "$ma_guest_qt" = "302" ] && [ "$ma_guest_dh" = "302" ]

JAR_LAN="$TMP/lan.txt"
gui -o /dev/null -c "$JAR_LAN" -b "$JAR_LAN" -d "email=lan@ued.vn" -d "mat_khau=matkhau123" "$BASE/dang-nhap.php"
ma_lan_qt=$(gui -o /dev/null -w '%{http_code}' -c "$JAR_LAN" -b "$JAR_LAN" "$BASE/quan-tri.php")
ma_lan_sua=$(gui -o /dev/null -w '%{http_code}' -c "$JAR_LAN" -b "$JAR_LAN" "$BASE/sua-san-pham.php?id=1")
ma_lan_don_an=$(gui -o /dev/null -w '%{http_code}' -c "$JAR_LAN" -b "$JAR_LAN" "$BASE/don-hang.php?id=2")
kq "Ca8: Tài khoản khách mở quan-tri.php và sua-san-pham.php?id=1 -> 403" [ "$ma_lan_qt" = "403" ] && [ "$ma_lan_sua" = "403" ]
kq "Ca8: Khách Lan mở đơn #2 của khách An -> 404" [ "$ma_lan_don_an" = "404" ]

# Ca 11: REST API api/san-pham.php
r_api1=$(gui -i "$BASE/api/san-pham.php?q=nhan")
r_api2=$(gui -i "$BASE/api/san-pham.php?id=999999")
r_api3=$(gui -i "$BASE/api/san-pham.php?id=abc")
kq "Ca11: api/san-pham.php?q=nhan trả 200 application/json" bash -c 'grep -q "HTTP/1.1 200" <<<"$0" && grep -qi "Content-Type: application/json" <<<"$0"' "$r_api1"
kq "Ca11: api/san-pham.php?id=999999 trả 404 JSON" co "$r_api2" "HTTP/1.1 404"
kq "Ca11: api/san-pham.php?id=abc trả 400 JSON" co "$r_api3" "HTTP/1.1 400"

# Ca 12: Các nhánh lỗi của api/gio-hang.php
JAR_API="$TMP/api.txt"
m1=$(gui -o /dev/null -w '%{http_code}' -c "$JAR_API" -b "$JAR_API" -H "Content-Type: application/json" -d '{id: 1}' "$BASE/api/gio-hang.php")
m2=$(gui -o /dev/null -w '%{http_code}' -c "$JAR_API" -b "$JAR_API" -H "Content-Type: application/json" -d '{"id": 1, "sl": -3}' "$BASE/api/gio-hang.php")
m3=$(gui -o /dev/null -w '%{http_code}' -c "$JAR_API" -b "$JAR_API" -H "Content-Type: application/json" -d '{"id": 999999, "sl": 1}' "$BASE/api/gio-hang.php")
m4=$(gui -o /dev/null -w '%{http_code}' -c "$JAR_API" -b "$JAR_API" -H "Content-Type: application/json" -d '{"id": 1, "sl": 50}' "$BASE/api/gio-hang.php")
r_del=$(gui -i -X DELETE "$BASE/api/gio-hang.php")
kq "Ca12: Thân không phải JSON -> 400" [ "$m1" = "400" ]
kq "Ca12: sl = -3 -> 422" [ "$m2" = "422" ]
kq "Ca12: id không có -> 404" [ "$m3" = "404" ]
kq "Ca12: vượt tồn kho -> 422" [ "$m4" = "422" ]
kq "Ca12: DELETE -> 405 kèm Allow: GET, POST" bash -c 'grep -q "HTTP/1.1 405" <<<"$0" && grep -qi "^Allow: GET, POST" <<<"$0"' "$r_del"

ghi ""; ghi "Tổng: $PASS đạt, $FAIL hỏng. Kết quả lưu ở $OUT"
rm -rf "$TMP"; [ "$FAIL" = "0" ]
