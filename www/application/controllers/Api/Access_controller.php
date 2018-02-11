<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  API для управления доступом к спискам задач.
 */
class Access_controller extends MY_Controller
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

		// Подключаем библиотеку для работы с доступом к спискам.
		$this->load->library('Shares', NULL, 'Shares');
		$this->Shares->start();
	}

	// ------------------------------------------------------------------------

	/**
	 *  Проверка наличия аккаунта в системе. Принимает логин или E-mail 
	 *  пользователя. JS-обработчик получит ID пользователя.
	 *  
	 *  @return  void
	 */
	public function check()
	{
		$this->load();

		// Логин или E-mail.
		$identity = (string) $this->input->post('identity');

		// Обрабатываем запрос.
		$result = $this->Shares->check($identity);

		$this->reply($result);
	}

	/**
	 *  Создание доступа новому пользователю. Форма содержит ID пользователя и 
	 *  права доступа. JS-обработчик должен получить ID доступа для последующей 
	 *  работы с ним и информацию о пользователе.
	 *  
	 *  @return  void
	 */
	public function create()
	{
		$this->load();

		// Данные из формы, ID списка и ID пользователя.
		$form    = (array) $this->input->post('access');
		$list_id = (integer) $this->input->post('list_id');
		$user_id = (integer) $this->input->post('user_id');

		// Обрабатываем запрос.
		$result = $this->Shares->create($form, $list_id, $user_id);

		$this->reply($result);
	}

	/**
	 *  Изменение доступа для пользователя. Форма содержит ID доступа и права доступа.
	 *  
	 *  @return  void
	 */
	public function edit()
	{
		$this->load();

		// Данные из формы и ID доступа.
		$form     = (array) $this->input->post('access');
		$share_id = (integer) $this->input->post('share_id');

		// Обрабатываем запрос.
		$result = $this->Shares->edit($form, $share_id);

		$this->reply($result);
	}

	/**
	 *  Удаление доступа к списку задач для пользователя. Форма содержит ID доступа.
	 *  Отписка может происходить как от лица владельца списка, так и от пользователя,
	 *  которому список был расшарен (отказ от подписки на список задач).
	 *  
	 *  @return  void
	 */
	public function delete()
	{
		$this->load();

		// ID доступа.
		$share_id = (integer) $this->input->post('share_id');

		// Обрабатываем запрос.
		$result = $this->Shares->delete($share_id);

		$this->reply($result);
	}
}

/* End of file Access_controller.php */
/* Location: ./application/controllers/Api/Access_controller.php */