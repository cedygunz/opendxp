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

namespace OpenDxp;

/**
 * Common date format constants used throughout the OpenDXP application.
 *
 * This class provides standardized PHP date format strings to ensure consistency
 * across database persistence, API responses, and UI rendering.
 *
 * @api
 */
class DateFormat
{
    /** @section Database and Display */

    /**
     * Short standard date.
     *
     * @example 2024-04-21
     */
    public const string DATE = 'Y-m-d';

    /**
     * ISO-8601-like standard datetime with second precision, but without timezone information.
     *
     * @example 2024-04-21 14:30:45
     */
    public const string DATETIME = 'Y-m-d H:i:s';

    /** @section Presentation & UI */

    /**
     * User-facing short datetime representation.
     *
     * @example 2024-04-21 14:30
     */
    public const string DATETIME_SHORT = 'Y-m-d H:i';

    /**
     * User-facing short time representation.
     *
     * @example 14:30
     */
    public const string TIME = 'H:i';

    /** @section Standards & Protocols */

    /**
     * ISO 8601 standard (Alias for 'c').
     *
     * @see https://www.php.net/manual/en/datetime.format.php
     *
     * @example 2024-04-21T14:30:45+00:00
     */
    public const string ISO_8601 = 'c';

    /**
     * ISO 8601 with explicit offset.
     *
     * @example 2024-04-21T14:30:45+0000
     */
    public const string ISO_8601_TIMEZONE = 'Y-m-d\TH:i:sO';

    /**
     * RFC 2822 / 5322 format for Email/Internet headers (Alias for 'r').
     *
     * @see https://www.php.net/manual/en/datetime.format.php
     *
     * @example Mon, 21 Apr 2024 14:30:45 +0000
     */
    public const string RFC_2822 = 'r';

    /**
     * RFC 1123 format for HTTP headers (Date, Expires, Last-Modified, etc.).
     *
     * @see https://www.ietf.org/rfc/rfc1123
     *
     * @example Mon, 21 Apr 2024 14:30:45 GMT
     */
    public const string RFC_1123 = 'D, d M Y H:i:s T';

    /** @section Storage & Filesystem */

    /**
     * Schema or table suffix for monthly archiving.
     *
     * @example 2024_04
     */
    public const string ARCHIVE_DATE = 'Y_m';

    /**
     * Directory nesting for date-based asset and object storage.
     *
     * @example 2024/04
     */
    public const string FOLDER_DATE = 'Y/m';

    /**
     * Full directory pathing for storage.
     *
     * @example /2024/04/21/
     */
    public const string FILEPATH_DATE = '/Y/m/d/';

    /**
     * Delimiter-free date for extra safe filename generation.
     *
     * @example 20240421
     */
    public const string FILENAME = 'Ymd';

    /** @section Utilities & Components */

    /**
     * Unix timestamp (seconds since epoch).
     *
     * @example 1713699045
     */
    public const string UNIX_TIMESTAMP = 'U';

    /**
     * 24-hour component (00-23).
     */
    public const string COMPONENT_HOUR = 'H';

    /**
     * Minute component (00-59).
     */
    public const string COMPONENT_MINUTE = 'i';
}
