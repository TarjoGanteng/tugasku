<?php
/**
 * TugasKu - Tugas Repository
 * Semua query untuk tabel tugas
 * NFR-05: prepared statements, no N+1 (JOIN)
 */

class TugasRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Format objek tugas dengan field computed (is_urgent, sisa_detik)
     */
    private function formatTugas(array $row): array
    {
        $deadlineTs = strtotime($row['deadline'] ?? '');
        $nowTs = time(); // sudah dalam WIB karena set_default_timezone

        $sisaDetik = $deadlineTs ? ($deadlineTs - $nowTs) : 0;
        $isUrgent = false;

        // BR-04: urgent jika status != Selesai DAN deadline < NOW() + 24 jam
        if ($row['status'] !== 'Selesai') {
            $isUrgent = $sisaDetik < (24 * 3600); // < 24 jam (termasuk negatif/lewat)
        }

        return [
            'id_tugas'     => (int)$row['id_tugas'],
            'id_mk'        => (int)$row['id_mk'],
            'judul'        => $row['judul'],
            'deskripsi'    => $row['deskripsi'],
            'deadline'     => $row['deadline'],
            'prioritas'    => $row['prioritas'],
            'status'       => $row['status'],
            'selesai_pada' => $row['selesai_pada'],
            'created_at'   => $row['created_at'],
            'updated_at'   => $row['updated_at'],
            'mata_kuliah'  => [
                'id_mk'       => (int)($row['mk_id_mk'] ?? $row['id_mk']),
                'nama_mk'     => $row['nama_mk'] ?? null,
                'kode_mk'     => $row['kode_mk'] ?? null,
                'dosen'       => $row['dosen'] ?? null,
                'sks'         => isset($row['sks']) ? (int)$row['sks'] : null,
                'warna_label' => $row['warna_label'] ?? null,
            ],
            'is_urgent'   => $isUrgent,
            'sisa_detik'  => (int)$sisaDetik,
        ];
    }

    /**
     * GET /api/tugas - Daftar tugas dengan filter
     * BR-03: diurutkan deadline terdekat, Selesai di bawah
     * NFR-05: satu query dengan JOIN (no N+1)
     */
    public function getDaftar(array $params = []): array
    {
        $conditions = [];
        $bindings = [];

        // Filter q (pencarian judul) - FR-08
        $q = isset($params['q']) ? trim($params['q']) : '';
        if ($q !== '') {
            // Escape karakter spesial LIKE
            $qEscaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
            $conditions[] = 'LOWER(t.judul) LIKE LOWER(:q) ESCAPE "\\\\"';
            $bindings[':q'] = '%' . $qEscaped . '%';
        }

        // Filter status (satu atau beberapa, dipisah koma)
        if (!empty($params['status'])) {
            $statuses = is_array($params['status'])
                ? $params['status']
                : array_map('trim', explode(',', $params['status']));
            $allowed = ['Belum', 'Dalam Proses', 'Selesai'];
            $validStatuses = array_filter($statuses, fn($s) => in_array($s, $allowed, true));
            if (!empty($validStatuses)) {
                $placeholders = [];
                foreach (array_values($validStatuses) as $i => $s) {
                    $key = ':status_' . $i;
                    $placeholders[] = $key;
                    $bindings[$key] = $s;
                }
                $conditions[] = 't.status IN (' . implode(',', $placeholders) . ')';
            }
        }

        // Filter id_mk (satu atau beberapa, dipisah koma)
        if (!empty($params['id_mk'])) {
            $mks = is_array($params['id_mk'])
                ? $params['id_mk']
                : array_map('trim', explode(',', $params['id_mk']));
            $validMks = array_filter($mks, fn($id) => is_numeric($id) && (int)$id > 0);
            if (!empty($validMks)) {
                $placeholders = [];
                foreach (array_values($validMks) as $i => $id) {
                    $key = ':mk_' . $i;
                    $placeholders[] = $key;
                    $bindings[$key] = (int)$id;
                }
                $conditions[] = 't.id_mk IN (' . implode(',', $placeholders) . ')';
            }
        }

        // Filter prioritas (opsional)
        if (!empty($params['prioritas'])) {
            $pris = is_array($params['prioritas'])
                ? $params['prioritas']
                : array_map('trim', explode(',', $params['prioritas']));
            $allowed = ['Rendah', 'Sedang', 'Tinggi'];
            $validPris = array_filter($pris, fn($p) => in_array($p, $allowed, true));
            if (!empty($validPris)) {
                $placeholders = [];
                foreach (array_values($validPris) as $i => $p) {
                    $key = ':pri_' . $i;
                    $placeholders[] = $key;
                    $bindings[$key] = $p;
                }
                $conditions[] = 't.prioritas IN (' . implode(',', $placeholders) . ')';
            }
        }

        // Filter tempo (opsional): hari_ini, minggu_ini, bulan_ini, semua
        $tempo = $params['tempo'] ?? 'semua';
        $allowedTempo = ['hari_ini', 'minggu_ini', 'bulan_ini', 'semua'];
        if (in_array($tempo, $allowedTempo, true) && $tempo !== 'semua') {
            switch ($tempo) {
                case 'hari_ini':
                    $conditions[] = 't.deadline < DATE_ADD(NOW(), INTERVAL 24 HOUR)';
                    break;
                case 'minggu_ini':
                    $conditions[] = 't.deadline <= DATE_ADD(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'bulan_ini':
                    $conditions[] = 't.deadline <= DATE_ADD(NOW(), INTERVAL 30 DAY)';
                    break;
            }
        }

        // Sort & Order (whitelist untuk mencegah SQL injection) §7
        $sortAllowed = ['deadline' => 't.deadline', 'judul' => 't.judul', 'created_at' => 't.created_at'];
        $orderAllowed = ['asc' => 'ASC', 'desc' => 'DESC'];

        $sort  = $sortAllowed[$params['sort'] ?? 'deadline'] ?? 't.deadline';
        $order = $orderAllowed[$params['order'] ?? 'asc'] ?? 'ASC';

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // BR-03: Selesai selalu di bawah, urut deadline terdekat
        // Satu query JOIN - no N+1 (NFR-10)
        $sql = "SELECT t.*,
                       mk.id_mk AS mk_id_mk,
                       mk.nama_mk, mk.kode_mk, mk.dosen, mk.sks, mk.warna_label
                FROM tugas t
                INNER JOIN mata_kuliah mk ON t.id_mk = mk.id_mk
                {$where}
                ORDER BY (t.status = 'Selesai') ASC, {$sort} {$order}, t.id_tugas ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll();

        return array_map([$this, 'formatTugas'], $rows);
    }

    /**
     * GET /api/tugas/{id} - Detail tugas + riwayat log
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.*,
                    mk.id_mk AS mk_id_mk,
                    mk.nama_mk, mk.kode_mk, mk.dosen, mk.sks, mk.warna_label
             FROM tugas t
             INNER JOIN mata_kuliah mk ON t.id_mk = mk.id_mk
             WHERE t.id_tugas = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        $tugas = $this->formatTugas($row);

        // Tambah riwayat log (FR-06)
        $stmtLog = $this->pdo->prepare(
            "SELECT id_log, status_lama, status_baru, waktu_perubahan
             FROM log_status_tugas
             WHERE id_tugas = :id
             ORDER BY waktu_perubahan DESC, id_log DESC"
        );
        $stmtLog->execute([':id' => $id]);
        $tugas['riwayat'] = $stmtLog->fetchAll();

        return $tugas;
    }

    /**
     * POST /api/tugas - Tambah tugas baru
     * FR-01: status selalu Belum (BR-06)
     * Proteksi duplikat: judul+id_mk+deadline identik dalam 10 detik
     */
    public function create(array $data): array
    {
        // Cek duplikat (opsional - A11 anti-duplicate)
        $stmtDup = $this->pdo->prepare(
            "SELECT id_tugas FROM tugas
             WHERE judul = :judul AND id_mk = :id_mk
             AND deadline = :deadline
             AND created_at >= DATE_SUB(NOW(), INTERVAL 10 SECOND)"
        );
        $stmtDup->execute([
            ':judul'    => $data['judul'],
            ':id_mk'    => $data['id_mk'],
            ':deadline' => $data['deadline'],
        ]);
        $existing = $stmtDup->fetch();
        if ($existing) {
            return $this->getById((int)$existing['id_tugas']);
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO tugas (id_mk, judul, deskripsi, deadline, prioritas, status, selesai_pada)
             VALUES (:id_mk, :judul, :deskripsi, :deadline, :prioritas, 'Belum', NULL)"
        );
        $stmt->execute([
            ':id_mk'     => $data['id_mk'],
            ':judul'     => $data['judul'],
            ':deskripsi' => $data['deskripsi'] ?: null,
            ':deadline'  => $data['deadline'],
            ':prioritas' => $data['prioritas'],
        ]);

        $id = (int)$this->pdo->lastInsertId();
        return $this->getById($id);
    }

    /**
     * PUT /api/tugas/{id} - Ubah detail tugas
     * Tidak boleh mengubah status, selesai_pada, id_tugas, created_at, updated_at
     */
    public function update(int $id, array $data): ?array
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tugas SET
                id_mk     = :id_mk,
                judul     = :judul,
                deskripsi = :deskripsi,
                deadline  = :deadline,
                prioritas = :prioritas,
                updated_at = NOW()
             WHERE id_tugas = :id"
        );
        $stmt->execute([
            ':id_mk'     => $data['id_mk'],
            ':judul'     => $data['judul'],
            ':deskripsi' => $data['deskripsi'] ?: null,
            ':deadline'  => $data['deadline'],
            ':prioritas' => $data['prioritas'],
            ':id'        => $id,
        ]);

        if ($stmt->rowCount() === 0) {
            // Cek apakah memang tidak ada
            $check = $this->pdo->prepare('SELECT id_tugas FROM tugas WHERE id_tugas = :id');
            $check->execute([':id' => $id]);
            if (!$check->fetch()) return null;
        }

        return $this->getById($id);
    }

    /**
     * DELETE /api/tugas/{id}
     * Log ikut terhapus via CASCADE
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tugas WHERE id_tugas = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * GET /api/ringkasan - Statistik dashboard
     * BR-10: satu query agregat, persen = Selesai/total*100 dibulatkan
     */
    public function getRingkasan(): array
    {
        $stmt = $this->pdo->query(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'Belum') AS belum,
                SUM(status = 'Dalam Proses') AS dalam_proses,
                SUM(status = 'Selesai') AS selesai,
                SUM(status != 'Selesai' AND deadline < DATE_ADD(NOW(), INTERVAL 24 HOUR)) AS urgent
             FROM tugas"
        );
        $row = $stmt->fetch();

        $total        = (int)$row['total'];
        $selesai      = (int)$row['selesai'];
        $persenSelesai = $total > 0 ? (int)round($selesai / $total * 100) : 0;

        return [
            'total'         => $total,
            'belum'         => (int)$row['belum'],
            'dalam_proses'  => (int)$row['dalam_proses'],
            'selesai'       => $selesai,
            'persen_selesai'=> $persenSelesai,
            'urgent'        => (int)$row['urgent'],
        ];
    }

    /**
     * Cek apakah deadline sudah lewat (untuk validasi PUT)
     */
    public function getDeadline(int $id): ?string
    {
        $stmt = $this->pdo->prepare('SELECT deadline FROM tugas WHERE id_tugas = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $row['deadline'] : null;
    }
}
