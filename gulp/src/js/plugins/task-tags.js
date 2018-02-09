// jQuery handler.
require('jquery');

// Модуль для парсинга шаблонов.
var Mustache = require('mustache');

/**
 *  Менеджер меток к задаче.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			tags: [],
			templates: {
				tag: $('#template-tag').html()
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
					container: $(this).find('.form-tags'),
					addButton: $(this).find('#form-task-tags-add')
				}, defaultOptions, customOptions);

				// Первичный рендеринг.
				prerender();

				// Если передан список меток, добавляем все.
				if (options.tags && options.tags.length) {
					options.tags.forEach(function(item) {
						add(item);
					});
				}

				// Добавление новой метки.
				options.addButton.on('click.TaskTags', function() {
					add();
				});

				// Удаление существующей метки.
				options.container.on('click.TaskTags', '.tag-delete', function() {
					remove.call($(this).parents('.tag-item'));
				});
			});
		},

		destroy: function() {
			return this.each(function() {
				if (!options) {
					return;
				}

				// Отключаем обработчики.
				options.addButton.off('.TaskTags');
				options.container.off('.TaskTags');

				// Удаляем все метки.
				options.container.empty();

				options = null;
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	// Добавление поля для новой метки.
	var add = function(data) {
		if (!options) {
			return;
		}

		push({value: data || null});
	};

	// Удаление поля с меткой.
	var remove = function() {
		if (!options) {
			return;
		}

		$(this).remove();
	};

	// Обрабатываем и добавляем новую карточку в обёртку.
	var push = function(data) {
		if (!options || !data) {
			return;
		}

		// Парсим шаблон.
		var $tag = parse(data);

		if ($tag) {
			// Вставляем новую форму для метки в список.
			$tag.appendTo(options.container);
		}
	};

	// Парсим информацию о доступе.
	var parse = function(data) {
		if (!options) {
			return;
		}

		// Форматирование данных.
		// ...

		// Рендер.
		var render = Mustache.render(options.templates.tag, data || {});

		return $(render);
	};

	// Подготовка шаблонов к дальнейшей работе.
	var prerender = function() {
		if (!options) {
			return;
		}

		$.each(options.templates, function(key, template) {
			Mustache.parse(template);
		});
	};

	/**
	 *  Роутер.
	 */
	$.fn.TaskTags = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('TaskTags: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);