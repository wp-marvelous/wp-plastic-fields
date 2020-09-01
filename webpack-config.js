const path = require('path'),
	AutoPrefixer = require('autoprefixer'),
	LiveReloadPlugin = require('webpack-livereload-plugin'),
	MiniCssExtractPlugin = require('mini-css-extract-plugin'),
	UglifyJSPlugin = require('uglifyjs-webpack-plugin'),
	OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin'),
	StyleLintPlugin = require('stylelint-webpack-plugin');

// Development
const devConfig = {
	context: __dirname,
	entry: {
		//frontend: ['./src/assets/js/frontend/frontend-index.js', './src/assets/scss/frontend/frontend.scss'],
		admin: ['./src/assets/js/admin/admin-index.js', './src/assets/scss/admin/admin.scss']
	},
	output: {
		path: path.resolve(__dirname, 'assets'),
		filename: '[name].js'
	},
	mode: 'development',
	devtool: 'source-map',
	module: {
		rules: [
			{
				enforce: 'pre',
				exclude: /node_modules/,
				test: /\.jsx?$/,
				loader: 'eslint-loader',
			},
			{
				test: /\.jsx?$/,
				loader: 'babel-loader',
				options: {
					presets: ["@babel/preset-env"]
				}
			},
			{
				test: /\.(scss|css)$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: "css-loader"
					},
					{
						loader: "postcss-loader",
						options: {
							autoprefixer: {
								browsers: ["last 2 versions"]
							},
							plugins: () => [
								AutoPrefixer
							]
						},
					},
					{
						loader: "sass-loader",
						options: {}
					}
				]
			}
		]
	},
	plugins: [
		new LiveReloadPlugin({}),
		new StyleLintPlugin({context: __dirname + "/src/assets/scss"}),
		new MiniCssExtractPlugin({filename: '[name].css'}),
	],
	optimization: {
		minimize: false
	}
};

// Production
const prodConfig = {
	...devConfig,
	mode: 'production',
	output: {
		path: path.resolve(__dirname, 'assets'),
		filename: '[name].min.js'
	},
	plugins: [
		new StyleLintPlugin({fix: true, context: __dirname + "/src/assets/scss"}),
		new MiniCssExtractPlugin({filename: '[name].min.css'}),
	],
	optimization: {
		minimizer: [new UglifyJSPlugin(), new OptimizeCssAssetsPlugin()]
	}
}

module.exports = (env, argv) => {
	switch (argv.mode) {
		case 'production':
			return prodConfig;
		default:
			return devConfig;
	}
}