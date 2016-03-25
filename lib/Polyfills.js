!function (proto) {

	/**
	 * https://developer.mozilla.org/en-US/docs/Web/API/Element/closest
	 */
	proto.closest = proto.closest || function (selectors) {

		var element = this

		while (element) {
			if (element.matches(selectors)) {
				return element
			}

			element = element.parentElement
		}

		return null
	}

	/**
	 * https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/remove
	 */
	proto.remove = proto.remove || function () {

		if (this.parentNode) {
			this.parentNode.removeChild(this)
		}

	}

	proto.addDelegatedEventListener = function(selectors, type, listener, useCapture) {

		this.addEventListener(type, function(ev) {

			if (!ev.target.match(selectors)) {
				return null
			}

			listener(ev, ev.target)

		}, useCapture)

	}

} (Element.prototype)
