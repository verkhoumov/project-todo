<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Обработка информации переданного списка расшариваний todo-листинга.
 *  
 *  @param   array   $data  [Список расшариваний todo-листинга]
 *  @return  array
 */
function get_shares_data($data = [])
{
	$data = (array) $data;
	
	$result = [];

	if (!empty($data))
	{
		foreach ($data as $key => $value)
		{
			$result[$key] = get_share_data($value);
		}
	}

	return $result;
}

/**
 *  Обработка информации о расшаривании todo-листинга.
 *  
 *  @param   array   $data  [Информация о расшаривании todo-листинга]
 *  @return  array
 */
function get_share_data($data = [], $all = TRUE)
{
	$data = (array) $data;

	$result = $all ? get_default_share_data() : [];
	
	if (isset($data['id']) && $data['id'] > 0)
	{
		$result['id'] = (integer) $data['id'];
	}

	if (isset($data['list_id']) && $data['list_id'] > 0)
	{
		$result['list_id'] = (integer) $data['list_id'];
	}

	if (isset($data['user_id']) && $data['user_id'] > 0)
	{
		$result['user_id'] = (integer) $data['user_id'];
	}

	if (isset($data['list_user_id']) && $data['list_user_id'] > 0)
	{
		$result['list_user_id'] = (integer) $data['list_user_id'];
	}

	if (isset($data['access_read']) && $data['access_read'] > 0)
	{
		$result['access_read'] = TRUE;
	}

	if (isset($data['access_edit']) && $data['access_edit'] > 0)
	{
		$result['access_edit'] = TRUE;
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
function get_default_share_data()
{
	return [
		'id'           => 0,
		'list_id'      => 0,
		'user_id'      => 0,
		'list_user_id' => 0,
		'access_read'  => FALSE,
		'access_edit'  => FALSE,
		'created'      => NULL,
		'updated'      => NULL,
		'status'       => 0
	];
}

/* End of file shares_helper.php */
/* Location: ./application/helpers/schemes/shares_helper.php */