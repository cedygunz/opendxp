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

use Exception;
use OpenDxp\Console\AbstractCommand;
use OpenDxp\Logger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(
    name:'opendxp:mysql-tools',
    description: 'Optimize and warmup mysql database',
    aliases: ['mysql-tools']
)]
class MysqlToolsCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'mode',
                'm',
                InputOption::VALUE_REQUIRED,
                'optimize or warmup'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // display error message
        if (!$input->getOption('mode')) {
            $this->writeError('Please specify the mode!');
            exit;
        }

        $db = \OpenDxp\Db::get();

        if ($input->getOption('mode') === 'optimize') {
            $tables = $db->fetchAllAssociative('SHOW TABLES');

            foreach ($tables as $table) {
                $t = current($table);

                try {
                    Logger::debug('Running: OPTIMIZE TABLE ' . $t);
                    $db->executeQuery(sprintf('OPTIMIZE TABLE %s', $t));
                } catch (Exception $e) {
                    Logger::error((string) $e);
                }
            }
        } elseif ($input->getOption('mode') === 'warmup') {
            $tables = $db->fetchAllAssociative('SHOW TABLES');

            foreach ($tables as $table) {
                $t = current($table);

                try {
                    Logger::debug("Running: SELECT COUNT(*) FROM $t");
                    $res = $db->fetchOne(sprintf('SELECT COUNT(*) FROM %s', $t));
                    Logger::debug('Result: ' . $res);
                } catch (Exception $e) {
                    Logger::error((string) $e);
                }
            }
        }

        return 0;
    }
}
