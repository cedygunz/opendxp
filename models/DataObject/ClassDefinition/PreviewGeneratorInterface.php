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

namespace OpenDxp\Model\DataObject\ClassDefinition;

use OpenDxp\Model\DataObject\Concrete;

interface PreviewGeneratorInterface
{
    public const string PARAMETER_SITE = 'site';
    public const string PARAMETER_LOCALE = 'locale';

    public function generatePreviewUrl(Concrete $object, array $params): string;

    /**
     * @return list<array{name: string, label: string, values: array<string, string>, defaultValue: int|string|null}>
     */
    public function getPreviewConfig(Concrete $object): array;
}
