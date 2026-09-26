// Phương Trinh: đếm số mục kỹ năng và chọn ngẫu nhiên một mục để đọc.
// Thử hai nút, quan sát dòng trạng thái được cập nhật bằng textContent.
const main=document.querySelector('main');const box=document.createElement('div');main.prepend(box);
const items=[...main.querySelectorAll('li')].filter(li=>!li.closest('nav'));
const status=document.createElement('p');status.setAttribute('aria-live','polite');
for(const [label,handler] of [['Đếm kỹ năng',()=>`Trang này có ${items.length} mục trong các danh sách.`],['Gợi ý một mục',()=>items.length?`Gợi ý: ${items[Math.floor(Math.random()*items.length)].textContent.trim()}`:'Chưa có mục để gợi ý.']]){
 const b=document.createElement('button');b.type='button';b.textContent=label;box.append(b);b.addEventListener('click',()=>{status.textContent=handler();});
}box.append(status);
