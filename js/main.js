function onAddToCartClick(productId) {
    const formData = new FormData();
    formData.append('productId', productId);

    fetch('/cart/add', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                alert('Товар добавлен в корзину');
            }
        });
}


function onRemoveFromCartClick(productId) {
    const formData = new FormData();
    formData.append('productId', productId);

    fetch('/cart/remove', {
        method: 'POST',
        body: formData
    })
        .then(() => {
            window.location.reload();
        });
}

function onClearCartClick() {
    fetch('/cart/clear', {
        method: 'POST',
    })
        .then(() => {
            window.location.reload();
        });
}

function onMakeOrderClick() {
    document.querySelector('.order-form-modal').style.display = 'flex';
}

function onSubmitOrderForm(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    fetch('/order/create', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Заказ успешно оформлен!');
                window.location.reload();
            } else {
                alert(data.message || 'Ошибка при оформлении заказа.');
            }
        })
        .catch(() => {
            alert('Не удалось отправить заказ.');
        });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', () => {
            onAddToCartClick(button.getAttribute('data-product-id'));
        });
    });

    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', () => {
            onRemoveFromCartClick(button.getAttribute('data-product-id'));
        });
    });

    const clearButton = document.querySelector('.clear-cart');
    if (clearButton) {
        clearButton.addEventListener('click', () => {
            onClearCartClick();
        });
    }

    const makeOrderButton = document.querySelector('.make-order');
    if (makeOrderButton) {
        makeOrderButton.addEventListener('click', () => {
            onMakeOrderClick();
        });
    }

    const orderForm = document.getElementById('order-form');
    if (orderForm) {
        orderForm.addEventListener('submit', onSubmitOrderForm);
    }
});