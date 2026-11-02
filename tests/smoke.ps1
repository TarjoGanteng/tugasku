# TugasKu - API Smoke Test PowerShell (§13)
# Menguji kepatuhan penuh terhadap spesifikasi brief §6 & §13

$baseUrl = "http://127.0.0.1:8000/api"
$failCount = 0

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "       TUGASKU API SMOKE TEST (PS)        " -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

function Assert-Step($title, $condition, $info="") {
    if ($condition) {
        Write-Host " [PASS] $title" -ForegroundColor Green
    } else {
        Write-Host " [FAIL] $title $info" -ForegroundColor Red
        $script:failCount++
    }
}

# 1. Ringkasan
try {
    $res = Invoke-RestMethod -Uri "$baseUrl/ringkasan" -Method Get
    Assert-Step "1. GET /api/ringkasan" ($res.data.persen_selesai -ne $null -and $res.data.total -ge 0)
} catch {
    Assert-Step "1. GET /api/ringkasan" $false $_.Exception.Message
}

# 2. Get Tugas
try {
    $res = Invoke-RestMethod -Uri "$baseUrl/tugas" -Method Get
    Assert-Step "2. GET /api/tugas" ($res.data.Count -gt 0 -and $res.meta.waktu_server -ne $null)
} catch {
    Assert-Step "2. GET /api/tugas" $false $_.Exception.Message
}

# 3. Create MK
$mkId = $null
try {
    $body = @{
        nama_mk = "Mata Kuliah Uji PS"
        kode_mk = "PS" + (Get-Random -Minimum 1000 -Maximum 9999)
        sks = 3
        warna_label = "#3B82F6"
    } | ConvertTo-Json
    $res = Invoke-RestMethod -Uri "$baseUrl/matakuliah" -Method Post -Body $body -ContentType "application/json" -Headers @{ "X-Requested-With" = "TugasKu" }
    $mkId = $res.data.id_mk
    Assert-Step "3. POST /api/matakuliah (ID: $mkId)" ($mkId -gt 0)
} catch {
    Assert-Step "3. POST /api/matakuliah" $false $_.Exception.Message
}

# 4. Create Tugas
$tugasId = $null
try {
    $dl = (Get-Date).AddDays(2).ToString("yyyy-MM-dd 23:59:00")
    $body = @{
        judul = "Tugas Uji PowerShell"
        id_mk = $mkId
        deadline = $dl
        prioritas = "Sedang"
        deskripsi = "Catatan uji otomatis via PowerShell"
    } | ConvertTo-Json
    $res = Invoke-RestMethod -Uri "$baseUrl/tugas" -Method Post -Body $body -ContentType "application/json" -Headers @{ "X-Requested-With" = "TugasKu" }
    $tugasId = $res.data.id_tugas
    Assert-Step "4. POST /api/tugas (ID: $tugasId)" ($tugasId -gt 0)
} catch {
    Assert-Step "4. POST /api/tugas" $false $_.Exception.Message
}

# 5. Status Transition to Dalam Proses
try {
    $body = @{ status = "Dalam Proses" } | ConvertTo-Json
    $res = Invoke-RestMethod -Uri "$baseUrl/tugas/$tugasId/status" -Method Patch -Body $body -ContentType "application/json" -Headers @{ "X-Requested-With" = "TugasKu" }
    Assert-Step "5. PATCH /api/tugas/$tugasId/status (Dalam Proses)" ($res.data.status_baru -eq "Dalam Proses")
} catch {
    Assert-Step "5. PATCH status" $false $_.Exception.Message
}

# 6. Status Transition to Selesai
try {
    $body = @{ status = "Selesai" } | ConvertTo-Json
    $res = Invoke-RestMethod -Uri "$baseUrl/tugas/$tugasId/status" -Method Patch -Body $body -ContentType "application/json" -Headers @{ "X-Requested-With" = "TugasKu" }
    Assert-Step "6. PATCH /api/tugas/$tugasId/status (Selesai)" ($res.data.status_baru -eq "Selesai" -and $res.data.tugas.selesai_pada -ne $null)
} catch {
    Assert-Step "6. PATCH status" $false $_.Exception.Message
}

# 7. Check Detail & Riwayat
try {
    $res = Invoke-RestMethod -Uri "$baseUrl/tugas/$tugasId" -Method Get
    $riwayatCount = $res.data.riwayat.Count
    Assert-Step "7. GET /api/tugas/$tugasId (Riwayat: $riwayatCount log)" ($riwayatCount -eq 2)
} catch {
    Assert-Step "7. GET detail" $false $_.Exception.Message
}

# 8. Protection: Delete MK with task must fail (MK_MASIH_DIPAKAI)
try {
    $body = @{ konfirmasi = $true } | ConvertTo-Json
    $res = Invoke-RestMethod -Uri "$baseUrl/matakuliah/$mkId" -Method Delete -Body $body -ContentType "application/json" -Headers @{ "X-Requested-With" = "TugasKu" }
    Assert-Step "8. DELETE MK with active task fails" $false "Expected 409 MK_MASIH_DIPAKAI but succeeded"
} catch {
    $stream = $_.Exception.Response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $respBody = $reader.ReadToEnd() | ConvertFrom-Json
    $code = $respBody.error.code
    Assert-Step "8. DELETE MK with active task fails (MK_MASIH_DIPAKAI)" ($code -eq "MK_MASIH_DIPAKAI")
}

# 9. Delete Tugas
try {
    $body = @{ konfirmasi = $true } | ConvertTo-Json
    $res = Invoke-RestMethod -Uri "$baseUrl/tugas/$tugasId" -Method Delete -Body $body -ContentType "application/json" -Headers @{ "X-Requested-With" = "TugasKu" }
    Assert-Step "9. DELETE /api/tugas/$tugasId" ($res.message -like "*dihapus*")
} catch {
    Assert-Step "9. DELETE tugas" $false $_.Exception.Message
}

# 10. Delete MK now succeeds
try {
    $body = @{ konfirmasi = $true } | ConvertTo-Json
    $res = Invoke-RestMethod -Uri "$baseUrl/matakuliah/$mkId" -Method Delete -Body $body -ContentType "application/json" -Headers @{ "X-Requested-With" = "TugasKu" }
    Assert-Step "10. DELETE /api/matakuliah/$mkId" ($res.message -like "*dihapus*")
} catch {
    Assert-Step "10. DELETE MK" $false $_.Exception.Message
}

# 11. Search & Filter test
try {
    $res = Invoke-RestMethod -Uri "$baseUrl/tugas?status=Dalam+Proses&q=Web" -Method Get
    Assert-Step "11. GET /api/tugas?status=Dalam+Proses&q=Web (Hasil: $($res.data.Count))" ($res.data.Count -gt 0)
} catch {
    Assert-Step "11. GET search" $false $_.Exception.Message
}

Write-Host "==========================================" -ForegroundColor Cyan
if ($failCount -eq 0) {
    Write-Host "SEMUA UJI BERHASIL! (11/11 PASS)" -ForegroundColor Green
    exit 0
} else {
    Write-Host "TERDAPAT $failCount KEGAGALAN UJI." -ForegroundColor Red
    exit 1
}
