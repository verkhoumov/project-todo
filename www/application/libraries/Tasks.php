<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Менеджер для работы с задачами.
 */
class Tasks
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
	 *  Получение информации о таске.
	 *
	 *  Статусы:
	 *  200 - таск найден.
	 *  400 - общая ошибка.
	 *  401 - ID таска не передан.
	 *  402 - пользователь не авторизован.
	 *  403 - доступ к просмотру таска отсутствует.
	 *  404 - информация о таске не найдена в бд.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @param   array    $access   [Доступ (если есть)]
	 *  @return  array
	 */
	public function get($task_id = 0, $access = [])
	{
		$task_id = (integer) $task_id;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if ($task_id > 0)
		{
			if ($this->CodeIgniter->User->auth)
			{
				if (empty($access))
				{
					$access = $this->get_task_access($task_id);
				}

				if ($access['read'])
				{
					$data = $this->db_get_by_id($task_id);

					if (!empty($data))
					{
						$result = [
							'status' => 200,
							'data'   => get_task_data($data)
						];
					}
					else
					{
						// Информация о таске не найдена.
						$result['status'] = 404;
					}
				}
				else
				{
					// У пользователя нет доступа к просмотру таска.
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
			// ID таска не передан.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Меняем состояние таска (активируем/деактивируем).
	 *
	 *  Статусы:
	 *  200 - состояние успешно изменено.
	 *  400 - общая ошибка.
	 *  401 - ID таска не указан.
	 *  402 - пользователь не авторизован.
	 *  403 - у пользователя нет доступа к изменению таска.
	 *  404 - таск не найден в базе.
	 *  405 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @return  array
	 */
	public function toggle($task_id = 0)
	{
		$task_id = (integer) $task_id;

		$result = [
			'status' => 400,
			'data'   => []
		];

		if ($task_id > 0)
		{
			if ($this->CodeIgniter->User->auth)
			{
				$access = $this->get_task_access($task_id);

				if ($access['edit'])
				{
					// Загружаем информацию о таске.
					$task = $this->get($task_id, $access);

					if ($task['status'] == 200 && $task['data']['id'] > 0)
					{
						$new_status = (integer) !$task['data']['status'];

						if ($this->db_update_by_id($task_id, ['status' => $new_status]))
						{
							$result = [
								'status' => 200,
								'data'   => [
									'status' => $new_status
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
						// Задача не найдена в базе данных.
						$result['status'] = 404;
					}
				}
				else
				{
					// У пользователя нет прав доступа для изменения задачи.
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
			// ID таска не указан.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Создание нового таска.
	 *
	 *  Статусы:
	 *  200 - таск успешно создан.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - пользователь не имеет достаточных прав для 
	 *  	создания таска (ну или список не существует).
	 *  403 - форма содержит ошибки.
	 *  404 - не удалось загрузить изображение.
	 *  405 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   array    $form     [Данные из формы]
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	public function create($form = [], $list_id = 0)
	{
		$form    = (array) $form;
		$list_id = (integer) $list_id;

		$result = [
			'status' => 400,
			'data'   => [],
			'error'  => ''
		];

		if ($this->CodeIgniter->User->auth)
		{
			$access = $this->get_list_access($list_id);

			if ($access['edit'])
			{
				// Оставляем только необходимые параметры.
				$form = filter_array($form, ['name', 'status', 'tags']);

				// Проверяем входные данные.
				$validate = $this->validate($form, ['name']);

				if ($validate['status'] == 200)
				{
					$tags = [];

					// Форматируем список меток.
					if (isset($form['tags']))
					{
						if (empty($form['tags']))
						{
							$form['tags'] = NULL;
						}
						else
						{
							$form['tags'] = tags_array_to_string($form['tags']);
							$tags = tags_string_to_array($form['tags']);
						}
					}
					else
					{
						$form['tags'] = NULL;
					}

					// Приводим статус к 1 или 0.
					$status = 0;

					if (isset($form['status']))
					{
						$form['status'] = (boolean) $form['status'];
						$form['status'] = (integer) $form['status'];
						$status = $form['status'];
					}

					// Пытаемся загрузить изображение, если оно указано.
					$image = NULL;
					$image_error = FALSE;

					if (isset($_FILES) && !empty($_FILES))
					{
						$upload = $this->CodeIgniter->Image->upload('task');

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
						// Создаём новый таск.
						$task_id = $this->db_create_task($form + ['list_id' => $list_id]);

						if ($task_id > 0)
						{
							$result = [
								'status' => 200,
								'data' => [
									'status' => $status,
									'id'     => $task_id,
									'name'   => get_clear_string($form['name']),
									'tags'   => $tags
								]
							];

							if (isset($image))
							{
								$result['data']['image'] = $image;
							}
						}
						else
						{
							// Не удалось выполнить запрос к базе данных.
							$result['status'] = 405;
						}
					}
					else
					{
						// Не удалось загрузить изображение.
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
				// Недостаточно прав для доступа.
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
	 *  Изменение существующего таска.
	 *
	 *  Статусы:
	 *  200 - таск успешно обновлён.
	 *  400 - общая ошибка.
	 *  401 - пользователь не авторизован.
	 *  402 - пользователь не имеет достаточных прав изменения списка.
	 *  403 - форма содержит ошибки.
	 *  404 - не удалось загрузить изображение.
	 *  405 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   array    $form     [Данные из формы]
	 *  @param   integer  $task_id  [ID таска]
	 *  @return  array
	 */
	public function edit($form = [], $task_id = 0)
	{
		$form    = (array) $form;
		$task_id = (integer) $task_id;

		$result = [
			'status' => 400,
			'data'   => [],
			'errors' => []
		];

		if ($this->CodeIgniter->User->auth)
		{
			$access = $this->get_task_access($task_id);

			if ($access['edit'])
			{
				// Оставляем только необходимые параметры.
				$form = filter_array($form, ['name', 'status', 'tags']);

				// Проверяем входные данные.
				$validate = $this->validate($form, ['name']);

				if ($validate['status'] == 200)
				{
					$tags = [];

					// Форматируем список меток.
					if (isset($form['tags']))
					{
						if (empty($form['tags']))
						{
							$form['tags'] = NULL;
						}
						else
						{
							$form['tags'] = tags_array_to_string($form['tags']);
							$tags = tags_string_to_array($form['tags']);
						}
					}
					else
					{
						$form['tags'] = NULL;
					}

					// Приводим статус к 1 или 0.
					$status = 0;

					if (isset($form['status']))
					{
						$form['status'] = (boolean) $form['status'];
						$form['status'] = (integer) $form['status'];
						$status = $form['status'];
					}
					else
					{
						$form['status'] = 0;
						$status = 0;
					}

					// Пытаемся загрузить изображение, если оно указано.
					$image = NULL;
					$image_error = FALSE;

					if (isset($_FILES) && !empty($_FILES))
					{
						// Пробуем удалить старое изображение.
						$this->deleteImage($task_id);

						// Загружаем новое.
						$upload = $this->CodeIgniter->Image->upload('task');

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
						// Обновляем таск.
						if (!empty($form) && $this->db_update_by_id($task_id, $form))
						{
							$result = [
								'status' => 200,
								'data' => [
									'status' => $status,
									'id'     => $task_id,
									'name'   => get_clear_string($form['name']),
									'tags'   => $tags
								]
							];

							if (isset($image))
							{
								$result['data']['image'] = $image;
							}
						}
						else
						{
							// Не удалось выполнить запрос к базе данных.
							$result['status'] = 405;
						}
					}
					else
					{
						// Не удалось загрузить изображение.
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
				// Недостаточно прав для доступа.
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
	 *  Удаление таска.
	 *
	 *  Статусы:
	 *  200 - таск успешно удалён.
	 *  400 - общая ошибка.
	 *  401 - ID таска не указан.
	 *  402 - пользователь не авторизован.
	 *  403 - у пользователя недостаточно прав для удаления таска или таск отсутствует.
	 *  404 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @return  array
	 */
	public function delete($task_id = 0)
	{
		$task_id = (integer) $task_id;

		$result = [
			'status' => 400
		];

		if ($task_id > 0)
		{
			if ($this->CodeIgniter->User->auth)
			{
				$access = $this->get_task_access($task_id);

				if ($access['edit'])
				{
					// Пробуем удалить старое изображение.
					$this->deleteImage($task_id);

					if ($this->db_delete_by_id($task_id))
					{
						$result['status'] = 200;
					}
					else
					{
						// Не удалось выполнить запрос к бд.
						$result['status'] = 404;
					}
				}
				else
				{
					// У пользователя нет прав доступа для изменения задачи.
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
			// ID таска не указан.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Удаление изображения к таску.
	 *
	 *  Статусы:
	 *  200 - изображение таска успешно удалено.
	 *  400 - общая ошибка.
	 *  401 - ID таска не указан.
	 *  402 - пользователь не авторизован.
	 *  403 - у пользователя недостаточно прав для удаления изображения.
	 *  404 - не удалось выполнить запрос к бд.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @return  array
	 */
	public function deleteImage($task_id = 0)
	{
		$task_id = (integer) $task_id;

		$result = [
			'status' => 400
		];

		if ($task_id > 0)
		{
			if ($this->CodeIgniter->User->auth)
			{
				$access = $this->get_task_access($task_id);

				if ($access['edit'])
				{
					// Загружаем информацию о таске, чтобы получить изображение.
					$task = $this->get($task_id, $access);

					if ($task['status'] == 200 && $task['data']['image_name'])
					{
						// Удаляем изображение.
						if ($this->CodeIgniter->Image->delete('task', $task['data']['image_name']) &&
							$this->db_update_by_id($task_id, ['image' => NULL]))
						{
							$result['status'] = 200;
						}
						else
						{
							// Не удалось выполнить запрос к бд.
							$result['status'] = 404;
						}
					}
				}
				else
				{
					// У пользователя нет прав доступа для изменения задачи.
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
			// ID таска не указан.
			$result['status'] = 401;
		}

		return $result;
	}

	/**
	 *  Получение списка всех тасков списка.
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
				$result = get_tasks_data($data);
			}
		}

		return $result;
	}

	/**
	 *  Получение прав доступа пользователя к таску.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	public function get_task_access($task_id = 0, $user_id = 0)
	{	
		return $this->get_access('task', $task_id, $user_id);
	}

	/**
	 *  Получение прав доступа пользователя к списку.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	public function get_list_access($list_id = 0, $user_id = 0)
	{	
		return $this->get_access('list', $list_id, $user_id);
	}

	/**
	 *  Получение прав доступа к списку/таску.
	 *  
	 *  @param   string   $type        [Тип проверки]
	 *  @param   integer  $element_id  [ID списка/таска]
	 *  @param   integer  $user_id     [ID пользователя]
	 *  @return  array
	 */
	private function get_access($type = 'task', $element_id = 0, $user_id = 0)
	{
		$element_id = (integer) $element_id;
		$user_id    = (integer) $user_id;

		$result = [
			'owner' => FALSE,
			'read'  => FALSE,
			'edit'  => FALSE
		];

		if ($user_id == 0 && $this->CodeIgniter->User->auth)
		{
			$user_id = $this->CodeIgniter->User->id;
		}

		if ($element_id > 0 && $user_id > 0)
		{
			$access = $type == 'task' ? $this->db_get_task_access($element_id, $user_id) : $this->db_get_list_access($element_id, $user_id);

			if (!empty($access))
			{
				if ($access['list_user_id'] == $user_id)
				{
					// Владелец списка имеет полные права доступа.
					$result = [
						'owner' => TRUE,
						'read'  => TRUE,
						'edit'  => TRUE
					];
				}
				else
				{
					// Чего не скажешь о пользователях, которым доступ был расшарен :)
					$result['read'] = (boolean) $access['access_read'];
					$result['edit'] = (boolean) $access['access_edit'];
				}
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Валидация информации о задаче.
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
			if (isset($data['name']) && !$this->check_name($data['name']))
			{
				$result['errors']['name'] = TRUE;
			}

			// Список меток.
			if (isset($data['tags']) && !$this->check_tags($data['tags']))
			{
				$result['errors']['tags'] = TRUE;
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
	 *  Валидация названия задачи.
	 *  
	 *  @param   string   $name  [Название]
	 *  @return  boolean
	 */
	public function check_name($name = '')
	{
		$name = get_string($name);

		$result = FALSE;

		// Длина названия.
		$length = mb_strlen($name, 'UTF-8');

		if ($length >= 1 && $length <= 400)
		{
			$result = TRUE;
		}

		return $result;
	}

	/**
	 *  Валидация меток для задачи.
	 *  
	 *  @param   string   $tags  [Метки]
	 *  @return  boolean
	 */
	public function check_tags($tags = [])
	{
		$tags = (array) $tags;

		$result = TRUE;

		if (!empty($tags))
		{
			$tags = get_tags($tags);

			foreach ($tags as $tag)
			{
				// Длина названия.
				$length = mb_strlen($tag, 'UTF-8');

				if (strpos($tag, '|') !== FALSE || $length < 1 || $length > 30)
				{
					$result = FALSE;

					break;
				}
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получение информации о таске.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @return  array
	 */
	private function db_get_by_id($task_id = 0)
	{
		return $this->CodeIgniter->tasks_model->get_one(['id' => $task_id]);
	}

	/**
	 *  Получение прав доступа пользователя к таску.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	private function db_get_task_access($task_id = 0, $user_id = 0)
	{
		return $this->CodeIgniter->tasks_model->get_task_access($task_id, $user_id);
	}

	/**
	 *  Получение прав доступа пользователя к списку.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	private function db_get_list_access($list_id = 0, $user_id = 0)
	{
		return $this->CodeIgniter->tasks_model->get_list_access($list_id, $user_id);
	}

	/**
	 *  Обноление информации о таске по ID.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @param   array    $data     [Обновления]
	 *  @return  boolean
	 */
	private function db_update_by_id($task_id = 0, $data = [])
	{
		return $this->CodeIgniter->tasks_model->update($data, ['id' => $task_id]);
	}

	/**
	 *  Создание нового таска.
	 *  
	 *  @param   array    $data  [Данные]
	 *  @return  integer
	 */
	private function db_create_task($data = [])
	{
		return $this->CodeIgniter->tasks_model->add($data);
	}

	/**
	 *  Удаление таска.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @return  boolean
	 */
	private function db_delete_by_id($task_id = 0)
	{
		return $this->CodeIgniter->tasks_model->delete(['id' => $task_id]);
	}

	/**
	 *  Получение списка тасков.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	private function db_get_by_list_id($list_id = 0)
	{
		return $this->CodeIgniter->tasks_model->get(['list_id' => $list_id]);
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
		$this->CodeIgniter->load->model('tasks_model');

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

/* End of file Tasks.php */
/* Location: ./application/libraries/Tasks.php */