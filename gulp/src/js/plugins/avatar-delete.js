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
					button: $(this)
				}, defaultOptions, customOptions);

				// Обработка нажатия на кнопку удаления аватара.
				$(this).on('click.DeleteAvatar', function(e) {
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
			url: '/Api/User/deleteImage',
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
					success();
				} else {
					error(status);
				}
			}
		});
	};

	// Успешная обработка запроса.
	var success = function() {
		HELPER.noty('success', LANG.imageDeleteSuccess); // 400, 402

		// Устанавливаем изображение по-умолчанию.
		$('#settings-image').get(0).src = HELPER.getDefaultUserImage();
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		// Снимаем блокировку с кнопки.
		enabledButton();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else HELPER.noty('error', LANG.requestProcessError); // 400, 402
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
	$.fn.DeleteAvatar = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('DeleteAvatar: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);