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

use OpenDxp\Model\DataObject;
use Twig\Attribute\AsTwigFunction;
use Twig\Attribute\AsTwigTest;

/**
 * @internal
 */
class DataObjectHelperExtensions
{
    #[AsTwigTest('opendxp_data_object')]
    public function isDataObject(mixed $object): bool
    {
        return $object instanceof DataObject\Concrete;
    }

    #[AsTwigTest('opendxp_data_object_folder')]
    public function isDataObjectFolder(mixed $object): bool
    {
        return $object instanceof DataObject\Folder;
    }

    #[AsTwigTest('opendxp_data_object_class')]
    public function isDataObjectClass(mixed $object, string $className): bool
    {
        $className = ucfirst($className);
        $className = 'OpenDxp\\Model\\DataObject\\' . $className;

        return class_exists($className) && $object instanceof $className;
    }

    #[AsTwigTest('opendxp_data_object_gallery')]
    public function isDataObjectGallery(mixed $object): bool
    {
        return $object instanceof DataObject\Data\ImageGallery;
    }

    #[AsTwigTest('opendxp_data_object_hotspot_image')]
    public function isDataObjectHotspotImage(mixed $object): bool
    {
        return $object instanceof DataObject\Data\Hotspotimage;
    }

    #[AsTwigFunction('opendxp_data_object_select_options')]
    public function getDataObjectSelectOptions(mixed $object, string $field): array
    {
        return DataObject\Service::getOptionsForSelectField($object, $field);
    }
}
