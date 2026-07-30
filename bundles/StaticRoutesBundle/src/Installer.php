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

namespace OpenDxp\Bundle\StaticRoutesBundle;

use OpenDxp\Bundle\StaticRoutesBundle\Security\StaticRoutesPermission;
use OpenDxp\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use OpenDxp\Model\Tool\SettingsStore;
use OpenDxp\Security\PermissionAttribute;
use Override;

/**
 * @internal
 */
class Installer extends SettingsStoreAwareInstaller
{
    protected const string SETTINGS_STORE_SCOPE = 'opendxp_staticroutes';

    protected const string USER_PERMISSION_CATEGORY = 'OpenDxp Static Routes Bundle';

    #[Override]
    public function install(): void
    {
        $this->addUserPermission();
        parent::install();
    }

    #[Override]
    public function uninstall(): void
    {
        $this->removeUserPermission();
        $this->removeRoutesFromSettingsStore();
        parent::uninstall();
    }

    private function addUserPermission(): void
    {
        $db = \OpenDxp\Db::get();

        foreach (StaticRoutesPermission::cases() as $permission) {
            $db->insert('users_permission_definitions', [
                $db->quoteIdentifier('key') => PermissionAttribute::for($permission->value),
                $db->quoteIdentifier('category') => self::USER_PERMISSION_CATEGORY,
            ]);
        }
    }

    private function removeUserPermission(): void
    {
        $db = \OpenDxp\Db::get();

        foreach (StaticRoutesPermission::cases() as $permission) {
            $db->delete('users_permission_definitions', [
                $db->quoteIdentifier('key') => PermissionAttribute::for($permission->value),
            ]);
        }
    }

    private function removeRoutesFromSettingsStore(): void
    {
        $staticRoutes = SettingsStore::getIdsByScope(self::SETTINGS_STORE_SCOPE);
        foreach ($staticRoutes as $staticRoute) {
            SettingsStore::delete($staticRoute, self::SETTINGS_STORE_SCOPE);
        }
    }
}
