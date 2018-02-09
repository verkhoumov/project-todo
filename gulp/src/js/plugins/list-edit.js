// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для создания нового списка задач или изменения старого.
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
					type: $(this).data('type'),
					title: $('#list-title'),
					description: $('#list-description'),
					inputTitle: $(this).find('#list-form-title'),
					inputDescription: $(this).find('#list-form-description'),
					list_id: 0
				}, defaultOptions, customOptions);

				// Обработка нажатия на кнопку регистрации.
				$(this).on('submit.EditList', function(e) {
					// Отменяем отправку формы.
					e.preventDefault();
					// Вызываем AJAX-запрос для регистрации.
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

				// Снимаем блокировку с полей и кнопок.
				enabledForm();

				// Отключаем обработчик.
				options.form.off('.EditList');

				// Удаляем все параметры.
				options = null;
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

		let status = 400,
			data = null;

		let postfix = '';

		if (options.type == 'edit' && options.list_id > 0) {
			postfix = '&list_id=' + options.list_id;
		}

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/Lists/' + options.type,
			timeout: 3000,
			data: options.form.serialize() + postfix,
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
		if (!options) {
			return;
		}

		// Сообщаем об успешной регистрации.
		options.inputs.addClass('is-valid');

		if (options.type == 'create') {
			// Через 2 секунды переходим на страницу со списком.
			setTimeout(function() {
				window.location.href = '/lists/' + data.list_id;
			}, 2000);
		} else {
			// Через 2 секунды убираем блокировку с формы и эффект успеха.
			setTimeout(function() {
				if (!options) {
					return;
				}

				options.inputs.removeClass('is-valid');
				enabledForm();
			}, 2000);

			// Обновляем информацию на странице.
			options.title.text(options.inputTitle.val());
			options.description.text(options.inputDescription.val());
		}
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		if (!options) {
			return;
		}

		// Снимаем блокировку с формы в случае ошибки.
		enabledForm();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402 && options.type == 'create') HELPER.noty('error', LANG.formError);
		else if (status == 402 && options.type == 'edit') HELPER.noty('error', LANG.formListIdError);
		else if (status == 403 && options.type == 'edit') HELPER.noty('error', LANG.formError);
		else HELPER.noty('error', LANG.requestProcessError); // 400, 403
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
	$.fn.EditList = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('EditList: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);