<?php
declare(strict_types=1);

use MEPDatabaseTask\fruits\Apple;
use MEPDatabaseTask\fruits\Banana;
use MEPDatabaseTask\fruits\Colour;
use MEPDatabaseTask\fruits\Dates;
use MEPDatabaseTask\fruits\Fruit;
use MEPDatabaseTask\fruits\Mango;
use MEPDatabaseTask\fruits\PeelableInterface;
use MEPDatabaseTask\fruits\Size;
use MEPDatabaseTask\storage\Box;
use MEPDatabaseTask\storage\BoxInterface;
use MEPDatabaseTask\storage\exception\BoxFullException;
use MEPDatabaseTask\storage\exception\FruitNotAllowedException;
use MEPDatabaseTask\storage\ExoticBox;

$container = require __DIR__ . '/config/bootstrap.php';

// Works from the command line (php box.php) and from the browser (/box.php).
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

/**
 * Tries to put a fruit into a box and tells the user what happened.
 */
function addToBox(BoxInterface $box, Fruit $fruit): void
{
    try {
        $box->add($fruit);
        echo "  + {$fruit->getType()} bekerült a dobozba." . PHP_EOL;
    } catch (BoxFullException $e) {
        echo "  ! A doboz megtelt (legfeljebb {$e->getCapacity()} gyümölcs fér bele), "
            . "{$fruit->getType()} kimaradt." . PHP_EOL;
    } catch (FruitNotAllowedException $e) {
        echo "  ! {$fruit->getType()} nem tehető ebbe a dobozba: csak egzotikus gyümölcs kerülhet bele." . PHP_EOL;
    }
}

// --- Box: one shared instance from the DI container, configurable limit -------

echo 'Általános doboz (legfeljebb 3 gyümölcs):' . PHP_EOL;

// The container builds the Box and injects the logger into it.
/** @var Box $box */
$box = $container->get(Box::class);
$box->setCapacity(3);

addToBox($box, new Apple(Size::Medium, Colour::Red, 95));
addToBox($box, new Banana(Size::Large, Colour::Yellow, 105));
addToBox($box, new Dates(Size::Small, Colour::Brown, 20));
addToBox($box, new Mango(Size::Large, Colour::Orange, 200)); // one too many

echo PHP_EOL;
echo 'Gyümölcsök száma: ' . $box->getCount() . PHP_EOL;
echo 'Összes kalória: ' . $box->getTotalCalories() . PHP_EOL;
echo 'A második gyümölcs típusa: ' . $box->getFruitAt(1)?->getType() . PHP_EOL;
echo 'Piros gyümölcsök száma: ' . count($box->getFruitByColour(Colour::Red)) . PHP_EOL;
echo 'Ugyanaz a példány: ' . ($container->get(Box::class) === $box ? 'igen' : 'nem') . PHP_EOL;

foreach ($box->getFruits() as $fruit) {
    if ($fruit instanceof PeelableInterface) {
        $fruit->peel();
        echo "Meghámozva: {$fruit->getType()}" . PHP_EOL;
    }
}

// --- ExoticBox: a new, independent box built by the container ---------------

echo PHP_EOL . 'Egzotikus doboz (legfeljebb 2 gyümölcs):' . PHP_EOL;

// make() always builds a new object; the logger is still injected automatically.
/** @var ExoticBox $exoticBox */
$exoticBox = $container->make(ExoticBox::class, ['capacity' => 2]);

addToBox($exoticBox, new Dates(Size::Small, Colour::Brown, 20));
addToBox($exoticBox, new Apple(Size::Small, Colour::Green, 80));     // not exotic
addToBox($exoticBox, new Mango(Size::Large, Colour::Orange, 200));
addToBox($exoticBox, new Mango(Size::Medium, Colour::Yellow, 150));  // one too many

echo PHP_EOL;
echo 'Egzotikus gyümölcsök száma: ' . $exoticBox->getCount() . PHP_EOL;
echo 'Az általános dobozban továbbra is: ' . $box->getCount() . PHP_EOL;

$box->empty();
echo 'Ürítés után az általános dobozban: ' . $box->getCount() . PHP_EOL;

echo PHP_EOL . 'Minden getCount() hívás naplózva: logs/app.log, valamint stderr '
    . '(parancssorban a terminál, Dockerben: docker compose logs app).' . PHP_EOL;
