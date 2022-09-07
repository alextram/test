<?php
namespace alextram\test;

define('PHONE_REGEXP', '(\+)?([- _():=+]?\d[- _():=+]?){10,14}');


class DB
{
	private $mysqli = null;


	function __construct($host, $user, $pass, $db)
	{
		$this->mysqli = mysqli_connect($host, $user, $pass, $db);
		if (!$this->mysqli) throw new \Exception('Cannot connect to MySQL');

		mysqli_query($this->mysqli,'SET NAMES `utf8mb4`');
	}


	function __destruct()
	{
		if ($this->mysqli) mysqli_close($this->mysqli);
	}


	/**
	 * Запрос к БД
	 * @param  $query
	 * @return bool|\mysqli_result
	 * @throws \Exception
	 */
	function query($query)
	{
		$res = mysqli_query($this->mysqli, $query);
		if (!$res) throw new \Exception($this->mysqli->error);

		return $res;
	}


	/**
	 * Получение единичного значения из запроса (первое поле первой строки)
	 * @param  string $query
	 * @return mixed
	 * @throws \Exception
	 */
	function get_val($query)
	{
		$arr = mysqli_fetch_row($this->query($query));
		return $arr[0];
	}


	/**
	 * Получение одной строки из запроса (ассоциативный массив)
	 * @param  string $query
	 * @return array
	 * @throws \Exception
	 */
	function get_data($query)
	{
		return mysqli_fetch_assoc($this->query($query));
	}


	/**
	 * Получение ассоциативного массива строк из запроса
	 * Ключ массива - первое поле в результате
	 * @param  string $query
	 * @return array
	 * @throws \Exception
	 */
	function get_assoc_data($query)
	{
		$data = array();

		$res = $this->query($query);

		if ($arr = mysqli_fetch_assoc($res))
		{
			$fields = array_keys($arr);
			$key = $fields[0];

			do $data[$arr[$key]] = $arr;
			while ($arr = mysqli_fetch_assoc($res));
		}

		return $data;
	}


	/**
	 * Получение ассоциативного массива значений из запроса
	 * Ключ массива - первое поле в результате, значение - второе
	 * @param  string $query
	 * @param  string $value_callback функция, вызываемая для обработки значений
	 * @return array
	 * @throws \Exception
	 */
	function get_assoc_values($query, $value_callback = '')
	{
		$data = array();

		$res = $this->query($query);
		while ($arr = mysqli_fetch_row($res))
			$data[$arr[0]] = $value_callback ? $value_callback($arr[1]) : $arr[1];

		return $data;
	}


	/**
	 * Получение нумерованного массива строк из запроса
	 * @param  string $query
	 * @return array
	 * @throws \Exception
	 */
	function get_array_data($query)
	{
		$data = array();

		$res = $this->query($query);
		while ($arr = mysqli_fetch_assoc($res))
			$data[] = $arr;

		return $data;
	}


	/**
	 * Получение нумерованного массива значений из запроса
	 * @param  string $query
	 * @return array
	 * @throws \Exception
	 */
	function get_values($query)
	{
		$data = array();

		$res = $this->query($query);
		while ($arr = mysqli_fetch_row($res))
			$data[] = $arr[0];

		return $data;
	}


	/**
	 * Обёртка для insert_id
	 * @return int
	 */
	function insert_id()
	{
		return mysqli_insert_id($this->mysqli);
	}


	/**
	 * Получение числа соответствовавших условию обновления строк (даже если фактически изменений не было)
	 * В отличие от affected_rows, которая не учитывает строки, содержимое которых не менялось
	 * Имеет смысл только после запроса UPDATE
	 * @return int
	 */
	function updated_rows()
	{
		$info = mysqli_info($this->mysqli);
		preg_match('/Rows matched: (\d+?) /i', $info, $matches);
		return $matches[1][0];
	}


	/**
	 * Обработка текста для вставки в БД (плюс обрезка пробелов)
	 * @param  string $str
	 * @return string
	 */
	function make_safe($str)
	{
		return mysqli_real_escape_string($this->mysqli, htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8'));
	}


	/**
	 * Проверка номера телефона на соответствие формату
	 * @param $phone
	 * @return bool|int
	 */
	function test_phone($phone)
	{
		return preg_match('/^' . PHONE_REGEXP . '$/', $phone);
	}
}