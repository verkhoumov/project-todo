// Вспомогательные функции.
var HELPER = require('functions');

/**
 *  Эффект загрузки изображений.
 */
// Обработчик загрузки изображения.
var loader = (image, type) => {
	// Обработчик успешной загрузки.
	image.onload = function() {
		// Валидация изображения.
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
		this.parentNode.classList.add('image-loader-success');
	};

	// Обработчик неудачной загрузки.
	image.onerror = function() {
		if (type == 'task') {
			this.src = HELPER.getDefaultTaskImage();
		} else if (type == 'user') {
			this.src = HELPER.getDefaultUserImage();
		}

		this.parentNode.classList.add('image-loader-error');
	};

	// Если изображение взято из кеша, инициируем успешную загрузку.
	if (image.complete) {
		image.onload();
	}
};

// Список изображений со страницы.
var tasks = document.querySelectorAll('.image-loader-task > img'),
	users = document.querySelectorAll('.image-loader-user > img');

// Вешаем на каждое изображение обработчик.
tasks.forEach(image => loader(image, 'case'));
users.forEach(image => loader(image, 'user'));