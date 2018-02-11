<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Настройки сайта.
 */
$config['site'] = [
	'url'             => 'http://todo.verkhoumov.ru/',
	'title'           => 'TODO-менеджер',
	'title_separator' => ' — ',
	'version'         => '1.0.1',

	// Информация об авторе.
	'author' => [
		'name' => 'Дмитрий Верхоумов',
		'year' => 2018,
		'link' => 'https://verkhoumov.ru/'
	],

	// Данные для отправки кода восстановления/подтверждения.
	'noreply' => [
		'email' => 'noreply@verkhoumov.ru',
		'name'  => 'Бот TODO-сервиса'
	]
];