# Feedback Forms

<div align="center">

<img src="public/images/logo.png" alt="Feedback Forms Logo" width="200"/>

[![Production Status](https://img.shields.io/badge/status-production-green)](https://feedback-forms.uts-x.com)
[![Test Environment](https://img.shields.io/badge/status-testing-yellow)](https://feedback-forms-test.uts-x.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-13.x-336791?logo=postgresql)](https://postgresql.org)

<p align="center">
A comprehensive Laravel-based web application for creating and managing educational feedback surveys. This platform enables teachers to easily create, distribute, and collect feedback through customizable survey templates.
</p>
</div>


## 🌐 Environments

| Environment | URL | Status |
|------------|-----|--------|
| Production | [feedback-forms.uts-x.com](https://feedback-forms.uts-x.com) | [![Production](https://img.shields.io/website?url=https://feedback-forms.uts-x.com)](https://feedback-forms.uts-x.com) |
| Test/Staging | [feedback-forms-test.uts-x.com](https://feedback-forms-test.uts-x.com) | [![Staging](https://img.shields.io/website?url=https://feedback-forms-test.uts-x.com)](https://feedback-forms-test.uts-x.com) |
| Local Development | [localhost:8000](http://localhost:8000) | - |

## 🚀 Quick Start

```bash
# Clone and setup the project
git clone [repository-url] && cd feedback-forms

# Install dependencies and setup environment
make install
make key
make npm-install

# Start the application
make up
make migrate
make seed
make npm-build

# Visit http://localhost:8000
```


## 🛠 Tech Stack

This application is built using a modern and robust technology stack, focusing on maintainability, scalability, and developer experience:

**Backend**
* **Laravel 11.x (PHP)** - The application's core framework
* **PostgreSQL** - Robust and reliable database
* **Laravel Queue System** - For background task processing

**Frontend**
* **Blade Templates** - Laravel's powerful templating engine
* **Livewire 3.x** - For dynamic interfaces
* **Tailwind CSS** - Utility-first styling
* **Alpine.js** - Lightweight JavaScript framework

**Infrastructure**
* **Docker** - Containerization for development and deployment
* **Kubernetes** - Container orchestration and scaling
* **Laravel Sail** - Local development environment

## 📦 Installation

<details>
<summary><strong>Available Make Commands</strong></summary>

| Command | Description |
|---------|------------|
| `make up` | Start the application |
| `make down` | Stop the application |
| `make build` | Build containers |
| `make install` | Install composer dependencies |
| `make migrate` | Run database migrations |
| `make refresh` | Refresh database (migrate:fresh) |
| `make clear` | Clear application cache |
| `make npm-install` | Install npm dependencies |
| `make npm-build` | Build frontend assets |
| `make npm-dev` | Run npm in dev mode |
| `make logs` | View application logs |
| `make ps` | List containers |
| `make key` | Generate application key |
| `make seed` | Seed the database |

</details>

<details>
<summary><strong>1. Clone and Setup</strong></summary>

```bash
# Clone the repository
git clone [repository-url]
cd feedback-forms

# Install dependencies
make install
make npm-install

# Configure environment
cp .env.example .env
make key
```
</details>

<details>
<summary><strong>2. Database Setup</strong></summary>

```bash
# Start the application
make up

# Setup database
make migrate
make seed
```
</details>

<details>
<summary><strong>3. Frontend Build</strong></summary>

```bash
# Build assets
make npm-build

# For development with hot reload
make npm-dev
```
</details>

<details>
<summary><strong>4. Development Commands</strong></summary>

```bash
# View logs
make logs

# List running containers
make ps

# Stop the application
make down

# Clear application cache
make clear

# Refresh database
make refresh
```
</details>

## 🔧 Configuration

<details>
<summary><strong>Environment Variables</strong></summary>

```env
# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=feedback_forms
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password

# Application URL
APP_URL=http://localhost:8000
```
</details>



## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request




## 🔐 Security

If you discover any security vulnerabilities, please report them via email to [security@feedback-forms.uts-x.com](mailto:security@feedback-forms.uts-x.com). All security vulnerabilities will be promptly addressed.

## 🙏 Acknowledgments

- [Laravel Framework](https://laravel.com) and its community
- All [contributors](https://github.com/feedback-forms/feedback-forms/graphs/contributors) who have helped shape this project
- Educational institutions using and providing feedback on the platform






<br/>

Made with ❤️ for education
