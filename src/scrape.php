<?php
require 'autoload.php';

use Classes\Parser;

// suppress any warnings
libxml_use_internal_errors(true);

$parser = new Parser();
try {
    $parser->load('https://wltest.dns-systems.net/');
    $products = json_decode($parser->getProducts(), true);

    foreach($products as $product) {
        echo $product["title"] . "\t\t£" . $product["price"] . "\n";

        // uncomment the following line to see all fields stored for the product
        // var_dump($product);
    }
} catch(\Exception $e) {
    echo "Failed to scrape: " . $e->getMessage();
}
