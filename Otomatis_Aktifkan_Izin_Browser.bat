@echo off
setlocal enabledelayedexpansion
title Otomatisasi Izin Browser SiPLN
color 0A

:: Cek izin Administrator, jika belum maka minta akses Admin otomatis
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Meminta izin Administrator Windows...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

echo =======================================================
echo        PENGATURAN OTOMATIS IZIN BROWSER SIPLN
echo =======================================================
echo.
echo Sedang mendaftarkan domain http://sipln.tail2af614.ts.net sebagai 
echo Secure Origin ke sistem Registry Windows (Edge, Chrome & Brave)...
echo.

:: 1. Microsoft Edge - OverrideSecurityRestrictionsOnInsecureOrigin
reg add "HKLM\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "2" /t REG_SZ /d "http://sipln" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "3" /t REG_SZ /d "http://sipln.tail2af614.ts.net:80" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "4" /t REG_SZ /d "http://*.tail2af614.ts.net" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "5" /t REG_SZ /d "http://*.ts.net" /f >nul 2>&1

reg add "HKCU\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "2" /t REG_SZ /d "http://sipln" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "3" /t REG_SZ /d "http://sipln.tail2af614.ts.net:80" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "4" /t REG_SZ /d "http://*.tail2af614.ts.net" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Microsoft\Edge\OverrideSecurityRestrictionsOnInsecureOrigin" /v "5" /t REG_SZ /d "http://*.ts.net" /f >nul 2>&1

:: 2. Microsoft Edge - InsecureContentAllowedForUrls
reg add "HKLM\SOFTWARE\Policies\Microsoft\Edge\InsecureContentAllowedForUrls" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net/*" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Microsoft\Edge\InsecureContentAllowedForUrls" /v "2" /t REG_SZ /d "http://sipln/*" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Microsoft\Edge\InsecureContentAllowedForUrls" /v "3" /t REG_SZ /d "[*.]tail2af614.ts.net" /f >nul 2>&1

reg add "HKCU\SOFTWARE\Policies\Microsoft\Edge\InsecureContentAllowedForUrls" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net/*" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Microsoft\Edge\InsecureContentAllowedForUrls" /v "2" /t REG_SZ /d "http://sipln/*" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Microsoft\Edge\InsecureContentAllowedForUrls" /v "3" /t REG_SZ /d "[*.]tail2af614.ts.net" /f >nul 2>&1

:: 3. Google Chrome - OverrideSecurityRestrictionsOnInsecureOrigin
reg add "HKLM\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "2" /t REG_SZ /d "http://sipln" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "3" /t REG_SZ /d "http://sipln.tail2af614.ts.net:80" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "4" /t REG_SZ /d "http://*.tail2af614.ts.net" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "5" /t REG_SZ /d "http://*.ts.net" /f >nul 2>&1

reg add "HKCU\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "2" /t REG_SZ /d "http://sipln" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "3" /t REG_SZ /d "http://sipln.tail2af614.ts.net:80" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "4" /t REG_SZ /d "http://*.tail2af614.ts.net" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Google\Chrome\OverrideSecurityRestrictionsOnInsecureOrigin" /v "5" /t REG_SZ /d "http://*.ts.net" /f >nul 2>&1

:: 4. Google Chrome - InsecureContentAllowedForUrls
reg add "HKLM\SOFTWARE\Policies\Google\Chrome\InsecureContentAllowedForUrls" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net/*" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Google\Chrome\InsecureContentAllowedForUrls" /v "2" /t REG_SZ /d "http://sipln/*" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\Google\Chrome\InsecureContentAllowedForUrls" /v "3" /t REG_SZ /d "[*.]tail2af614.ts.net" /f >nul 2>&1

reg add "HKCU\SOFTWARE\Policies\Google\Chrome\InsecureContentAllowedForUrls" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net/*" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Google\Chrome\InsecureContentAllowedForUrls" /v "2" /t REG_SZ /d "http://sipln/*" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\Google\Chrome\InsecureContentAllowedForUrls" /v "3" /t REG_SZ /d "[*.]tail2af614.ts.net" /f >nul 2>&1

:: 5. Brave Browser
reg add "HKLM\SOFTWARE\Policies\BraveSoftware\Brave\OverrideSecurityRestrictionsOnInsecureOrigin" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net" /f >nul 2>&1
reg add "HKLM\SOFTWARE\Policies\BraveSoftware\Brave\OverrideSecurityRestrictionsOnInsecureOrigin" /v "2" /t REG_SZ /d "http://sipln" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\BraveSoftware\Brave\OverrideSecurityRestrictionsOnInsecureOrigin" /v "1" /t REG_SZ /d "http://sipln.tail2af614.ts.net" /f >nul 2>&1
reg add "HKCU\SOFTWARE\Policies\BraveSoftware\Brave\OverrideSecurityRestrictionsOnInsecureOrigin" /v "2" /t REG_SZ /d "http://sipln" /f >nul 2>&1

echo [OK] Izin browser Secure Origin berhasil didaftarkan secara permanen!
echo.
echo Menutup dan membuka kembali browser agar pengaturan aktif...

:: Matikan proses Edge / Chrome jika ada yang berjalan agar policy ter-reload
taskkill /F /IM msedge.exe >nul 2>&1
taskkill /F /IM chrome.exe >nul 2>&1
taskkill /F /IM brave.exe >nul 2>&1

timeout /t 2 /nobreak >nul

:: Buka halaman web SiPLN di browser
start "" "http://sipln.tail2af614.ts.net/webapp"

echo.
echo =======================================================
echo   SUKSES: Browser Anda sekarang sudah mengenali:
echo   http://sipln.tail2af614.ts.net
echo   sebagai domain Secure Origin terpercaya!
echo   
echo   Halaman web SiPLN telah dibuka. Silakan klik ikon
echo   [Pasang Aplikasi / Install] di bar URL browser Anda.
echo =======================================================
echo.
pause
