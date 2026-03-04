@echo off
echo ========================================
echo   Opening Runyakitara Hub Admin
echo ========================================
echo.
echo Opening admin login page...
start http://localhost:8000/admin/login.php
echo.
echo Login Credentials:
echo Username: admin
echo Password: admin123
echo.
echo ========================================
pause
