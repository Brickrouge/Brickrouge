module.exports = {

	entry: './lib/Brickrouge.js',
	output: {
		path: __dirname + '/assets',
		filename: 'brickrouge.js'
	},
	module: {
		loaders: [
			{
				test: /\.js$/,
				loader: 'babel-loader',
				query: {
					presets: [ 'es2015', 'es2016' ]
				}
			}
		]
	}
}
