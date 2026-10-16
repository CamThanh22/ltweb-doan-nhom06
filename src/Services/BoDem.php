<?php
/**
 * src/Services/BoDem.php - bộ đếm lượt xem lưu trong tệp (storage/), mỗi PHIÊN chỉ tính một lần.
 * Dùng khóa LOCK_EX để hai request cùng lúc không ghi đè nhau; cờ "đã đếm" nằm trong $_SESSION.
 * Cách thử: mở trang cá nhân của Hạnh, F5 nhiều lần -> số không đổi; mở bằng trình duyệt khác -> +1.
 */
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class BoDem
{
    public function __construct(private string $tep, private string $khoaPhien)
    {
    }

    /** Tăng bộ đếm nếu phiên này chưa đếm, rồi trả về giá trị hiện tại. */
    public function tangMotLanMoiPhien(): int
    {
        $hienTai = $this->doc();
        if (!empty($_SESSION['bo_dem'][$this->khoaPhien])) {
            return $hienTai;
        }
        $thuMuc = dirname($this->tep);
        if (!is_dir($thuMuc) && !mkdir($thuMuc, 0775, true) && !is_dir($thuMuc)) {
            throw new RuntimeException('Không tạo được thư mục lưu trữ.');
        }
        $f = fopen($this->tep, 'c+');
        if ($f === false) {
            throw new RuntimeException('Không mở được tệp bộ đếm.');
        }
        try {
            flock($f, LOCK_EX);
            $so = (int) trim((string) stream_get_contents($f)) + 1;
            ftruncate($f, 0);
            rewind($f);
            fwrite($f, (string) $so);
            fflush($f);
            flock($f, LOCK_UN);
        } finally {
            fclose($f);
        }
        $_SESSION['bo_dem'][$this->khoaPhien] = true;
        return $so;
    }

    /** Đọc giá trị hiện tại (0 nếu chưa có tệp). */
    public function doc(): int
    {
        if (!is_file($this->tep)) {
            return 0;
        }
        $noiDung = file_get_contents($this->tep);
        return $noiDung === false ? 0 : max(0, (int) trim($noiDung));
    }
}
