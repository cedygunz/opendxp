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

use OpenDxp\Model\Document\Editable\EditableInterface;
use OpenDxp\Model\Document\PageSnippet;
use OpenDxp\Templating\Renderer\EditableRenderer;
use OpenDxp\Twig\TokenParser\BlockParser;
use OpenDxp\Twig\TokenParser\ManualBlockParser;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Registers the wildcard fallback `opendxp_*` for custom editables (e.g. from third-party bundles)
 * and the token parsers.
 *
 * Built-in editable types are additionally registered with explicit signatures in DocumentEditableRenderExtension.
 * Twig resolves exact matches before wildcards, so both coexist without conflict.
 *
 * @internal
 */
class DocumentEditableExtension extends AbstractExtension
{
    public function __construct(protected EditableRenderer $editableRenderer)
    {
    }

    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('opendxp_*', $this->renderEditable(...), [
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    #[Override]
    public function getTokenParsers(): array
    {
        return [
            new BlockParser(),
            new ManualBlockParser(),
        ];
    }

    public function renderEditable(array $context, string $type, string $name, array $options = []): string|EditableInterface
    {
        $document = $context['document'] ?? null;
        if (!($document instanceof PageSnippet)) {
            return '';
        }

        return $this->editableRenderer->render(
            $document,
            $type,
            $name,
            $options,
            $context['editmode'] ?? false
        );
    }
}
