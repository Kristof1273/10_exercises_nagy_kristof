<?php

declare(strict_types=1);

use MEPDatabaseTask\models\Product;
use MEPDatabaseTask\models\ProductForm;
use MEPDatabaseTask\services\ProductServiceInterface;

$container = require __DIR__ . '/config/bootstrap.php';

// 303 See Other: after a POST the browser loads the target page with GET.
$redirect = static function (string $target): never {
    header('Location: ' . $target, true, 303);
    exit();
};

if (empty($_POST)) {
    $redirect('index.php');
}

$input = static fn (string $key): string => is_string($_POST[$key] ?? null) ? $_POST[$key] : '';

$id = (int) $input('id');
$form = new ProductForm($input('name'), $input('category'), $input('description'), $id > 0 ? $id : null);

if (!$form->validate() || $form->id === null) {
    $redirect('index.php?error=invalid_data');
}

$productService = $container->get(ProductServiceInterface::class);

if ($productService->get($form->id) === null) {
    $redirect('index.php?error=not_found');
}

$product = new Product($form->name, $form->category, $form->description, $form->id);

$productService->update($product);

$redirect('index.php?status=updated');
