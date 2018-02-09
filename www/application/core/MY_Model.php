<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model
{
	/**
	 *  Таблицы базы данных.
	 *
	 *  @var  string
	 */
	protected $db_sessions = 'sessions';
	protected $db_users    = 'users';
	protected $db_socials  = 'socials';
	protected $db_lists    = 'lists';
	protected $db_tasks    = 'tasks';
	protected $db_shares   = 'shares';
	protected $db_tags     = 'tags';

	/**
	 *  Рабочая таблица для основных запросов.
	 *  
	 *  @var  string
	 */
	protected $table = NULL;

	/**
	 *  Поле, используемое в качестве ключа.
	 *  
	 *  @var  string
	 */
	protected $key_field = NULL;

	/**
	 *  Конструктор для работы с базой данных.
	 */
	public function __construct()
	{
		parent::__construct();

		// Подключение к базе данных.
		$this->load->database();
	}

	// ------------------------------------------------------------------------

	/**
	 *  Поиск записей в базе данных.
	 *  
	 *  @param   array    $where   [Условия поиска]
	 *  @param   array    $order   [Сортировка]
	 *  @param   integer  $limit   [LIMIT]
	 *  @param   integer  $offset  [OFFSET]
	 *  @return  array
	 */
	public function get($where = [], $order = [], $limit = 0, $offset = 0)
	{
		$this->db->from($this->table);

		// Условия запроса.
		$this->set_conditions($where, $order, $limit, $offset);

		// Получение найденных записей.
		$result = $this->get_array($this->key_field);

		// Сбрасываем запрос.
		$this->db->reset_query();
		$this->key_field = NULL;

		return $result;
	}

	/**
	 *  Поиск и получение только одной записи.
	 *  
	 *  @param   array    $where   [Условия поиска]
	 *  @param   array    $order   [Сортировка]
	 *  @param   integer  $offset  [OFFSET]
	 *  @return  array
	 */
	public function get_one($where = [], $order = [], $offset = 0)
	{
		$this->db->from($this->table);

		// Условия запроса.
		$this->set_conditions($where, $order, 0, $offset);

		// Получение одной записи.
		$result = $this->get_row();

		// Сбрасываем запрос.
		$this->db->reset_query();

		return $result;
	}

	/**
	 *  Загрузка записи по ID.
	 *  
	 *  @param   integer  $id     [ID записи]
	 *  @param   array    $where  [Условия поиска]
	 *  @return  array
	 */
	public function get_by_id($id = 0, $where = [])
	{
		$id = (integer) $id;

		// Задаём целевое условие.
		$this->db->where('id', $id);

		return self::get_one($where);
	}

	/**
	 *  Создание новой записи.
	 *  
	 *  @param   array   $data  [Данные]
	 *  @return  integer
	 */
	public function add($data = [])
	{
		$data = (array) $data;

		$insert_id = 0;

		// Данные для вставки.
		if (!empty($data))
		{
			$this->db->set($data);
		}

		// Если запись создана, возвращаем её ID.
		if ($this->db->insert($this->table))
		{
			$insert_id = $this->db->insert_id();
		}

		// Сбрасываем запрос.
		$this->db->reset_query();

		return (integer) $insert_id;
	}

	/**
	 *  Добавление нескольких записей одновременно.
	 *  
	 *  @param   array   $data  [Данные]
	 *  @return  integer
	 */
	public function add_all($data = [])
	{
		$data = (array) $data;

		$status = FALSE;

		if (!empty($data) && $this->db->insert_batch($this->table, $data) > 0)
		{
			$status = TRUE;
		}

		// Сбрасываем запрос.
		$this->db->reset_query();

		return $status;
	}

	/**
	 *  Обновление существующей записи по заданному условию.
	 *  
	 *  @param   array    $data   [Обновления]
	 *  @param   array    $where  [Условия поиска]
	 *  @return  boolean
	 */
	public function update($data = [], $where = [])
	{
		$data = (array) $data;
		$where = (array) $where;

		$status = FALSE;

		// Обновлённые данные.
		if (!empty($data))
		{
			$this->db->set($data);
		}

		// Условия поиска для последующего обновления.
		if (!empty($where))
		{
			$this->db->where($where);
		}

		// Проверяем, была ли затронута хотя бы 1 строка.
		if ($this->db->update($this->table) && $this->db->affected_rows() > 0)
		{
			$status = TRUE;
		}

		// Сбрасываем запрос.
		$this->db->reset_query();

		return (boolean) $status;
	}

	/**
	 *  Обновление нескольких записей по заданному ключевому полю.
	 *  
	 *  @param   array   $data   [Данные]
	 *  @param   string  $field  [Ключевое поле]
	 *  @return  boolean
	 */
	public function update_all($data = [], $field = 'id')
	{
		$data = (array) $data;
		$field = (string) $field;

		$status = FALSE;

		if (!empty($data) && $this->db->update_batch($this->table, $data, $field) > 0)
		{
			$status = TRUE;
		}

		// Сбрасываем запрос.
		$this->db->reset_query();

		return $status;
	}

	/**
	 *  Удаление записи.
	 *  
	 *  @param   array     $where  [Условия поиска]
	 *  @return  boolean
	 */
	public function delete($where = [])
	{
		$where = (array) $where;

		$result = FALSE;

		// Условия поиска.
		if (!empty($where))
		{
			$this->db->where($where);
		}

		// Удаляем записи.
		if ($this->db->delete($this->table) && $this->db->affected_rows() > 0)
		{
			$result = TRUE;
		}

		// Сбрасываем запрос.
		$this->db->reset_query();

		return $result;
	}

	/**
	 *  Очистка таблицы базы данных и сброс счётчика Auto-Increment.
	 *  
	 *  @return  boolean
	 */
	public function truncate()
	{
		return $this->db->truncate($this->table);
	}

	// ------------------------------------------------------------------------

	/**
	 *  Конструктор запроса на получение данных из БД.
	 *  
	 *  @param   array    $where   [Условия поиска]
	 *  @param   array    $order   [Сортировка]
	 *  @param   integer  $limit   [LIMIT]
	 *  @param   integer  $offset  [OFFSET]
	 *  @return  void
	 */
	protected function set_conditions($where = [], $order = [], $limit = 0, $offset = 0)
	{
		$where = (array) $where;
		$order = (array) $order;
		$limit = (integer) $limit;
		$offset = (integer) $offset;

		// Условия поиска.
		if (!empty($where))
		{
			$this->db->where($where);
		}

		// Сортировка.
		if (!empty($order))
		{
			foreach ($order as $field => $sort)
			{
				$this->db->order_by($field, $sort);
			}
		}

		// Ограничения.
		if ($limit > 0)
		{
			$this->db->limit($limit);
		}

		if ($offset > 0)
		{
			$this->db->offset($offset);
		}
	}

	/**
	 *  Выполнение запроса и получение одной записи.
	 *  
	 *  @return  array
	 */
	protected function get_row()
	{
		$result = [];

		if ($query = $this->db->get())
		{
			$result = $query->row_array();
		}

		return (array) $result;
	}

	/**
	 *  Выполнение запроса и получение массива данных.
	 *  Можно указать, по какому полю будут формироваться
	 *  ключи массива. Если таких полей указано несколько,
	 *  то массив будет разбит на группы.
	 *  
	 *  @param   array   $fields  [Поля, которые будут использованы в качестве ключей]
	 *  @return  array
	 */
	protected function get_array($fields = [])
	{
		$result = [];

		if ($query = $this->db->get())
		{
			foreach ($query->result_array() as $data)
			{
				if (empty($fields))
				{
					$result[] = $data;
				}
				else
				{
					if (!is_array($fields)) {
						$fields = [$fields];
					}

					$fields_count = count($fields);

					foreach ($fields as $field)
					{
						if ($fields_count > 1)
						{
							$result[$field][$data[$field]] = $data;
						}
						else
						{
							$result[$data[$field]] = $data;
						}
					}
				}
			}
		}

		return (array) $result;
	}
}

/* End of file MY_Model.php */
/* Location: ./application/core/MY_Model.php */