/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Animates a carousel.
 */
Brickrouge.Carousel = new Class({

	Implements: [ Options, Events ],

	options: {

		autoplay: false,
		delay: 6000,
		method: 'fade'
	},

	initialize: function(el, options)
	{
		this.element = $(el)
		this.setOptions(options)
		this.inner = el.getElement('.carousel-inner')
		this.slides = this.inner.getChildren()
		this.position = 0
		this.limit = this.slides.length
		this.timer = null

		if (this.options.method)
		{
			this.setMethod(this.options.method)

			if (this.method.initialize)
			{
				this.method.initialize.apply(this)
			}
		}

		this.element.addEvents({

			'click:relay(.carousel-control.left)': function(ev) {

				ev.stop()
				this.prev()

			}.bind(this),

			'click:relay(.carousel-control.right)': function(ev) {

				ev.stop()
				this.next()

			}.bind(this),

			mouseenter: this.pause.bind(this),
			mouseleave: this.resume.bind(this)

		})

		this.resume()
	},

	setMethod: function(method)
	{
		if (typeOf(method) == 'string')
		{
			method = Brickrouge.Carousel.Methods[method]
		}

		this.method = method

		if (method.next) this.next = method.next
		if (method.prev) this.prev = method.prev
	},

	play: function()
	{
		if (this.timer) return

		this.timer = (function() {

			this.setPosition(this.position + 1)

		}).periodical(this.options.delay, this)

		this.fireEvent('play', { position: this.position, slide: this.slides[this.position] })
	},

	pause: function()
	{
		if (!this.timer) return

		clearInterval(this.timer)
		this.timer = null

		this.fireEvent('pause', { position: this.position, slide: this.slides[this.position] })
	},

	resume: function()
	{
		if (!this.options.autoplay) return

		this.play()
	},

	setPosition: function(position)
	{
		position = position % this.limit

		if (position == this.position) return

		this.method.go.apply(this, [ position ])

		this.fireEvent('position', { position: this.position, slide: this.slides[this.position] })
	},

	prev: function()
	{
		this.setPosition(this.position ? this.position - 1 : this.limit - 1)
	},

	next: function()
	{
		this.setPosition(this.position == this.limit ? 0 : this.position + 1)
	}
})

/**
 * Carousel methods.
 */
Brickrouge.Carousel.Methods = {

	fade: {

		initialize: function()
		{
			this.slides.each(function(slide, i) {

				slide.setStyles({

					left: 0,
					top: 0,
					position: 'absolute',
					opacity: i ? 0 : 1,
					visibility: i ? 'hidden' : 'visible',
				})
			})
		},

		go: function(position)
		{
			var slideOut = this.slides[this.position]
			, slideIn = this.slides[position]

			slideIn.setStyles({ opacity: 0, visibility: 'visible' }).inject(slideOut, 'after').fade('in')

			this.position = position
		}
	},

	columns: {

		initialize: function()
		{
			this.working = false
			this.fitting = 0
			this.childWidth = 0

			var offset = 0
			, totalWidth = 0
			, width = 0
			, visible_w = this.element.getSize().x

			this.view = new Element
			(
				'div',
				{
					'styles':
					{
						position: 'absolute',
						top: 0,
						left: 0,
						height: this.element.getStyle('height'),
					}
				}
			);

			this.view.adopt(this.slides);
			this.view.inject(this.inner);
			this.view.set('tween', { property: 'left' });

			this.slides.each
			(
				function(el)
				{
					if (el.get('data-url'))
					{
						el.setStyle('cursor', 'pointer')
					}

					var w = el.getSize().x + el.getStyle('margin-left').toInt() + el.getStyle('margin-right').toInt()

					el.setStyles
					({
						'position': 'absolute',
						'top': 0,
						'left': offset
					})

					offset += w
					totalWidth += w
					width = Math.max(width, w)
				},

				this
			);

			this.childWidth = width
			this.fitting = (visible_w / width).floor()
			this.view.setStyle('width', totalWidth)
		},

		go: function(position)
		{
			var n = this.limit
			, diff = this.position - position
			, to_uncover = null
			, left = 0

			if (this.working)
			{
				return;
			}

			this.working = true;

//				console.log('request position: %d (current: %d), diff: %d (count: %d)', position, this.position, diff, n);

			to_uncover = (diff < 0) ? this.position + this.fitting : this.position - diff

			if (to_uncover < 0)
			{
//					console.log('uncover out of range %d (%d)', to_uncover, n);

				to_uncover = n + to_uncover
			}
			else if (to_uncover > n - 1)
			{
//					console.log('uncover out of range %d (%d)', to_uncover, n);

				to_uncover = to_uncover - n
			}

			if (position < 0)
			{
				position = n - diff
			}
			else
			{
				position = position % n
			}

			this.position = position

//				console.log('final position: %d (%d), final uncover: %d', position, this.position, to_uncover);

			left = diff < 0 ? this.childWidth * this.fitting : -this.childWidth

//				console.log('left: ', left);

			this.slides[to_uncover].setStyle('left', left)

			this.view.get('tween').start(this.childWidth * diff).chain
			(
				function()
				{
					var i = position
					, offset = 0
					, w = this.childWidth

					for ( ; i < n ; i++, offset += w)
					{
						this.slides[i].setStyle('left', offset)
					}

					for (i = 0 ; i < position ; i++, offset += w)
					{
						this.slides[i].setStyle('left', offset);
					}

					this.view.setStyle('left', 0);

					this.working = false;
				}
				.bind(this)
			);
		},

		next: function()
		{
			this.setPosition(this.position + 1)
		},

		prev: function()
		{
			this.setPosition(this.position - 1)
		}
	}
}
