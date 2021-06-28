<?php

namespace Michavie\Bearhub\Commands;

use Exception;
use Illuminate\Console\Command;
use Michavie\Bearhub\SyncResult;
use Michavie\Bearhub\Actions\SyncNotesAction;

class SyncBearHubCommand extends Command
{
    protected $signature = 'bearhub:sync';

    protected $description = 'Syncs Bear Notes with your Statamic Entries.';

    public function handle(SyncNotesAction $syncNotesAction)
    {
        try {
            $syncedEntries = $syncNotesAction->execute();

            $syncedTitles = $syncedEntries
                ->map(fn ($entries, $bearParentTag) => $entries->map(fn (SyncResult $result) => "{$result->getStateIcon()}  #{$bearParentTag}: {$result->title}"))
                ->flatten()
                ->each(fn ($output) => $this->info($output));

            $this->info("Notes synced: {$syncedTitles->count()}");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
