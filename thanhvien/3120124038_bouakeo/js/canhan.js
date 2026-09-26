// Bouakeo: lọc thông tin theo từ khóa và in trang cá nhân.
// Thử ô tìm nội dung và nút in; dùng Esc để xóa bộ lọc.
const main=document.querySelector('main');const box=document.createElement('div');main.prepend(box);
const label=document.createElement('label');label.textContent='Lọc các mục hồ sơ: ';
const input=document.createElement('input');input.type='search';label.append(input);box.append(label);
const sections=[...main.children].filter(el=>el.matches('section,article'));
function filter(){const q=input.value.toLocaleLowerCase('vi');sections.forEach(el=>{el.hidden=!el.textContent.toLocaleLowerCase('vi').includes(q);});}
input.addEventListener('input',filter);
input.addEventListener('keydown',e=>{if(e.key==='Escape'){input.value='';filter();}});
const print=document.createElement('button');print.type='button';print.textContent='In hồ sơ';print.addEventListener('click',()=>window.print());box.append(print);
