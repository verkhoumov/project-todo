-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Фев 09 2018 г., 19:32
-- Версия сервера: 5.6.33-79.0
-- Версия PHP: 5.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `u0037136_todo`
--

-- --------------------------------------------------------

--
-- Структура таблицы `lists`
--

CREATE TABLE IF NOT EXISTS `lists` (
  `id` int(10) unsigned NOT NULL COMMENT 'ID списка',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'ID пользователя',
  `title` varchar(200) DEFAULT NULL COMMENT 'Название списка',
  `description` text COMMENT 'Описание списка',
  `created` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания списка',
  `updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата последнего изменения списка',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Статус (0 - удалённый список, 1 - активный список)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Списки';

-- --------------------------------------------------------

--
-- Структура таблицы `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(10) unsigned NOT NULL COMMENT 'ID сессии',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'ID пользователя',
  `user_auth` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Авторизован ли пользователь? (0 - нет, 1 - да)',
  `user_host` varchar(15) DEFAULT NULL COMMENT 'IP-адрес',
  `user_agent` varchar(200) DEFAULT NULL COMMENT 'User-Agent',
  `token` varchar(64) DEFAULT NULL COMMENT 'Токен сессии',
  `created` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания сессии',
  `updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата последнего обновления сессии'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Сессии пользователей';

--
-- Дамп данных таблицы `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `user_auth`, `user_host`, `user_agent`, `token`, `created`, `updated`) VALUES
(1, 1, 1, '5.149.156.24', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36', '944a7aa467bac8e7546fe737e3d6a2d1a4ac1b8308a50b8ad4c5f27d2fdbb6a3', '2018-02-09 19:30:30', '2018-02-09 19:30:45');

-- --------------------------------------------------------

--
-- Структура таблицы `shares`
--

CREATE TABLE IF NOT EXISTS `shares` (
  `id` int(10) unsigned NOT NULL COMMENT 'ID шаринга',
  `list_id` int(10) unsigned DEFAULT NULL COMMENT 'ID расшариваемого списка',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'ID пользователя, которому открывается доступ',
  `access_read` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Можно ли просматривать список?',
  `access_edit` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Можно ли редактировать список?',
  `created` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания шаринга',
  `updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата последнего обновления шаринга',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Статус (0 - шаринг удалён, 1 - шаринг активен)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Шаринг списков другим пользователям';

-- --------------------------------------------------------

--
-- Структура таблицы `tasks`
--

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(10) unsigned NOT NULL COMMENT 'ID пункта списка',
  `list_id` int(10) unsigned DEFAULT NULL COMMENT 'ID списка',
  `name` text COMMENT 'Текст задачи',
  `image` varchar(30) DEFAULT NULL COMMENT 'Хэш изображения (будет хранится в /upload/images/items/[ID]/[IMAGE].png)',
  `tags` text COMMENT 'Метки к пункту списка (в качестве разделителя используется "|")',
  `created` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания пункта',
  `updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата последнего изменения пункта',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Статус (0 - не выполнена, 1 - выполнена)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Пункты списков';

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL COMMENT 'ID пользователя',
  `login` varchar(20) DEFAULT NULL COMMENT 'Логин',
  `password` varchar(64) DEFAULT NULL COMMENT 'Зашифрованный пароль',
  `name` varchar(30) DEFAULT NULL COMMENT 'Имя',
  `email` varchar(60) DEFAULT NULL COMMENT 'E-mail',
  `email_accept` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Статус подтверждения почты (0 - нет, 1 - да)',
  `email_code` varchar(64) DEFAULT NULL COMMENT 'Код подтверждения, отправленный на почту (в базе хранится в зашифрованном виде)',
  `image` varchar(30) DEFAULT NULL COMMENT 'Хэш изображения (будет храниться в /upload/images/users/[ID]/[IMAGE].png)',
  `created` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата регистрации',
  `updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата последнего обновления',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Статус (0 - отключенный аккаунт [доступ к сервису закрыт], 1 - активный аккаунт [доступ открыт])'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Список пользователей';

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `name`, `email`, `email_accept`, `email_code`, `image`, `created`, `updated`, `status`) VALUES
(1, 'verkhoumov', 'f104093d561d7a8aa1a8fd4891c6a6fb11043ceefa7e26cf00d0e589b63bba8f', 'Дмитрий Верхоумов', 'verkhoumov@yandex.ru', 1, '0ccc1ff693772a494ad22649b8458ffcfa3d5ff2cc6c79c572f13de9089895ad', 'NXMkTlEgPCwKtdaAsfYrSopZhqRLeB', '2018-02-09 19:30:45', '2018-02-09 19:31:21', 1);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `lists`
--
ALTER TABLE `lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `shares`
--
ALTER TABLE `shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `list_id` (`list_id`,`user_id`),
  ADD KEY `list_id_2` (`list_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `list_id` (`list_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `lists`
--
ALTER TABLE `lists`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID списка';
--
-- AUTO_INCREMENT для таблицы `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID сессии',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT для таблицы `shares`
--
ALTER TABLE `shares`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID шаринга';
--
-- AUTO_INCREMENT для таблицы `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID пункта списка';
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID пользователя',AUTO_INCREMENT=2;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `lists`
--
ALTER TABLE `lists`
  ADD CONSTRAINT `lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shares`
--
ALTER TABLE `shares`
  ADD CONSTRAINT `shares_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shares_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
