<?php

return [

    /**
     * Define the connection between Bear Tag -> Statamic Collection you want to sync.
     *
     * Hint: BearHub makes use of Bear's nested tags. They are useful to create a hierarchical structure of your tags.
     * Learn more: https://bear.app/faq/Tags%20&%20Linking/Nested%20Tags/
     *
     * Bear Parent Tag: The parent of all the child tags that are synced as taxonomies
     * Statamic Collection: The collection that will be synced with your Bear notes
     * Statamic Taxonomy: Tags used in Bear notes will be synced with the terms of this taxonomy (blueprint) field. Set null to disable.
     */
    'syncables' => [
        'myblog' /* Bear Parent Tag: e.g. mycompany, blog, website, ... */ => [
            'collection' => 'articles',
            'taxonomy' => 'topics',
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
    'meta-separator' => env('BEARHUB_TAG_SEPARATOR', '===='),

    /**
     * Action tags are ignored as taxonomies because they serve a specific purpose:
     */
    'action-tags' => [

        /**
         * Use this tag below the meta-separator to mark the synced entry as published.
         * Hint: If this tag does not exist in the note, the entry will be unpublished.
         */
        'published' => env('BEARHUB_TAG_PUBLISHED', 'published'),
    ],

    /**
     * BearHub tries to locate your Bear database at "/Users/{youruser}/Library/Group Containers/XXXXXXXX.net.shinyfrog.bear/Application Data" by default.
     * If, for some reason, your database is hosted somewhere else, feel free to set your own database path.
     */
    'db-path' => env('BEARHUB_DB_PATH'),
];
