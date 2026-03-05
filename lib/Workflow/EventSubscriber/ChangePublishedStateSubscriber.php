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

namespace OpenDxp\Workflow\EventSubscriber;

use OpenDxp\Model\DataObject\Concrete;
use OpenDxp\Model\Document;
use OpenDxp\Workflow\Transition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * @internal
 */
class ChangePublishedStateSubscriber implements EventSubscriberInterface
{
    public const string NO_CHANGE = 'no_change';

    public const string FORCE_PUBLISHED = 'force_published';

    public const string FORCE_UNPUBLISHED = 'force_unpublished';

    public const string SAVE_VERSION = 'save_version';

    public function onWorkflowCompleted(Event $event): void
    {
        if (!$this->checkEvent($event)) {
            return;
        }

        /** @var Transition $transition */
        $transition = $event->getTransition();

        /** @var Document|Concrete $subject */
        $subject = $event->getSubject();

        $changePublishedState = $transition->getChangePublishedState();

        if ($changePublishedState === self::FORCE_UNPUBLISHED) {
            $subject->setPublished(false);
        } elseif ($changePublishedState === self::FORCE_PUBLISHED) {
            $subject->setPublished(true);
        }
    }

    /**
     * check's if the event subscriber should be executed
     */
    private function checkEvent(Event $event): bool
    {
        return $event->getTransition() instanceof Transition
            && ($event->getSubject() instanceof Concrete || $event->getSubject() instanceof Document);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.completed' => 'onWorkflowCompleted',
        ];
    }
}
