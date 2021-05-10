<?php

namespace Michavie\Bearhub\Commands;

use Exception;
use Carbon\Carbon;
use Statamic\Facades\User;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Illuminate\Console\Command;
use Michavie\Bearhub\BearEntryField;
use Michavie\Bearhub\Models\BearNote;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Entry as EntryContract;

class SyncBearHubCommand extends Command
{
    protected $signature = 'bearhub:sync';

    protected $description = 'Syncs Bear Notes with your Statamic Entries.';

    public function handle()
    {
        collect(config('bearhub.syncables'))
            ->each(function ($statamicCollection, $bearTagTitle) {
                $bearTag = \Michavie\Bearhub\Models\BearTag::whereTitle($bearTagTitle)->first();

                throw_unless($bearTag, Exception::class, "BearHub: Did not find any notes with Bear tag '#{$bearTagTitle}'.");

                $bearTag->notes->each(function (BearNote $bearNote) use ($statamicCollection) {
                    $entry = $this->findEntryFor($bearNote, $statamicCollection) ?? Entry::make();
                    $author = User::findByEmail($authorEmail = config('bearhub.author-email'));
                    $shouldPublish = !$bearNote->trashed && !$bearNote->archived;
                    $shouldUpdate = $entry->{BearEntryField::NoteChecksum} !== $bearNote->checksum || $shouldPublish !== $entry->published();

                    throw_unless($author, Exception::class, "BearHub: Did not find user with configured email {$authorEmail}. Be sure you have set the 'BEARHUB_AUTHOR_EMAIL' env variable.");

                    if ($shouldUpdate) {
                        $this->saveEntry($entry, $statamicCollection, $bearNote, $author, $shouldPublish);
                    }
                });
            });
    }

    private function findEntryFor(BearNote $bearNote, string $collection): ?EntryContract
    {
        return Entry::query()
            ->where('collection', $collection)
            ->where(BearEntryField::NoteId, $bearNote->id)
            ->first();
    }

    private function saveEntry(EntryContract $entry, string $collection, BearNote $bearNote, UserContract $author, bool $published = true): void
    {
        $entry
            ->collection($collection)
            ->slug($entry->slug() ?: Str::slug($bearNote->title))
            ->date(Carbon::now())
            ->published($published)
            ->data([
                'title' => $bearNote->title,
                'author' => $author->id(),
                BearEntryField::NoteId => $bearNote->id,
                BearEntryField::NoteChecksum => $bearNote->checksum,
                'content' => $bearNote->content,
            ])
            ->save();
    }
}
