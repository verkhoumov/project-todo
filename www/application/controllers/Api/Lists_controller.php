<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  API для работы со списками задач.
 */
class Lists_controller extends MY_Controller
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
		$this->load->library('Lists', NULL, 'Lists');
		$this->Lists->start();
	}

	// ------------------------------------------------------------------------

	/**
	 *  Создание нового списка задач. Форма содержит название и описание (не обязательно) 
	 *  будущего списка. JS-обработчик должен получить ID нового списка, чтобы после успешного 
	 *  создания выполнить перенаправление на страницу списка.
	 *  
	 *  @return  void
	 */
	public function create()
	{
		$this->load();

		// Данные из формы.
		$form = (array) $this->input->post('list');

		// Обрабатываем запрос.
		$result = $this->Lists->create($form);

		$this->reply($result);
	}

	/**
	 *  Изменение информации о списке задач. Форма содержит новое название, 
	 *  описание и ID списка.
	 *  
	 *  @return  void
	 */
	public function edit()
	{
		$this->load();

		// Данные из формы.
		$form    = (array) $this->input->post('list');
		$list_id = (integer) $this->input->post('list_id');

		// Обрабатываем запрос.
		$result = $this->Lists->edit($list_id, $form);

		$this->reply($result);
	}

	/**
	 *  Удаление списка и всех сопутствующих данных. Форма содержит ID списка.
	 *  
	 *  @return  void
	 */
	public function delete()
	{
		$this->load();

		// ID удаляемого списка.
		$list_id = (integer) $this->input->post('list_id');

		// Обрабатываем запрос.
		$result = $this->Lists->delete($list_id);

		$this->reply($result);
	}
}

/* End of file Lists_controller.php */
/* Location: ./application/controllers/Api/Lists_controller.php */