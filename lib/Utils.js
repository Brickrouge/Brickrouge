define([

	'./Core',
	'olvlvl-mixin'

], function (Brickrouge, mixin) {

	"use strict";

	Brickrouge.mixin = mixin

	function Busy () {

		this.busyNest = 0

	}

	Busy.prototype.startBusy = function () {

		if (++this.busyNest == 1) return

		this.element.classList.add('busy')

	}

	Busy.prototype.finishBusy = function () {

		if (--this.busyNest) return

		this.element.classList.remove('busy')

	}

	Brickrouge.Utils = {

		Busy: Busy
	}

})
