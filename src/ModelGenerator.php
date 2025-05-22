<?php namespace Ci4ModelGenerator;

use Config\Database;

class ModelGenerator
{
    protected $db;
    protected $table;

    public function __construct(string $table)
    {
        $this->db = Database::connect();
        $this->table = $table;
    }

    public function generate(): string
    {
        $fields = $this->db->getFieldData($this->table);
        $primaryKey = null;
        $allowedFields = [];

        foreach ($fields as $field) {
            if ($field->primary_key) {
                $primaryKey = $field->name;
            } else {
                $allowedFields[] = $field->name;
            }
        }

        $modelName = ucfirst($this->table) . 'Model';

        $content = "<?php namespace App\Models;\n\nuse CodeIgniter\Model;\n\nclass $modelName extends Model\n{\n";
        $content .= "    protected \$table            = '$this->table';\n";
        $content .= "    protected \$primaryKey       = '$primaryKey';\n";
        $content .= "    protected \$useAutoIncrement = true;\n";
        $content .= "    protected \$returnType       = 'object';\n";
        $content .= "    protected \$useSoftDeletes   = false;\n";
        $content .= "    protected \$protectFields    = true;\n";
        $content .= "    protected \$allowedFields    = ['" . implode("','", $allowedFields) . "'];\n\n";
        $content .= "    protected bool \$allowEmptyInserts = false;\n";
        $content .= "}\n";

        return $content;
    }
}
