<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Обработка информации переданного списка todo-листингов.
 *  
 *  @param   array   $data  [Список todo-листингов]
 *  @return  array
 */
function get_lists_data($data = [])
{
	$data = (array) $data;
	
	$result = [];

	if (!empty($data))
	{
		foreach ($data as $key => $value)
		{
			$result[$key] = get_list_data($value);
		}
	}

	return $result;
}

/**
 *  Обработка информации о todo-листинге.
 *  
 *  @param   array   $data  [Информация о todo-листинге]
 *  @return  array
 */
function get_list_data($data = [], $all = TRUE)
{
	$data = (array) $data;

	$result = $all ? get_default_list_data() : [];
	
	if (isset($data['id']) && $data['id'] > 0)
	{
		$result['id'] = (integer) $data['id'];
	}

	if (isset($data['user_id']) && $data['user_id'] > 0)
	{
		$result['user_id'] = (integer) $data['user_id'];
	}

	if (isset($data['title']) && $data['title'] != '')
	{
		$result['title'] = get_string($data['title']);
	}

	if (isset($data['description']) && $data['description'] != '')
	{
		$result['description'] = get_string($data['description']);
	}

	if (isset($data['tasks_count']) && $data['tasks_count'] > 0)
	{
		$result['count']['all'] = (integer) $data['tasks_count'];
	}

	if (isset($data['tasks_completed']) && $data['tasks_completed'] > 0)
	{
		$result['count']['completed'] = (integer) $data['tasks_completed'];
	}

	if (isset($data['owner']) && $data['owner'] > 0)
	{
		$result['access']['owner'] = TRUE;
	}

	if (isset($data['access_read']) && $data['access_read'] > 0)
	{
		$result['access']['read'] = TRUE;
	}

	if (isset($data['access_edit']) && $data['access_edit'] > 0)
	{
		$result['access']['edit'] = TRUE;
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
function get_default_list_data()
{
	return [
		'id'          => 0,
		'user_id'     => 0,
		'title'       => NULL,
		'description' => NULL,
		'count' => [
			'all'       => 0,
			'completed' => 0
		],
		'access' => [
			'owner' => FALSE,
			'read'  => FALSE,
			'edit'  => FALSE
		],
		'created'     => NULL,
		'updated'     => NULL,
		'status'      => 0
	];
}

/* End of file lists_helper.php */
/* Location: ./application/helpers/schemes/lists_helper.php */