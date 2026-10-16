<?php
/**
 * thanhvien/3120224050_hanh/gioithieu.php - trang cá nhân của Nguyễn Thị Bích Hạnh (dùng chung header/footer).
 * Chức năng PHP 1: sổ lưu bút (POST, kiểm tra, storage/3120224050_luubut.jsonl, PRG). 2: bộ đếm lượt xem mỗi phiên một lần.
 * Cách thử: gửi lời nhắn rồi F5 (không gửi lại); F5 nhiều lần số lượt xem không tăng, trình duyệt khác thì +1.
 */
require __DIR__ . '/../../inc/config.php';
use App\Data\KhoJsonl;
use App\Services\BoDem;


// Chức năng 2: bộ đếm lượt xem, mỗi PHIÊN chỉ tính một lần (tệp trong storage/ + cờ trong $_SESSION)
$luotXem = (new BoDem(__DIR__ . '/../../storage/3120224050_luotxem.txt', 'hanh'))->tangMotLanMoiPhien();

// Chức năng 1: sổ lưu bút - biểu mẫu tên + lời nhắn, kiểm tra ở máy chủ, lưu storage/3120224050_luubut.jsonl
$khoLuuBut = new KhoJsonl(__DIR__ . '/../../storage/3120224050_luubut.jsonl');
$du = ['ten' => '', 'loinhan' => ''];
$loi = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['ten'] = layChuoi($_POST, 'ten');
    $du['loinhan'] = layChuoi($_POST, 'loinhan');
    if (mb_strlen($du['ten']) < 2 || mb_strlen($du['ten']) > 40) {
        $loi['ten'] = 'Tên cần từ 2 đến 40 ký tự.';
    }
    if (mb_strlen($du['loinhan']) < 5 || mb_strlen($du['loinhan']) > 300) {
        $loi['loinhan'] = 'Lời nhắn cần từ 5 đến 300 ký tự.';
    }
    if (!$loi) {
        $khoLuuBut->them(['thoiGian' => date('Y-m-d H:i:s'), 'ten' => $du['ten'], 'loiNhan' => $du['loinhan']]);
        datFlash('ok', 'Cảm ơn bạn đã để lại lời nhắn!');
        header('Location: gioithieu.php#luu-but');   // PRG
        exit;
    }
}
$luuBut = $khoLuuBut->moiNhat(5);       // năm lời nhắn mới nhất

$goc = '../../';                      // đường dẫn từ trang này về thư mục gốc
$tieuDe = 'Hồ sơ cá nhân | Nguyễn Thị Bích Hạnh';
$trang = 'gioi-thieu';
$moTa = 'Trang giới thiệu cá nhân, kỹ năng chuyên môn và thời khóa biểu của sinh viên Nguyễn Thị Bích Hạnh.';
$bodyClass = '';
$cssThem = ['css/style-hanh.css'];
$jsThem = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
                        <h1>Hồ sơ sinh viên: Nguyễn Thị Bích Hạnh</h1>
                        <section>
                                <h2>1. Thông tin bản thân</h2>
                                <figure>
                                        <img alt="Ảnh thẻ chân dung của sinh viên Nguyễn Thị Bích Hạnh" src="chan-dung-hanh.jpg"
                                                width="200" />
                                        <figcaption>Ảnh 1: Chân dung sinh viên</figcaption>
                                </figure>
                                <p><strong>Mã sinh viên:</strong> 3120224050</p>
                                <p><strong>Lớp:</strong> 24CNTT2 — Khoa Toán - Tin, Trường Đại học Sư phạm - ĐHĐN</p>
                                <p><strong>Vai trò trong nhóm:</strong> Thành viên, tham gia xây dựng và phát triển dự
                                        án
                                        JadeHub.
                                </p>
                        </section>
                        <article>
                                <h2>2. Định hướng chuyên môn và kỹ năng</h2>
                                <p>Em định hướng phát triển theo lĩnh vực kỹ thuật phần mềm. Yêu thích việc tìm
                                        hiểu kiến trúc mạng máy tính và phát triển các sản phẩm công nghệ .</p>
                                <h3>Kỹ năng chính:</h3>
                                <ul>
                                        <li>Ngôn ngữ lập trình: HTML5, CSS3, PHP, Python, C++</li>
                                        <li>Hệ quản trị cơ sở dữ liệu: MySQL, PostgreSQL, SQL Server</li>
                                        <li>Công cụ làm việc: Visual Studio Code, Git/GitHub, Postman</li>
                                </ul>
                        </article>
                        <section>
                                <h2>3. Thời khóa biểu học tập tuần này</h2>
                                <div class="khung-thoi-khoa-bieu">
                                        <table>
                                                <caption>Bảng 1: Lịch học các học phần trong học kỳ 1 năm học 2026 -
                                                        2027
                                                </caption>
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
                                                                <td>An toàn thông tin</td>
                                                        </tr>
                                                        <tr>
                                                                <th scope="row">Thứ Ba</th>
                                                                <td>Nghỉ</td>
                                                                <td>Hệ quản trị cơ sở dữ liệu</td>
                                                        </tr>
                                                        <tr>
                                                                <th scope="row">Thứ Tư</th>
                                                                <td>nghỉ</td>
                                                                <td>làm bài tập</td>
                                                        </tr>
                                                        <tr>
                                                                <th scope="row">Thứ Năm</th>
                                                                <td>Khai phá dữ liệu</td>
                                                                <td>Lịch sử đảng; Công nghệ phần mềm</td>
                                                        </tr>
                                                        <tr>
                                                                <th scope="row">Thứ Sáu</th>
                                                                <td>Lập trình mạng</td>
                                                                <td>Sinh hoạt nhóm</td>
                                                        </tr>
                                                        <tr>
                                                                <th scope="row">Thứ Bảy</th>
                                                                <td>Thiết kế và lập trình Web</td>
                                                                <td>Học</td>
                                                        </tr>
                                                </tbody>
                                        </table>
                                </div>
                        </section>
        

        <section id="luu-but">
            <h2>4. Sổ lưu bút</h2>
            <p>Lượt xem trang này: <strong><?= $luotXem ?></strong> (mỗi phiên chỉ tính một lần).</p>
<?php if ($loi): ?>
            <p class="thong-bao loi" role="alert">Vui lòng sửa các ô được báo lỗi.</p>
<?php endif; ?>
            <form method="post" action="gioithieu.php#luu-but">
                <p>
                    <label for="lb-ten">Tên của bạn (*)</label>
                    <input id="lb-ten" name="ten" type="text" maxlength="40" required value="<?= e($du['ten']) ?>">
                    <small class="loi-truong"><?= e($loi['ten'] ?? '') ?></small>
                </p>
                <p>
                    <label for="lb-loinhan">Lời nhắn (*)</label>
                    <textarea id="lb-loinhan" name="loinhan" rows="3" maxlength="300" required><?= e($du['loinhan']) ?></textarea>
                    <small class="loi-truong"><?= e($loi['loinhan'] ?? '') ?></small>
                </p>
                <button type="submit">Gửi lời nhắn</button>
            </form>
            <h3>Năm lời nhắn mới nhất</h3>
<?php if (!$luuBut): ?>
            <p>Chưa có lời nhắn nào.</p>
<?php else: ?>
            <ul>
<?php foreach ($luuBut as $lb): ?>
                <li><strong><?= e((string) ($lb['ten'] ?? '')) ?></strong> <small>(<?= e((string) ($lb['thoiGian'] ?? '')) ?>)</small><br><?= nl2br(e((string) ($lb['loiNhan'] ?? ''))) ?></li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
