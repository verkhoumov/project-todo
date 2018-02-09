// jQuery handler.
require('jquery');

// Вспомогательные функции.
var HELPER = require('functions');
// Словарь.
var LANG = require('lang');

// Дополнительные модули.
require('task-create');
require('task-delete');
require('task-deleteImage');
require('task-edit');
require('task-tags');

/**
 *  Плагин для управления списком задач.
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
			task: null,
			container: null,
			element: null,
			callbacks: {
				onCreate: function(task_id, data) {},
				onUpdate: function(task_id, data) {},
				onDelete: function(task_id) {}
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
					// Создание нового таска.
					if ($.fn.TaskCreate) {
						options.form.TaskCreate({
							list_id: options.task.list_id,
							container: options.container,
							callbacks: {
								onSuccess: function(task) {
									if (task) {
										// Вызываем Callback.
										options.callbacks.onCreate(task.id, task);
										// Закрываем модальное окно.
										options.modal.modal('hide');
									}
								}
							}
						});
					}

					// Менеджер меток.
					if ($.fn.TaskTags) {
						options.form.TaskTags();
					}
				} else {
					// Изменение таска.
					if ($.fn.TaskEdit) {
						options.form.TaskEdit({
							task_id: options.task.id,
							element: options.element,
							callbacks: {
								onSuccess: function(task, newElement) {
									if (task) {
										// Подставляем данные в форму.
										setFormValues(task);
										// Вызываем Callback.
										options.callbacks.onUpdate(options.task.id, task);
									}

									if (newElement) {
										options.element = newElement;
									}
								}
							}
						});
					}

					// Удаление таска.
					if ($.fn.TaskDelete) {
						$('#form-task-delete').TaskDelete({
							task_id: options.task.id,
							callbacks: {
								onSuccess: function() {
									if (!options) {
										return;
									}

									// Удаляем элемент.
									options.element.remove();
									// Вызываем Callback.
									options.callbacks.onDelete(options.task.id);
									// Закрываем модальное окно.
									options.modal.modal('hide');
								}
							}
						});
					}

					// Удаление изображения таска.
					if ($.fn.TaskDeleteImage) {
						$('#form-task-image-delete').TaskDeleteImage({
							task_id: options.task.id,
							callbacks: {
								onSuccess: function() {
									// Сбрасываем изображение модального окна.
									options.modal.find('#form-task-image').get(0).src = HELPER.getDefaultTaskImage();
									// Сбрасываем изображение элемента.
									options.element.find('.image > img').get(0).src = HELPER.getDefaultTaskImage();
								}
							}
						});
					}

					// Менеджер меток.
					if ($.fn.TaskTags) {
						options.form.TaskTags({
							tags: options.task.tags
						});
					}
				}

				// Построение модального окна под изменение или создание нового таска.
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
				if ($.fn.TaskCreate) options.form.TaskCreate('destroy');
				if ($.fn.TaskEdit) options.form.TaskEdit('destroy');
				if ($.fn.TaskTags) options.form.TaskTags('destroy');
				if ($.fn.TaskDelete) $('#form-task-delete').TaskDelete('destroy');
				if ($.fn.TaskDeleteImage) $('#form-task-image-delete').TaskDeleteImage('destroy');

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
			options.modal.find('#modal-task-title').text(LANG.modalCreateTask);
			options.modal.find('#form-task-image-delete').attr('disabled', true);
			options.modal.find('#form-task-delete').parent().addClass('d-none');
			options.modal.find('form [type="submit"]').text(LANG.modalButtonCreate);
			options.modal.find('#form-task-image').get(0).src = HELPER.getDefaultTaskImage();
		} else {
			options.modal.find('#modal-task-title').text(LANG.modalEditTask);
			options.modal.find('#form-task-image-delete').attr('disabled', false);
			options.modal.find('#form-task-delete').parent().removeClass('d-none');
			options.modal.find('form [type="submit"]').text(LANG.modalButtonSave);
			options.modal.find('#form-task-image').get(0).src = options.task.image ? options.task.image : HELPER.getDefaultTaskImage();

			// Подставляем данные в форму.
			setFormValues(options.task);
		}
	};

	// Установка значений для полей формы.
	var setFormValues = function(data) {
		if (!options || !data) {
			return;
		}

		if (typeof data.name !== 'undefined') {
			$('#form-task-name').val(data.name);
		}

		if (typeof data.status !== 'undefined') {
			$('#form-task-status').attr('checked', data.status == 1 ? true : false);
		}
	};

	/**
	 *  Роутер.
	 */
	$.fn.Task = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('Task: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);