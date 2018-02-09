// jQuery handler.
require('jquery');

// Модуль для работы с уведомлениями.
var Noty = require('./addons/noty');

// Параметры.
var CONFIG = require('config');

// Вспомогательные функции.
var HELPER = {
	// Обработка ошибки при AJAX-запросе.
	getAjaxError: function(jqXHR, textStatus, errorThrown) {
		status = 310;

		if (textStatus) {
			var errors = {
				timeout: 310,
				error: 311,
				abort: 312,
				parsererror: 313
			};

			if (errors[textStatus]) {
				status = errors[textStatus];
			}
		}

		return status;
	},

	// Уведомления.
	_isNotyFromTop: $(window).width() < CONFIG.screenSizes.xs,

	noty: function(type, message, timeout) {
		if (!Noty) {
			return;
		}

		return new Noty({
			animation: {
				open: 'animated bounceIn' + (this._isNotyFromTop ? 'Down' : 'Right'),
				close: 'animated bounceOut' + (this._isNotyFromTop ? 'Up' : 'Right')
			},
			type: type || 'error',
			text: message,
			timeout: typeof timeout !== 'undefined' ? timeout : 5000
		}).show();
	},

	// Фильтрация массива таким образом, чтобы остались только уникальные элементы.
	arrayUnique: function(array) {
		if (!array || !array.length) {
			return [];
		}

		return array.filter(function(value, index, arr) {
			return value && arr.indexOf(value) === index;
		});
	},

	// Загрузка серии изображений.
	preloadImages: function(images, callback) {
		if (!images || !images.length) {
			if (typeof callback === 'function') {
				callback();
			}

			return false;
		}

		// Оставляем только уникальные изображения.
		var _images = this.arrayUnique(images);

		var status = {
			count: _images.length, 
			loaded: 0,
			loadedLinks: [],
			failed: 0,
			failedLinks: []
		};

		// Завершение загрузки изображений.
		var complete = function(status, data) {
			if (typeof callback === 'function') {
				callback(status, data);
			}
		};

		// Проход по каждому изображению с последующей загрузкой.
		_images.forEach(function(image) {
			var img = new Image();

			// Обработчик успешной загрузки изображения.
			img.onload = function() {
				// Проверка изображения.
				if ('naturalHeight' in this) {
					if (this.naturalHeight + this.naturalWidth === 0) {
						this.onerror();
						return;
					}
				} else if (this.width + this.height == 0) {
					this.onerror();
					return;
				}

				// Начиная с данного места изображение можно считать загруженным.
				++status.loaded;

				// Запоминаем ссылку на изображение.
				status.loadedLinks.push(image);

				// Если все изображения обработаны, вызываем callback. При этом, если
				// каждое удалось загрузить, отмечаем процесс как success.
				if (status.loaded == status.count) {
					complete('success', status);
				} else if ((status.loaded + status.failed) == status.count) {
					complete('error', status);
				}
			};

			// Обработчик ошибки при загрузке изображения.
			img.onerror = function() {
				++status.failed;

				// Запоминаем нерабочую ссылку.
				status.failedLinks.push(image);
			
				// Если все изображения обработаны, вызываем callback.
				if ((status.loaded + status.failed) == status.count) {
					complete('error', status);
				}
			};

			// Ссылка на изображения.
			img.src = image;
		});

		return status;
	},

	// Загрузка одного изображения.
	preloadImage: function(link, callback) {
		return this.preloadImages([link], callback);
	},

	// Изображение пользователя по-умолчанию.
	getDefaultUserImage: () => {
		return '/upload/images/user.png';
	},

	// Изображение задачи по-умолчанию.
	getDefaultTaskImage: () => {
		return '/upload/images/task.png';
	}
};

module.exports = HELPER;