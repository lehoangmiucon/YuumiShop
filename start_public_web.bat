@echo off
title Public YuumiShop Web

echo === Starting Docker containers ===
docker-compose up -d
echo Docker started!
echo.

echo === Starting Cloudflare Tunnel ===
cloudflared tunnel --url http://localhost:8080

pause
