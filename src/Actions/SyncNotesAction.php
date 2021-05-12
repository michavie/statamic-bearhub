<?php

namespace Michavie\Bearhub\Actions;

use Exception;
use Statamic\Facades\User;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Michavie\Bearhub\Syncable;
use Statamic\Facades\Taxonomy;
use Illuminate\Support\Collection;
use Michavie\Bearhub\BearEntryField;
use Michavie\Bearhub\Models\BearTag;
use Michavie\Bearhub\Models\BearNote;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Entry as EntryContract;

class SyncNotesAction
{
    public function execute(): Collection
    {
        return collect(config('bearhub.syncables'))
            ->map(fn ($statamicProperties, $bearParentTag) => Syncable::fromConfig($bearParentTag, $statamicProperties))
            ->mapWithKeys(function (Syncable $syncable) {
                $syncedEntries = $this->getBearNotesFrom($syncable->bearParentTag)
                    ->map(fn (BearNote $bearNote) => $this->syncEntry($syncable, $bearNote))
                    ->filter();

                return $syncedEntries->isNotEmpty() ? [$syncable->bearParentTag => $syncedEntries] : [];
            })
            ->filter();
    }

    private function syncEntry(Syncable $syncable, BearNote $bearNote): ?EntryContract
    {
        $entry = $this->findEntryFor($bearNote, $syncable->statamicCollection);
        $isNew = is_null($entry);
        $author = User::findByEmail($authorEmail = config('bearhub.author-email')) ?? User::current();
        $shouldPublish = !$bearNote->trashed && !$bearNote->archived && $bearNote->hasPublishedActionTag();
        $shouldUpdate = $isNew || $entry->{BearEntryField::NoteChecksum} !== $bearNote->checksum;

        throw_unless($author, Exception::class, "BearHub: Did not find user with configured email {$authorEmail}. Be sure you have set the 'BEARHUB_AUTHOR_EMAIL' env variable.");

        return $shouldUpdate
            ? $this->saveEntry($isNew, $syncable, $entry ?? Entry::make(), $bearNote, $author, $shouldPublish)
            : null;
    }

    private function getBearNotesFrom(string $bearTagTitle): Collection
    {
        throw_unless($bearTag = BearTag::whereTitle($bearTagTitle)->first(), Exception::class, "BearHub: Did not find any notes with Bear tag '#{$bearTagTitle}'.");

        return $bearTag->notes;
    }

    private function findEntryFor(BearNote $bearNote, string $collection): ?EntryContract
    {
        return Entry::query()
            ->where('collection', $collection)
            ->where(BearEntryField::NoteId, $bearNote->id)
            ->first();
    }

    private function saveEntry(bool $isNew, Syncable $syncable, EntryContract $entry, BearNote $bearNote, UserContract $author, bool $published = true): EntryContract
    {
        $entry
            ->collection($syncable->statamicCollection)
            ->published($published)
            ->updateLastModified($author)
            ->data($this->getEntryData($syncable, $bearNote, $author));

        if ($isNew || config('bearhub.update-slugs')) $entry->slug(Str::slug($bearNote->title));
        if ($isNew) $entry->date($bearNote->created_at);
        if (config('bearhub.update-dates')) $entry->date($bearNote->modified_at);

        $entry->save();

        return $entry;
    }

    private function getEntryData(Syncable $syncable, BearNote $bearNote, UserContract $author): array
    {
        $data = [
            'title' => $bearNote->title,
            'author' => $author->id(),
            'content' => $bearNote->content,
            BearEntryField::NoteId => $bearNote->id,
            BearEntryField::NoteChecksum => $bearNote->checksum,
        ];

        if ($syncable->statamicTaxonomyField) {
            throw_unless(Taxonomy::handleExists($syncable->statamicTaxonomyField), Exception::class, "BearHub: Taxonomy '{$syncable->statamicTaxonomyField}' does not exist.");
            $data[$syncable->statamicTaxonomyField] = $bearNote->getCleanTags($syncable->bearParentTag, $syncable->statamicTaxonomyField);
        }

        return $data;
    }
}
