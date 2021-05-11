<?php

namespace Michavie\Bearhub\Commands;

use Illuminate\Console\Command;
use Michavie\Bearhub\Actions\SyncNotesAction;

class SyncBearHubCommand extends Command
{
    protected $signature = 'bearhub:sync';

    protected $description = 'Syncs Bear Notes with your Statamic Entries.';

    public function handle(SyncNotesAction $syncNotesAction)
    {
        $syncNotesAction->execute();
    }
}
