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

use OpenDxp\Twig\Extension\Templating\HeadLink;
use OpenDxp\Twig\Extension\Templating\HeadMeta;
use OpenDxp\Twig\Extension\Templating\HeadScript;
use OpenDxp\Twig\Extension\Templating\HeadStyle;
use OpenDxp\Twig\Extension\Templating\HeadTitle;
use OpenDxp\Twig\Extension\Templating\InlineScript;
use OpenDxp\Twig\Extension\Templating\Placeholder;
use OpenDxp\Twig\Extension\Templating\Placeholder\Container;
use Twig\Attribute\AsTwigFunction;

/**
 * @internal
 */
class HeaderExtension
{
    public function __construct(
        private readonly HeadLink $headLink,
        private readonly HeadMeta $headMeta,
        private readonly HeadScript $headScript,
        private readonly HeadStyle $headStyle,
        private readonly HeadTitle $headTitle,
        private readonly InlineScript $inlineScript,
        private readonly Placeholder $placeholder
    ) {
    }

    #[AsTwigFunction('opendxp_head_link', isSafe: ['html'])]
    public function renderHeadLink(?array $attributes = null, string $placement = Container::APPEND): HeadLink
    {
        return ($this->headLink)($attributes, $placement);
    }

    #[AsTwigFunction('opendxp_head_meta', isSafe: ['html'])]
    public function renderHeadMeta(?string $content = null, ?string $keyValue = null, string $keyType = 'name', array $modifiers = [], string $placement = Container::APPEND): HeadMeta
    {
        return ($this->headMeta)($content, $keyValue, $keyType, $modifiers, $placement);
    }

    #[AsTwigFunction('opendxp_head_script', isSafe: ['html'])]
    public function renderHeadScript(string $mode = HeadScript::FILE, ?string $spec = null, string $placement = 'APPEND', array $attrs = [], string $type = 'text/javascript'): HeadScript
    {
        return ($this->headScript)($mode, $spec, $placement, $attrs, $type);
    }

    #[AsTwigFunction('opendxp_head_style', isSafe: ['html'])]
    public function renderHeadStyle(?string $content = null, string $placement = 'APPEND', array|string $attributes = []): HeadStyle
    {
        return ($this->headStyle)($content, $placement, $attributes);
    }

    #[AsTwigFunction('opendxp_head_title', isSafe: ['html'])]
    public function renderHeadTitle(?string $title = null, ?string $setType = null): HeadTitle
    {
        return ($this->headTitle)($title, $setType);
    }

    #[AsTwigFunction('opendxp_inline_script', isSafe: ['html'])]
    public function renderInlineScript(string $mode = HeadScript::FILE, ?string $spec = null, string $placement = 'APPEND', array $attrs = [], string $type = 'text/javascript'): InlineScript
    {
        return ($this->inlineScript)($mode, $spec, $placement, $attrs, $type);
    }

    #[AsTwigFunction('opendxp_placeholder', isSafe: ['html'])]
    public function renderPlaceholder(?string $containerName = null): Container
    {
        return ($this->placeholder)($containerName);
    }
}