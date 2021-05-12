<?php

namespace Michavie\Bearhub\Models;

use Carbon\Carbon;
use Statamic\Facades\Term;
use Illuminate\Support\Str;
use Statamic\Facades\Taxonomy;
use Illuminate\Database\Eloquent\Model;
use Michavie\Bearhub\Traits\ResolvesNoteTagPivot;
use Michavie\Bearhub\Traits\UsesBearsDatabaseConnection;

class BearNote extends Model
{
    use UsesBearsDatabaseConnection, ResolvesNoteTagPivot;

    protected $appends = ['content', 'trashed', 'archived', 'created_at', 'modified_at'];

    protected int $cocoaCoreDataTimestampSecondsToUnix = 978307200;

    private static array $sqlSelectFields = [
        'ZTITLE as title',
        'ZTEXT as raw_content',
        'ZTRASHED as trashed',
        'ZARCHIVED as archived',
        'ZCREATIONDATE as created_at',
        'ZMODIFICATIONDATE as modified_at',
    ];

    public function tags()
    {
        return $this->belongsToMany(
            BearTag::class,
            $this->getNoteTagPivotTable(),
            $this->getNoteColumn(),
            $this->getTagColumn()
        );
    }

    public function hasTag(string $tagName): bool
    {
        return $this->tags->pluck('title')->contains($tagName);
    }

    public function hasPublishedActionTag(): bool
    {
        return $this->hasTag(Str::remove('#', config('bearhub.action-tags.published')));
    }

    public function getCleanTags(string $bearParentTag, string $statamicTaxonomy): array
    {
        return $this->tags
            ->pluck('title')
            ->reject(fn ($tag) => $tag === $bearParentTag)
            ->map(fn ($tag) => Str::replaceFirst("{$bearParentTag}/", '', $tag))
            ->diff(collect(config('bearhub.action-tags'))->values())
            ->map(fn ($tag) => Term::findBySlug($tag, $statamicTaxonomy) ? $tag : null)
            ->filter()
            ->values()
            ->toArray();
    }

    public static function searchByTitle($query)
    {
        return static::where('title', 'like', '%'.$query.'%')->get();
    }

    public function getContentAndStoreImages($callback)
    {
        // Check the note's content for images:
        preg_match_all('/\[image:.*\]/', $this->content, $matches);

        $replaceStack = [];

        foreach ($matches[0] as $match) {
            if (empty($match)) continue;

            $originalPath = Str::after(Str::beforeLast($match, ']'), '[image:');
            $originalFullPath = static::getBearPath().'/Local Files/Note Images/'.$originalPath;

            $newFileName = crc32($originalPath).'.'.Str::afterLast($originalPath, '.');

            $newFile = $callback($originalFullPath, $newFileName);

            $replaceStack[$match] = '![]('.$newFile.')';
        }

        return str_replace(array_keys($replaceStack), array_values($replaceStack), $this->content);
    }

    public function getContentAttribute(): string
    {
        return trim(Str::between($this->raw_content, $this->title, config('bearhub.tag-separator')));
    }

    public function getTrashedAttribute(): bool
    {
        return (bool) $this->attributes['trashed'];
    }

    public function getArchivedAttribute(): bool
    {
        return (bool) $this->attributes['archived'];
    }

    public function getChecksumAttribute(): string
    {
        return crc32($this->raw_content);
    }

    public function getCreatedAtAttribute(): Carbon
    {
        $cocoaCoreDataTimestamp = $this->attributes['created_at'] ?? null;

        return $cocoaCoreDataTimestamp
            ? Carbon::createFromTimestampUTC((float) $cocoaCoreDataTimestamp + $this->cocoaCoreDataTimestampSecondsToUnix)
            : Carbon::now();
    }

    public function getModifiedAtAttribute(): Carbon
    {
        $cocoaCoreDataTimestamp = $this->attributes['modified_at'] ?? null;

        return $cocoaCoreDataTimestamp
            ? Carbon::createFromTimestampUTC((float) $cocoaCoreDataTimestamp + $this->cocoaCoreDataTimestampSecondsToUnix)
            : Carbon::now();
    }

    protected static function boot()
    {
        parent::boot();

        $sqlSelectFields = implode(', ', static::$sqlSelectFields);

        static::addGlobalScope(function ($builder) use ($sqlSelectFields) {
            $builder->fromRaw("(select Z_PK as id, {$sqlSelectFields}, Z_ENT as pivot_column_id from ZSFNOTE) as bear_notes");
        });
    }
}
