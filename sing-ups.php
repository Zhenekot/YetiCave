<?php
require_once('helpers.php');
require_once('function.php');
require_once('init.php');

$user_list = get_user_list($con);
$category_list = category_list($con);
$nav = include_template('categoriya.php', ['categories' => $category_list]);
const MIN_NAME = 2;
const MAX_NAME = 30;
const MAX_EMAIL = 300;
const MAX_CONTACT = 300;
$errors = [];
$required_fields = ['email', 'password', 'name', 'message'];

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
            foreach ($user_list as $user_item) {

                if ($user_item["email"] === $_POST['email']) {
                    $errors['email'] = 'Такой email уже используется!';
                    break;
                }
            }
        }

        if (!isset($errors['email'])) {
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'email введен некорректно!';
            }
        }

        if (!isset($errors['name'])) {
            $len = strlen($_POST['name']);

            if ($len < MIN_NAME or $len > MAX_NAME) {
                $errors['name'] = "Имя должно быть от " . MIN_NAME . " до " . MAX_NAME . " символов";
            }

        }

        if (!isset($errors['email'])) {
            $len = strlen($_POST['email']);

            if ($len > MAX_EMAIL) {
                $errors['email'] = "Email должен быть до " . MAX_EMAIL . " символов";
            }

        }

        if (!isset($errors['message'])) {
            $len = strlen($_POST['message']);

            if ($len > MAX_CONTACT) {
                $errors['message'] = "Контактная информация должна быть до " . MAX_CONTACT . " символов";
            }

        }

        if (!$errors) {
            $email = $_POST['email'];
            $name = $_POST['name'];
            $password = $_POST['password'];
            $message = $_POST['message'];

            add_user($email, $name, $password, $message, $con);
            header('Location: /logins.php');
            
        }
    }

    $add_content = include_template('sing-up.php', ['nav' => $nav, 'errors' => $errors]);
    print(include_template('layout.php', [
        'title' => 'Регистрация',
        'main' => $add_content,
        'nav' => $nav
    ]));
}