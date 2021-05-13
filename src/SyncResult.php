<?php

namespace Michavie\Bearhub;

use Michavie\Bearhub\SyncResultState;

class SyncResult
{
    public string $title;
    public int $syncState;

    public function __construct(string $title, int $syncState)
    {
        $this->title = $title;
        $this->syncState = $syncState;
    }

    public function getStateIcon(): string
    {
        return match ($this->syncState) {
            SyncResultState::Pending => '🚧',
            SyncResultState::Published => '✅',
            SyncResultState::Trashed => '🗑',
        };
    }
}
