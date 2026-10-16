/*
 * Kiểm tra biểu mẫu liên hệ phía client (lớp kiểm tra thứ nhất; máy chủ PHP luôn kiểm tra lại).
 * Dùng Constraint Validation API để báo lỗi dưới từng ô; nếu hợp lệ thì để trình duyệt gửi
 * biểu mẫu thật tới lien-he.php (không còn fetch tới JSONPlaceholder), khóa nút khi đang gửi.
 */

const form = document.querySelector('.form-tham-dinh');
const bao = document.querySelector('#ket-qua-form');

if (!form || !bao) {
    console.warn(
        'Không tìm thấy biểu mẫu thẩm định hoặc vùng thông báo.'
    );
} else {
    const gui = form.querySelector(
        'button[type="submit"]'
    );

    const ngayHen = form.querySelector(
        '#date-hen'
    );

    const nutReset = form.querySelector(
        'button[type="reset"]'
    );

    /*
     * Khi JavaScript hoạt động:
     * tắt validation mặc định để tự kiểm tra
     * bằng Constraint Validation API.
     *
     * Khi JavaScript bị tắt:
     * dòng này không chạy nên HTML5 Validation
     * vẫn hoạt động bình thường.
     */
    form.noValidate = true;


    // ==============================
    // THIẾT LẬP NGÀY HẸN TỐI THIỂU
    // ==============================

    function layNgayHomNay() {
        const homNay = new Date();

        const nam =
            homNay.getFullYear();

        const thang = String(
            homNay.getMonth() + 1
        ).padStart(2, '0');

        const ngay = String(
            homNay.getDate()
        ).padStart(2, '0');

        return `${nam}-${thang}-${ngay}`;
    }


    if (ngayHen) {
        ngayHen.min =
            layNgayHomNay();
    }


    // ==============================
    // CÁC TRƯỜNG CẦN KIỂM TRA
    // ==============================

    const fields = [
        ...form.querySelectorAll(
            'input, select, textarea'
        )
    ].filter((field) =>
        field.type !== 'file' &&
        field.type !== 'reset' &&
        field.type !== 'submit'
    );


    // ==============================
    // HIỂN THỊ LỖI DƯỚI TRƯỜNG
    // ==============================

    function hienThiLoi(field) {
        const out = form.querySelector(
            `[data-loi="${field.id}"]`
        );

        if (!out) {
            return;
        }

        out.textContent =
            field.validationMessage;
    }


    // ==============================
    // KIỂM TRA RIÊNG TỪNG TRƯỜNG
    // ==============================

    function loiRieng(field) {
        /*
         * Xóa lỗi tùy chỉnh trước đó để trình duyệt
         * có thể tính lại validity của trường.
         */
        field.setCustomValidity('');


        // Họ tên
        if (field.id === 'txt-hoten') {
            const hoTen =
                field.value.trim();

            if (
                hoTen.length > 0 &&
                hoTen.length < 4
            ) {
                field.setCustomValidity(
                    'Họ tên cần ít nhất 4 ký tự.'
                );
            }
        }


        // Số điện thoại Việt Nam
        if (field.id === 'txt-sdt') {
            const soDienThoai =
                field.value.trim();

            if (
                soDienThoai &&
                !/^0\d{9}$/.test(
                    soDienThoai
                )
            ) {
                field.setCustomValidity(
                    'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0.'
                );
            }
        }


        // Ngày hẹn
        if (
            field.id === 'date-hen' &&
            field.value
        ) {
            if (
                field.value <
                layNgayHomNay()
            ) {
                field.setCustomValidity(
                    'Ngày hẹn không được trước ngày hiện tại.'
                );
            }
        }


        hienThiLoi(field);
    }


    // ==============================
    // KIỂM TRA KHI NGƯỜI DÙNG NHẬP
    // ==============================

    fields.forEach((field) => {
        field.addEventListener(
            'blur',
            () => {
                loiRieng(field);
            }
        );

        field.addEventListener(
            'input',
            () => {
                loiRieng(field);
            }
        );

        field.addEventListener(
            'change',
            () => {
                loiRieng(field);
            }
        );
    });


    // ==============================
    // XÓA TOÀN BỘ THÔNG BÁO LỖI
    // ==============================

    function xoaThongBaoLoi() {
        form
            .querySelectorAll(
                '.loi-truong'
            )
            .forEach((out) => {
                out.textContent = '';
            });

        fields.forEach((field) => {
            field.setCustomValidity('');
        });
    }


    // ==============================
    // RESET FORM
    // ==============================

    form.addEventListener(
        'reset',
        () => {
            /*
             * Chờ trình duyệt reset giá trị trước,
             * sau đó mới xóa trạng thái lỗi.
             */
            setTimeout(() => {
                xoaThongBaoLoi();

                if (ngayHen) {
                    ngayHen.min =
                        layNgayHomNay();
                }
            }, 0);
        }
    );


    if (nutReset) {
        nutReset.addEventListener(
            'click',
            () => {
                bao.textContent = '';
            }
        );
    }


    // ==============================
    // GỬI FORM (gửi thật tới lien-he.php)
    // ==============================

    form.addEventListener('submit', (event) => {
        fields.forEach((field) => {
            loiRieng(field);
        });

        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            bao.textContent = 'Vui lòng sửa các trường chưa hợp lệ.';

            const truongLoi = form.querySelector(':invalid');
            if (truongLoi) {
                truongLoi.focus();
            }
            return;
        }

        // Hợp lệ: không chặn, trình duyệt gửi POST tới máy chủ; khóa nút để tránh bấm hai lần
        if (gui) {
            gui.disabled = true;
            gui.textContent = 'Đang gửi…';
        }
        bao.textContent = 'Đang gửi biểu mẫu…';
    });

    // Quay lại bằng nút Back (bfcache) thì mở lại nút gửi
    window.addEventListener('pageshow', () => {
        if (gui) {
            gui.disabled = false;
            gui.textContent = 'Gửi thông tin thẩm định';
        }
    });
}
