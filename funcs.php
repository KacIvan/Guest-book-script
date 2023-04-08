<?php

function debug($data)
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
}

function registration(): bool
{
    global $pdo;

    $login = !empty($_POST['login']) ? trim($_POST['login']) : '';
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';

    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Поля логин/пароль не могут быть пустыми!';
        return false;
    }

    $res = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = ?");
    $res->execute([$login]);
    if ($res->fetchColumn()) {
        $_SESSION['errors'] = 'Данное имя уже используется!';
        return false;
    }

    $pass = password_hash($pass, PASSWORD_DEFAULT);
    $res = $pdo->prepare("INSERT INTO users (login, pass) VALUES (?, ?)");
    if ($res->execute([$login, $pass])) {
        $_SESSION['success'] = 'Успешная регистрация :)';
        return true;
    } else {
        $_SESSION['errors'] = 'Ошибка регистрации :(';
        return false;
    }
}

function login(): bool
{
    global $pdo;

    $login = !empty($_POST['login']) ? trim($_POST['login']) : '';
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';

    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Поля логин/пароль не могут быть пустыми!';
        return false;
    }

    $res = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $res->execute([$login]);
    if (!$user = $res->fetch()) {
        $_SESSION['errors'] = 'Неверный логин или пароль!';
        return false;
    }

    if (!password_verify($pass, $user['pass'])) {
        $_SESSION['errors'] = 'Неверный логин или пароль!';
        return false;
    } else {
        $_SESSION['success'] = 'Успешная авторизация!';
        $_SESSION['user']['name'] = $user['login'];
        $_SESSION['user']['id'] = $user['id'];
        return true;
    }
}

function save_message(): bool
{
    global $pdo;
    $message = !empty($_POST['message']) ? trim($_POST['message']) : '';

    if (!isset($_SESSION['user']['name'])) {
        $_SESSION['errors'] = 'Чтобы отправлять сообщения необходимо авторизоваться!';
        return false;
    }

    if (empty($message)) {
        $_SESSION['errors'] = 'Введите текст сообщения!';
        return false;
    }

    $res = $pdo->prepare("INSERT INTO messages (name, message) VALUES (?,?)");
    if ($res->execute([$_SESSION['user']['name'], $message])) {
        $_SESSION['success'] = 'Сообщение добавлено.';
        return true;
    } else {
        $_SESSION['errors'] = 'Непредвиденная ошибка :(';
        return false;
    }
}

function get_messages(): array
{
    global $pdo;
    $res = $pdo->query("SELECT * FROM messages");
    return $res->fetchAll();
}