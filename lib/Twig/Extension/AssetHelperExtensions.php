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

namespace OpenDxp\Twig\Extension;

use OpenDxp\Model\Asset;
use Twig\Attribute\AsTwigTest;

/**
 * @internal
 */
class AssetHelperExtensions
{
    #[AsTwigTest('opendxp_asset')]
    public function isAsset(mixed $object): bool
    {
        return $object instanceof Asset;
    }

    #[AsTwigTest('opendxp_asset_archive')]
    public function isAssetArchive(mixed $object): bool
    {
        return $object instanceof Asset\Archive;
    }

    #[AsTwigTest('opendxp_asset_audio')]
    public function isAssetAudio(mixed $object): bool
    {
        return $object instanceof Asset\Audio;
    }

    #[AsTwigTest('opendxp_asset_document')]
    public function isAssetDocument(mixed $object): bool
    {
        return $object instanceof Asset\Document;
    }

    #[AsTwigTest('opendxp_asset_folder')]
    public function isAssetFolder(mixed $object): bool
    {
        return $object instanceof Asset\Folder;
    }

    #[AsTwigTest('opendxp_asset_image')]
    public function isAssetImage(mixed $object): bool
    {
        return $object instanceof Asset\Image;
    }

    #[AsTwigTest('opendxp_asset_text')]
    public function isAssetText(mixed $object): bool
    {
        return $object instanceof Asset\Text;
    }

    #[AsTwigTest('opendxp_asset_unknown')]
    public function isAssetUnknown(mixed $object): bool
    {
        return $object instanceof Asset\Unknown;
    }

    #[AsTwigTest('opendxp_asset_video')]
    public function isAssetVideo(mixed $object): bool
    {
        return $object instanceof Asset\Video;
    }
}
