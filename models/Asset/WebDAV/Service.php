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

namespace OpenDxp\Model\Asset\WebDAV;

use OpenDxp\Tool\Serialize;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
class Service
{
    public static function getDeleteLogFile(): string
    {
        return OPENDXP_SYSTEM_TEMP_DIRECTORY . '/webdav-delete.dat';
    }

    public static function getDeleteLog(): array
    {
        if (!file_exists(self::getDeleteLogFile())) {
            return [];
        }

        $log = Serialize::unserialize(
            file_get_contents(self::getDeleteLogFile()),
            ['allowed_classes' => false]
        );

        if (!is_array($log)) {
            return [];
        }

        $cutoff = time() - 30;

        return array_filter($log, static fn (array $data) => $data['timestamp'] > $cutoff);
    }

    public static function saveDeleteLog(array $log): void
    {
        $cutoff = time() - 30;
        $log = array_filter($log, static fn (array $data) => $data['timestamp'] > $cutoff);

        $filesystem = new Filesystem();
        $filesystem->dumpFile(self::getDeleteLogFile(), Serialize::serialize($log));
    }
}
