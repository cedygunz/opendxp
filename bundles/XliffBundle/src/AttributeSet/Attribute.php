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

namespace OpenDxp\Bundle\XliffBundle\AttributeSet;

class Attribute
{
    public const string TYPE_PROPERTY = 'property';
    public const string TYPE_TAG = 'tag';
    public const string TYPE_SETTINGS = 'settings';
    public const string TYPE_LOCALIZED_FIELD = 'localizedfield';
    public const string TYPE_BRICK_LOCALIZED_FIELD = 'localizedbrick';
    public const string TYPE_BLOCK = 'block';
    public const string TYPE_BLOCK_IN_LOCALIZED_FIELD = 'blockinlocalizedfield';
    public const string TYPE_BLOCK_IN_LOCALIZED_FIELD_COLLECTION = 'blockinlocalizedfieldcollection';
    public const string TYPE_FIELD_COLLECTION_LOCALIZED_FIELD = 'localizedfieldcollection';
    public const string TYPE_ELEMENT_KEY = 'key';

    /**
     * @param string[] $targetContent
     */
    public function __construct(
        private readonly string $type,
        private readonly string $name,
        private readonly string $content,
        private readonly bool $isReadonly = false,
        private readonly array $targetContent = []
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string[]
     */
    public function getTargetContent(): array
    {
        return $this->targetContent;
    }

    /**
     * Readonly attributes should not be translated - relevant for information purposes only.
     */
    public function isReadonly(): bool
    {
        return $this->isReadonly;
    }
}
