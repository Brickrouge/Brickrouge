declare interface Brickrouge {
    register(name: string, factory: Function)
    from(element: Element): Brickrouge.Widget
    observe(event: Function, callback: Function)
    observeRunning(callback: Function)
    observeUpdate(callback: Function)
    observeWidget(callback: Function)
    notify(event: Object)
}

declare namespace Brickrouge {

    class Form {

    }

    interface Widget {
        element: Element
        options: Widget.Options
    }

    namespace Widget {

        interface Options extends Object {}

    }

}
