<?php
/**
 * thanhvien/3120124031_trinh/gioithieu.php - trang cá nhân của Huỳnh Trần Phương Trinh (dùng chung header/footer).
 * Chức năng PHP 1: danh sách kỹ năng lọc theo nhóm (GET ?nhom=). 2: việc cần làm lưu trong session (POST, PRG).
 * Cách thử: ?nhom=web, ?nhom=abc (bị bỏ qua); thêm/đánh dấu/xóa việc rồi F5, đóng trình duyệt thì mất.
 */
require __DIR__ . '/../../inc/config.php';


// Chức năng 1: danh sách kỹ năng lọc theo nhóm bằng tham số GET ?nhom= (dữ liệu từ mảng PHP)
$nhomKiNang = ['web' => 'Lập trình web', 'cong-cu' => 'Công cụ', 'he-thong' => 'Hệ thống'];
$kiNang = [
    ['nhom' => 'web', 'ten' => 'Lập trình Web (HTML5, CSS3, PHP, JavaScript)'],
    ['nhom' => 'cong-cu', 'ten' => 'Quản lý mã nguồn với Git & GitHub'],
    ['nhom' => 'he-thong', 'ten' => 'Quản trị hệ thống Linux cơ bản'],
];
$nhomLoc = layChuoi($_GET, 'nhom');
if ($nhomLoc !== '' && !array_key_exists($nhomLoc, $nhomKyNang)) {
    $nhomLoc = '';
}
$kyNangHienThi = array_values(array_filter($kyNang, fn(array $k): bool => $nhomLoc === '' || $k['nhom'] === $nhomLoc));

// Chức năng 2: danh sách việc cần làm lưu trong SESSION (thêm / đánh dấu xong / xóa), POST rồi PRG
$viec = $_SESSION['viec_trinh'] ?? [];
if (!is_array($viec)) {
    $viec = [];
}
$loiViec = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hd = layChuoi($_POST, 'hanhdong');
    $idViec = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($hd === 'them') {
        $nd = layChuoi($_POST, 'noidung');
        if (mb_strlen($nd) < 3 || mb_strlen($nd) > 100) {
            $loiViec = 'Nội dung việc cần từ 3 đến 100 ký tự.';
        } elseif (count($viec) >= 10) {
            $loiViec = 'Chỉ lưu tối đa 10 việc, hãy xóa bớt trước.';
        } else {
            $viec[] = ['id' => ($viec ? max(array_column($viec, 'id')) : 0) + 1, 'nd' => $nd, 'xong' => false];
            $_SESSION['viec_trinh'] = $viec;
        }
    } elseif (in_array($hd, ['xong', 'xoa'], true) && $idViec !== false) {
        $viec = array_values(array_filter(array_map(function (array $v) use ($hd, $idViec) {
            if ($v['id'] !== $idViec) {
                return $v;
            }
            if ($hd === 'xoa') {
                return null;
            }
            $v['xong'] = !$v['xong'];
            return $v;
        }, $viec)));
        $_SESSION['viec_trinh'] = $viec;
    } else {
        $loiViec = 'Thao tác không hợp lệ.';
    }
    if ($loiViec === '') {
        header('Location: gioithieu.php#viec');   // PRG
        exit;
    }
}

$goc = '../../';                      // đường dẫn từ trang này về thư mục gốc
$tieuDe = 'Hồ sơ cá nhân | Huỳnh Trần Phương Trinh';
$trang = 'gioi-thieu';
$moTa = 'Trang giới thiệu cá nhân, kỹ năng chuyên môn và thời khóa biểu của sinh viên Huỳnh Trần Phương Trinh.';
$bodyClass = '';
$cssThem = ['css/style-trinh.css'];
$jsThem = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
        <h1>Hồ sơ sinh viên: Huỳnh Trần Phương Trinh</h1>
        <!-- Section: Giới thiệu bản thân -->
        <section id="gioi-thieu">
        <h2>Giới thiệu bản thân</h2>
        <img alt="Ảnh chân dung của Huỳnh Trần Phương Trinh" height="150" src="chan-dung-trinh.jpg" width="150"/>
        <p>Họ và tên: <strong>Huỳnh Trần Phương Trinh</strong></p>
        <p>Sinh viên ngành: CNTT / Sư phạm Tin học</p>
        <h3>Kỹ năng cá nhân</h3>
        <form method="get" action="gioithieu.php#ky-nang" class="form-loc-thu" id="ky-nang">
            <label for="loc-nhom">Lọc theo nhóm</label>
            <select id="loc-nhom" name="nhom">
                <option value="">Tất cả</option>
        <?php foreach ($nhomKyNang as $khoa => $nhan): ?>
                <option value="<?= e($khoa) ?>"<?= $khoa === $nhomLoc ? ' selected' : '' ?>><?= e($nhan) ?></option>
        <?php endforeach; ?>
            </select>
            <button type="submit">Lọc</button>
        </form>
        <ul>
        <?php foreach ($kyNangHienThi as $k): ?>
            <li><?= e($k['ten']) ?></li>
        <?php endforeach; ?>
        </ul>
        </section>
        <!-- Article: Dự án / Sở thích -->
        <article id="du-an-so-thich">
        <h2>Dự án &amp; Sở thích</h2>
        <p><strong>Dự án:</strong> Phát triển game xếp hình bằng Python Tkinter và xây dựng bài giảng điện tử số.
                    </p>
        <p><strong>Sở thích:</strong> nghe nhạc.</p>
        </article>
        <!-- Section: Thời khóa biểu tuần -->
        <section id="thoi-khoa-bieu">
        <h2>Thời khóa biểu tuần</h2>
        <div class="khung-thoi-khoa-bieu">
        <table>
        <caption>Thời khóa biểu các học phần trong tuần</caption>
        <thead>
        <tr>
        <th scope="col">Ca / Thứ</th>
        <th scope="col">Thứ 2</th>
        <th scope="col">Thứ 3</th>
        <th scope="col">Thứ 4</th>
        <th scope="col">Thứ 5</th>
        <th scope="col">Thứ 6</th>
        </tr>
        </thead>
        <tbody>
        <tr>
        <th scope="row">Sáng</th>
        <td>tự học</td>
        <td>Phân tích thiết kế hệ thống</td>
        <td>Phương pháp dạy học bộ môn tin học</td>
        <td>Lịch sử Đảng</td>
        <td>HQT CSDL</td>
        </tr>
        <tr>
        <th scope="row">Chiều</th>
        <td>Tự học</td>
        <td>Tự học</td>
        <td>Tự học</td>
        <td>Cơ sở dữ liệu</td>
        <td>Đi dạy thêm</td>
        </tr>
        </tbody>
        </table>
        </div>
        </section>

        <section id="viec">
            <h2>Việc cần làm khi học PHP (lưu trong session)</h2>
<?php if ($loiViec !== ''): ?>
            <p class="thong-bao loi" role="alert"><?= e($loiViec) ?></p>
<?php endif; ?>
            <form method="post" action="gioithieu.php#viec">
                <input type="hidden" name="hanhdong" value="them">
                <label for="viec-nd">Việc mới (3-100 ký tự)</label>
                <input id="viec-nd" name="noidung" type="text" maxlength="100" required>
                <button type="submit">Thêm</button>
            </form>
<?php if (!$viec): ?>
            <p>Chưa có việc nào.</p>
<?php else: ?>
            <ul>
<?php foreach ($viec as $v): ?>
                <li>
                    <span<?= $v['xong'] ? ' style="text-decoration: line-through"' : '' ?>><?= e((string) $v['nd']) ?></span>
                    <form method="post" action="gioithieu.php#viec" style="display:inline">
                        <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                        <button type="submit" name="hanhdong" value="xong"><?= $v['xong'] ? 'Bỏ đánh dấu' : 'Đã xong' ?></button>
                        <button type="submit" name="hanhdong" value="xoa">Xóa</button>
                    </form>
                </li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
