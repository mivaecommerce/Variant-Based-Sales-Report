<?php
date_default_timezone_set('America/Los_Angeles');
require 'db/db_connect.php';
include 'includes/functions.php';
include 'includes/IntervalMakerClass.php';

$myIntervalMaker = new IntervalMaker();
$dates = $myIntervalMaker->createDates($_POST['settings:startdate'], $_POST['settings:finishdate'], $_POST['settings:desired_interval']);
$timestamps = $myIntervalMaker->createTimestamps($_POST['settings:startdate'], $_POST['settings:finishdate'], $_POST['settings:desired_interval']);

$first_wave = get_products_between_interval($_POST['settings:startdate'], $_POST['settings:finishdate'], $db);
$first_wave = merge_variants($first_wave);

$first_row = 'PRODUCT_CODE,VARIANT_CODE,PRODUCT_NAME';

foreach ($first_wave as $key => $product) {
    $row = array();
    $row += $product['code'] . ',';
    $row += $product['variant_code'] . ',';
    $row += $product['name'] . ',';

    $match_found = false;

    foreach ($timestamps as $key2 => $timestamp) {
        $products = get_products_between_interval($timestamp[0], $timestamp[1], $db);
        $products = merge_variants($products);

        if ($products !== null) {
            foreach ($products as $product_inside) {
                if ($product['variant_code'] != '' && $product['variant_code'] == $product_inside['variant_code']) {
                    $row += $product_inside['quantity'] . ',';
                    $match_found = true;
                } elseif (strpos($product['line_id'], $product_inside['line_id']) !== false) {
                    $row += $product_inside['quantity'] . ',';
                    $match_found = true;
                } elseif ($product_inside['attr_id'] == '' && $product_inside['attr_code'] == '' && $product_inside['option_id'] == '' && $product_inside['variant_code'] == '' && $product_inside['product_id'] == $product['product_id']) {
                    $row += $product_inside['quantity'] . ',';
                    $match_found = true;
                }
            }
            if ($match_found === false) {
                $row += ',';
            }
            $products_null = false;
        } else {
            $row += ',';
            $products_null = true;
        }

        if ($key == 0) {
            if (!($key2 == count($timestamps) - 1 && $products_null === true)) {
                $first_row += $dates[$key2] . ',';
            }
        }

    }
    $csv += $row . "\n";
}

echo $first_row . "\n";
echo $csv;