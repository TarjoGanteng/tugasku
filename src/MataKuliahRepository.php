<?php
/**
 * TugasKu - Mata Kuliah Repository
 */

class MataKuliahRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * GET /api/matakuliah - Daftar mata kuliah + jumlah tugas
     */
    public function getDaftar(): array
    {
        $stmt = $this->pdo->query(
            "SELECT mk.*,
                    COUNT(t.id_tugas) AS jumlah_tugas
             FROM mata_kuliah mk
             LEFT JOIN tugas t ON mk.id_mk = t.id_mk
             GROUP BY mk.id_mk
             ORDER BY mk.nama_mk ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * GET /api/matakuliah/{id}
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT mk.*, COUNT(t.id_tugas) AS jumlah_tugas
             FROM mata_kuliah mk
             LEFT JOIN tugas t ON mk.id_mk = t.id_mk
             WHERE mk.id_mk = :id
             GROUP BY mk.id_mk"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * POST /api/matakuliah - Tambah mata kuliah baru
     */
    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO mata_kuliah (kode_mk, nama_mk, dosen, sks, warna_label)
             VALUES (:kode_mk, :nama_mk, :dosen, :sks, :warna_label)"
        );
        $stmt->execute([
            ':kode_mk'     => $data['kode_mk'] ?: null,
            ':nama_mk'     => $data['nama_mk'],
            ':dosen'       => $data['dosen'] ?: null,
            ':sks'         => $data['sks'] ?: null,
            ':warna_label' => $data['warna_label'] ?: null,
        ]);

        $id = (int)$this->pdo->lastInsertId();
        return $this->getById($id);
    }

    /**
     * PUT /api/matakuliah/{id} - Update mata kuliah
     */
    public function update(int $id, array $data): ?array
    {
        $stmt = $this->pdo->prepare(
            "UPDATE mata_kuliah SET
                kode_mk     = :kode_mk,
                nama_mk     = :nama_mk,
                dosen       = :dosen,
                sks         = :sks,
                warna_label = :warna_label,
                updated_at  = NOW()
             WHERE id_mk = :id"
        );
        $stmt->execute([
            ':kode_mk'     => $data['kode_mk'] ?: null,
            ':nama_mk'     => $data['nama_mk'],
            ':dosen'       => $data['dosen'] ?: null,
            ':sks'         => $data['sks'] ?: null,
            ':warna_label' => $data['warna_label'] ?: null,
            ':id'          => $id,
        ]);

        if ($stmt->rowCount() === 0) {
            $check = $this->pdo->prepare('SELECT id_mk FROM mata_kuliah WHERE id_mk = :id');
            $check->execute([':id' => $id]);
            if (!$check->fetch()) return null;
        }

        return $this->getById($id);
    }

    /**
     * DELETE /api/matakuliah/{id}
     * A15: hapus yang masih punya tugas → 409 MK_MASIH_DIPAKAI
     */
    public function delete(int $id): string
    {
        // Cek ada tidak
        $check = $this->pdo->prepare('SELECT id_mk FROM mata_kuliah WHERE id_mk = :id');
        $check->execute([':id' => $id]);
        if (!$check->fetch()) return 'NOT_FOUND';

        // Cek ada tugas
        $cekTugas = $this->pdo->prepare('SELECT COUNT(*) FROM tugas WHERE id_mk = :id');
        $cekTugas->execute([':id' => $id]);
        if ($cekTugas->fetchColumn() > 0) return 'MK_MASIH_DIPAKAI';

        $stmt = $this->pdo->prepare('DELETE FROM mata_kuliah WHERE id_mk = :id');
        $stmt->execute([':id' => $id]);
        return 'OK';
    }

    /**
     * Validasi data mata kuliah
     */
    public function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        // nama_mk wajib
        $namaMk = trim(strip_tags($data['nama_mk'] ?? ''));
        if ($namaMk === '') {
            $errors['nama_mk'] = 'Nama mata kuliah wajib diisi.';
        } elseif (mb_strlen($namaMk) > 100) {
            $errors['nama_mk'] = 'Nama mata kuliah maksimal 100 karakter.';
        }

        // kode_mk opsional, unik
        $kodeMk = trim(strip_tags($data['kode_mk'] ?? ''));
        if ($kodeMk !== '') {
            if (mb_strlen($kodeMk) > 20) {
                $errors['kode_mk'] = 'Kode mata kuliah maksimal 20 karakter.';
            } else {
                $sql = 'SELECT id_mk FROM mata_kuliah WHERE kode_mk = :kode';
                if ($excludeId) $sql .= ' AND id_mk != :exclude';
                $stmt = $this->pdo->prepare($sql);
                $params = [':kode' => $kodeMk];
                if ($excludeId) $params[':exclude'] = $excludeId;
                $stmt->execute($params);
                if ($stmt->fetch()) {
                    $errors['kode_mk'] = 'Kode mata kuliah sudah digunakan.';
                }
            }
        }

        // dosen opsional, maks 100
        $dosen = trim(strip_tags($data['dosen'] ?? ''));
        if ($dosen !== '' && mb_strlen($dosen) > 100) {
            $errors['dosen'] = 'Nama dosen maksimal 100 karakter.';
        }

        // sks opsional, 1-6
        $sks = $data['sks'] ?? null;
        if ($sks !== null && $sks !== '') {
            if (!is_numeric($sks) || (int)$sks < 1 || (int)$sks > 6) {
                $errors['sks'] = 'SKS harus antara 1 dan 6.';
            }
        }

        // warna_label opsional, heks
        $warna = trim($data['warna_label'] ?? '');
        if ($warna !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $warna)) {
            $errors['warna_label'] = 'Format warna tidak valid (contoh: #4F46E5).';
        }

        return $errors;
    }
}
