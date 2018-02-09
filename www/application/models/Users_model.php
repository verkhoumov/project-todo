<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();

		// Задаём основную таблицу.
		$this->table = $this->db_users;

		// Подключение обработчика данных.
		$this->load->helper('schemes/users');
	}

	// ------------------------------------------------------------------------
	
	/**
	 *  Поиск пользователя по логину или E-mail.
	 *  
	 *  @param   string   $identity  [Логин или E-mail]
	 *  @return  array
	 */
	public function find_user_by_login_or_email($identity = '')
	{
		$identity = (string) $identity;

		$result = [];

		if ($identity != '')
		{
			$this->db
				->select('id, login, name, image')
				->where('login', $identity)
				->or_where('email', $identity);

			$result = $this->get_one();
		}

		return $result;
	}
}

/* End of file Users_model.php */
/* Location: ./application/models/Users_model.php */