<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Библиотека для загрузки/удаления изображений.
 */
class Image
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
	 *  Доступные форматы данных.
	 *  
	 *  @var  array
	 */
	private $types = [
		'task' => 'tasks',
		'user' => 'users'
	];

	/**
	 *  Параметры библиотеки для загрузки.
	 *  
	 *  @var  array
	 */
	private $upload_config = [
		'allowed_types' => 'gif|jpg|png',
		'max_size'      => 2048, // 2MB
		'max_width'     => 1024,
		'max_height'    => 1024
	];

	/**
	 *  Параметры библиотеки для резки.
	 *  
	 *  @var  array
	 */
	private $crop_config = [
		'image_library'  => 'gd2',
		'maintain_ratio' => FALSE,
		'width'          => 150,
		'height'         => 150
	];

	/**
	 *  Имя поля формы, в котором хранится изображение.
	 *  
	 *  @var  string
	 */
	private $input_name = 'image';

	/**
	 *  Путь до изображения.
	 *
	 *  /upload/images/[type]/
	 *  
	 *  @var  string
	 */
	private $path = 'upload/images/%s/';

	/**
	 *  Конструктор.
	 */
	public function __construct()
	{
		$this->CodeIgniter = &get_instance();
	}

	// ------------------------------------------------------------------------

	/**
	 *  Загрузка изображения из $_FILES.
	 *  
	 *  @param   string  $type  [Тип]
	 *  @return  array
	 */
	public function upload($type = '')
	{
		$type = (string) $type;

		$result = [
			'status' => FALSE,
			'name'   => NULL,
			'path'   => NULL,
			'error'  => NULL
		];

		if (array_key_exists($type, $this->types))
		{
			$name = random_string('alpha', 30);
			$path = $this->get_path($type);
			$filepath = $this->get_filepath($type, $name);

			$this->CodeIgniter->load->library('upload', $this->upload_config + [
				'upload_path' => $path,
				'file_name'   => $name . '.png'
			]);

			if (!$this->CodeIgniter->upload->do_upload($this->input_name))
			{
				$result['error'] = $this->CodeIgniter->upload->display_errors('', ' ');
			}
			else
			{
				// Информация о загруженном изображении.
				$data = $this->CodeIgniter->upload->data();

				// Режем его до заданных размеров.
				$this->crop($filepath);

				$result['status'] = TRUE;
				$result['name']   = $name;
				$result['path']   = '/' . $filepath;
			}
		}

		return $result;
	}

	/**
	 *  Удаление изображения.
	 *  
	 *  @param   string   $type      [Тип]
	 *  @param   string   $filename  [Имя файла]
	 *  @return  boolean
	 */
	public function delete($type = '', $filename = '')
	{
		$type     = (string) $type;
		$filename = (string) $filename;

		$result = FALSE;

		if (array_key_exists($type, $this->types) && $filename != '')
		{
			$result = unlink(FCPATH . $this->get_filepath($type, $filename));
		}

		return $result;
	}

	/**
	 *  Обрезка изображения.
	 *  
	 *  @param   string   $filepath  [Путь до изображения]
	 *  @return  boolean
	 */
	public function crop($filepath = '')
	{
		$filepath = (string) $filepath;

		$this->CodeIgniter->load->library('image_lib', $this->crop_config + [
			'source_image' => $filepath
		]);

		return $this->CodeIgniter->image_lib->resize();
	}

	/**
	 *  Генерация пути до каталога, куда надо сохранить изображение.
	 *  
	 *  @param   string  $type  [Тип]
	 *  @return  string
	 */
	private function get_path($type = '')
	{
		return sprintf($this->path, $this->types[$type]);
	}

	/**
	 *  Генерация пути непосредственно до изображения.
	 *  
	 *  @param   string  $type      [Тип]
	 *  @param   string  $filename  [Имя файла]
	 *  @return  string
	 */
	private function get_filepath($type = '', $filename = '')
	{
		return $this->get_path($type) . $filename . '.png';
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
		// 
		$this->CodeIgniter->load->helper('file');
		$this->CodeIgniter->load->helper('string');

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

/* End of file Image.php */
/* Location: ./application/libraries/Image.php */