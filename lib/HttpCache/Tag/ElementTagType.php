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
 * @copyright  Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\HttpCache\Tag;

enum ElementTagType: string implements CacheTagType
{
    case Document = 'doc';
    case DocumentList = 'doc_list';
    case DataObject = 'obj';
    case DataObjectClass = 'obj_class';
    case Asset = 'asset';
    case AssetList = 'asset_list';
    case Translation = 'translation';

    public function prefix(): string
    {
        return $this->value;
    }
}
