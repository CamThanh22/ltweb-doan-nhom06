// Ánh Hồng: tăng/giảm cỡ chữ và hiển thị đồng hồ hiện tại.
// Thử hai nút thay cỡ chữ và quan sát giờ cập nhật mỗi phút.
const main=document.querySelector('main');const box=document.createElement('div');main.prepend(box);
let size=100;
for(const [label,step] of [['Chữ lớn hơn',10],['Chữ nhỏ hơn',-10]]){
 const b=document.createElement('button');b.type='button';b.textContent=label;box.append(b);
 b.addEventListener('click',()=>{size=Math.min(140,Math.max(80,size+step));main.style.fontSize=size+'%';});
}
const clock=document.createElement('p');clock.setAttribute('aria-live','polite');box.append(clock);
function tick(){clock.textContent='Giờ hiện tại: '+new Intl.DateTimeFormat('vi-VN',{hour:'2-digit',minute:'2-digit',timeZone:'Asia/Ho_Chi_Minh'}).format(new Date());}
tick();setInterval(tick,60000);
