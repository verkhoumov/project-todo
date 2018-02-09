<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Выход, страница с настройками.
 */
class User_controller extends MY_Controller
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

		// Если пользователь не авторизован, направляем его на главную страницу.
		if (!$this->User->auth)
		{
			redirect('/', 'refresh');
		}
	}

	// ------------------------------------------------------------------------

	/**
	 *  Страница с настройками аккаунта.
	 *  
	 *  @return  void
	 */
	public function settings()
	{
		// Подключение компонентов.
		$this->load();

		// Данные.
		$title = 'Настройки';
		$description = 'Страница с настройками аккаунта.';

		// Схема данных.
		$data = [
			'this' => [
				'title'       => $this->get_title($title),
				'description' => $description
			],
			'header' => [],
			'content' => [
				'login'        => $this->User->login,
				'name'         => $this->User->name,
				'email'        => $this->User->email,
				'email_accept' => $this->User->email_accept,
				'image'        => $this->User->image,
				'image_name'   => $this->User->image_name
			],
			'footer' => [],
			'templates' => [
				'modal-email-accept' => []
			]
		];

		// Формирование страницы.
		$this->Mustache->parse('index', $this->get_index_components($data));
	}

	/**
	 *  Точка выхода из аккаунта.
	 *  
	 *  @return  void
	 */
	public function signOut()
	{
		// Подключение компонентов.
		$this->load();

		// Если пользователь авторизован, отключаем его.
		if ($this->User->auth)
		{
			$this->User->set_auth_status($this->User->id, FALSE);
		}

		// Направляем пользователя на главную страницу.
		redirect('/', 'refresh');
	}

	// ------------------------------------------------------------------------

	/**
	 *  REBUILD / Шаблон контента.
	 *  
	 *  @param   array   $data  [Данные для подстановки]
	 *  @return  string
	 */
	protected function get_content_render($data = [])
	{
		return $this->Mustache->parse('settings', $data, TRUE);
	}
}

/* End of file User_controller.php */
/* Location: ./application/controllers/User_controller.php */