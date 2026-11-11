// Native dialog provides keyboard focus trapping and Escape-to-close.
const dialog = document.querySelector('#viet-bai');
const opener = document.querySelector('[data-community-open]');
if (dialog && opener && typeof dialog.showModal === 'function') {
    const show = () => {
        if (dialog.open) dialog.close();
        dialog.showModal();
        document.documentElement.classList.add('community-dialog-open');
    };
    opener.addEventListener('click', event => { event.preventDefault(); show(); });
    dialog.querySelector('[data-community-close]')?.addEventListener('click', event => {
        event.preventDefault();
        dialog.close();
    });
    dialog.addEventListener('close', () => {
        document.documentElement.classList.remove('community-dialog-open');
        opener.focus();
    });
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const box = dialog.getBoundingClientRect();
        if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close();
    });
    if (dialog.open) show();
}
