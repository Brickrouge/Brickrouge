BrickRouge.Popover = new Class({

	Implements: [ Events, Options ],

	options:
	{
		anchor: null,
		position: null
	},

	initialize: function(el, options)
	{
		this.element = $(el);
		this.setOptions(options);
		this.arrow = this.element.getElement('.arrow');
		this.actions = this.element.getElement('div.actions');
		this.repositionCallback = this.reposition.bind(this, false);
		this.quickRepositionCallback = this.reposition.bind(this, true);

		var visible = !this.element.hasClass('invisible');

		this.element.addClass('invisible');

		this.iframe = null;

		if (this.options.anchor)
		{
			this.attachAnchor(this.options.anchor);
		}

		this.element.addEvent('click', this.onClick.bind(this));

		if (visible)
		{
			this.open();
		}
	},

	attachAnchor: function(anchor)
	{
		this.anchor = $(anchor);

		if (!this.anchor)
		{
			this.anchor = $(document.body).getElement(anchor);
		}
	},

	onClick: function(ev)
	{
		var target = ev.target;

		if (target.tagName == 'BUTTON' && target.getParent('div.actions'))
		{
			this.fireAction({ action: target.get('data-action'), popover: this, ev: ev });
		}
	},

	fireAction: function(params)
	{
		this.fireEvent('action', arguments);
	},

	changePositionClass: function(position)
	{
		this.element.removeClass('before');
		this.element.removeClass('after');
		this.element.removeClass('above');
		this.element.removeClass('below');

		this.element.addClass(position);
	},

	open: function()
	{
		this.element.addClass('invisible');

		window.addEvents
		({
			'load': this.quickRepositionCallback,
			'resize': this.quickRepositionCallback,
			'scroll': this.repositionCallback
		});

		if (this.iframe)
		{
			$(this.iframe.contentWindow).addEvents
			({
				'load': this.quickRepositionCallback,
				'resize': this.quickRepositionCallback,
				'scroll': this.repositionCallback
			});
		}

		this.reposition(true);

		this.element.removeClass('invisible');
	},

	close: function()
	{
		this.element.addClass('invisible');
		this.element.dispose();

		window.removeEvent('load', this.quickRepositionCallback);
		window.removeEvent('resize', this.quickRepositionCallback);
		window.removeEvent('scroll', this.repositionCallback);

		if (this.iframe)
		{
			var contentWindow = $(this.iframe.contentWindow);

			contentWindow.removeEvent('load', this.quickRepositionCallback);
			contentWindow.removeEvent('resize', this.quickRepositionCallback);
			contentWindow.removeEvent('scroll', this.repositionCallback);
		}
	},

	computeAnchorBox: function()
	{
		var anchor = this.anchor, anchorCoords, iframe = this.iframe, iframeCoords,
		iHTML, visibleH, visibleW, hiddenTop, hiddenLeft;

		if (iframe)
		{
			iframeCoords = iframe.getCoordinates();
			iHTML = iframe.contentDocument.documentElement;

			aX = anchor.offsetLeft;
			aY = anchor.offsetTop;
			aW = anchor.offsetWidth;
			aH = anchor.offsetHeight;

			visibleH = iHTML.clientHeight;
			hiddenTop = iHTML.scrollTop;

			aY -= hiddenTop;

			if (aY < 0)
			{
				aH += aY;
			}

			aY = Math.max(aY, 0);
			aH = Math.min(aH, visibleH);

			visibleW = iHTML.clientWidth;
			hiddenLeft = iHTML.scrollLeft;

			aX -= hiddenLeft;

			if (aX < 0)
			{
				aW += aX;
			}

			aX = Math.max(aX, 0);
			aW = Math.min(aW, visibleW);

			aX += iframeCoords.left;
			aY += iframeCoords.top;
		}
		else
		{
			anchorCoords = anchor.getCoordinates();

			aX = anchorCoords.left;
			aY = anchorCoords.top;
			aH = anchorCoords.height;
			aW = anchorCoords.width;
		}

		return { x: aX, y: aY, w: aW, h: aH };
	},

	computeBestPosition: function(anchorBox, w, h)
	{
		var html = document.body.parentNode,
		bodyCompleteH = html.scrollHeight,
		bodyCompleteW = html.scrollWidth,
		aX = anchorBox.x,
		aY = anchorBox.y,
		aW = anchorBox.w,
		aH = anchorBox.h,
		max = aX + 1,
		position = 'before',
		size;

		size = bodyCompleteW - aX - aW + 1;

		if (size > max)
		{
			position = 'after';
			max = size;
		}

		size = aY + 1;

		if (size > max)
		{
			position = 'above';
			max = size;
		}

		size = bodyCompleteH - aY - aH + 1;

		if (size > max)
		{
			position = 'below';
		}

		return position;
	},

	reposition: function(quick)
	{
		if (!this.anchor)
		{
			return;
		}

		var pad = 20, actions = this.actions,
		anchorBox, aX, aY, aW, aH, anchorMiddleX, anchorMiddleY,
		size = this.element.getSize(), w = size.x , h = size.y, x, y,
		position,
		body = document.id(document.body),
		bodySize = body.getSize(),
		bodyScroll = body.getScroll(),
		bodyX = bodyScroll.x,
		bodyY = bodyScroll.y,
		bodyW = bodySize.x,
		bodyH = bodySize.y,
		arrowTransform = { top: null, left: null }, arX, arY;

		if (quick === undefined)
		{
			quick = this.element.getStyle('visibility') != 'visible';
		}

		anchorBox = this.computeAnchorBox();
		aX = anchorBox.x;
		aY = anchorBox.y;
		aW = anchorBox.w;
		aH = anchorBox.h;
		anchorMiddleX = aX + aW / 2 - 1;
		anchorMiddleY = aY + aH / 2 - 1;

		position = this.options.position || this.computeBestPosition(anchorBox, w, h);

		this.changePositionClass(position);

		if (position == 'before' || position == 'after')
		{
			y = Math.round(aY + (aH - h) / 2 - 1);
			x = (position == 'before') ? aX - w + 1 : aX + aW - 1;

			//
			// limit 'x' and 'y' to the limits of the document incuding a padding value.
			//

			x = x.limit(bodyX + pad - 1, bodyX + bodyW - (w + pad) - 1);
			y = y.limit(bodyY + pad - 1, bodyY + bodyH - (h + pad) - 1);

			//
			// adjust arrow
			//

			arY = (aY + aH / 2 - 1) - y;

			arY = Math.min(h - (actions ? actions.getSize().y : 20) - 10, arY);
			arY = Math.max(50, arY);

			// adjust element Y so that the arrow is always centered on the anchor visible height

			if (arY + y - 1 != anchorMiddleY)
			{
				y -= (y + arY) - anchorMiddleY;
			}

			arrowTransform.top = arY;
		}
		else
		{
			x = Math.round(aX + (aW - w) / 2 - 1);
			y = (position == 'above') ? aY - h + 1 : aY + aH - 1;

			//
			// limit 'x' and 'y' to the limits of the document incuding a padding value.
			//

			x = x.limit(bodyX + pad - 1, bodyX + bodyW - (w + pad) - 1);
			//y = y.limit(bodyY + pad, bodyY + bodyH - (h + pad));

			//
			// adjust arrow
			//

			arX = ((aX + aW / 2 - 1) - x).limit(pad, w - pad);

			// adjust element X so that the arrow is always centered on the anchor visible width

			if (arX + w - 1 != anchorMiddleX)
			{
				x -= (x + arX) - anchorMiddleX;
			}

			arrowTransform.left = arX;
		}

		if (quick)
		{
			this.element.setStyles({ left: x, top: y });
			this.arrow.setStyles(arrowTransform);
		}
		else
		{
			this.element.morph({ left: x, top: y });
			this.arrow.morph(arrowTransform);
		}
	}
});

/**
 * Creates a popover element using the provided options.
 *
 * @param options
 *
 * @returns {BrickRouge.Popover}
 */
BrickRouge.Popover.from = function(options)
{
	var title = options.title,
	content = options.content,
	direction = options.direction || 'auto',
	actions = options.actions,
	inner = new Element('div.inner'),
	popover;

	if (title)
	{
		inner.adopt(new Element('h3.title', { 'html': title }));
	}

	inner.adopt(new Element('div.content').adopt(content));

	if (actions == 'boolean')
	{
		actions = [ new Element('button.cancel[data-action="cancel"]', { html: 'Cancel' }), new Element('button.primary[data-action="ok"]', { html: 'Ok' }) ];
	}

	if (actions)
	{
		inner.adopt(new Element('div.actions').adopt(actions));
	}

	popover = new Element('div.popover.' + direction).adopt([ new Element('div.arrow'), inner ]);

	return new BrickRouge.Popover(popover, options);
};

BrickRouge.Widget.Popover = BrickRouge.Popover;