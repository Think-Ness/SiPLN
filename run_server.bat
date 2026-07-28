@echo off
title Jalankan Web Server - Sistem Informasi
color 0A

echo ===================================================
echo     MEMULAI SERVER SISTEM INFORMASI
echo ===================================================
echo.

:: Mengecek apakah PHP sudah terinstal
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP tidak ditemukan di sistem ini!
    echo Pastikan Anda sudah menginstal XAMPP/Laragon dan memasukkan PHP ke dalam Environment Variables.
    echo.
    pause
    exit /b
)

:: Mencoba menyalakan MySQL otomatis jika memakai XAMPP default
if exist "C:\xampp\mysql\bin\mysqld.exe" (
    echo [INFO] Menyalakan Database MySQL dari XAMPP...
    start /B "" "C:\xampp\mysql\bin\mysqld.exe"
    timeout /t 2 >nul
) else (
    echo [INFO] Pastikan Database MySQL (XAMPP/Laragon) sudah Anda nyalakan.
)

:: Mengambil IP Address lokal (WiFi/LAN) untuk diakses via HP
set IP=127.0.0.1
for /f "tokens=2 delims=:" %%i in ('ipconfig ^| findstr /c:"IPv4 Address" /c:"Address IPv4"') do (
    for /f "tokens=1" %%x in ("%%i") do set IP=%%x
)

echo.
echo ===================================================
echo SERVER BERHASIL BERJALAN!
echo.
echo Buka alamat berikut di browser Anda:
echo.
echo [Di PC ini]   : http://localhost:8080/
echo [Di HP / PC lain] : http://%IP%:8080/
echo.
echo ^(Syarat: HP atau PC lain harus menggunakan koneksi WiFi yang sama^)
echo ===================================================
echo.
echo [PERINGATAN] JANGAN TUTUP JENDELA INI!
echo Jika jendela ini ditutup, web tidak akan bisa diakses.
echo Untuk mematikan server, tekan CTRL + C.
echo.

:: Pindah ke direktori skrip berada
cd /d "%~dp0"

:: Menjalankan server PHP agar bisa diakses dari semua IP (0.0.0.0)
php -S 0.0.0.0:8080 -t public

pause
