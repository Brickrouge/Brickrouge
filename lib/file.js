
Brickrouge.Widget.File = new Class
({
	Implements: [ Chain, Options, Events ],

	options:
	{
		uploadUrl: null,
		maxFileSize: 2 * 1024 * 1024,
		name: 'Filedata'
	},

	initialize: function(el, options)
	{
		this.element = $(el);
		this.setOptions(options);
		this.trigger = this.element.getElement('input[type=file]');
		this.trigger.addEvent('change', this.onChange.bind(this));
	},

	onChange: function(ev)
	{
		var files = ev.target.files
		, file

		if (!files.length || !this.options.uploadUrl) return

		file = files[0];

		if (file.size > this.options.maxFileSize)
		{
			this.element.getElement('.alert-error').innerHTML = "Le fichier sélectionné est trop volumineux."
			this.element.addClass('has-error')

			return
		}

		this.start()
		this.upload(files[0])
	},

	upload: function(file)
	{
		var xhr = this.xhr = new XMLHttpRequest();
		var self = this;

		xhr.onreadystatechange = function(ev)
		{
			if (this.readyState != XMLHttpRequest.DONE)
			{
				return;
			}

			var response = null;

			if (this.status == 200)
			{
				response = JSON.parse(this.responseText);

//				console.log('%a- transfer complete with the following response: %a', ev, response);

				var reminder = self.element.getElement('.reminder');

				if (reminder && response.file)
				{
					reminder.setAttribute('value', response.file.location);
				}

				var el = self.element;

				if (response.infos)
				{
					var infosTarget = el.getElement('div.infos');

					if (infosTarget)
					{
						infosTarget.innerHTML = response.infos;
						el.addClass('has-info');
					}
				}

				self.success(response);
			}
			else if (this.status >= 400)
			{
				response = JSON.parse(this.responseText);
				var el = self.element
				, alertMessage='';

				if (response.errors)
				{
					Object.each(response.errors, function(message) {
						alertMessage += '<p>' + message + '</p>';
					})
				}

				el.getElement('.alert-error').innerHTML = alertMessage;
				el.addClass('has-error');

				el.removeClass('has-info');
				el.getElement('div.infos').innerHTML = '';

				el.getElement('input.reminder').removeAttribute('value');

				//console.log('%a- readyState: %d, status: %d, response: %s', ev, this.readyState, this.status, this.responseText);

				self.failure(response);
			}
		};

		// http://dev.w3.org/2006/webapi/progress/#interface-progressevent

		var	fileUpload = xhr.upload;

		fileUpload.onprogress = this.onProgress.bind(this);
		fileUpload.onload = this.onProgress.bind(this);

		xhr.open("POST", this.options.uploadUrl);

		xhr.setRequestHeader('Accept', 'application/json');
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.setRequestHeader('X-Request', 'JSON');
		xhr.setRequestHeader('X-Using-File-API', true);

		var fd = new FormData();

		fd.append(this.options.name, file);

		xhr.send(fd);
	},

	cancel: function()
	{
		if (this.xhr)
		{
			this.xhr.abort();
			delete this.xhr;
			this.xhr = null;
		}

		this.complete();
	},

	start: function()
	{
		var el = this.element;

		if (!this.positionTween)
		{
			this.positionElement = el.getElement('.progress .position');
			this.positionLabelElement = this.positionElement.getElement('.label');
			this.positionTween = new Fx.Tween(this.positionElement, { property: 'width', link: 'cancel', unit: '%', duration: 'short' });
			this.cancelElement = el.getElement('button.cancel');

			this.cancelElement.addEvent('click', this.cancel.bind(this));
		}

		this.positionTween.set(0);
		el.addClass('uploading');

		el.removeClass('has-info');
		el.getElement('div.infos').innerHTML = '';

		el.removeClass('has-error');
	},

	complete: function(response)
	{
		this.element.removeClass('uploading');

		if (response)
		{
			this.fireEvent('change', response);
		}
	},

	onProgress: function(ev)
	{
		if (!ev.lengthComputable)
		{
			return;
		}

		var position = 100 * ev.loaded / ev.total;

		this.positionTween.set(position);
		this.positionLabelElement.innerHTML = Math.round(position) + '%';
	},

	success: function(response)
	{
		this.onSuccess(response);
	},

	onSuccess: function()
	{
		this.complete();
		this.fireEvent('complete', arguments).fireEvent('success', arguments).callChain();
	},

	failure: function(response)
	{
		this.cancel()
	}

});