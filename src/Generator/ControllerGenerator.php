<?php
namespace YourVendor\Ci4ModelGenerator\Generator;

class ControllerGenerator
{
    protected string $outputDir;
    protected string $modelNamespace = 'App\Models';

    public function __construct(string $outputDir)
    {
        $this->outputDir = rtrim($outputDir, DIRECTORY_SEPARATOR);
    }

    /**
     * Generate controller file for given model (table)
     * @param string $tableName e.g. 'service_area'
     * @param string $controllerName e.g. 'Area'
     * @param string $controllerFolder e.g. 'Admin' or empty string
     * @return string full path generated file
     */
    public function generateController(string $tableName, string $controllerName, string $controllerFolder = ''): string
    {
        $modelClass = $this->toPascalCase($tableName) . 'Model';
        $controllerClass = $controllerName;

        $namespace = 'App\Controllers';
        if ($controllerFolder !== '') {
            $namespace .= '\\' . $controllerFolder;
        }

        $modelFQCN = $this->modelNamespace . '\\' . $modelClass;

        $code = "<?php\n";
        $code .= "namespace $namespace;\n\n";
        $code .= "use App\Controllers\BaseController;\n";
        $code .= "use CodeIgniter\\HTTP\\ResponseInterface;\n\n";
        $code .= "class $controllerClass extends BaseController\n";
        $code .= "{\n";
        $code .= "    protected \$$tableName;\n\n";
        $code .= "    public function __construct() {\n";
        $code .= "        \$this->$tableName = new $modelFQCN();\n";
        $code .= "    }\n\n";

        // index method
        $code .= "    public function index(): string\n";
        $code .= "    {\n";
        $code .= "        return view('".strtolower($controllerFolder !== '' ? $controllerFolder . '/' : '') . strtolower($controllerName)."');\n";
        $code .= "    }\n\n";

        // store (read all)
        $code .= "    public function store()\n";
        $code .= "    {\n";
        $code .= "        return \$this->response->setJSON(\$this->$tableName->findAll());\n";
        $code .= "    }\n\n";

        // add method
        $code .= "    public function add(): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$param = \$this->request->getJSON();\n";
        $code .= "        try {\n";
        $code .= "            \$this->$tableName->insert(\$param);\n";
        $code .= "            \$param->id = \$this->$tableName->insertID();\n";
        $code .= "            return \$this->response->setJSON(\$param);\n";
        $code .= "        } catch (\\Throwable \$th) {\n";
        $code .= "            return \$this->response->setJSON([\n";
        $code .= "                'status' => 'error',\n";
        $code .= "                'message' => \$th->getMessage()\n";
        $code .= "            ]);\n";
        $code .= "        }\n";
        $code .= "    }\n\n";

        // edit method
        $code .= "    public function edit(): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$param = \$this->request->getJSON();\n";
        $code .= "        try {\n";
        $code .= "            \$this->$tableName->update(\$param->id, \$param);\n";
        $code .= "            return \$this->response->setJSON([\n";
        $code .= "                'status' => 'success',\n";
        $code .= "                'message' => 'Data berhasil diubah'\n";
        $code .= "            ]);\n";
        $code .= "        } catch (\\Throwable \$th) {\n";
        $code .= "            return \$this->response->setJSON([\n";
        $code .= "                'status' => 'error',\n";
        $code .= "                'message' => \$th->getMessage()\n";
        $code .= "            ]);\n";
        $code .= "        }\n";
        $code .= "    }\n\n";

        // delete method
        $code .= "    public function delete(\$id = null): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        try {\n";
        $code .= "            \$this->$tableName->delete(\$id);\n";
        $code .= "            return \$this->response->setJSON([\n";
        $code .= "                'status' => 'success',\n";
        $code .= "                'message' => 'Data berhasil dihapus'\n";
        $code .= "            ]);\n";
        $code .= "        } catch (\\Throwable \$th) {\n";
        $code .= "            return \$this->response->setJSON([\n";
        $code .= "                'status' => 'error',\n";
        $code .= "                'message' => \$th->getMessage()\n";
        $code .= "            ])->setStatusCode(500);\n";
        $code .= "        }\n";
        $code .= "    }\n";

        $code .= "}\n";

        $dir = $this->outputDir;
        if ($controllerFolder !== '') {
            $dir .= DIRECTORY_SEPARATOR . $controllerFolder;
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $filePath = $dir . DIRECTORY_SEPARATOR . $controllerClass . '.php';

        file_put_contents($filePath, $code);

        return $filePath;
    }

    /**
     * Generate route snippet for controller group
     */
    public function generateRouteSnippet(string $controllerName, string $controllerFolder): string
    {
        $prefix = strtolower($controllerName);
        $namespace = $controllerFolder !== '' ? $controllerFolder . '\\' : '';
        return <<<ROUTES
\$routes->group('$prefix', function (\$routes) {
    \$routes->get('/', '$namespace$controllerName::index');
    \$routes->get('read', '$namespace$controllerName::store');
    \$routes->post('add', '$namespace$controllerName::add');
    \$routes->put('edit', '$namespace$controllerName::edit');
    \$routes->delete('delete/(:hash)', '$namespace$controllerName::delete/\$1');
});
ROUTES;
    }

    protected function toPascalCase(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }
}
