// Lấy dữ liệu thời tiết Đà Nẵng từ REST API Open-Meteo.
// Hiển thị nhiệt độ và độ ẩm hiện tại.
// Có trạng thái đang tải và xử lý lỗi khi API không hoạt động.
// Nội dung API được đưa vào trang bằng textContent/createElement.

import { taiJSON } from './api.js';

const box = document.querySelector('#du-lieu-api');

async function hienThiThoiTiet() {
    if (!box) {
        return;
    }

    try {
        // Trạng thái đang tải
        box.textContent = 'Đang tải thời tiết Đà Nẵng…';

        const params = new URLSearchParams({
            latitude: '16.05',
            longitude: '108.20',
            current:
                'temperature_2m,relative_humidity_2m,weather_code',
            timezone: 'Asia/Ho_Chi_Minh'
        });

        const url =
            `https://api.open-meteo.com/v1/forecast?${params}`;

        const duLieu = await taiJSON(url);

        // Xóa trạng thái đang tải
        box.replaceChildren();

        const tieuDe = document.createElement('h2');
        tieuDe.textContent =
            'Thời tiết Đà Nẵng – hỗ trợ bảo quản ngọc';

        const thoiTiet = document.createElement('p');
        thoiTiet.textContent =
            `Nhiệt độ hiện tại: ` +
            `${duLieu.current.temperature_2m} °C · ` +
            `Độ ẩm: ${duLieu.current.relative_humidity_2m}%`;

        const ghiChu = document.createElement('p');
        ghiChu.textContent =
            'Thông tin tham khảo khi bảo quản và ' +
            'vận chuyển sản phẩm ngọc quý.';

        box.append(tieuDe, thoiTiet, ghiChu);
    } catch (error) {
        console.error(
            'Không thể tải dữ liệu thời tiết:',
            error
        );

        box.replaceChildren();

        const thongBao = document.createElement('p');
        thongBao.textContent =
            'Không tải được dữ liệu thời tiết. ' +
            'Nội dung chính của JadeHub vẫn sử dụng bình thường.';

        const nutThuLai = document.createElement('button');
        nutThuLai.type = 'button';
        nutThuLai.textContent = 'Thử lại';
        nutThuLai.addEventListener('click', hienThiThoiTiet);

        box.append(thongBao, nutThuLai);
    }
}

hienThiThoiTiet();