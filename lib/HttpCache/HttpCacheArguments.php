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

final class HttpCacheArguments
{
    /**
     * Pass as a save() argument to suppress cache invalidation for that operation.
     *
     * Example: $document->save([HttpCacheArguments::SKIP_INVALIDATION => true])
     */
    public const string SKIP_INVALIDATION = 'skipHttpCacheInvalidation';
}
