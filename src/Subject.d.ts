declare namespace Brickrouge {

    class Subject {
        static createEvent(constructor: Function): Function
        observe(constructor: Function, callback: Function): Subject
        unobserve(callback: Function): Subject
        notify(event: Object): Subject
    }

}
