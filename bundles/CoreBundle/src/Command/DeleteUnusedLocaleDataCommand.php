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

use Doctrine\DBAL\ArrayParameterType;
use OpenDxp\Console\AbstractCommand;
use OpenDxp\Console\Traits\DryRun;
use OpenDxp\Db;
use OpenDxp\Helper\ArrayHelper;
use OpenDxp\Tool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(
    name: 'opendxp:locale:delete-unused-tables',
    description: 'Delete unused locale(invalid language) tables & views'
)]
class DeleteUnusedLocaleDataCommand extends AbstractCommand
{
    use DryRun;

    protected function configure(): void
    {
        $this
            ->addOption(
                'skip-locales',
                's',
                InputOption::VALUE_OPTIONAL,
                'Do not delete specified locale tables (comma separated eg.: en, en_AT)'
            )
        ;

        $this->configureDryRunOption('Just output the delete localized queries to be executed.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $db = Db::get();
        $skipLocales = [];
        if ($input->getOption('skip-locales')) {
            $skipLocales = explode(',', $input->getOption('skip-locales'));
        }

        $validLanguages = Tool::getValidLanguages();

        $tables = $db->fetchAllAssociative("SHOW TABLES LIKE 'object\_localized\_data\_%'");

        foreach ($tables as $table) {
            $printLine = false;
            $table = current($table);
            $classId = str_replace('object_localized_data_', '', $table);

            $result = $db->fetchAllAssociative(
                sprintf('SELECT DISTINCT `language` FROM %s WHERE `language` NOT IN(?)', $table),
                [$validLanguages],
                [ArrayParameterType::STRING]
            );
            $result = ($result ?: []);

            //delete data from object_localized_data_classID tables
            foreach ($result as $res) {
                $language = $res['language'];
                if (!ArrayHelper::inArrayCaseInsensitive($language, $skipLocales) && !ArrayHelper::inArrayCaseInsensitive($language, $validLanguages)) {
                    $printLine = true;
                    $deleteStmt = sprintf('DELETE FROM object_localized_data_%s WHERE `language` = ?', $classId);
                    if (!$this->isDryRun()) {
                        $output->writeln(sprintf('DELETE FROM object_localized_data_%s WHERE `language` = %s', $classId, $db->quote($language)));
                        $db->executeStatement($deleteStmt, [$language]);
                    } else {
                        $output->writeln($this->dryRunMessage(sprintf('DELETE FROM object_localized_data_%s WHERE `language` = %s', $classId, $db->quote($language))));
                    }
                }
            }

            //drop unused localized view e.g. object_localized_classId_*
            $existingViews = $db->fetchAllAssociative("SHOW TABLES LIKE 'object\_localized\_{$classId}\_%'");
            foreach ($existingViews as $existingView) {
                $localizedView = current($existingView);
                $existingLanguage = str_replace('object_localized_'.$classId.'_', '', $localizedView);

                if (!ArrayHelper::inArrayCaseInsensitive($existingLanguage, $validLanguages)) {
                    $sqlDropView = sprintf('DROP VIEW IF EXISTS object_localized_%s_%s', $classId, $existingLanguage);
                    $printLine = true;

                    if (!$this->isDryRun()) {
                        $output->writeln($sqlDropView);
                        $db->executeQuery($sqlDropView);
                    } else {
                        $output->writeln($this->dryRunMessage($sqlDropView));
                    }
                }
            }

            //drop unused localized table e.g. object_localized_query_classId_*
            $existingTables = $db->fetchAllAssociative("SHOW TABLES LIKE 'object\_localized\_query\_{$classId}\_%'");
            foreach ($existingTables as $existingTable) {
                $localizedTable = current($existingTable);
                $existingLanguage = str_replace('object_localized_query_'.$classId.'_', '', $localizedTable);

                if (!ArrayHelper::inArrayCaseInsensitive($existingLanguage, $validLanguages)) {
                    $sqlDropTable = sprintf('DROP TABLE IF EXISTS object_localized_query_%s_%s', $classId, $existingLanguage);
                    $printLine = true;

                    if (!$this->isDryRun()) {
                        $output->writeln($sqlDropTable);
                        $db->executeQuery($sqlDropTable);
                    } else {
                        $output->writeln($this->dryRunMessage($sqlDropTable));
                    }
                }
            }

            if ($printLine) {
                $output->writeln('------------');
            }
        }

        return 0;
    }
}
