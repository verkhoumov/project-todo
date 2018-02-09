<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Построение списка меток из строки.
 *  
 *  @param   string  $tags  [Список меток, разделённых символом]
 *  @return  array
 */
function get_tags($tags = '')
{
	$tags = get_string($tags);

	return explode('|', $tags);
}

/* End of file tags_helper.php */
/* Location: ./application/helpers/schemes/tags_helper.php */