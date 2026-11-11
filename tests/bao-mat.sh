#!/usr/bin/env bash
set -euo pipefail
BASE="${BASE:-http://localhost:8000}"
echo 'GET logout must be 405:'
curl -s -o /dev/null -w '%{http_code}\n' "$BASE/dang-xuat.php"
echo 'POST without CSRF must be 403:'
curl -s -o /dev/null -w '%{http_code}\n' -X POST "$BASE/dang-nhap.php" -d 'email=test@example.com&mat_khau=invalid'
echo 'Hidden .env must not be readable:'
curl -s -o /dev/null -w '%{http_code}\n' "$BASE/.env"
echo 'Internal SQL must not be readable:'
curl -s -o /dev/null -w '%{http_code}\n' "$BASE/database/01-cai-moi.sql"
echo 'Invalid JSON API must be rejected with valid CSRF/session (see api.http):'
