<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  API для авторизации, регистрации и восстановления доступа к аккаунту.
 */
class Auth_controller extends MY_Controller
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

		// Подключаем библиотеку для работы с авторизацией.
		$this->load->library('Auth', NULL, 'Auth');
		$this->Auth->start();
	}

	// ------------------------------------------------------------------------

	/**
	 *  При авторизации форма содержит логин и пароль пользователя.
	 *  
	 *  @return  void
	 */
	public function signIn()
	{
		$this->load();

		// Данные из формы.
		$form = (array) $this->input->post('signin');

		$login    = isset($form['login']) ? $form['login'] : NULL;
		$password = isset($form['password']) ? $form['password'] : NULL;

		// Обрабатываем запрос.
		$result = $this->Auth->signIn($login, $password);

		$this->reply($result);
	}
	
	/**
	 *  При регистрации форма содержит логин, пароль и 
	 *  E-mail пользователя (не обязательно).
	 *  
	 *  @return  void
	 */
	public function signUp()
	{
		$this->load();

		// Данные из формы.
		$form = (array) $this->input->post('signup');

		// Обрабатываем запрос.
		$result = $this->Auth->signUp($form);

		$this->reply($result);
	}
	
	/**
	 *  При восстановлении доступа форма содержит логин пользователя. Надо проверить, 
	 *  привязан ли к аккаунту E-mail и подтверждён ли он, чтобы отправить на него 
	 *  код восстановления. Обратно надо вернуть полускрытый E-mail.
	 *  
	 *  @return  void
	 */
	public function restore()
	{
		$this->load();

		// Логин пользователя.
		$login = (string) $this->input->post('login');

		// Обрабатываем запрос.
		$result = $this->Auth->restore($login);

		$this->reply($result);
	}
	
	/**
	 *  Установка нового пароля при восстановлении доступа к аккаунту. При этом форма
	 *  также содержит логин и код, полученный пользователем на E-mail, чтобы можно было 
	 *  дать ему доступ на изменение пароля и дальнейшую авторизацию.
	 *  
	 *  @return  void
	 */
	public function restoreNewPassword()
	{
		$this->load();

		// Логин, новый пароль и код восстановления пользователя.
		$login    = (string) $this->input->post('login');
		$password = (string) $this->input->post('password');
		$code     = (string) $this->input->post('code');

		// Обрабатываем запрос.
		$result = $this->Auth->restoreNewPassword($login, $password, $code);

		$this->reply($result);
	}
}

/* End of file Auth_controller.php */
/* Location: ./application/controllers/Api/Auth_controller.php */