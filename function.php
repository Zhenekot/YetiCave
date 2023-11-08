<?php
require_once('init.php');
const COUNT_MINUTE = 60;
const COUNT_HOUR = 3600;
const COUNT = COUNT_MINUTE * 60 * 24;
const MINUTE = 1;
const DAY = 24;

/**
 * Форматирует Цену
 * @param int $num Неформатированая цена
 *
 * @return string форматированная цена
 */
function formNum(int $price): string
{
    return number_format($price, thousands_separator: ' ') . ' ₽';
}

/**
 * Возварщает время оставшееся до завершения лота
 * @param string $date дата завершения лота
 *
 * @return array [часы, минуты]
 */
function get_dt_range(string $date): array
{
    date_default_timezone_set('Asia/Yekaterinburg');


    $minutes = floor(((strtotime($date) + (COUNT)) - time()) / COUNT_MINUTE);
    $hours = str_pad(floor($minutes / COUNT_MINUTE), 2, "0", STR_PAD_LEFT);
    $minutes = str_pad(floor($minutes - ($hours * COUNT_MINUTE) + MINUTE), 2, "0", STR_PAD_LEFT);
    return [$hours, $minutes];

}

/**
 * Получает список всех не истекших лотов лотов в порядке создания от последнего к первому
 * @param mysqli $con подключение к базе
 *
 * @return array список лотов
 */
function lot_list(mysqli $con): array
{
    $sql = "SELECT Lot.id,  Lot.name as name_lot, picture, start_price, date_end, Category.name  as cat_name FROM Lot
    INNER JOIN Category ON Lot.category_id = Category.id
    WHERE date_end >= CURRENT_DATE ORDER BY date_reg DESC";

    return mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_ASSOC);
}

/**
 * Получает список всех категорий
 * @param mysqli $con подключение к базе
 *
 * @return array список категорий
 */
function category_list(mysqli $con): array
{
    $sql = "SELECT id, name, sym_code FROM Category";

    return mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_ASSOC);
}

/**
 * Возвращает Лот по id или null если лот не найден
 * @param mysqli $con подключение к базе
 * @param int $id_lot id лота
 *
 * @return array|null лот или ничего
 */
function lot_detail(mysqli $con, int $id_lot): array|null
{
    $sql = "SELECT Lot.name as name_lot, picture, start_price, date_end, rate_step, description, Category.name FROM Lot
    INNER JOIN Category ON Lot.category_id = Category.id 
    WHERE Lot.id = ? ";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_lot);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = mysqli_fetch_assoc($res);
    if (mysqli_num_rows($res) === 0) {
        http_response_code(404);
    }
    return $rows;
}

/**
 * Добавление лота
 * @param string $lot_name имя лота
 * @param int $category id категории
 * @param string $message описание лота
 * @param string $picture изображение лота
 * @param int $lotRate начальная цена
 * @param int $lotStep шаг ставки
 * @param string $endDate дата завершения
 * @param mysqli $con подключение к базе
 *
 * @return int id лота
 */
function add_lot(string $lot_name, int $category, string $message, string $picture, int $lotRate, int $lotStep, string $endDate, mysqli $con): int
{

    $authorId = $_SESSION['id_user'];

    $sql = "INSERT INTO Lot(name, description, picture, start_price, date_end, rate_step, creator_id, category_id)
            VALUES(?,?,?,?,?,?,?,?);";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'sssisiii', $lot_name, $message, $picture, $lotRate, $endDate, $lotStep, $authorId, $category);
    mysqli_stmt_execute($stmt);

    return $con->insert_id;
}

/**
 * Возвращает список пользователей
 * @param mysqli $con подключение к базе
 *
 * @return array список пользователей
 */
function get_user_list(mysqli $con): array
{
    $sql = "SELECT id, date_reg, email, name, contact, password FROM User";
    return mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_ASSOC);
}

/**
 * Добавление пользователя
 * @param string $email email пользователя
 * @param string $name имя пользователя
 * @param string $password пароль пользователя
 * @param int $message контактная информация
 * @param mysqli $con подключение к базе
 */
function add_user(string $email, string $name, string $password, string $message, mysqli $con): void
{
    $password_temp = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO User(email, name, contact, password)
            VALUES(?,?,?,?);";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ssss', $email, $name, $message, $password_temp);
    mysqli_stmt_execute($stmt);
}

/**
 * Получение пользователя
 * @param string $email Email пользователя
 * @param mysqli $con подключение к базе
 *
 * @return array|null пользователь или ничего
 */
function get_user(string $email, mysqli $con): array|null
{
    $sql = "SELECT id, date_reg, email, name, contact, password FROM User WHERE email = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res);
}

/**
 * Возвращает количетво найденых лотов, соответствующих строке поиска
 * @param string $search_str строка поиска
 * @param mysqli $con подключение к базе
 *
 * @return int количетво лотов
 */
function search_lot_count(string $search_str, mysqli $con): int
{
    $sql = "SELECT COUNT(Lot.id) AS count_lot FROM Lot WHERE date_end >= CURRENT_DATE AND MATCH(Lot.name,Lot.description) AGAINST(?);";
    $stmt = mysqli_prepare($con, $sql);

    mysqli_stmt_bind_param($stmt, 's', $search_str);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $result[0]['count_lot'];
}

/**
 * Возвращает Лоты по названию или категории если лоты не найден возвращает null
 * @param string $search_str стрка поиска
 * @param mysqli $con подключение к базе
 * @param int $limit количетсво возвращаемых записей
 * @param int $offset позиция с которой начинается выборка
 *
 * @return array|null лоты подходяшие под условия поиска или ничего
 */
function search_lot(string $search_str, mysqli $con, int $limit, int $offset): array|bool
{
    $sql = "SELECT Lot.id,  Lot.name as name_lot, picture, start_price, date_end, Category.name FROM Lot
    INNER JOIN Category ON Lot.category_id = Category.id
    WHERE date_end >= CURRENT_DATE AND MATCH(Lot.name,Lot.description)  AGAINST(?) 
    ORDER BY date_reg DESC
    LIMIT ?
    OFFSET ?;";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'sii', $search_str, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
        return mysqli_fetch_all($res, MYSQLI_ASSOC);
    } else {
        return $res;
    }
}

/**
 * Добавление ставки
 * @param int $lot_id id лота
 * @param int $user_id id пользователя
 * @param int $cost сумма ставки
 * @param mysqli $con подключение к базе
 */
function bet_add(int $lot_id, int $user_id, int $cost, mysqli $con): void
{
    $sql = "INSERT INTO Bet(`sum`, lot_id, user_id)
            VALUES(?,?,?);";
    $stmt = mysqli_prepare($con, $sql);

    mysqli_stmt_bind_param($stmt, 'iii', $cost, $lot_id, $user_id);

    mysqli_stmt_execute($stmt);
}

/**
 * Возвращает ставки для лота
 * @param int $id_lot Id лота
 * @param mysqli $con подключение к базе
 *
 * @return array ставки на лот
 */
function bets_lot(int $id_lot, mysqli $con): array
{
    $sql = "SELECT Bet.id, User.name as user_name, sum, Bet.date_reg, lot_id, user_id FROM Bet
    INNER JOIN User ON User.id = user_id
    WHERE lot_id = ? ORDER BY Bet.date_reg DESC;";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_lot);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

/**
 * Возвращает ставки пользователя
 * @param int $id_user Id пользователя
 * @param mysqli $con подключение к базе
 *
 * @return array список ставок на лот пользователя
 */
function bets_my(int $id_user, mysqli $con): array
{
    $sql = "SELECT Bet.id, User.name as user_name, Lot.date_end as lot_date, Lot.picture as pic_lot, sum, Bet.date_reg, lot_id, user_id, Lot.name as lot_name, 
    Category.name as category_name FROM Bet
    INNER JOIN User ON User.id = user_id
    INNER JOIN Lot ON Lot.id = lot_id
    INNER JOIN Category ON Category.id = category_id
    WHERE user_id = ? ORDER BY Bet.date_reg DESC;";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_user);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

/**
 * Возвращает победную ставку лота
 * @param int $id_lot Id лота
 * @param mysqli $con подключение к базе
 *
 * @return array|null ставка или ничего
 */
function bets_win(int $id_lot, mysqli $con): array|null
{
    $sql = "SELECT Bet.id, winner.name as user_name, creator.contact as creator_contact, sum, Bet.date_reg, lot_id, user_id FROM Bet
        INNER JOIN User AS winner ON winner.id = user_id
        INNER JOIN Lot ON Bet.lot_id = Lot.id
        INNER JOIN User AS creator ON creator.id = Lot.creator_id
        INNER JOIN User ON User.id = user_id
        WHERE lot_id = ? AND date_end < CURRENT_DATE ORDER BY Bet.date_reg DESC;";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_lot);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = mysqli_fetch_assoc($res);
    return $rows;
}

/**
 * Возвращает формат даты
 * @param string $date дата и время ставки
 *
 * @return string время прошедшее с момента ставки в нужном формате
 */
function formate_date(string $date): string
{
    date_default_timezone_set('Asia/Yekaterinburg');
    $diff = time() - strtotime($date);

    $day = floor($diff / (COUNT_HOUR * DAY));
    $hour = floor($diff / COUNT_HOUR);
    $minute = floor($diff / COUNT_MINUTE);

    $sec = $diff;

    if ($day >= 1) {
        return $day . ' ' . get_noun_plural_form($day, 'день', 'дня', 'дней') . ' назад';
    }
    if ($hour >= 1) {
        return $hour . ' ' . get_noun_plural_form($hour, 'час', 'часа', 'часов') . ' назад';
    }
    if ($minute >= 1) {
        return $minute . ' ' . get_noun_plural_form($minute, 'минуту', 'минуты', 'минут') . ' назад';
    } else {
        return $sec . ' ' . get_noun_plural_form($sec, 'секунду', 'секунды', 'секунд') . ' назад';
    }

}

/**
 * Возвращает Лоты по категории если лоты не найден возвращает null
 * @param int $id_cat Id категории
 * @param mysqli $con подключение к базе
 * @param int $limit количетсво возвращаемых записей
 * @param int $offset позиция с которой начинается выборка
 *
 *
 * @return array|null лоты подходящей категории или ничего
 */
function lot_list_cat(int $id_cat, mysqli $con, int $limit, int $offset): array|null
{
    $sql = "SELECT Lot.id,  Lot.name as name_lot, picture, start_price, date_end, Category.name  as cat_name, category_id FROM Lot
    INNER JOIN Category ON Lot.category_id = Category.id
    WHERE date_end >= CURRENT_DATE && category_id = ?  ORDER BY date_reg DESC 
    LIMIT ?
    OFFSET ?;";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'iii', $id_cat, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($res) === 0) {
        http_response_code(404);
    }
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}
/**
 * Возвращает количетво лотов в категории
 * @param int $id_cat Id категории
 * @param mysqli $con подключение к базе
 *
 * @return int количество лотов в категории
 */
function cat_lot_count(int $id_cat, mysqli $con): int
{
    $sql = "SELECT COUNT(Lot.id) AS count_lot FROM Lot
    INNER JOIN Category ON Lot.category_id = Category.id
    WHERE date_end >= CURRENT_DATE && category_id = ?  ORDER BY date_reg DESC ";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_cat);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $result[0]['count_lot'];
}

/**
 * Добавление победителя в лот
 * @param int $lot_id Id лота
 * @param int $bet_id Id ставки
 * @param mysqli $con подключение к базе
 */
function add_winner(int $lot_id, int $bet_id, mysqli $con): void
{
    $sql = 'UPDATE `Lot` SET `winner_id`= ? WHERE id = ?';
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $bet_id, $lot_id);
    mysqli_stmt_execute($stmt);

}

/**
 * Возвращает все лоты
 * @param mysqli $con подключение к базе
 *
 * @return array список лотов
 */
function lot_list_all(mysqli $con): array
{
    $sql = "SELECT Lot.id, Lot.name as name_lot, picture, start_price, date_end, Category.name  as cat_name, winner_id FROM Lot
    INNER JOIN Category ON Lot.category_id = Category.id
    ORDER BY date_reg DESC";

    return mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_ASSOC);
}

/**
 * Сохраняет значение поля при POST запросе
 * @param string $name имя поля
 *
 * @return string значение поля
 */

function getPostVal($name): string
{
    return $_POST[$name] ?? "";
}