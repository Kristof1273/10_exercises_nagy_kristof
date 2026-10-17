<?php
/**
 * Page header shared by the product pages.
 *
 * @var string $pageTitle  title of the current page
 * @var string $activePage 'list' | 'add' | 'update'
 * @var \Psr\Container\ContainerInterface $container
 */

$dataLayers = [
    'pdo' => 'PDO · ProductService',
    'dbal' => 'Doctrine DBAL · DbalProductService',
];
$dataLayer = $dataLayers[$container->get('db.layer')] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title><?= htmlspecialchars($pageTitle) ?> · PHP Mentored Education Program</title>
</head>
<body class="pt-5">
    <div class="container">
        <div class="shadow-lg p-3 mb-5 bg-body rounded">
            <h1>PHP Mentored Education Program, Module 11: Advanced PHP</h1>
        </div>

        <div class="shadow-lg p-3 mb-5 bg-body rounded">
            <ul class="nav justify-content-center">
                <li class="nav-item">
                    <a class="nav-link<?= $activePage === 'list' ? ' active disabled' : '' ?>"<?= $activePage === 'list' ? ' aria-current="page"' : '' ?> href="index.php">List Page</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $activePage === 'add' ? ' active disabled' : '' ?>"<?= $activePage === 'add' ? ' aria-current="page"' : '' ?> href="add-product-page.php">Add new Product</a>
                </li>
            </ul>
            <p class="text-center text-muted small mb-0">Data layer: <?= htmlspecialchars($dataLayer) ?> (DB_LAYER in .env)</p>
        </div>
