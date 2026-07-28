@echo off
color 0A
title Launcher Aplikasi PLN (Offline)
echo ====================================================
echo      MEMULAI APLIKASI PLN (OFFLINE)
echo ====================================================
echo.

:: Coba cari PHP bawaan XAMPP atau Laragon di semua drive umum
set PHP_EXE=
for %%d in (C D E F G H I J K L M N O P Q R S T U V W X Y Z) do (
    if exist "%%d:\xampp\php\php.exe" set PHP_EXE="%%d:\xampp\php\php.exe"
    if exist "%%d:\laragon\bin\php\php-*\php.exe" for /f "delims=" %%I in ('dir /b /s "%%d:\laragon\bin\php\php-*\php.exe"') do set PHP_EXE="%%I"
)

:: Jika tidak ketemu, coba gunakan php dari system PATH
if "%PHP_EXE%"=="" set PHP_EXE=php

echo Menghidupkan Server Internal di Port 8080...
start /B "" %PHP_EXE% -S localhost:8080 -t public

echo Menunggu server siap...
timeout /t 3 /nobreak > NUL

echo Membuka Aplikasi di Browser...
start http://localhost:8080/

echo.
echo ====================================================
echo   APLIKASI SUDAH BERJALAN DI BROWSER!
echo   JANGAN TUTUP jendela hitam ini selama aplikasi 
echo   masih digunakan.
echo ====================================================
pause
