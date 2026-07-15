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

namespace OpenDxp\HttpCache;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use OpenDxp\HttpCache\Tag\CacheTag;
use OpenDxp\HttpCache\Tag\SimpleTagType;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Generic HTTP cache strategy for Doctrine entities
 */
final class DoctrineGeneralEntityCacheStrategy implements HttpCacheTagStrategyInterface
{
    private readonly ExpressionLanguage $expressionLanguage;

    public function __construct(
        private readonly string $entityClass,
        private readonly string $tagPrefix,
        private readonly string $identifierExpression = 'object.getId()',
        private readonly ?HttpCache $httpCache = null,
    ) {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function supports(object $element): bool
    {
        return $element instanceof $this->entityClass;
    }

    public function getTags(object $element): array
    {
        $identifier = $this->expressionLanguage->evaluate($this->identifierExpression, ['object' => $element]);

        return [new CacheTag(new SimpleTagType($this->tagPrefix), $identifier)];
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $obj = $args->getObject();
        if ($obj instanceof $this->entityClass) {
            $this->httpCache?->collectTagsFor($obj);
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $obj = $args->getObject();
        if ($obj instanceof $this->entityClass) {
            $this->httpCache?->invalidate($obj);
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $obj = $args->getObject();
        if ($obj instanceof $this->entityClass) {
            $this->httpCache?->invalidate($obj);
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $obj = $args->getObject();
        if ($obj instanceof $this->entityClass) {
            $this->httpCache?->invalidate($obj);
        }
    }
}
