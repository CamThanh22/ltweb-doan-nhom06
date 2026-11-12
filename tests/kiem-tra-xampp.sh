#!/usr/bin/env bash
set -uo pipefail
cd -- "$(dirname -- "$0")/.." || exit 1
php_bin="${PHP_BIN:-/c/xampp/php/php.exe}"
if [[ ! -x "$php_bin" ]]; then php_bin="$(command -v php || true)"; fi
if [[ -z "$php_bin" ]]; then echo "Không tìm thấy PHP. Đặt PHP_BIN trỏ tới php.exe."; exit 2; fi
export BASE="${BASE:-http://localhost/LTW_BTN6_Nhom06_code}"
export SQL_STATUS=403

failed=0
for file in tests/kiem-tra-moi-truong.php tests/tu-kiem-tra.php tests/kiem-tra-mo-rong.php tests/kiem-tra-csdl.php tests/kiem-thu.php tests/hoi-quy-day-du.php tests/huy-hoan-danh-gia.php; do
    printf '\n--- %s ---\n' "$file"
    "$php_bin" "$file" || failed=1
done
printf '\n--- Kiểm tra HTTP ---\n'
bash tests/bao-mat.sh || failed=1

if (( failed )); then
    printf '\nCó ca kiểm tra chưa đạt.\n'
else
    printf '\nTất cả ca trong bộ kiểm tra XAMPP đã đạt.\n'
fi
exit "$failed"
