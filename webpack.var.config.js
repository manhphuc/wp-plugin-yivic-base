/* Set webpack variables */

/* Set webpack variables */
const basePath = '.';

var webpackParams = {
    // Input file path
    entryPath: {
        main: [
			basePath + '/public-assets/src/js/main.js',
			basePath + '/public-assets/src/scss/main.scss',
		],
        admin: [
			basePath + '/public-assets/src/js/admin.js',
			basePath + '/public-assets/src/scss/admin.scss',
		],
	},

    // Output for CSS and JS
    jsOutputPath: basePath + '/public-assets/dist/js/[name].js',
    cssOutputPath: basePath + '/public-assets/dist/css/[name].css',
};

module.exports = { webpackParams };



