#!/usr/bin/env bash
# TugasKu - API Smoke Test (§13)
# Pastikan server sudah jalan: php -S localhost:8000 -t public public/router.php

BASE_URL="http://127.0.0.1:8000/api"
FAIL=0

echo "=== TUGASKU SMOKE TEST ==="

# 1. Ringkasan
echo -n "1. GET /api/ringkasan ... "
RES=$(curl -s "$BASE_URL/ringkasan")
if echo "$RES" | grep -q '"persen_selesai"'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 2. Get Daftar Tugas
echo -n "2. GET /api/tugas ... "
RES=$(curl -s "$BASE_URL/tugas")
if echo "$RES" | grep -q '"id_tugas"'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 3. Create MK
echo -n "3. POST /api/matakuliah ... "
RES=$(curl -s -X POST "$BASE_URL/matakuliah" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: TugasKu" \
  -d '{"nama_mk":"Mata Kuliah Uji","kode_mk":"UJI01","sks":2,"warna_label":"#10B981"}')
MK_ID=$(echo "$RES" | grep -o '"id_mk":[0-9]*' | head -1 | cut -d: -f2)
if [ -n "$MK_ID" ]; then
  echo "OK (ID: $MK_ID)"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 4. Create Tugas (deadline besok)
echo -n "4. POST /api/tugas ... "
TOMORROW=$(date -d "+1 day" "+%Y-%m-%d 23:59:00" 2>/dev/null || date -v+1d "+%Y-%m-%d 23:59:00" 2>/dev/null || echo "2026-11-04 23:59:00")
RES=$(curl -s -X POST "$BASE_URL/tugas" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: TugasKu" \
  -d "{\"judul\":\"Tugas Uji Otomatis\",\"id_mk\":$MK_ID,\"deadline\":\"$TOMORROW\",\"prioritas\":\"Tinggi\",\"deskripsi\":\"Catatan uji\"}")
TUGAS_ID=$(echo "$RES" | grep -o '"id_tugas":[0-9]*' | head -1 | cut -d: -f2)
if [ -n "$TUGAS_ID" ]; then
  echo "OK (ID: $TUGAS_ID)"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 5. Detail Tugas & Log Awal
echo -n "5. GET /api/tugas/$TUGAS_ID ... "
RES=$(curl -s "$BASE_URL/tugas/$TUGAS_ID")
if echo "$RES" | grep -q '"status":"Belum"'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 6. Status change to Dalam Proses
echo -n "6. PATCH /api/tugas/$TUGAS_ID/status (Dalam Proses) ... "
RES=$(curl -s -X PATCH "$BASE_URL/tugas/$TUGAS_ID/status" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: TugasKu" \
  -d '{"status":"Dalam Proses"}')
if echo "$RES" | grep -q '"Dalam Proses"'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 7. Status change to Selesai
echo -n "7. PATCH /api/tugas/$TUGAS_ID/status (Selesai) ... "
RES=$(curl -s -X PATCH "$BASE_URL/tugas/$TUGAS_ID/status" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: TugasKu" \
  -d '{"status":"Selesai"}')
if echo "$RES" | grep -q '"Selesai"'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 8. Check Riwayat Log
echo -n "8. GET /api/tugas/$TUGAS_ID (cek 2 log riwayat) ... "
RES=$(curl -s "$BASE_URL/tugas/$TUGAS_ID")
if echo "$RES" | grep -q '"riwayat"' && echo "$RES" | grep -q '"status_baru":"Selesai"'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 9. Test MK masih dipakai protection (BR-08)
echo -n "9. DELETE /api/matakuliah/$MK_ID (harus gagal MK_MASIH_DIPAKAI) ... "
RES=$(curl -s -X DELETE "$BASE_URL/matakuliah/$MK_ID" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: TugasKu" \
  -d '{"konfirmasi":true}')
if echo "$RES" | grep -q 'MK_MASIH_DIPAKAI'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 10. Delete Tugas
echo -n "10. DELETE /api/tugas/$TUGAS_ID ... "
RES=$(curl -s -X DELETE "$BASE_URL/tugas/$TUGAS_ID" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: TugasKu" \
  -d '{"konfirmasi":true}')
if echo "$RES" | grep -q '"success":true'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 11. Delete MK (sekarang harus berhasil)
echo -n "11. DELETE /api/matakuliah/$MK_ID ... "
RES=$(curl -s -X DELETE "$BASE_URL/matakuliah/$MK_ID" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: TugasKu" \
  -d '{"konfirmasi":true}')
if echo "$RES" | grep -q '"success":true'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

# 12. Search & Filter test
echo -n "12. GET /api/tugas?status=Belum&q=Web ... "
RES=$(curl -s "$BASE_URL/tugas?status=Belum&q=Web")
if echo "$RES" | grep -q '"success":true'; then
  echo "OK"
else
  echo "FAIL: $RES"
  FAIL=$((FAIL+1))
fi

echo "==========================="
if [ $FAIL -eq 0 ]; then
  echo "SEMUA UJI SMOKE BERHASIL!"
  exit 0
else
  echo "ADA $FAIL PENGUJIAN GAGAL!"
  exit 1
fi

