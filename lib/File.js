!function (Brickrouge) {

	var Subject = Brickrouge.Subject

	/**
	 * @param {XMLHttpRequest} xhr
	 * @param {File} file
	 * @param {object} data
	 *
	 * @event Brickrouge.File#prepare
	 * @property {XMLHttpRequest} xhr
	 * @property {File} file
	 * @property {object} data
	 */
	var PrepareEvent = Subject.createEvent('prepare', function (xhr, file, data) {

		this.xhr = xhr
		this.file = file
		this.data = data

	})

	/**
	 * @param {object} response
	 *
	 * @event Brickrouge.File#change
	 * @property {object} response
	 */
	var ChangeEvent = Subject.createEvent('change', function (response) {

		this.response = response

	})

	/**
	 * @param {object} response
	 * @param {XMLHttpRequest} xhr
	 *
	 * @event Brickrouge.File#complete
	 * @property {object} response
	 * @property {XMLHttpRequest} xhr
	 */
	var CompleteEvent = Subject.createEvent('complete', function (response, xhr) {

		this.response = response
		this.xhr = xhr

	})

	/**
	 * @param {object} response
	 * @param {XMLHttpRequest} xhr
	 *
	 * @event Brickrouge.File#failure
	 * @property {object} response
	 * @property {XMLHttpRequest} xhr
	 */
	var FailureEvent = Subject.createEvent('failure', function (response, xhr) {

		this.response = response
		this.xhr = xhr

	})

	/**
	 * @param {object} response
	 * @param {XMLHttpRequest} xhr
	 *
	 * @event Brickrouge.File#success
	 * @property {object} response
	 * @property {XMLHttpRequest} xhr
	 */
	var SuccessEvent = Subject.createEvent('success', function (response, xhr) {

		this.response = response
		this.xhr = xhr

	})

	var File = new Class({

		Implements: [ Chain, Options, Subject ],

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

			var trigger = el.querySelector('.trigger')
			, control = trigger.querySelector('input[type="file"]')

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
				this.element.querySelector('.alert-danger').innerHTML = "Le fichier sélectionné est trop volumineux."
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
			this.notify(new PrepareEvent(xhr, file, data))
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
				this.positionElement = el.querySelector('.progress .position')
				this.positionLabelElement = this.positionElement.querySelector('.text')
				this.positionTween = new Fx.Tween(this.positionElement, { property: 'width', link: 'cancel', unit: '%', duration: 'short' })
				this.cancelElement = el.querySelector('.cancel')
				this.cancelElement.addEvent('click', this.cancel.bind(this))
			}

			this.positionTween.set(0)

			el.classList.add('uploading')
			el.classList.remove('has-info')
			el.classList.remove('has-danger')

			this.clearInfo()
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

		/**
		 * @param {object} response
		 *
		 * @fires Brickrouge.File#change
		 */
		complete: function(response)
		{
			this.element.classList.remove('uploading')

			if (response)
			{
				this.notify(new ChangeEvent(response))
			}
		},

		success: function(ev)
		{
			var response = JSON.parse(ev.target.responseText)
			, el = this.element
			, reminder = el.querySelector('.reminder')
			, infosTarget = el.querySelector('.infos')

			if (reminder && response.rc.pathname)
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
				el.classList.remove('has-info')
			}

			this.onSuccess(response, ev.target)
		},

		/**
		 * @param {object} response
		 * @param {XMLHttpRequest} xhr
		 *
		 * @fires Brickrouge.File#success
		 * @fires Brickrouge.File#complete
		 */
		onSuccess: function(response, xhr)
		{
			this.complete(response, xhr)
			this.notify(new SuccessEvent(response, xhr)).notify(new CompleteEvent(response, xhr))
		},

		failure: function(ev)
		{
			var response = JSON.parse(ev.target.responseText)
			, el = this.element
			, alertMessage = ''

			if (response.errors)
			{
				Object.forEach(response.errors, function(message) {
					alertMessage += '<p>' + message + '</p>';
				})
			}

			el.querySelector('.alert-danger').innerHTML = alertMessage
			el.classList.add('has-danger')

			this.clearInfo()

			el.querySelector('input.reminder').removeAttribute('value')

			this.onFailure(response, ev.target)
		},

		/**
		 * @param {object} response
		 * @param {XMLHttpRequest} xhr
		 *
		 * @fires Brickrouge.File#failure
		 * @fires Brickrouge.File#complete
		 */
		onFailure: function(response, xhr)
		{
			this.complete(response, xhr)
			this.notify(new FailureEvent(response, xhr)).notify(new CompleteEvent(response, xhr))
		},

		clearInfo: function ()
		{
			var info = this.element.querySelector('div.infos')

			this.element.classList.remove('has-info')

			if (!info) return

			Brickrouge.empty(info)
		}
	})

	Object.defineProperties(File, {

		EVENT_PREPARE:  { value: PrepareEvent },
		EVENT_CHANGE:   { value: ChangeEvent },
		EVENT_COMPLETE: { value: CompleteEvent },
		EVENT_FAILURE:  { value: FailureEvent },
		EVENT_SUCCESS:  { value: SuccessEvent }

	})

	Brickrouge.register('File', function (element, options) {

		return new File(element, options)

	})

	Brickrouge.Widget.File = File

} (Brickrouge);
