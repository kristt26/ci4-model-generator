<?php
class ControllerGenerator
{
    protected string $controllerName;
    protected string $modelName;
    protected string $namespace;
    protected string $controllerFolder;

    public function __construct(string $controllerName, string $modelName, string $namespace, string $controllerFolder = '')
    {
        $this->controllerName = $controllerName;
        $this->modelName = $modelName;
        $this->namespace = $namespace;
        $this->controllerFolder = $controllerFolder;
    }

    public function generate(): string
    {
        $namespaceLine = "namespace {$this->namespace};";

        return <<<PHP
<?php
$namespaceLine

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class {$this->controllerName} extends BaseController
{
    protected \${$this->controllerFolder ? strtolower($this->controllerFolder) . '_' : ''}{$this->modelName};

    public function __construct()
    {
        \$this->{$this->controllerFolder ? strtolower($this->controllerFolder) . '_' : ''}{$this->modelName} = new \\App\\Models\\{$this->modelName}();
    }

    public function index(): string
    {
        return view('{$this->controllerFolder ? strtolower($this->controllerFolder) . '/' : ''}{$this->controllerName}');
    }

    public function store()
    {
        return \$this->response->setJSON(\$this->{$this->controllerFolder ? strtolower($this->controllerFolder) . '_' : ''}{$this->modelName}->findAll());
    }

    public function add(): ResponseInterface
    {
        \$param = \$this->request->getJSON();
        try {
            \$this->{$this->controllerFolder ? strtolower($this->controllerFolder) . '_' : ''}{$this->modelName}->insert(\$param);
            \$param->id = \$this->{$this->controllerFolder ? strtolower($this->controllerFolder) . '_' : ''}{$this->modelName}->insertID();
            return \$this->response->setJSON(\$param);
        } catch (\Throwable \$th) {
            return \$this->response->setJSON([
                'status' => 'error',
                'message' => \$th->getMessage()
            ]);
        }
    }

    public function edit(): ResponseInterface
    {
        \$param = \$this->request->getJSON();
        try {
            \$this->{$this->controllerFolder ? strtolower($this->controllerFolder) . '_' : ''}{$this->modelName}->update(\$param->id, \$param);
            return \$this->response->setJSON([
                'status' => 'success',
                'message' => 'Data berhasil diubah'
            ]);
        } catch (\Throwable \$th) {
            return \$this->response->setJSON([
                'status' => 'error',
                'message' => \$th->getMessage()
            ]);
        }
    }

    public function delete(\$id = null): ResponseInterface
    {
        try {
            \$this->{$this->controllerFolder ? strtolower($this->controllerFolder) . '_' : ''}{$this->modelName}->delete(\$id);
            return \$this->response->setJSON([
                'status' => 'success',
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable \$th) {
            return \$this->response->setJSON([
                'status' => 'error',
                'message' => \$th->getMessage()
            ])->setStatusCode(500);
        }
    }
}

PHP;
    }
}
