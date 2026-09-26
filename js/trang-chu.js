import { taiJSON } from './api.js';
const khung=document.querySelector('#thoi-tiet');
async function tai() {
  khung.textContent='Đang tải thời tiết Đà Nẵng…';
  try {
    const params=new URLSearchParams({latitude:'16.05',longitude:'108.2',current:'temperature_2m',timezone:'Asia/Ho_Chi_Minh'});
    const data=await taiJSON(`https://api.open-meteo.com/v1/forecast?${params}`);
    if (typeof data.current?.temperature_2m !== 'number') throw new Error('Thiếu nhiệt độ');
    khung.textContent=`Nhiệt độ Đà Nẵng hiện tại: ${data.current.temperature_2m} °C (Open-Meteo).`;
  } catch(loi) {
    console.error('Không tải được thời tiết:',loi);
    khung.textContent='Không tải được thời tiết. Kiểm tra kết nối rồi thử lại. ';
    const btn=document.createElement('button'); btn.type='button'; btn.textContent='Thử lại'; btn.addEventListener('click',tai); khung.append(btn);
  }
}
tai();
