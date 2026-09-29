<?php

require __DIR__ . '/vendor/autoload.php';

use MEPDatabaseTask\models\ProductForm;
use MEPDatabaseTask\models\Product;
use MEPDatabaseTask\repositories\ProductRepository;

if (!empty($_POST)) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $form = new ProductForm(
        $_POST['name'] ?? '',
        $_POST['category'] ?? '',
        $_POST['description'] ?? '',
        $id
    );

    if ($form->validate() && $form->id !== null) {
        $product = new Product($form->name, $form->category, $form->description, $form->id);

        $pdo = new \PDO("mysql:host=db;dbname=mep_db_task;charset=utf8", "root", "root");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $repository = new ProductRepository($pdo);
        $repository->update($product);
    } else {
        header('Location: index.php?error=invalid_data', true, 301);
        exit();
    }
}

// This part of the code will redirect back the user to the "main page". Status Code 301 means a permanent redirect
header('Location: index.php', true, 301);
exit();