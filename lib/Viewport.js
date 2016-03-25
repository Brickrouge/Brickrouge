define([

	'./Core'

], function (Brickrouge) {
	"use strict";

	/**
	 * @constructor
	 */
	function Viewport() {}

	Viewport.prototype.getCoordinates = function () {

		var left = document.documentElement.scrollLeft || window.pageXOffset
		, top = document.documentElement.scrollTop || window.pageYOffset
		, width = document.documentElement.clientWidth
		, height = document.documentElement.clientHeight

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

		var vpCoordinates = this.getCoordinates()
		, elCoordinates = element.getCoordinates()
		, vy1 = vpCoordinates.y1
		, vy2 = vpCoordinates.y2
		, ey = elCoordinates.top
		, eh = elCoordinates.height
		, ey2 = ey + eh - 1

		return (ey >= vy1 && ey < vy2 ) || (ey < vy1 && ey2 > vy1)
	}

	Brickrouge.viewport = new Viewport

	return Viewport

})
