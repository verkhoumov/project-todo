// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Удаление доступа к списку/Отписка.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			share_id: 0,
			unsubscribe: false,
			callbacks: {
				onSuccess: function(share_id) {}
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

				// Обработка нажатия на кнопку отписки/удаления доступа.
				$(this).on('click.ShareDelete', function(e) {
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
				$(this).off('.ShareDelete');

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
			url: '/Api/Access/delete',
			timeout: 3000,
			data: {
				share_id: options.share_id
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

		// Направляем пользователя на страницу со всеми списками.
		if (options.unsubscribe) {
			window.location.href = '/';
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
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402) HELPER.noty('error', LANG.accessDenied);
		else if (status == 403) HELPER.noty('error', LANG.cantDeclineAccess);
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
	$.fn.ShareDelete = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('ShareDelete: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);