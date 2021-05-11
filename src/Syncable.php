<?php

namespace Michavie\Bearhub;

use Exception;

class Syncable
{
    public string $bearParentTag;
    public string $statamicCollection;
    public ?string $statamicTaxonomyField;

    public function __construct(string $bearParentTag, string $statamicCollection, ?string $statamicTaxonomyField)
    {
        $this->bearParentTag = $bearParentTag;
        $this->statamicCollection = $statamicCollection;
        $this->statamicTaxonomyField = $statamicTaxonomyField;
    }

    public static function fromConfig(string $bearParentTag, array $statamicProperties): static
    {
        throw_unless($bearParentTag, Exception::class, 'BearHub: Configuration error with syncables - Bear Parent Tag');
        throw_unless(isset($statamicProperties['collection']), Exception::class, 'BearHub: Configuration error with syncables - Statamic Collection');

        return new static($bearParentTag, $statamicProperties['collection'], $statamicProperties['taxonomy']);
    }
}
