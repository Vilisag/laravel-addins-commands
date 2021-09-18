<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name} {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a new module';

    /**
     * Module dir path
     *
     * @var string
     */
    private $modulePath = 'modules';

    /**
     * Module structure
     *
     * @var array
     */
    private $modStruct = [
        'src' => [
            'config',
            'database' => [
                'migrations'
            ],
            'Http' => [
                'Controllers',
                'Requests',
                'Resources',
            ],
            'Models',
            'Repositories' => [
                'Contracts',
                'Eloquents',
            ],
            'Services',
            'resources' => [
                'css',
                'js',
                'lang' => [
                    'en',
                ],
                'views',
            ],
            'routes',
        ],
    ];

    /**
     * Modules files
     *
     * @var array
     */
    private $modFiles = [
        'config'     => "/src/config/your_name.php",
        'controller' => "/src/Http/Controllers/Controller.php",
        'interface'  => "/src/Repositories/Contracts/BaseRepository.php",
        'eloquent'   => "/src/Repositories/Eloquents/EloquentBaseRepository.php",
        'service'    => "/src/Services/BaseService.php",
        'model'      => "/src/Models/Model.php",
        'provider'   => "/src/YourNameServiceProvider.php",
        'api'        => "/src/routes/api.php",
        'web'        => "/src/routes/web.php",
        'composer'   => "composer.json",
        'readme'     => "readme.md",
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Get arguments
            $name = $this->argument('name');
            $options = $this->options();

            // Create folder structure if it does not exist
            if ($options['path']) {
                $this->modulePath = $options['path'];
            }
            $this->modulePath .= '/'.strtolower($name);
            $this->makeDirs($this->modulePath);
            $this->makeDirStruct($this->modStruct, $this->modulePath);

            // Create modules files if it does not exist
            $this->createModuleFiles($this->modFiles, $name, $this->modulePath);

            $this->info('Create modules sucessful!');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
        return 0;
    }

    /**
     * Creates directories based on the array given
     *
     * @param array $structure
     * @param string $path
     * @return void
     */
    private function makeDirStruct($structure, $path = __DIR__)
    {
        foreach ($structure as $folder => $sub_folder)
        {
            // Folder with subfolders
            if (is_array($sub_folder))
            {
                $new_path = "{$path}/{$folder}";
                $this->makeDirs($new_path);
                $this->makeDirStruct($sub_folder, $new_path);
            }
            else
            {
                $new_path = "{$path}/{$sub_folder}";
                $this->makeDirs($new_path);
            }
        }
    }

    /**
     * Create dir function
     *
     * @param string $dirpath
     * @param integer $mode
     * @param boolean $recursive
     * @return void
     */
    private function makeDirs($dirpath, $mode = 0777, $recursive = true)
    {
        return is_dir($dirpath) || mkdir($dirpath, $mode, $recursive);
    }

    /**
     * Create repo files
     *
     * @param array $files
     * @param string $name
     * @param string $path
     * @return void
     */
    private function createModuleFiles($files, $name, $path = __DIR__)
    {
        foreach ($files as $key => $file) {
            $stub_file = app_path("Console/Commands/stubs/module/{$key}.stub");
            $new_path = "{$path}/{$file}";
            $new_path = str_replace(["YourName", "your_name"], [$name, strtolower($name)], $new_path);
            if (!is_file($new_path) && file_exists($stub_file)) {
                file_put_contents($new_path, str_replace(["YourName", "your_name"], [$name, strtolower($name)], file_get_contents($stub_file)));
            }
        }
    }
}
