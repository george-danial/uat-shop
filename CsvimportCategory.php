<?php

ini_set('error_reporting', E_ALL);
ini_set("display_errors", "1");
ini_set('memory_limit', '2048M');

use Magento\Framework\App\Bootstrap;

require 'app/bootstrap.php';
require_once "Csvimport.php";
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');


$Csvimport = new Csvimport();
$Csvimport->filepath("./JustReturnsData.csv");

$csvArry = $Csvimport->get_array(false, ["root", "sub", "sub2"]);


function createCat($cat_val, $parntName = False)
{


    global $objectManager;
    $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
    $parentId = 2;
    $category_factory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
    $store_Id = 1;
    $root_cat = $category_factory->create()->load($parentId);
    $category = $category_factory->create();

    // catagory name
    $categoryName = ucfirst($cat_val);
    $site_url = strtolower($cat_val);
    $clean_url = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($site_url))))));

    $cate = $category->getCollection()
        ->addAttributeToFilter('name', $categoryName)
        ->getFirstItem();

    if ($cate->getId()) {
        return $category;
    }

    if (!empty($parntName)) {

        $parentCat = $category->getCollection()
            ->addAttributeToFilter('name', $parntName)
            ->getFirstItem();
        if ($parentCat->getId()) {
            $root_cat = $parentCat;
            $parentId = $parentCat->getId();
        }
    }

    $category->setPath($root_cat->getPath())
        ->setParentId($parentId)
        ->setUrlKey($clean_url)
        ->setName($categoryName)
        ->setData('description', '')
        ->setStoreId($store_Id)
        ->setPath($root_cat->getPath())
        ->setIsActive(true);

    try {

        $category->save();

    } catch (\Exception $e) {
        var_dump($e->getMessage());
    } catch (\Error $e) {
        var_dump($e->getMessage());
    }


}


$mainCreated = [];
foreach ($csvArry as $item) {


    $cat_val = $item["root"];
    $cat_name = ucfirst($cat_val);


    if (in_array($cat_name, $mainCreated)) {
        continue;
    }


    $mainCreated[] = $cat_name;
    var_dump($cat_name);

    createCat($cat_name);
}


$mainCreated = [];
foreach ($csvArry as $item) {

    $cat_val = $item["sub"];
    $cat_name = ucfirst($cat_val);
    $parntName = ucfirst($item["root"]);

    if (in_array($cat_name, $mainCreated)) {
        continue;
    }

    $mainCreated[] = $cat_name;
    var_dump($cat_name);

    createCat($cat_name, $parntName);
}



$mainCreated = [];
foreach ($csvArry as $item) {

    $cat_val = $item["sub2"];
    $cat_name = ucfirst($cat_val);
    $parntName = ucfirst($item["sub"]);

    if (in_array($cat_name, $mainCreated)) {
        continue;
    }

    $mainCreated[] = $cat_name;
    var_dump($cat_name);

    createCat($cat_name, $parntName);
}

