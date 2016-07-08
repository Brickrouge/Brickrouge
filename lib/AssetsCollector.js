define([

	'./Core',
	'olvlvl-assets-promises'

], function (Brickrouge, assetsPromises) {

	"use strict";

	const StyleSheetPromises = assetsPromises.StyleSheetPromise
	const JavaScriptPromises = assetsPromises.JavaScriptPromise

	let availableCSS = null
	let availableJS = null

	/**
	 * @param {{css: Array<string>, js: Array<string>}} assets
	 *
	 * @returns {{css: Array<string>, js: Array<string>}}
	 */
	function filterMissing(assets)
	{
		const css = []
		const js = []

		if (availableCSS === null)
		{
			availableCSS = typeof brickrouge_cached_css_assets !== 'undefined'
				? brickrouge_cached_css_assets
				: []

			document.head.querySelectorAll('link[href][type="text/css"]').forEach(el => {

				availableCSS.push(el.getAttribute('href'))

			})
		}

		if (availableJS === null)
		{
			availableJS = typeof brickrouge_cached_js_assets !== 'undefined'
				? brickrouge_cached_js_assets
				: []

			document.html.querySelectorAll('script[src]').forEach(el => {

				availableJS.push(el.getAttribute('src'))

			})
		}

		assets.css.forEach(url => {

			if (availableCSS.indexOf(url) !== -1)
			{
				return
			}

			css.push(url)

		})

		assets.js.forEach(url => {

			if (availableJS.indexOf(url) !== -1)
			{
				return
			}

			js.push(url)

		})

		return { css: css, js: js }
	}

	/**
	 * Updates the document assets then calls a callback function.
	 *
	 * @param {{css: Array<string>, js: Array<string>}} assets
	 * @param {Function} resolved An optional callback to call once the required assets have been loaded.
	 * @param {Function} [rejected]
	 */
	function updateAssets(assets, resolved, rejected)
	{
		const filtered = filterMissing(assets)
		const promises = []

		if (filtered.css.length)
		{
			promises.push(StyleSheetPromises.all(filtered.css))
		}

		if (filtered.js.length)
		{
			promises.push(JavaScriptPromises.all(filtered.js))
		}

		if (!promises.length)
		{
			resolved()
			return
		}

		Promise.all(promises).then(resolved).catch(rejected || (rejected => {

			console.error("The following promise were rejected:", rejected)

		}))
	}

	Brickrouge.updateAssets = updateAssets

})
