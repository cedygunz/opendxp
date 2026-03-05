<?php
declare(strict_types=1);

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Event;

class BundleManagerEvents
{
    /**
     * The CSS_PATHS event is triggered for paths to CSS files which are about to be loaded for the admin interface.
     *
     * @Event("OpenDxp\Event\BundleManager\PathsEvent")
     *
     * @var string
     */
    public const string CSS_PATHS = 'opendxp.bundle_manager.paths.css';

    /**
     * The JS_PATHS event is triggered for paths to JS files which are about to be loaded for the admin interface.
     *
     * @Event("OpenDxp\Event\BundleManager\PathsEvent")
     *
     * @var string
     */
    public const string JS_PATHS = 'opendxp.bundle_manager.paths.js';

    /**
     * The EDITMODE_CSS_PATHS event is triggered for paths to CSS files which are about to be loaded in editmode.
     *
     * @Event("OpenDxp\Event\BundleManager\PathsEvent")
     *
     * @var string
     */
    public const string EDITMODE_CSS_PATHS = 'opendxp.bundle_manager.paths.editmode_css';

    /**
     * The EDITMODE_JS_PATHS event is triggered for paths to JS files which are about to be loaded in editmode.
     *
     * @Event("OpenDxp\Event\BundleManager\PathsEvent")
     *
     * @var string
     */
    public const string EDITMODE_JS_PATHS = 'opendxp.bundle_manager.paths.editmode_js';
}
