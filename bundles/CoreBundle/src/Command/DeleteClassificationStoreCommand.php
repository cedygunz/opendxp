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

namespace OpenDxp\Bundle\CoreBundle\Command;

use InvalidArgumentException;
use OpenDxp\Cache;
use OpenDxp\Console\AbstractCommand;
use OpenDxp\Db;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(
    name: 'opendxp:classificationstore:delete-store',
    description: 'Delete Classification Store',
    aliases: ['classificationstore:delete-store']
)]
class DeleteClassificationStoreCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument('storeId', InputArgument::REQUIRED, 'The store ID to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $storeId = $input->getArgument('storeId');

        if (!is_numeric($storeId)) {
            throw new InvalidArgumentException('Invalid store ID');
        }

        $storeId = (int) $storeId;
        $db = Db::get();

        $tableList = $db->fetchAllAssociative('SHOW TABLES LIKE "object_classificationstore_data_%"');
        foreach ($tableList as $table) {
            $theTable = current($table);
            $output->writeln(sprintf('Deleting classification store data from <info>%s</info>', $theTable));
            $db->executeStatement(
                sprintf('DELETE FROM %s WHERE keyId IN (SELECT id FROM classificationstore_keys WHERE storeId = ?)', $theTable),
                [$storeId]
            );
        }

        $tableList = $db->fetchAllAssociative('SHOW TABLES LIKE "object_classificationstore_groups_%"');
        foreach ($tableList as $table) {
            $theTable = current($table);
            $output->writeln(sprintf('Deleting classification store groups from <info>%s</info>', $theTable));
            $db->executeStatement(
                sprintf('DELETE FROM %s WHERE groupId IN (SELECT id FROM classificationstore_groups WHERE storeId = ?)', $theTable),
                [$storeId]
            );
        }

        $output->writeln('Deleting keys, groups, collections and store record');
        $db->executeStatement('DELETE FROM classificationstore_keys WHERE storeId = ?', [$storeId]);
        $db->executeStatement('DELETE FROM classificationstore_groups WHERE storeId = ?', [$storeId]);
        $db->executeStatement('DELETE FROM classificationstore_collections WHERE storeId = ?', [$storeId]);
        $db->executeStatement('DELETE FROM classificationstore_stores WHERE id = ?', [$storeId]);

        Cache::clearAll();

        return self::SUCCESS;
    }
}