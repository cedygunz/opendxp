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

namespace OpenDxp\Twig\Extension;

use OpenDxp\Model\Document;
use Twig\Attribute\AsTwigTest;

/**
 * @internal
 */
class DocumentHelperExtensions
{
    #[AsTwigTest('opendxp_document')]
    public function isDocument(mixed $object): bool
    {
        return $object instanceof Document;
    }

    #[AsTwigTest('opendxp_document_email')]
    public function isDocumentEmail(mixed $object): bool
    {
        return $object instanceof Document\Email;
    }

    #[AsTwigTest('opendxp_document_folder')]
    public function isDocumentFolder(mixed $object): bool
    {
        return $object instanceof Document\Folder;
    }

    #[AsTwigTest('opendxp_document_hardlink')]
    public function isDocumentHardlink(mixed $object): bool
    {
        return $object instanceof Document\Hardlink;
    }

    #[AsTwigTest('opendxp_document_page')]
    public function isDocumentPage(mixed $object): bool
    {
        return $object instanceof Document\Page;
    }

    #[AsTwigTest('opendxp_document_link')]
    public function isDocumentLink(mixed $object): bool
    {
        return $object instanceof Document\Link;
    }

    #[AsTwigTest('opendxp_document_page_snippet')]
    public function isDocumentPageSnippet(mixed $object): bool
    {
        return $object instanceof Document\PageSnippet;
    }

    #[AsTwigTest('opendxp_document_snippet')]
    public function isDocumentSnippet(mixed $object): bool
    {
        return $object instanceof Document\Snippet;
    }
}
