<?php
namespace Classes;

class Parser
{
    private $xpath;

    public function load($url)
    {
        $httpClient = new \GuzzleHttp\Client();
        try {
            $response = $httpClient->get($url);
            $html = (string) $response->getBody();
        } catch(ConnectException $exception) {
            throw new \Exception("Failed get url content");
        }
        
        $this->xpath = $this->xPath($html);
        return $this->xpath;
    }

    public function xPath(string $html)
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        return new \DOMXPath($doc);
    }

    public function pricingTable()
    {
        if(!$this->xpath) {
            throw new Exception("No content, did you forget to load the url");
        }

        $pricingTable = null;
        $subNodes = $this->xpath->query('//*[@id="subscriptions"]//div');
        foreach($subNodes as $sub) {
            $h2 = $sub->getElementsByTagName("h2");
            if(count($h2)>0 && $h2[0]->nodeValue == "Annual Subscription Packages") {
                
                $nodes = $this->xpath->query('.//div[@class="pricing-table"]//div', $sub);
                if($nodes) {
                    $pricingTable = $nodes[0];
                }
                break;
            }
        }
        return $pricingTable;
    }

    public function getProducts()
    {
        $products = [];
        
        $pricingTable = $this->pricingTable();
        if(!$pricingTable) {
            throw new Exception("Pricing table not found in the content");
        }

        foreach($pricingTable->childNodes as $child) {
            $product = [];
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $products[] = $this->readProduct($child);
            }
        }
        usort($products, function($a, $b) { 
            return $a["price"] < $b["price"];
        });
        return json_encode($products);
    }

    public function readProduct($child)
    {
        $discount = $this->getPathValue('.//div[@class="package-price"]//p', $child);
        preg_match('/[0-9\.]+/', $discount, $matches);
        $discount = (count($matches) > 0 ? $matches[0]: 0);
        
        return [
            "title" => $this->getPathValue('.//h3', $child),
            "name" => $this->getPathValue('.//div[@class="package-name"]', $child),
            "description" => $this->getPathValue('.//div[@class="package-description"]', $child),
            "price" => trim($this->getPathValue('.//div[@class="package-price"]//span', $child), '£'),
            "discount" => $discount,
        ];
    }

    private function getPathValue($path, $relativeNode = null)
    {
        $nodes = $this->xpath->query($path, $relativeNode);
        if($nodes && $nodes->length > 0) {
            return $nodes[0]->nodeValue;
        }
        return null;
    }
}