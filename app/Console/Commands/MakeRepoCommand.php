<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class MakeRepoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repo {name} {--all} {--module=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a new repository with all of necessary elements';

    /**
     * Repository structure
     *
     * @var array
     */
    private $repoStruct = [
        'Repositories' => [
            'Contracts',
            'Eloquents',
        ],
        'Services',
    ];

    /**
     * Repo base files
     *
     * @var array
     */
    private $baseFiles = [
        'interface_base_file' => '/Repositories/Contracts/BaseRepository.php',
        'eloquent_base_file'  => '/Repositories/Eloquents/EloquentBaseRepository.php',
        'service_base_file'   => '/Services/BaseService.php',
    ];

    /**
     * Repo files
     *
     * @var array
     */
    private $repoFiles = [
        'interface_file' => "/Repositories/Contracts/YourNameRepository.php",
        'eloquent_file'  => "/Repositories/Eloquents/EloquentYourNameRepository.php",
        'service_file'   => "/Services/YourNameService.php",
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
            $name = $this->argument('name');
            $options = $this->options();
            $module = $options['module'];
            $repoPath = app_path();
            if ($module) {
                $modulePath = 'modules/'.strtolower($module).'/src';
                $repoPath = base_path($modulePath);
                if (!is_dir($repoPath)) {
                    $this->error("Module {$module} does not exist!");
                    return 0;
                }
                $this->createRepo($name, $repoPath, $options['all']);
            } else {
                // Create folder structure if it does not exist
                $this->makeDirStruct($this->repoStruct, $repoPath);

                // Create base files if it does not exist
                $this->createBaseFiles($this->baseFiles, $repoPath);

                // Create repo files
                $this->createRepo($name, $repoPath, $options['all']);
            }
            $this->info('Create repo sucessful!');
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
     * Create base files
     *
     * @param array $files
     * @param string $path
     * @return void
     */
    private function createBaseFiles($files, $path = __DIR__)
    {
        foreach ($files as $key => $file) {
            $stub_file = app_path("Console/Commands/stubs/repo/{$key}.stub");
            $new_path = "{$path}/{$file}";
            if (!is_file($new_path) && file_exists($stub_file)) {
                copy($stub_file, $new_path);
            }
        }
    }

    /**
     * Create repo files
     *
     * @param array $files
     * @param string $name
     * @param string $path
     * @return void
     */
    private function createRepoFiles($files, $name, $path = __DIR__)
    {
        foreach ($files as $key => $file) {
            $stub_file = app_path("Console/Commands/stubs/repo/{$key}.stub");
            $new_path = "{$path}/{$file}";
            $new_path = str_replace("YourName", $name, $new_path);
            if (!is_file($new_path) && file_exists($stub_file)) {
                if ($path != app_path()) {
                    file_put_contents($new_path, str_replace(["YourName", "App"], $name, file_get_contents($stub_file)));
                } else {
                    file_put_contents($new_path, str_replace("YourName", $name, file_get_contents($stub_file)));
                }
            }
        }
    }

    /**
     * Create repo data
     *
     * @param string $name
     * @param string $path
     * @param boolean $option
     * @return void
     */
    private function createRepo($name, $path = __DIR__, $option = false)
    {
        // Create repo files if it does not exist
        $this->createRepoFiles($this->repoFiles, $name, $path);

        if ($option) {
            $this->call('make:model', ['name' => $name,
                '--migration' => 'default',
                '--controller' => 'default',
                '--resource' => 'default',
            ]);
            $this->call('make:request', ['name' => "{$name}Request"]);
            $this->call('make:resource', ['name' => "{$name}Resource"]);
            // Move repo files to module
            if ($path != app_path()) {
                // Model
                $old_path = app_path("Models/{$name}.php");
                $new_path = "{$path}/Models/{$name}.php";
                file_put_contents($new_path, str_replace("App", $name, file_get_contents($old_path)));
                unlink($old_path);
                // Controler
                $old_path = app_path("Http/Controllers/{$name}Controller.php");
                $new_path = "{$path}/Http/Controllers/{$name}Controller.php";
                file_put_contents($new_path, str_replace("App", $name, file_get_contents($old_path)));
                unlink($old_path);
                // Request
                $old_path = app_path("Http/Requests/{$name}Request.php");
                $new_path = "{$path}/Http/Requests/{$name}Request.php";
                file_put_contents($new_path, str_replace("App", $name, file_get_contents($old_path)));
                unlink($old_path);
                // Resources
                $old_path = app_path("Http/Resources/{$name}Resource.php");
                $new_path = "{$path}/Http/Resources/{$name}Resource.php";
                file_put_contents($new_path, str_replace("App", $name, file_get_contents($old_path)));
                unlink($old_path);
            }
        } else {
            $this->call('make:model', ['name' => $name]);
            // Move file to module
            if ($path != app_path()) {
                $old_path = app_path("Models/{$name}.php");
                $new_path = "{$path}/Models/{$name}.php";
                file_put_contents($new_path, str_replace("App", $name, file_get_contents($old_path)));
                unlink($old_path);
            }
        }
    }
}
