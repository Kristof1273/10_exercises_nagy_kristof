<?php

declare(strict_types=1);

use MEPDatabaseTask\services\ProductServiceInterface;

$container = require __DIR__ . '/config/bootstrap.php';

/** @var ProductServiceInterface $productService */
$productService = $container->get(ProductServiceInterface::class);

$total = $productService->count();
$pageCount = max(1, (int) ceil($total / ProductServiceInterface::PAGE_SIZE));

$query = static fn (string $key): string => is_string($_GET[$key] ?? null) ? $_GET[$key] : '';

$page = $query('page') !== '' ? (int) $query('page') : 1;
$page = min(max(1, $page), $pageCount);

$products = $productService->getAll($page);

// Messages are chosen by a fixed key, so the URL cannot inject its own text.
$errors = [
    'invalid_data' => 'The product was not saved: the name and the category are required (max. 64 characters each), '
        . 'the description can be at most 500 characters long.',
    'not_found' => 'This product does not exist.',
];
$successes = [
    'saved' => 'The product has been saved.',
    'updated' => 'The product has been updated.',
];
$error = $errors[$query('error')] ?? null;
$success = $successes[$query('status')] ?? null;

$pageTitle = 'Products';
$activePage = 'list';
require __DIR__ . '/templates/header.php';
?>
        <div class="shadow-lg p-3 mb-5 bg-body rounded">
            <h2>Products</h2>
            <hr>

            <?php if ($error !== null): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success !== null): ?>
                <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div id="products">
                <?php if ($products === []): ?>
                    <div class="alert alert-info">No products available.</div>
                <?php else: ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) $product->id) ?></td>
                                    <td><?= htmlspecialchars($product->name) ?></td>
                                    <td><?= htmlspecialchars($product->category) ?></td>
                                    <td><?= nl2br(htmlspecialchars($product->description)) ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($product->createdAt ?? '') ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($product->updatedAt ?? '—') ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="update-product-page.php?id=<?= urlencode((string) $product->id) ?>">Update</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p class="text-muted">
                        <?= $total ?> product(s), page <?= $page ?> of <?= $pageCount ?>
                    </p>

                    <?php if ($pageCount > 1): ?>
                        <nav aria-label="Product pages">
                            <ul class="pagination justify-content-center">
                                <li class="page-item<?= $page === 1 ? ' disabled' : '' ?>">
                                    <a class="page-link" href="index.php?page=<?= $page - 1 ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                                    <li class="page-item<?= $i === $page ? ' active' : '' ?>">
                                        <a class="page-link" href="index.php?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item<?= $page === $pageCount ? ' disabled' : '' ?>">
                                    <a class="page-link" href="index.php?page=<?= $page + 1 ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
<?php require __DIR__ . '/templates/footer.php'; ?>
