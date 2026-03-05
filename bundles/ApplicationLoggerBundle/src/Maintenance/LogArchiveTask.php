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

namespace OpenDxp\Bundle\ApplicationLoggerBundle\Maintenance;

use Carbon\Carbon;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use OpenDxp\Bundle\ApplicationLoggerBundle\Handler\ApplicationLoggerDb;
use OpenDxp\Config;
use OpenDxp\Maintenance\TaskInterface;
use OpenDxp\Tool\Storage;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class LogArchiveTask implements TaskInterface
{
    public function __construct(
        private readonly Connection $db,
        private Config $config,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        $storage = Storage::get('application_log');

        $date = new DateTime('now');
        $archiveTable = sprintf('%s_%s', ApplicationLoggerDb::TABLE_ARCHIVE_PREFIX, $date->format('Y_m'));

        if (!empty($this->config['applicationlog']['archive_alternative_database'])) {
            $archiveTable = sprintf(
                '%s.%s',
                $this->db->quoteIdentifier($this->config['applicationlog']['archive_alternative_database']),
                $archiveTable
            );
        }

        $archiveThreshold = (int) ($this->config['applicationlog']['archive_treshold'] ?? 30);
        $sourceTable = ApplicationLoggerDb::TABLE_NAME;
        $cutoff = (new DateTimeImmutable())->modify(sprintf('-%d days', $archiveThreshold))->format('Y-m-d H:i:s');
        $whereParams = [$cutoff];

        $count = $this->db->fetchOne(
            sprintf('SELECT COUNT(*) FROM %s WHERE `timestamp` < ?', $sourceTable),
            $whereParams
        );

        if ($count > 0) {
            $this->db->executeStatement(sprintf(
                "CREATE TABLE IF NOT EXISTS %s (
                    id BIGINT(20) NOT NULL,
                    `pid` INT(11) NULL DEFAULT NULL,
                    `timestamp` DATETIME NOT NULL,
                    message VARCHAR(1024),
                    `priority` ENUM('emergency','alert','critical','error','warning','notice','info','debug') DEFAULT NULL,
                    fileobject VARCHAR(1024),
                    info VARCHAR(1024),
                    component VARCHAR(255),
                    source VARCHAR(255) NULL DEFAULT NULL,
                    relatedobject BIGINT(20),
                    relatedobjecttype ENUM('object', 'document', 'asset'),
                    maintenanceChecked TINYINT(1)
                ) ENGINE = ARCHIVE ROW_FORMAT = DEFAULT",
                $archiveTable
            ));

            $this->db->executeStatement(
                sprintf('INSERT INTO %s SELECT * FROM %s WHERE `timestamp` < ?', $archiveTable, $sourceTable),
                $whereParams
            );

            $this->logger->debug(sprintf(
                'Deleting referenced FileObjects of application_logs which are older than %d days',
                $archiveThreshold
            ));

            $fileObjectPaths = $this->db->fetchAllAssociative(
                sprintf('SELECT fileobject FROM %s WHERE `timestamp` < ?', $sourceTable),
                $whereParams
            );

            foreach ($fileObjectPaths as $objectPath) {
                $filePath = $objectPath['fileobject'];
                if ($filePath !== null && $storage->fileExists($filePath)) {
                    $storage->delete($filePath);
                }
            }

            $this->db->executeStatement(
                sprintf('DELETE FROM %s WHERE `timestamp` < ?', $sourceTable),
                $whereParams
            );
        }

        $archiveTables = $this->db->fetchFirstColumn(
            'SELECT table_name
                FROM information_schema.tables
                WHERE table_schema = ?
                AND table_name LIKE ?',
            [
                $this->config['applicationlog']['archive_alternative_database'] ?: $this->db->getDatabase(),
                ApplicationLoggerDb::TABLE_ARCHIVE_PREFIX . '_%',
            ]
        );

        foreach ($archiveTables as $archiveTableName) {
            if (preg_match('/^' . ApplicationLoggerDb::TABLE_ARCHIVE_PREFIX . '_(\d{4})_(\d{2})$/', $archiveTableName, $matches)) {
                $deleteArchiveLogDate = Carbon::createFromFormat('Y/m', $matches[1] . '/' . $matches[2]);
                if ($deleteArchiveLogDate->add(new DateInterval('P' . ($this->config['applicationlog']['delete_archive_threshold'] ?? 6) . 'M')) < new DateTimeImmutable()) {
                    $this->db->executeStatement(sprintf(
                        'DROP TABLE IF EXISTS %s.%s',
                        $this->db->quoteIdentifier($this->config['applicationlog']['archive_alternative_database'] ?: $this->db->getDatabase()),
                        $this->db->quoteIdentifier($archiveTableName)
                    ));

                    $folderName = $deleteArchiveLogDate->format('Y/m');
                    if ($storage->directoryExists($folderName)) {
                        $storage->deleteDirectory($folderName);
                    }
                }
            }
        }
    }
}
