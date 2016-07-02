declare interface Brickrouge {
    register(name: string, factory: Function)
    from(element: Element): Brickrouge.Widget
    observeRunning(callback: Function)
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
