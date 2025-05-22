<?php namespace Ci4ModelGenerator;

class ControllerGenerator
{
    protected string $controllerName;
    protected string $modelName;
    protected string $namespace;
    protected string $controllerFolder; // misal: Admin atau Api

    public function __construct(string $controllerName, string $modelName, string $namespace = 'App\Controllers', string $controllerFolder = '')
    {
        $this->controllerName = $controllerName;
        $this->modelName = $modelName;
        $this->namespace = $namespace;
        $this->controllerFolder = trim($controllerFolder, '\\/');
    }

    public function generate(): string
    {
        $namespaceLine = $this->namespace;
        if ($this->controllerFolder !== '') {
            $namespaceLine .= '\\' . str_replace('/', '\\', $this->controllerFolder);
        }

        $modelFullName = "App\Models\\$this->modelName";

        $controllerClassName = $this->controllerName;

        return <<<PHP
<?php namespace $namespaceLine;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class $controllerClassName extends BaseController
{
    protected \$$this->modelName;

    public function __construct()
    {
        \$this->{$this->modelName} = new $modelFullName();
    }

    public function index(): string
    {
        return view('{$this->controllerFolder}/" . strtolower($this->controllerName) . "');
    }

    public function store()
    {
        return \$this->response->setJSON(\$this->{$this->modelName}->findAll());
    }

    public function add(): ResponseInterface
    {
        \$param = \$this->request->getJSON();
        try {
            \$this->{$this->modelName}->insert(\$param);
            \$param->id = \$this->{$this->modelName}->insertID();
            return \$this->response->setJSON(\$param);
        } catch (\\Throwable \$th) {
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
            \$this->{$this->modelName}->update(\$param->id, \$param);
            return \$this->response->setJSON([
                'status' => 'success',
                'message' => 'Data berhasil diubah'
            ]);
        } catch (\\Throwable \$th) {
            return \$this->response->setJSON([
                'status' => 'error',
                'message' => \$th->getMessage()
            ]);
        }
    }

    public function delete(\$id = null): ResponseInterface
    {
        try {
            \$this->{$this->modelName}->delete(\$id);
            return \$this->response->setJSON([
                'status' => 'success',
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\\Throwable \$th) {
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
