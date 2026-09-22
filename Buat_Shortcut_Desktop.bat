@echo off
setlocal enabledelayedexpansion
title Pembuat Pintasan Desktop SiPLN
color 0B

echo =======================================================
echo          PEMBUAT PINTASAN DESKTOP APLIKASI SIPLN
echo =======================================================
echo.

set CURRENT_DIR=%~dp0
set CURRENT_DIR=%CURRENT_DIR:~0,-1%
set ICON_PATH=%CURRENT_DIR%\public\assets\logopln.ico

:: Cek browser Chrome atau Edge untuk App Mode (jendela tanpa URL bar)
set BROWSER_EXE=
if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
    set BROWSER_EXE="%ProgramFiles%\Google\Chrome\Application\chrome.exe"
) else if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
    set BROWSER_EXE="%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe"
) else if exist "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" (
    set BROWSER_EXE="%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe"
) else if exist "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" (
    set BROWSER_EXE="%ProgramFiles%\Microsoft\Edge\Application\msedge.exe"
)

:: Target URL: Gunakan URL Tailscale sipln
set TARGET_URL=http://sipln.tail2af614.ts.net/webapp

:: Path Shortcut di Desktop pengguna
set DESKTOP_DIR=%USERPROFILE%\Desktop
set SHORTCUT_PATH=%DESKTOP_DIR%\SiPLN.lnk

echo [1/3] Menyiapkan Pintasan Aplikasi...
echo Target URL : %TARGET_URL%
echo Ikon       : %ICON_PATH%
echo.

:: Buat VBScript sementara untuk membuat .lnk
set VBS_SCRIPT=%TEMP%\create_sipln_shortcut.vbs

if defined BROWSER_EXE (
    echo Set oWS = WScript.CreateObject("WScript.Shell") > "%VBS_SCRIPT%"
    echo sLinkFile = "%SHORTCUT_PATH%" >> "%VBS_SCRIPT%"
    echo Set oLink = oWS.CreateShortcut(sLinkFile) >> "%VBS_SCRIPT%"
    echo oLink.TargetPath = %BROWSER_EXE% >> "%VBS_SCRIPT%"
    echo oLink.Arguments = "--app=%TARGET_URL% --unsafely-treat-insecure-origin-as-secure=http://sipln.tail2af614.ts.net" >> "%VBS_SCRIPT%"
    echo oLink.Description = "Sistem Informasi Santri Luar Negeri" >> "%VBS_SCRIPT%"
    if exist "%ICON_PATH%" (
        echo oLink.IconLocation = "%ICON_PATH%, 0" >> "%VBS_SCRIPT%"
    )
    echo oLink.WorkingDirectory = "%CURRENT_DIR%" >> "%VBS_SCRIPT%"
    echo oLink.Save >> "%VBS_SCRIPT%"
) else (
    echo Set oWS = WScript.CreateObject("WScript.Shell") > "%VBS_SCRIPT%"
    echo sLinkFile = "%DESKTOP_DIR%\SiPLN.url" >> "%VBS_SCRIPT%"
    echo Set oLink = oWS.CreateShortcut(sLinkFile) >> "%VBS_SCRIPT%"
    echo oLink.TargetPath = "%TARGET_URL%" >> "%VBS_SCRIPT%"
    echo oLink.Save >> "%VBS_SCRIPT%"
)

echo [2/3] Memasang Pintasan ke Desktop...
cscript //nologo "%VBS_SCRIPT%"
del "%VBS_SCRIPT%"

echo [3/3] Selesai!
echo.
echo =======================================================
echo   SUKSES: Pintasan aplikasi SiPLN telah berhasil dibuat 
echo   di Desktop Anda! (Target: %TARGET_URL%)
echo   
echo   Anda dapat langsung mengeklik ikon "SiPLN" di Desktop 
echo   untuk membuka aplikasi dalam jendela mandiri.
echo =======================================================
echo.
pause
