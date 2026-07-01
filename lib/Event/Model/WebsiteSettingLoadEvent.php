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

namespace OpenDxp\Event\Model;

use OpenDxp\Model\WebsiteSetting;
use Symfony\Contracts\EventDispatcher\Event;

final class WebsiteSettingLoadEvent extends Event
{
    public const string TYPE_SINGLE = 'single';
    public const string TYPE_LIST   = 'list';
    public const string TYPE_DATA   = 'data';

    /**
     * @param array<string, mixed>|null $values
     */
    public function __construct(
        private readonly string $type,
        private readonly ?WebsiteSetting $setting = null,
        private readonly ?string $key = null,
        private readonly ?string $language = null,
        private readonly ?int $id = null,
        private readonly mixed $value = null,
        private readonly ?array $values = null,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSetting(): ?WebsiteSetting
    {
        return $this->setting;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }
}
