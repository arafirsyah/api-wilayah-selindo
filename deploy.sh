#!/bin/bash

# ==============================
# Skrip Ajaib Git Otomatis
# ==============================

# Cek apakah user kasih pesan commit
if [ -z "$1" ]; then
  echo "⚠️  Anda harus memberikan pesan commit!"
  echo "   Contoh: ./commit-n-push.sh \"feat: update AJAX frontend\""
  exit 1
fi

# Tambahkan semua perubahan
echo "➕ Menambahkan semua file yang berubah..."
git add .

# Commit dengan pesan
echo "💬 Commit dengan pesan: $1"
git commit -m "$1"

# Push ke branch main
echo "🚀 Push ke branch main..."
git push origin main

echo "✅ Selesai! Semua perubahan telah didorong ke GitHub. Ngopi dulu boss! ☕"
