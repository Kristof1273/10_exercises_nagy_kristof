<?php

declare(strict_types=1);

$container = require __DIR__ . '/config/bootstrap.php';

$pageTitle = 'Add new product';
$activePage = 'add';
require __DIR__ . '/templates/header.php';
?>
        <div class="shadow-lg p-3 mb-5 bg-body rounded">
            <h2>Add new product</h2>
            <hr>
            <div id="products">
                <form id="product-form" method="POST" action="save-process.php">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="name" id="productName" maxlength="64">
                    </div>
                    <div class="mb-3">
                        <label for="productCategory" class="form-label">Product Category</label>
                        <input type="text" class="form-control" name="category" id="productCategory" maxlength="64">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3" name="description" maxlength="500"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
<?php require __DIR__ . '/templates/footer.php'; ?>
