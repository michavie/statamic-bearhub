<?php

namespace Michavie\Bearhub\Traits;

trait UsesBearsDatabaseConnection
{
    public static function resolveConnection($connection = null)
    {
        $connFactory = new \Illuminate\Database\Connectors\ConnectionFactory(new \Illuminate\Container\Container);
        $conn = $connFactory->make([
            'driver' => 'sqlite',
            'database' => static::getBearDatabasePath(),
        ]);

        $resolver = new \Illuminate\Database\ConnectionResolver();
        $resolver->addConnection('default', $conn);
        $resolver->setDefaultConnection('default');

        \Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);

        return $conn;
    }

    public static function getBearDatabasePath()
    {
        throw_unless(
            file_exists($bearDb = static::getBearPath().'/database.sqlite'),
            new \Exception('Bear: Can\'t find Bear database')
        );

        return $bearDb;
    }

    public static function getBearPath()
    {
        if ($bearPathFromEnv = env('BEARHUB_DB_PATH')) return $bearPathFromEnv;

        // Static cache in case of multiple calls per execution.
        static $bearPath;
        if ($bearPath) return $bearPath;

        preg_match('/\/Users\/([a-zA-Z0-9\s]*)\//', dirname(__DIR__), $match);

        $user = throw_unless($match[1] ?? false, new \Exception('BearHub: Can\'t find system\'s user'));

        $bearBasePath = "/Users/{$user}/Library/Group Containers/";
        $bearPath = $bearBasePath.'/'.collect(scandir($bearBasePath))->first(function ($directory) {
            return preg_match('/shinyfrog\.bear/', $directory);
        });

        return throw_unless($bearPath = $bearPath.'/Application Data', new \Exception('BearHub: Can\'t find Bear in system'));
    }
}
