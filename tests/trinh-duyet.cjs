// Requires Playwright, sample accounts and an approved admin seller on a disposable test DB.
// Weather is a deterministic fixture; live external service is not asserted.
const {chromium}=require('playwright');
(async()=>{
const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_BIN?{executablePath:process.env.CHROMIUM_BIN}:{}),args:['--no-sandbox']});
const ctx=await browser.newContext(); const page=await ctx.newPage();
const base=(process.env.BASE || 'http://localhost/LTW_BTN6_Nhom06_code').replace(/\/$/,'')+'/';
if(!['localhost','127.0.0.1'].includes(new URL(base).hostname))throw Error('Localhost only');
const errors=[];
await page.route('https://api.open-meteo.com/**',r=>r.fulfill({status:200,contentType:'application/json',body:JSON.stringify({current:{temperature_2m:27,relative_humidity_2m:80,weather_code:2}})}));
page.on('pageerror',e=>{errors.push(e.message);console.log('JSERROR',e.message)});
page.on('console',m=>{if(m.type()==='error' && !m.text().includes('api.open-meteo.com')) {errors.push(m.text()); console.log('CONSOLE',page.url(),m.text());}});
page.on('response',r=>{if(r.url().startsWith(base)&&r.status()>=400 && ['script','stylesheet','image'].includes(r.request().resourceType())){errors.push(r.status()+' '+r.url());console.log('ASSET',r.status(),r.url());}});
await page.goto(base+'dang-nhap.php');
await page.locator('[name=email]').fill('admin@ued.vn');
await page.locator('[name=mat_khau]').fill('matkhau123');
await Promise.all([page.waitForURL('**/quan-tri.php'),page.locator('form.form-dang-nhap button').click()]);
const paths=['index.php','danh-sach.php','chi-tiet.php?id=1','gio-hang.php','mua-ngay.php?id=1','don-hang.php','ho-so.php','cong-dong.php','tin-nhan.php?id=2','thong-bao-xa-hoi.php','nguoi-ban.php','dang-ban.php','quan-tri.php','kiem-duyet.php','them-san-pham.php','sua-san-pham.php?id=1','gioi-thieu.php','lien-he.php',
'thanhvien/3120224133_thanh/gioithieu.php','thanhvien/3120224050_hanh/gioithieu.php','thanhvien/3120224063_hong/gioithieu.php','thanhvien/3120124031_trinh/gioithieu.php','thanhvien/3120124038_bouakeo/gioithieu.php'];
for(const path of paths){
 let before=errors.length;await page.goto(base+path,{waitUntil:'load'});
 await page.waitForTimeout(400);console.log((errors.length===before?'PASS ':'FAIL ')+'browser '+path);
}
await page.goto(base+'chi-tiet.php?id=1');
const added=page.waitForResponse(r=>r.url().endsWith('/api/gio-hang.php') && r.request().method()==='POST');
await page.locator('form.them-gio button').first().click();
const response=await added;
console.log('Cart AJAX with CSRF:',response.status()===200 && !!response.request().headers()['x-csrf-token']);
await page.waitForTimeout(200);
console.log('Cart counter updated:',Number(await page.locator('#so-mon').textContent())>0);
await page.setViewportSize({width:390,height:844});
await page.goto(base+'index.php',{waitUntil:'load'});
await page.locator('[data-nav-toggle]').click();
console.log('Mobile menu expanded:',await page.locator('[data-nav-toggle]').getAttribute('aria-expanded'));
console.log('Mobile no overflow:',await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
console.log('HttpOnly:',!(await page.evaluate(()=>document.cookie)).includes('PHPSESSID'));
const blocked=await page.evaluate(()=>{window.__cspTest=0;const s=document.createElement('script');s.textContent='window.__cspTest=1';document.body.append(s);return window.__cspTest===0;});
console.log('Inline CSP blocked:',blocked);
console.log('Weather uses deterministic fixture; live provider availability not asserted');
const unexpected=errors.filter(x=>!x.includes("Executing inline script"));
console.log('BROWSER ERRORS BEFORE INTENTIONAL CSP PROBE',JSON.stringify(unexpected));
await browser.close();
if(unexpected.length) process.exitCode=1;
})();
