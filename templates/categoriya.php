<nav class="nav">
    <ul class="nav__list container">
        <?php foreach ($categories as $item): ?>
            <li class="nav__item <?php if ($category_id === $item['id']): ?> nav__item--current <?php endif; ?>">
                <a href="all-lots.php?id=<?= $item['id'] ?>">
                    <?= htmlspecialchars($item['name']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>