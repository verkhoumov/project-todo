<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sessions_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();

		// Задаём основную таблицу.
		$this->table = $this->db_sessions;

		// Подключение обработчика данных.
		$this->load->helper('schemes/sessions');
	}

	// ------------------------------------------------------------------------

	/**
	 *  Запрос на получение информации о сессии.
	 *  
	 *  @param   integer  $session_db_id  [ID записи сессии]
	 *  @param   string   $token          [Хеш идентификатора сессии]
	 *  @return  array
	 */
	public function get_by_db_id($session_db_id = 0, $session_token = '')
	{
		$session_db_id = (integer) $session_db_id;
		$session_token = (string) $session_token;

		$result = [];

		if ($session_db_id > 0 && $session_token != '')
		{
			$this->db
				->reset_query()
				->select('S.*')
				->select('U.login, U.password, U.name, U.email, U.email_accept, U.email_code, U.image, U.created AS user_created, U.updated AS user_updated, U.status AS user_status')
				->from("{$this->db_sessions} AS S")
				->join("{$this->db_users} AS U", 'U.id = S.user_id', 'left')
				->where('S.id', $session_db_id)
				->where('S.token', $session_token);

			// Получение найденной записи.
			$result = $this->get_row();

			// Сбрасываем запрос.
			$this->db->reset_query();
		}

		return $result;
	}
}

/* End of file Sessions_model.php */
/* Location: ./application/models/Sessions_model.php */