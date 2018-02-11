// jQuery handler.
require('jquery');

// Словарь.
var LANG = require('lang');

/**
 *  Работа поиска.
 */
(function($) {
	'use strict';
	
	/**
	 *  Параметры.
	 */
	var options = null,
		defaultOptions = {
			tasks: null,
			tasksList: null
		};

	// Происходит ли поиск?
	var isSearch = false;

	/**
	 *  Публичные методы.
	 */
	var methods = {
		// Инициализация плагина.
		load: function(customOptions) {
			return this.each(function() {
				options = $.extend(true, {
					form: $(this),
					input: $(this).find('#search-form-text'),
					select: $(this).find('#search-form-tags'),
					resetButton: $(this).find('button[type="reset"]'),
					submitButton: $(this).find('button[type="submit"]')
				}, defaultOptions, customOptions);

				// Запуск поиска.
				options.form.on('submit', function(event) {
					event.preventDefault();
					submit();
				});

				// Сброс поиска.
				options.form.on('reset', function() {
					reset();
				});
			});
		},

		// Формирование списка меток в форме поиска.
		makeSelect: function(searchTags) {
			return this.each(function() {
				if (!options) {
					return;
				}

				makeSelectTags(searchTags);
			});
		},

		// Перезапуск поиска.
		research: function(tasks) {
			return this.each(function() {
				if (!options) {
					return;
				}

				// Обновляем список тасков.
				options.tasks = tasks;

				if (isSearch) {
					submit();
				}
			});
		}
	};

	/**
	 *  Приватные методы.
	 */
	//
	var submit = function() {
		if (!options) {
			return;
		}

		isSearch = true;

		// Делаем все таски видимыми.
		options.tasksList.find('.task-item').removeClass('find');

		// Активируем режим поиска.
		options.tasksList.addClass('search');

		// Получаем поисковую фразу и список меток.
		var word = getWord(),
			tags = getTags();

		// Проходим по каждому таску и ищем хоть какие-то вхождения.
		$.each(options.tasks, function(index, task) {
			var $element = options.tasksList.find('.task-item[data-task-id="' + task.id + '"]');

			if (word && word.length && task.name.indexOf(word) !== -1) {
				$element.addClass('find');
				return;
			}

			if (task.tags && task.tags.length) {
				$.each(task.tags, function(index, tag) {
					if (tags[tag]) {
						$element.addClass('find');
						return;
					}
				});
			}
		});
	};

	// Сброс формы и отмена поиска.
	var reset = function() {
		if (!options) {
			return;
		}

		isSearch = false;

		// Деактивируем режим поиска.
		options.tasksList.removeClass('search');
		// Делаем все таски видимыми.
		options.tasksList.find('.task-item').removeClass('find');
	};

	// Получение поисковой фразы.
	var getWord = function() {
		return options.input.val();
	};

	// Получение списка меток.
	var getTags = function() {
		var tags = {};

		options.select.children(':selected').each(function(index, el) {
			var tag = $(el).val();

			if (tag && tag.length) {
				tags[tag] = true;
			}
		});

		return tags;
	};

	// Перестроение выпадающего списка.
	var makeSelectTags = function(searchTags) {
		if (!options || !searchTags || !searchTags.length) {
			return;
		}

		var selected = {};

		// Запоминаем активные пункты.
		options.select.children(':selected').each(function(index, el) {
			selected[$(el).val()] = true;
		});

		// Очищаем текущий список.
		options.select.empty();
		options.select.append($('<option>' + LANG.selectTags + '</option>'));

		// Формируем новый список.
		$.each(searchTags, function(index, tag) {
			var $option = $('<option value="' + tag + '">' + tag + '</option>');

			if (selected[tag]) {
				$option.attr('selected', true);
			}

			options.select.append($option);
		});
	};

	/**
	 *  Роутер.
	 */
	$.fn.Search = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.load.apply(this, arguments);
		} else {
			$.error('Search: запрашиваемый метод `' +  method + '` отсутствует!');
		}
	};
})(jQuery);