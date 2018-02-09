// jQuery handler.
require('jquery');

// Модуль для парсинга шаблонов.
var Mustache = require('mustache');
// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для редактирования задач списка.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			task_id: 0,
			element: null,
			callbacks: {
				onSuccess: function(task, newElement) {}
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

				// Обработка нажатия на кнопку сохранения.
				$(this).on('submit.TaskEdit', function(e) {
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
				$(this).off('.TaskEdit');
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
	// AJAX-запрос для обновления параметров доступа.
	var ajax = function() {
		if (!options) {
			return;
		}

		let status = 400,
			data = null,
			errors = null;

		// С помощью FormData можно добавить загрузку изображения вместе с прочими данными.
		var formData = new FormData();

		formData.append('task[name]', $('#form-task-name').val());
		formData.append('task[status]', $('#form-task-status:checked').val() == 1 ? 1 : 0);
		formData.append('image', $('#form-task-file').get(0).files[0]);
		formData.append('task_id', options.task_id);

		$('[name="task[tags][]"]').each(function(index, el) {
			formData.append('task[tags][' + index + ']', $(el).val());
		});

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Tasks/edit',
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
					errors = json.errors;
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				status = HELPER.getAjaxError(jqXHR, textStatus, errorThrown);
			},
			complete: function() {
				if (status == 200) {
					success(data);
				} else {
					error(status, errors);
				}
			}
		});
	};

	// Успешная обработка запроса.
	var success = function(data) {
		if (!options) {
			return;
		}

		// Сообщаем об успешном изменении настроек.
		options.inputs.addClass('is-valid');

		// Показываем уведомление.
		HELPER.noty('success', LANG.formEditSuccess);

		// Через 2 секунды скрываем эффект успеха.
		let inputs = options.inputs;

		setTimeout(function() {
			inputs.removeClass('is-valid');

			// Снимаем блокировку с формы.
			enabledForm();
		}, 2000);

		// Данные.
		let task = {
			id: data.id || 0,
			image: data.image || null,
			status: data.status || 0,
			name: data.name || null,
			tags: data.tags || []
		};

		// Заменяем содержимое.
		change(task, function(taskData) {
			// Меняем картинку в модальном окне (при необходимости).
			if (taskData.image) {
				options.form.find('#form-task-image').get(0).src = taskData.image;
				options.form.find('#form-task-file').val('');
			}

			// Вызываем Callback.
			options.callbacks.onSuccess(taskData, options.element);
		});
	};

	// Ошибка при выполнении запроса.
	var error = function(status, errors) {
		// Снимаем блокировку с формы в случае ошибки.
		enabledForm();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402) HELPER.noty('error', LANG.accessDeniedOrListUndefined);
		else if (status == 403) HELPER.noty('error', LANG.formImageError);
		else if (status == 404) HELPER.noty('error', LANG.formError);
		else HELPER.noty('error', LANG.requestProcessError); // 400, 404
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

	// Обрабатываем и добавляем обновлённую задачу в обёртку и заменяем старую.
	var change = function(data, callback) {
		if (!options || !data) {
			return;
		}

		var build = function(data, callback) {
			// Парсим шаблон.
			var $task = parse(data);

			if ($task) {
				// Заменяем старую задачу новой.
				$task.replaceAll(options.element);
				// Запоминаем новую задачу.
				options.element = $task;
			}

			if (typeof callback === 'function') {
				callback(data);
			}
		};

		if (data.image) {
			// Загружаем изображение таска.
			HELPER.preloadImage(data.image || '#', function(status, result) {
				// Заменяем незагруженные изображения на дефолтные.
				if (status == 'error') {
					$.each(result.failedLinks, function(index, image) {
						if (image == data.image) {
							data.image = HELPER.getDefaultTaskImage();
						}
					});
				}

				build(data, callback)
			});
		} else {
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
	$.fn.TaskEdit = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('TaskEdit: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);