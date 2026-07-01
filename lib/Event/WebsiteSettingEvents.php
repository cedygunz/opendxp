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

final class WebsiteSettingEvents
{
    /**
     * Fired by WebsiteSetting::getById() after a single setting is loaded.
     *
     * @Event("OpenDxp\Event\Model\WebsiteSettingLoadEvent")
     */
    public const string POST_LOAD = 'opendxp.websiteSetting.postLoad';

    /**
     * Fired by Config::getWebsiteConfig() after the full setting collection is loaded.
     *
     * @Event("OpenDxp\Event\Model\WebsiteSettingLoadEvent")
     */
    public const string LIST_LOAD = 'opendxp.websiteSetting.listLoad';

    /**
     * Fired by Config::getWebsiteConfigValue() when a specific key is accessed.
     *
     * @Event("OpenDxp\Event\Model\WebsiteSettingLoadEvent")
     */
    public const string DATA_LOAD = 'opendxp.websiteSetting.dataLoad';

    /**
     * @Event("OpenDxp\Event\Model\WebsiteSettingEvent")
     */
    public const string PRE_ADD = 'opendxp.websiteSetting.preAdd';

    /**
     * @Event("OpenDxp\Event\Model\WebsiteSettingEvent")
     */
    public const string POST_ADD = 'opendxp.websiteSetting.postAdd';

    /**
     * @Event("OpenDxp\Event\Model\WebsiteSettingEvent")
     */
    public const string PRE_UPDATE = 'opendxp.websiteSetting.preUpdate';

    /**
     * @Event("OpenDxp\Event\Model\WebsiteSettingEvent")
     */
    public const string POST_UPDATE = 'opendxp.websiteSetting.postUpdate';

    /**
     * @Event("OpenDxp\Event\Model\WebsiteSettingEvent")
     */
    public const string PRE_DELETE = 'opendxp.websiteSetting.preDelete';

    /**
     * @Event("OpenDxp\Event\Model\WebsiteSettingEvent")
     */
    public const string POST_DELETE = 'opendxp.websiteSetting.postDelete';
}
