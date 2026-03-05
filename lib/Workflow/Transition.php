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

namespace OpenDxp\Workflow;

use OpenDxp\Workflow\Notes\NotesAwareInterface;
use OpenDxp\Workflow\Notes\NotesAwareTrait;
use OpenDxp\Workflow\Notification\NotificationInterface;
use OpenDxp\Workflow\Notification\NotificationTrait;

class Transition extends \Symfony\Component\Workflow\Transition implements NotesAwareInterface, NotificationInterface
{
    use NotesAwareTrait;
    use NotificationTrait;

    public const string UNSAVED_CHANGES_BEHAVIOUR_SAVE = 'save';

    public const string UNSAVED_CHANGES_BEHAVIOUR_IGNORE = 'ignore';

    public const string UNSAVED_CHANGES_BEHAVIOUR_WARN = 'warn';

    private array $options;

    /**
     * @param string|string[] $froms
     * @param string|string[] $tos
     */
    public function __construct(string $name, $froms, $tos, array $options = [])
    {
        parent::__construct($name, $froms, $tos);
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getLabel(): string
    {
        return $this->options['label'] ?? $this->getName();
    }

    public function getIconClass(): string
    {
        return $this->options['iconClass'] ?? 'opendxp_icon_workflow_action';
    }

    /**
     * @return string|int|false
     */
    public function getObjectLayout(): bool|int|string
    {
        return $this->options['objectLayout'] ?: false;
    }

    public function getChangePublishedState(): string
    {
        return (string) $this->options['changePublishedState'];
    }
}
