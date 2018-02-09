<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Обработка информации переданного списка пользователей.
 *  
 *  @param   array   $data  [Список пользователей]
 *  @return  array
 */
function get_users_data($data = [])
{
	$data = (array) $data;
	
	$result = [];

	if (!empty($data))
	{
		foreach ($data as $key => $value)
		{
			$result[$key] = get_user_data($value);
		}
	}

	return $result;
}

/**
 *  Обработка информации о пользователе.
 *  
 *  @param   array   $data  [Информация о пользователе]
 *  @return  array
 */
function get_user_data($data = [], $all = TRUE)
{
	$data = (array) $data;

	$result = $all ? get_default_user_data() : [];
	
	if (isset($data['id']) && $data['id'] > 0)
	{
		$result['id'] = (integer) $data['id'];

		if (isset($data['auth']) && $data['auth'] > 0)
		{
			$result['auth'] = TRUE;
		}
	}

	if (isset($data['login']) && $data['login'] != '')
	{
		$result['login'] = get_string($data['login']);
	}

	if (isset($data['password']) && $data['password'] != '')
	{
		$result['password'] = get_string($data['password']);
	}

	if (isset($data['name']) && $data['name'] != '')
	{
		$result['name'] = get_string($data['name']);
	}

	if (isset($data['email']) && $data['email'] != '')
	{
		$result['email'] = get_string($data['email']);
	}

	if (isset($data['email_accept']) && $data['email_accept'] > 0)
	{
		$result['email_accept'] = TRUE;
	}

	if (isset($data['email_code']) && $data['email_code'] != '')
	{
		$result['email_code'] = get_string($data['email_code']);
	}

	if (isset($data['email']) && $data['email'] != '')
	{
		$result['email'] = get_string($data['email']);
	}

	if (isset($data['image']) && $data['image'] != '')
	{
		$result['image_name'] = get_string($data['image']);
		$result['image'] = '/upload/images/users/' . $result['image_name'] . '.png';
	}
	else
	{
		$result['image'] = '/upload/images/user.png';
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
function get_default_user_data()
{
	return [
		'id'           => 0,
		'auth'         => FALSE,
		'login'        => NULL,
		'password'     => NULL,
		'name'         => NULL,
		'email'        => NULL,
		'email_accept' => FALSE,
		'email_code'   => NULL,
		'image'        => NULL,
		'image_name'   => NULL,
		//'is_image'     => FALSE,
		'created'      => NULL,
		'updated'      => NULL,
		'status'       => 0
	];
}

// ------------------------------------------------------------------------

/**
 *  Шифрование пароля.
 *  
 *  @param   string  $password  [Пароль]
 *  @return  string
 */
function get_password_hash($password = '')
{
	$password = (string) $password;

	return hash('sha256', $password . PRIVATE_PASSWORD_SALT);
}

/**
 *  Маскировка E-mail.
 *  
 *  @param   string  $email  [E-mail]
 *  @return  string
 */
function get_masked_email($email = '')
{
	$email = (string) $email;

	$email  = explode('@', $email);
	$name   = implode(array_slice($email, 0, count($email) - 1), '@');
	$length = floor(strlen($name) / 2);

	return substr($name, 0, $length) . str_repeat('*', $length) . '@' . end($email);
}

/**
 *  Генерация кода подтверждения E-mail.
 *  
 *  @return  string
 */
function get_email_code()
{
	return (string) rand(100000, 999999);
}

/**
 *  Хеширование кода подтверждения E-mail.
 *  
 *  @param   string  $code  [Код]
 *  @return  string
 */
function get_email_code_hash($code = '')
{
	$code = (string) $code;

	return hash('sha256', $code);
}

/* End of file users_helper.php */
/* Location: ./application/helpers/schemes/users_helper.php */