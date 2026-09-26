// Bích Hạnh: mở/đóng phần kỹ năng và lọc hàng thời khóa biểu.
// Thử bằng nút thu gọn và ô tìm thứ trong bảng.
const main=document.querySelector('main');
const article=main.querySelector('article');
const btn=document.createElement('button');btn.type='button';btn.textContent='Thu gọn / mở rộng kỹ năng';btn.setAttribute('aria-expanded','true');
article.before(btn);
btn.addEventListener('click',()=>{article.hidden=!article.hidden;btn.setAttribute('aria-expanded',String(!article.hidden));});
const label=document.createElement('label');label.textContent='Tìm thứ trong lịch: ';
const input=document.createElement('input');input.type='search';label.append(input);
const table=main.querySelector('table');table.before(label);
input.addEventListener('input',()=>{const q=input.value.toLocaleLowerCase('vi');table.querySelectorAll('tbody tr').forEach(tr=>{tr.hidden=!tr.textContent.toLocaleLowerCase('vi').includes(q);});});
