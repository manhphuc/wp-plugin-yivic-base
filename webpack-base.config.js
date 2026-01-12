/**
 * WEBPACK CONFIGURATION
 */
const path = require('path');
const webpackBuildNotifierPlugin = require('webpack-build-notifier');
const TerserPlugin = require('terser-webpack-plugin');
const miniCssExtractPlugin = require("mini-css-extract-plugin");
const cssMinimizerPlugin = require("css-minimizer-webpack-plugin");

module.exports.buildConfig = function (webpackVariables, mode) {
	const isProduction = mode === 'production';
	return {
		devtool: isProduction ? false : 'inline-source-map',
		entry: webpackVariables.webpackParams.entryPath,
		output: {
			filename: webpackVariables.webpackParams.jsOutputPath,
			path: path.resolve(__dirname),
		},
		module: {
			rules: [
				{
					test: /\.tsx?$/,
					use: 'ts-loader',
					exclude: /node_modules/,
				},
				// perform js babelization on all .js files
				{
					test: /\.js$/,
					exclude: /node_modules/,
					loader: 'babel-loader',
					options: {
						presets: [
							['@babel/preset-env', {targets: "defaults"}]
						]
					}
				},
				{
					test: /\.js$/,
					enforce: "pre",
					use: ["source-map-loader"],
				},
				// inject CSS to page
				{
					test: /\.css$/i,
					use: [miniCssExtractPlugin.loader, 'style-loader', 'css-loader', 'postcss-loader']
				},

				// compile all .scss files to plain old css
				{
					test: /\.(sass|scss)$/,
					use: [
						miniCssExtractPlugin.loader,
						{
							loader: 'css-loader',
							options: {
								sourceMap: true,
								url: false
							},

						},
						{
							loader: 'resolve-url-loader',
							options: {
								sourceMap: true,
							},
						},
						{
							loader: 'postcss-loader',
							options: {
								sourceMap: true
							}
						},
						{
							loader: 'sass-loader',
							options: {
								sourceMap: true
							}
						}
					]
				},
			]
		},
		plugins: [
			// extract css into dedicated file
			new miniCssExtractPlugin({
				filename: webpackVariables.webpackParams.cssOutputPath,
			}),

			// notifier plugin
			new webpackBuildNotifierPlugin({
				title: "WP Webpack Build",
				suppressSuccess: true
			}),
		],
		resolve: {
			extensions: ['.js', '.jsx', '.ts', '.tsx'],
		},
		optimization: {
			minimizer: [
				// enable the js minification plugin
				new TerserPlugin({
					parallel: true,
					terserOptions: {
						sourceMap: true,
						compress: true,
					},
				}),
				// enable the css minification plugin
				new cssMinimizerPlugin(),
			]
		}
	}
};
