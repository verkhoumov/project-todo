var moment = require('moment');

// Добавляем русский перевод.
require('moment-ru');

// Язык и временная зона по-умолчанию.
moment.locale('ru');
moment().utcOffset(180);

module.exports = moment;