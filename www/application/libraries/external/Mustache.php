<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Подключение обработчика библиотеки Mustache.
require_once APPPATH.'third_party/Mustache/Autoloader.php';

class Mustache
{
	/**
	 *  CodeIgniter handler.
	 */
	protected $CodeIgniter;

	/**
	 *  Mustache handler.
	 */
	protected $Mustache;

	/**
	 *  Параметры Mustache.
	 *  
	 *  @var  array
	 */
	protected $config = [];

	/**
	 *  Конструктор.
	 */
	public function __construct($config = [])
	{
		// CodeIgniter instance.
		$this->CodeIgniter = &get_instance();

		// Параметры Mustache.
		$this->config = (array) $config;

		// Инициализация.
		$this->start();
	}

	/**
	 *  Парсер шаблонов через библиотеку Mustache.
	 *  
	 *  @param   string   $template  [Шаблон]
	 *  @param   array    $data      [Данные для подскановки]
	 *  @param   boolean  $return    [Надо ли вернуть шаблон в виде строки?]
	 *  @return  string/boolean
	 */
	public function parse($template, $data = [], $return = FALSE)
	{
		// Загружаем шаблон базовыми средствами CodeIgniter.
		$template = $this->CodeIgniter->load->view($template, $data, TRUE);

		return $this->_parse($template, $data, $return);
	}

	/**
	 *  Парсер текста через библиотеку Mustache.
	 *  
	 *  @param   string   $string    [Строка]
	 *  @param   array    $data      [Данные для подскановки]
	 *  @param   boolean  $return    [Надо ли вернуть шаблон в виде строки?]
	 *  @return  string/boolean
	 */
	public function parse_string($string, $data = [], $return = FALSE)
	{
		return $this->_parse($string, $data, $return);
	}

	/**
	 *  Загрузка шаблона базовыми средствами CodeIgniter без подстановки данных.
	 *  
	 *  @param   string   $template  [Шаблон]
	 *  @param   boolean  $return    [Вернуть или вывести на экран?]
	 *  @return  string
	 */
	public function load($template, $return = FALSE)
	{
		return $this->CodeIgniter->load->view($template, NULL, $return);
	}

	// ------------------------------------------------------------------------
	
	/**
	 *  Инициализация библиотеки.
	 *  
	 *  @return  void
	 */
	private function start()
	{
		// Запуск Mustache.
		Mustache_Autoloader::register();

		// Объект для работы с библиотекой Mustache.
		$this->Mustache = new Mustache_Engine($this->config);
	}

	/**
	 *  Парсер Mustache.
	 *  
	 *  @param   string   $template  [Шаблон]
	 *  @param   array    $data      [Данные для подскановки]
	 *  @param   boolean  $return    [Надо ли вернуть шаблон в виде строки?]
	 *  @return  string/boolean
	 */
	private function _parse($template, $data = [], $return = FALSE)
	{
		if ($template == '')
		{
			return FALSE;
		}

		// Парсинг шаблона с помощью Mustache.
		$template = $this->Mustache->render($template, $data);

		// Добавление обработанного шаблона в поток для
		// последующего вывода на экран.
		if ($return === FALSE)
		{
			$this->CodeIgniter->output->append_output($template);
		}

		// Возвращение обработанного шаблона в переменную.
		return $template;
	}
}

/* End of file Mustache.php */
/* Location: ./application/libraries/Mustache.php */