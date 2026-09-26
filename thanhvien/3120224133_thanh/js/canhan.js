// Cẩm Thanh: đổi sáng/tối và lọc nội dung kỹ năng.
// Thử bằng nút giao diện và ô tìm kiếm; tải lại trang để xem lựa chọn được lưu.
const main=document.querySelector('main');
const zone=document.createElement('div'); main.prepend(zone);
const mode=document.createElement('button'); mode.type='button'; mode.textContent='Đổi giao diện sáng / tối'; zone.append(mode);
const saved=localStorage.getItem('thanh-theme')==='toi'; document.body.classList.toggle('ca-nhan-toi',saved);
mode.addEventListener('click',()=>{const dark=document.body.classList.toggle('ca-nhan-toi'); try{localStorage.setItem('thanh-theme',dark?'toi':'sang');}catch{} });
const label=document.createElement('label');label.textContent='Tìm kỹ năng: ';
const search=document.createElement('input');search.type='search';label.append(search);zone.append(label);
const list=[...main.querySelectorAll('li')].filter(x=>/HTML|CSS|Git|Python|Linux|SQL|JavaScript/i.test(x.textContent));
search.addEventListener('input',()=>{const q=search.value.toLocaleLowerCase('vi');list.forEach(li=>{li.hidden=!li.textContent.toLocaleLowerCase('vi').includes(q);});});
