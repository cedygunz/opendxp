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

namespace OpenDxp\Model\DataObject\ClassDefinition\Data;

interface OptionsProviderInterface
{
    public const string TYPE_CONFIGURE = 'configure';
    public const string TYPE_SELECT_OPTIONS = 'select_options';
    public const string TYPE_CLASS = 'class';

    public const array TYPES = [
        self::TYPE_CONFIGURE,
        self::TYPE_SELECT_OPTIONS,
        self::TYPE_CLASS,
    ];

    public function getOptionsProviderType(): ?string;

    public function setOptionsProviderType(?string $optionsProviderType): void;

    public function getOptionsProviderClass(): ?string;

    public function setOptionsProviderClass(?string $optionsProviderClass): void;

    public function getOptionsProviderData(): ?string;

    public function setOptionsProviderData(?string $optionsProviderData): void;

    public function useConfiguredOptions(): bool;
}
