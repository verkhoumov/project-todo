<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Обработка информации переданного списка пунктов todo-листинга.
 *  
 *  @param   array   $data  [Список пунктов todo-листинга]
 *  @return  array
 */
function get_tasks_data($data = [])
{
	$data = (array) $data;
	
	$result = [];

	if (!empty($data))
	{
		foreach ($data as $key => $value)
		{
			$result[$key] = get_task_data($value);
		}
	}

	return $result;
}

/**
 *  Обработка информации о пунтке todo-листинга.
 *  
 *  @param   array   $data  [Информация о пунтке todo-листинга]
 *  @return  array
 */
function get_task_data($data = [], $all = TRUE)
{
	$data = (array) $data;

	$result = $all ? get_default_task_data() : [];
	
	if (isset($data['id']) && $data['id'] > 0)
	{
		$result['id'] = (integer) $data['id'];
	}

	if (isset($data['list_id']) && $data['list_id'] > 0)
	{
		$result['list_id'] = (integer) $data['list_id'];
	}

	if (isset($data['name']) && $data['name'] != '')
	{
		$result['name'] = get_string($data['name']);
	}

	if (isset($data['image']) && $data['image'] != '')
	{
		$result['image_name'] = get_string($data['image']);
		$result['image'] = '/upload/images/tasks/' . $result['image_name'] . '.png';
	}
	else
	{
		$result['image'] = '/upload/images/task.png';
	}

	if (isset($data['tags']) && !empty($data['tags']))
	{
		$result['tags'] = tags_string_to_array($data['tags']);
	}

	if (isset($data['created']) && $data['created'] != '')
	{
		$result['created'] = get_string($data['created']);
	}

	if (isset($data['updated']) && $data['updated'] != '')
	{
		$result['updated'] = get_string($data['updated']);
	}

	if (isset($data['status']) && $data['status'] > 0)
	{
		$result['status'] = (integer) $data['status'];
	}

	return $result;
}

/**
 *  Данные по-умолчанию.
 *  
 *  @return  array
 */
function get_default_task_data()
{
	return [
		'id'         => 0,
		'list_id'    => 0,
		'name'       => NULL,
		'image'      => NULL,
		'image_name' => NULL,
		'tags'       => [],
		'created'    => NULL,
		'updated'    => NULL,
		'status'     => 0
	];
}

/* End of file tasks_helper.php */
/* Location: ./application/helpers/schemes/tasks_helper.php */