const form=document.querySelector('.form-tham-dinh');
const trangThai=document.querySelector('#trang-thai-lien-he');
form.noValidate=true;
function kiemTra(ô) {
  const error=document.querySelector(`#loi-${ô.id}`);
  if (!error) return ô.checkValidity();
  if (ô.id==='date-hen') ô.setCustomValidity(ô.value && ô.value < new Date().toISOString().slice(0,10) ? 'Hãy chọn ngày hôm nay hoặc ngày sau.' : '');
  const hopLe=ô.checkValidity();
  error.textContent=hopLe?'':ô.validationMessage;
  ô.setAttribute('aria-invalid',String(!hopLe));
  return hopLe;
}
const fields=[...form.querySelectorAll('input:not([type="file"]), select, textarea')];
fields.forEach(ô=>ô.addEventListener('blur',()=>kiemTra(ô)));
form.addEventListener('reset',()=>setTimeout(()=>{
  fields.forEach(ô=>{ ô.setCustomValidity(''); ô.removeAttribute('aria-invalid'); const e=document.querySelector(`#loi-${ô.id}`); if(e) e.textContent=''; });
},0));
form.querySelector('[type="reset"]').addEventListener('click',()=>{trangThai.textContent='';});
form.addEventListener('submit',async e=>{
  e.preventDefault();
  const hopLe=fields.map(kiemTra).every(Boolean);
  if (!hopLe) { trangThai.textContent='Vui lòng sửa các trường được báo lỗi.'; form.querySelector('[aria-invalid="true"]')?.focus(); return; }
  const nut=form.querySelector('[type="submit"]'); nut.disabled=true;
  trangThai.textContent='Đang gửi yêu cầu…';
  const payload=Object.fromEntries(new FormData(form)); delete payload.anhngoc;
  try {
    const res=await fetch('https://jsonplaceholder.typicode.com/posts',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    trangThai.textContent='API thử nghiệm đã nhận yêu cầu. Dữ liệu chưa được lưu thật; vui lòng không gửi thông tin nhạy cảm.';
    form.reset();
  } catch(loi) {
    console.error('Không gửi được biểu mẫu:',loi);
    trangThai.textContent='Không gửi được. Kiểm tra kết nối rồi bấm Gửi để thử lại.';
  } finally { nut.disabled=false; }
});
