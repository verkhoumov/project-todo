<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sessions
{
	/**
	 *  CodeIgniter handler.
	 *  
	 *  @var  link
	 */
	private $CodeIgniter;

	/**
	 *  Статус инициализации библиотеки.
	 *  
	 *  @var  boolean
	 */
	private $start = FALSE;

	/**
	 *  Статус работы сессии.
	 *  
	 *  @var  boolean
	 */
	private $status = FALSE;

	/**
	 *  Идентификатор сессии.
	 *  
	 *  @var  null|string
	 */
	private $session_id = NULL;

	/**
	 *  ID записи с информацией о сессии в БД.
	 *  
	 *  @var  integer
	 */
	private $session_db_id = 0;

	/**
	 *  Актуальная информация по текущей сессии.
	 *  
	 *  @var  array
	 */
	private $session_data = [];

	/**
	 *  По-умолчанию за информацию о пользователе отвечает
	 *  обработчик сессий, так как авторизация начинается
	 *  именно тут.
	 *  
	 *  @var  array
	 */
	public $user = [];

	/**
	 *  Список меток, при наличии которых в User-Agent необходимо
	 *  произвести блокировку доступа к веб-сайту.
	 *  
	 *  @var  array
	 */
	private $user_agent_banlist = [
		'curl', 'MJ12bot', 'XoviBot', 'python', 'DomainCrawler'
	];

	/**
	 *  Имя куки, где хранится значение $session_db_id.
	 */
	const SESSION_DB_ID_COOKIE_NAME = 'SessionID';

	/**
	 *  Время жизни куки, где хранится значение параметра $session_db_id.
	 */
	const SESSION_DB_ID_COOKIE_TTL = 2592000; // 30 дней

	/**
	 *  Как часто надо обновлять сессию?
	 */
	const SESSION_UPDATE = 180; // каждые 3 минуты
	
	// ------------------------------------------------------------------------

	/**
	 *  Конструктор.
	 */
	public function __construct()
	{
		$this->CodeIgniter = &get_instance();
	}

	/**
	 *  Получение информации о сессии по ключу.
	 *  
	 *  @return  mixed
	 */
	public function __get($key = '')
	{
		$key = (string) $key;

		$result = NULL;

		if ($this->is_start())
		{
			// Получение всей информации сразу.
			if ($key == 'data')
			{
				$result = $this->session_data;
			}
			elseif (array_key_exists($key, $this->session_data))
			{
				$result = $this->session_data[$key];
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Подключение сторонних компонентов для работы библиотеки.
	 *  
	 *  @return  $this
	 */
	private function load()
	{
		// Модель для работы с базой данных.
		$this->CodeIgniter->load->model('sessions_model');

		// Библиотека для работы с User-Agent.
		$this->CodeIgniter->load->library('user_agent', NULL, 'agent');

		// Библиотека для работы с сессиями через CodeIgniter.
		$this->CodeIgniter->load->library('session', NULL, 'CI_Sessions');
		
		return $this;
	}

	/**
	 *  Запуск обработчика сессии пользователя. На данном этапе происходит создание
	 *  или обновление сессии, формирование пакета необходимых данных.
	 *  
	 *  @return  $this
	 */
	public function start()
	{
		if (!$this->is_start())
		{
			$this->start = TRUE;

			$this->load()->work();
		}

		return $this;
	}

	/**
	 *  Проверка, была ли проведена инициализация.
	 *  
	 *  @return  boolean
	 */
	private function is_start()
	{
		return $this->start;
	}

	/**
	 *  Обработчик сессии пользователя: обновляет существующую сессию или создаёт новую.
	 *  
	 *  @return  $this
	 */
	private function work()
	{
		$dynamic = [];
		
		// Если к сайту обращается не пользователь, то нету смысла обрабатывать
		// его сессию и создавать дополнительную нагрузку на сервер.
		if (!$this->is_valid_session())
		{
			return $this;
		}

		// Получаем текущий идентификатор сессии и ID записи 
		// с информацией о сессии из Cookies.
		$session_id    = $this->get_session_id();
		$session_db_id = $this->get_session_db_id();

		// Пытаемся получить информацию о сессии из базы данных.
		// Если параметр [$session_db_id] не был определён ранее
		// и равен 0, то будет возвращён пустой массив.
		$session = $this->db_get_session($session_db_id, $session_id);

		// Если информация о сессии не найдена в базе данных, создаём новую запись.
		if (empty($session))
		{
			// Информация о сессии.
			$session = $this->get_session_data($session_id);

			// Создание новой записи в базе данных.
			$session_db_id = $this->db_set_session($session);

			// Сохранение ID записи с информацией о сессии в Cookies.
			$this->set_session_db_id($session_db_id);
		}
		else // Информация найдена в БД.
		{
			// Запоминаем старый идентификатор сессии на случай, если 
			// будет произведено его обновление. Так можно будет обновить
			// идентификатор в базе данных.
			$old_session_id = $session_id;

			// Данные на обновление.
			$dynamic = $this->get_session_dynamic_data($old_session_id, $session_id);
			
			// Обновляем информацию о сессии, если прошло больше N минут.
			if ($this->is_session_info_update($session))
			{
				$this->db_update_session($session_db_id, $old_session_id, get_array_changes($dynamic, $session));
			}
		}

		// Сохранение оставшихся переменных.
		$this->status = TRUE;
		$this->session_id = $session_id;
		$this->session_db_id = $session_db_id;

		// Сбор и сохранение актуальной информации о сессии и пользователе.
		$this->set_session_data($dynamic + $session);
		$this->set_user_data($session);

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получение хеша сессии.
	 *  
	 *  @return  string
	 */
	public function get_session_token()
	{
		$result = NULL;

		if ($this->is_start())
		{
			$result = $this->get_session_token_by_id($this->session_id);
		}

		return $result;
	}

	/**
	 *  Установка статуса авторизации.
	 *
	 *  @param  integer  $user_id  [ID пользователя]
	 *  @param  boolean  $status   [Статус]
	 */
	public function set_auth_status($user_id = 0, $status = FALSE)
	{
		$user_id = (integer) $user_id;
		$status  = (boolean) $status;

		return $this->db_set_auth_status($user_id, $status);
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получение идентификатора сессии с помощью функции [session_id()].
	 *  
	 *  @return  string
	 */
	private function get_session_id()
	{
		return session_id();
	}

	/**
	 *  Сохранение идентификатора сессии в параметр [$this->session_id].
	 *  
	 *  @param  string  $session_id  [Идентификатор сессии]
	 */
	private function set_session_id($session_id = '')
	{
		$this->session_id = (string) $session_id;
	}

	/**
	 *  Получение ID записи с информацией о сессии в базе данных 
	 *  из Cookies для определения сессии пользователя.
	 *  
	 *  @return  integer
	 */
	private function get_session_db_id()
	{
		return (integer) $this->CodeIgniter->CI_Sessions->tempdata(self::SESSION_DB_ID_COOKIE_NAME);
	}

	/**
	 *  Сохранение ID записи с информацией о сессии в базе данных
	 *  в Cookies, чтобы определить сессию пользователя при следующей
	 *  загрузке страницы.
	 *  
	 *  @param  integer  $session_db_id  [ID записи]
	 */
	private function set_session_db_id($session_db_id = 0)
	{
		$this->CodeIgniter->CI_Sessions->set_tempdata(self::SESSION_DB_ID_COOKIE_NAME, (integer) $session_db_id, self::SESSION_DB_ID_COOKIE_TTL);
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получение всей доступной информации о текущей сессии пользователя.
	 *  
	 *  @param   string  $session_id  [Идентификатор сессии]
	 *  @return  array
	 */
	private function get_session_data($session_id = '')
	{
		$session_id = (string) $session_id;

		// Текущее время в MySQL формате 'DATETIME'.
		$time = $this->get_time();

		return [
			'user_host'  => $this->get_user_host(),
			'user_agent' => $this->get_user_agent(),
			'token'      => $this->get_session_token_by_id($session_id),
			'created'    => $time,
			'updated'    => $time
		];
	}

	/**
	 *  Получение информации, которая может меняться каждый раз,
	 *  когда пользователь загружает страницу. Можно указать старый
	 *  и новый идентификаторы сессии и если они не будут совпадать,
	 *  значит производилось обновление идентификатора. А поэтому надо
	 *  обновить поле [token].
	 *  
	 *  @param   string  $old_session_id  [Старый идентификатор сессии]
	 *  @param   string  $session_id      [Идентификатор сессии]
	 *  @return  array
	 */
	private function get_session_dynamic_data($old_session_id = '', $session_id = '')
	{
		$old_session_id = (string) $old_session_id;
		$session_id     = (string) $session_id;

		// Текущее время в MySQL формате 'DATETIME'.
		$time = $this->get_time();

		// Основные динамичные данные.
		$result = [
			'user_host'  => $this->get_user_host(),
			'user_agent' => $this->get_user_agent(),
			'updated'    => $time
		];

		// Дополняем динамичные данные новым идентификатором сессии и
		// датой его обновления.
		if ($old_session_id != $session_id)
		{
			$result += [
				'token' => $this->get_session_token_by_id($session_id)
			];
		}

		return $result;
	}

	/**
	 *  Формирование самой актуальной информации о текущей
	 *  сессии пользователя и сохранение в параметр [$this->session_data].
	 *  
	 *  @param  array  $data  [Информация о сессии]
	 */
	private function set_session_data($data = [])
	{
		$data = (array) $data;

		$fresh = [
			'id'    => $this->session_db_id,
			'token' => $this->get_session_token_by_id($this->session_id)
		];

		$this->session_data = $fresh + get_session_data($data);
	}

	/**
	 *  Формирование самой актуальной информации о пользователе,
	 *  к которому привязана текущая сессия.
	 *  
	 *  @param  array  $data  [Информация о сессии]
	 */
	private function set_user_data($data = [])
	{
		$data = (array) $data;

		// Заменяем ключи данных на стандартные.
		$data = filter_array($data, ['user_id', 'user_auth', 'login', 'password', 'name', 'email', 'email_accept', 'email_code', 'image', 'user_created', 'user_updated', 'user_status']);
		$data = array_keys_changer($data, ['user_id' => 'id', 'user_auth' => 'auth', 'user_created' => 'created', 'user_updated' => 'updated', 'user_status' => 'status']);

		$this->user = $data;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Создание хеша идентификатора сессии.
	 *  
	 *  @param   string  $session_id  [Идентификатор сессии]
	 *  @return  string
	 */
	private function get_session_token_by_id($session_id = '')
	{
		$session_id = (string) $session_id;

		return hash('sha256', PRIVATE_SESSION_PREFIX . $session_id . PRIVATE_SESSION_POSTFIX);
	}

	/**
	 *  Получение IP-адреса пользователя.
	 *  
	 *  @param   bool    $sensetive  [Надо ли использовать чувствительный метод?]
	 *  @return  string
	 */
	private function get_user_host($sensetive = FALSE)
	{
		if ($sensetive)
		{
			return $this->CodeIgniter->input->ip_address();
		}

		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 *  Получение User-Agent пользователя.
	 *  
	 *  @return  string
	 */
	private function get_user_agent()
	{
		return $this->CodeIgniter->input->user_agent();
	}

	/**
	 *  Создание даты в формате MySQL 'DATETIME' для использования
	 *  при работе с базой данных.
	 *  
	 *  @param   integer  $time  [Временная метка UNIX]
	 *  @return  string
	 */
	private function get_time($time = 0)
	{
		$time = (integer) $time;
		$date = new DateTime();
		
		// Если была передана временная метка,
		// устанавливаем её значение для форматирования.
		if ($time > 0)
		{
			$date->setTimestamp($time);
		}
		
		return $date->format('Y-m-d H:i:s');
	}

	// ------------------------------------------------------------------------
	
	/**
	 *  Проверка сессии на валидность. Все лишние запросы будут
	 *  блокироваться или пропускаться ради экономии памяти.
	 *  
	 *  @return  boolean
	 */
	private function is_valid_session()
	{
		// Блокировка всех запросов, в заголовках которых указаны
		// запрещённые наборы символов, например, CURL-запросы.
		if ($this->is_user_agent_banned())
		{
			show_403();
		}

		// От роботов и сторонних сайтов запросы можно пропустить,
		// но сессию не подключать, чтобы не расходовать ресурсы.
		if ($this->is_external_request() || $this->is_robot_request() || $this->is_user_robot())
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 *  Проверка, содержит ли User-Agent пользователя запрещённые наборы символов, 
	 *  которые могут охарактеризовать его. Таким образом блокируются CURL-запросы, 
	 *  запросы от специальных ботов и аналогичные спам-запросы.
	 *  
	 *  @return  boolean
	 */
	private function is_user_agent_banned()
	{
		$banlist = $this->user_agent_banlist;

		if (!empty($banlist))
		{
			foreach ($banlist as $item)
			{
				if ($this->has_user_agent($item))
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 *  Проверка, отправлен ли запрос от стороннего сайта.
	 *  
	 *  @return  boolean
	 */
	private function is_external_request()
	{
		return $this->has_user_agent('http');
	}

	/**
	 *  Проверка, отправлен ли запрос от специального робота.
	 *  Это могут быть, например, боты поисковых систем.
	 *  
	 *  @return  boolean
	 */
	private function is_robot_request()
	{
		return $this->has_user_agent('bot');
	}

	/**
	 *  Проверка, является ли пользователь роботом. Это
	 *  дополнительная проверка через средства CodeIgniter.
	 *  
	 *  @return  boolean
	 */
	private function is_user_robot()
	{
		return $this->CodeIgniter->agent->is_robot();
	}

	/**
	 *  Пришло ли время для обновления информации по текущей сессии?
	 *  
	 *  @param   array    $data  [Информация по текущей сессии]
	 *  @return  boolean
	 */
	private function is_session_info_update($data = [])
	{
		$data = (array) $data;

		$result = FALSE;

		if (array_key_exists('updated', $data))
		{
			$date = time() - strtotime($data['updated']);

			if (self::SESSION_UPDATE < $date)
			{
				$result = TRUE;
			}
		}

		return $result;
	}

	/**
	 *  Чекер на наличие строки в User-Agent.
	 *  
	 *  @param   string   $line  [Искомая строка]
	 *  @return  boolean
	 */
	private function has_user_agent($line = '')
	{
		$line = (string) $line;

		$user_agent = $this->get_user_agent();

		if (stripos($user_agent, $line) !== FALSE)
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 *  Получение информации по запрошенной сессии из базы данных.
	 *  
	 *  @param   integer  $session_db_id  [ID записи]
	 *  @param   string   $session_id     [Идентификатор сессии]
	 *  @return  array
	 */
	private function db_get_session($session_db_id = 0, $session_id = '')
	{
		$session_token = $this->get_session_token_by_id($session_id);

		return $this->CodeIgniter->sessions_model->get_by_db_id($session_db_id, $session_token);
	}

	/**
	 *  Создание записи о новой сессии в базе данных.
	 *  
	 *  @param   array   $session_data  [Информация о сессии]
	 *  @return  integer
	 */
	private function db_set_session($session_data = [])
	{
		return $this->CodeIgniter->sessions_model->add($session_data);
	}

	/**
	 *  Обновление информации о текущей сессии пользователя.
	 *  
	 *  @param   integer  $session_db_id  [ID записи]
	 *  @param   string   $session_id     [Идентификатор сессии]
	 *  @param   array    $session_data   [Обновления]
	 *  @return  boolean
	 */
	private function db_update_session($session_db_id = 0, $session_id = '', $session_data = [])
	{
		$session_token = $this->get_session_token_by_id($session_id);

		return $this->CodeIgniter->sessions_model->update($session_data, [
			'id'    => $session_db_id, 
			'token' => $session_token
		]);
	}

	/**
	 *  Обновление статуса авторизации в базе данных.
	 *
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @param   boolean  $status   [Статус]
	 *  @return  boolean
	 */
	private function db_set_auth_status($user_id = 0, $status = FALSE)
	{
		return $this->CodeIgniter->sessions_model->update([
			'user_id'   => $user_id,
			'user_auth' => (integer) $status
		], ['id' => $this->session_db_id]);
	}
}

/* End of file Sessions.php */
/* Location: ./application/libraries/Sessions.php */