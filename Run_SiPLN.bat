@echo off
echo Menyalakan Server (Apache & MySQL)...
start "" "D:\xampp\xampp_start.exe"
timeout /t 3 /nobreak >nul
start http://localhost/webapp/public
exit