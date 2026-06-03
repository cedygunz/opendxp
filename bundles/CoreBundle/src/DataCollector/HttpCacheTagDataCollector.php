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

namespace OpenDxp\Bundle\CoreBundle\DataCollector;

use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use OpenDxp\HttpCache\Tag\CacheTag;
use OpenDxp\HttpCache\TraceableHttpCacheTagCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * @internal
 */
class HttpCacheTagDataCollector extends DataCollector
{
    public function __construct(
        private readonly TraceableHttpCacheTagCollector $collector,
        private readonly SymfonyResponseTagger $responseTagger,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $tracedTags = $this->deduplicateTags($this->collector->getCollectedTags());
        $tracedStrings = array_map(strval(...), $tracedTags);

        $this->data = [
            'tags'          => $tracedStrings,
            'count'         => count($tracedStrings),
            'types'         => $this->groupByType($tracedTags),
            'external_tags' => $this->collectExternalTags($response, $tracedStrings),
        ];

        $this->data['count'] += count($this->data['external_tags']);
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getName(): string
    {
        return 'opendxp.http_cache_tags';
    }

    /** @return string[] */
    public function getTags(): array
    {
        return $this->data['tags'] ?? [];
    }

    public function getCount(): int
    {
        return $this->data['count'] ?? 0;
    }

    /** @return array<string, string[]> */
    public function getTypes(): array
    {
        return $this->data['types'] ?? [];
    }

    /** @return string[] Tags added via FOSHttpCacheBundle directly */
    public function getExternalTags(): array
    {
        return $this->data['external_tags'] ?? [];
    }

    /**
     * @param CacheTag[] $tags
     * @return CacheTag[]
     */
    private function deduplicateTags(array $tags): array
    {
        $keyed = [];
        foreach ($tags as $tag) {
            $keyed[(string) $tag] ??= $tag;
        }

        return array_values($keyed);
    }

    /**
     * FOS has no public getTags() method, reading the response header is the only available approach.
     *
     * @param string[] $tracedStrings
     * @return string[]
     */
    private function collectExternalTags(Response $response, array $tracedStrings): array
    {
        $headerValue = $response->headers->get($this->responseTagger->getTagsHeaderName(), '');
        if ($headerValue === '') {
            return [];
        }

        $allTags = array_filter(array_map(trim(...), preg_split('/[\s,]+/', $headerValue) ?: []));
        $tracedSet = array_flip($tracedStrings);

        return array_values(array_filter($allTags, static fn (string $tag) => !isset($tracedSet[$tag])));
    }

    /**
     * @param CacheTag[] $tags
     * @return array<string, string[]>
     */
    private function groupByType(array $tags): array
    {
        $grouped = [];
        foreach ($tags as $tag) {
            $grouped[$tag->type->prefix()][] = (string) $tag;
        }

        return $grouped;
    }
}