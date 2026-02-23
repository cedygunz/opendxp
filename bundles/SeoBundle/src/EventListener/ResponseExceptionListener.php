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

namespace OpenDxp\Bundle\SeoBundle\EventListener;

use Doctrine\DBAL\Connection;
use OpenDxp;
use OpenDxp\Bundle\CoreBundle\EventListener\Traits\OpenDxpContextAwareTrait;
use OpenDxp\Bundle\SeoBundle\OpenDxpSeoBundle;
use OpenDxp\Http\Exception\ResponseException;
use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
class ResponseExceptionListener implements EventSubscriberInterface
{
    use OpenDxpContextAwareTrait;

    public function __construct(
        protected Connection $db,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // run with high priority before handling real errors
            KernelEvents::EXCEPTION => ['onKernelException', 64],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!OpenDxpSeoBundle::isInstalled()) {
            return;
        }

        $exception = $event->getThrowable();

        // handle ResponseException (can be used from any context)
        if ($exception instanceof ResponseException) {
            return;
        }

        // further checks are only valid for default context
        $request = $event->getRequest();
        if ($this->matchesOpenDxpContext($request, OpenDxpContextResolver::CONTEXT_DEFAULT)) {
            if (OpenDxp::inDebugMode()) {
                return;
            }

            $exception = $event->getThrowable();

            $statusCode = 500;

            if ($exception instanceof HttpExceptionInterface) {
                $statusCode = $exception->getStatusCode();
            }

            $this->logToHttpErrorLog($event->getRequest(), $statusCode);
        }
    }

    protected function logToHttpErrorLog(Request $request, int $statusCode): void
    {
        $uri = $request->getUri();
        $exists = $this->db->fetchOne('SELECT date FROM http_error_log WHERE uri = ?', [$uri]);
        if ($exists) {
            $this->db->executeStatement(
                'UPDATE http_error_log SET `count` = `count` + 1, date = ? WHERE uri = ?',
                [time(), $uri]
            );
        } else {
            $this->db->insert('http_error_log', [
                'uri' => $uri,
                'code' => $statusCode,
                'parametersGet' => serialize($_GET),
                'date' => time(),
                'count' => 1,
            ]);
        }
    }
}
