<?php
require_once('helpers.php');
require_once('function.php');
require_once('init.php');

$lot_list = lot_list($con);
$lot_list_all = lot_list_all($con);

for ($i = 0; $i < count($lot_list_all); $i++) {
    $bet_list = bets_win($lot_list_all[$i]['id'], $con);
    if (strtotime($lot_list_all[$i]['date_end']) < time() && $lot_list_all[$i]['winner_id'] === null) {
        if ($bet_list !== null) {
            add_winner($lot_list_all[$i]['id'], $bet_list['user_id'], $con);
        }
    }
}

$category_list = category_list($con);
$nav = include_template('categoriya.php', ['categories' => $category_list]);
$main = include_template('main.php', [
    'lots' => $lot_list,
    'categories' => $category_list
]);

print($layout = include_template('layout.php', [
    'title' => 'Главная',
    'nav' => $nav,
    'lots' => $lot_list,
    'main' => $main
]));

