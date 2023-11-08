<?php
require_once('helpers.php');
require_once('function.php');
require_once('init.php');

$get_id = $_GET['id'] ?? -1;

$category_list = category_list($con);
$nav = include_template('categoriya.php', ['categories' => $category_list, 'category_id' => $get_id]);
const OFFSET_ONE = 1;
const LIMIT = 3;
$count_lot = cat_lot_count($get_id, $con);
$count_pages = ceil($count_lot / LIMIT);
$curr_page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($curr_page - OFFSET_ONE) * LIMIT;
$lot_list = lot_list_cat($get_id, $con, LIMIT, $offset);
$lots = include_template('lots.php', [
    'lots' => $lot_list,
    'categories' => $category_list,
    'nav' => $nav,
    'count_page' => $count_pages,
    'curr_page' => $curr_page
]);

print($layout = include_template('layout.php', [

    'title' => 'Главная',
    'nav' => $nav,
    'lots' => $lot_list,
    'main' => $lots
]));