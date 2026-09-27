/*
 * JavaScript cho trang cá nhân Nguyễn Nhật Ánh Hồng.
 * Tương tác 1: tăng, giảm và đặt lại cỡ chữ của nội dung chính.
 * Tương tác 2: thu gọn hoặc mở rộng bảng thời khóa biểu.
 * Cách thử: dùng các nút A+, A−, Mặc định và nút Ẩn/hiện thời khóa biểu.
 */

const main = document.querySelector('main');

if (main) {
    const bar = document.createElement('div');
    bar.className = 'tuong-tac-ca-nhan';

    // Tương tác 1: điều chỉnh cỡ chữ.
    const plus = document.createElement('button');
    plus.type = 'button';
    plus.textContent = 'A+';
    plus.setAttribute('aria-label', 'Tăng cỡ chữ');

    const minus = document.createElement('button');
    minus.type = 'button';
    minus.textContent = 'A−';
    minus.setAttribute('aria-label', 'Giảm cỡ chữ');

    const reset = document.createElement('button');
    reset.type = 'button';
    reset.textContent = 'Mặc định';

    let size = 100;

    function capNhatCoChu() {
        main.style.fontSize = `${size}%`;
    }

    plus.addEventListener('click', () => {
        size = Math.min(130, size + 10);
        capNhatCoChu();
    });

    minus.addEventListener('click', () => {
        size = Math.max(80, size - 10);
        capNhatCoChu();
    });

    reset.addEventListener('click', () => {
        size = 100;
        capNhatCoChu();
    });

    // Tương tác 2: thu gọn / mở rộng bảng thời khóa biểu.
    const bang = main.querySelector('table');
    const nutBang = document.createElement('button');
    nutBang.type = 'button';
    nutBang.textContent = 'Ẩn thời khóa biểu';
    nutBang.setAttribute('aria-expanded', 'true');

    if (!bang) {
        nutBang.disabled = true;
    }

    nutBang.addEventListener('click', () => {
        if (!bang) return;

        bang.hidden = !bang.hidden;
        nutBang.setAttribute('aria-expanded', String(!bang.hidden));
        nutBang.textContent = bang.hidden
            ? 'Hiện thời khóa biểu'
            : 'Ẩn thời khóa biểu';
    });

    bar.append(plus, minus, reset, nutBang);
    main.insertBefore(bar, main.firstChild);
}
