// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для восстановления доступа к аккаунту.
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
					steps: $(this).find('.step'),
					login: null,
					email: null
				}, defaultOptions, customOptions);

				// Показываем 1-ый шаг восстановления.
				showRestoreStep(1);

				// Вешаем событие на кнопку проверки логина.
				$('#restore-form-button-restore').on('click.RestoreEmail', function() {
					options.login = $('#restore-form-login').val();

					ajax(1, {
						login: options.login
					});
				});
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
				enabledStepForm();

				// Отключаем все обработчики событий.
				$('#restore-form-button-restore').off('.RestoreEmail');
				$('#restore-form-button-save').off('.RestoreEmail');

				// Удаляем все параметры.
				options = null;
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	// AJAX-запрос для регистрации.
	var ajax = function(step, _data) {
		if (!options || !step || !_data) {
			return;
		}

		let type = step == 1 ? 'restore' : 'restoreNewPassword';
		let status = 400,
			data = null;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Auth/' + type,
			timeout: 3000,
			data: _data,
			beforeSend: function() {
				// Блокируем поля шага.
				disabledStepForm(step);
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
					success(step, data);
				} else {
					error(step, status);
				}
			}
		});
	};

	// Успешная обработка запроса.
	var success = function(step, data) {
		if (!step) {
			return;
		}

		if (step == 1) {
			// Запоминаем почту.
			options.email = data.email;

			// Подставляем почту на страницу.
			options.form.find('#restore-email').text(options.email);

			// Обработчик нового пароля.
			$('#restore-form-button-save').on('click.RestoreEmail', function() {
				ajax(2, {
					login: options.login,
					password: $('#restore-form-password').val(),
					code: $('#restore-form-code').val()
				});
			});

			// Показываем новый шаг.
			showRestoreStep(2);
		} else {
			// Сообщаем об успешной регистрации.
			HELPER.noty('success', LANG.formPasswordSuccess);

			// Через 2 секунды обновляем страницу.
			setTimeout(function() {
				window.location.href = '/';
			}, 2000);
		}
	};

	// Ошибка при выполнении запроса.
	var error = function(step, status) {
		if (!step) {
			return;
		}

		// Снимаем блокировку в полей шага.
		enabledStepForm(step);

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402 && step == 1) HELPER.noty('error', LANG.emailNotVerified);
		else if (status == 402 && step == 2) HELPER.noty('error', LANG.restoreCodeError);
		else if (status == 403 && step == 1) HELPER.noty('error', LANG.sendEmailError);
		else if (status == 403 && step == 2) HELPER.noty('error', LANG.formPasswordError);
		else if (status == 501) HELPER.noty('error', LANG.formLoginError);
		else if (status == 502) HELPER.noty('error', LANG.userNotExists);
		else HELPER.noty('error', LANG.requestProcessError); // 400, 500

		if (step == 1) {
			options.login = null;
		}
	};

	// Показать шаг восстановления почты.
	var showRestoreStep = function(step) {
		if (!options || !step) {
			return;
		}

		// Скрываем все прочие шаги.
		options.steps.filter(':not(.step-' + step + ')').fadeOut(0);

		// Показываем запрошенный шаг.
		options.steps.filter('.step-' + step).fadeIn(0);
	};

	// Заблокировать поля шага.
	var disabledStepForm = function(step) {
		if (!options) {
			return;
		}

		options.steps.filter(!step ? '.step' : '.step-' + step).find('input, button').attr('disabled', true);
	};

	// Разблокировать поля шага.
	var enabledStepForm = function(step) {
		if (!options) {
			return;
		}

		options.steps.filter(!step ? '.step' : '.step-' + step).find('input, button').attr('disabled', false);
	};

	/**
	 *  Роутер.
	 */
	$.fn.RestoreEmail = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('RestoreEmail: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);