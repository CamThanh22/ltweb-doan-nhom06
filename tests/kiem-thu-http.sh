#!/usr/bin/env bash
# tests/kiem-thu-http.sh - chạy các ca của Bảng 3 bằng curl và ghi kết quả THẬT vào kiemtra/ket-qua-curl.txt
# Dùng:  1) php -S localhost:8000     2) bash tests/kiem-thu-http.sh http://localhost:8000
# (Windows: chạy bằng Git Bash; ca 12 phải làm tay vì cần đổi MOI_TRUONG, script chỉ nhắc cách làm.)
BASE="${1:-http://localhost:8000}"
GOC="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$GOC/kiemtra/ket-qua-curl.txt"
TMP="$(mktemp -d)"; JAR="$TMP/cookie.txt"
NGAY="$(date -d '+3 days' +%F 2>/dev/null || date -v+3d +%F)"
: > "$OUT"
PASS=0; FAIL=0

ghi() { echo "$*" | tee -a "$OUT"; }
kq()  { # kq "ten ca" lenh_kiem_tra...
  local ten="$1"; shift
  if "$@"; then PASS=$((PASS+1)); ghi "PASS  $ten"; else FAIL=$((FAIL+1)); ghi "FAIL  $ten"; fi
}
co()    { grep -Fq -- "$2" <<<"$1"; }           # co "$noi_dung" "chuoi"
khong() { ! grep -Fq -- "$2" <<<"$1"; }
dongLienHe() { [ -f "$GOC/storage/lien-he.jsonl" ] && wc -l < "$GOC/storage/lien-he.jsonl" | tr -d ' ' || echo 0; }
soUploads()  { ls "$GOC/uploads" 2>/dev/null | grep -vc -E '^\.(gitkeep|htaccess)$'; }
gui() { curl -s "$@"; }

ghi "== Kiểm thử máy chủ $(date '+%F %T') - $BASE =="

# --- Ca 1: để trống mọi ô (curl bỏ qua kiểm tra phía client)
truoc=$(dongLienHe)
r=$(gui -i -d "hoten=&email=&sdt=&soluong=&ngayhen=" "$BASE/lien-he.php")
kq "Ca1 trả 200 và hiện lại form"            co "$r" "HTTP/1.1 200"
kq "Ca1 báo lỗi dưới ô họ tên"                co "$r" "Vui lòng nhập họ và tên."
kq "Ca1 báo lỗi email, sdt, ngày, cam kết"    bash -c 'grep -Fq "Vui lòng nhập địa chỉ email." <<<"$0" && grep -Fq "Vui lòng nhập số điện thoại." <<<"$0" && grep -Fq "Bạn cần xác nhận cam kết" <<<"$0"' "$r"
kq "Ca1 không lưu gì"                         [ "$(dongLienHe)" = "$truoc" ]

# --- Ca 2: email và điện thoại sai, ô khác hợp lệ
r=$(gui -d "hoten=Nguyen Van Lan" -d "email=lan@ued" -d "sdt=abc" -d "loaingoc=phithuy" -d "soluong=2" -d "ngayhen=$NGAY" -d "camket=on" -d "ghichu=ghi chu thu" "$BASE/lien-he.php")
kq "Ca2 báo lỗi email"                        co "$r" "Email không hợp lệ"
kq "Ca2 báo lỗi điện thoại"                   co "$r" "Số điện thoại phải gồm 10 chữ số"
kq "Ca2 không báo lỗi họ tên"                 khong "$r" "Họ và tên cần từ"
kq "Ca2 giữ nguyên giá trị đã nhập"           bash -c 'grep -Fq "value=\"Nguyen Van Lan\"" <<<"$0" && grep -Fq "ghi chu thu</textarea>" <<<"$0"' "$r"

# --- Ca 3: tệp văn bản chứa <?php phpinfo(); đổi đuôi .jpg
printf '<?php phpinfo(); ?>' > "$TMP/gia-mao.jpg"
u0=$(soUploads); l0=$(dongLienHe)
r=$(gui -F "hoten=Nguyen Van Lan" -F "email=lan@example.com" -F "sdt=0912345678" -F "loaingoc=phithuy" -F "soluong=1" -F "ngayhen=$NGAY" -F "camket=on" -F "anhngoc=@$TMP/gia-mao.jpg;type=image/jpeg" "$BASE/lien-he.php")
kq "Ca3 từ chối, báo chỉ nhận JPG/PNG/WebP"   co "$r" "Chỉ nhận ảnh JPG, PNG hoặc WebP."
kq "Ca3 uploads/ không có tệp mới"            [ "$(soUploads)" = "$u0" ]
kq "Ca3 không lưu liên hệ"                    [ "$(dongLienHe)" = "$l0" ]

# --- Ca 4: ảnh lớn hơn 2 MB (đầu tệp là chữ ký JPEG)
{ printf '\xff\xd8\xff\xe0'; head -c 3200000 /dev/zero; } > "$TMP/lon.jpg"
r=$(gui -F "hoten=Nguyen Van Lan" -F "email=lan@example.com" -F "sdt=0912345678" -F "loaingoc=phithuy" -F "soluong=1" -F "ngayhen=$NGAY" -F "camket=on" -F "anhngoc=@$TMP/lon.jpg;type=image/jpeg" "$BASE/lien-he.php")
kq "Ca4 báo lỗi kích thước"                   co "$r" "2 MB"
kq "Ca4 không có Warning/Notice trên trang"   bash -c '! grep -Eq "<b>(Warning|Notice|Deprecated|Fatal error)" <<<"$0"' "$r"
kq "Ca4 các ô khác giữ nguyên giá trị"        co "$r" 'value="Nguyen Van Lan"'

# --- Ca 5: liên hệ hợp lệ + PRG
l0=$(dongLienHe)
r=$(gui -i -c "$JAR" -b "$JAR" -F "hoten=Nguyen Van Lan" -F "email=lan@example.com" -F "sdt=0912345678" -F "loaingoc=phithuy" -F "soluong=1" -F "ngayhen=$NGAY" -F "camket=on" -F "ghichu=lien he hop le" "$BASE/lien-he.php")
kq "Ca5 trả 302 và Location lien-he.php"      bash -c 'grep -q "HTTP/1.1 302" <<<"$0" && grep -qi "^Location: lien-he.php" <<<"$0"' "$r"
kq "Ca5 thêm đúng một dòng vào lien-he.jsonl" [ "$(dongLienHe)" = "$((l0+1))" ]
r1=$(gui -c "$JAR" -b "$JAR" "$BASE/lien-he.php"); r2=$(gui -c "$JAR" -b "$JAR" "$BASE/lien-he.php")
kq "Ca5 thông báo 'đã gửi' hiện lần đầu"      co "$r1" "Đã gửi liên hệ"
kq "Ca5 F5 (GET lại) không hiện thông báo nữa" khong "$r2" "Đã gửi liên hệ"
kq "Ca5 F5 không thêm dòng"                   [ "$(dongLienHe)" = "$((l0+1))" ]

# --- Ca 6: chuỗi XSS
XSS='"><script>alert(1)</script>'
r=$(gui -G --data-urlencode "q=$XSS" "$BASE/danh-sach.php")
kq "Ca6 danh-sach.php: không có <script>alert(1) thô" khong "$r" "<script>alert(1)"
kq "Ca6 danh-sach.php: chuỗi hiện dạng &lt;script&gt;"  co "$r" "&lt;script&gt;alert(1)&lt;/script&gt;"
gui -o /dev/null -F "hoten=Nguyen Van Xss" -F "email=xss@example.com" -F "sdt=0912345678" -F "loaingoc=phithuy" -F "soluong=1" -F "ngayhen=$NGAY" -F "camket=on" -F "ghichu=$XSS" "$BASE/lien-he.php"
JAR2="$TMP/admin.txt"
gui -o /dev/null -c "$JAR2" -b "$JAR2" -d "ten=admin" -d "matkhau=Jade@2026" "$BASE/dang-nhap.php"
r=$(gui -c "$JAR2" -b "$JAR2" "$BASE/quan-tri.php")
kq "Ca6 quan-tri.php: không có <script>alert(1) thô"   khong "$r" "<script>alert(1)"
kq "Ca6 quan-tri.php: nội dung liên hệ được thoát HTML" co "$r" "&lt;script&gt;alert(1)&lt;/script&gt;"

# --- Ca 7: id sai trên chi-tiet.php
for u in "chi-tiet.php?id=abc" "chi-tiet.php?id=999999" "chi-tiet.php"; do
  ma=$(gui -o "$TMP/b.html" -w '%{http_code}' "$BASE/$u")
  kq "Ca7 $u -> HTTP 404"                     [ "$ma" = "404" ]
  kq "Ca7 $u -> không có Warning/Notice"      bash -c '! grep -Eq "<b>(Warning|Notice|Deprecated|Fatal error)" "$0"' "$TMP/b.html"
done
kq "Ca7 chi-tiet.php?id=1 -> HTTP 200"        [ "$(gui -o /dev/null -w '%{http_code}' "$BASE/chi-tiet.php?id=1")" = "200" ]

# --- Ca 8: thêm vào giỏ với dữ liệu sai (giỏ dùng session -> giữ cookie)
JG="$TMP/gio.txt"
for sl in 0 -3 1000 abc; do
  r=$(gui -c "$JG" -b "$JG" -d "hanhdong=them" -d "id=1" -d "sl=$sl" "$BASE/gio-hang.php")
  kq "Ca8 sl=$sl bị từ chối, báo lỗi"          co "$r" "Số lượng phải là số nguyên"
done
r=$(gui -c "$JG" -b "$JG" -d "hanhdong=them" -d "id=999999" -d "sl=1" "$BASE/gio-hang.php")
kq "Ca8 id không tồn tại bị từ chối"          co "$r" "Sản phẩm không tồn tại"
r=$(gui -c "$JG" -b "$JG" "$BASE/gio-hang.php")
kq "Ca8 giỏ vẫn trống"                        co "$r" "Giỏ hàng đang trống"

# --- Ca 9: thêm 2 mục, đổi số lượng, xóa một mục (giá: JADE-01 18.500.000; JADE-02 45.000.000)
gui -o /dev/null -c "$JG" -b "$JG" -d "hanhdong=them" -d "id=1" -d "sl=2" "$BASE/gio-hang.php"
gui -o /dev/null -c "$JG" -b "$JG" -d "hanhdong=them" -d "id=2" -d "sl=1" "$BASE/gio-hang.php"
r=$(gui -c "$JG" -b "$JG" "$BASE/gio-hang.php")
kq "Ca9 tổng tiền 82.000.000 VNĐ, 3 món"      bash -c 'grep -Fq "82.000.000 VNĐ" <<<"$0" && grep -Fq "<strong>3</strong>" <<<"$0"' "$r"
gui -o /dev/null -c "$JG" -b "$JG" -d "hanhdong=capnhat" -d "id=1" -d "sl=1" "$BASE/gio-hang.php"
gui -o /dev/null -c "$JG" -b "$JG" -d "hanhdong=xoa" -d "id=2" "$BASE/gio-hang.php"
r=$(gui -c "$JG" -b "$JG" "$BASE/gio-hang.php"); r2=$(gui -c "$JG" -b "$JG" "$BASE/index.php")
kq "Ca9 sau đổi/xóa tổng 18.500.000 VNĐ"      co "$r" "Tổng tiền: <strong>18.500.000 VNĐ</strong>"
kq "Ca9 header trang khác báo 1 món"          co "$r2" "<span data-dem-gio>1</span>"

# --- Ca 10: đăng nhập / quản trị
JD="$TMP/dn.txt"
r=$(gui -i "$BASE/quan-tri.php")
kq "Ca10 chưa đăng nhập -> 302 về dang-nhap.php" bash -c 'grep -q "HTTP/1.1 302" <<<"$0" && grep -qi "^Location: dang-nhap.php" <<<"$0"' "$r"
r=$(gui -c "$JD" -b "$JD" -d "ten=admin" -d "matkhau=sai-mat-khau" "$BASE/dang-nhap.php")
kq "Ca10 sai mật khẩu -> báo lỗi chung"       co "$r" "Sai tên đăng nhập hoặc mật khẩu."
r=$(gui -i -c "$JD" -b "$JD" -d "ten=admin" -d "matkhau=Jade@2026" "$BASE/dang-nhap.php")
kq "Ca10 đúng -> 302 tới quan-tri.php"        bash -c 'grep -q "HTTP/1.1 302" <<<"$0" && grep -qi "^Location: quan-tri.php" <<<"$0"' "$r"
ma=$(gui -o /dev/null -w '%{http_code}' -c "$JD" -b "$JD" "$BASE/quan-tri.php")
kq "Ca10 đã đăng nhập vào được quan-tri.php"  [ "$ma" = "200" ]
gui -o /dev/null -c "$JD" -b "$JD" "$BASE/dang-xuat.php"
r=$(gui -i -c "$JD" -b "$JD" "$BASE/quan-tri.php")
kq "Ca10 đăng xuất xong không vào lại được"   co "$r" "Location: dang-nhap.php"

# --- Ca 11: sửa cookie da_xem thành giá trị lạ
for v in "abc" "-1" "999999" "abc,-1,999999"; do
  r=$(gui -b "da_xem=$v" "$BASE/index.php")
  kq "Ca11 da_xem=$v -> trang chủ bình thường" bash -c 'grep -Fq "JadeHub" <<<"$0" && ! grep -Eq "<b>(Warning|Notice|Deprecated|Fatal error)" <<<"$0"' "$r"
done

# --- Ca 12: làm tay
ghi "Ca12 (LÀM TAY): đặt MOI_TRUONG = 'prod' trong inc/config.php, đổi tên data/san-pham.json, mở danh-sach.php,"
ghi "      kiểm tra trang 500 thân thiện + chi tiết lỗi trong logs/php-error.log, rồi khôi phục như cũ."

# --- Nhật ký lỗi PHP
if [ -f "$GOC/logs/php-error.log" ]; then
  n=$(grep -Ec "PHP (Warning|Notice|Deprecated|Fatal error|Parse error)" "$GOC/logs/php-error.log")
  kq "logs/php-error.log không có Warning/Notice/Deprecated/Fatal" [ "$n" = "0" ]
else
  kq "logs/php-error.log chưa có (không có lỗi nào được ghi)" true
fi

ghi ""; ghi "Tổng: $PASS đạt, $FAIL hỏng. Kết quả lưu ở $OUT"
rm -rf "$TMP"; [ "$FAIL" = "0" ]
