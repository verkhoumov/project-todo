// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');


/**
 *  Плагин для авторизации по логину и паролю.
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
					form: $(this),
					inputs: $(this).find('input'),
					buttons: $(this).find('button')
				}, defaultOptions, customOptions);

				// Обработка нажатия на кнопку авторизации.
				$(this).on('submit.Auth', function(e) {
					// Отменяем отправку формы.
					e.preventDefault();
					// Вызываем AJAX-запрос для авторизации.
					ajax();
				});
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	// AJAX-запрос для авторизации.
	var ajax = function() {
		if (!options) {
			return;
		}

		let status = 400;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Auth/signIn',
			timeout: 3000,
			data: options.form.serialize(),
			beforeSend: function() {
				disabledForm();
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
		// Сообщаем об успешной авторизации.
		options.inputs.addClass('is-valid');

		// Через 2 секунды обновляем страницу.
		setTimeout(function() {
			window.location.href = '/';
		}, 2000);
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		// Снимаем блокировку с формы в случае ошибки.
		enabledForm();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401) window.location.href = '/';
		else if (status == 402) HELPER.noty('error', LANG.formPasswordError);
		else if (status == 501) HELPER.noty('error', LANG.formLoginError);
		else if (status == 502) HELPER.noty('error', LANG.userNotExists);
		else HELPER.noty('error', LANG.requestProcessError); // 400, 500
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

	/**
	 *  Роутер.
	 */
	$.fn.Auth = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('Auth: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);