const log = document.querySelector('[data-chat-url]');
if (log) {
    const lastId = (element) => element.querySelector('[data-message-id]:last-child')?.dataset.messageId;
    log.scrollTop = log.scrollHeight;
    let sending = false;

    async function refreshChat() {
        if (document.hidden || sending) return;
        try {
            const response = await fetch(log.dataset.chatUrl, { cache: 'no-store', redirect: 'error' });
            if (!response.ok) return;
            const next = new DOMParser().parseFromString(await response.text(), 'text/html').body;
            if (sending || lastId(next) === lastId(log)) return;
            const nearBottom = log.scrollHeight - log.scrollTop - log.clientHeight < 80;
            const oldTop = log.scrollTop;
            log.replaceChildren(...next.childNodes);
            log.scrollTop = nearBottom ? log.scrollHeight : oldTop;
        } catch {
            // Tiếp tục cập nhật khi kết nối trở lại.
        }
    }

    const form = document.querySelector('.social-chat-compose');
    const status = form.querySelector('[data-chat-status]');
    const button = form.querySelector('button[type="submit"]');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (sending) return;
        sending = true;
        button.disabled = true;
        status.textContent = 'Đang gửi…';
        const payload = new FormData(form);
        const fields = form.querySelectorAll('textarea, input[type="file"]');
        fields.forEach((field) => { field.disabled = true; });

        try {
            const response = await fetch(form.action, { method: 'POST', body: payload });
            const html = await response.text();
            if (response.status === 422) {
                status.textContent = new DOMParser().parseFromString(html, 'text/html').body.textContent;
                return;
            }
            const page = new DOMParser().parseFromString(html, 'text/html');
            const nextLog = page.querySelector('[data-chat-url]');
            if (!response.ok || !response.redirected || !nextLog) {
                status.textContent = 'Chưa xác nhận được tin đã gửi. Hãy kiểm tra cuộc trò chuyện trước khi gửi lại.';
                return;
            }
            log.replaceChildren(...nextLog.childNodes);
            log.scrollTop = log.scrollHeight;
            const nextList = page.querySelector('.social-chat-list');
            if (nextList) document.querySelector('.social-chat-list').replaceChildren(...nextList.childNodes);
            form.reset();
            status.textContent = 'Đã gửi.';
        } catch {
            status.textContent = 'Mất kết nối. Hãy kiểm tra cuộc trò chuyện trước khi gửi lại.';
        } finally {
            sending = false;
            button.disabled = false;
            fields.forEach((field) => { field.disabled = false; });
        }
    });

    async function scheduleRefresh() {
        await refreshChat();
        window.setTimeout(scheduleRefresh, 4000);
    }
    window.setTimeout(scheduleRefresh, 4000);
}
