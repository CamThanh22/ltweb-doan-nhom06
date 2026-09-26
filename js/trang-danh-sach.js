import { taiJSON } from './api.js';
import { docYeuThich } from './yeu-thich.js';
const khung = document.querySelector('#danh-sach-dong');
const trangThai = document.querySelector('#trang-thai-danh-sach');
const tim = document.querySelector('#tim-san-pham');
const loc = document.querySelector('#loc-danh-muc');
const sap = document.querySelector('#sap-xep');
let danhSach = [];
const khongDau = s => String(s).normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase();
function the(tag, text, className) {
  const el = document.createElement(tag);
  el.textContent = text;
  if (className) el.className = className;
  return el;
}
function ve() {
  const q = khongDau(tim.value.trim());
  const ds = danhSach.filter(sp => (!loc.value || sp.danhMuc === loc.value) && khongDau(sp.ten + ' ' + sp.loai).includes(q));
  ds.sort((a,b) => sap.value === 'gia-tang' ? a.gia-b.gia : sap.value === 'gia-giam' ? b.gia-a.gia : a.ten.localeCompare(b.ten, 'vi'));
  khung.replaceChildren();
  trangThai.textContent = ds.length ? `Tìm thấy ${ds.length} sản phẩm.` : 'Không có sản phẩm phù hợp. Hãy thử từ khóa khác.';
  for (const sp of ds) {
    const bai = the('article','', 'the-san-pham');
    const img = document.createElement('img');
    img.src = sp.hinh; img.alt = sp.ten; img.width = 400; img.height = 300; img.loading = 'lazy';
    const noiDung = the('div','', 'noi-dung-the');
    noiDung.append(the('h3',sp.ten),the('p',sp.loai),the('p',new Intl.NumberFormat('vi-VN').format(sp.gia)+' VNĐ','gia-san-pham'));
    const link = the('a','Xem chi tiết'); link.href = `chi-tiet.html?id=${sp.id}`;
    const nut = the('button',docYeuThich().includes(sp.id) ? 'Bỏ yêu thích' : 'Yêu thích');
    nut.type = 'button'; nut.dataset.yeuThich = String(sp.id);
    nut.setAttribute('aria-pressed',String(docYeuThich().includes(sp.id)));
    nut.setAttribute('aria-label',`Yêu thích ${sp.ten}`);
    noiDung.append(link,nut); bai.append(img,noiDung); khung.append(bai);
  }
}
[tim,loc,sap].forEach(el => el.addEventListener(el === tim ? 'input' : 'change',ve));
window.addEventListener('jadehub:yeu-thich',ve);
async function tai() {
  trangThai.textContent='Đang tải danh sách sản phẩm…';
  try {
    const ds = await taiJSON('data/san-pham.json');
    if (!Array.isArray(ds)) throw new Error('Dữ liệu sản phẩm không hợp lệ');
    danhSach=ds; ve();
    document.querySelector('#san-pham-tinh').hidden=true;
  } catch (loi) {
    console.error('Không tải được danh sách:',loi);
    trangThai.replaceChildren(the('span','Không tải được danh sách. '));
    const thuLai=the('button','Thử lại'); thuLai.type='button'; thuLai.addEventListener('click',tai);
    trangThai.append(thuLai);
  }
}
tai();
