const KHOA = 'jadehub-yeu-thich';
export function docYeuThich() {
  try {
    const ids = JSON.parse(localStorage.getItem(KHOA) || '[]');
    return Array.isArray(ids) ? ids.filter(Number.isInteger) : [];
  } catch { return []; }
}
export function doiYeuThich(id) {
  const ids = docYeuThich();
  const moi = ids.includes(id) ? ids.filter(x => x !== id) : [...ids, id];
  try { localStorage.setItem(KHOA, JSON.stringify(moi)); } catch { /* Bộ nhớ có thể bị chặn. */ }
  window.dispatchEvent(new Event('jadehub:yeu-thich'));
  return moi.includes(id);
}
