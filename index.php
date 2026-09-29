<?php

require __DIR__ . '/vendor/autoload.php';


use MEPDatabaseTask\models\Product;
use MEPDatabaseTask\repositories\ProductRepository;


$host = 'db';
$dbName = 'mep_db_task';
$user = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

$repository = new ProductRepository($pdo);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$products = $repository->getAll($page);

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
    <title>PHP Mentored Education Program, Module 10: SQL, PDO</title>
</head>
<body class="pt-5">
    <div class="container">
        <div class="shadow-lg p-3 mb-5 bg-body rounded">
            <h1>PHP Mentored Education Program, Module 10: SQL, PDO</h1>
        </div>

        <div class="shadow-lg p-3 mb-5 bg-body rounded">
            <ul class="nav justify-content-center">
                <li class="nav-item">
                    <a class="nav-link active disabled" aria-current="page" href="index.php">List Page</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add-product-page.php">Add new Product</a>
                </li>
            </ul>
        </div>

        <div class="shadow-lg p-3 mb-5 bg-body rounded">
            <h2>Products</h2>
            <hr>
            <div id="products">
                
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">No products available.</div>
                <?php else: ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>name</th>
                                <th>category</th>
                                <th>description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product->id ?? '') ?></td>
                                    <td><?= htmlspecialchars($product->name) ?></td>
                                    <td><?= htmlspecialchars($product->category) ?></td>
                                    <td><?= htmlspecialchars($product->description) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
            crossorigin="anonymous"></script>
</body>
</html>