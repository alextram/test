<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/common.php';


/*
 * На всякий случай поясяю, сообщения в exception я написал на английском, потому что при нормальном
 * функционировании они не должны появляться в принципе - вся проверка в JS перед отправкой формы.
 * Если до них дошло, значит, уже раньше что-то начало идти не по плану.
 */


try
{
	$db = new \alextram\test\DB($dbhost, $dbuser, $dbpass, $dbname);
	$id = intval($_GET['id']);

	switch ($_GET['action'])
	{
	case 'write':
		/* При добавлении $id == 0, при редактировании $id != 0
		 * В случае, если после редактирования возвращается id == 0, это означает, что такой записи найдено не было
		 */

		$name = $db->make_safe($_POST['name']);
		$dept = $db->make_safe($_POST['dept']);

		if ($name == '') throw new Exception('No name specified');
		if ($dept == '') throw new Exception('No department specified');

		if (!$db->test_phone($_POST['phone'])) throw new Exception('Wrong phone format');
		$phone = $db->make_safe($_POST['phone']);

		if (isset($_POST['g-recaptcha-response'])) $data = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=6LdC19khAAAAAG3xnWsRJvlYJ-rZttvJvs0dN3eU&response=' . $_POST['g-recaptcha-response'] . '&remoteip=' . $_SERVER['REMOTE_ADDR']));
		if (!isset($data) || !$data->success) throw new Exception('Captcha not passed');

		$query = "users SET name='$name', dept='$dept', phone='$phone'";
		$db->query($id ? "UPDATE $query WHERE id=$id" : "INSERT INTO $query");

		if ($id)
		{
			if (!$db->updated_rows())
				$id = 0;
		}
		else $id = $db->insert_id();

		exit(json_encode(array('id' => $id)));


	case 'del':
		if (!$id) throw new Exception('No ID specified');
		$db->query("DELETE FROM users WHERE id=$id");
		exit();


	default:
		throw new Exception('Unknown action');
	}
}
catch (Exception $e)
{
	exit($e->getMessage());
}