<?php
require_once('helpers.php');
require_once('function.php');
require_once('init.php');

$nav = include_template('categoriya.php', ['categories' => category_list($con)]);
if (!isset($_SESSION['is_auth']) || !$_SESSION['is_auth']) {
    http_response_code(403);
    $detail = include_template('403.php', ['nav' => $nav]);
    print(include_template('layout.php', [
        'title' => '403',
        'nav' => $nav,
        'main' => $detail
    ]));
} else {

    $bets_my = bets_my($_SESSION['id_user'], $con);
    for ($i = 0; $i < count($bets_my); $i++) {
        $bet = $bets_my[$i];
        $bet_win = bets_win($bet['lot_id'], $con);
        $bets_my[$i]['is_win'] = isset($bet_win['id']) && $bet_win['id'] === $bet['id'];
        $bets_my[$i]['creator_contact'] = $bet_win['creator_contact'] ?? "";
    }

    $my_bets = include_template('my-bets.php', ['nav' => $nav, 'bet_lots' => $bets_my]);
    print(include_template('layout.php', [

        'title' => 'Мои ставки',
        'nav' => $nav,
        'main' => $my_bets
    ]));
}