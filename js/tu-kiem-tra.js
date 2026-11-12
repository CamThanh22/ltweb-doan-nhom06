/* Phản hồi kiểm tra lỗi tại trình duyệt; máy chủ vẫn là nguồn xác thực bắt buộc. */
document.addEventListener('DOMContentLoaded', () => {
  for (const form of document.querySelectorAll('form')) {
    for (const field of form.querySelectorAll('input,textarea,select')) {
      if (field.type === 'hidden' || field.type === 'submit') continue;
      const output = document.createElement('small');
      output.className = 'loi-truong'; output.setAttribute('aria-live', 'polite');
      field.insertAdjacentElement('afterend', output);
      const validate = () => {
        if (!field.validity.valid) {
          output.textContent = field.validity.valueMissing ? 'Vui lòng nhập thông tin bắt buộc.' :
              field.validity.typeMismatch ? 'Thông tin chưa đúng định dạng.' :
              field.validity.rangeUnderflow || field.validity.rangeOverflow ? 'Giá trị nằm ngoài phạm vi cho phép.' :
              field.validity.tooLong ? 'Nội dung quá dài.' : 'Vui lòng kiểm tra dữ liệu.';
          field.setAttribute('aria-invalid', 'true');
        } else { output.textContent = ''; field.removeAttribute('aria-invalid'); }
      };
      field.addEventListener('blur', validate);
      field.addEventListener('input', validate);
    }
  }
});
