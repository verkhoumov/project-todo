// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для редактирования доступа к списку.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			share_id: 0,
			callbacks: {
				onSuccess: function(data) {}
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
				$(this).on('submit.ShareEdit', function(e) {
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
				$(this).off('.ShareEdit');
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

		let status = 400;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Access/edit',
			timeout: 3000,
			data: options.form.serialize() + '&share_id=' + options.share_id,
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

		// Вызываем Callback.
		options.callbacks.onSuccess({
			login: $('#form-access-identity').val() || null,
			access_read: $('#form-access-read:checked').val() ? 1 : 0,
			access_edit: $('#form-access-edit:checked').val() ? 1 : 0
		});
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		// Снимаем блокировку с формы в случае ошибки.
		enabledForm();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402) HELPER.noty('error', LANG.shareUndefined);
		else if (status == 403) HELPER.noty('error', LANG.onlyOnwerHaveRules);
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
	$.fn.ShareEdit = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('ShareEdit: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);