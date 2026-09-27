// Hàm dùng chung để tải dữ liệu JSON bằng Fetch API.
// Sử dụng async/await để xử lý bất đồng bộ.
// Kiểm tra response.ok trước khi đọc dữ liệu.
// Nếu có lỗi HTTP, lỗi sẽ được đưa về catch ở nơi gọi hàm.

export async function taiJSON(url, tuyChon = {}) {
  const response = await fetch(url, tuyChon);

  if (!response.ok) {
    throw new Error(
      `HTTP ${response.status} khi tải ${url}`
    );
  }

  return response.json();
}