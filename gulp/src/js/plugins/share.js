// jQuery handler.
require('jquery');

// Словарь.
var LANG = require('lang');

// Дополнительные модули.
require('share-check');
require('share-create');
require('share-edit');
require('share-delete');

/**
 *  Плагин для управления доступом к списку.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			type: null,
			modal: null,
			share: null,
			container: null,
			element: null,
			callbacks: {
				onCreate: function(share_id, data) {},
				onUpdate: function(share_id, data) {},
				onDelete: function(share_id) {}
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
					form: $(this)
				}, defaultOptions, customOptions);

				// В зависимости от ситуации инициируем определённые обработчики.
				if (options.type == 'create') {
					// Проверка аккаунта пользователя.
					if ($.fn.ShareCheck) {
						options.form.ShareCheck({
							callbacks: {
								onSuccess: function(user_id) {
									if (user_id) {
										options.share.user_id = user_id;
									}

									// Создание нового доступа.
									if ($.fn.ShareCreate) {
										options.form.ShareCreate({
											list_id: options.share.list_id,
											user_id: options.share.user_id,
											container: options.container,
											callbacks: {
												onSuccess: function(share) {
													if (share) {
														// Вызываем Callback.
														options.callbacks.onCreate(share.id, share);
														// Закрываем модальное окно.
														options.modal.modal('hide');
													}
												}
											}
										});
									}
								}
							}
						});
					}
				} else {
					// Изменение параметров доступа.
					if ($.fn.ShareEdit) {
						options.form.ShareEdit({
							share_id: options.share.id,
							callbacks: {
								onSuccess: function(share) {
									if (share) {
										// Подставляем данные в форму.
										setFormValues(share);
										// Вызываем Callback.
										options.callbacks.onUpdate(options.share.id, share);
										// Обновляем макет.
										options.element.find('[data-access-type="read"]')[!!share.access_read ? 'addClass' : 'removeClass']('active');
										options.element.find('[data-access-type="edit"]')[!!share.access_edit ? 'addClass' : 'removeClass']('active');
									}
								}
							}
						});
					}

					// Удаление доступа.
					if ($.fn.ShareDelete) {
						$('#share-delete').ShareDelete({
							share_id: options.share.id,
							callbacks: {
								onSuccess: function() {
									if (!options) {
										return;
									}

									// Удаляем элемент.
									options.element.remove();
									// Вызываем Callback.
									options.callbacks.onDelete(options.share.id);
									// Закрываем модальное окно.
									options.modal.modal('hide');
								}
							}
						});
					}
				}

				// Построение модального окна под изменение или создание нового доступа.
				buildModal();
			});
		},

		// Отключение плагина и всех сопутствующих обработчиков.
		destroy: function() {
			return this.each(function() {
				if (!options) {
					return;
				}

				// Отключаем все обработчики.
				if ($.fn.ShareCheck) options.form.ShareCheck('destroy');
				if ($.fn.ShareCreate) options.form.ShareCreate('destroy');
				if ($.fn.ShareEdit) options.form.ShareEdit('destroy');
				if ($.fn.ShareDelete) $('#share-delete').ShareDelete('destroy');

				// Делаем сброс формы.
				options.form.get(0).reset();

				// Удаляем параметры.
				options = null;
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	// Компоновка модального окна в зависимости от операции.
	var buildModal = function() {
		if (!options) {
			return;
		}

		if (options.type == 'create') {
			options.modal.find('#modal-share-title').text(LANG.modalCreateShare);
			options.modal.find('#form-access-identity-button').parent().removeClass('d-none');
			options.modal.find('#form-access-identity').attr('disabled', false).removeClass('disabled');
			options.modal.find('form [type="submit"]').text(LANG.modalButtonCreate).attr('disabled', true);
			options.modal.find('#share-delete').parent().addClass('d-none');
		} else {
			options.modal.find('#modal-share-title').text(LANG.modalEditShare);
			options.modal.find('#form-access-identity-button').parent().addClass('d-none');
			options.modal.find('#form-access-identity').attr('disabled', true).addClass('disabled');
			options.modal.find('form [type="submit"]').text(LANG.modalButtonSave).attr('disabled', false);
			options.modal.find('#share-delete').parent().removeClass('d-none');

			// Подставляем данные в форму.
			setFormValues(options.share);
		}
	};

	// Установка значений для полей формы.
	var setFormValues = function(data) {
		if (!options || !data) {
			return;
		}

		if (typeof data.login !== 'undefined') {
			$('#form-access-identity').val(data.login);
		}

		if (typeof data.access_read !== 'undefined') {
			$('#form-access-read').attr('checked', data.access_read == 1 ? true : false);
		}

		if (typeof data.access_edit !== 'undefined') {
			$('#form-access-edit').attr('checked', data.access_edit == 1 ? true : false);
		}
	};

	/**
	 *  Роутер.
	 */
	$.fn.Share = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('Share: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);