<?php
/**
 * thanhvien/3120124038_bouakeo/gioithieu.php - trang cá nhân của Ladisa Bouakeo (dùng chung header/footer).
 * Chức năng PHP 1: nhớ tên khách bằng cookie và chào lại. 2: đánh giá hồ sơ 1-5 sao, mỗi phiên một lần (jsonl).
 * Cách thử: ghi nhớ tên rồi tải lại; gửi điểm 9 (bị từ chối) hoặc đánh giá hai lần trong một phiên.
 */
require __DIR__ . '/../../inc/config.php';
use App\Data\KhoJsonl;


// Chức năng 1: nhớ tên khách bằng cookie bouakeo_khach (đọc lại phải kiểm tra, không tin cookie)
$tenKhach = $_COOKIE['bouakeo_khach'] ?? '';
if (!is_string($tenKhach) || mb_strlen($tenKhach) < 2 || mb_strlen($tenKhach) > 30
    || preg_match('/^[\p{L} .\'-]+$/u', $tenKhach) !== 1) {
    $tenKhach = '';
}
// Chức năng 2: đánh giá hồ sơ 1-5 sao, mỗi phiên một lần, lưu storage/3120124038_danhgia.jsonl
$khoDanhGia = new KhoJsonl(__DIR__ . '/../../storage/3120124038_danhgia.jsonl');
$loiTen = '';
$loiDg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hd = layChuoi($_POST, 'hanhdong');
    if ($hd === 'luuten') {
        $ten = layChuoi($_POST, 'ten');
        if (mb_strlen($ten) < 2 || mb_strlen($ten) > 30 || preg_match('/^[\p{L} .\'-]+$/u', $ten) !== 1) {
            $loiTen = 'Tên cần từ 2 đến 30 ký tự, chỉ gồm chữ cái, khoảng trắng, dấu chấm, gạch nối.';
        } else {
            setcookie('bouakeo_khach', $ten, ['expires' => time() + 30 * 24 * 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
            datFlash('ok', 'Đã ghi nhớ tên của bạn.');
        }
    } elseif ($hd === 'quenten') {
        setcookie('bouakeo_khach', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        datFlash('ok', 'Đã xóa tên đã ghi nhớ.');
    } elseif ($hd === 'danhgia') {
        $diemSao = filter_var($_POST['diem'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        $nhanXet = layChuoi($_POST, 'nhanxet');
        if (!empty($_SESSION['da_danh_gia_bouakeo'])) {
            $loiDg = 'Bạn đã đánh giá trong phiên này rồi.';
        } elseif ($diemSao === false) {
            $loiDg = 'Điểm đánh giá phải là số nguyên từ 1 đến 5.';
        } elseif (mb_strlen($nhanXet) > 200) {
            $loiDg = 'Nhận xét tối đa 200 ký tự.';
        } else {
            $khoDanhGia->them(['thoiGian' => date('Y-m-d H:i:s'), 'diem' => $diemSao, 'nhanXet' => $nhanXet]);
            $_SESSION['da_danh_gia_bouakeo'] = true;
            datFlash('ok', 'Cảm ơn bạn đã đánh giá!');
        }
    } else {
        $loiDg = 'Thao tác không hợp lệ.';
    }
    if ($loiTen === '' && $loiDg === '') {
        header('Location: gioithieu.php#danh-gia');   // PRG
        exit;
    }
}
$tatCaDg = $khoDanhGia->tatCa();
$soDg = count($tatCaDg);
$tbDg = $soDg > 0 ? array_sum(array_column($tatCaDg, 'diem')) / $soDg : 0;

$goc = '../../';                      // đường dẫn từ trang này về thư mục gốc
$tieuDe = 'Hồ sơ cá nhân | Ladisa Bouakeo';
$trang = 'gioi-thieu';
$moTa = 'Trang giới thiệu cá nhân, kỹ năng chuyên môn và thời khóa biểu của sinh viên Ladisa Bouakeo.';
$bodyClass = '';
$cssThem = ['css/style-bouakeo.css'];
$jsThem = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
        <h1>Hồ sơ sinh viên: Ladisa Bouakeo</h1>
        <section>
        <h2>1. Thông tin bản thân</h2>
        <figure>
        <img alt="Ảnh thẻ chân dung của sinh viên Ladisa Bouakeo" src="chan-dung-bouakeo.jpg" width="200"/>
        <figcaption>Ảnh 1: Chân dung sinh viên</figcaption>
        </figure>
        <p><strong>Mã sinh viên:</strong> 3120124038</p>
        <p><strong>Lớp:</strong> Sư phạm Tin học (SPT) — Khoa Toán - Tin, Trường Đại học Sư phạm - ĐHĐN</p>
        <p><strong>Vai trò trong nhóm:</strong> Thành viên, tham gia xây dựng và phát triển dự án JadeHub.
                </p></section>
        <article>
        <h2>2. Định hướng chuyên môn và kỹ năng</h2>
        <p> Em định hướng phát triển trong lĩnh vực công nghệ thông tin và giáo dục sáng tạo.
                        Em đặc biệt yêu thích việc tìm hiểu về lập trình game, ứng dụng trí tuệ nhân tạo (AI) để tạo hoạt hình
                        và các sản phẩm công nghệ tương tác.</p>
        <h3>Kỹ năng chính:</h3>
        <ul>
        <li>Lập trình &amp; Phát triển Game: C++, Python, HTML5, CSS3</li>
        <li>Công nghệ AI &amp; Hoạt hình: Tạo hoạt hình AI, ứng dụng công cụ AI trong sáng tạo nội dung</li>
        <li>Hệ quản trị cơ sở dữ liệu: MySQL, PostgreSQL, SQL Server</li>
        <li>Công cụ làm việc: Visual Studio Code, Git/GitHub</li>
        </ul>
        </article>
        <section>
        <h2>3. Thời khóa biểu học tập tuần này</h2>
        <div class="khung-thoi-khoa-bieu">
        <table>
        <caption>Bảng 1: Lịch học các học phần trong học kỳ 1 năm học 2026 - 2027</caption>
        <thead>
        <tr>
        <th scope="col">Thứ</th>
        <th scope="col">Buổi sáng (Tiết 1 - 6)</th>
        <th scope="col">Buổi chiều (Tiết 7 - 12)</th>
        </tr>
        </thead>
        <tbody>
        <tr>
        <th scope="row">Thứ Hai</th>
        <td>Nghỉ</td>
        <td>nghỉ</td>
        </tr>
        <tr>
        <th scope="row">Thứ Ba</th>
        <td>Phân tích và thiết kế hệ thống thông tin (Phòng B1.106)</td>
        <td>Hệ quản trị cơ sở dữ liệu</td>
        </tr>
        <tr>
        <th scope="row">Thứ Tư</th>
        <td>Phương pháp dạy học bộ môn Tin học (Phòng A5-406)</td>
        <td>Nghỉ</td>
        </tr>
        <tr>
        <th scope="row">Thứ Năm</th>
        <td>Lịch sử Đảng Cộng sản Việt Nam (Phòng A6-502)</td>
        <td>Nghỉ</td>
        </tr>
        <tr>
        <th scope="row">Thứ Sáu</th>
        <td>Nghỉ</td>
        <td>Phân tích thiết kế hướng đối tượng (Phòng B3-102)</td>
        </tr>
        <tr>
        <th scope="row">Thứ Bảy</th>
        <td>Thiết kế và lập trình Web</td>
        <td>nghỉ</td>
        </tr>
        </tbody>
        </table>
        </div>
        </section>

        <section id="danh-gia">
            <h2>4. Lời chào và đánh giá hồ sơ</h2>
<?php if ($tenKhach !== ''): ?>
            <p>Chào mừng quay lại, <strong><?= e($tenKhach) ?></strong>!</p>
            <form method="post" action="gioithieu.php#danh-gia">
                <input type="hidden" name="hanhdong" value="quenten">
                <button type="submit">Quên tên của tôi</button>
            </form>
<?php else: ?>
            <form method="post" action="gioithieu.php#danh-gia">
                <input type="hidden" name="hanhdong" value="luuten">
                <label for="ten-khach">Tên của bạn (để lần sau chào lại)</label>
                <input id="ten-khach" name="ten" type="text" maxlength="30" required>
                <button type="submit">Ghi nhớ tên</button>
                <small class="loi-truong"><?= e($loiTen) ?></small>
            </form>
<?php endif; ?>

            <h3>Đánh giá hồ sơ (1-5 sao)</h3>
            <p>Điểm trung bình: <strong><?= $soDg > 0 ? e(number_format($tbDg, 1, ',', '.')) : 'chưa có' ?></strong> (<?= $soDg ?> lượt đánh giá).</p>
<?php if ($loiDg !== ''): ?>
            <p class="thong-bao loi" role="alert"><?= e($loiDg) ?></p>
<?php endif; ?>
            <form method="post" action="gioithieu.php#danh-gia">
                <input type="hidden" name="hanhdong" value="danhgia">
                <label for="dg-diem">Số sao</label>
                <select id="dg-diem" name="diem">
<?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?> sao</option>
<?php endfor; ?>
                </select>
                <label for="dg-nhanxet">Nhận xét (không bắt buộc)</label>
                <input id="dg-nhanxet" name="nhanxet" type="text" maxlength="200">
                <button type="submit">Gửi đánh giá</button>
            </form>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
