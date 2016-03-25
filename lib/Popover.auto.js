define([

	'./Core',
	'./Popover'

], function (Brickrouge) {

	"use strict";

	var instances = []

	function retrieve(target)
	{
		return instances[Brickrouge.uidOf(target)]
	}

	function from(target)
	{
		var uid = Brickrouge.uidOf(target)
			, options

		if (uid in instances)
		{
			return instances[uid]
		}

		options = Brickrouge.Dataset.from(target)
		options.anchor = target

		return instances[uid] = Brickrouge.Popover.from(options)
	}

	/**
	 * Event delegation for elements with a `rel="popover"` attribute.
	 */
	document.body.addDelegatedEventListener('[rel="popover"]', 'mouseover', function(ev, target) {

		from(target).show()

	})

	document.body.addDelegatedEventListener('[rel="popover"]', 'mouseout', function(ev, target) {

		var popover = retrieve(target)

		if (!popover) return

		popover.hide()

	})

})
