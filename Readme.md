# Guide

These commands use to create the Laravel module/Laravel repository structure.
Have fun to use.

## Installing

* Copy ```app``` folder to your Laravel App folder
* Register these commands in your ```app/Console/Kernel.php```

~~~php
  //.....
  /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\MakeRepoCommand::class,
        \App\Console\Commands\MakeModuleCommand::class,
    ];
  //.....
~~~

* Check these commands

~~~bash
  $ php artisan list
~~~

## License

MIT
