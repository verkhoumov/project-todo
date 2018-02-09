<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Lists_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();

		// Задаём основную таблицу.
		$this->table = $this->db_lists;

		// Подключение обработчика данных.
		$this->load->helper('schemes/lists');
	}

	// ------------------------------------------------------------------------
	
	/**
	 *  Удаление списка и всех сопутствующих данных.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  boolean
	 */
	public function delete_list_by_id($list_id = 0, $user_id = 0)
	{
		$list_id = (integer) $list_id;
		$user_id = (integer) $user_id;

		$result = FALSE;

		if ($list_id > 0 && $user_id > 0)
		{
			$query = "
				DELETE L, T, S
				FROM {$this->db_lists} AS L
				LEFT JOIN {$this->db_tasks} AS T ON T.list_id = L.id
				LEFT JOIN {$this->db_shares} AS S ON S.list_id = L.id
				WHERE L.id = {$list_id} AND L.user_id = {$user_id}
			";

			// Выполняем запрос.
			if ($this->db->query($query) && $this->db->affected_rows() > 0)
			{
				$result = TRUE;
			}

			// Сбрасываем запрос.
			$this->db->reset_query();
		}

		return $result;
	}

	/**
	 *  Получение информации о списке по ID.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	public function get_list_by_id($list_id = 0)
	{
		$list_id = (integer) $list_id;

		$result = [];

		if ($list_id > 0)
		{
			$this->db
				->select('*')
				->select("(SELECT COUNT(*) FROM {$this->db_tasks} WHERE list_id = {$list_id}) AS tasks_count")
				->select("(SELECT COUNT(*) FROM {$this->db_tasks} WHERE list_id = {$list_id} AND status = 1) AS tasks_completed")
				->where('id', $list_id);

			$result = $this->get_one();
		}

		return $result;
	}

	/**
	 *  Получение всех списков пользователя.
	 *  
	 *  @param   integer  $user_id  [ID пользователя]
	 *  @return  array
	 */
	public function get_lists_by_user_id($user_id = 0)
	{
		$user_id = (integer) $user_id;

		$result = [];

		if ($user_id > 0)
		{
			// Данный запрос собирает все списки, как созданные пользователем, так и те, к которым у него есть доступ.
			$query = "
				(
					SELECT L.*, COUNT(T.list_id) AS tasks_count, 1 AS owner, 1 AS access_read, 1 AS access_edit
					FROM {$this->db_lists} AS L
					LEFT JOIN {$this->db_tasks} AS T ON T.list_id = L.id
					WHERE L.user_id = {$user_id}
					GROUP BY T.list_id
				)
				UNION
				(
					SELECT L.*, COUNT(T.list_id) AS tasks_count, 0 AS owner, S.access_read, S.access_edit
					FROM {$this->db_lists} AS L
					JOIN {$this->db_shares} AS S ON S.list_id = L.id AND S.user_id = {$user_id}
					LEFT JOIN {$this->db_tasks} T ON T.list_id = L.id
					GROUP BY T.list_id
				)
				ORDER BY updated DESC
			";

			// Выполняем запрос.
			$ids = [];
			$query = $this->db->query($query);

			foreach ($query->result_array() as $data)
			{
				$id = (integer) $data['id'];

				$result[$id] = $data;
				$ids[] = $id;
			}

			// Сбрасываем запрос.
			$this->db->reset_query();

			if (!empty($ids))
			{
				$ids_string = implode(', ', $ids);

				// Данный запрос определяет количество выполненных тасков.
				$query = "
					SELECT T.list_id, COUNT(T.list_id) AS tasks_completed
					FROM {$this->db_tasks} T
					WHERE T.list_id IN ($ids_string) AND T.status = 1
					GROUP BY T.list_id
				";

				// Выполняем запрос.
				$completed = [];
				$query = $this->db->query($query);

				foreach ($query->result_array() as $data)
				{
					$completed[$data['list_id']] = (integer) $data['tasks_completed'];
				}

				// Сбрасываем запрос.
				$this->db->reset_query();
			}

			// Записываем кол-во выполненных тасков для каждого списка.
			if (!empty($result))
			{
				foreach ($result as $key => $value)
				{
					if (array_key_exists($value['id'], $completed))
					{
						$result[$key]['tasks_completed'] = $completed[$value['id']];
					}
					else
					{
						$result[$key]['tasks_completed'] = 0;
					}
				}

				// Сбрасываем ключи.
				$result = array_values($result);
			}
		}

		return (array) $result;
	}
}

/* End of file Lists_model.php */
/* Location: ./application/models/Lists_model.php */