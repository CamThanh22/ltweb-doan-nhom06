<?php
/**
 * src/Data/KhoJsonl.php - lưu bản ghi vào tệp JSON Lines (mỗi dòng một bản ghi JSON).
 * Dùng chung cho liên hệ, sổ lưu bút, đánh giá ở trang cá nhân; khóa tệp khi ghi (LOCK_EX).
 * Cách thử: gửi một liên hệ rồi mở storage/lien-he.jsonl: mỗi lần gửi thêm đúng một dòng.
 */
declare(strict_types=1);

namespace App\Data;

use RuntimeException;

class KhoJsonl
{
    public function __construct(protected string $tep)
    {
    }

    /** Thêm một bản ghi vào cuối tệp. */
    public function them(array $banGhi): void
    {
        $thuMuc = dirname($this->tep);
        if (!is_dir($thuMuc) && !mkdir($thuMuc, 0775, true) && !is_dir($thuMuc)) {
            throw new RuntimeException('Không tạo được thư mục lưu trữ.');
        }
        $dong = json_encode($banGhi, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (file_put_contents($this->tep, $dong . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException('Không ghi được dữ liệu.');
        }
    }

    /** Mọi bản ghi, mới nhất đứng đầu; dòng hỏng bị bỏ qua. */
    public function tatCa(): array
    {
        if (!is_file($this->tep)) {
            return [];
        }
        $dong = file($this->tep, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($dong === false) {
            return [];
        }
        $ds = array_map(fn(string $d) => json_decode($d, true), $dong);
        return array_reverse(array_values(array_filter($ds, 'is_array')));
    }

    /** $n bản ghi mới nhất. */
    public function moiNhat(int $n): array
    {
        return array_slice($this->tatCa(), 0, max(0, $n));
    }

    public function dem(): int
    {
        return count($this->tatCa());
    }
}
