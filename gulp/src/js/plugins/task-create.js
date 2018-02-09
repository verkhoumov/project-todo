// jQuery handler.
require('jquery');

// Модуль для парсинга шаблонов.
var Mustache = require('mustache');
// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для добавления новой задачи к списку.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			list_id: 0,
			container: null,
			callbacks: {
				onSuccess: function(task) {}
			},
			templates: {
				task: $('#template-task').html()
			}
		};

	/**
	 *  Публичные методы.
	 */
	var methods = {
		// Инициализация плагина.
		load: function(customOptions) {
			return this.each(function() {
				options = $.extend(true, {
					form: $(this),
					inputs: $(this).find('input, textarea').filter(':not(.disabled)'),
					buttons: $(this).find('button')
				}, defaultOptions, customOptions);

				// Первичный рендеринг.
				prerender();

				// Обработка нажатия на кнопку добавления.
				$(this).on('submit.TaskCreate', function(e) {
					e.preventDefault();
					ajax();
				});
			});
		},

		destroy: function() {
			return this.each(function() {
				if (!options) {
					return;
				}

				// Отключаем обработчик.
				$(this).off('.TaskCreate');
				// Снимаем блокировку с формы.
				enabledForm();
				// Удаляем параметры.
				options = null;
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	// AJAX-запрос для добавления новой задачи.
	var ajax = function() {
		if (!options) {
			return;
		}

		let status = 400,
			data = null;

		// С помощью FormData можно добавить загрузку изображения вместе с прочими данными.
		var formData = new FormData();

		formData.append('task[name]', $('#form-task-name').val());
		formData.append('task[status]', $('#form-task-status:checked').val() == 1 ? 1 : 0);
		formData.append('image', $('#form-task-file').get(0).files[0]);
		formData.append('list_id', options.list_id);

		$('[name="task[tags][]"]').each(function(index, el) {
			formData.append('task[tags][' + index + ']', $(el).val());
		});

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Tasks/create',
			timeout: 20000,
			contentType: false,
			processData: false,
			cache: false,
			data: formData,
			beforeSend: function() {
				// Блокируем форму.
				disabledForm();
			},
			success: function(json) {
				if (json.status && json.status >= 200) {
					status = json.status;
					data = json.data;
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				status = HELPER.getAjaxError(jqXHR, textStatus, errorThrown);
			},
			complete: function() {
				if (status == 200) {
					success(data);
				} else {
					error(status);
				}
			}
		});
	};

	// Успешная обработка запроса.
	var success = function(data) {
		if (!options || !data) {
			return;
		}

		// Снимаем блокировку с формы.
		enabledForm();
		// Добавляем новую задачу в список.
		push(data, function(taskData) {
			// Вызываем Callback.
			options.callbacks.onSuccess(taskData);
		});
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		// Снимаем блокировку с формы в случае ошибки.
		enabledForm();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402) HELPER.noty('error', LANG.accessDeniedOrListUndefined);
		else if (status == 403) HELPER.noty('error', LANG.formError);
		else if (status == 404) HELPER.noty('error', LANG.formImageError);
		else HELPER.noty('error', LANG.requestProcessError); // 400, 405
	};

	// Блокировка формы на время выполнения запроса.
	var disabledForm = function() {
		if (!options) {
			return;
		}

		options.inputs.attr('disabled', true);
		options.buttons.attr('disabled', true);
	};

	// Снятие блокировки при возникновении ошибки.
	var enabledForm = function() {
		if (!options) {
			return;
		}

		options.inputs.attr('disabled', false);
		options.buttons.attr('disabled', false);
	};

	// Обрабатываем и добавляем новую задачу в обёртку.
	var push = function(data, callback) {
		if (!options || !data) {
			return;
		}

		var build = function(data, callback) {
			console.log(data);

			// Парсим шаблон.
			var $task = parse(data);

			if ($task) {
				// Вставляем новую задачу в обёртку.
				$task.appendTo(options.container);
			}

			if (typeof callback === 'function') {
				callback(data);
			}
		};

		// Загружаем изображение таска.
		if (data.image) {
			HELPER.preloadImage(data.image || '#', function(status, result) {
				// Заменяем незагруженные изображения на дефолтные.
				if (status == 'error') {
					$.each(result.failedLinks, function(index, image) {
						if (image == data.image) {
							data.image = HELPER.getDefaultTaskImage();
						}
					});
				}

				build(data, callback);
			});
		} else {
			data.image = HELPER.getDefaultTaskImage();
			build(data, callback);
		}
	};

	// Парсим информацию о задаче.
	var parse = function(data) {
		if (!options) {
			return;
		}

		// Форматирование данных.
		// ...

		// Рендер.
		var render = Mustache.render(options.templates.task, data || {});

		return $(render);
	};

	// Подготовка шаблонов к дальнейшей работе.
	var prerender = function() {
		if (!options) {
			return;
		}

		$.each(options.templates, function(key, template) {
			Mustache.parse(template);
		});
	};

	/**
	 *  Роутер.
	 */
	$.fn.TaskCreate = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('TaskCreate: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);