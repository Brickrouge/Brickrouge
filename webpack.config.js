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
				exclude: /(node_modules|bower_components)/,
				loader: 'babel',
				query: {
					presets: [ 'es2015' ]
				}
			}
		]
	}
}
