<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__FILE__) . '/../classes/Parser.php';
use Classes\Parser;

// suppress any warnings
libxml_use_internal_errors(true);

class ScraperTest extends TestCase
{
    public function testLoadUrl()
    {
        $parser = new Parser();
        $return = $parser->load('https://wltest.dns-systems.net/');
        $this->assertInstanceOf('DOMXPath', $return);
    }

    public function testLoadUrlFail()
    {
        $parser = new Parser();
        $return = null;
        try {
        $parser->load('https://someInvalidDomain/');
        } catch(\Exception $e) {}
        $this->assertEquals(null, $return);
    }

    public function testPriceTable()
    {
        $parser = new Parser();
        $parser->load('https://wltest.dns-systems.net/');
        $priceTable = $parser->pricingTable();
        
        $this->assertInstanceOf('DOMElement', $priceTable);
        $this->assertEquals('div', $priceTable->tagName);
    }

    public function testReadProduct()
    {
        $parser = new Parser();
        $parser->load('https://wltest.dns-systems.net/');
        $priceTable = $parser->pricingTable();
        $product = [];
        foreach($priceTable->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $product = $parser->readProduct($child);
            }
        }
        
        $this->assertEquals(5, count($product));
        $this->assertEquals('title', array_keys($product)[0]);
    }

    public function testReadProductPrice()
    {
        $parser = new Parser();
        $parser->load('https://wltest.dns-systems.net/');
        $priceTable = $parser->pricingTable();
        $product = [];
        foreach($priceTable->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $product = $parser->readProduct($child);
            }
        }
        
        $this->assertTrue(is_numeric($product["price"]));
    }

    public function testReadProductDiscount()
    {
        $parser = new Parser();
        $parser->load('https://wltest.dns-systems.net/');
        $priceTable = $parser->pricingTable();
        $product = [];
        foreach($priceTable->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $product = $parser->readProduct($child);
            }
        }
        
        $this->assertTrue(is_numeric($product["discount"]));
    }

    public function testReadProducts()
    {
        $parser = new Parser();
        $parser->load('https://wltest.dns-systems.net/');
        $products = json_decode($parser->getProducts(), true);
        
        $this->assertEquals(3, count($products));
    }

    public function testReadProductsSort()
    {
        $parser = new Parser();
        $parser->load('https://wltest.dns-systems.net/');
        $products = json_decode($parser->getProducts(), true);
        $price = 1000000000;
        foreach($products as $product) {
            $this->assertGreaterThan($product["price"], $price);
            $price = $product["price"];
        }
    }
}