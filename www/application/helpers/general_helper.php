<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Генерация заголовка страницы.
 *  
 *  @param   string/array  $page_title  [Заголовок текущей страницы]
 *  @param   string        $site_title  [Бренд]
 *  @param   string        $separator   [Разделитель]
 *  @return  string
 */
function get_title($title, $brand = '', $separator = ' — ')
{
	$title_list = [];
	$result = 'Страница не определена';

	// Обработка заголовка текушей страницы.
	if (!empty($title))
	{
		if (is_string($title) || is_numeric($title))
		{
			$title_list[] = get_clear_string($title);
		}
		elseif (is_array($title))
		{
			foreach ($title as $value)
			{
				$title_list[] = get_clear_string($value);
			}
		}
	}

	// Обработка названия бренда.
	if (!empty($brand))
	{
		$title_list[] = get_clear_string($brand);
	}

	// Формирование итогового заголовка страницы.
	if (!empty($title_list))
	{
		$result = implode($separator, $title_list);
	}

	return $result;
}

/**
 *  Обработка строки.
 *  
 *  @param   string  $string  [Строка]
 *  @return  string
 */
function get_string($string = '')
{
	$string = (string) $string;

	return trim($string);
}

/**
 *  Очистка строки.
 *  
 *  @param   string  $string  [Строка]
 *  @return  string
 */
function get_clear_string($string = '')
{
	return htmlspecialchars(get_string($string), ENT_QUOTES);
}

/**
 *  Сравнение двух массивов, поиск отличий. В качестве ответа
 *  возвращает список параметров, которые есть в $new, но нету в $old.
 *  
 *  @param   array   $new  [Массив с новой информацией]
 *  @param   array   $old  [Массив со старой информацией]
 *  @return  array
 */
function get_array_changes($new = [], $old = [])
{
	$new = (array) $new;
	$old = (array) $old;

	if (!empty($new))
	{
		foreach ($new as $key => $value)
		{
			if (is_array($value))
			{
				if (!isset($old[$key]))
				{
					$difference[$key] = $value;
				}
				elseif (!is_array($old[$key]))
				{
					$difference[$key] = $value;
				}
				else
				{
					$new_diff = get_array_changes($value, $old[$key]);

					if ($new_diff != FALSE)
					{
						$difference[$key] = $new_diff;
					}
				}
			}
			elseif (!isset($old[$key]) || $old[$key] != $value)
			{
				$difference[$key] = $value;
			}
		}
	}

	return isset($difference) ? $difference : [];
}

/**
 *  Фильтрация данных массива по переданному списку ключей.
 *  Функция возвращает массив только с теми элементами, которые
 *  перечислены в аргументе `$filter_by`.
 *
 *  @example   filter_array(['a' => 1, 'b' => 2, 'c' => 3], ['b', 'c']) => ['b' => 2, 'c' => 3]
 *  
 *  @param   array   $data       [Массив]
 *  @param   array   $filter_by  [Ключи массива, которые надо оставить]
 *  @return  array
 */
function filter_array($data = [], $filter_by = [])
{
	$data = (array) $data;
	$filter_by = (array) $filter_by;

	$result = [];

	if (!empty($data) && !empty($filter_by))
	{
		foreach ($filter_by as $key)
		{
			if (array_key_exists($key, $data))
			{
				$result[$key] = $data[$key];
			}
		}
	}

	return $result;
}

/**
 *  Группировка массива по заданному полю.
 *  
 *  @param   array   $data      [Исходный массив]
 *  @param   string  $group_by  [Имя поля, по которому будет выполняться группировка]
 *  @param   string  $identity  [Имя поля, по которому будет строится массив в группе]
 *  @return  array
 */
function group_array($data = [], $group_by = '', $identity = '')
{
	$data = (array) $data;
	$group_by = (string) $group_by;
	$identity = (string) $identity;

	$result = [];

	if (!empty($data) && $group_by != '')
	{
		foreach ($data as $key => $value)
		{
			// Если массив не содержит запрашиваемый ключ, отменяем группировку.
			if (!array_key_exists($group_by, $value))
			{
				break;
			}

			// При группировке можно также указать поле, которое будет
			// использоваться как ключ записи.
			if ($identity != '')
			{
				$result[$value[$group_by]][$value[$identity]] = $value;
			}
			else
			{
				$result[$value[$group_by]][] = $value;
			}
		}
	}

	return $result;
}

/**
 *  Замена ключей массива на новые.
 *  
 *  @param   array   $array  [Исходный массив]
 *  @param   array   $keys   [Ключи]
 *  @return  array
 */
function array_keys_changer($array = [], $keys = [])
{
	$array = (array) $array;
	$keys = (array) $keys;

	if (!empty($array) && !empty($keys))
	{
		foreach ($keys as $old_key => $new_key)
		{
			if (isset($array[$old_key]))
			{
				// Перезаписываем данные под новым ключем.
				$array[$new_key] = $array[$old_key];

				// Удаляем старый ключ.
				unset($array[$old_key]);
			}
		}
	}

	return $array;
}

/**
 *  Проверка на наличие нескольких ключей в массиве.
 *  
 *  @param   array    $keys   [Список ключей]
 *  @param   array    $array  [Масив]
 *  @return  boolean
 */
function array_keys_exists($keys = [], $array = [])
{
	$keys = (array) $keys;
	$array = (array) $array;

	if (!empty($keys) && !empty($array))
	{
		foreach ($keys as $key)
		{
			if (!array_key_exists($key, $array))
			{
				return FALSE;
			}
		}
	}
	else
	{
		return FALSE;
	}

	return TRUE;
}

/* End of file general_helper.php */
/* Location: ./application/helpers/general_helper.php */