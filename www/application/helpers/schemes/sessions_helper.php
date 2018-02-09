<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Обработка информации переданного списка сессий.
 *  
 *  @param   array   $data  [Список сессий]
 *  @return  array
 */
function get_sessions_data($data = [])
{
	$data = (array) $data;
	
	$result = [];

	if (!empty($data))
	{
		foreach ($data as $key => $value)
		{
			$result[$key] = get_session_data($value);
		}
	}

	return $result;
}

/**
 *  Обработка информации о сессии.
 *  
 *  @param   array   $data  [Информация о сессии]
 *  @return  array
 */
function get_session_data($data = [], $all = TRUE)
{
	$data = (array) $data;

	$result = $all ? get_default_session_data() : [];

	if (isset($data['id']) && $data['id'] > 0)
	{
		$result['id'] = (integer) $data['id'];
	}

	if (isset($data['user_id']) && $data['user_id'] > 0)
	{
		$result['user_id'] = (integer) $data['user_id'];
	}

	if (isset($data['user_auth']) && $data['user_auth'] == 1)
	{
		$result['user_auth'] = TRUE;
	}

	if (isset($data['user_host']) && $data['user_host'] != '')
	{
		$result['user_host'] = get_string($data['user_host']);
	}

	if (isset($data['user_agent']) && $data['user_agent'] != '')
	{
		$result['user_agent'] = get_string($data['user_agent']);
	}

	if (isset($data['token']) && $data['token'] != '')
	{
		$result['token'] = get_string($data['token']);
	}

	if (isset($data['created']) && $data['created'] != '')
	{
		$result['created'] = get_string($data['created']);
	}

	if (isset($data['updated']) && $data['updated'] != '')
	{
		$result['updated'] = get_string($data['updated']);
	}

	return $result;
}

/**
 *  Информация о сессии по-умолчанию.
 *  
 *  @return  array
 */
function get_default_session_data()
{
	return [
		'id'         => 0,
		'user_id'    => 0,
		'user_auth'  => FALSE,
		'user_host'  => NULL,
		'user_agent' => NULL,
		'token'      => NULL,
		'created'    => NULL,
		'updated'    => NULL
	];
}

/* End of file sessions_helper.php */
/* Location: ./application/helpers/schemes/sessions_helper.php */