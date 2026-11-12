<article class="jh-review-card">
<p><strong><?= (int)$review['sao'] ?>/5 <?= $review['loai']==='san_pham'?'sao sản phẩm':'sao người mua' ?></strong>
<?php if($review['loai']==='san_pham'): ?> · Shop <?= (int)$review['sao_doi_tac'] ?>/5<?php endif;?>
<span class="jh-verified">Giao dịch đã hoàn thành</span></p>
<p><a href="ho-so.php?id=<?= (int)$review['nguoi_viet_id'] ?>"><?= e($review['ten_nguoi_viet']) ?></a> · <?= e(substr($review['ngay_tao'],0,10)) ?></p>
<p><?= nl2br(e($review['noi_dung'])) ?></p>
<?php if(!empty($review['ten_san_pham'])): ?><p><a href="chi-tiet.php?id=<?= (int)$review['san_pham_id'] ?>"><?= e($review['ten_san_pham']) ?></a></p><?php endif;?>
<?php if($review['phan_hoi']!==''): ?><blockquote><strong>Phản hồi của người được đánh giá</strong><p><?= nl2br(e($review['phan_hoi'])) ?></p></blockquote><?php endif;?>
<?php if((int)(nguoiDung()['id']??0)===(int)$review['nguoi_nhan_id']): ?>
<details><summary><?= $review['phan_hoi']!==''?'Cập nhật phản hồi':'Phản hồi đánh giá' ?></summary>
<form method="post" action="hau-mai.php"><?= csrfField() ?><input type="hidden" name="hanh_dong" value="phan_hoi"><input type="hidden" name="danh_gia_id" value="<?= (int)$review['id'] ?>">
<textarea name="phan_hoi" minlength="5" maxlength="1000" required aria-label="Nội dung phản hồi"><?= e($review['phan_hoi']) ?></textarea><button type="submit">Lưu phản hồi</button></form></details>
<?php endif;?></article>
