<?php

namespace Michavie\Bearhub\Commands;

use Exception;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Illuminate\Console\Command;
use Michavie\Bearhub\BearEntryField;
use Michavie\Bearhub\Models\BearNote;

class SyncBearHubCommand extends Command
{
    protected $signature = 'bearhub:sync';

    protected $description = 'Syncs Bear Notes with your Statamic Entries.';

    public function handle()
    {

        collect(config('bearhub.syncables'))
        ->map(function ($statamicCollection, $bearTagTitle) {
            $bearTag = \Michavie\Bearhub\Models\BearTag::whereTitle($bearTagTitle)->first();

            throw_unless($bearTag, Exception::class, "BearHub: Did not find any notes with Bear tag #{$bearTagTitle}");

            $bearTag->notes->each(function ($bearNote) use ($statamicCollection) {
                $entry = $this->findEntryFor($bearNote, $statamicCollection);

                if (!$entry) {
                    $this->saveEntry(Entry::make(), $statamicCollection, $bearNote);
                    return;
                }

                // ...
            });
        });
    }

    private function findEntryFor(BearNote $bearNote, string $collection): ?Entry
    {
        return Entry::query()
            ->where('collection', $collection)
            ->where(BearEntryField::NoteId, $bearNote->id)
            ->first();
    }

    private function saveEntry(Entry $entry, string $collection, BearNote $bearNote): void
    {
        $entry
            ->collection($collection)
            ->slug(Str::slug($bearNote->title))
            ->date()
            ->set(BearEntryField::NoteId, $bearNote->id)
            ->set(BearEntryField::NoteChecksum, $bearNote->checksum)
            ->data([
                'title' => $bearNote->title,
                'content' => $bearNote->content
            ])
            ->save();
    }
}
