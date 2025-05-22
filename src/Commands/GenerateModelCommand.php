<?php namespace Ci4ModelGenerator\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Ci4ModelGenerator\ModelGenerator;
use Config\Database;

class GenerateModelCommand extends BaseCommand
{
    protected $group       = 'Generator';
    protected $name        = 'model:generate';
    protected $description = 'Generate CodeIgniter 4 Models from database tables. Use --all to generate all tables.';

    protected $options = [
        '--all' => 'Generate models for all tables in the database'
    ];

    public function run(array $params = [])
    {
        $db = Database::connect();

        $generateAll = CLI::getOption('all');

        if ($generateAll) {
            // Ambil semua tabel dari database
            $tables = $this->getAllTables($db);

            if (empty($tables)) {
                CLI::error('No tables found in the database.');
                return;
            }

            foreach ($tables as $table) {
                $this->generateModelForTable($table);
            }

            CLI::write('All models generated successfully.');
        } else {
            // Generate satu tabel saja (input manual)
            $table = $params[0] ?? CLI::prompt('Enter table name');

            if (!$table) {
                CLI::error('Table name is required.');
                return;
            }

            $this->generateModelForTable($table);
        }
    }

    protected function getAllTables($db): array
    {
        $tables = [];

        // Query untuk MySQL, bisa sesuaikan jika pakai db lain
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

        if (file_exists($modelFile)) {
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
}
