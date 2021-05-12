# Usage

Publish the `bearhub.php` configuration file via `php please vendor:publish --provider=Michavie\\Bearhub\\ServiceProvider`

## Syncables — Create a connection between a Bear Parent Tag and your Statamic collection:

```php
// config/bearhub.php
return [
    'syncables' => [
        // ⬇️ This is the Bear Parent Tag. All notes that contain this tag will be synced.
        'myblog' => [
            'collection' => 'articles', // Your Statamic collection
            'taxonomy' => 'topics', // The taxonomy field of your Statamic collection.
            // ⬆️ All nested tags in the note will sync with existing terms of this taxonomy. Set to null to disable.

        ],

        // Set up as many as you want ...
    ]
]
```

## Tagging Content with Taxonomy Terms

BearHub makes use of Bear’s [nested tags](https://bear.app/faq/Tags%20&%20Linking/Nested%20Tags/).

In the syncable section above, you have set up a connection using a specified parent tag. Tags you created as a hierarchical child tag will be used as taxonomy terms for your collection entries. It sounds complex, but it’s straightforward. Here is an example using the configuration from step 2:

`myblog` is your parent tag which is synced with your `articles`. Now, to tag an article with the `news` taxonomy term, add a nested tag like `myblog/news` to your notes or to the meta section (see below).

## Action Tags

- `published`: Notes tagged with this tag will mark the Statamic entry as published ()

You can configure action tags to your preference in the `bearhub.php` config file.

## The Meta Section

The meta section starts with a configurable meta separator, a string after which everything is written will be excluded from the main content.

This is useful for:

- Tagging the article with the Bear Parent Tag
- Using nested tags to apply taxonomy terms
- Adding additional drafts or notes that are only meant for you to see
