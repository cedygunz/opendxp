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

namespace OpenDxp\Bundle\CoreBundle\EventListener\Doctrine;

use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\Persistence\ManagerRegistry;
use OpenDxp\Bundle\CoreBundle\Doctrine\ExcludesUnmanagedTablesInterface;
use Override;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[AutoconfigureTag('doctrine.dbal.schema_filter')]
class UnmanagedTablesSchemaFilter implements EventSubscriberInterface
{
    private bool $enabled = false;

    protected bool $initialized = false;

    protected array $managedTables;

    public function __construct(protected ManagerRegistry $managerRegistry)
    {
    }

    public function __invoke(AbstractAsset|string $assetName): bool
    {
        if (!$this->enabled) {
            return true;
        }

        if ($assetName instanceof AbstractAsset) {
            $assetName = $assetName->getName();
        }

        $this->loadManagedTables();

        return in_array($assetName, $this->managedTables, true);
    }

    private function loadManagedTables(): void
    {
        if ($this->initialized === true) {
            return;
        }

        $this->initialized = true;
        $this->managedTables = [];

        foreach ($this->managerRegistry->getManagers() as $em) {
            foreach ($em->getMetadataFactory()->getAllMetadata() as $metadata) {
                if ($metadata instanceof ClassMetadata && !in_array($metadata->getTableName(), $this->managedTables, true)) {
                    $this->managedTables[] = $metadata->getTableName();
                }
            }
        }
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        $this->enabled = $command instanceof UpdateCommand
            || $command instanceof ValidateSchemaCommand
            || $command instanceof ExcludesUnmanagedTablesInterface;
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => 'onConsoleCommand',
        ];
    }
}