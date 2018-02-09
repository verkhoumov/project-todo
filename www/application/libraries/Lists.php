<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Работа со списками задач:
 *  1. Добавление.
 *  2. Изменение.
 *  3. Удаление.
 */
class Lists
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
	 *  Получение информации о списке.
	 *
	 *  Статусы:
	 *  200 - список найден.
	 *  400 - общая ошибка.
	 *  401 - ID списка не передан.
	 *  402 - пользователь не авторизован.
	 *  403 - список не найден в бд.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	public function get($list_id = 0)
	{
		$list_id = (integer) $list_id;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if ($list_id > 0)
		{
			if ($this->CodeIgniter->User->auth)
			{
				$list = $this->db_get_list_by_id($list_id);

				if (!empty($list))
				{
					$result = [
						'status' => 200,
						'data'   => get_list_data($list)
					];
				}
				else
				{
					// Список не найден в базе данных.
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
			// ID списка не передан.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Получение списков пользователя.
	 *  
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	public function get_lists_by_user_id($user_id = 0)
	{
		$user_id = (integer) $user_id;

		$result = [];

		if ($user_id > 0)
		{
			$data = $this->db_get_lists_by_user_id($user_id);

			if (!empty($data))
			{
				$result = get_lists_data($data);
			}
		}

		return $result;
	}

	/**
	 *  Создание нового списка.
	 *
	 *  Статусы:
	 *  200 - список успешно создан.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - форма содержит ошибку.
	 *  403 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   array   $form  [Данные из формы]
	 *  @return  array
	 */
	public function create($form = [])
	{
		$form = (array) $form;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if ($this->CodeIgniter->User->auth)
		{
			// Оставляем только необходимые параметры.
			$form = filter_array($form, ['title', 'description']);

			// Убираем описание, если оно не указано.
			if ($form['description'] == '')
			{
				unset($form['description']);
			}

			// Проверяем входные данные.
			$validate = $this->validate($form, ['title']);

			if ($validate['status'] == 200)
			{
				// Создаём новый список.
				$list_id = $this->db_create_list($form, $this->CodeIgniter->User->id);

				if ($list_id > 0)
				{
					$result = [
						'status' => 200,
						'data' => [
							'list_id' => $list_id
						]
					];
				}
				else
				{
					// Не удалось выполнить запрос к базе данных.
					$result['status'] = 403;
				}
			}
			else
			{
				// Форма содержит ошибку.
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
	 *  Изменение информации о списке.
	 *
	 *  Статусы:
	 *  200 - список успешно обновлён.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - ID списка не передан.
	 *  403 - форма содержит ошибки.
	 *  404 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   array   $form  [Данные из формы]
	 *  @return  array
	 */
	public function edit($list_id = 0, $form = [])
	{
		$list_id = (integer) $list_id;
		$form    = (array) $form;

		$result = [
			'status' => 400
		];

		if ($this->CodeIgniter->User->auth)
		{
			if ($list_id > 0)
			{
				// Оставляем только необходимые параметры.
				$form = filter_array($form, ['title', 'description']);

				// Убираем описание, если оно не указано.
				if ($form['description'] == '')
				{
					$form['description'] = NULL;
				}

				// Проверяем входные данные.
				$validate = $this->validate($form, ['title']);

				if ($validate['status'] == 200)
				{
					// Создаём новый список.
					if ($this->db_update_list_by_id($list_id, $form, $this->CodeIgniter->User->id))
					{
						$result['status'] = 200;
					}
					else
					{
						// Не удалось выполнить запрос к базе данных.
						$result['status'] = 404;
					}
				}
				else
				{
					// Форма содержит ошибку.
					$result = [
						'status' => 403,
						'errors' => $validate['errors']
					];
				}
			}
			else
			{
				// ID списка не передан.
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
	 *  Удаление списка. Удалять может только владелец.
	 *
	 *  Статусы:
	 *  200 - список успешно удалён.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - ID списка не передан.
	 *  403 - не удалось удалить список.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	public function delete($list_id = 0)
	{
		$list_id = (integer) $list_id;

		$result = [
			'status' => 400
		];

		if ($this->CodeIgniter->User->auth)
		{
			if ($list_id > 0)
			{
				// Если удаление пройдёт нормально, значит всё ок, иначе, 
				// либо списка не существует, либо к нему нет доступа для 
				// данного пользователя.
				if ($this->db_delete_list_by_id($list_id, $this->CodeIgniter->User->id))
				{
					$result['status'] = 200;
				}
				else
				{
					// Не удалось удалить список.
					$result['status'] = 403;
				}
			}
			else
			{
				// ID списка не передан.
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

	// ------------------------------------------------------------------------

	/**
	 *  Валидация информации о списке задач.
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
			// Название.
			if (isset($data['title']) && !$this->check_title($data['title']))
			{
				$result['errors']['title'] = TRUE;
			}

			// Описание.
			if (isset($data['description']) && !$this->check_description($data['description']))
			{
				$result['errors']['description'] = TRUE;
			}

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
	 *  Валидация названия списка.
	 *  
	 *  @param   string   $title  [Название]
	 *  @return  boolean
	 */
	public function check_title($title = '')
	{
		$title = get_string($title);

		$result = FALSE;

		// Длина названия.
		$length = mb_strlen($title, 'UTF-8');

		if ($length >= 5 && $length <= 200)
		{
			$result = TRUE;
		}

		return $result;
	}

	/**
	 *  Валидация описания списка.
	 *  
	 *  @param   string   $description  [Описание]
	 *  @return  boolean
	 */
	public function check_description($description = '')
	{
		$description = get_string($description);

		$result = FALSE;

		// Длина описания.
		$length = mb_strlen($description, 'UTF-8');

		if ($length >= 5 && $length <= 2000)
		{
			$result = TRUE;
		}

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получение информации о списке из бд.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	private function db_get_list_by_id($list_id = 0)
	{
		return $this->CodeIgniter->lists_model->get_list_by_id($list_id);
	}

	/**
	 *  Удаление списка со всеми сопутствующими данными из базы.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  boolean
	 */
	private function db_delete_list_by_id($list_id = 0, $user_id = 0)
	{
		return $this->CodeIgniter->lists_model->delete_list_by_id($list_id, $user_id);
	}

	/**
	 *  Добавление нового списка в базу данных.
	 *  
	 *  @param   array     $data     [Данные]
	 *  @param   integer   $user_id  [ID пользователя]
	 *  @return  integer
	 */
	private function db_create_list($data = [], $user_id = 0)
	{
		return $this->CodeIgniter->lists_model->add($data + ['user_id' => $user_id]);
	}

	/**
	 *  Обновление существующего списка.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @param   array    $data     [Данные]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  boolean
	 */
	private function db_update_list_by_id($list_id = 0, $data = [], $user_id = 0)
	{
		return $this->CodeIgniter->lists_model->update($data, [
			'id'      => $list_id,
			'user_id' => $user_id
		]);
	}

	/**
	 *  Получение всех списков пользователя.
	 *  
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	private function db_get_lists_by_user_id($user_id = 0)
	{
		return $this->CodeIgniter->lists_model->get_lists_by_user_id($user_id);
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
		// Модель для работы с базой данных.
		$this->CodeIgniter->load->model('lists_model');

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

/* End of file Lists.php */
/* Location: ./application/libraries/Lists.php */