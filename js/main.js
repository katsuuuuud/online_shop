
const api = {
    async request(method, path, body = null) {
        const opts = {
            method,
            headers: { 'Content-Type': 'application/json' },
        };
        if (body) opts.body = JSON.stringify(body);

        const res  = await fetch(path, opts);
        const data = await res.json();

        if (!res.ok) throw new Error(data.error || 'Ошибка запроса');
        return data;
    },

    get:    (path)        => api.request('GET',    path),
    post:   (path, body)  => api.request('POST',   path, body),
    patch:  (path, body)  => api.request('PATCH',  path, body),
    delete: (path, body)  => api.request('DELETE', path, body),
};
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('toast--visible'));
    setTimeout(() => {
        toast.classList.remove('toast--visible');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3000);
}
function updateCartDOM(items, total) {
    const list    = document.querySelector('.cart-list');
    const summary = document.querySelector('.cart-summary');

    if (!list) return;

    if (!items.length) {
        list.closest('.cart-content')?.replaceWith(
            Object.assign(document.createElement('p'), { textContent: 'Корзина пуста.' })
        );
        return;
    }

    list.innerHTML = items.map(item => `
        <li class="cart-item" data-id="${item.productId}">
            <span>${item.name} — ${item.quantity} шт.</span>
            <button type="button" class="btn-cart remove-from-cart"
                    data-product-id="${item.productId}">Удалить</button>
        </li>
    `).join('');

    if (summary) {
        summary.textContent = `Итого: ${total.toFixed(2)}`;
    }

    bindRemoveButtons();
}

async function onAddToCart(productId) {
    try {
        const { data, total } = await api.post('/api/cart', { productId });
        updateCartCount(data.length);
        showToast('Товар добавлен в корзину');
    } catch (e) {
        showToast(e.message, 'error');
    }
}

async function onRemoveFromCart(productId) {
    try {
        const { data, total } = await api.delete(`/api/cart/${productId}`);
        updateCartDOM(data, total);
        updateCartCount(data.length);
    } catch (e) {
        showToast(e.message, 'error');
    }
}

async function onClearCart() {
    try {
        const { data } = await api.delete('/api/cart');
        updateCartDOM(data, 0);
        updateCartCount(0);
    } catch (e) {
        showToast(e.message, 'error');
    }
}

function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(el => {
        el.textContent = count;
        el.hidden = count === 0;
    });
}
async function onAuthSubmit(event) {
    event.preventDefault();

    const form     = event.target;
    const isLogin  = form.dataset.mode === 'login';
    const endpoint = isLogin ? '/api/auth/login' : '/api/auth/register';

    const body = Object.fromEntries(new FormData(form));
    const submitBtn = form.querySelector('[type=submit]');
    submitBtn.disabled = true;

    try {
        await api.post(endpoint, body);
        const next = new URLSearchParams(location.search).get('next') || '/';
        location.href = next;
    } catch (e) {
        showError(form, e.message);
        submitBtn.disabled = false;
    }
}

function showError(form, message) {
    let box = form.closest('.auth-section').querySelector('.alert');
    if (!box) {
        box = document.createElement('div');
        box.className = 'alert alert-error';
        form.before(box);
    }
    box.textContent = message;
}

function onMakeOrder() {
    document.querySelector('.order-form-modal').style.display = 'flex';
}

async function onSubmitOrderForm(event) {
    event.preventDefault();

    const submitBtn = event.target.querySelector('[type=submit]');
    submitBtn.disabled = true;

    try {
        const { data } = await api.post('/api/orders', {});
        showToast(`Заказ успешно оформлен!`);
        document.querySelector('.order-form-modal').style.display = 'none';

        updateCartDOM([], 0);
        updateCartCount(0);
    } catch (e) {
        showToast(e.message, 'error');
        submitBtn.disabled = false;
    }
}

async function onProfileUpdate(event) {
    event.preventDefault();

    const form = event.target;
    const body = Object.fromEntries(new FormData(form));
    const submitBtn = form.querySelector('[type=submit]');
    submitBtn.disabled = true;

    try {
        await api.patch('/api/profile', body);
        showToast('Профиль успешно обновлён');
    } catch (e) {
        showToast(e.message, 'error');
    } finally {
        submitBtn.disabled = false;
    }
}

function bindRemoveButtons() {
    document.querySelectorAll('.remove-from-cart').forEach(btn => {
        const fresh = btn.cloneNode(true);
        btn.replaceWith(fresh);
        fresh.addEventListener('click', () => onRemoveFromCart(+fresh.dataset.productId));
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => onAddToCart(+btn.dataset.productId));
    });

    bindRemoveButtons();

    document.querySelector('.clear-cart')
        ?.addEventListener('click', onClearCart);

    document.querySelector('.make-order')
        ?.addEventListener('click', onMakeOrder);

    document.getElementById('order-form')
        ?.addEventListener('submit', onSubmitOrderForm);

    document.querySelectorAll('.auth-form').forEach(form => {
        form.addEventListener('submit', onAuthSubmit);
    });
    
    document.querySelector('.profile-form')
        ?.addEventListener('submit', onProfileUpdate);
});