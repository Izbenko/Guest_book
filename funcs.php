<?php
function debug(array $arr)
{
    echo '<pre>' . print_r($arr, 1) . '</pre>';
}

function registration(): bool
{
    global $pdo;
    $login = !empty($_POST['login']) ? trim($_POST['login']) : '';
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';
    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Упс... Пароль или логин не должны быть пустыми';
        return false;
    }
    $res = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = ?");
    $res->execute([$login]);
    if ($res->fetchColumn()) {
        $_SESSION['errors'] = 'Такой уже у нас есть. Второй не нужен)';
        return false;
    }
    $pass = password_hash($pass, PASSWORD_DEFAULT);
    $res = $pdo->prepare("INSERT INTO users (login, pass) VALUES (?, ?)");
    if ($res->execute([$login, $pass])) {
        $_SESSION['success'] = 'Ты успешно к нам присоединился)';
        return true;
    }
    else {
    $_SESSION['errors'] = 'Упс... Что-то пошло не так';
    return false;
    }
}

function login(): bool
{
    global $pdo;
    $login = !empty($_POST['login']) ? trim($_POST['login']) : '';
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';
    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Упс... Пароль или логин не должны быть пустыми';
        return false;
    }

    $res = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $res->execute([$login]);
    if (!$user = $res->fetch()) {
        $_SESSION['errors'] = 'Неверные данные, друг)';
        return false;
    }
    if (!password_verify($pass, $user['pass'])) {
        $_SESSION['errors'] = 'Неверные данные, друг)';
        return false;
    } else {
        $_SESSION['success'] = 'Вы успешно авторизовались у нас)';
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
        $_SESSION['errors'] = 'Не-не. Сначала авторизуйся)';
        return false;
    }

    if (empty($message)) {
        $_SESSION['errors'] = 'Что ты отправлять собрался, братишка?) Введи хотя бы что-нибудь)';
        return false;
    }

    $res = $pdo->prepare("INSERT INTO messages (name, message) VALUES (?,?)");
    if ($res->execute([$_SESSION['user']['name'], $message])) {
        $_SESSION['success'] = 'Сообщение добавлено)';
        return true;
    } else {
        $_SESSION['errors'] = 'Упс... Ошибочка вышла)';
        return false;
    }
}

function get_messages(): array
{
    global $pdo;
    $res = $pdo->query("SELECT * FROM messages ");
    return $res->fetchAll();
}

?>
