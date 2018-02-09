// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для регистрации нового пользователя.
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

				// Обработка нажатия на кнопку регистрации.
				$(this).on('submit.Registration', function(e) {
					e.preventDefault();
					ajax();
				});
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	// AJAX-запрос для регистрации.
	var ajax = function() {
		if (!options) {
			return;
		}

		let status = 400;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Auth/signUp',
			timeout: 3000,
			data: options.form.serialize(),
			beforeSend: function() {
				// Блокируем форму.
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
		// Сообщаем об успешной регистрации.
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
		else if (status == 402) HELPER.noty('error', LANG.formError);
		else if (status == 403) HELPER.noty('error', LANG.userAlreadyRegistered);
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

	/**
	 *  Роутер.
	 */
	$.fn.Registration = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('Registration: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);