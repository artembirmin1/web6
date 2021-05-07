<?php

/**
 * Задача 6. Реализовать вход администратора с использованием
 * HTTP-авторизации для просмотра и удаления результатов.
 **/

// Пример HTTP-аутентификации.
// PHP хранит логин и пароль в суперглобальном массиве $_SERVER.
// Подробнее см. стр. 26 и 99 в учебном пособии Веб-программирование и веб-сервисы.
if (empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != 'admin' ||
    md5($_SERVER['PHP_AUTH_PW']) != md5('admin')) {
    header('HTTP/1.1 401 Unanthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

// Инициализируем переменные для подключения к базе данных.
$db_user = 'u20619';   // Логин БД
$db_pass = '4751225';  // Пароль БД

// Подключаемся к базе данных на сервере.
$db = new PDO('mysql:host=localhost;dbname=u20619', $db_user, $db_pass, array(
    PDO::ATTR_PERSISTENT => true
));

// Если метод был POST, значит мы нажали на кнопку удаления.
// Пробуем удалить запись из базы данных.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
      // Удаляем данные пользователя
        $stmt = $db->prepare('DELETE FROM table5 WHERE userId = ?');
        $stmt->execute(array(
            $_POST['remove']
        ));
        // Удаляем пользователя
        $stmt = $db->prepare('DELETE FROM users WHERE login = ?');
        $stmt->execute(array(
            $_POST['remove']
        ));
    } catch (PDOException $e) {
        echo 'Ошибка: ' . $e->getMessage();
        exit();
    }
}

try {
  // Получили все данные соединением 2х таблиц по логину
    $stmt = $db->query('SELECT users.login, users.password, table5.name, table5.email,table5.year, table5.sex, table5.limbs, table5.powers, table5.bio FROM users JOIN table5 on table5.userId = users.login');
  // Получили и посчитали способности
    $stmt2 = $db->query(
		'SELECT SUM((length(table5.powers) - length(replace(table5.powers, "tp", "")))/2),
			SUM((length(table5.powers) - length(replace(table5.powers, "vision", "")))/6),
			SUM((length(table5.powers) - length(replace(table5.powers, "levit", "")))/5)
		FROM table5'
		);
	?>

    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Админ панель | Задание 6</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.8.2/css/bulma.min.css">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <form action="" method="post">
        <div class="table-container">
        	<!-- Данные пользователей -->
          <table class="table is-hoverable is-fullwidth">
              <thead>
              <tr>
                  <th>Логин</th>
                  <th>Пароль</th>
                  <th>Имя</th>
                  <th>Email</th>
                  <th>Год гождения</th>
                  <th>Пол</th>
                  <th>Количество конечностей</th>
                  <th>Сверхспособности</th>
                  <th>Биография</th>
                  <th>Удалить</th>
              </tr>
              </thead>
              <tbody>
              <?php
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  print('<tr>');
                  foreach ($row as $cell) {
                      print('<td>' . $cell . '</td>');
                  }
                  print('<td><button class="button is-info is-small is-danger is-light" name="remove" type="submit" value="' . $row['login'] . '">x</button></td>');
                  print('</tr>');
              }
              ?>
              </tbody>
          </table>
          <!-- Статистика способностей -->
          <table class="table is-hoverable is-fullwidth">
			<thead>
				<tr>
				<th>Телепортация</th>
				<th>Ночное зрение</th>
				<th>Левитация</th>
				</tr>
			</thead>
		<tbody>
		<?php
			while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
					print('<tr>');
						foreach ($row as $cell) {
							print('<td>' . $cell . '</td>');
						}
					print('</tr>');
			}
		?>
		</tbody>
		</table>
        </div>
    </form>
    </body>
    <?php
} catch (PDOException $e) {
    echo 'Ошибка: ' . $e->getMessage();
    exit();
}
