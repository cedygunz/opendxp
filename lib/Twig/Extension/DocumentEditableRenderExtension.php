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

use Generator;
use OpenDxp\Model\Document\Editable\BlockInterface;
use OpenDxp\Model\Document\Editable\EditableInterface;
use OpenDxp\Model\Document\PageSnippet;
use OpenDxp\Templating\Renderer\EditableRenderer;
use Twig\Attribute\AsTwigFunction;

/**
 * @internal
 */
class DocumentEditableRenderExtension
{
    public function __construct(protected EditableRenderer $editableRenderer)
    {
    }

    #[AsTwigFunction('opendxp_area', needsContext: true, isSafe: ['html'])]
    public function renderArea(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'area', $name, $options);
    }

    #[AsTwigFunction('opendxp_areablock', needsContext: true, isSafe: ['html'])]
    public function renderAreablock(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'areablock', $name, $options);
    }

    #[AsTwigFunction('opendxp_block', needsContext: true, isSafe: ['html'])]
    public function renderBlock(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'block', $name, $options);
    }

    #[AsTwigFunction('opendxp_checkbox', needsContext: true, isSafe: ['html'])]
    public function renderCheckbox(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'checkbox', $name, $options);
    }

    #[AsTwigFunction('opendxp_date', needsContext: true, isSafe: ['html'])]
    public function renderDate(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'date', $name, $options);
    }

    #[AsTwigFunction('opendxp_embed', needsContext: true, isSafe: ['html'])]
    public function renderEmbed(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'embed', $name, $options);
    }

    #[AsTwigFunction('opendxp_image', needsContext: true, isSafe: ['html'])]
    public function renderImage(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'image', $name, $options);
    }

    #[AsTwigFunction('opendxp_input', needsContext: true, isSafe: ['html'])]
    public function renderInput(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'input', $name, $options);
    }

    #[AsTwigFunction('opendxp_link', needsContext: true, isSafe: ['html'])]
    public function renderLink(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'link', $name, $options);
    }

    #[AsTwigFunction('opendxp_multiselect', needsContext: true, isSafe: ['html'])]
    public function renderMultiselect(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'multiselect', $name, $options);
    }

    #[AsTwigFunction('opendxp_numeric', needsContext: true, isSafe: ['html'])]
    public function renderNumeric(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'numeric', $name, $options);
    }

    #[AsTwigFunction('opendxp_pdf', needsContext: true, isSafe: ['html'])]
    public function renderPdf(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'pdf', $name, $options);
    }

    #[AsTwigFunction('opendxp_relation', needsContext: true, isSafe: ['html'])]
    public function renderRelation(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'relation', $name, $options);
    }

    #[AsTwigFunction('opendxp_relations', needsContext: true, isSafe: ['html'])]
    public function renderRelations(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'relations', $name, $options);
    }

    #[AsTwigFunction('opendxp_renderlet', needsContext: true, isSafe: ['html'])]
    public function renderRenderlet(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'renderlet', $name, $options);
    }

    #[AsTwigFunction('opendxp_scheduledblock', needsContext: true, isSafe: ['html'])]
    public function renderScheduledblock(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'scheduledblock', $name, $options);
    }

    #[AsTwigFunction('opendxp_select', needsContext: true, isSafe: ['html'])]
    public function renderSelect(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'select', $name, $options);
    }

    #[AsTwigFunction('opendxp_snippet', needsContext: true, isSafe: ['html'])]
    public function renderSnippet(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'snippet', $name, $options);
    }

    #[AsTwigFunction('opendxp_table', needsContext: true, isSafe: ['html'])]
    public function renderTable(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'table', $name, $options);
    }

    #[AsTwigFunction('opendxp_textarea', needsContext: true, isSafe: ['html'])]
    public function renderTextarea(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'textarea', $name, $options);
    }

    #[AsTwigFunction('opendxp_video', needsContext: true, isSafe: ['html'])]
    public function renderVideo(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'video', $name, $options);
    }

    #[AsTwigFunction('opendxp_wysiwyg', needsContext: true, isSafe: ['html'])]
    public function renderWysiwyg(array $context, string $name, array $options = []): string|EditableInterface
    {
        return $this->renderEditable($context, 'wysiwyg', $name, $options);
    }

    #[AsTwigFunction('opendxp_iterate_block')]
    public function getBlockIterator(BlockInterface $block): Generator
    {
        return $block->getIterator();
    }

    private function renderEditable(array $context, string $type, string $name, array $options = []): string|EditableInterface
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
