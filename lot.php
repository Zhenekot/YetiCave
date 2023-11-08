<?php
require_once('helpers.php');
require_once('function.php');
require_once('init.php');

$get_id = $_GET['id'] ?? -1;
$errors = [];
$lots = lot_detail($con, $get_id);

$nav = include_template('categoriya.php', ['categories' => category_list($con) ]);
if (http_response_code() === 404) {
    $detail_lot = include_template('404.php', ['nav' => $nav]);
} else {
    $bets_lot = bets_lot($get_id, $con);
    $count_bets = count($bets_lot);
    if (!$count_bets) {
        $min_bet = $lots['start_price'];;
        $price = $lots['start_price'];
    } else {
        $min_bet = $bets_lot[0]['sum'] + $lots['rate_step'];
        $price = $bets_lot[0]['sum'];
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (empty($_POST['cost'])) {
            $errors['cost'] = 'Поле не заполнено';
        }
        if (!isset($errors['cost'])) {
            if (!filter_var($_POST['cost'], FILTER_VALIDATE_INT)) {
                $errors['cost'] = 'Ставка должна быть целым числом';
            } else {
                if (!isset($errors['cost']) && $_POST['cost'] < $min_bet) {
                    $errors['cost'] = 'Ставка должна быть больше, либо равна';
                }
            }
            if (!isset($errors['cost'])) {
                bet_add($get_id, $_SESSION['id_user'], (int) $_POST['cost'], $con);
                header('Location: /lot.php?id=' . $get_id);
            }
        }
    }
    $detail_lot = include_template('lot.php', [
        'nav' => $nav,
        'min_bet' => $min_bet,
        'errors' => $errors,
        'bet_lots' => $bets_lot,
        'lots' => $lots,
        'count_bets' => $count_bets,
        'price' => $price
    ]);
}

$name_title = isset($lots['name_lot']) ? $lots['name_lot'] : '404';
print(include_template('layout.php', [

    'title' => $name_title,
    'nav' => $nav,
    'main' => $detail_lot
]));

