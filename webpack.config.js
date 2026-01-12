/**
 * WEBPACK CONFIGURATION
 */

const baseConfig = require('./webpack-base.config');

const pluginVariables = require('./webpack.var.config');

module.exports = function (env, argv) {
	return baseConfig.buildConfig(pluginVariables, argv.mode);
};
