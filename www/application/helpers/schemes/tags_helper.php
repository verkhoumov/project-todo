<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Обработка списка меток.
 *  
 *  @param   array   $tags  [Метки]
 *  @return  array
 */
function get_tags($tags = [])
{
	$tags = (array) $tags;

	$result = [];

	if (!empty($tags))
	{
		foreach ($tags as $key => $value)
		{
			$tag = get_clear_string($value);

			if ($tag != '')
			{
				$result[] = $value;
			}
		}
	}

	return $result;
}

/**
 *  Преобразование списка меток из строки в массив.
 *  
 *  @param   string  $string  [Список меток]
 *  @return  array
 */
function tags_string_to_array($string = '')
{
	$string = get_string($string);

	return get_tags(explode('|', $string));
}

/**
 *  Преобразование списка меток из массива в строку.
 *  
 *  @param   array    $array  [Список меток]
 *  @return  string
 */
function tags_array_to_string($array = [])
{
	$array = (array) $array;

	return implode('|', $array);
}

/* End of file tags_helper.php */
/* Location: ./application/helpers/schemes/tags_helper.php */