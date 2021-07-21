!function (proto) {

    /**
     * https://developer.mozilla.org/en-US/docs/Web/API/Element/closest
     */
    proto.closest = proto.closest || function (selectors) {

        let element = this

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

    /**
     * https://developer.mozilla.org/en/docs/Web/API/Element/matches
     */
    proto.matches = proto.matches || proto.matchesSelector || proto.webkitMatchesSelector
        || proto.msMatchesSelector || proto.oMatchesSelector

    if (!proto.matches) {
        throw new Error("Unable to implement Element.prototype.matches")
    }

} (Element.prototype)
