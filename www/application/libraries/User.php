<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Библиотека для обработки пользователя.
 */
class User
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
	 *  Информация о пользователе.
	 *  
	 *  @var  array
	 */
	private $user = [];
	
	/**
	 *  Конструктор.
	 */
	public function __construct()
	{
		$this->CodeIgniter = &get_instance();
	}

	/**
	 *  Получение информации о пользователе по ключу.
	 *  
	 *  @return  array
	 */
	public function __get($key = '')
	{
		$key = (string) $key;

		if ($this->is_start())
		{
			// Получение всей информации сразу.
			if ($key == 'data')
			{
				return $this->user;
			}
			elseif (array_key_exists($key, $this->user))
			{
				return $this->user[$key];
			}
		}
	}

	/**
	 *  Изменение информации о пользователе по ключу.
	 *  
	 *  @return  array
	 */
	public function __set($key = '', $value = NULL)
	{
		$key = (string) $key;

		if ($this->is_start())
		{
			if (array_key_exists($key, $this->user))
			{
				$this->user[$key] = $value;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получение информации о пользователе, авторизованном или по ID.
	 *
	 *  Статусы:
	 *  200 - пользователь найден.
	 *  400 - общая ошибка.
	 *  401 - пользователь не найден в базе данных.
	 *  402 - пользователь не авторизован.
	 *  
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	public function get($user_id = 0)
	{
		$user_id = (integer) $user_id;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if ($user_id > 0)
		{
			$user = $this->db_get_user_by_id($user_id);

			if (!empty($user))
			{
				$result = [
					'status' => 200,
					'data'   => get_user_data($user)
				];
			}
			else
			{
				// Пользователь не найден в базе данных.
				$result['status'] = 401;
			}
		}
		else
		{
			if ($this->is_auth())
			{
				$result = [
					'status' => 200,
					'data'   => $this->user
				];
			}
			else
			{
				// Пользователь не авторизован.
				$result['status'] = 402;
			}
		}

		return $result;
	}

	/**
	 *  Получение информации о пользователе по логину.
	 *
	 *  Статусы:
	 *  200 - информация получена.
	 *  400 - общая ошибка.
	 *  401 - получен пустой логин.
	 *  402 - пользователь не найден в базе данных.
	 *  
	 *  @param   string  $login  [Логин]
	 *  @return  array
	 */
	public function get_by_login($login = '')
	{
		$login = (string) $login;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if ($login != '')
		{
			$user = $this->db_get_user_by_login($login);

			if (!empty($user))
			{
				$result = [
					'status' => 200,
					'data'   => get_user_data($user)
				];
			}
			else
			{
				// Пользователь не найден в базе данных.
				$result['status'] = 402;
			}
		}
		else
		{
			// Логин не может быть пустым.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Обновление статуса авторизации пользователя.
	 *  
	 *  @param  integer  $user_id  [ID пользователя]
	 *  @param  boolean  $status   [Статус]
	 */
	public function set_auth_status($user_id = 0, $status = FALSE)
	{
		$user_id = (integer) $user_id;
		$status = (boolean) $status;

		if (($status && !$this->is_auth()) || 
			(!$status && $this->is_auth()))
		{
			// Обновляем значение в базе данных.
			$this->CodeIgniter->Session->set_auth_status($user_id, $status);

			// Обновляем значение объекта.
			$this->auth = $status;
		}

		return $status;
	}

	/**
	 *  Создание нового пользователя.
	 *
	 *  Статусы:
	 *  200 - пользователь успешно зарегистрирован и авторизован.
	 *  400 - общая ошибка.
	 *  401 - пользователь уже авторизован.
	 *  402 - форма содержит ошибку.
	 *  403 - пользователь уже создан.
	 *  404 - не удалось создать пользователя (ошибка БД).
	 *  
	 *  @param   array   $form  [Данные из формы регистрации]
	 *  @return  array
	 */
	public function create($form = [])
	{
		$form = (array) $form;

		$result = [
			'status' => 400,
			'errors' => []
		];

		if (!$this->is_auth())
		{
			// Оставляем только необходимые для регистрации параметры.
			$form = filter_array($form, ['login', 'password', 'email']);

			// Убираем E-mail, если он не указан.
			if ($form['email'] == '')
			{
				unset($form['email']);
			}

			// Проверяем входные данные.
			$validate = $this->validate($form, ['login', 'password']);

			if ($validate['status'] == 200)
			{
				$user = $this->get_by_login($form['login']);

				if ($user['status'] != 200)
				{
					// Шифруем пароль.
					$form['password'] = get_password_hash($form['password']);

					// Регистрируем нового пользователя.
					$user_id = $this->db_create_user($form);

					if ($user_id > 0)
					{
						$result['status'] = 200;

						// Обновляем статус авторизации.
						$this->set_auth_status($user_id, TRUE);
					}
					else
					{
						// Не удалось выполнить запрос к базе данных.
						$result['status'] = 404;
					}
				}
				else
				{
					// Пользователь с таким логином уже зарегистрирован.
					$result['status'] = 403;
				}
			}
			else
			{
				// Форма регистрации содержит ошибку.
				$result = [
					'status' => 402,
					'errors' => $validate['errors']
				];
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
	 *  Изменение информации о пользователе.
	 *
	 *  Статусы:
	 *  200 - изменения внесены успешно.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - форма содержит ошибку.
	 *  403 - не удалось загрузить изображение.
	 *  404 - не удалось выполнить запрос к базе данных.
	 *  
	 *  @param   array   $form  [Данные из формы]
	 *  @return  array
	 */
	public function edit($form = [])
	{
		$form = (array) $form;

		$result = [
			'status' => 400,
			'errors' => [],
			'data'   => []
		];

		if ($this->is_auth())
		{
			// Оставляем только необходимые параметры.
			$form = filter_array($form, ['password', 'name', 'email']);

			// Удаляем параметры, для которых не установлено значение.
			if (!empty($form))
			{
				foreach ($form as $key => $value)
				{
					if ($value == '')
					{
						if ($key == 'password')
						{
							unset($form[$key]);
						}
						else
						{
							$form[$key] = NULL;
						}
					}
				}
			}

			// Проверяем входные данные.
			$validate = $this->validate($form);

			if ($validate['status'] == 200)
			{
				// Если указан пароль, выполняем его шифрование.
				if (isset($form['password']))
				{
					$form['password'] = get_password_hash($form['password']);
				}

				// Если указана новая почта, делаем сброс активации.
				if (isset($form['email']) && $form['email'] != $this->email)
				{
					$form['email_accept'] = 0;
				}

				// Пытаемся загрузить изображение, если оно указано.
				$image = NULL;
				$image_error = FALSE;

				if (isset($_FILES) && !empty($_FILES))
				{
					// Пробуем удалить старое изображение.
					$this->deleteImage();

					// Загружаем новое.
					$upload = $this->CodeIgniter->Image->upload('user');

					if ($upload['status'])
					{
						$form['image'] = $upload['name'];
						$image = $upload['path'];
					}
					else
					{
						$result['error'] = $upload['error'];
						$image_error = TRUE;
					}
				}
				
				if (!$image_error)
				{
					// Обновляем информацию.
					if (!empty($form) && $this->update($form))
					{
						$result['status'] = 200;

						if ($image)
						{
							$result['data']['image'] = $image;
						}
					}
					else
					{
						// Не удалось обновить данные.
						$result['status'] = 404;
					}
				}
				else
				{
					// Не удалось загрузить изображение.
					$result['status'] = 403;
				}
			}
			else
			{
				// Форма регистрации содержит ошибку.
				$result = [
					'status' => 402,
					'errors' => $validate['errors']
				];
			}
		}
		else
		{
			// Пользователь не авторизован.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Обновление информации об авторизованном пользователе.
	 *  
	 *  @param   array     $data  [Данные]
	 *  @return  boolean
	 */
	public function update($data = [])
	{
		return $this->update_by_id($this->id, $data);
	}

	/**
	 *  Обновление информации о пользователе по ID.
	 *  
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @param   array    $data     [Данные]
	 *  @return  array
	 */
	public function update_by_id($user_id = 0, $data = [])
	{
		$user_id = (integer) $user_id;
		$data    = (array) $data;

		$result = FALSE;
	
		if ($user_id > 0 && !empty($data))
		{
			$result = $this->db_update_by_id($user_id, $data);
		}

		return $result;
	}

	/**
	 *  Удаление аватара пользователя.
	 *
	 *  Статусы:
	 *  200 - изображение удалено.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - не удалось выполнить запрос к бд.
	 *  
	 *  @return  array
	 */
	public function deleteImage()
	{
		$result = [
			'status' => 400
		];

		if ($this->is_auth())
		{
			if ($this->image_name &&  
				$this->CodeIgniter->Image->delete('user', $this->image_name) && 
				$this->update(['image' => NULL]))
			{
				$result['status'] = 200;
			}
			else
			{
				// Не удалось выполнить запрос.
				$result['status'] = 402;
			}
		}
		else
		{
			// Пользователь не авторизован.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Отправка письма с кодом подтверждения.
	 *
	 *  Статусы:
	 *  200 - письмо с кодом отправлено.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - почта не указана.
	 *  403 - почта уже подтверждена.
	 *  404 - не удалось отправить письмо с кодом.
	 *  
	 *  @return  array
	 */
	public function acceptEmail()
	{
		$result = [
			'status' => 400
		];

		if ($this->is_auth())
		{
			if ($this->email != '')
			{
				if (!$this->email_accept)
				{
					// Генерация кода подтверждения.
					$code = get_email_code();
					$hash = get_email_code_hash($code);

					// Отправление письма с кодом подтверждения на почту.
					if ($this->send_accept_code_to_email($code, $this->email))
					{
						// Сохранение нового кода в базу данных в зашифрованном виде.
						$this->update([
							'email_code' => $hash
						]);

						$result['status'] = 200;
					}
					else
					{
						// Не удалось отправить письмо с кодом подтверждения.
						$result['status'] = 404;
					}
				}
				else
				{
					// Почта уже подтверждена.
					$result['status'] = 403;
				}
			}
			else
			{
				// Сначала необходимо указать почту.
				$result['status'] = 402;
			}
		}
		else
		{
			// Пользователь не авторизован.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Проверка кода подтверждения.
	 *
	 *  Статусы:
	 *  200 - почта успешно подтверждена.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - код не совпадает с отправленным на почту.
	 *  
	 *  @param   string  $code  [Код]
	 *  @return  array
	 */
	public function acceptEmailCode($code = '')
	{
		$code = (string) $code;

		$result = [
			'status' => 400
		];

		if ($this->is_auth())
		{
			if ($this->email_code == get_email_code_hash($code))
			{
				// Подтверждаем почту.
				$this->update([
					'email_accept' => 1
				]);

				$result['status'] = 200;
			}
			else
			{
				// Код указан неверно.
				$result['status'] = 402;
			}
		}
		else
		{
			// Пользователь не авторизован.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Поиск пользователя по логину или E-mail.
	 *  
	 *  @param   string   $identity  [Лоигн или E-mail]
	 *  @return  array
	 */
	public function find_user_by_login_or_email($identity = '')
	{
		$identity = (string) $identity;

		return $this->db_find_user_by_login_or_email($identity);
	}

	// ------------------------------------------------------------------------

	/**
	 *  Валидация информации о пользователе, полученной из формы, например при AJAX-заросе.
	 *
	 *  Статуса:
	 *  200 - всё ок.
	 *  400 - данные содержат ошибки.
	 *  
	 *  @param   array   $data         [Данные из формы]
	 *  @param   array   $strict_data  [Данные, которые должны быть указаны]
	 *  @return  array
	 */
	public function validate($data = [], $strict_data = [])
	{
		$data        = (array) $data;
		$strict_data = (array) $strict_data;

		$result = [
			'status' => 200,
			'errors' => []
		];

		if (!empty($data))
		{
			// Логин.
			if (isset($data['login']) && !$this->check_login($data['login']))
			{
				$result['errors']['login'] = TRUE;
			}

			// Пароль.
			if (isset($data['password']) && !$this->check_password($data['password']))
			{
				$result['errors']['password'] = TRUE;
			}

			// Имя.
			if (isset($data['name']) && !$this->check_name($data['name']))
			{
				$result['errors']['name'] = TRUE;
			}

			// Почта.
			if (isset($data['email']) && !$this->check_email($data['email']))
			{
				$result['errors']['email'] = TRUE;
			}

			// TODO
			// IMAGE

			// Проверка данных на наличие.
			if (!empty($strict_data))
			{
				foreach ($strict_data as $value)
				{
					if (!isset($data[$value]))
					{
						$result['errors'][$value] = TRUE;
					}
				}
			}

			if (count($result['errors']))
			{
				$result['status'] = 400;
			}
		}

		return $result;
	}

	/**
	 *  Валидация логина.
	 *  
	 *  @param   string   $login  [Логин]
	 *  @return  boolean
	 */
	public function check_login($login = '')
	{
		$login = get_string($login);

		$result = FALSE;

		// Длина логина.
		$length = strlen($login);

		if (!preg_match('#[^0-9A-Z\_]+#i', $login) && $length >= 3 && $length <= 20)
		{
			$result = TRUE;
		}

		return $result;
	}

	/**
	 *  Валидация пароля.
	 *  
	 *  @param   string   $password  [Пароль]
	 *  @return  boolean
	 */
	public function check_password($password = '')
	{
		$password = get_string($password);

		$result = FALSE;

		// Длина пароля.
		$length = mb_strlen($password, 'UTF-8');

		if ($length >= 5 && $length <= 60)
		{
			$result = TRUE;
		}

		return $result;
	}

	/**
	 *  Валидация имени.
	 *  
	 *  @param   string   $name  [Пароль]
	 *  @return  boolean
	 */
	public function check_name($name = '')
	{
		$name = get_string($name);

		$result = FALSE;

		// Длина имени.
		$length = mb_strlen($name, 'UTF-8');

		if ($length >= 2 && $length <= 30)
		{
			$result = TRUE;
		}

		return $result;
	}

	/**
	 *  Валидация E-mail.
	 *  
	 *  @param   string   $email  [E-mail]
	 *  @return  boolean
	 */
	public function check_email($email = '')
	{
		$email = get_string($email);

		$result = FALSE;

		// Длина E-mail.
		$length = strlen($email);

		if (filter_var($email, FILTER_VALIDATE_EMAIL) && $length >= 5 && $length <= 60)
		{
			$result = TRUE;
		}

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Отправка письма с кодом восстановления доступа.
	 *  
	 *  @param   string   $code   [Код]
	 *  @param   string   $email  [Почта получателя]
	 *  @return  boolean
	 */
	public function send_restore_code_to_email($code = '', $email = '')
	{
		$code = (string) $code;
		$email = (string) $email;

		// Библиотека для работы с почтой.
		$this->CodeIgniter->load->library('email');

		// Текст сообщения.
		$message = $this->CodeIgniter->Mustache->parse('email/restore', [
			'code' => $code
		], TRUE);

		// Настройки для отправки почты.
		$this->CodeIgniter->email->initialize([
			'mailtype' => 'html',
			'protocol' => 'sendmail'
		]);

		$this->CodeIgniter->email->from($this->CodeIgniter->cfg['noreply']['email'], $this->CodeIgniter->cfg['noreply']['name']);
		$this->CodeIgniter->email->to($email);
		$this->CodeIgniter->email->subject('Код восстановления');
		$this->CodeIgniter->email->message(nl2br($message));

		return $this->CodeIgniter->email->send();
	}

	/**
	 *  Отправка письма с кодом подтверждения доступа.
	 *  
	 *  @param   string   $code   [Код]
	 *  @param   string   $email  [Почта получателя]
	 *  @return  boolean
	 */
	public function send_accept_code_to_email($code = '', $email = '')
	{
		$code = (string) $code;
		$email = (string) $email;

		// Библиотека для работы с почтой.
		$this->CodeIgniter->load->library('email');

		// Текст сообщения.
		$message = $this->CodeIgniter->Mustache->parse('email/accept', [
			'code' => $code
		], TRUE);

		// Настройки для отправки почты.
		$this->CodeIgniter->email->initialize([
			'mailtype' => 'html',
			'protocol' => 'sendmail'
		]);

		$this->CodeIgniter->email->from($this->CodeIgniter->cfg['noreply']['email'], $this->CodeIgniter->cfg['noreply']['name']);
		$this->CodeIgniter->email->to($email);
		$this->CodeIgniter->email->subject('Код подтверждения');
		$this->CodeIgniter->email->message(nl2br($message));

		return $this->CodeIgniter->email->send();
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получение информации о пользователе по ID.
	 *  
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	private function db_get_user_by_id($user_id = 0)
	{
		return $this->CodeIgniter->users_model->get_one(['id' => $user_id]);
	}

	/**
	 *  Получение информации о пользователе по логину.
	 *  
	 *  @param   string  $login  [Логин]
	 *  @return  array
	 */
	private function db_get_user_by_login($login = '')
	{
		return $this->CodeIgniter->users_model->get_one(['login' => $login]);
	}

	/**
	 *  Обновление информации о пользователе по ID.
	 *  
	 *  @param   integer  $user_id  [ID пользователья]
	 *  @param   array    $data     [Обновления]
	 *  @return  boolean
	 */
	private function db_update_by_id($user_id = 0, $data = [])
	{
		return $this->CodeIgniter->users_model->update($data, ['id' => $user_id]);
	}

	/**
	 *  Поиск пользователя по логину или E-mail.
	 *  
	 *  @param   string   $identity  [Логин или E-mail]
	 *  @return  array
	 */
	private function db_find_user_by_login_or_email($identity = '')
	{
		return $this->CodeIgniter->users_model->find_user_by_login_or_email($identity);
	}

	/**
	 *  Создание нового пользователя.
	 *  
	 *  @param   array     $data  [Данные из формы]
	 *  @return  integer
	 */
	private function db_create_user($data = [])
	{
		return $this->CodeIgniter->users_model->add($data);
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

	/**
	 *  Проверка авторизации пользователя
	 *  
	 *  @return  boolean
	 */
	private function is_auth()
	{
		return $this->auth;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Подключение зависимостей.
	 *  
	 *  @return  $this
	 */
	private function load()
	{
		// Модель для работы с базой данных.
		$this->CodeIgniter->load->model('users_model');

		// Библиотека для работы с изображениями.
		$this->CodeIgniter->load->library('Image', NULL, 'Image');
		$this->CodeIgniter->Image->start();
		
		return $this;
	}

	/**
	 *  Инициализация работы библиотеки.
	 *  
	 *  @return  $this
	 */
	public function start($user = [])
	{
		$user = (array) $user;
		
		if (!$this->is_start())
		{
			// Подключение зависимостей.
			$this->load();

			// Конфигурация рабочей области.
			if (!empty($user))
			{
				$this->configure($user);
			}
			else
			{
				$this->configure($this->CodeIgniter->Session->user);
			}

			// Активируем библиотеку.
			$this->start = TRUE;
		}

		return $this;
	}

	/**
	 *  Обработка данных о пользователе, переданных из
	 *  обработчика сессии либо вручную.
	 *  
	 *  @param   array   $user  [Информация о пользователе]
	 *  @return  void
	 */
	public function configure($user = [])
	{
		$this->user = get_user_data($user);

		return $this;
	}
}

/* End of file User.php */
/* Location: ./application/libraries/User.php */