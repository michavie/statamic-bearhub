# Docs

## Setup

1. The `bearhub.php` config file publishes by default, but you can also do so manually using `php please vendor:publish --tag=bearhub`.

2. Syncables — Create a connection between a Bear Parent Tag and your Statamic Collection:

```php
// config/bearhub.php
return [
    'syncables' => [
        // ⬇️ This is the Bear Parent Tag. All Bear notes that contain this tag will be synced.
        'myblog' => [
            'collection' => 'articles', // The name of your Statamic collection
            'fields' => [ // Define the fields of your collection.
                'title' => 'mytitle', // Remove line to use default: 'title'
                'content' => 'mycontent', // Remove line to use default: 'content'
                'taxonomy' => 'topics', // Remove line to disable taxonomy term synchronization
            ],
        ],

        // Set up as many as you want ...
    ]
]
```

3. (optional) If you want to run a console command instead of using the control panel, be sure to set the `BEARHUB_AUTHOR_EMAIL` env variable to an existing user's email address.

4. (optional) If you want to enable the BearHub widget inside your control panel's dashboard, open the `cp.php` configuration file and add the following to the `widgets` section:

```php
[
    'type' => 'bearhub',
    'width' => 50,
],
```

## Tagging Content with Taxonomy Terms

BearHub makes use of Bear’s [nested tags](https://bear.app/faq/Tags%20&%20Linking/Nested%20Tags/).

In the syncable section above, you have set up a connection using a specified parent tag. Tags you created as a hierarchical child tag will be used as taxonomy terms for your collection entries. It sounds complex, but it’s straightforward. Here is an example using the configuration from step 2:

`myblog` is your parent tag which is synced with your `articles`. Now, to tag an article with the `news` taxonomy term, add a nested tag like `myblog/news` to your notes or to the meta section (see below).

## The Meta Section

The meta section starts with a configurable meta separator, a string after which everything is written will be excluded from the main content:

```

Your main content here

====

Everything written below the meta separator `====` will be excluded from the main content. You can configure it to your preference in the `bearhub.php` config file.

#myblog (Bear Parent Tag)
#myblog/news #myblog/tech #myblog/global (`news, tech and global` are recognized as taxonomy terms)
#published (Action Tag: mark the entry as published)

...

```

This is useful for:

- Tagging the article with the Bear Parent Tag
- Using nested tags to apply taxonomy terms
- Adding additional drafts or notes that are only meant for you to see

## Action Tags

- `published`: Notes tagged with this tag will mark the Statamic entry as published

You can configure action tags to your preference in the `bearhub.php` config file.

## Markdown

By default, your notes are written using Bear's own Markup Language called [Polar](https://bear.app/faq/Markup%20:%20Markdown/Polar%20Bear%20markup%20language/). To have Bear and Statamic talk the same language, you most likely want to enable Bear's [Markdown Compatibility Mode](https://bear.app/faq/Markup%20:%20Markdown/Markdown%20compatibility%20mode/) in the app's settings:

<p align="center"><img src="/art/markdown-compatibility.png" alt="Markdown Compatibility Mode Settings"></p>
