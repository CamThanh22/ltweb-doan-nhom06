import { taiJSON } from './api.js';
import { docYeuThich } from './yeu-thich.js';
const khung=document.querySelector('#chi-tiet-dong');
const trangThai=document.querySelector('#trang-thai-chi-tiet');
const id=Number(new URLSearchParams(location.search).get('id'));
function p(label,value) {
  const el=document.createElement('p');
  const strong=document.createElement('strong'); strong.textContent=label+': ';
  el.append(strong,document.createTextNode(String(value)));
  return el;
}
async function tai() {
  trangThai.textContent='Đang tải thông tin sản phẩm…';
  try {
    const ds=await taiJSON('data/san-pham.json');
    if (!Array.isArray(ds)) throw new Error('Dữ liệu không hợp lệ');
    const sp=ds.find(x=>x.id===id);
    if (!sp) { khung.replaceChildren(); trangThai.textContent='Không tìm thấy sản phẩm có mã này.'; document.querySelector('#chi-tiet-tinh').hidden=true; return; }
    document.title=`${sp.ten} | JadeHub`;
    const article=document.createElement('article');
    const h=document.createElement('h2'); h.textContent=sp.ten;
    const img=document.createElement('img'); img.src=sp.hinh; img.alt=sp.ten; img.width=450; img.height=338;
    const button=document.createElement('button'); button.type='button'; button.dataset.yeuThich=String(sp.id);
    button.textContent=docYeuThich().includes(sp.id)?'Bỏ yêu thích':'Yêu thích';
    button.setAttribute('aria-pressed',String(docYeuThich().includes(sp.id)));
    button.setAttribute('aria-label',`Yêu thích ${sp.ten}`);
    article.append(h,img,p('Mã',`JADE-${String(sp.id).padStart(2,'0')}`),p('Loại',sp.loai),p('Xuất xứ',sp.xuatXu),p('Giá',new Intl.NumberFormat('vi-VN').format(sp.gia)+' VNĐ'),p('Tình trạng',sp.tinhTrang),p('Mô tả',sp.moTa),button);
    khung.replaceChildren(article);
    trangThai.textContent='Đã tải thông tin sản phẩm.';
    document.querySelector('#chi-tiet-tinh').hidden=true;
  } catch(loi) {
    console.error('Không tải được chi tiết:',loi);
    trangThai.textContent='Không tải được dữ liệu. Vui lòng kiểm tra kết nối và thử lại. ';
    const btn=document.createElement('button'); btn.type='button'; btn.textContent='Thử lại'; btn.addEventListener('click',tai); trangThai.append(btn);
  }
}
tai();
