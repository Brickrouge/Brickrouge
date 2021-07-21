define(
    [

    './Core',
    './Popover'

    ],
    /**
    * @param {Brickrouge} Brickrouge
    */
    function (Brickrouge) {

        "use strict";

        const instances = []

        /**
         * @param {Element} target
         *
         * @returns {Brickrouge.Popover}
         */
        function retrieve(target)
        {
            return instances[Brickrouge.uidOf(target)]
        }

        /**
         * @param {Element} target
         *
         * @returns {Brickrouge.Popover}
         */
        function from(target)
        {
            const uid = Brickrouge.uidOf(target)

            if (uid in instances) {
                return instances[uid]
            }

            let options = Brickrouge.Dataset.from(target)
            options.anchor = target

            return instances[uid] = Brickrouge.Popover.from(options)
        }

        /**
         * Event delegation for elements with a `rel="popover"` attribute.
         */
        document.body.addDelegatedEventListener('[rel="popover"]', 'mouseover', (ev, target) => {

            from(target).show()

        })

        document.body.addDelegatedEventListener('[rel="popover"]', 'mouseout', (ev, target) => {

            const popover = retrieve(target)

            if (!popover) {
                return
            }

            popover.hide()

        })

    }
)
