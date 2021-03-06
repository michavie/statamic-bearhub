<?php

return [

    /**
     * Define the connection between Bear Parent Tag -> Statamic Collection you want to sync.
     *
     * Hint: BearHub makes use of Bear's nested tags. They are useful to create a hierarchical structure of your tags.
     * Learn more: https://bear.app/faq/Tags%20&%20Linking/Nested%20Tags/
     */
    'syncables' => [

        'myblog' /* Bear Parent Tag: e.g. mycompany, blog, website, ... */ => [
            'collection' => 'articles', // The name of your Statamic collection to be synced
            'fields' => [ // Define the blueprint fields of your collection
                'title' => 'mytitle', // Remove line to use default: 'title'
                'content' => 'mycontent', // Remove line to use default: 'content'
                'taxonomy' => 'topics', // Remove line to disable taxonomy term synchronization
            ],
        ],

        // ...
    ],

    /**
     * Synced entries will be created or updated under the name of this user.
     * If no user with the provided email address exists, the currently authenticated user will be used.
     */
    'author-email' => env('BEARHUB_AUTHOR_EMAIL', 'replace-this@bearhub.com'),

    /**
     * Once an entry has been created, the slug will not be updated anymore to avoid broken links.
     * Set this to true if you like to live dangerously.
     */
    'update-slugs' => env('BEARHUB_UPDATE_SLUGS', false),

    /**
     * Should BearHub update entry dates even after they have been created?
     */
    'update-dates' => env('BEARHUB_UPDATE_DATES', false),

    /**
     * Specify a separator string that separates the entries content from any tags you want to
     * be considered but not present in the main content.
     * Hint: This is a great place to add parent-tag or action-tags.
     */
    'meta-separator' => env('BEARHUB_META_SEPARATOR', '===='),

    /**
     * Action tags are ignored as taxonomies because they serve a specific purpose:
     */
    'action-tags' => [

        /**
         * Use this tag below the meta-separator to mark the synced entry as published.
         * Hint: If this tag does not exist in the note, the entry will be unpublished.
         */
        'published' => env('BEARHUB_ACTIONTAG_PUBLISHED', 'published'),
    ],

    /**
     * BearHub loves automation.
     * To make the content import even more seamless, you can enable auto-commits.
     * Note: Auto-commits require you to commit any pending changes before synchronization.
     */
    'git' => [
        'auto-commit' => env('BEARHUB_GIT_AUTO_COMMIT', false),

        'commit-message' => 'Sync entries with BearHub',

        'auto-push' => env('BEARHUB_GIT_AUTO_PUSH', false),
    ],

    /**
     * BearHub tries to locate your Bear database at "/Users/{youruser}/Library/Group Containers/XXXXXXXX.net.shinyfrog.bear/Application Data" by default.
     * If, for some reason, your database is hosted somewhere else, feel free to set your own database path.
     */
    'db-path' => env('BEARHUB_DB_PATH'),

    /**
     * Customize where BearHub stores images.
     */
    'storage' => [
        'container' => env('BEARHUB_STORAGE_CONTAINER', 'assets'),
        'path' => env('BEARHUB_STORAGE_PATH', 'content/bearhub'),
    ],
];
