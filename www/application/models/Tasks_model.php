<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tasks_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();

		// Задаём основную таблицу.
		$this->table = $this->db_tasks;

		// Подключение обработчика данных.
		$this->load->helper('schemes/tasks');
		$this->load->helper('schemes/tags');
	}

	// ------------------------------------------------------------------------
	
	/**
	 *  Получение прав доступа пользователя к таску.
	 *  
	 *  @param   integer  $task_id  [ID таска]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	public function get_task_access($task_id = 0, $user_id = 0)
	{
		$task_id = (integer) $task_id;
		$user_id = (integer) $user_id;

		$result = [];

		if ($task_id > 0 && $user_id > 0)
		{
			$this->db
				->select('L.user_id AS list_user_id')
				->select('S.user_id AS share_user_id, S.access_read, S.access_edit')
				->from("{$this->db_tasks} AS T")
				->join("{$this->db_lists} AS L", 'L.id = T.list_id')
				->join("{$this->db_shares} AS S", "S.list_id = L.id AND (L.user_id = {$user_id} OR S.user_id = {$user_id})", 'left')
				->where('T.id', $task_id);

			// Получение одной записи.
			$result = $this->get_row();

			// Сбрасываем запрос.
			$this->db->reset_query();
		}

		return $result;
	}

	/**
	 *  Получение прав доступа пользователя к списку задач.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	public function get_list_access($list_id = 0, $user_id = 0)
	{
		$list_id = (integer) $list_id;
		$user_id = (integer) $user_id;

		$result = [];

		if ($list_id > 0 && $user_id > 0)
		{
			$this->db
				->select('L.user_id AS list_user_id')
				->select('S.user_id AS share_user_id, S.access_read, S.access_edit')
				->from("{$this->db_lists} AS L")
				->join("{$this->db_shares} AS S", "S.list_id = L.id AND (L.user_id = {$user_id} OR S.user_id = {$user_id})", 'left')
				->where('L.id', $list_id);

			// Получение одной записи.
			$result = $this->get_row();

			// Сбрасываем запрос.
			$this->db->reset_query();
		}

		return $result;
	}
}

/* End of file Tasks_model.php */
/* Location: ./application/models/Tasks_model.php */