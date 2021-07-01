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
        switch ($this->syncState) {
            case SyncResultState::Pending: return '🚧';
            case SyncResultState::Published: return '✅';
            case SyncResultState::Trashed: return '🗑';
            default: return '❓';
        }
    }
}
