function onAddToCartClick(productId) {
    fetch('/?action=add_to_cart&productId=' + encodeURIComponent(productId))
        .then(() => {
            alert('Товар добавлен в корзину');
        });
}

function onRemoveFromCartClick(productId) {
    fetch('/?page=cart&remove=' + encodeURIComponent(productId))
        .then(() => {
            window.location.reload();
        });
}

function onClearCartClick() {
    fetch('/?page=cart&clear=1')
        .then(() => {
            window.location.reload();
        });
}

function onMakeOrderClick() {
    fetch('/?page=cart&make_order=1')
        .then(() => {
            document.querySelector('.order-form-modal').style.display = 'flex';
        });
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