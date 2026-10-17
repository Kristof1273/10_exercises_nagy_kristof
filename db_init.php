<?php

declare(strict_types=1);

/*
 * Creates the `products` table and fills it with sample data.
 *
 * Run it once from the browser:   http://localhost:8080/db_init.php
 * or from the command line:       docker compose exec app php db_init.php
 *
 * It is safe to run again: the table is only created when it is missing, and
 * the sample products are only inserted into an empty table, so existing data
 * is never touched. The connection comes from the DI container (settings in .env).
 */

use MEPDatabaseTask\database\ConnectionInterface;

$container = require __DIR__ . '/config/bootstrap.php';
$pdo = $container->get(ConnectionInterface::class)->getPdo();

// Same structure as in the SQL/PDO module (Module 10).
$pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS products (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(64) NOT NULL,
        category VARCHAR(64) NOT NULL,
        description TEXT,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL);

// [name, category, description] — 25 products, so the list has 3 pages of 10.
$samples = [
    ['Laptop Pro 14', 'Electronics', '14-inch laptop with 16 GB RAM and a 512 GB SSD.'],
    ['Wireless Mouse', 'Electronics', 'Ergonomic mouse with a silent click and USB-C charging.'],
    ['Mechanical Keyboard', 'Electronics', 'Tenkeyless keyboard with brown switches.'],
    ['27" Monitor', 'Electronics', '2560×1440 IPS panel, 75 Hz.'],
    ['USB-C Hub', 'Electronics', null],
    ['Noise Cancelling Headphones', 'Electronics', 'Over-ear headphones with 30 hours of battery life.'],
    ['Clean Code', 'Books', 'Robert C. Martin — a handbook of agile software craftsmanship.'],
    ['The Pragmatic Programmer', 'Books', 'David Thomas, Andrew Hunt — 20th anniversary edition.'],
    ['Design Patterns', 'Books', 'Elements of reusable object-oriented software.'],
    ['Refactoring', 'Books', 'Martin Fowler — improving the design of existing code.'],
    ['PHP 8 Objects, Patterns, and Practice', 'Books', null],
    ['Espresso Beans 1 kg', 'Groceries', 'Medium roast, 70% arabica, 30% robusta.'],
    ['Green Tea', 'Groceries', '20 tea bags of Japanese sencha.'],
    ['Dark Chocolate 70%', 'Groceries', '100 g bar.'],
    ['Olive Oil 0.75 l', 'Groceries', 'Extra virgin, cold pressed.'],
    ['Desk Lamp', 'Home & Office', 'LED lamp with adjustable colour temperature.'],
    ['Standing Desk', 'Home & Office', 'Electric height adjustment between 70 and 120 cm.'],
    ['Office Chair', 'Home & Office', 'Mesh back with lumbar support.'],
    ['Notebook A5', 'Home & Office', 'Dotted, 160 pages.'],
    ['Fountain Pen', 'Home & Office', null],
    ['Running Shoes', 'Sports', 'Lightweight shoes for road running.'],
    ['Yoga Mat', 'Sports', 'Non-slip, 6 mm thick.'],
    ['Water Bottle', 'Sports', 'Stainless steel, keeps drinks cold for 24 hours.'],
    ['Bicycle Helmet', 'Sports', 'Size M, with a rear LED light.'],
    ['Tennis Racket', 'Sports', 'Graphite frame, 300 g.'],
];

$existing = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$inserted = 0;

if ($existing === 0) {
    $statement = $pdo->prepare(
        'INSERT INTO products (name, category, description) VALUES (:name, :category, :description)'
    );

    $pdo->beginTransaction();
    foreach ($samples as [$name, $category, $description]) {
        $statement->execute([':name' => $name, ':category' => $category, ':description' => $description]);
        $inserted++;
    }
    $pdo->commit();
}

$total = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$message = $inserted > 0
    ? "The products table is ready: {$inserted} sample products were inserted."
    : "The products table already existed with {$existing} products; nothing was changed.";

if (PHP_SAPI === 'cli') {
    echo $message . PHP_EOL . "Products in the table: {$total}" . PHP_EOL;
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Database initialisation</title>
</head>
<body class="pt-5">
    <div class="container">
        <div class="shadow-lg p-3 mb-5 bg-body rounded">
            <h1>Database initialisation</h1>
            <div class="alert alert-success mt-3"><?= htmlspecialchars($message) ?></div>
            <p>Products in the table: <strong><?= $total ?></strong></p>
            <a class="btn btn-primary" href="index.php">Go to the product list</a>
        </div>
    </div>
</body>
</html>
