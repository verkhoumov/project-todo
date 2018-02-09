<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Работа с доступом к спискам задач:
 *  1. Проверка наличия аккаунта.
 *  2. Создание нового доступа.
 *  3. Изменение прав доступа.
 *  4. Удаление доступа.
 */
class Shares
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
	 *  Получение информации о доступе.
	 *
	 *  Статусы:
	 *  200 - доступ найден.
	 *  400 - общая ошибка.
	 *  401 - ID доступа не передан.
	 *  402 - пользователь не авторизован.
	 *  403 - доступ не найден в бд.
	 *  
	 *  @param   integer  $share_id  [ID доступа]
	 *  @return  array
	 */
	public function get($share_id = 0)
	{
		$share_id = (integer) $share_id;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if ($share_id > 0)
		{
			if ($this->CodeIgniter->User->auth)
			{
				$share = $this->db_get_by_id($share_id);

				if (!empty($share))
				{
					$result = [
						'status' => 200,
						'data'   => get_share_data($share)
					];
				}
				else
				{
					// Доступ не найден в базе данных.
					$result['status'] = 403;
				}
			}
			else
			{
				// Пользователь не авторизован.
				$result['status'] = 402;
			}
		}
		else
		{
			// ID доступа не передан.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Проверка существования пользователя по логину или E-mail.
	 *
	 *  Статусы:
	 *  200 - пользователь найден.
	 *  400 - общая ошибка.
	 *  401 - отсутствует авторизация.
	 *  402 - полученный параметр не является логином или E-mail'ом.
	 *  403 - пользователь не найден.
	 *  404 - нельзя прописать доступ самому себе.
	 *  
	 *  @param   string  $identity  [Логин или E-mail]
	 *  @return  array
	 */
	public function check($identity = '')
	{
		$identity = (string) $identity;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if ($this->CodeIgniter->User->auth)
		{
			// Проверяем, является ли идентификатор пользователя логином или E-mail'ом.
			if ($this->CodeIgniter->User->check_login($identity) || $this->CodeIgniter->User->check_email($identity))
			{
				$user = $this->CodeIgniter->User->find_user_by_login_or_email($identity);

				if (!empty($user))
				{
					if ($user['id'] != $this->CodeIgniter->User->id)
					{
						$result = [
							'status' => 200,
							'data' => [
								'user_id' => (integer) $user['id']
							]
						];
					}
					else
					{
						// Нельзя прописать доступ самому себе.
						$result['status'] = 404;
					}
				}
				else
				{
					// Пользователь не найден.
					$result['status'] = 403;
				}
			}
			else
			{
				// Указанный идентификатор не является логином или электронной почтой.
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
	 *  Создание доступа для нового пользователя.
	 *
	 *  Статусы:
	 *  200 - доступ успешно создан.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - список не найден.
	 *  403 - пользователь не найден.
	 *  404 - пользователь уже имеет доступ к списку.
	 *  405 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   array    $form     [Данные из формы]
	 *  @param   integer  $list_id  [ID списка]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	public function create($form = [], $list_id = 0, $user_id = 0)
	{
		$form    = (array) $form;
		$list_id = (integer) $list_id;
		$user_id = (integer) $user_id;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if ($this->CodeIgniter->User->auth)
		{
			// Информация о списке.
			$list = $this->CodeIgniter->Lists->get($list_id);

			if ($list['status'] == 200 && $list['data']['id'] > 0 && $list['data']['user_id'] == $this->CodeIgniter->User->id)
			{
				// Информация о пользователе.
				$user = $this->CodeIgniter->User->get($user_id);

				if ($user['status'] == 200 && $user['data']['id'] > 0)
				{
					// Проверяем, не имеет ли пользователь доступ к списку?
					$share = $this->db_get_by_user_id($list_id, $user_id);

					if (empty($share))
					{
						// Тут можно обойтись без особой валидации, потому есть 
						// надо проверить только 1 параметр - возможность изменения.
						$access_edit = (isset($form['edit']) && $form['edit'] == 1) ? 1 : 0;

						$share_id = $this->db_create_share([
							'list_id'     => $list_id,
							'user_id'     => $user_id,
							'access_edit' => $access_edit
						]);

						if ($share_id > 0)
						{
							// В идеале, надо вернуть данные в том же виде, 
							// в котором они используются при построении карточки.
							$result = [
								'status' => 200,
								'data' => [
									'id'          => $share_id,
									'login'       => $user['data']['login'],
									'image'       => $user['data']['image'],
									'list_id'     => $list_id,
									'user_id'     => $user_id,
									'access_read' => 1,
									'access_edit' => $access_edit
								]
							];
						}
						else
						{
							// Не удалось выполнить запрос к бд.
							$result['status'] = 405;
						}
					}
					else
					{
						// Пользователь уже имеет доступ к списку.
						$result['status'] = 404;
					}
				}
				else
				{
					// Запрашиваемый пользователь не найден.
					$result['status'] = 403;
				}
			}
			else
			{
				// Запрашиваемый список не найден.
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
	 *  Изменение доступа к списку.
	 *
	 *  Статусы:
	 *  200 - изменения успешно внесены.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - доступ не найден.
	 *  403 - только автор списка может имзенять доступ.
	 *  404 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   array    $form      [Данные из формы]
	 *  @param   integer  $share_id  [ID доступа]
	 *  @return  array
	 */
	public function edit($form = [], $share_id = 0)
	{
		$form     = (array) $form;
		$share_id = (integer) $share_id;

		$result = [
			'status' => 400
		];

		if ($this->CodeIgniter->User->auth)
		{
			// Информация о доступе.
			$share = $this->get($share_id);

			if ($share['status'] == 200 && $share['data']['id'] > 0)
			{
				if ($share['data']['list_user_id'] == $this->CodeIgniter->User->id)
				{
					// Тут можно обойтись без особой валидации, потому есть 
					// надо проверить только 1 параметр - возможность изменения.
					$access_edit = (isset($form['edit']) && $form['edit'] == 1) ? 1 : 0;

					if ($this->db_update_by_id($share_id, ['access_edit' => $access_edit]))
					{
						$result = ['status' => 200];
					}
					else
					{
						// Не удалось выполнить запрос к бд.
						$result['status'] = 404;
					}
				}
				else
				{
					// Только автор списка может изменять доступ.
					$result['status'] = 403;
				}
			}
			else
			{
				// Доступ не найден.
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
	 *  Удаление доступа к списку.
	 *
	 *  Статусы:
	 *  200 - доступ удалён.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - доступ не найден.
	 *  403 - вы не можете отказаться от доступа.
	 *  404 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   integer  $share_id  [ID доступа]
	 *  @return  array
	 */
	public function delete($share_id = 0)
	{
		$share_id = (integer) $share_id;

		$result = [
			'status' => 400
		];

		if ($this->CodeIgniter->User->auth)
		{
			// Информация о доступе.
			$share = $this->get($share_id);

			if ($share['status'] == 200 && $share['data']['id'] > 0)
			{
				if ($share['data']['list_user_id'] == $this->CodeIgniter->User->id ||
					$share['data']['user_id'] == $this->CodeIgniter->User->id)
				{
					if ($this->db_delete_by_id($share_id))
					{
						$result['status'] = 200;
					}
					else
					{
						// Не удалось удалить доступ.
						$result['status'] = 404;
					}
				}
				else
				{
					// Пользователь не может отказаться от данного списка.
					$result['status'] = 403;
				}
			}
			else
			{
				// Доступ не найден.
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
	 *  Получение списка расшариваний списка.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	public function get_by_list_id($list_id = 0)
	{
		$list_id = (integer) $list_id;

		$result = [];

		if ($list_id > 0)
		{
			$data = $this->db_get_by_list_id($list_id);

			if (!empty($data))
			{
				// Запоминаем информацию о пользователях в отдельный массив, 
				// чтобы потом объединить с расшариваниями.
				$users = [];

				foreach ($data as $key => $share)
				{
					$user = filter_array($share, ['login', 'image']);
					$user = get_user_data($user, FALSE);

					$users[$key] = $user;
				}

				$data = get_shares_data($data);

				foreach ($data as $key => $share)
				{
					$result[] = $share + $users[$key];
				}
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получаем доступ к списку для заданного пользователя.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	private function db_get_by_user_id($list_id = 0, $user_id = 0)
	{
		return $this->CodeIgniter->shares_model->get_one([
			'list_id' => $list_id,
			'user_id' => $user_id
		]);
	}

	/**
	 *  Получение информации о доступе по ID.
	 *  
	 *  @param   integer  $share_id  [ID доступа]
	 *  @return  array
	 */
	private function db_get_by_id($share_id = 0)
	{
		return $this->CodeIgniter->shares_model->get_by_share_id($share_id);
	}

	/**
	 *  Создание нового доступа к списку.
	 *  
	 *  @param   array     $data  [Параметры доступа]
	 *  @return  integer
	 */
	private function db_create_share($data = [])
	{
		return $this->CodeIgniter->shares_model->add($data);
	}

	/**
	 *  Обновление информации о доступе к списку.
	 *  
	 *  @param   integer  $share_id  [ID доступа]
	 *  @param   array    $data      [Обноления]
	 *  @return  boolean
	 */
	private function db_update_by_id($share_id = 0, $data = [])
	{
		return $this->CodeIgniter->shares_model->update($data, [
			'id' => $share_id
		]);
	}

	/**
	 *  Удаление доступа.
	 *  
	 *  @param   integer  $share_id  [ID доступа]
	 *  @return  array
	 */
	private function db_delete_by_id($share_id = 0)
	{
		return $this->CodeIgniter->shares_model->delete(['id' => $share_id]);
	}

	/**
	 *  Получение списка расшариваний.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	private function db_get_by_list_id($list_id = 0)
	{
		return $this->CodeIgniter->shares_model->get_by_list_id($list_id);
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
		// Библиотека для работы со списками.
		$this->CodeIgniter->load->library('Lists', NULL, 'Lists');
		$this->CodeIgniter->Lists->start();

		// Модель для работы с базой данных.
		$this->CodeIgniter->load->model('shares_model');

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

/* End of file Shares.php */
/* Location: ./application/libraries/Shares.php */