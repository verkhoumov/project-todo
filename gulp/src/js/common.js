/**
 *  Подключение компонентов.
 */
// jQuery handler.
require('jquery');

// Bootstrap (Tooltip).
require('bootstrap');

// Обработчик загрузки изображений.
require('image-loader');

// Модуль для работы со временем.
var moment = require('./addons/moment');

// jQuery плагины.
require('auth');
require('registration');
require('avatar-delete');
require('email-accept');
require('email-restore');
require('list-delete');
require('list-edit');
require('settings');
require('share');
require('share-delete');
require('task');
require('task-search');

// Параметры и словарь.
var CONFIG = require('config');
var LANG = require('lang');

// Вспомогательные функции.
var HELPER = require('functions');


/**
 *  Инициализация.
 */
// Запуск всех обработчиков при полной загрузке страницы.
$(document).ready(function() {
	var listId  = 0;
	var shareId = 0;
	var shares  = {};
	var tasks = {};

	if (CustomData) {
		listId  = CustomData.list_id || 0;
		shareId = CustomData.share_id || 0;
		shares  = CustomData.shares || {};
		tasks   = CustomData.tasks || {};
	}

	/**
	 *  Авторизация.
	 */
	var AuthHandler = null;

	if ($.fn.Auth) {
		AuthHandler = $('#form-signin');
		AuthHandler.Auth();
	}

	/**
	 *  Регистрация.
	 */
	var RegistrationHandler = null;

	if ($.fn.Registration) {
		RegistrationHandler = $('#form-signup');
		RegistrationHandler.Registration();
	}

	/**
	 *  Восстановление доступа к аккаунту.
	 */
	var RestoreEmailHandler = null;

	if ($.fn.RestoreEmail) {
		RestoreEmailHandler = $('#form-restore');

		// При открытии модального окна инициируем плагин заного.
		$('#modal-restore').on('show.bs.modal', function() {
			RestoreEmailHandler.RestoreEmail();
		});
		
		// При закрытии модального окна дестроим плагин.
		$('#modal-restore').on('hide.bs.modal', function() {
			RestoreEmailHandler.RestoreEmail('destroy');
		});
	}

	/**
	 *  Создание нового списка задач/изменение старого.
	 */
	var EditListHandler = null;

	if ($.fn.EditList) {
		EditListHandler = $('#form-list');

		// При открытии модального окна инициируем плагин заного.
		$('#modal-list').on('show.bs.modal', function() {
			EditListHandler.EditList({
				list_id: listId
			});
		});
		
		// При закрытии модального окна дестроим плагин.
		$('#modal-list').on('hide.bs.modal', function() {
			EditListHandler.EditList('destroy');
		});
	}

	/**
	 *  Удаление списка задач.
	 */
	var DeleteListHandler = null;

	if ($.fn.DeleteList) {
		DeleteListHandler = $('#list-delete');
		DeleteListHandler.DeleteList({
			list_id: listId
		});
	}

	/**
	 *  Обновление настроек аккаунта.
	 */
	var SettingsHandler = null;

	if ($.fn.Settings) {
		SettingsHandler = $('#form-settings');
		SettingsHandler.Settings();
	}

	/**
	 *  Подтверждение почты.
	 */
	var AcceptEmailHandler = null;

	if ($.fn.AcceptEmail) {
		AcceptEmailHandler = $('#form-email-accept');
		
		$('#email-accept-button').on('click', function() {
			// Инициируем плагин.
			AcceptEmailHandler.AcceptEmail({
				acceptButton: $(this),
				modal: $('#modal-email-accept')
			});

			// Отправляем код и если операция пройдёт успешно, открываем модальное окно.
			AcceptEmailHandler.AcceptEmail('sendCode', function() {
				$('#modal-email-accept').modal('show');
			});
		});

		// При закрытии модального окна дестроим плагин.
		$('#modal-email-accept').on('hide.bs.modal', function() {
			AcceptEmailHandler.AcceptEmail('destroy');
		});
	}

	/**
	 *  Удаление аватара пользователя.
	 */
	var DeleteAvatarHandler = null;

	if ($.fn.DeleteAvatar) {
		DeleteAvatarHandler = $('#delete-avatar');
		DeleteAvatarHandler.DeleteAvatar();
	}

	/**
	 *  Сброс файла.
	 */
	$('#file-reset').on('click', function() {
		$('#settings-form-avatar, #form-task-file').val('');
	});


	/**
	 *  Изменение/создание доступа.
	 */
	var ShareAddHandler = null;

	if ($.fn.Share) {
		ShareAddHandler = $('#share-add');

		// Добавление нового доступа.
		ShareAddHandler.on('click.ShareHandler', function() {
			// Инициируем плагин.
			$('#form-access').Share({
				type: 'create',
				modal: $('#modal-share'),
				container: $('.shares-list'),
				share: {
					list_id: listId
				},
				callbacks: {
					onCreate: function(share_id, data) {
						shares[share_id] = data;
					}
				}
			});

			// Открываем модальное окно.
			$('#modal-share').modal('show');
		});

		// Изменение старого доступа.
		$(document).on('click.ShareHandler', '.share-edit', function() {
			let share_id = $(this).data('shareId') || 0;

			// Инициируем плагин.
			$('#form-access').Share({
				type: 'edit',
				modal: $('#modal-share'),
				share: shares && shares[share_id] ? shares[share_id] : null,
				element: $(this).parents('.shares-user'),
				callbacks: {
					onUpdate: function(share_id, data) {
						shares[share_id] = $.extend(true, {
							//...
						}, shares[share_id], data);
					},
					onDelete: function(share_id) {
						delete shares[share_id];
					}
				}
			});

			// Открываем модальное окно.
			$('#modal-share').modal('show');
		});

		// При закрытии модального окна дестроим Share.
		$('#modal-share').on('hide.bs.modal', function() {
			$('#form-access').Share('destroy');
		});
	}

	/**
	 *  Управление статистикой.
	 */
	var tasksAllElement = $('#tasks-count'),
		tasksCompletedElement = $('#tasks-completed'),
		tasksAll = +tasksAllElement.text(),
		tasksCompleted = +tasksCompletedElement.text();

	var tasksCounter = function(all, completed) {
		if (!all) all = 0;
		if (!completed) completed = 0;

		// Обновляем показатели.
		tasksAll += all;
		tasksCompleted += completed;

		// Обновляем значения элементов.
		tasksAllElement.text(tasksAll);
		tasksCompletedElement.text(tasksCompleted);
	};

	/**
	 *  Работа с поиском.
	 */
	var SearchHandler = null,
		searchTags = [],
		compareSearchTags;

	if ($.fn.Search) {
		SearchHandler = $('#form-search');
		SearchHandler.Search({
			tasks: tasks,
			tasksList: $('.tasks-list')
		});

		// Определение актуальных меток к таскам.
		compareSearchTags = function() {
			// Обнуляем список меток.
			searchTags = [];

			// Записываем все возможные метки.
			$.each(tasks, function(index, task) {
				if (!task.tags || !task.tags.length) {
					return;
				}

				$.each(task.tags, function(index, tag) {
					searchTags.push(tag);
				});
			});

			// Оставляем только уникальные значения.
			searchTags = HELPER.arrayUnique(searchTags);
			// Сортируем.
			searchTags.sort();
			// Строим новый список меток в форме поиска.
			SearchHandler
				.Search('makeSelect', searchTags)
				.Search('research', tasks);

			return searchTags;
		};

		// При загрузке страницы формируем первичный список меток.
		compareSearchTags();
	}

	/**
	 *  Отписка от списка задач.
	 */
	var ShareDeleteHandler = null;

	if ($.fn.ShareDelete) {
		ShareDeleteHandler = $('#list-unsubscribe');
		ShareDeleteHandler.ShareDelete({
			share_id: shareId,
			unsubscribe: true
		});
	}

	/**
	 *  Изменение/создание задачи.
	 */
	var TaskAddHandler = null;

	if ($.fn.Task) {
		TaskAddHandler = $('#task-add');

		// Добавление нового таска.
		TaskAddHandler.on('click.TaskHandler', function() {
			// Инициируем плагин.
			$('#form-task').Task({
				type: 'create',
				modal: $('#modal-task'),
				container: $('.tasks-list'),
				task: {
					list_id: listId
				},
				callbacks: {
					onCreate: function(task_id, data) {
						tasks[task_id] = data;
						// Меняем статистику на сайте.
						tasksCounter(1, data.status ? 1 : 0);
						// Пересчитываем метки.
						compareSearchTags();
					}
				}
			});

			// Открываем модальное окно.
			$('#modal-task').modal('show');
		});

		// Изменение старого таска.
		$(document).on('click.TaskHandler', '.task-edit', function() {
			let task_id = $(this).data('taskId') || 0;

			// Инициируем плагин.
			$('#form-task').Task({
				type: 'edit',
				modal: $('#modal-task'),
				task: tasks && tasks[task_id] ? tasks[task_id] : null,
				element: $(this).parents('.task-item'),
				callbacks: {
					onUpdate: function(task_id, data) {
						// Меняем статистику на сайте.
						tasksCounter(0, tasks[task_id].status != data.status ? (data.status ? 1 : -1) : 0);

						tasks[task_id] = $.extend(true, {
							//...
						}, tasks[task_id], data);

						// Пересчитываем метки.
						compareSearchTags();
					},
					onDelete: function(task_id) {
						// Меняем статистику на сайте.
						tasksCounter(-1, tasks[task_id].status ? -1 : 0);
						// Удаляем таск.
						delete tasks[task_id];
						// Пересчитываем метки.
						compareSearchTags();
					}
				}
			});

			// Открываем модальное окно.
			$('#modal-task').modal('show');
		});

		// При закрытии модального окна дестроим Task.
		$('#modal-task').on('hide.bs.modal', function() {
			$('#form-task').Task('destroy');
		});
	}

	/**
	 *  Активация/деактивация таска.
	 */
	$('.tasks-list.editable').on('click', '.task-item', function(event) {
		let $element = $(this),
			taskId = $element.data('taskId');

		// Игнорируем нажатие на кнопку редактирования и изображение.
		if ($(event.target).hasClass('task-edit') ||
			$(event.target).parents('.image').length) {
			return;
		}

		let status = 400,
			data = null;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Tasks/toggle',
			timeout: 3000,
			data: {
				task_id: taskId
			},
			beforeSend: function() {
				$element.addClass('disabled');
			},
			success: function(json) {
				if (json.status && json.status >= 200) {
					status = json.status;
					data = json.data;
				}
			},
			complete: function() {
				$element.removeClass('disabled');

				if (status == 200) {
					// Меняем состояние.
					$element[data.status ? 'addClass' : 'removeClass']('active');
					// Обновляем данные.
					tasks[taskId].status = data.status;
					// Меняем статистику на сайте.
					tasksCounter(0, data.status ? 1 : -1);
				}
			}
		});
	});

	/**
	 *  Динамические даты.
	 */
	$('[data-time]').each(function(index, el) {
		var $element = $(el),
			date = $element.data('time');

		// Форматируем дату.
		var time = moment.utc(date + '+03:00', CONFIG.dateFormat + ' Z').utcOffset(moment().utcOffset()),
			timer = function() {
				return time.fromNow();
			};

		$element
			.text(timer())
			.attr('title', time.format('D MMMM ' + LANG.at + ' HH:mm'));

		// Вешаем автообновление даты.
		setInterval(function() {
			$element.text(timer());
		}, 5000);
	});

	/**
	 *  Вешаем красивые подсказки.
	 */
	if ($.fn.tooltip) {
		$('.tooltips').tooltip({
			offset: '0, 3px'
		});
	}

	/**
	 *  Открытие изображений по клику.
	 */
	$(document).on('click', '.image.opener', function() {
		var src = $(this).children('img').get(0).src;

		// Подменяем ссылку на оригинал.
		src = src.replace('.png', '_full.png');

		window.open(src, '_blank');
	});
});