<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Главная страница.
 */
class Main_controller extends MY_Controller
{
	/**
	 *  Шаблон, подключаемый в контенте.
	 *  
	 *  @var  string
	 */
	private $page = 'lists';

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
	}

	// ------------------------------------------------------------------------

	public function auth()
	{
		// Подключение компонентов.
		$this->load();

		// Меняем контент страницы.
		$this->page = 'auth';

		// Данные.
		$title = 'Вход';
		$description = 'Страница авторизации и регистрации.';

		// Схема данных.
		$data = [
			'this' => [
				'title'       => $this->get_title($title),
				'description' => $description
			],
			'header'  => [],
			'content' => [],
			'footer'  => [],
			'templates' => [
				'modal-restore' => []
			]
		];

		// Формирование страницы.
		$this->Mustache->parse('index', $this->get_index_components($data));
	}

	public function lists()
	{
		// Подключение компонентов.
		$this->load();

		// Если пользователь не авторизован, показываем форму авторизации.
		if (!$this->User->auth)
		{
			$this->auth();
			return;
		}

		// Формируем список.
		$lists = $this->get_lists();

		// Данные.
		$title = 'Списки';
		$description = 'Листинг списков с задачами.';

		// Схема данных.
		$data = [
			'this' => [
				'title'       => $this->get_title($title),
				'description' => $description
			],
			'header' => [],
			'content' => [
				'lists'       => $lists,
				'lists_count' => count($lists)
			],
			'footer' => [],
			'templates' => [
				'modal-list' => [
					'type'   => 'create',
					'button' => 'Создать'
				]
			]
		];

		// Формирование страницы.
		$this->Mustache->parse('index', $this->get_index_components($data));
	}

	public function view($list_id = 0)
	{
		// Подключение компонентов.
		$this->load();

		// Если пользователь не авторизован, направляем его на главную страницу.
		if (!$this->User->auth)
		{
			redirect('/', 'refresh');
		}

		// Меняем контент страницы.
		$this->page = 'view';

		// Подробная информация о списке.
		$list = $this->get_list($list_id);

		if (empty($list))
		{
			show_404();
		}

		// Удаляем совсем лишние данные.
		unset($list['access']);

		// Список тасков и доступ к самому списку.
		$_tasks = $this->get_tasks($list_id);
		$tasks = $_tasks['tasks'];
		$access = $_tasks['access'];

		if (!$access['read'])
		{
			show_404();
		}

		// Если пользователь является владельцем списка, показываем ему все 
		// расшаривания, а если нет, то самого владельца и возможность отписки.
		$shares = [];
		$share_id = 0;

		$shares = $this->get_shares($list_id);

		if (!$access['owner'])
		{
			if (!empty($shares))
			{
				foreach ($shares as $share)
				{
					if ($share['user_id'] == $this->User->id)
					{
						$share_id = $share['id'];
						break;
					}
				}
			}

			$shares = [];
		}

		// Если пользователь не является владельцем списка, надо узнать, кто владелец.
		if (!$access['owner'])
		{
			$user = $this->User->get($list['user_id']);

			if ($user['status'] == 200)
			{
				$list['login'] = $user['data']['login'];
				$list['image'] = $user['data']['image'];
			}
		}

		// Данные.
		$title = $list['title'];
		$description = $list['description'];
		
		// Список доступа с ID в качестве ключа.
		$shares_by_id = [];

		if (!empty($shares))
		{
			foreach ($shares as $share)
			{
				$shares_by_id[$share['id']] = $share;
			}
		}

		// Список задач с ID в качестве ключа.
		$tasks_by_id = [];

		if (!empty($tasks))
		{
			foreach ($tasks as $task)
			{
				$tasks_by_id[$task['id']] = $task;
			}
		}

		// Схема данных.
		$data = [
			'this' => [
				'title'       => $this->get_title($title),
				'description' => $description,
				'json' => [
					'list_id'  => $list_id,
					'share_id' => $share_id,
					'shares'   => count($shares_by_id) > 0 ? $shares_by_id : null,
					'tasks'    => count($tasks_by_id) > 0 ? $tasks_by_id : null
				]
			],
			'header' => [],
			'content' => [
				'list'   => $list,
				'tasks'  => $tasks,
				'access' => $access,
				'shares' => $shares
			],
			'footer' => [],
			'templates' => [
				'modal-list' => [
					'type'        => 'edit',
					'button'      => 'Сохранить',
					'title'       => $list['title'],
					'description' => nl2br($list['description'])
				],
				'modal-share'    => [],
				'modal-task'     => [],
				'template-share' => [],
				'template-tag'   => [],
				'template-task'  => []
			]
		];

		// Формирование страницы.
		$this->Mustache->parse('index', $this->get_index_components($data));
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получение всех списков пользователя.
	 *  
	 *  @return  array
	 */
	private function get_lists()
	{
		// Подключаем библиотеку для работы со списками.
		$this->load->library('Lists', NULL, 'Lists');
		$this->Lists->start();

		return $this->Lists->get_lists_by_user_id($this->User->id);
	}

	/**
	 *  Получение информации о заданном списке.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	private function get_list($list_id = 0)
	{
		// Подключаем библиотеку для работы со списками.
		$this->load->library('Lists', NULL, 'Lists');
		$this->Lists->start();

		$result = [];

		$list = $this->Lists->get($list_id);

		if ($list['status'] == 200)
		{
			$result = $list['data'];
		}

		return $result;
	}

	/**
	 *  Получение списка задач.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	private function get_tasks($list_id = 0)
	{
		// Подключаем библиотеку для работы с задачами.
		$this->load->library('Tasks', NULL, 'Tasks');
		$this->Tasks->start();

		return [
			'tasks'  => $this->Tasks->get_by_list_id($list_id),
			'access' => $this->Tasks->get_list_access($list_id, $this->User->id)
		];
	}

	/**
	 *  Получение списка расшариваний.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	private function get_shares($list_id = 0)
	{
		// Подключаем библиотеку для работы с задачами.
		$this->load->library('Shares', NULL, 'Shares');
		$this->Shares->start();

		return $this->Shares->get_by_list_id($list_id);
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
		return $this->Mustache->parse($this->page, $data, TRUE);
	}
}

/* End of file Main_controller.php */
/* Location: ./application/controllers/Main_controller.php */