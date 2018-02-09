// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для подтверждения почты пользователя.
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
					inputs: $(this).find('input, textarea'),
					buttons: $(this).find('button'),
					acceptButton: null,
					modal: null
				}, defaultOptions, customOptions);

				// Обработка нажатия на кнопку проверки кода.
				$(this).on('submit.AcceptEmail', function(e) {
					// Отменяем отправку формы.
					e.preventDefault();
					// Вызываем AJAX-запрос для проверки кода.
					ajaxAcceptEmailCode();
				});
			});
		},

		// Отправка кода подтверждения на почту.
		sendCode: function(callback) {
			return this.each(function() {
				ajaxAcceptEmail(callback);
			});
		},

		// Отключение плагина.
		destroy: function() {
			return this.each(function() {
				if (!options) {
					return;
				}

				// Сбрасываем параметры формы.
				options.form.get(0).reset();

				// Снимаем блокировку с полей и кнопок.
				enabledForm();

				// Отключаем обработчик.
				options.form.off('.AcceptEmail');

				// Удаляем все параметры.
				options = null;
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	// AJAX-запрос на отправку кода на почту.
	var ajaxAcceptEmail = function(callback) {
		if (!options) {
			return;
		}

		let status = 400;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/User/acceptEmail',
			timeout: 3000,
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
					success(1, callback);
				} else {
					error(1, status);
				}
			}
		});
	};

	// AJAX-запрос на проверку введённого кода подтверждения.
	var ajaxAcceptEmailCode = function() {
		if (!options) {
			return;
		}

		let status = 400;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/User/acceptEmailCode',
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
					success(2);
				} else {
					error(2, status);
				}
			}
		});
	};

	// Успешная обработка запроса.
	var success = function(step, callback) {
		if (!options) {
			return;
		}

		if (step == 1) {
			enabledButton();

			// Вызываем callback.
			if (typeof callback === 'function') {
				callback.call(this);
			}
		} else {
			// Показываем успех операции.
			options.inputs.addClass('is-valid');

			// Скрываем кнопку подтверждения.
			options.acceptButton.parent().addClass('d-none');

			// Через 2 секунды скрываем эффект успеха.
			setTimeout(function() {
				if (!options) {
					return;
				}

				options.inputs.removeClass('is-valid');

				// Снимаем блокировку с формы.
				enabledForm();

				// Закрываем модальное окно.
				options.modal.modal('hide');
			}, 2000);
		}
	};

	// Ошибка при выполнении запроса.
	var error = function(step, status) {
		if (!options) {
			return;
		}

		if (step == 1) {
			enabledButton();

			// В зависимости от кода ошибки показываем соответствующее уведомление.
			if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
			else if (status == 401 || status == 301) window.location.href = '/';
			else if (status == 402) HELPER.noty('error', LANG.emailUndefined);
			else if (status == 403) HELPER.noty('error', LANG.emailAlreadyAccepted);
			else HELPER.noty('error', LANG.requestProcessError); // 400, 404
		} else {
			enabledForm();

			// В зависимости от кода ошибки показываем соответствующее уведомление.
			if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
			else if (status == 401 || status == 301) window.location.href = '/';
			else if (status == 402) HELPER.noty('error', LANG.acceptedCodeError);
			else HELPER.noty('error', LANG.requestProcessError); // 400
		}
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

	// Блокировка кнопки подтверждения на время выполнения запроса.
	var disabledButton = function() {
		if (!options) {
			return;
		}

		options.acceptButton.attr('disabled', true);
	};

	// Снятие блокировки при возникновении ошибки.
	var enabledButton = function() {
		if (!options) {
			return;
		}

		options.acceptButton.attr('disabled', false);
	};

	/**
	 *  Роутер.
	 */
	$.fn.AcceptEmail = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('AcceptEmail: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);