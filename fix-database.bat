@echo off
echo ================================================
echo   DATABASE SCHEMA FIX UTILITY
echo ================================================
echo.
echo This will check and fix all database tables...
echo.
pause

php fix-all-database-issues.php

echo.
echo ================================================
echo   Press any key to exit...
echo ================================================
pause > nul
