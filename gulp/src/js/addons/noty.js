/**
 *  Import addon.
 */
var Noty = require('noty');

/**
 *  Defaults.
 */
if (typeof Noty !== 'undefined') {
	Noty.overrideDefaults({
		theme: 'custom',
		progressBar: false
	});
}

module.exports = Noty;