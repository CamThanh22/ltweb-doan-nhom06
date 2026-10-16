<?php
/**
 * inc/thong-bao.php - in thông báo flash (nếu có) rồi xóa nó khỏi session: hiện đúng một lần.
 * Trang nào muốn hiện thì require tệp này ngay sau thẻ <main>.
 * Cách thử: gửi liên hệ hợp lệ rồi bấm F5 ở trang hiện ra -> thông báo không hiện lại.
 */
$tbFlash = layFlash();
if ($tbFlash !== null): ?>
<p class="thong-bao <?= $tbFlash['loai'] === 'ok' ? 'thanh-cong' : 'loi' ?>" role="<?= $tbFlash['loai'] === 'ok' ? 'status' : 'alert' ?>"><?= e((string) $tbFlash['nd']) ?></p>
<?php endif; unset($tbFlash); ?>
