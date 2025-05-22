<?php
namespace YourVendor\Ci4ModelGenerator\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use YourVendor\Ci4ModelGenerator\Generator\ModelGenerator;
use YourVendor\Ci4ModelGenerator\Generator\ControllerGenerator;

class ModelGenerateCommand extends BaseCommand
{
    protected $group       = 'generate';
    protected $name        = 'model:generate';
    protected $description = 'Generate all models and controllers from database tables.';

    protected $usage = 'model:generate --controllerFolder=Admin --modelPath=app/Models --controllerPath=app/Controllers';

    protected $options = [
        '--controllerFolder' => 'Subfolder for controllers (e.g. Admin)',
        '--modelPath'        => 'Output folder for models (default: app/Models)',
        '--controllerPath'   => 'Output folder for controllers (default: app/Controllers)',
        '--dbHost'           => 'Database host (default: localhost)',
        '--dbUser'           => 'Database user (default: root)',
        '--dbPass'           => 'Database password (default: empty)',
        '--dbName'           => 'Database name (required)'
    ];

    public function run(array $params)
    {
        $controllerFolder = CLI::getOption('controllerFolder') ?? '';
        $modelPath = CLI::getOption('modelPath') ?? APPPATH . 'Models';
        $controllerPath = CLI::getOption('controllerPath') ?? APPPATH . 'Controllers';

        $dbHost = CLI::getOption('dbHost') ?? 'localhost';
        $dbUser = CLI::getOption('dbUser') ?? 'root';
        $dbPass = CLI::getOption('dbPass') ?? '';
        $dbName = CLI::getOption('dbName');

        if (!$dbName) {
            CLI::error('Database name (--dbName) is required.');
            return;
        }

        // Init generators
        $modelGenerator = new ModelGenerator([
            'hostname' => $dbHost,
            'username' => $dbUser,
            'password' => $dbPass,
            'database' => $dbName,
        ], $modelPath);

        $controllerGenerator = new ControllerGenerator($controllerPath);

        CLI::write("Generating models to $modelPath ...");
        $modelFiles = $modelGenerator->generateAllModels();

        CLI::write("Generating controllers to $controllerPath" . ($controllerFolder ? "/$controllerFolder" : "") . " ...");

        foreach ($modelFiles as $modelFile) {
            // Extract table name from model filename
            $fileName = basename($modelFile, '.php');
            $tableName = $this->toSnakeCase(str_replace('Model', '', $fileName));
            $controllerName = str_replace('Model', '', $fileName);

            $fileController = $controllerGenerator->generateController($tableName, $controllerName, $controllerFolder);

            CLI::write("Generated controller: $fileController");

            // Print route snippet
            $routeSnippet = $controllerGenerator->generateRouteSnippet($controllerName, $controllerFolder);
            CLI::write("Route snippet for $controllerName:");
            CLI::write($routeSnippet);
        }

        CLI::write("Done!");
    }

    protected function toSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}
