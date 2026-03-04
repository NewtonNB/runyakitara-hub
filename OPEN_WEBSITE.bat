@echo off
echo ========================================
echo   Runyakitara Hub - Opening Website
echo ========================================
echo.
echo Starting PHP Server...
start /B php -S localhost:8000
timeout /t 2 /nobreak >nul
echo.
echo Opening website in your browser...
start http://localhost:8000/index.html
echo.
echo ========================================
echo   Server is running on localhost:8000
echo ========================================
echo.
echo Press Ctrl+C to stop the server
echo.
pause
