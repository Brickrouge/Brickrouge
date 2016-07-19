declare namespace Brickrouge {
    namespace Form {
        interface SubmitEvent {

        }

        interface RequestEvent {

        }

        interface SuccessEvent {
            response: Object
        }

        interface FailureEvent {
            xhr: XMLHttpRequest
            response: Object
        }

        interface CompleteEvent {

        }

        interface Options {
            url: string|null,
            useXHR: boolean,
            replaceOnSuccess: boolean
        }
    }

    class Form extends Subject {
        static readonly SubmitEvent: Form.SubmitEvent
        static readonly RequestEvent: Form.RequestEvent
        static readonly SuccessEvent: Form.SuccessEvent
        static readonly FailureEvent: Form.FailureEvent
        static readonly CompleteEvent: Form.CompleteEvent
        static from(element: HTMLFormElement): Form

        constructor (element: HTMLFormElement, options: Form.Options)
        readonly isProcessingSubmit: boolean
        alert(messages: string|Object, type: string)
        clearAlert()
        submit()
        observeSubmit(callback: Function)
        observeRequest(callback: Function)
        observeSuccess(callback: Function)
        observeFailure(callback: Function)
        observeComplete(callback: Function)
    }

    interface Response {
        errors: Object|undefined
        exception: string|undefined
    }
}
