<?php

ini_set('error_reporting', E_ALL);
ini_set("display_errors", "1");
ini_set('memory_limit', '2048M');
require_once "vendor/autoload.php";
require 'app/bootstrap.php';

use Magento\Framework\App\Bootstrap;
use  Spatie\SimpleExcel\SimpleExcelReader;


$dirPath = "pub/media/import/products" ;

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');
$state = $objectManager->get('Magento\Framework\App\State');
$Filesystem = $objectManager->get('\Magento\Framework\Filesystem');
$FileIo = $objectManager->get('Magento\Framework\Filesystem\Io\File');
$state->setAreaCode('frontend');
/**
 * @var \Magento\Framework\Filesystem $Filesystem
 * @var \Magento\Framework\Filesystem\Io\File $FileIo
 */

$mainDir = $Filesystem->getDirectoryReadByPath($dirPath);

$skuDirs = $mainDir->read();

if (!is_array($skuDirs) || empty($skuDirs))
{
    var_dump("error reading  dir:: " . $dirPath);
    return;
}

foreach ($skuDirs as $SKU )
{
    $FileIo = $objectManager->create('Magento\Framework\Filesystem\Io\File');

    $dir = $Filesystem->getDirectoryReadByPath($dirPath . DS . $SKU);
    $imgs = $dir->read();
    $count = 0;
    $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
    $productGallery = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\Gallery');
    $productObj = $productRepository->get($SKU);
    $productimages = $productObj->getMediaGalleryImages();
    $mediaAttribute = ['thumbnail','small_image','image'];
    $imgs = $dir->read();
    $count = 0;
    $dirname = BP . DS . $dirPath . DS . $SKU;

    if(count($productimages) > 0) {
        $msg = 'Product has already images. Product ID: ' . $productObj->getId();
        echo $msg ."\n";
        foreach($productimages as $gimage){
            $productGallery->deleteGallery($gimage->getValueId());
        }
        $productObj->setMediaGalleryEntries([]);
        $productObj->save();
        //continue;
    }

//continue;
    
    foreach ($imgs as $img)
    {
        $image = $dirname . DS .$img;
        var_dump($image);
        if($count== 0) {
            $productObj->setStoreId(0);
            $productObj->addImageToMediaGallery($image, $mediaAttribute, false, false);
        } else {
            $productObj->addImageToMediaGallery($image, null, false, false);
        }
        try {
            $productObj->save();
        } catch(\Exception $e) {
            echo $e->getMessage();
            exit;
        }
        $count++;

    }

    echo 'Product image saved. Product ID: ' . $productObj->getId() ." \n";

}

//// find . -name "*.webp" | parallel -eta cwebp {} -o {.}.png
//
///*
// *
//find . -name "*.webp" -exec rm -f {} \;
//
//for file in ./*/*
//do
//    cwebp -q 80 "$file" -o "${file%.webp}.png"
//done
//
//*/

