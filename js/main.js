
function onAddToCartClick(productId) {
    fetch('/?action=add_to_cart&productId=' + encodeURIComponent(productId))
        .then(() => {
            alert('Товар добавлен в корзину');
        });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', () => {
            onAddToCartClick(button.getAttribute('data-product-id'));
        });
    });
});