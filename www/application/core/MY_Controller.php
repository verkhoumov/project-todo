<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	/**
	 *  Параметры сайта.
	 *  
	 *  @var  array
	 */
	public $cfg = [];

	/**
	 *  Инициализация исходного контроллера.
	 */
	public function __construct()
	{
		parent::__construct();

		// Конфиги.
		$this->cfg = $this->config->item('site');
	}

	/**
	 *  Подключение всех обязательных зависимостей,
	 *  необходимых для работы сайта.
	 *  
	 *  @return  void
	 */
	protected function load()
	{
		// Парсинг шаблонов.
		$this->load->library('external/Mustache', NULL, 'Mustache');

		// Обработка сессии.
		$this->load->library('Sessions', NULL, 'Session');
		$this->Session->start();

		// Обработка пользователя.
		$this->load->library('User', NULL, 'User');
		$this->User->start();
	}

	/**
	 *  Вывод данных в формате JSON.
	 *  
	 *  @param   array    $data    [Данные]
	 *  @param   integer  $status  [Статус-код]
	 *  @return  void
	 */
	public function reply($data = [], $status = 200)
	{
		$data = (array) $data;
		$status = (integer) $status;

		$this->output
			->set_status_header($status)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data))
			->_display();

		exit;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Заголовок главной страницы.
	 *  
	 *  @param   string  $title  [Заголовок]
	 *  @return  string
	 */
	protected function get_main_title($title)
	{
		return get_title($this->cfg['title'], $title, $this->cfg['title_separator']);
	}

	/**
	 *  Заголовок страницы.
	 *  
	 *  @param   string/array  $title  [Один или несколько заголовков]
	 *  @return  string
	 */
	protected function get_title($title)
	{
		return get_title($title, $this->cfg['title'], $this->cfg['title_separator']);
	}

	/**
	 *  Описание страницы.
	 *  
	 *  @return  string
	 */
	protected function get_description()
	{
		return $this->cfg['description'];
	}

	// ------------------------------------------------------------------------

	/**
	 *  Компоненты корневого шаблона.
	 *  
	 *  @param   array   $data  [Данные для подстановки]
	 *  @return  array
	 */
	protected function get_index_components($data = [])
	{
		$_this = isset($data['this']) ? $data['this'] : [];
		unset($data['this']);

		$result = $_this + [
			'header'    => $this->get_header_render(isset($data['header']) ? $data['header'] : []),
			'content'   => $this->get_content_render(isset($data['content']) ? $data['content'] : []),
			'footer'    => $this->get_footer_render(isset($data['footer']) ? $data['footer'] : []),
			'templates' => $this->get_templates_render(isset($data['templates']) ? $data['templates'] : [])
		];

		// Дополнительная информация.
		if (!array_key_exists('url', $result)) {
			$result['url'] = $this->cfg['url'];
		}

		if (!array_key_exists('name', $result)) {
			$result['name'] = $this->cfg['title'];
		}

		if (!array_key_exists('version', $result)) {
			$result['version'] = $this->cfg['version'];
		}

		// JSON-данные, передаваемые в JS.
		$json = [];

		if (array_key_exists('json', $result)) {
			$json = array_replace_recursive($json, $result['json']);
		}

		$result['json'] = json_encode($json);

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Шаблон контента.
	 *  
	 *  @param   array   $data  [Данные для подстановки]
	 *  @return  string
	 */
	protected function get_content_render($data = [])
	{
		return NULL;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Шаблон подвала сайта.
	 *  
	 *  @param   array   $data  [Данные для подстановки]
	 *  @return  string
	 */
	protected function get_footer_render($data = [])
	{
		// Дополняем данные информацией об авторе.
		$data += $this->cfg['author'];

		return $this->Mustache->parse('footer', $data, TRUE);
	}

	// ------------------------------------------------------------------------

	/**
	 *  Подключаемые шаблоны для использования в JavaScript.
	 *  
	 *  @param   array   $data  [Данные для подстановки]
	 *  @return  string
	 */
	protected function get_templates_render($data = [])
	{
		return $this->Mustache->parse('templates', $this->get_templates_components($data), TRUE);
	}

	/**
	 *  Компоненты подключаемых шаблонов.
	 *  
	 *  @param   array   $data  [Данные для подстановки]
	 *  @return  array
	 */
	protected function get_templates_components($templates = [])
	{
		$result = [];

		if (!empty($templates))
		{
			foreach ($templates as $template => $data)
			{
				$result[$template] = $this->Mustache->parse("templates/{$template}", $data, TRUE);
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Шаблон шапки сайта.
	 *  
	 *  @param   array   $data  [Данные для подстановки]
	 *  @return  string
	 */
	protected function get_header_render($data = [])
	{
		if ($this->User->auth)
		{
			$data['auth'] = TRUE;
			$data['login'] = $this->User->login;
		}

		return $this->Mustache->parse('header', $data, TRUE);
	}
}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */