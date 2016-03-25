!function (Brickrouge) {

	Brickrouge.Widget.File = new Class({

		Implements: [ Chain, Options, Events ],

		options:
		{
			uploadUrl: null,
			maxFileSize: 2 * 1024 * 1024,
			name: 'Filedata'
		},

		initialize: function(el, options)
		{
			this.element = el = document.id(el)
			this.setOptions(options)
			this.positionTween = null

			var trigger = el.getElement('.trigger')
			, control = trigger.getElement('input[type="file"]')

			if (Browser.name == 'firefox' && Browser.version < 22)
			{
				trigger.addEvent('click', function(ev) {

					if (ev.target != trigger) return

					control.click()

				})
			}

			control.addEvent('change', this.onChange.bind(this))

			function fileDragHover(ev)
			{
				ev.preventDefault()

				el.classList[ev.type == "dragover" ? 'add' : 'remove']('dnd-hover')
			}

			function fileSelectHandler(ev) {

				ev.stopPropagation()
				ev.preventDefault()

				el.classList.remove('dnd-hover')

				var files = ev.target.files || ev.dataTransfer.files

				if (!this.check(files)) return

				this.start()
				this.upload(files[0])
			}

			el.addEventListener('dragover', fileDragHover, false)
			el.addEventListener('dragleave', fileDragHover, false)
			el.addEventListener('drop', fileSelectHandler.bind(this), false);
		},

		onChange: function(ev)
		{
			var files = ev.target.files

			if (!this.check(files)) return

			this.start()
			this.upload(files[0])
		},

		check: function(files)
		{
			var file

			if (!files.length || !this.options.uploadUrl) return null

			file = files[0]

			if (file.size > this.options.maxFileSize)
			{
				this.element.getElement('.alert-danger').innerHTML = "Le fichier sélectionné est trop volumineux."
				this.element.classList.add('has-danger')

				return false
			}

			return true
		},

		upload: function(file)
		{
			var xhr = this.xhr = new XMLHttpRequest()
			, fileUpload = xhr.upload
			, fd = new FormData()

			fileUpload.onprogress = this.onProgress.bind(this)
			fileUpload.onload = this.onProgress.bind(this)
			xhr.onreadystatechange = this.onReadyStateChange.bind(this)

			fd.append(this.options.name, file, file.name)

			xhr.open("POST", this.options.uploadUrl)

			xhr.setRequestHeader('Accept', 'application/json')
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
			xhr.setRequestHeader('X-Request', 'JSON')
			xhr.setRequestHeader('X-Using-File-API', true)

			this.prepare(xhr, file, fd)

			xhr.send(fd)
		},

		prepare: function(xhr, file, data)
		{
			this.onPrepare({ xhr: xhr, file: file, data: data })
		},

		onPrepare: function(ev)
		{
			this.fireEvent('prepare', arguments)
		},

		cancel: function()
		{
			if (this.xhr)
			{
				this.xhr.abort()
				delete this.xhr
				this.xhr = null
			}

			this.complete()
		},

		start: function()
		{
			var el = this.element

			if (!this.positionTween)
			{
				this.positionElement = el.getElement('.progress .position')
				this.positionLabelElement = this.positionElement.getElement('.text')
				this.positionTween = new Fx.Tween(this.positionElement, { property: 'width', link: 'cancel', unit: '%', duration: 'short' })
				this.cancelElement = el.getElement('.cancel')
				this.cancelElement.addEvent('click', this.cancel.bind(this))
			}

			this.positionTween.set(0)

			el.classList.add('uploading')
			el.classList.remove('has-info')
			el.classList.remove('has-danger')

			el.getElement('div.infos').innerHTML = ''
		},

		onReadyStateChange: function(ev)
		{
			var xhr = ev.target

			if (xhr.readyState != XMLHttpRequest.DONE)
			{
				return
			}

			if (xhr.status == 200)
			{
				this.success(ev)
			}
			else if (xhr.status >= 400)
			{
				this.failure(ev)
			}
		},

		onProgress: function(ev)
		{
			if (!ev.lengthComputable) return

			var position = 100 * ev.loaded / ev.total

			this.positionTween.set(position)
			this.positionLabelElement.innerHTML = Math.round(position) + '%'
		},

		complete: function(response)
		{
			this.element.classList.remove('uploading')

			if (response)
			{
				this.fireEvent('change', response)
			}
		},

		success: function(ev)
		{
			var response = JSON.parse(ev.target.responseText)
			, el = this.element
			, reminder = el.getElement('.reminder')
			, infosTarget = el.getElement('.infos')

	//		console.log('%a- transfer complete with the following response: %a', ev, response)

			if (reminder && response.rc)
			{
				reminder.setAttribute('value', response.rc.pathname)
			}

			if (response.infos && infosTarget)
			{
				infosTarget.innerHTML = response.infos
				el.classList.add('has-info')
			}
			else
			{
				el.classList.add('has-info')
			}

			this.onSuccess(response, ev.target)
		},

		onSuccess: function(response, xhr)
		{
			this.complete(response, xhr)
			this.fireEvent('complete', arguments).fireEvent('success', arguments).callChain()
		},

		failure: function(ev)
		{
			var response = JSON.parse(ev.target.responseText)
			, el = this.element
			, alertMessage = ''

			if (response.errors)
			{
				Object.each(response.errors, function(message) {
					alertMessage += '<p>' + message + '</p>';
				})
			}

			el.getElement('.alert-danger').innerHTML = alertMessage
			el.classList.add('has-danger')

			el.classList.remove('has-info')
			el.getElement('div.infos').innerHTML = ''

			el.getElement('input.reminder').removeAttribute('value')

			this.onFailure(response, ev.target)
		},

		onFailure: function(response, xhr)
		{
			this.complete(response, xhr)
			this.fireEvent('complete', arguments).fireEvent('failure', arguments).callChain()
		}
	})

	Brickrouge.register('File', function (element, options) {

		return new Brickrouge.Widget.File(element, options)

	})

} (Brickrouge);
