<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Shares_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();

		// Задаём основную таблицу.
		$this->table = $this->db_shares;

		// Подключение обработчика данных.
		$this->load->helper('schemes/lists');
		$this->load->helper('schemes/shares');
	}

	// ------------------------------------------------------------------------
	
	/**
	 *  Получение информации о доступе + владельце списка.
	 *  
	 *  @param   integer  $share_id  [ID доступа]
	 *  @return  array
	 */
	public function get_by_share_id($share_id = 0)
	{
		$share_id = (integer) $share_id;

		$result = [];

		if ($share_id > 0)
		{
			$this->db
				->select('S.*')
				->select('L.user_id AS list_user_id')
				->from("{$this->db_shares} AS S")
				->join("{$this->db_lists} AS L", 'L.id = S.list_id')
				->where('S.id', $share_id);

			// Получение одной записи.
			$result = $this->get_row();

			// Сбрасываем запрос.
			$this->db->reset_query();
		}

		return $result;
	}

	/**
	 *  Информация о списке задач по ID.
	 *  
	 *  @param   integer  $list_id  [ID списка]
	 *  @return  array
	 */
	public function get_by_list_id($list_id = 0)
	{
		$list_id = (integer) $list_id;

		$result = [];

		if ($list_id > 0)
		{
			$this->db
				->select('S.*')
				->select('U.login, U.image')
				->from("{$this->db_shares} AS S")
				->join("{$this->db_users} AS U", 'U.id = S.user_id')
				->where('S.list_id', $list_id);

			// Получение записей.
			$result = $this->get_array();

			// Сбрасываем запрос.
			$this->db->reset_query();
		}

		return $result;
	}
}

/* End of file Shares_model.php */
/* Location: ./application/models/Shares_model.php */