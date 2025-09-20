Siap 👍 saya bikinkan **`README.md`** yang cocok untuk library CodeIgniter 4 kamu (generator model & controller).

Berikut draft `README.md`:

````markdown
# CI4 Model & Controller Generator

[![CodeIgniter 4](https://img.shields.io/badge/CodeIgniter-4-red)](https://codeigniter.com/)
[![PHP](https://img.shields.io/badge/PHP-^8.1-blue)](https://www.php.net/)

Library ini menyediakan **Command Line Interface (CLI)** untuk meng-generate **Model** dan **Controller** otomatis dari tabel database pada CodeIgniter 4.  
Mendukung opsi generate semua tabel, generate controller sekaligus, serta mode **refresh** (overwrite file tanpa konfirmasi).

---

## ✨ Fitur
- Generate model dari satu tabel atau semua tabel.
- Generate controller otomatis dari model yang dibuat.
- Mendukung namespace/folder controller (misal: `Admin`, `Api`).
- Tambahkan route otomatis ke `app/Config/Routes.php`.
- Opsi `--refresh` untuk overwrite tanpa prompt konfirmasi.

---

## 📦 Instalasi
Clone atau install library ini ke dalam project CodeIgniter 4 kamu:

```bash
composer require ajenkris/ci4-model-generator
````

> Pastikan sudah menggunakan **CodeIgniter 4** dan **PHP >= 8.1**.

---

## 🚀 Penggunaan

### Generate Model untuk satu tabel

```bash
php spark model:generate users
```

Akan menghasilkan file:

```
app/Models/UsersModel.php
```

---

### Generate Model + Controller

```bash
php spark model:generate users --controller
```

Akan menghasilkan file:

```
app/Models/UsersModel.php
app/Controllers/Users.php
```

Sekaligus menambahkan route group di `app/Config/Routes.php`.

---

### Generate Semua Tabel

```bash
php spark model:generate --all
```

Dengan controller:

```bash
php spark model:generate --all --controller
```

---

### Generate dengan Folder/Namespace Controller

```bash
php spark model:generate users --controller --controllerFolder=Admin
```

Akan menghasilkan:

```
app/Controllers/Admin/Users.php
```

Dengan namespace `App\Controllers\Admin`.

---

### Mode Refresh (overwrite tanpa prompt)

```bash
php spark model:generate users --refresh
```

atau

```bash
php spark model:generate --all --controller --refresh
```

atau 

```bash
php spark model:generate --all --controller --controllerFolder=Admin --refresh
```
---

## ⚙️ Options

| Opsi                 | Deskripsi                                                    |
| -------------------- | ------------------------------------------------------------ |
| `--all`              | Generate semua tabel dalam database                          |
| `--controller`       | Generate controller untuk model yang dibuat                  |
| `--controllerFolder` | Tentukan folder/namespace controller (misal: `Admin`, `Api`) |
| `--refresh`          | Overwrite file lama tanpa prompt konfirmasi                  |

---

## 📂 Struktur Output

```
app/
├── Controllers/
│   ├── Users.php
│   └── Admin/
│       └── Products.php
└── Models/
    ├── UsersModel.php
    └── ProductsModel.php
```

---

## 🛠️ Catatan

* Pastikan database sudah terkoneksi dengan benar di `.env` atau `app/Config/Database.php`.
* File model/controller yang sudah ada **tidak akan di-overwrite** kecuali menggunakan opsi `--refresh`.

---

## 📜 Lisensi

MIT License © 2025 [Ajenkris Yanto Kungkung](https://github.com/ajenkris)

```

---

Mau saya tambahkan juga contoh **controller hasil generate otomatis** (isi kode skeleton) di `README.md` biar user tahu outputnya?
```
