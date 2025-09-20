Oke, biar rapi saya susun ulang README.md-nya. Versi ini sudah saya format pakai Markdown standar GitHub, jadi harusnya tampil **rapi** dan enak dibaca. Silakan langsung copy:

````markdown
# CodeIgniter 4 – Model & Controller Generator

Generator otomatis untuk membuat file **Model** (dan opsional **Controller**) dari skema database.  
Dengan ini, Anda tidak perlu lagi menulis `class ... extends Model` secara berulang.

---

## 🚀 Instalasi

1. Pastikan **CodeIgniter 4** sudah berjalan.  
2. Letakkan folder `Ci4ModelGenerator` ke dalam `app/Commands` (atau namespace lain sesuai kebutuhan).  
3. Selesai – CodeIgniter otomatis akan mendeteksi command baru.  

---

## ⚡ Sintaks Dasar

```bash
php spark model:generate [nama_tabel] [options]
````

### Options

| Option                  | Fungsi                                                      |
| ----------------------- | ----------------------------------------------------------- |
| `--all`                 | Generate model untuk **semua tabel** dalam database.        |
| `--controller`          | Sekaligus generate **controller**.                          |
| `--controllerFolder=Ns` | Controller ditaruh di sub-folder (misalnya `Admin`, `Api`). |

---

## 📌 Contoh Penggunaan

### 1. Generate Model untuk 1 tabel

```bash
php spark model:generate users
```

**Output:**

```
app/Models/UsersModel.php
```

---

### 2. Generate Model + Controller

```bash
php spark model:generate users --controller
```

**Output:**

```
app/Models/UsersModel.php
app/Controllers/Users.php
```

Route otomatis ditambahkan ke `app/Config/Routes.php`.

---

### 3. Generate untuk Semua Tabel

```bash
php spark model:generate --all
```

---

### 4. Semua Tabel + Controller

```bash
php spark model:generate --all --controller
```

---

### 5. Controller di Folder Admin

```bash
php spark model:generate users --controller --controllerFolder=Admin
```

**Route yang ditambahkan:**

```php
$routes->group('users', static function ($r) {
    $r->get('/', 'Admin\Users::index');
    $r->get('read', 'Admin\Users::store');
    $r->post('add', 'Admin\Users::add');
    $r->put('edit', 'Admin\Users::edit');
    $r->delete('delete/(:hash)', 'Admin\Users::delete/$1');
});
```

---

## 📝 Hasil Generate

### Model

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['username', 'password', 'akses'];
}
```

### Controller (CRUD sederhana)

```php
<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\UsersModel;

class Users extends BaseController
{
    public function index()
    {
        $data['users'] = (new UsersModel)->findAll();
        return view('users/index', $data);
    }

    public function store()
    {
        return $this->response->setJSON((new UsersModel)->findAll());
    }

    public function add()
    {
        (new UsersModel)->insert($this->request->getPost());
        return redirect()->to('/users');
    }

    public function edit($id)
    {
        (new UsersModel)->update($id, $this->request->getPost());
        return redirect()->to('/users');
    }

    public function delete($id)
    {
        (new UsersModel)->delete($id);
        return redirect()->to('/users');
    }
}
```

---

## 📂 Struktur Project (Contoh)

### Sebelum generate

```
app/
├─ Config/
├─ Controllers/
├─ Models/
└─ ...
```

### Setelah generate

Command:

```bash
php spark model:generate users --controller --controllerFolder=Api
```

Struktur:

```
app/
├─ Config/
│  └─ Routes.php        ← tambahan group route users
├─ Controllers/
│  └─ Api/
│     └─ Users.php
├─ Models/
│  └─ UsersModel.php
└─ ...
```

---

## ✅ Kesimpulan

Dengan command ini, Anda bisa lebih cepat membangun **Model** dan **Controller CRUD dasar** di CodeIgniter 4 tanpa menulis boilerplate code berulang kali.

```

Mau saya buatkan juga **preview tampilan di GitHub** (screenshot hasil render README) biar Anda yakin tampilannya rapi?
```
