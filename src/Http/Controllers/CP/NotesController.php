<?php

namespace Michavie\Bearhub\Http\Controllers\CP;

use Exception;
use Michavie\Bearhub\SyncResult;
use Illuminate\Routing\Controller;
use Michavie\Bearhub\Actions\SyncNotesAction;

class NotesController extends Controller
{
    public function sync(SyncNotesAction $syncNotesAction)
    {
        try {
            $syncedEntries = $syncNotesAction->execute();

            $syncedTitles = $syncedEntries
                ->map(fn ($entries, $bearParentTag) => $entries->map(fn (SyncResult $result) => "{$result->getStateIcon()} #{$bearParentTag}: {$result->title}"))
                ->flatten();

            return back()
                ->with('syncedTitles', $syncedTitles->toArray())
                ->with('success', __('Notes synced: :amount', [
                    'amount' => $syncedTitles->count(),
                ]));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
