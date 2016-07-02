declare namespace Brickrouge {

    class Popover extends Subject {
        static readonly DEFAULT_OPTIONS: Popover.Options
        static readonly ActionEvent: Popover.ActionEvent
        static from(options: Popover.FromOptions)

        constructor(element: Element, options: Popover.Options)
        attachAnchor(anchor: Element|string)
        show()
        hide()
        isVisible(): boolean
        reposition(quick: boolean)
        observeAction(callback: Function)
    }

    namespace Popover {

        interface Options {
            anchor: Element|null,
            animate: boolean,
            popoverClass: string|null,
            placement: string|null,
            visible: boolean,
            fitContent: boolean,
            loveContent: boolean,
            iframe: Element|null
        }

        interface FromOptions extends Options {
            title: string
            content: string|Element
            actions: string|Array
        }

        interface ActionEvent {
            action: string
            popover: Popover,
            event: Event
        }

    }

}
