// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для проверки пользователя, чтобы дать ему доступ к списку.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			callbacks: {
				onSuccess: function(user_id) {}
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
					input: $(this).find('#form-access-identity'),
					checkButton: $(this).find('#form-access-identity-button'),
					createButton: $(this).find('[name="access[submit]"]')
				}, defaultOptions, customOptions);

				// Обработка нажатия на кнопку сохранения.
				options.checkButton.on('click.ShareCheck', function(e) {
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
				options.checkButton.off('.ShareCheck');
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

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Access/check',
			timeout: 3000,
			data: {
				identity: options.input.val()
			},
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

		// Меняем цвет формы.
		let input = options.input;
		input.removeClass('is-invalid').addClass('is-valid');

		setTimeout(function() {
			input.removeClass('is-valid');
		}, 2000);

		// Снимаем блокировку с кнопки "Создать".
		options.createButton.attr('disabled', false);
		// Вызываем Callback.
		options.callbacks.onSuccess(data.user_id || 0);
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		// Снимаем блокировку с формы в случае ошибки.
		enabledForm();
		// Блокируем кнопку "Создать".
		options.createButton.attr('disabled', true);
		// Меняем цвет формы.
		options.input.addClass('is-invalid').removeClass('is-valid');

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402) HELPER.noty('error', LANG.formIdentityError);
		else if (status == 403) HELPER.noty('error', LANG.userNotExists);
		else if (status == 404) HELPER.noty('error', LANG.youIsOwner);
		else HELPER.noty('error', LANG.requestProcessError); // 400
	};

	// Блокировка формы на время выполнения запроса.
	var disabledForm = function() {
		if (!options) {
			return;
		}

		options.input.attr('disabled', true);
		options.checkButton.attr('disabled', true);
	};

	// Снятие блокировки при возникновении ошибки.
	var enabledForm = function() {
		if (!options) {
			return;
		}

		options.input.attr('disabled', false);
		options.checkButton.attr('disabled', false);
	};

	/**
	 *  Роутер.
	 */
	$.fn.ShareCheck = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('ShareCheck: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);