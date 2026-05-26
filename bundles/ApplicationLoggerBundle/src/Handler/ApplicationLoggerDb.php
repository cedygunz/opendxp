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

namespace OpenDxp\Bundle\ApplicationLoggerBundle\Handler;

use DateTimeZone;
use Doctrine\DBAL\Connection;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use OpenDxp\DateFormat;
use OpenDxp\Db;

class ApplicationLoggerDb extends AbstractProcessingHandler
{
    public const string TABLE_NAME = 'application_logs';

    public const string TABLE_ARCHIVE_PREFIX = 'application_logs_archive';

    public function __construct(
        private readonly Connection $db,
        int|string|Level $level = Level::Debug,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    public function write(LogRecord $record): void
    {
        $data = [
            'pid' => getmypid(),
            'priority' => $record->level->toPsrLogLevel(),
            'message' => $record->message,
            'timestamp' => $record->datetime->setTimezone(new DateTimeZone('UTC'))->format(DateFormat::DATETIME),
            'component' => $record->context['component'] ?? $record->channel,
            'fileobject' => $record->context['fileObject'] ?? null,
            'relatedobject' => $record->context['relatedObject'] ?? null,
            'relatedobjecttype' => $record->context['relatedObjectType'] ?? null,
            'source' => $record->context['source'] ?? null,
        ];

        $this->db->insert(self::TABLE_NAME, $data);
    }

    /**
     * @return string[]
     */
    public static function getComponents(): array
    {
        return Db::get()->fetchFirstColumn(sprintf(
            'SELECT component FROM %s WHERE component IS NOT NULL GROUP BY component',
            self::TABLE_NAME
        )
        );
    }

    /**
     * @return string[]
     */
    public static function getPriorities(): array
    {
        $priorities = [];
        $priorityNames = [
            'debug' => 'DEBUG',
            'info' => 'INFO',
            'notice' => 'NOTICE',
            'warning' => 'WARN',
            'error' => 'ERR',
            'critical' => 'CRIT',
            'alert' => 'ALERT',
            'emergency' => 'EMERG',
        ];

        $priorityNumbers = Db::get()->fetchFirstColumn(sprintf(
            'SELECT priority FROM %s WHERE priority IS NOT NULL GROUP BY priority',
            self::TABLE_NAME
        )
        );

        foreach ($priorityNumbers as $priorityNumber) {
            $priorities[$priorityNumber] = $priorityNames[$priorityNumber];
        }

        return $priorities;
    }
}
