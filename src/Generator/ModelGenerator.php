<?php
namespace YourVendor\Ci4ModelGenerator\Generator;

use mysqli;

class ModelGenerator
{
    protected mysqli $db;
    protected string $database;
    protected string $outputDir;

    public function __construct(array $dbConfig, string $outputDir)
    {
        $this->database = $dbConfig['database'];
        $this->outputDir = rtrim($outputDir, DIRECTORY_SEPARATOR);
        $this->db = new mysqli(
            $dbConfig['hostname'], 
            $dbConfig['username'], 
            $dbConfig['password'], 
            $this->database
        );

        if ($this->db->connect_error) {
            throw new \Exception('Database connect error: ' . $this->db->connect_error);
        }
    }

    /**
     * Generate model file for given table
     */
    public function generateModel(string $tableName): string
    {
        $primaryKey = 'id';
        $useAutoIncrement = true;
        $allowedFields = [];

        // Get primary key and auto increment info
        $res = $this->db->query("SHOW KEYS FROM `$tableName` WHERE Key_name = 'PRIMARY'");
        if ($res && $row = $res->fetch_assoc()) {
            $primaryKey = $row['Column_name'];
        }

        // Check if primary key is auto increment
        $res = $this->db->query("SHOW COLUMNS FROM `$tableName` WHERE Field = '$primaryKey'");
        if ($res && $row = $res->fetch_assoc()) {
            $useAutoIncrement = stripos($row['Extra'], 'auto_increment') !== false;
        }

        // Get allowed fields (all except primary key)
        $res = $this->db->query("SHOW COLUMNS FROM `$tableName`");
        while ($row = $res->fetch_assoc()) {
            if ($row['Field'] !== $primaryKey) {
                $allowedFields[] = $row['Field'];
            }
        }

        $className = $this->toPascalCase($tableName) . 'Model';
        $code = "<?php\n";
        $code .= "namespace App\Models;\n\n";
        $code .= "use CodeIgniter\Model;\n\n";
        $code .= "class $className extends Model\n";
        $code .= "{\n";
        $code .= "    protected \$table            = '$tableName';\n";
        $code .= "    protected \$primaryKey       = '$primaryKey';\n";
        $code .= "    protected \$useAutoIncrement = " . ($useAutoIncrement ? 'true' : 'false') . ";\n";
        $code .= "    protected \$returnType       = 'object';\n";
        $code .= "    protected \$useSoftDeletes   = false;\n";
        $code .= "    protected \$protectFields    = true;\n";
        $code .= "    protected \$allowedFields    = ['" . implode("','", $allowedFields) . "'];\n\n";
        $code .= "    protected bool \$allowEmptyInserts = false;\n";
        $code .= "}\n";

        $filePath = $this->outputDir . DIRECTORY_SEPARATOR . $className . '.php';

        // write/overwrite file
        file_put_contents($filePath, $code);

        return $filePath;
    }

    /**
     * Generate all models for all tables in DB
     */
    public function generateAllModels(): array
    {
        $tables = [];
        $res = $this->db->query("SHOW TABLES");
        $key = "Tables_in_" . $this->database;

        while ($row = $res->fetch_assoc()) {
            $tables[] = $row[$key];
        }

        $generatedFiles = [];
        foreach ($tables as $table) {
            $generatedFiles[] = $this->generateModel($table);
        }

        return $generatedFiles;
    }

    protected function toPascalCase(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }
}
