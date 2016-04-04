module.exports = {

	entry: './lib/Brickrouge.js',
	output: {
		path: __dirname + '/build',
		filename: 'brickrouge-uncompressed.js'
	},
	module: {
		loaders: [
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/
			}
		]
	}
}
