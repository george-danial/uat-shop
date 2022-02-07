<?php

ini_set('error_reporting', E_ALL);
ini_set("display_errors", "1");
ini_set('memory_limit', '2048M');
require_once "vendor/autoload.php";
require 'app/bootstrap.php';

use Magento\Framework\App\Bootstrap;
use  Spatie\SimpleExcel\SimpleExcelReader;


$dirPath = BP . DS  . "justreturns.dev-products";


$pathToFile = '/home/www/justreturns.dev/JustReturnsDataEntry.xlsx';
$pathToFile = realpath($pathToFile);
$Headers = SimpleExcelReader::create($pathToFile)->getHeaders();
$Rows = SimpleExcelReader::create($pathToFile)->getRows();


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

if (!is_array($skuDirs) || empty($skuDirs)) {
    var_dump("error reading  dir:: " . $dirPath);
    return;
}

foreach ($skuDirs as $SKU) {
    $FileIo = $objectManager->create('Magento\Framework\Filesystem\Io\File');

    if(!is_dir($dirPath . DS . $SKU))
    {
        echo "Not A Dire " . $dirPath . DS . $SKU . " \n";
        continue;
    }
    $dir = $Filesystem->getDirectoryReadByPath($dirPath . DS . $SKU);
    $imgs = $dir->read();
    $count = 0;
    foreach ($imgs as $img) {
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $name = $SKU . "-" . $count;
        $name .= "." . $ext;

        $args['path'] = $dirPath . DS . $SKU;
        $FileIo->open($args);
        $FileIo->mv($img, $name);
        $count++;
    }
    var_dump($SKU);
    var_dump($imgs);
//    return;
}




