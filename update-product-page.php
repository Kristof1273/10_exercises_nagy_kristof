<?php

declare(strict_types=1);

use MEPDatabaseTask\services\ProductServiceInterface;

$container = require __DIR__ . '/config/bootstrap.php';

$id = isset($_GET['id']) && is_string($_GET['id']) ? (int) $_GET['id'] : 0;

$product = $id > 0 ? $container->get(ProductServiceInterface::class)->get($id) : null;

if ($product === null) {
    header('Location: index.php?error=not_found', true, 303);
    exit;
}

$pageTitle = 'Update product';
$activePage = 'update';
require __DIR__ . '/templates/header.php';
?>
        <div class="shadow-lg p-3 mb-5 bg-body rounded">
            <h2>Update product</h2>
            <hr>
            <div id="products">
                <form id="product-form" method="POST" action="update-process.php">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $product->id) ?>">

                    <div class="mb-3">
                        <label for="productId" class="form-label">Product ID (Disabled)</label>
                        <input type="text" class="form-control" id="productId" value="<?= htmlspecialchars((string) $product->id) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="name" id="name" maxlength="64" value="<?= htmlspecialchars($product->name) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Product Category</label>
                        <input type="text" class="form-control" name="category" id="category" maxlength="64" value="<?= htmlspecialchars($product->category) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3" name="description" maxlength="500"><?= htmlspecialchars($product->description) ?></textarea>
                    </div>
                    <p class="text-muted">
                        Created: <?= htmlspecialchars($product->createdAt ?? '') ?>
                        <?php if ($product->updatedAt !== null): ?>
                            · Last updated: <?= htmlspecialchars($product->updatedAt) ?>
                        <?php endif; ?>
                    </p>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
<?php require __DIR__ . '/templates/footer.php'; ?>
