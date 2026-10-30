/*
 * js/tim-kiem.js - Tìm kiếm tức thời (live search) ở danh-sach.php bằng fetch + debounce 300ms (slide 51).
 * Mọi dữ liệu từ API được gắn vào DOM bằng createElement + textContent (không bao giờ dùng innerHTML).
 * Khi tắt JavaScript, biểu mẫu GET của danh-sach.php vẫn hoạt động bình thường.
 * Cách thử: gõ nhanh từ khóa "nhan" vào ô tìm kiếm ở danh-sach.php -> tab Network chỉ gửi 1 request.
 */

const o = document.querySelector('#o-tim');
const goiY = document.querySelector('#goi-y');
let hen;

const vnd = (so) => Number(so).toLocaleString('vi-VN') + ' VNĐ';

function taoGoiY(sp) {
    const a = document.createElement('a');
    a.href = 'chi-tiet.php?id=' + encodeURIComponent(sp.id);
    a.textContent = `${sp.ten} (${sp.ma})`;

    const li = document.createElement('li');
    li.append(a, ' — ' + vnd(sp.gia) + (sp.con_hang ? '' : ' [Hết hàng]'));
    return li;
}

async function timNgay() {
    if (!o || !goiY) {
        return;
    }
    const q = o.value.trim();
    if (q.length < 2) {
        goiY.replaceChildren();
        return;
    }
    try {
        const url = 'api/san-pham.php?q=' + encodeURIComponent(q);
        const res = await fetch(url);
        if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
        }
        const { du_lieu } = await res.json();
        if (!Array.isArray(du_lieu) || du_lieu.length === 0) {
            const li = document.createElement('li');
            li.textContent = 'Không tìm thấy sản phẩm phù hợp.';
            goiY.replaceChildren(li);
            return;
        }
        goiY.replaceChildren(...du_lieu.map(taoGoiY));
    } catch (loi) {
        const li = document.createElement('li');
        li.textContent = 'Không tải được gợi ý tìm kiếm.';
        goiY.replaceChildren(li);
        console.error(loi);
    }
}

if (o && goiY) {
    o.addEventListener('input', () => {
        clearTimeout(hen);
        hen = setTimeout(timNgay, 300);
    });
}
