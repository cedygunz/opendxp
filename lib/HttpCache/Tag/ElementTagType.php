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
    case Document = 'document';
    case DocumentList = 'document_list';
    case DataObject = 'data_object';
    case DataObjectClass = 'data_object_class';
    case Asset = 'asset';
    case AssetList = 'asset_list';
    case Translation = 'translation';
    case WebsiteSetting = 'website_setting';
    case WebsiteSettingList = 'website_setting_list';

    public function prefix(): string
    {
        return $this->value;
    }
}
