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

namespace OpenDxp\Bundle\CoreBundle\DependencyInjection\Compiler;

use OpenDxp\HttpCache\HttpCache;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class DoctrineEntityCacheStrategyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (
            !$container->hasParameter('opendxp.http_cache.enabled') ||
            $container->getParameter('opendxp.http_cache.enabled') === false
        ) {
            return;
        }

        foreach ($container->findTaggedServiceIds('opendxp.http_cache.doctrine_entity') as $id => $tags) {
            $definition = $container->getDefinition($id);

            if (count($tags) > 1) {
                throw new InvalidConfigurationException(sprintf(
                    'Service "%s" may only have one "opendxp.http_cache.doctrine_entity" tag.',
                    $id
                ));
            }

            $tag = $tags[0];

            $entityClass = $tag['entity_class'] ?? null;
            $tagPrefix = $tag['tag_prefix'] ?? null;

            if (!$entityClass || !$tagPrefix) {
                throw new \InvalidArgumentException(sprintf(
                    'Service "%s" tagged with "opendxp.http_cache.doctrine_entity" must define "entity_class" and "tag_prefix".',
                    $id
                ));
            }

            $definition->setArguments([
                '$entityClass'          => $entityClass,
                '$tagPrefix'            => $tagPrefix,
                '$identifierExpression' => $tag['identifier_expression'] ?? 'object.getId()',
                '$httpCache'            => new Reference(HttpCache::class),
            ]);

            $definition->addTag('opendxp.http_cache.strategy');

            foreach (['postLoad', 'postPersist', 'postUpdate', 'postRemove'] as $event) {
                $definition->addTag('doctrine.event_listener', [
                    'event'  => $event,
                    'method' => $event,
                    'lazy'   => true,
                ]);
            }
        }
    }
}