#!/bin/bash

# ==============================================================================
# SCRIPT DEPLOYMENT OTOMATIS:IT-Submissions
# Mendeskripsikan proses otomatis menarik kode, menginstall dependency,
# menjalankan migrasi, membersihkan cache, dan merestart container Docker.
# ==============================================================================

# Tangkap error di baris manapun dan langsung hentikan script
set -e

# ==================== KONFIGURASI ====================
CONTAINER="it_app"
ARTISAN="docker exec $CONTAINER php artisan"
BRANCH="main"
LOG_FILE="storage/logs/deploy_$(date +%Y%m%d_%H%M%S).log"

# Kode warna untuk output terminal yang lebih rapi
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# ==================== FUNGSI HELPER ====================
print_step() {
    echo -e "${BLUE}======================================================================${NC}"
    echo -e "${GREEN}==> [$1]${NC}"
    echo -e "${BLUE}======================================================================${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ ERROR: $1${NC}"
}

# Menyimpan riwayat commit sebelum pull untuk opsi rollback (opsional)
PREV_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")

# ==================== PROSES UTAMA ====================
echo -e "${BLUE}🚀 Memulai Sinkronisasi Server IT-Submissions...${NC}"

# Opsional: Aktifkan mode maintenance selama proses berjalan
# print_step "Mengaktifkan Maintenance Mode"
# $ARTISAN down || true

print_step "Update Kode dari Git ($BRANCH)"
echo -e "Menarik kode terbaru dari remote..."
git pull origin $BRANCH

print_step "Proteksi File (Revert)"
echo -e "Membersihkan perubahan manual pada file yang diproteksi:"
echo -e "- app/Models/ArsipMutasiItem.php"
git checkout -- app/Models/ArsipMutasiItem.php
echo -e "- resources/views/print/arsip_draft.blade.php"
git checkout -- resources/views/print/arsip_draft.blade.php

print_step "Membangun Dependencies (Composer)"
echo -e "Menjalankan composer install (no-dev, optimize-autoloader)..."
docker exec $CONTAINER composer install --no-dev --optimize-autoloader

print_step "Menjalankan Migrasi Database"
echo -e "Migrating database schemas..."
$ARTISAN migrate --force

print_step "Optimalisasi Cache Server"
echo -e "Membersihkan dan me-rebuild cache (config, route, views)..."
# Menggunakan optimize:clear dulu memastikan kebersihan, baru dicache ulang oleh optimize
$ARTISAN optimize:clear
$ARTISAN optimize

#print_step "Restart Service"
#echo -e "Merestart Docker Containers (App, Nginx, DB)..."
#docker restart $CONTAINER
#docker restart it_nginx
# docker restart it_db  # Uncomment jika memang Database perlu direstart juga (Biasanya jarang direstart saat deploy)

# Opsional: Matikan mode maintenance
# print_step "Menonaktifkan Maintenance Mode"
# $ARTISAN up || true

# Cetak 5 baris terakhir dari laravel log untuk keamanan
print_step "Validasi Eksekusi Terakhir"
echo -e "Log 5 transaksi terakhir dari laravel:"
docker exec $CONTAINER tail -n 5 storage/logs/laravel.log || echo "Belum ada log."

echo -e "\n${GREEN}======================================================================${NC}"
echo -e "${GREEN}✅ DEPLOYMENT SELESAI DENGAN SEMPURNA!${NC}"
echo -e "${YELLOW}Previous Git Commit: $PREV_COMMIT${NC}"
echo -e "${GREEN}======================================================================${NC}"
