// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

/**
 *  Плагин для изменения настроек пользователя.
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
					inputs: $(this).find('input, textarea').filter(':not(.disabled)'),
					buttons: $(this).find('button'),
					emailInput: $('#settings-form-email'),
					email: $('#settings-form-email').val()
				}, defaultOptions, customOptions);

				// При любом изменении формы активируем кнопку сохранения.
				$(this).on('change.Settings', function(e) {
					$(this).find('[type="submit"]').attr('disabled', false);
				});

				// Обработка нажатия на кнопку авторизации.
				$(this).on('submit.Settings', function(e) {
					// Отменяем отправку формы.
					e.preventDefault();
					// Вызываем AJAX-запрос для авторизации.
					ajax.call(this);
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

		let status = 400,
			errors = null,
			data = null;

		// С помощью FormData можно добавить загрузку изображения вместе с прочими данными.
		var formData = new FormData(this);

		// Удаляем изображение из формы.
		formData.delete('image');
		formData.append('image', $('#settings-form-avatar').get(0).files[0]);

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '/Api/User/edit',
			timeout: 20000,
			contentType: false,
			processData: false,
			cache: false,
			data: formData,
			beforeSend: function() {
				// Блокируем форму.
				disabledForm();
			},
			success: function(json) {
				if (json.status && json.status >= 200) {
					status = json.status;
					errors = json.errors;
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
					error(status, errors);
				}
			}
		});
	};

	// Успешная обработка запроса.
	var success = function(data) {
		if (!options) {
			return;
		}

		// Сообщаем об успешном изменении настроек.
		options.inputs.addClass('is-valid');

		// Показываем уведомление.
		HELPER.noty('success', LANG.formEditSuccess);

		// Если изменился E-mail, показываем кнопку подтверждения.
		if (options.email != options.emailInput.val()) {
			// Показываем элементы подтверждения.
			$('#email-accept').removeClass('d-none');
		}

		// Если указано изображение, обновляем его.
		if (data.image) {
			// Загружаем аватар пользователя.
			HELPER.preloadImage(data.image, function(status, result) {
				// Заменяем незагруженные изображения на дефолтные.
				if (status == 'error') {
					$.each(result.failedLinks, function(index, image) {
						if (image == data.image) {
							data.image = HELPER.getDefaultTaskImage();
						}
					});
				}

				options.form.find('#settings-image').get(0).src = data.image;
				options.form.find('#settings-form-avatar').val('');
			});
		}

		// Через 2 секунды скрываем эффект успеха.
		setTimeout(function() {
			options.inputs.removeClass('is-valid');

			// Снимаем блокировку с формы.
			enabledForm();
		}, 2000);
	};

	// Ошибка при выполнении запроса.
	var error = function(status) {
		// Снимаем блокировку с формы в случае ошибки.
		enabledForm();

		// В зависимости от кода ошибки показываем соответствующее уведомление.
		if (status < 400 && status != 301) HELPER.noty('error', LANG.requestSendError); // 300+
		else if (status == 401 || status == 301) window.location.href = '/';
		else if (status == 402) HELPER.noty('error', LANG.formError);
		else if (status == 403) HELPER.noty('error', LANG.formImageError);
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
	$.fn.Settings = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('Settings: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);