# 🚀 CMOS - WorkFlow App

<div align="center">

![SAVANA Logo](https://img.shields.io/badge/SAVANA-WorkFlow%20App-7C3AED?style=for-the-badge&logo=laravel&logoColor=white)

**Sistem Manajemen Program Kerja & Evaluasi Organisasi**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Svelte](https://img.shields.io/badge/Svelte-5-FF3E00?style=flat-square&logo=svelte&logoColor=white)](https://svelte.dev)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Docker](https://img.shields.io/badge/Docker-Sail%20%2B%20Coolify-2496ED?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com)

</div>

---

## 📋 Tentang CMOS

CMOS adalah aplikasi manajemen workflow modern yang dirancang untuk organisasi/komunitas dalam mengelola:

- 📊 **Program Kerja (Proker)** - Tracking progress program dengan timeline
- ✅ **Task Management** - Penugasan dan monitoring task per anggota
- 📈 **Evaluasi Staff** - Penilaian performa anggota dengan parameter terkustomisasi
- 📢 **Pengumuman** - Social feed dengan komentar, reaksi, dan polling
- 💬 **Internal Chat** - Komunikasi real-time antar anggota
- 📅 **Timeline & Kalender** - Visualisasi jadwal kegiatan
- 🔗 **Resource Sharing** - Google Drive & link penting

---

## ✨ Fitur Utama

### 🎯 Dashboard

- Overview statistik program dan task
- Quick actions untuk navigasi cepat
- Top staff ranking berdasarkan evaluasi

### 👥 Manajemen User & Departemen

- Role-based access (Admin, BPH, Kabinet, Staff)
- Multi-departemen dengan kabinet terpisah
- Avatar dan profil pengguna

### 📊 Program Kerja

- CRUD program dengan timeline
- Assign member dan PIC (Person In Charge)
- Progress tracking per program

### ✅ Task Management

- Penugasan task ke staff
- Status tracking (Todo, In Progress, Done)
- Deadline dan progress percentage
- Komentar pada task

### 📈 Evaluasi & Penilaian

- Parameter penilaian dinamis per departemen
- Scoring dengan bobot
- Grade otomatis (A, B, C, D, E)
- Periode evaluasi configurable

### 📢 Pengumuman (Social Feed)

- Post pengumuman (semua user bisa post)
- Komentar
- Reaksi emoji (👍❤️😂😮😢😡)
- Polling dengan duration

### 💬 Internal Chat

- Real-time messaging
- Unread notification badge
- Message preview di list

### 🎨 Kustomisasi

- 12 pilihan warna tema
- Nama aplikasi dinamis
- Dark mode support

---

## 🛠 Tech Stack

| Layer          | Technology                                |
| -------------- | ----------------------------------------- |
| **Framework**  | Laravel 12.x                              |
| **Language**   | PHP 8.4                                   |
| **Database**   | MySQL 8.4                                 |
| **Frontend**   | Blade shell + Svelte 5                    |
| **Styling**    | Tailwind CSS 4                            |
| **Build Tool** | Vite 7                                    |
| **Realtime**   | Laravel Reverb + Laravel Echo + Pusher JS |
| **UI Toolkit** | Bits UI + Svelte Sonner + Lucide Svelte   |
| **Charts**     | Chart.js                                  |
| **Icons**      | Font Awesome 6 + Lucide                   |
| **Fonts**      | Public Sans (Google Fonts)                |
| **Container**  | Docker (Laravel Sail) + Coolify           |

---

## 🚀 Quick Start

### Prerequisites

- Docker Desktop
- Git

### Access

- 🌐 **App**: http://localhost
- 🗄️ **phpMyAdmin**: http://localhost:8081

### Default Login

| Role    | Email                    | Password |
| ------- | ------------------------ | -------- |
| Admin   | admin@savana.test        | password |
| BPH     | bph@savana.test          | password |
| Kabinet | kabinet.psdm@savana.test | password |
| Staff   | staff1@savana.test       | password |

---

## 🚢 Sail Deployment

The repository includes a production-aware Laravel Sail Compose stack. The
application container builds the frontend and SSR assets, runs migrations, and
starts Octane, the queue worker, scheduler, Reverb, and SSR services.

### First Deployment

Install Docker Engine with the Compose plugin and Git on the server, then run:

```bash
git clone https://github.com/DH1252/CMOS.git
cd CMOS
cp .env.example .env
```

Edit `.env` before starting the containers. Set at least:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_PORT=80
APP_KEY=

DB_DATABASE=cmos
DB_PASSWORD=strong-root-password
SAIL_DB_USERNAME=sail
SAIL_DB_PASSWORD=strong-app-password
```

Build the image, install PHP dependencies, and generate the encryption key:

```bash
docker compose build
docker compose run --rm laravel.test composer install --no-interaction --prefer-dist --optimize-autoloader
docker compose run --rm laravel.test php artisan key:generate --force
docker compose up -d
```

On startup, `SAIL_AUTO_MIGRATE=true` runs pending migrations automatically. The
container also prepares frontend assets and creates the `public/storage`
symlink.

### Seed the Database

Run the production-safe baseline seeders with:

```bash
docker compose exec laravel.test php artisan db:seed --force
```

This seeds roles, departments, information categories, settings, and evaluation
configuration. It does not create demo users when `APP_ENV=production`.

For a local or demo environment, set the following in `.env` before seeding:

```env
APP_SEED_DEVELOPMENT_DATA=true
```

Then run:

```bash
docker compose exec laravel.test php artisan config:clear
docker compose exec laravel.test php artisan db:seed --force
docker compose exec laravel.test php artisan config:cache
```

The development seeder creates demo accounts with the password `password`. Do
not use this seeder on a public production server unless all generated account
passwords are changed immediately.

### Subsequent Deployments

```bash
git pull origin main
docker compose up -d --build
docker compose exec laravel.test php artisan migrate --force
```

After the initial dependency installation, the Sail wrapper can be used:

```bash
./vendor/bin/sail up -d --build
./vendor/bin/sail artisan migrate --force
./vendor/bin/sail artisan db:seed --force
./vendor/bin/sail logs -f
```

Do not run `migrate:fresh` on a server containing real data because it drops all
tables. Use `docker compose down` only when stopping the stack; do not use
`docker compose down -v` unless the database volume may be deleted.

---

## 📁 Project Structure

```
SAVANA/
├── app/
│   ├── Http/Controllers/     # Controllers
│   ├── Models/               # Eloquent models
│   └── ...
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Data seeders
├── resources/
│   └── views/                # Blade templates
├── public/
│   └── css/app.css           # Main stylesheet
├── routes/
│   └── web.php               # Web routes
└── docker/                   # Docker configuration
```

---

## 🔐 Role Permissions

| Feature              | Admin | BPH | Kabinet | Staff |
| -------------------- | :---: | :-: | :-----: | :---: |
| Manage Users         |  ✅   | ❌  |   ❌    |  ❌   |
| Manage Departments   |  ✅   | ✅  |   ❌    |  ❌   |
| Manage Programs      |  ✅   | ✅  |   ✅    |  👁️   |
| Create Tasks         |  ✅   | ✅  |   ✅    |  ❌   |
| Update Task Progress |  ✅   | ✅  |   ✅    |  ✅   |
| Evaluate Staff       |  ✅   | ✅  |   ✅    |  ❌   |
| Post Announcements   |  ✅   | ✅  |   ✅    |  ✅   |
| Internal Chat        |  ✅   | ✅  |   ✅    |  ✅   |
| Settings             |  ✅   | ❌  |   ❌    |  ❌   |

---

## 🎨 Theme Colors

Pilih warna tema favorit di Settings:

![Purple](https://img.shields.io/badge/-7C3AED?style=flat-square)
![Blue](https://img.shields.io/badge/-3B82F6?style=flat-square)
![Green](https://img.shields.io/badge/-10B981?style=flat-square)
![Red](https://img.shields.io/badge/-EF4444?style=flat-square)
![Orange](https://img.shields.io/badge/-F59E0B?style=flat-square)
![Pink](https://img.shields.io/badge/-EC4899?style=flat-square)
![Indigo](https://img.shields.io/badge/-6366F1?style=flat-square)
![Teal](https://img.shields.io/badge/-14B8A6?style=flat-square)
![Cyan](https://img.shields.io/badge/-06B6D4?style=flat-square)
![Rose](https://img.shields.io/badge/-F43F5E?style=flat-square)
![Amber](https://img.shields.io/badge/-F59E0B?style=flat-square)
![Slate](https://img.shields.io/badge/-64748B?style=flat-square)

---

## 📝 Development Commands

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Run artisan commands
./vendor/bin/sail artisan [command]

# Run migrations
./vendor/bin/sail artisan migrate

# Fresh database with seeders
./vendor/bin/sail artisan migrate:fresh --seed

# View logs
./vendor/bin/sail logs -f
```

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👨‍💻 Author

**Thoriq** - [@thoriqqrn](https://github.com/thoriqqrn)

---

<div align="center">

Made with ❤️ using Laravel

</div>
