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
		'image_library' => 'gd2',
		'x_axis'        => 0,
		'y_axis'        => 0
	];

	/**
	 *  Размер стороны изображения.
	 *  
	 *  @var  integer
	 */
	private $image_size = 150;

	/**
	 *  Имя поля формы, в котором хранится изображение.
	 *  
	 *  @var  string
	 */
	private $input_name = 'image';

	/**
	 *  Постфикс для сохранения оригинала.
	 *  
	 *  @var  string
	 */
	private $full_postfix = '_full';

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

			$path          = $this->get_path($type);
			$filepath      = $this->get_filepath($type, $name);
			$filepath_full = $this->get_filepath_full($type, $name);

			$this->CodeIgniter->load->library('upload', $this->upload_config + [
				'upload_path' => $path,
				'file_name'   => $name . $this->full_postfix . '.png'
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
				$this->crop($filepath_full, $filepath, $data);

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
			$resultA = unlink(FCPATH . $this->get_filepath($type, $filename));
			$resultB = unlink(FCPATH . $this->get_filepath_full($type, $filename));

			if ($resultA || $resultB)
			{
				$result = TRUE;
			}
		}

		return $result;
	}

	/**
	 *  Обрезка изображения.
	 *  
	 *  @param   string   $from  [Путь до изображения]
	 *  @param   string   $to    [Куда сохранить изображение?]
	 *  @param   array    $data  [Параметры изображения]
	 *  @return  boolean
	 */
	public function crop($from = '', $to = '', $data = [])
	{
		$from = (string) $from;
		$to   = (string) $to;
		$data = (array) $data;

		$result = FALSE;

		// Параметры обреза.
		$config = [
			'source_image'   => $from,
			'new_image'      => $to,
			'maintain_ratio' => FALSE
		] + $this->crop_config;

		// Размер исходного изображения.
		$width  = (integer) $data['image_width'];
		$height = (integer) $data['image_height'];

		if ($width > $height)
		{
			$config['x_axis'] = (integer) (($width - $height) / 2);
		}
		elseif ($width < $height)
		{
			$config['y_axis'] = (integer) (($height - $width) / 2);
		}

		// Размеры.
		$min = min([$width, $height]);
		$config['width'] = $min;
		$config['height'] = $min;

		// Выполняем ресайз.
		$this->CodeIgniter->load->library('image_lib', $config);

		if ($this->CodeIgniter->image_lib->crop())
		{
			$this->CodeIgniter->image_lib->initialize([
				'width'          => $this->image_size,
				'height'         => $this->image_size,
				'source_image'   => $to,
				'maintain_ratio' => TRUE
			] + $config);

			$result = $this->CodeIgniter->image_lib->resize();
		}

		return $result;
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

	/**
	 *  Генерация пути непосредственно до оригинала изображения.
	 *  
	 *  @param   string  $type      [Тип]
	 *  @param   string  $filename  [Имя файла]
	 *  @return  string
	 */
	private function get_filepath_full($type = '', $filename = '')
	{
		return $this->get_path($type) . $filename . $this->full_postfix . '.png';
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