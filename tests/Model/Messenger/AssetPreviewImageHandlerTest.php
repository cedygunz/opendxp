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

namespace OpenDxp\Tests\Model\Messenger;

use OpenDxp\Exception\ThumbnailGenerationFailedException;
use OpenDxp\Messenger\AssetPreviewImageMessage;
use OpenDxp\Messenger\Handler\AssetPreviewImageHandler;
use OpenDxp\Model\Asset;
use OpenDxp\Tests\Support\Test\ModelTestCase;
use OpenDxp\Tests\Support\Util\TestHelper;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\Acknowledger;
use Throwable;

class AssetPreviewImageHandlerTest extends ModelTestCase
{
    public function testPreviewImageIsGeneratedForValidImage(): void
    {
        $asset = TestHelper::createImageAsset();

        $previewConfig = Asset\Image\Thumbnail\Config::getPreviewConfig();
        $this->assertFalse($asset->getThumbnail($previewConfig)->exists());

        $error = $this->handleMessage(new AssetPreviewImageMessage($asset->getId()));

        $this->assertNull($error);
        $this->assertTrue($asset->getThumbnail($previewConfig)->exists());
    }

    public function testFailedPreviewImageGenerationIsNacked(): void
    {
        $asset = TestHelper::createImageAsset('', 'this-is-not-a-valid-image');

        $error = $this->handleMessage(new AssetPreviewImageMessage($asset->getId()));

        $this->assertInstanceOf(ThumbnailGenerationFailedException::class, $error);
        $this->assertFalse($asset->getThumbnail(Asset\Image\Thumbnail\Config::getPreviewConfig())->exists());
    }

    private function handleMessage(AssetPreviewImageMessage $message): ?Throwable
    {
        $error = null;
        $ack = new Acknowledger(
            AssetPreviewImageHandler::class,
            function (?Throwable $e) use (&$error): void {
                $error = $e;
            }
        );

        $handler = new AssetPreviewImageHandler(new NullLogger());
        $handler($message, $ack);
        $handler->flush(true);

        return $error;
    }
}
