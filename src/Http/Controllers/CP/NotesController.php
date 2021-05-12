<?php

namespace Michavie\Bearhub\Http\Controllers\CP;

use Exception;
use Illuminate\Routing\Controller;
use Michavie\Bearhub\Actions\SyncNotesAction;

class NotesController extends Controller
{
    public function sync(SyncNotesAction $syncNotesAction)
    {
        try {
            $syncedEntries = $syncNotesAction->execute();

            $syncedTitles = $syncedEntries
                ->map(fn ($entries, $bearParentTag) => $entries->map(fn ($entry) => "#{$bearParentTag}: {$entry->title}"))
                ->flatten()
                ->toArray();

            return back()
                ->with('syncedTitles', $syncedTitles)
                ->with('success', __('Notes synced: :amount', [
                    'amount' => $syncedEntries->count(),
                ]));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
