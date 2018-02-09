<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  API для управления задачами в списке.
 */
class Tasks_controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 *  Подключение сторонних компонентов.
	 *  
	 *  @return  void
	 */
	protected function load()
	{
		parent::load();

		// Проверка, является ли запрос AJAX-овым.
		if (!$this->input->is_ajax_request())
		{
			$this->reply(['status' => 300]);
		}

		// Проверка, авторизован ли пользователь.
		if (!$this->User->auth)
		{
			$this->reply(['status' => 301]);
		}

		// Подключаем библиотеку для работы со списками.
		$this->load->library('Tasks', NULL, 'Tasks');
		$this->Tasks->start();
	}

	// ------------------------------------------------------------------------

	/**
	 *  Изменение состояния задачи (не выполнена/выполнена). Принимает 
	 *  ID задачи. Возвращает информацию о текущем статусе задачи.
	 *  
	 *  @return  void
	 */
	public function toggle()
	{
		$this->load();

		// ID таска.
		$task_id = (integer) $this->input->post('task_id');

		// Обрабатываем запрос.
		$result = $this->Tasks->toggle($task_id);

		$this->reply($result);
	}

	/**
	 *  Создание новой задачи. Форма содержит название задачи, статус выполнения, 
	 *  изображение (не обязательно), которое надо загрузить и список меток. JS-обработчик должен 
	 *  получить ID новой задачи и ссылку на загруженное изображение.
	 *  
	 *  @return  void
	 */
	public function create()
	{
		$this->load();

		// Данные из формы и ID списка.
		$form    = (array) $this->input->post('task');
		$list_id = (integer) $this->input->post('list_id');

		// Обрабатываем запрос.
		$result = $this->Tasks->create($form, $list_id);

		$this->reply($result);
	}

	/**
	 *  Изменение задачи. Форма содержит название задачи, статус выполнения, 
	 *  новое изображение (не обязательно) и список меток. В случае, если было 
	 *  указано новое изображение, JS-обработчик должен получить ссылку на новое 
	 *  загруженное изображение.
	 *  
	 *  @return  void
	 */
	public function edit()
	{
		$this->load();

		// Данные из формы и ID таска.
		$form    = (array) $this->input->post('task');
		$task_id = (integer) $this->input->post('task_id');

		// Обрабатываем запрос.
		$result = $this->Tasks->edit($form, $task_id);

		$this->reply($result);
	}

	/**
	 *  Удаление задачи. Обработчик принимает ID задачи.
	 *  
	 *  @return  void
	 */
	public function delete()
	{
		$this->load();

		// ID таска.
		$task_id = (integer) $this->input->post('task_id');

		// Обрабатываем запрос.
		$result = $this->Tasks->delete($task_id);

		$this->reply($result);
	}

	/**
	 *  Удаление текущего изображения задачи. Обработчик принимает ID задачи.
	 *  
	 *  @return  void
	 */
	public function deleteImage()
	{
		$this->load();

		// ID таска.
		$task_id = (integer) $this->input->post('task_id');

		// Обрабатываем запрос.
		$result = $this->Tasks->deleteImage($task_id);

		$this->reply($result);
	}
}

/* End of file Tasks_controller.php */
/* Location: ./application/controllers/Api/Tasks_controller.php */