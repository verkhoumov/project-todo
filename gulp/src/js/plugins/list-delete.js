// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для удаления списка задач.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			// Тут могут быть параметры.
		};

	/**
	 *  Публичные методы.
	 */
	var methods = {
		// Инициализация плагина.
		load: function(customOptions) {
			return this.each(function() {
				options = $.extend(true, {
					button: $(this),
					list_id: 0
				}, defaultOptions, customOptions);

				// Обработка нажатия на кнопку удаления списка.
				$(this).on('click.DeleteList', function(e) {
					// Отменяем отправку формы.
					e.preventDefault();
					// Вызываем AJAX-запрос для удаления списка.
					ajax();
				});
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	// AJAX-запрос для удаления списка.
	var ajax = function() {
		if (!options) {
			return;
		}

		let status = 400;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Lists/delete',
			timeout: 3000,
			data: {
				list_id: options.list_id
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
		// Направляем пользователя на страницу со всеми списками.
		window.location.href = '/';
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		// Снимаем блокировку с кнопки в случае ошибки.
		enabledButton();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402) HELPER.noty('error', LANG.formListIdError);
		else if (status == 403) HELPER.noty('error', LANG.deleteListError);
		else HELPER.noty('error', LANG.requestProcessError); // 400
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
	$.fn.DeleteList = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('DeleteList: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);