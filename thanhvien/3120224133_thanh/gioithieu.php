<?php
/**
 * thanhvien/3120224133_thanh/gioithieu.php - trang cá nhân của Trần Thị Cẩm Thanh (dùng chung header/footer).
 * Chức năng PHP 1: giao diện sáng/tối ghi nhớ bằng cookie thanh_giaodien. 2: máy tính điểm học phần (kiểm tra 0-10).
 * Cách thử: bấm Giao diện tối rồi F5/đóng mở lại trình duyệt; nhập điểm 11 hoặc abc để thấy lỗi từ máy chủ.
 */
require __DIR__ . '/../../inc/config.php';


// Chức năng 1: giao diện sáng/tối ghi nhớ bằng cookie (máy chủ đọc cookie để đặt class của <body>)
$giaoDien = $_COOKIE['thanh_giaodien'] ?? 'sang';
if (!is_string($giaoDien) || !in_array($giaoDien, ['sang', 'toi'], true)) {
    $giaoDien = 'sang';                // không tin cookie: giá trị lạ -> mặc định
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['giaodien'])) {
    $moi = layChuoi($_POST, 'giaodien');
    if (in_array($moi, ['sang', 'toi'], true)) {
        setcookie('thanh_giaodien', $moi, [   // setcookie trước mọi output
            'expires' => time() + 365 * 24 * 3600, 'path' => '/',
            'httponly' => true, 'samesite' => 'Lax',
        ]);
        datFlash('ok', 'Đã đổi giao diện sang chế độ ' . ($moi === 'toi' ? 'tối' : 'sáng') . '.');
    } else {
        datFlash('loi', 'Giao diện không hợp lệ.');
    }
    header('Location: gioithieu.php');    // PRG
    exit;
}

// Chức năng 2: máy tính điểm học phần = 0,2 x A1 + 0,3 x A2 + 0,5 x A3 (điểm từ 0 đến 10), gửi bằng GET
$diem = ['a1' => '', 'a2' => '', 'a3' => ''];
$loiDiem = [];
$ketQuaDiem = null;
if (isset($_GET['tinh'])) {
    $so = [];
    foreach ($diem as $khoa => $_) {
        $v = layChuoi($_GET, $khoa);
        $diem[$khoa] = $v;
        if ($v === '') {
            $loiDiem[$khoa] = 'Vui lòng nhập điểm.';
        } elseif (preg_match('/^[0-9]{1,2}([.,][0-9]{1,2})?\z/', $v) !== 1) {
            $loiDiem[$khoa] = 'Điểm phải là số (ví dụ 7.5).';
        } else {
            $n = (float) str_replace(',', '.', $v);
            if ($n < 0 || $n > 10) {
                $loiDiem[$khoa] = 'Điểm phải nằm trong khoảng 0 đến 10.';
            } else {
                $so[$khoa] = $n;
            }
        }
    }
    if (!$loiDiem) {
        $ketQuaDiem = round(0.2 * $so['a1'] + 0.3 * $so['a2'] + 0.5 * $so['a3'], 2);
    }
}

$goc = '../../';                      // đường dẫn từ trang này về thư mục gốc
$tieuDe = 'Hồ sơ cá nhân | Trần Thị Cẩm Thanh';
$trang = 'gioi-thieu';
$moTa = 'Trang giới thiệu cá nhân, kỹ năng chuyên môn và thời khóa biểu của sinh viên Trần Thị Cẩm Thanh.';
$bodyClass = $giaoDien === 'toi' ? 'che-do-toi' : '';
$cssThem = ['css/style-thanh.css'];
$jsThem = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
        <h1>Hồ sơ sinh viên: Trần Thị Cẩm Thanh</h1>
        <section>
        <h2>1. Thông tin bản thân</h2>
        <figure>
        <img alt="Ảnh thẻ chân dung của sinh viên Trần Thị Cẩm Thanh" src="chan-dung-thanh.jpg" width="200"/>
        <figcaption>Ảnh 1: Chân dung sinh viên</figcaption>
        </figure>
        <p><strong>Mã sinh viên:</strong> 3120224133</p>
        <p><strong>Lớp:</strong> 24CNTT2 — Khoa Toán - Tin, Trường Đại học Sư phạm - ĐHĐN</p>
        <p><strong>Vai trò trong nhóm:</strong> Nhóm trưởng, phụ trách kiến trúc HTML5 và quản lý phiên bản Git.</p>
        </section>
        <article>
        <h2>2. Định hướng chuyên môn và kỹ năng</h2>
        <p>Em định hướng phát triển theo lĩnh vực kỹ thuật phần mềm và lập trình ứng dụng web. Yêu thích việc tìm
                        hiểu kiến trúc mạng máy tính và phát triển các sản phẩm công nghệ có tính thực tiễn cao.</p>
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
        <td>An toàn thông tin</td>
        </tr>
        <tr>
        <th scope="row">Thứ Ba</th>
        <td>Nghỉ</td>
        <td>Hệ quản trị cơ sở dữ liệu</td>
        </tr>
        <tr>
        <th scope="row">Thứ Tư</th>
        <td>Sinh hoạt nhóm</td>
        <td>Làm nhiệm vụ</td>
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
        <td>tổng kết</td>
        </tr>
        </tbody>
        </table>
        </div>
        </section>

        <section id="giao-dien">
            <h2>4. Giao diện sáng / tối (ghi nhớ bằng cookie)</h2>
            <p>Đang dùng chế độ: <strong><?= $giaoDien === 'toi' ? 'tối' : 'sáng' ?></strong>.</p>
            <form method="post" action="gioithieu.php">
                <button type="submit" name="giaodien" value="sang">Giao diện sáng</button>
                <button type="submit" name="giaodien" value="toi">Giao diện tối</button>
            </form>
        </section>

        <section id="tinh-diem">
            <h2>5. Máy tính điểm học phần</h2>
            <p>Công thức: 0,2 × A1 + 0,3 × A2 + 0,5 × A3. Điểm nhập từ 0 đến 10.</p>
            <form method="get" action="gioithieu.php#tinh-diem">
<?php foreach (['a1' => 'Điểm A1 (20%)', 'a2' => 'Điểm A2 (30%)', 'a3' => 'Điểm A3 (50%)'] as $k => $nhan): ?>
                <p>
                    <label for="diem-<?= $k ?>"><?= e($nhan) ?></label>
                    <input id="diem-<?= $k ?>" name="<?= $k ?>" type="text" inputmode="decimal" value="<?= e($diem[$k]) ?>"<?= isset($loiDiem[$k]) ? ' aria-invalid="true" aria-describedby="loi-' . $k . '"' : '' ?>>
                    <small class="loi-truong" id="loi-<?= $k ?>"><?= e($loiDiem[$k] ?? '') ?></small>
                </p>
<?php endforeach; ?>
                <button type="submit" name="tinh" value="1">Tính điểm</button>
            </form>
<?php if ($ketQuaDiem !== null): ?>
            <p role="status">Điểm học phần: <strong><?= e(number_format($ketQuaDiem, 2, ',', '.')) ?></strong> — <?= $ketQuaDiem >= 4.0 ? 'Đạt' : 'Chưa đạt' ?>.</p>
<?php endif; ?>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
