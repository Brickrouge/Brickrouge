!function (Brickrouge) {

	const Subject = Brickrouge.Subject

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
	const PrepareEvent = Subject.createEvent(function (xhr, file, data) {

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
	const ChangeEvent = Subject.createEvent(function (response) {

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
	const CompleteEvent = Subject.createEvent(function (response, xhr) {

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
	const FailureEvent = Subject.createEvent(function (response, xhr) {

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
	const SuccessEvent = Subject.createEvent(function (response, xhr) {

		this.response = response
		this.xhr = xhr

	})

	/**
	 * @property {string|null} uploadUrl
	 * @property {number} maxFileSize
	 * @property {string} name
	 */
	const DEFAULT_OPTIONS = {

		uploadUrl: null,
		maxFileSize: 2 * 1024 * 1024,
		name: 'Filedata'

	}

	class FileElement extends Brickrouge.mixin(Object, Subject)
	{
		constructor(el, options)
		{
			super()

			this.element = el
			this.options = Object.assign({}, DEFAULT_OPTIONS, options)
			this.positionTween = null

			const trigger = el.querySelector('.trigger')
			const control = trigger.querySelector('input[type="file"]')

			if (Browser.name == 'firefox' && Browser.version < 22)
			{
				trigger.addEventListener('click', ev => {

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

			/**
			 * @param {drag} ev
			 */
			function fileSelectHandler(ev) {

				ev.stopPropagation()
				ev.preventDefault()

				el.classList.remove('dnd-hover')

				const files = ev.target.files || ev.dataTransfer.files

				if (!this.check(files)) return

				this.start()
				this.upload(files[0])
			}

			el.addEventListener('dragover', fileDragHover, false)
			el.addEventListener('dragleave', fileDragHover, false)
			el.addEventListener('drop', fileSelectHandler.bind(this), false)
		}

		/**
		 * @param {Event} ev
		 */
		onChange(ev)
		{
			const files = ev.target.files

			if (!this.check(files)) return

			this.start()
			this.upload(files[0])
		}

		/**
		 * @param {FileList} files
		 *
		 * @returns {boolean|null}
		 */
		check(files)
		{
			if (!files.length || !this.options.uploadUrl)
			{
				return null
			}

			const file = files[0]

			if (file.size > this.options.maxFileSize)
			{
				this.element.querySelector('.alert-danger').innerHTML = "Le fichier sélectionné est trop volumineux."
				this.element.classList.add('has-danger')

				return false
			}

			return true
		}

		/**
		 * @param {File} file
		 */
		upload(file)
		{
			const xhr = this.xhr = new XMLHttpRequest()
			const fileUpload = xhr.upload
			const fd = new FormData()

			fileUpload.onprogress = this.onProgress.bind(this)
			fileUpload.onload = this.onProgress.bind(this)
			xhr.onreadystatechange = this.onReadyStateChange.bind(this)

			fd.append(this.options.name, file, file.name)

			xhr.open("POST", this.options.uploadUrl)

			xhr.setRequestHeader('Accept', 'application/json')
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
			xhr.setRequestHeader('X-Request', 'JSON')
			xhr.setRequestHeader('X-Using-File-API', 'yes')

			this.prepare(xhr, file, fd)

			xhr.send(fd)
		}

		prepare(xhr, file, data)
		{
			this.notify(new PrepareEvent(xhr, file, data))
		}

		cancel()
		{
			if (this.xhr)
			{
				this.xhr.abort()
				delete this.xhr
				this.xhr = null
			}

			this.complete()
		}

		start()
		{
			const el = this.element

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
		}

		/**
		 * @param {Event} ev
		 */
		onReadyStateChange(ev)
		{
			const xhr = ev.target

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
		}

		/**
		 * @param {ProgressEvent} ev
		 */
		onProgress(ev)
		{
			if (!ev.lengthComputable) return

			const position = 100 * ev.loaded / ev.total

			this.positionTween.set(position)
			this.positionLabelElement.innerHTML = Math.round(position) + '%'
		}

		/**
		 * @param {object} [response]
		 *
		 * @fires Brickrouge.File#change
		 */
		complete(response)
		{
			this.element.classList.remove('uploading')

			if (response)
			{
				this.notify(new ChangeEvent(response))
			}
		}

		/**
		 * @param {Event} ev
		 */
		success(ev)
		{
			/**
			 * @type {XMLHttpRequest|EventTarget}
			 */
			const target = ev.target

			/**
			 * @property {{pathname: string}} rc
			 * @property {string} infos
			 */
			const response = JSON.parse(target.responseText)
			const el = this.element
			const reminder = el.querySelector('.reminder')
			const infoTarget = el.querySelector('.infos')

			if (reminder && response.rc.pathname)
			{
				reminder.setAttribute('value', response.rc.pathname)
			}

			if (response.infos && infoTarget)
			{
				infoTarget.innerHTML = response.infos
				el.classList.add('has-info')
			}
			else
			{
				el.classList.remove('has-info')
			}

			this.onSuccess(response, target)
		}

		/**
		 * @param {object} response
		 * @param {XMLHttpRequest} xhr
		 *
		 * @fires Brickrouge.File#success
		 * @fires Brickrouge.File#complete
		 */
		onSuccess(response, xhr)
		{
			this.complete(response)
			this.notify(new SuccessEvent(response, xhr)).notify(new CompleteEvent(response, xhr))
		}

		/**
		 * @param {Event} ev
		 */
		failure(ev)
		{
			/**
			 * @type {XMLHttpRequest|EventTarget}
			 */
			const target = ev.target

			/**
			 * @type {ICanBoogie.Response}
			 */
			const response = JSON.parse(target.responseText)
			const el = this.element

			let alertMessage = ''

			if (response.errors)
			{
				Object.forEach(response.errors, message => {
					alertMessage += '<p>' + message + '</p>'
				})
			}

			el.querySelector('.alert-danger').innerHTML = alertMessage
			el.classList.add('has-danger')

			this.clearInfo()

			el.querySelector('input.reminder').removeAttribute('value')

			this.onFailure(response, target)
		}

		/**
		 * @param {object} response
		 * @param {XMLHttpRequest} xhr
		 *
		 * @fires Brickrouge.File#failure
		 * @fires Brickrouge.File#complete
		 */
		onFailure(response, xhr)
		{
			this.complete(response)
			this.notify(new FailureEvent(response, xhr)).notify(new CompleteEvent(response, xhr))
		}

		clearInfo()
		{
			const el = this.element
			const info = el.querySelector('div.infos')

			el.classList.remove('has-info')

			if (!info) return

			Brickrouge.empty(info)
		}

		/**
		 * @param {function} callback
		 */
		observePrepare(callback)
		{
			this.observe(PrepareEvent, callback)
		}

		/**
		 * @param {function} callback
		 */
		observeChange(callback)
		{
			this.observe(ChangeEvent, callback)
		}

		/**
		 * @param {function} callback
		 */
		observeComplete(callback)
		{
			this.observe(CompleteEvent, callback)
		}

		/**
		 * @param {function} callback
		 */
		observeFailure(callback)
		{
			this.observe(FailureEvent, callback)
		}

		/**
		 * @param {function} callback
		 */
		observeSuccess(callback)
		{
			this.observe(SuccessEvent, callback)
		}

	}

	Object.defineProperties(FileElement, {

		EVENT_PREPARE:  { value: PrepareEvent },
		EVENT_CHANGE:   { value: ChangeEvent },
		EVENT_COMPLETE: { value: CompleteEvent },
		EVENT_FAILURE:  { value: FailureEvent },
		EVENT_SUCCESS:  { value: SuccessEvent }

	})

	Brickrouge.register('File', function (element, options) {

		return new FileElement(element, options)

	})

	Brickrouge.File = FileElement

} (Brickrouge);
