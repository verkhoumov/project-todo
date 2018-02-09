// jQuery handler.
require('jquery');

// Модуль для парсинга шаблонов.
var Mustache = require('mustache');
// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для добавления нового доступа к списку.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			list_id: 0,
			user_id: 0,
			container: null,
			callbacks: {
				onSuccess: function(share) {}
			},
			templates: {
				share: $('#template-share').html()
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

				// Обработка нажатия на кнопку сохранения.
				$(this).on('submit.ShareCreate', function(e) {
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
				$(this).off('.ShareCreate');

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
			data = null;

		let list_id = '&list_id=' + options.list_id;
		let user_id = '&user_id=' + options.user_id;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Access/create',
			timeout: 3000,
			data: options.form.serialize() + list_id + user_id,
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
		// Добавляем новую карточку с доступом.
		push(data, function() {
			// Вызываем Callback.
			options.callbacks.onSuccess(data);
		});
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		// Снимаем блокировку с формы в случае ошибки.
		enabledForm();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402) HELPER.noty('error', LANG.listUndefined);
		else if (status == 403) HELPER.noty('error', LANG.userNotExists);
		else if (status == 404) HELPER.noty('error', LANG.userAlreadyAccessed);
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

	// Обрабатываем и добавляем новую карточку в обёртку.
	var push = function(data, callback) {
		if (!options || !data) {
			return;
		}

		// Загружаем аватар пользователя.
		HELPER.preloadImage(data.image || '#', function(status, result) {
			// Заменяем незагруженные изображения на дефолтные.
			if (status == 'error') {
				$.each(result.failedLinks, function(index, image) {
					if (image == data.image) {
						data.image = HELPER.getDefaultUserImage();
					}
				});
			}

			// Парсим шаблон.
			var $share = parse(data);

			if ($share) {
				// Вставляем новую карточку в обёртку.
				$share.appendTo(options.container);
			}

			if (typeof callback === 'function') {
				callback();
			}
		});
	};

	// Парсим информацию о доступе.
	var parse = function(data) {
		if (!options) {
			return;
		}

		// Форматирование данных.
		// ...

		// Рендер.
		var render = Mustache.render(options.templates.share, data || {});

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
	$.fn.ShareCreate = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('ShareCreate: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);