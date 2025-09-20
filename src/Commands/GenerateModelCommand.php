<?php

namespace Ci4ModelGenerator\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Ci4ModelGenerator\ModelGenerator;
use Ci4ModelGenerator\ControllerGenerator;

class GenerateModelCommand extends BaseCommand
{
    protected $group       = 'Generator';
    protected $name        = 'model:generate';
    protected $description = 'Generate CodeIgniter 4 Models from database tables. Use --all to generate all tables.';

    protected $options = [
        '--all'              => 'Generate models for all tables in the database',
        '--controller'       => 'Generate controller for the model',
        '--controllerFolder' => 'Specify controller folder/namespace (e.g. Admin, Api)',
        '--refresh'          => 'Force overwrite existing files without prompt',
    ];

    protected $refresh = false;

    public function run(array $params = [])
    {
        $db = Database::connect();

        $generateAll       = CLI::getOption('all');
        $generateController = CLI::getOption('controller');
        $controllerFolder  = CLI::getOption('controllerFolder') ?? '';
        $this->refresh     = CLI::getOption('refresh') ?? false;

        if ($generateAll) {
            $tables = $this->getAllTables($db);

            if (empty($tables)) {
                CLI::error('No tables found in the database.');
                return;
            }

            foreach ($tables as $table) {
                $this->generateModelForTable($table);

                if ($generateController) {
                    $this->generateControllerForTable($table, $controllerFolder);
                }
            }

            CLI::write('All models' . ($generateController ? ' and controllers' : '') . ' generated successfully.');
        } else {
            $table = $params[0] ?? CLI::prompt('Enter table name');

            if (!$table) {
                CLI::error('Table name is required.');
                return;
            }

            $this->generateModelForTable($table);

            if ($generateController) {
                $this->generateControllerForTable($table, $controllerFolder);
            }
        }
    }

    protected function getAllTables($db): array
    {
        $tables = [];

        $query = $db->query('SHOW TABLES');

        foreach ($query->getResultArray() as $row) {
            $tables[] = array_values($row)[0];
        }

        return $tables;
    }

    protected function generateModelForTable(string $table)
    {
        $generator = new ModelGenerator($table);
        $modelCode = $generator->generate();

        $modelFile = WRITEPATH . '../app/Models/' . ucfirst($table) . 'Model.php';

        if (file_exists($modelFile) && !$this->refresh) {
            CLI::write("Model file already exists: $modelFile");
            $overwrite = CLI::prompt('Overwrite? (y/n)', ['y', 'n']);
            if (strtolower($overwrite) !== 'y') {
                CLI::write("Skipped generating model for table '$table'.");
                return;
            }
        }

        file_put_contents($modelFile, $modelCode);
        CLI::write("Model for table '$table' generated successfully at: $modelFile");
    }

    protected function generateControllerForTable(string $table, string $controllerFolder)
    {
        $modelName      = ucfirst($table) . 'Model';
        $controllerName = ucfirst($table);

        $generator       = new ControllerGenerator($controllerName, $modelName, 'App\Controllers', $controllerFolder);
        $controllerCode  = $generator->generate();

        $folderPath = WRITEPATH . '../app/Controllers/';
        if ($controllerFolder !== '') {
            $folderPath .= $controllerFolder . '/';
            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0755, true);
            }
        }

        $controllerFile = $folderPath . $controllerName . '.php';

        if (file_exists($controllerFile) && !$this->refresh) {
            CLI::write("Controller file already exists: $controllerFile");
            $overwrite = CLI::prompt('Overwrite? (y/n)', ['y', 'n']);
            if (strtolower($overwrite) !== 'y') {
                CLI::write("Skipped generating controller for table '$table'.");
                return;
            }
        }

        file_put_contents($controllerFile, $controllerCode);
        CLI::write("Controller for table '$table' generated successfully at: $controllerFile");

        // Tambahkan routes otomatis ke app/Config/Routes.php
        $success = $this->appendRouteToRoutesFile($controllerName, $controllerFolder);
        if ($success) {
            CLI::write("Route group for '$controllerName' successfully added to app/Config/Routes.php");
        } else {
            CLI::write("Route group for '$controllerName' already exists in app/Config/Routes.php, skipped.");
        }
    }

    protected function appendRouteToRoutesFile(string $controllerName, string $controllerFolder): bool
    {
        $routesFile = WRITEPATH . '../app/Config/Routes.php';

        if (!file_exists($routesFile)) {
            CLI::error("Routes file not found: $routesFile");
            return false;
        }

        $groupName = strtolower($controllerName);
        $prefix    = $controllerFolder !== '' ? $controllerFolder . '\\' : '';
        $prefix    = str_replace('/', '\\', $prefix);

        $routeGroupCode = <<<ROUTES

// Routes for {$controllerName}
\$routes->group('$groupName', function (\$routes) {
    \$routes->get('/', '{$prefix}{$controllerName}::index');
    \$routes->get('read', '{$prefix}{$controllerName}::store');
    \$routes->post('add', '{$prefix}{$controllerName}::add');
    \$routes->put('edit', '{$prefix}{$controllerName}::edit');
    \$routes->delete('delete/(:hash)', '{$prefix}{$controllerName}::delete/\$1');
});

ROUTES;

        $routesContent = file_get_contents($routesFile);

        // Simple cek apakah sudah ada route group ini
        if (strpos($routesContent, "\$routes->group('$groupName'") !== false) {
            return false; // Sudah ada, jangan tambahkan
        }

        // Tambahkan di akhir file routes.php
        file_put_contents($routesFile, $routesContent . $routeGroupCode);
        return true;
    }
}
