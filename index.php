<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/common.php';

?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8">
	<title>Employee Details</title>

	<link rel="stylesheet" href="/test.css?<?php echo time() ?>">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;700&display=swap" rel="stylesheet">

	<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>

	<script src="/index.js?<?php echo time() ?>"></script>
	<script>
		const phone_regexp = <?php echo '/' . PHONE_REGEXP . '/' ?>;
	</script>
</head>

<body>

	<div id="nb-form" class="popup">
		<a href="#" class="nb-close">&times;</a>
		<h2>Добавить пользователя</h2>
		<form>
			<div class="fields">
				<input type="text" name="name"  class="nb-name"  value="" maxlength="150" required placeholder="Name">
				<input type="text" name="dept"  class="nb-dept"  value="" maxlength="150" required placeholder="Department">
				<input type="tel"  name="phone" class="nb-phone" value="" maxlength="30"  required placeholder="Phone" pattern="<?php echo PHONE_REGEXP ?>">
			</div>
			<div class="g-recaptcha" style="text-align:center" data-sitekey="6LdC19khAAAAAHwcAJO4zfMFqB278L5Xmw9Z5tPk" data-callback="captchaCallback"></div>
			<div class="errbox"></div>
			<input type="submit" class="nb-submit rounded-btn" value="Сохранить">
		</form>
	</div>

	<div id="nb-del-confirm" class="popup">
		<h2>Вы точно хотите удалить элемент?</h2>
		<div class="errbox"></div>
		<input type="button" class="nb-del-yes rounded-btn" value="Удалить">&ensp;
		<input type="button" class="nb-del-no  rounded-btn" value="Отмена">
	</div>

	<div id="backgr"></div>

	<div class="header">
		<h1><span class="grayed">Employee</span> Details</h1>
		<a href="#" class="nb-add-new rounded-btn">+ Add New</a>
	</div>

	<?php

	try
	{
		/* Можно было бы грузить содержимое динамически и не писать дублирующийся код содержимого таблицы,
		 * но лично мне не нравятся меняющиеся несколько раз при загрузке страницы.
		 * Пусть будет показано сразу всё уже в готовом виде.
		 */

		$db = new \alextram\test\DB($dbhost, $dbuser, $dbpass, $dbname);
		$items = $db->get_assoc_data('SELECT * FROM users ORDER BY name');

	?>

	<table id="notebook">
		<thead><tr><th>Name</th><th>Department</th><th>Phone</th><th class="algin-center">Actions</th></tr></thead>
		<tbody id="nb-data">
		<?php

		foreach ($items as $arr)
		{
			echo '<tr data-id="' . $arr['id'] . '">';
			echo '<td>' . $arr['name'] . '</td>';
			echo '<td>' . $arr['dept'] . '</td>';
			echo '<td>' . $arr['phone'] . '</td>';
			echo '<td class="actions algin-center"><a href="#" class="nb-edit">&#9998;</a>&ensp;<a href="#" class="nb-del">&#128465;</a></td>';
			echo '</td>';
		}

		?>
		</tbody>
		<tbody id="nb-empty"<?php if ($items) echo ' class="hidden"' ?>><tr><td colspan="4">Не создано ни одной записи.</td></tr></tbody>
	</table>

	<?php
	}
	catch (Exception $e)
	{
		echo '<div class="error"><b>Произошла ошибка:</b><br>' . $e->getMessage() . '<br>Файл: <b>' . $e->getFile() . '</b><br>Строка: <b>' . $e->getLine() . '</b></div>';
	}

	?>

</body>

</html>