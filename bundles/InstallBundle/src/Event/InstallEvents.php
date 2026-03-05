<?php

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

namespace OpenDxp\Bundle\InstallBundle\Event;

class InstallEvents
{
    /**
     * Event gets fire for every installer step e.g. install assets, install db
     */
    public const string EVENT_NAME_STEP = 'opendxp.installer.step';

    /**
     * Event is fired before bundle selection in installer. Bundles and Recommendations can be added or removed here
     */
    public const string EVENT_BUNDLE_SETUP = 'opendxp.installer.setup_bundles';
}
