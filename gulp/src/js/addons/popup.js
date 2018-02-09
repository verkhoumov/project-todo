// Подключение плагина для создания всплывающих окон.
require('popup');

// Вспомогательные функции.
var HELPER = require('functions');

// Параметры по-умолчанию.
if ($ && $.magnificPopup) {
	$.magnificPopup.defaults = HELPER.extend($.magnificPopup.defaults, {
		midClick: true,
		removalDelay: 300,
		mainClass: 'mfp-fade',
		closeMarkup: '<div class="mfp-close modal-close icon icon-modal-close"></div>'
	});
}