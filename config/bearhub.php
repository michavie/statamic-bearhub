<?php

return [
    'syncables' => collect(explode(';', env('BEARHUB_SYNCABLES')))
        ->flatMap(function ($syncableSetting) {
            $syncable = explode('>', $syncableSetting);
            return !empty($syncable[0]) && !empty($syncable[1])
                ? [$syncable[0] => $syncable[1]]
                : null;
        })
        ->filter()
        ->toArray(),
];
