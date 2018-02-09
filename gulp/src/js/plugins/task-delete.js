// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Удаление задачи.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			task_id: 0,
			callbacks: {
				onSuccess: function(task_id) {}
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
					button: $(this)
				}, defaultOptions, customOptions);

				// Обработка нажатия на кнопку удаления задачи.
				$(this).on('click.TaskDelete', function(e) {
					ajax();
				});
			});
		},

		// Отключение плагина.
		destroy: function() {
			return this.each(function() {
				if (!options) {
					return;
				}

				// Отключаем обработчик.
				$(this).off('.TaskDelete');
				// Удаляем параметры.
				options = null;
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	// AJAX-запрос для удаления доступа.
	var ajax = function() {
		if (!options) {
			return;
		}

		let status = 400;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Tasks/delete',
			timeout: 3000,
			data: {
				task_id: options.task_id
			},
			beforeSend: function() {
				// Блокируем кнопку.
				disabledButton();
			},
			success: function(json) {
				if (json.status && json.status >= 200) {
					status = json.status;
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				status = HELPER.getAjaxError(jqXHR, textStatus, errorThrown);
			},
			complete: function() {
				if (status == 200) {
					success();
				} else {
					error(status);
				}
			}
		});
	};

	// Успешная обработка запроса.
	var success = function() {
		if (!options) {
			return;
		}

		// Вызываем callback без контекста.
		options.callbacks.onSuccess();
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		// Снимаем блокировку с кнопки в случае ошибки.
		enabledButton();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 402 || status == 301) window.location.href = '/';
		else if (status == 401) HELPER.noty('error', LANG.formTaskIdError);
		else if (status == 403) HELPER.noty('error', LANG.accessDeniedOrTaskUndefined);
		else HELPER.noty('error', LANG.requestProcessError); // 400, 404
	};

	// Блокировка кнопки на время выполнения запроса.
	var disabledButton = function() {
		if (!options) {
			return;
		}

		options.button.attr('disabled', true);
	};

	// Снятие блокировки при возникновении ошибки.
	var enabledButton = function() {
		if (!options) {
			return;
		}

		options.button.attr('disabled', false);
	};

	/**
	 *  Роутер.
	 */
	$.fn.TaskDelete = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('TaskDelete: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);