#!/bin/bash
# ================================================================
# DEPLOY SCRIPT - Dijalankan otomatis oleh webhook saat ada push
# ================================================================

set -e

PROJECT_DIR="$HOME/projects/RM_TRANS-Monitoring-Bengkel"

echo "========================================="
echo " RM TRANS - Auto Deploy"
echo " $(date '+%Y-%m-%d %H:%M:%S')"
echo "========================================="

cd "$PROJECT_DIR"

echo "[1/4] Pulling latest code dari GitHub..."
git pull origin main

echo "[2/4] Rebuild dan restart containers..."
docker compose up --build -d

echo "[3/4] Menunggu containers siap (30 detik)..."
sleep 30

echo "[4/4] Membersihkan image lama yang tidak terpakai..."
docker image prune -f

echo "========================================="
echo " Deploy selesai! Aplikasi sudah update."
echo "========================================="
