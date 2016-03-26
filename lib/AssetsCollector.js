define([

	'./Core'

], function (Brickrouge) {
	"use strict";

	var available_css = null
	, available_js = null

	/**
	 * Updates the document assets then calls a callback function.
	 *
	 * @param assets An object with a 'css' and a 'js' array defining the assets required.
	 * @param done An optional callback to call once the required assets have been loaded.
	 */
	Brickrouge.updateAssets = function (assets, done) {

		var css = [], js = [], js_count

		if (available_css === null)
		{
			available_css = []

			if (typeof(brickrouge_cached_css_assets) !== 'undefined') {
				available_css = brickrouge_cached_css_assets
			}

			document.head.querySelectorAll('link[type="text/css"]').forEach(function (el) {

				available_css.push(el.getAttribute('href'))

			})
		}

		if (available_js === null)
		{
			available_js = []

			if (typeof(brickrouge_cached_js_assets) !== 'undefined')
			{
				available_js = brickrouge_cached_js_assets
			}

			document.html.querySelectorAll('script').forEach(function (el) {

				var src = el.getAttribute('src')

				if (src) available_js.push(src)

			})
		}

		assets.css.each(function (url) {

			if (available_css.indexOf(url) != -1) return
			css.push(url)
		})

		css.each(function (url) {

			new Asset.css(url)
			available_css.push(url)
		})

		assets.js.each(function (url) {

			if (available_js.indexOf(url) != -1) return
			js.push(url)
		})

		js_count = js.length

		if (!js_count) {
			done();
			return
		}

		js.each(function (url) {

			new Asset.javascript(url, {

				onload: function () {

					available_js.push(url)
					if (!--js_count) done()
				}
			})
		})
	}

});
