<?php

namespace Michavie\Bearhub\Actions;

use Exception;
use Statamic\Facades\Git;
use Statamic\Facades\User;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Michavie\Bearhub\Syncable;
use Statamic\Facades\Taxonomy;
use Michavie\Bearhub\SyncResult;
use Illuminate\Support\Collection;
use Michavie\Bearhub\BearEntryField;
use Michavie\Bearhub\Models\BearTag;
use Statamic\Facades\AssetContainer;
use Michavie\Bearhub\Models\BearNote;
use Michavie\Bearhub\SyncResultState;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Entry as EntryContract;

class SyncNotesAction
{
    public function execute(): Collection
    {
        if (config('bearhub.git.auto-commit') && Git::statuses() !== null) {
            throw new Exception('BearHub: Auto-commit is enabled. Please commit pending changes before synchronization.');
        }

        $results = collect(config('bearhub.syncables'))
            ->map(fn ($statamicProperties, $bearParentTag) => Syncable::fromConfig($bearParentTag, $statamicProperties))
            ->mapWithKeys(function (Syncable $syncable) {
                $syncedEntries = $this->getBearNotesFrom($syncable->bearParentTag)
                    ->map(fn (BearNote $bearNote) => $this->syncEntry($syncable, $bearNote))
                    ->filter();

                return $syncedEntries->isNotEmpty() ? [$syncable->bearParentTag => $syncedEntries] : [];
            })
            ->filter();

        if ($results->isNotEmpty() && config('bearhub.git.auto-commit')) {
            $this->commitToVersionHistory();
        }

        return $results;
    }

    private function syncEntry(Syncable $syncable, BearNote $bearNote): ?SyncResult
    {
        $entry = $this->findEntryFor($bearNote, $syncable->collection);
        $isNew = is_null($entry);
        $author = User::findByEmail($authorEmail = config('bearhub.author-email')) ?? User::current();
        $isTrashed = $bearNote->trashed && is_null($entry);
        $shouldUpdate = $isNew || $bearNote->hasContentOrStateChanges($entry->{BearEntryField::NoteChecksum});

        if (!$shouldUpdate || $isTrashed) {
            return null;
        }

        throw_unless($author, Exception::class, "BearHub: Did not find user with configured email {$authorEmail}. Be sure you have set the 'BEARHUB_AUTHOR_EMAIL' env variable.");

        return $bearNote->trashed
            ? $this->deleteEntry($syncable, $entry)
            : $this->saveEntry($isNew, $syncable, $entry ?? Entry::make(), $bearNote, $author);
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
            ->where(BearEntryField::NoteId, $bearNote->getUniqueId())
            ->first();
    }

    public function deleteEntry(Syncable $syncable, EntryContract $entry): SyncResult
    {
        $result = new SyncResult($entry->{$syncable->titleField}, SyncResultState::Trashed);

        Entry::delete($entry);

        return $result;
    }

    private function saveEntry(bool $isNew, Syncable $syncable, EntryContract $entry, BearNote $bearNote, UserContract $author): SyncResult
    {
        $entry
            ->collection($syncable->collection)
            ->published($bearNote->hasPublishedActionTag())
            ->updateLastModified($author)
            ->data($this->getEntryData($syncable, $bearNote, $author));

        if ($isNew || config('bearhub.update-slugs')) {
            $entry->slug(Str::slug($bearNote->title));
        }

        if ($isNew) {
            $entry->date($bearNote->created_at);
        }

        if (config('bearhub.update-dates')) {
            $entry->date($bearNote->modified_at);
        }

        $entry->save();

        return new SyncResult($entry->{$syncable->titleField}, $entry->published());
    }

    private function getEntryData(Syncable $syncable, BearNote $bearNote, UserContract $author): array
    {
        $content = $this->getContentWithImages($bearNote);

        $data = [
            $syncable->titleField => $bearNote->title,
            $syncable->contentField => $content,
            'author' => $author->id(),
            BearEntryField::NoteId => $bearNote->getUniqueId(),
            BearEntryField::NoteChecksum => $bearNote->checksum,
        ];

        if ($syncable->taxonomyField) {
            throw_unless(Taxonomy::handleExists($syncable->taxonomyField), Exception::class, "BearHub: Taxonomy '{$syncable->taxonomyField}' does not exist.");
            $data[$syncable->taxonomyField] = $bearNote->getCleanTags($syncable->bearParentTag, $syncable->taxonomyField);
        }

        return $data;
    }

    private function getContentWithImages(BearNote $bearNote): string
    {
        return $bearNote->getContentAndStoreImages(function ($originalPath, $newFileName) {
            $basePath = config('bearhub.storage.path');
            $path = "{$basePath}/{$newFileName}";
            $containerHandle = config('bearhub.storage.container');

            throw_unless($container = AssetContainer::findByHandle($containerHandle), Exception::class, "BearHub: Did not find asset container '{$containerHandle}'.");

            $asset = $container->makeAsset($basePath);
            $asset->disk()->put($path, file_get_contents($originalPath));
            $asset->path($path);
            $asset->save();

            return $asset->url();
        });
    }

    private function commitToVersionHistory(): void
    {
        if (!$message = config('bearhub.git.commit-message')) {
            return;
        }

        $originalAutoPushConfigValue = config('statamic.git.push');
        $shouldAutoPush = config('bearhub.git.auto-push') || $originalAutoPushConfigValue;

        config(['statamic.git.push' => $shouldAutoPush]);

        Git::commit($message);

        config(['statamic.git.push' => $originalAutoPushConfigValue]);
    }
}
