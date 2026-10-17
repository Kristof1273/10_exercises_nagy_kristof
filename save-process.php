<?php

declare(strict_types=1);

use MEPDatabaseTask\models\Product;
use MEPDatabaseTask\models\ProductForm;
use MEPDatabaseTask\services\ProductServiceInterface;

$container = require __DIR__ . '/config/bootstrap.php';

// 303 See Other: after a POST the browser loads the target page with GET,
// so refreshing the list cannot submit the form again.
$redirect = static function (string $target): never {
    header('Location: ' . $target, true, 303);
    exit();
};

if (empty($_POST)) {
    $redirect('index.php');
}

$input = static fn (string $key): string => is_string($_POST[$key] ?? null) ? $_POST[$key] : '';

$form = new ProductForm($input('name'), $input('category'), $input('description'));

if (!$form->validate()) {
    $redirect('index.php?error=invalid_data');
}

$product = new Product($form->name, $form->category, $form->description);

$container->get(ProductServiceInterface::class)->save($product);

$redirect('index.php?status=saved');
