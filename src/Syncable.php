<?php

namespace Michavie\Bearhub;

use Exception;

class Syncable
{
    public string $bearParentTag;
    public string $collection;
    public string $titleField;
    public string $contentField;
    public ?string $taxonomyField;

    private function __construct(string $bearParentTag, string $collection, string $titleField, string $contentField, ?string $statamicTaxonomyField)
    {
        $this->bearParentTag = $bearParentTag;
        $this->collection = $collection;
        $this->titleField = $titleField;
        $this->contentField = $contentField;
        $this->taxonomyField = $statamicTaxonomyField;
    }

    public static function fromConfig(string $bearParentTag, array $statamicProperties): static
    {
        throw_unless($bearParentTag, Exception::class, 'BearHub: Configuration error with syncables - Bear Parent Tag');
        throw_unless(isset($statamicProperties['collection']), Exception::class, 'BearHub: Configuration error with syncables - Statamic Collection');

        return new static(
            $bearParentTag,
            $statamicProperties['collection'],
            $statamicProperties['fields']['title'] ?? 'title',
            $statamicProperties['fields']['content'] ?? 'content',
            $statamicProperties['fields']['taxonomy'] ?? null,
        );
    }
}
