<?php

ini_set('error_reporting', E_ALL);
ini_set("display_errors", "1");
ini_set('memory_limit', '2048M');
require_once "vendor/autoload.php";
require 'app/bootstrap.php';

use Magento\Framework\App\Bootstrap;
use  Spatie\SimpleExcel\SimpleExcelReader;





$pathToFile = BP . DS . 'JustReturnsDataEntry.xlsx';
$pathToFile = realpath($pathToFile);
$Headers = SimpleExcelReader::create($pathToFile)->getHeaders();
$Rows = SimpleExcelReader::create($pathToFile)->getRows();


$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');




function createProduct($row)
{


    global $objectManager;

    $category_factory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
    $product = $objectManager->create('\Magento\Catalog\Model\Product');

    $store_Id = 1;

    // catagory name
    $cat_val = $row["SubSubCategory"];
    $cat_val = trim($cat_val);
    $categoryName = ucfirst($cat_val);
    $site_url = strtolower($cat_val);
    $clean_url = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($site_url))))));
    $category = $category_factory->create();

    $SKU = trim($row["SKU"]);
    $productPrice = $row["Price"];
    $productName = trim($row["ProductName"]);
    $productDescribition = $row["Describition"];
    $category_id= [];



    $cate = $category->getCollection()
        ->addAttributeToFilter('name', $categoryName)
        ->getFirstItem();

    if ($cate->getId()) {
        $category_id[]= $cate->getId();
        $category_id = array_merge($category_id , $cate->getParentIds());
    }

//    var_dump($categoryName);
//    var_dump($category_id);
//    return;
    try {

        $product->setWebsiteIds(array(1));
        $product->setAttributeSetId(4);
        $product->setTypeId('simple');
        $product->setCreatedAt(strtotime('now'));
        $product->setName($productName);
        $product->setSku($SKU);
        $product->setWeight(10);
        $product->setStatus(1);
        $product->setCategoryIds($category_id);
        $product->setTaxClassId(2); // (0 - none, 1 - default, 2 - taxable, 4 - shipping)
        $product->setVisibility(4); // catalog and search visibility
        $product->setColor(24);
        $product->setPrice($productPrice) ;
        $product->setCost(1);
        $product->setMetaTitle($productName);
        $product->setMetaKeyword($productName);
        $product->setMetaDescription($productDescribition);
        $product->setDescription($productDescribition);
        $product->setShortDescription($productDescribition);

        $product->setStockData(
            array(
                'use_config_manage_stock' => 0,
                'manage_stock' => 1, // manage stock
                'min_sale_qty' => 1, // Shopping Cart Minimum Qty Allowed
                'max_sale_qty' => 2, // Shopping Cart Maximum Qty Allowed
                'is_in_stock' => 1, // Stock Availability of product
                'qty' => (int) 30
            )
        );


        $product->save();
        echo "Upload simple product  SKU:: (" . $SKU . ")  id:: ".$product->getId()."\n";
    }
    catch(Exception $e)
    {
        echo 'Something failed for product import (' . $SKU .") ". $productName . PHP_EOL;
        print_r($e);
    }

}


foreach ($Rows as $row)
{

    createProduct($row);
//    var_dump($row);
}
