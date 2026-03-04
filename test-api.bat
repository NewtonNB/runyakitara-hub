@echo off
echo ================================================
echo   API v1 Testing Utility
echo ================================================
echo.
echo Running API tests...
echo.

php test-api-simple.php

echo.
echo ================================================
echo   Opening API Tester in Browser...
echo ================================================
echo.

start http://localhost/RUNYAKITARA%%20HUB/test-api.html

echo.
echo You can also view the API documentation at:
echo http://localhost/RUNYAKITARA%%20HUB/api/v1/docs.php
echo.
pause
