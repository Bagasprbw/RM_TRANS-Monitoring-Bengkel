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

echo "[2/4] Pulling latest Docker images..."
docker compose pull

echo "[3/4] Restarting containers..."
docker compose up -d
sleep 30

echo "[4/4] Membersihkan image lama yang tidak terpakai..."
docker image prune -f

echo "========================================="
echo " Deploy selesai! Aplikasi sudah update."
echo "========================================="
