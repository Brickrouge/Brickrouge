define([

	'./Core'

], function (Brickrouge) {
	"use strict";

	/**
	 * @constructor
	 */
	function Viewport() {}

	Viewport.prototype.getCoordinates = function () {

		let left = document.documentElement.scrollLeft || window.pageXOffset
		let top = document.documentElement.scrollTop || window.pageYOffset
		let width = document.documentElement.clientWidth
		let height = document.documentElement.clientHeight

		return {

			left: left,
			top: top,
			width: width,
			height: height,
			x1: left,
			y1: top,
			x2: left + width - 1,
			y2: top + height - 1

		}
	}

	Viewport.prototype.isElementVisible = function (element) {

		let vpCoordinates = this.getCoordinates()
		let elCoordinates = element.getCoordinates()
		let vy1 = vpCoordinates.y1
		let vy2 = vpCoordinates.y2
		let ey = elCoordinates.top
		let eh = elCoordinates.height
		let ey2 = ey + eh - 1

		return (ey >= vy1 && ey < vy2 ) || (ey < vy1 && ey2 > vy1)
	}

	Brickrouge.viewport = new Viewport

	return Viewport

})
