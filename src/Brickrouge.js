/*!
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define([

    './Core',
    './Polyfills',
    './DOM',
    './AssetsCollector',
    './Utils',
    './Alert',
    './Form',
    './Popover',
    './Popover.auto',
    './DropdownMenu.auto',
    './Modal',
    './Modal.auto',
    './Tabs.auto',
    './Tooltip',
    './Searchbox',
    './Carousel'

], function (Brickrouge) {

    /**
     * Invokes {@link Brickrouge.run} on _DOM ready_.
     */
    document.addEventListener('DOMContentLoaded', Brickrouge.run)

})
