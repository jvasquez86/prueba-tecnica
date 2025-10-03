<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# API de Transacciones - Laravel 12

Este proyecto implementa una API REST en **Laravel 12** para gestionar usuarios y transacciones, con autenticación mediante **Laravel Sanctum**.

## 📌 Repositorio

👉 [https://github.com/jvasquez86/prueba-tecnica.git](https://github.com/jvasquez86/prueba-tecnica.git)

---

## 🚀 Requisitos

- PHP >= 8.2  
- Composer >= 2.x  
- MySQL >= 8 o MariaDB  
- Node.js >= 18 (opcional para frontend)  
- Git  

---

## 📦 Instalación

Ejecuta los siguientes pasos:

```bash
# 1. Clonar el repositorio
git clone https://github.com/jvasquez86/prueba-tecnica.git
cd prueba-tecnica

# 2. Instalar dependencias de PHP
composer install

# 3. Crear base de datos en MySQL
mysql -u root -p -e "CREATE DATABASE prueba_tecnica;"

# 4. Copiar archivo de entorno
cp .env.example .env

# 5. Configurar .env con credenciales de base de datos
# (editar DB_DATABASE=prueba_tecnica, DB_USERNAME y DB_PASSWORD)

# 6. Generar key de aplicación
php artisan key:generate

# 7. Ejecutar migraciones y seeders
php artisan migrate --seed

# 8. Iniciar servidor de desarrollo
composer run dev

## Documentacion
http://127.0.0.1:8000/api/documentation#/
