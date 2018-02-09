<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Библиотека, отвечающая за регистрацию и авторизацию пользователей 
 *  на сайте. Поддерживает следующие возможности:
 *  1. Регистрация по логину, паролю и почте.
 *  2. Авторизация по логину и паролю.
 *  3. Восстановление доступа с помощью E-mail (пересоздание пароля через ввод кода, отправленного на почту).
 */
class Auth
{
	/**
	 *  CodeIgniter handler.
	 *  
	 *  @var  link
	 */
	private $CodeIgniter;

	/**
	 *  Показывает, производилась ли инициализация библиотеки.
	 *  
	 *  @var  boolean
	 */
	private $start = FALSE;

	/**
	 *  Конструктор.
	 */
	public function __construct()
	{
		$this->CodeIgniter = &get_instance();
	}

	// ------------------------------------------------------------------------

	/**
	 *  Авторизация пользователя на сайте по логину и паролю.
	 *
	 *  Статусы:
	 *  200 - авторизация прошла успешно.
	 *  400 - общая ошибка.
	 *  401 - пользователь уже авторизован.
	 *  402 - пароль указан неверно.
	 *  500 - общая ошибка внутри обработчика get_by_login().
	 *  501 - получен пустой логин.
	 *  502 - пользовател не найден в базе данных.
	 *  
	 *  @param   string  $login     [Логин]
	 *  @param   string  $password  [Пароль]
	 *  @return  array
	 */
	public function signIn($login = '', $password = '')
	{
		$login    = (string) $login;
		$password = (string) $password;

		$result = [
			'status' => 400
		];

		if (!$this->CodeIgniter->User->auth)
		{
			$data = $this->CodeIgniter->User->get_by_login($login);

			if ($data['status'] == 200)
			{
				// Шифруем указанный пользователем пароль и сравниваем с паролем из базы.
				$hash = get_password_hash($password);

				if ($hash == $data['data']['password'])
				{
					$result['status'] = 200;

					// Обновляем статус авторизации.
					$this->CodeIgniter->User->set_auth_status($data['data']['id'], TRUE);
				}
				else
				{
					// Пароль указан неверно.
					$result['status'] = 402;
				}
			}
			else
			{
				// Все ошибки обработчика становятся 500+.
				$result['status'] = $data['status'] + 100;
			}
		}
		else
		{
			// Пользователь уже авторизован.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Регистрация нового пользователя по логину и паролю.
	 *
	 *  Статусы: см. метод create() класса User.
	 *  
	 *  @param   array   $form  [Данные из формы регистрации]
	 *  @return  array
	 */
	public function signUp($form = [])
	{
		return $this->CodeIgniter->User->create($form);
	}

	/**
	 *  Восстановление доступа к аккаунту с помощью E-mail.
	 *
	 *  Статусы:
	 *  200 - пользователь существует, код отправлен.
	 *  400 - общая ошибка.
	 *  401 - пользователь уже авторизован.
	 *  402 - E-mail не был привязан или не подтверждён.
	 *  403 - не удалось отправить письмо.
	 *  500 - общая ошибка внутри обработчика get_by_login().
	 *  501 - получен пустой логин.
	 *  502 - пользовател не найден в базе данных.
	 *  
	 *  @param   string  $login  [Логин пользователя]
	 *  @return  array
	 */
	public function restore($login = '')
	{
		$login = (string) $login;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if (!$this->CodeIgniter->User->auth)
		{
			$data = $this->CodeIgniter->User->get_by_login($login);

			if ($data['status'] == 200)
			{
				// Проверяем, привязан ли к аккаунту E-mail и подтверждён ли он.
				if (isset($data['data']['email']) && $data['data']['email_accept'])
				{
					// Генерация кода подтверждения.
					$code = get_email_code();
					$hash = get_email_code_hash($code);

					// Отправление письма с кодом подтверждения на почту.
					if ($this->CodeIgniter->User->send_restore_code_to_email($code, $data['data']['email']))
					{
						// Сохранение нового кода в базу данных в зашифрованном виде.
						$this->CodeIgniter->User->update_by_id($data['data']['id'], [
							'email_code' => $hash
						]);

						$result = [
							'status' => 200,
							'data'   => [
								'email' => get_masked_email($data['data']['email'])
							]
						];
					}
					else
					{
						// Не удалось отправить письмо с кодом подтверждения.
						$result['status'] = 403;
					}
				}
				else
				{
					// К аккаунту не был привязан E-mail, либо он не был подтверждён.
					$result['status'] = 402;
				}
			}
			else
			{
				// Все ошибки обработчика становятся 500+.
				$result['status'] = $data['status'] + 100;
			}
		}
		else
		{
			// Пользователь уже авторизован.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Изменение пароля во время восстановления доступа к аккаунту.
	 *
	 *  Статусы:
	 *  200 - пароль успешно изменён, пользователь авторизован.
	 *  400 - общая ошибка.
	 *  401 - пользователь уже авторизован.
	 *  402 - код восстановления не правильный.
	 *  403 - пароль указан не верно.
	 *  500 - общая ошибка внутри обработчика get_by_login().
	 *  501 - получен пустой логин.
	 *  502 - пользовател не найден в базе данных.
	 *  
	 *  @param   string  $login     [Логин]
	 *  @param   string  $password  [Новый пароль]
	 *  @param   string  $code      [Код подтверждения]
	 *  @return  array
	 */
	public function restoreNewPassword($login = '', $password = '', $code = '')
	{
		$login    = (string) $login;
		$password = (string) $password;
		$code     = (string) $code;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if (!$this->CodeIgniter->User->auth)
		{
			$data = $this->CodeIgniter->User->get_by_login($login);

			if ($data['status'] == 200)
			{
				if ($code != '' && $data['data']['email_code'] == get_email_code_hash($code))
				{
					// Проверяем корректность пароля.
					if ($this->CodeIgniter->User->check_password($password))
					{
						// Сохранение нового пароля в базу данных в зашифрованном виде.
						$this->CodeIgniter->User->update_by_id($data['data']['id'], [
							'password' => get_password_hash($password)
						]);

						// Обновляем статус авторизации.
						$this->CodeIgniter->User->set_auth_status($data['data']['id'], TRUE);

						$result['status'] = 200;
					}
					else
					{
						// Пароль не валиден.
						$result['status'] = 403;
					}
				}
				else
				{
					// Код восстановления доступа указан неправильно.
					$result['status'] = 402;
				}
			}
			else
			{
				// Все ошибки обработчика становятся 500+.
				$result['status'] = $data['status'] + 100;
			}
		}
		else
		{
			// Пользователь уже авторизован.
			$result['status'] = 401;
		}

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Проверка, была ли проведена инициализация.
	 *  
	 *  @return  boolean
	 */
	private function is_start()
	{
		return $this->start;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Подключение зависимостей.
	 *  
	 *  @return  $this
	 */
	private function load()
	{
		return $this;
	}

	/**
	 *  Инициализация работы библиотеки.
	 *  
	 *  @return  $this
	 */
	public function start()
	{
		if (!$this->is_start())
		{
			// Подключение зависимостей.
			$this->load();

			// Активируем библиотеку.
			$this->start = TRUE;
		}

		return $this;
	}
}

/* End of file Auth.php */
/* Location: ./application/libraries/Auth.php */