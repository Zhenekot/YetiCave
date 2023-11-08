<?php
require_once('helpers.php');
require_once('function.php');
require_once('init.php');
$category_list = category_list($con);
$nav = include_template('categoriya.php', ['categories' => $category_list]);

$errors = [];
$required_fields = ['email', 'password'];
if (isset($_SESSION['is_auth']) && $_SESSION['is_auth']) {
    http_response_code(403);
    $detail = include_template('403.php', ['nav' => $nav]);
    print(include_template('layout.php', [
        'title' => '403',
        'nav' => $nav,
        'main' => $detail
    ]));
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[$field] = 'Поле не заполнено';
            }
        }

        if (!isset($errors['email'])) {
            $user_get = get_user($_POST['email'], $con);
            if ($user_get) {
                if (!isset($errors['password'])) {
                    if (!password_verify($_POST['password'], $user_get['password'])) {
                        $errors['password'] = 'Пароль введен неверно';

                    } else {

                        $_SESSION['username'] = $user_get['name'];
                        $_SESSION['is_auth'] = 1;
                        $_SESSION['id_user'] = $user_get['id'];
                        header('Location: /');
                    }
                }
            } else {
                $errors['email'] = 'Еmail введен неверно';
            }

        }
    }

    $add_content = include_template('login.php', ['nav' => $nav, 'errors' => $errors]);
    print(include_template('layout.php', [

        'title' => 'Вход',
        'main' => $add_content,
        'nav' => $nav
    ]));
}