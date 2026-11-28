/**
 * Courier Dashboard & Profile Interactive Controller
 * Lumpia Ubi Store
 */

(function () {
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) return meta.content;
        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    function formatNowTime() {
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        return hh + ':' + mm;
    }

    function appendBubble(thread, senderName, text, timeStr, isOutgoing, isSynced = false) {
        if (!thread) return;

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + (isSynced ? 'chat-bubble-synced ' : '') + (isOutgoing ? 'chat-bubble-outgoing' : 'chat-bubble-incoming');

        const label = document.createElement('div');
        label.className = 'chat-bubble-label';
        label.textContent = senderName;

        const body = document.createElement('div');
        body.textContent = text;

        const footer = document.createElement('div');
        footer.className = 'chat-bubble-footer';
        footer.textContent = timeStr;

        bubble.appendChild(label);
        bubble.appendChild(body);
        bubble.appendChild(footer);
        thread.appendChild(bubble);
        thread.scrollTop = thread.scrollHeight;
    }

    async function loadOrderMessages(orderId, thread) {
        if (!orderId || !thread) return;

        try {
            const res = await fetch(`/orders/${encodeURIComponent(orderId)}/chat`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (!Array.isArray(data.messages)) return;

            thread.querySelectorAll('.chat-bubble-synced').forEach(el => el.remove());

            data.messages.forEach(function (msg) {
                const isOutgoing = msg.sender_role === 'kurir';
                appendBubble(
                    thread,
                    msg.sender_name || (isOutgoing ? 'Kurir' : 'Customer'),
                    msg.message || '',
                    msg.time || '',
                    isOutgoing,
                    true
                );
            });
        } catch (e) {
            // Abaikan error jaringan sementara
        }
    }

    async function handleChatSubmit(form) {
        const input = form.querySelector('.chat-reply-input');
        const threadId = form.getAttribute('data-target-thread');
        const orderId = form.getAttribute('data-order-id');
        const thread = threadId ? document.getElementById(threadId) : null;
        if (!input || !thread) return;

        const text = input.value.trim();
        if (!text) return;

        if (orderId) {
            try {
                const res = await fetch(`/orders/${encodeURIComponent(orderId)}/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ message: text })
                });
                if (res.ok) {
                    input.value = '';
                    await loadOrderMessages(orderId, thread);
                    return;
                }
            } catch (e) {
                // Fallback ke bubble lokal jika request gagal
            }
        }

        appendBubble(thread, 'Catatan Kurir', text, formatNowTime(), true, false);
        input.value = '';
    }

    window.toggleEditForm = function () {
        const viewCard = document.getElementById('profileViewCard');
        const editCard = document.getElementById('profileEditCard');
        const btnToggle = document.getElementById('btnToggleEdit');
        if (!viewCard || !editCard) return;

        const isHidden = editCard.style.display === 'none' || window.getComputedStyle(editCard).display === 'none';
        editCard.style.display = isHidden ? 'block' : 'none';
        viewCard.style.display = isHidden ? 'none' : 'block';
        if (btnToggle) {
            btnToggle.style.display = isHidden ? 'none' : 'inline-flex';
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (document.body && document.body.dataset.hasErrors === '1') {
            window.toggleEditForm();
        }

        document.querySelectorAll('.courier-quick-note-form').forEach(function (form) {
            const threadId = form.getAttribute('data-target-thread');
            const orderId = form.getAttribute('data-order-id');
            const thread = threadId ? document.getElementById(threadId) : null;

            if (orderId && thread) {
                loadOrderMessages(orderId, thread);
                setInterval(function () {
                    loadOrderMessages(orderId, thread);
                }, 4000);
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                handleChatSubmit(form);
            });
        });
    });
})();