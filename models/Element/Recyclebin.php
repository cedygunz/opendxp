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

namespace OpenDxp\Model\Element;

use OpenDxp\Model;
use OpenDxp\Tool\Storage;

/**
 * @method \OpenDxp\Model\Element\Recyclebin\Dao getDao()
 *
 * @internal
 */
final class Recyclebin extends Model\AbstractModel
{
    public function flush(): void
    {
        $this->getDao()->flush();

        $storage = Storage::get('recycle_bin');
        foreach ($storage->listContents('/', false) as $item) {
            if ($item->isDir()) {
                $storage->deleteDirectory($item->path());
            } else {
                $storage->delete($item->path());
            }
        }
    }
}
