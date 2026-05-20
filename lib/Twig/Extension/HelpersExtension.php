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

use Exception;
use OpenDxp\Document;
use OpenDxp\Twig\Extension\Templating\OpenDxpUrl;
use OpenDxp\Video;
use Symfony\Component\Mime\MimeTypes;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Attribute\AsTwigTest;

/**
 * @internal
 */
class HelpersExtension
{
    public function __construct(private readonly OpenDxpUrl $OpenDxpUrlHelper)
    {
    }

    #[AsTwigFilter('basename')]
    public function basenameFilter(string $value, string $suffix = ''): string
    {
        return basename($value, $suffix);
    }

    #[AsTwigFunction('opendxp_video_is_available')]
    public function isVideoAvailable(): bool
    {
        return Video::isAvailable();
    }

    #[AsTwigFunction('opendxp_document_is_available')]
    public function isDocumentAvailable(): bool
    {
        return Document::isAvailable();
    }

    #[AsTwigFunction('opendxp_file_exists')]
    public function fileExists(string $path): bool
    {
        return is_file($path);
    }

    #[AsTwigFunction('opendxp_file_extension')]
    public function getFileExtension(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    /**
     * @throws Exception
     */
    #[AsTwigFunction('opendxp_image_version_preview')]
    public function getImageVersionPreview(string $file): string
    {
        $thumbnail = OPENDXP_SYSTEM_TEMP_DIRECTORY . '/image-version-preview-' . uniqid() . '.png';
        $convert = \OpenDxp\Image::getInstance();
        $convert->load($file);
        $convert->contain(500, 500);
        $convert->save($thumbnail, 'png');

        $dataUri = 'data:image/png;base64,' . base64_encode(file_get_contents($thumbnail));
        unlink($thumbnail);
        unlink($file);

        return $dataUri;
    }

    /**
     * @throws Exception
     */
    #[AsTwigFunction('opendxp_asset_version_preview')]
    public function getAssetVersionPreview(string $file): string
    {
        $dataUri = 'data:'.MimeTypes::getDefault()->guessMimeType($file).';base64,'.base64_encode(file_get_contents($file));
        unlink($file);

        return $dataUri;
    }

    /**
     * @throws Exception
     */
    #[AsTwigFunction('opendxp_breach_attack_random_content', isSafe: ['html'])]
    public function breachAttackRandomContent(): string
    {
        $length = 50;
        $randomData = random_bytes($length);

        return '<!--'
            . substr(
                base64_encode($randomData),
                0,
                ord($randomData[$length - 1]) % 32
            )
            . '-->';
    }

    #[AsTwigFunction('opendxp_url')]
    public function opendxpUrl(array $urlOptions = [], ?string $name = null, bool $reset = false, bool $encode = true, bool $relative = false): string
    {
        return ($this->OpenDxpUrlHelper)($urlOptions, $name, $reset, $encode, $relative);
    }

    #[AsTwigTest('instanceof')]
    public function isInstanceOf(mixed $object, string $class): bool
    {
        return $object instanceof $class;
    }
}