#!/usr/bin/env bash
set -euo pipefail

base="${BASE:-http://localhost:8000}"
if [[ ! "$base" =~ ^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?(/[^?#]*)?$ ]]; then
    printf 'BASE phải là URL localhost của bản JadeHub dùng để kiểm thử.\n' >&2
    exit 2
fi
base="${base%/}"
temp_dir=$(mktemp -d)
trap 'rm -rf "$temp_dir"' EXIT
fail=0

check_status() {
    local label="$1" expected="$2" path="$3" actual
    shift 3
    if actual=$(curl --silent --show-error --max-time 10 -o "$temp_dir/body" \
        -D "$temp_dir/headers" -w '%{http_code}' "$@" "$base/$path") &&
        [[ "$actual" == "$expected" ]]; then
        printf 'PASS %s (%s)\n' "$label" "$actual"
    else
        printf 'FAIL %s (cần %s, nhận %s)\n' "$label" "$expected" "${actual:-lỗi kết nối}"
        fail=1
    fi
}

check_status 'Trang đăng nhập' 200 dang-nhap.php -c "$temp_dir/cookies"
token=$(sed -n 's/.*name="csrf-token" content="\([a-f0-9]*\)".*/\1/p' "$temp_dir/body")
for header in 'Content-Security-Policy:' 'X-Content-Type-Options: nosniff' 'Referrer-Policy:'; do
    if grep -qi "^$header" "$temp_dir/headers"; then
        printf 'PASS %s\n' "$header"
    else
        printf 'FAIL thiếu %s\n' "$header"
        fail=1
    fi
done
if grep -qiE '^Set-Cookie: PHPSESSID=.*HttpOnly.*SameSite=Lax' "$temp_dir/headers"; then
    printf 'PASS cờ cookie phiên\n'
else
    printf 'FAIL cờ cookie phiên\n'
    fail=1
fi

check_status 'Xóa sản phẩm chỉ nhận POST' 405 xoa-san-pham.php
check_status 'Đăng xuất chỉ nhận POST' 405 dang-xuat.php
check_status 'Đọc giỏ hàng' 200 api/gio-hang.php
check_status 'Khách được chuyển tới đăng nhập từ trang quản trị' 302 quan-tri.php
check_status 'API từ chối POST thiếu CSRF' 403 api/gio-hang.php -d '{}'
check_status 'Tệp SQL nội bộ được bảo vệ' "${SQL_STATUS:-404}" database/cai-moi-cau-truc-va-du-lieu-mau.sql
check_status 'Tệp cấu hình được bảo vệ' 404 .env
check_status 'Phương thức API không hỗ trợ' 405 api/gio-hang.php -X DELETE
if [[ "$token" =~ ^[a-f0-9]{64}$ ]]; then
    check_status 'JSON không hợp lệ' 400 api/gio-hang.php -b "$temp_dir/cookies" \
        -H "X-CSRF-Token: $token" -H 'Content-Type: application/json' -d '{'
    check_status 'Số lượng ngoài giới hạn' 422 api/gio-hang.php -b "$temp_dir/cookies" \
        -H "X-CSRF-Token: $token" -H 'Content-Type: application/json' -d '{"id":1,"sl":0}'
else
    printf 'FAIL không lấy được mã CSRF từ trang đăng nhập\n'
    fail=1
fi

check_status 'Git nội bộ' 404 .git/config
check_status 'Mã PHP nội bộ' 404 inc/db.php
check_status 'Composer autoloader nội bộ' 404 vendor/autoload.php
check_status 'Nguồn trang nội bộ' 404 pages/cua-hang/index.php
check_status 'Composer manifest' "${SQL_STATUS:-404}" composer.json
check_status 'Log bảo mật' "${SQL_STATUS:-404}" logs/bao-mat.log
check_status 'CSS công khai' 200 css/style.css
check_status 'Ảnh công khai' 200 images/logo-jadehub.jpg
if [[ "${SQL_STATUS:-404}" == 403 ]]; then
    check_status 'Không liệt kê uploads' 403 uploads/
fi

if [[ "${SQL_STATUS:-404}" == 403 ]]; then
    root_dir="$(cd -- "$(dirname -- "$0")/.." && pwd)"
    probe="kiem-tra-$RANDOM-$RANDOM.php"
    printf '<?php echo "MUST_NOT_EXECUTE";' > "$root_dir/uploads/$probe"
    check_status 'Uploads không thực thi PHP' 403 "uploads/$probe"
    rm -f -- "$root_dir/uploads/$probe"
fi
exit "$fail"


