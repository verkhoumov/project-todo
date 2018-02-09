<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  API для управления доступом к спискам задач.
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
	}

	// ------------------------------------------------------------------------

	/**
	 *  Изменение данных пользователя. Форма содержит пароль (не обязательно), 
	 *  имя (не обязательно), E-mail (не обяз.), новое изображение для загрузки 
	 *  (не обяз.). В случае загрузки изображения JS-обработчик должен получить
	 *  ссылку на него. В случае изменения E-mail JS-обработчик должен показать
	 *  кнопку подтверждения.
	 *  
	 *  @return  void
	 */
	public function edit()
	{
		$this->load();

		// Данные из формы.
		$form = (array) $this->input->post('settings');

		// Обрабатываем запрос.
		$result = $this->User->edit($form);

		$this->reply($result);
	}

	/**
	 *  Удаление текущего изображения пользователя.
	 *  
	 *  @return  void
	 */
	public function deleteImage()
	{
		$this->load();

		// Обрабатываем запрос.
		$result = $this->User->deleteImage();

		$this->reply($result);
	}

	/**
	 *  Запрос на подтверждение новой почты пользователя.
	 *  
	 *  @return  void
	 */
	public function acceptEmail()
	{
		$this->load();

		// Обрабатываем запрос.
		$result = $this->User->acceptEmail();

		$this->reply($result);
	}

	/**
	 *  Валидация кода подтверждения, отправленного на почту и 
	 *  введённого пользователем в форму.
	 *  
	 *  @return  void
	 */
	public function acceptEmailCode()
	{
		$this->load();

		// Данные из формы.
		$code = (string) $this->input->post('code');

		// Обрабатываем запрос.
		$result = $this->User->acceptEmailCode($code);

		$this->reply($result);
	}
}

/* End of file User_controller.php */
/* Location: ./application/controllers/Api/User_controller.php */