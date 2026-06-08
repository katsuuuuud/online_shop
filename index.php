<?php
require_once 'data/products.php';

$active_category    = isset($_GET['cat']) ? $_GET['cat'] : $categories[0];
$current_products   = $products[$active_category] ?? [];
$current_icons      = $emojis[$active_category]   ?? array_fill(0, 6, '📦');

require_once 'includes/header.php';
?>

<div class="wrapper">

  <!-- Sidebar: категории -->
  <aside>
    <p class="sidebar-title">Категории</p>
    <ul class="cat-list">
      <?php foreach ($categories as $cat): ?>
        <li>
          <a href="?cat=<?= urlencode($cat) ?>"
             class="<?= $cat === $active_category ? 'active' : '' ?>">
            <span class="cat-dot"></span>
            <?= htmlspecialchars($cat) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </aside>

  <!-- Основной контент: товары -->
  <main>
    <div class="section-head">
      <h1><?= htmlspecialchars($active_category) ?></h1>
      <span class="count"><?= count($current_products) ?> товаров</span>
    </div>

    <div class="grid">
      <?php foreach ($current_products as $i => $product):
        $tag_class = match($product['tag']) {
          'Хит'     => 'tag-hit',
          'Новинка' => 'tag-new',
          'Скидка'  => 'tag-sale',
          default   => '',
        };
      ?>
      <div class="card">

        <?php if ($product['tag']): ?>
          <span class="tag <?= $tag_class ?>">
            <?= htmlspecialchars($product['tag']) ?>
          </span>
        <?php endif; ?>

        <div class="card-img"><?= $current_icons[$i] ?? '📦' ?></div>

        <div class="card-name"><?= htmlspecialchars($product['name']) ?></div>

        <div class="card-footer">
          <span class="price">
            <?= number_format($product['price'], 0, '.', ' ') ?> ₽
          </span>
          <a href="#" class="btn-cart" title="В корзину">+</a>
        </div>

      </div>
      <?php endforeach; ?>
    </div>
  </main>

</div>

<?php require_once 'includes/footer.php'; ?>
