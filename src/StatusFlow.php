<?php
/**
 * TugasKu - Status Flow
 * BR-05 to BR-09: semua perpindahan antar tiga status diizinkan
 * Transaksi atomik: UPDATE tugas + INSERT log
 */

class StatusFlow
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Ubah status tugas
     * @return array ['berubah' => bool, 'tugas' => array|null]
     * @throws Exception jika id tidak ada (404) atau DB error
     */
    public function ubahStatus(int $idTugas, string $statusBaru): array
    {
        $this->pdo->beginTransaction();

        try {
            // Lock baris untuk atomic update (BR-08)
            $stmt = $this->pdo->prepare(
                'SELECT id_tugas, status FROM tugas WHERE id_tugas = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $idTugas]);
            $tugas = $stmt->fetch();

            if (!$tugas) {
                $this->pdo->rollBack();
                throw new RuntimeException('NOT_FOUND:Tugas tidak ditemukan.');
            }

            $statusLama = $tugas['status'];

            // BR-08: status sama → tidak ada perubahan, tidak ada log
            if ($statusLama === $statusBaru) {
                $this->pdo->rollBack();
                return ['berubah' => false, 'status' => $statusLama];
            }

            // BR-07: set selesai_pada
            $selesaiPada = ($statusBaru === 'Selesai') ? 'NOW()' : 'NULL';

            $updateSql = "UPDATE tugas SET status = :status,
                          selesai_pada = IF(:is_selesai = 1, NOW(), NULL),
                          updated_at = NOW()
                          WHERE id_tugas = :id";
            $stmtUpdate = $this->pdo->prepare($updateSql);
            $stmtUpdate->execute([
                ':status'     => $statusBaru,
                ':is_selesai' => ($statusBaru === 'Selesai') ? 1 : 0,
                ':id'         => $idTugas,
            ]);

            // INSERT log (BR-05)
            $stmtLog = $this->pdo->prepare(
                'INSERT INTO log_status_tugas (id_tugas, status_lama, status_baru)
                 VALUES (:id_tugas, :status_lama, :status_baru)'
            );
            $stmtLog->execute([
                ':id_tugas'    => $idTugas,
                ':status_lama' => $statusLama,
                ':status_baru' => $statusBaru,
            ]);

            $this->pdo->commit();

            return ['berubah' => true, 'status_lama' => $statusLama, 'status_baru' => $statusBaru];

        } catch (RuntimeException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log('[TugasKu] StatusFlow PDO Error: ' . $e->getMessage());
            throw new RuntimeException('SERVER_ERROR:Gagal mengubah status tugas.');
        }
    }
}
