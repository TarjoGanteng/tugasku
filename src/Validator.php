<?php
/**
 * TugasKu - Validator
 * NFR-05: validasi di server + query terparameter
 * Pesan validasi persis sesuai §6 kontrak API
 */

class Validator
{
    private array $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Bersihkan string: trim, hapus tag HTML, normalisasi spasi ganda
     */
    public static function sanitizeString(?string $value): string
    {
        if ($value === null) return '';
        $value = strip_tags($value);          // hapus tag HTML (§7)
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value); // spasi ganda → satu
        return $value;
    }

    /**
     * Validasi judul tugas
     * BR-06: 1-150 karakter
     */
    public function validateJudul(?string $value): string
    {
        $val = self::sanitizeString($value);
        if ($val === '') {
            $this->errors['judul'] = 'Judul tugas wajib diisi.';
        } elseif (mb_strlen($val) > 150) {
            $this->errors['judul'] = 'Judul maksimal 150 karakter.';
        }
        return $val;
    }

    /**
     * Validasi id_mk
     */
    public function validateIdMk(mixed $value, PDO $pdo): int
    {
        if ($value === null || $value === '') {
            $this->errors['id_mk'] = 'Mata kuliah wajib dipilih.';
            return 0;
        }

        if (!is_numeric($value) || (int)$value <= 0) {
            $this->errors['id_mk'] = 'Mata kuliah wajib dipilih.';
            return 0;
        }

        $id = (int)$value;
        $stmt = $pdo->prepare('SELECT id_mk FROM mata_kuliah WHERE id_mk = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            $this->errors['id_mk'] = 'Mata kuliah wajib dipilih.';
            return 0;
        }

        return $id;
    }

    /**
     * Validasi deadline
     * BR-02: wajib, tidak boleh di masa lalu saat tambah/ubah deadline
     * @param bool $checkPast apakah cek tanggal lampau (false saat ubah field lain)
     */
    public function validateDeadline(?string $value, bool $checkPast = true): string
    {
        if ($value === null || trim($value) === '') {
            $this->errors['deadline'] = 'Deadline wajib diisi.';
            return '';
        }

        $value = trim($value);

        // Normalisasi format: YYYY-MM-DDTHH:MM atau YYYY-MM-DD HH:MM[:SS]
        $value = str_replace('T', ' ', $value);

        // Parse tanggal
        $parsed = null;
        foreach (['Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            $dt = DateTime::createFromFormat($format, $value, new DateTimeZone('Asia/Jakarta'));
            if ($dt && $dt->format($format === 'Y-m-d H:i:s' ? 'Y-m-d H:i:s' : 'Y-m-d H:i') === $value) {
                $parsed = $dt;
                break;
            }
        }

        if ($parsed === null) {
            $this->errors['deadline'] = 'Deadline wajib diisi.';
            return '';
        }

        // Cek tanggal nyata (mis. 2026-02-30 tidak valid)
        $checkDate = checkdate(
            (int)$parsed->format('m'),
            (int)$parsed->format('d'),
            (int)$parsed->format('Y')
        );
        if (!$checkDate) {
            $this->errors['deadline'] = 'Deadline wajib diisi.';
            return '';
        }

        // BR-02: tidak boleh di masa lalu
        if ($checkPast) {
            $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
            if ($parsed <= $now) {
                $this->errors['deadline'] = 'Deadline tidak boleh di tanggal lampau.';
                return '';
            }
        }

        return $parsed->format('Y-m-d H:i:s');
    }

    /**
     * Validasi prioritas
     * BR-06: opsional, enum, kosong → Sedang
     */
    public function validatePrioritas(?string $value): string
    {
        $allowed = ['Rendah', 'Sedang', 'Tinggi'];

        if ($value === null || trim($value) === '') {
            return 'Sedang'; // default
        }

        $val = trim($value);
        if (!in_array($val, $allowed, true)) {
            $this->errors['prioritas'] = 'Prioritas tidak valid.';
            return 'Sedang';
        }

        return $val;
    }

    /**
     * Validasi deskripsi/catatan
     * Opsional, maks 500 karakter, tag dibuang
     */
    public function validateDeskripsi(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $val = strip_tags(trim($value));
        if (mb_strlen($val) > 500) {
            $this->errors['deskripsi'] = 'Catatan maksimal 500 karakter.';
            return '';
        }

        return $val;
    }

    /**
     * Validasi status untuk PATCH
     */
    public function validateStatus(?string $value): string
    {
        $allowed = ['Belum', 'Dalam Proses', 'Selesai'];

        if ($value === null || trim($value) === '') {
            $this->errors['status'] = 'Status harus Belum, Dalam Proses, atau Selesai.';
            return '';
        }

        $val = trim($value);
        if (!in_array($val, $allowed, true)) {
            $this->errors['status'] = 'Status harus Belum, Dalam Proses, atau Selesai.';
            return '';
        }

        return $val;
    }

    /**
     * Validasi field terlarang di PUT
     */
    public function validateNoForbiddenFields(array $data): void
    {
        $forbidden = ['status', 'selesai_pada', 'id_tugas', 'created_at', 'updated_at'];
        foreach ($forbidden as $field) {
            if (array_key_exists($field, $data)) {
                $this->errors[$field] = 'Field ini tidak boleh dikirim atau diubah.';
            }
        }
    }

    /**
     * Validasi konfirmasi hapus
     */
    public function validateKonfirmasi(mixed $value): bool
    {
        if ($value !== true) {
            $this->errors['konfirmasi'] = 'Konfirmasi penghapusan diperlukan.';
            return false;
        }
        return true;
    }

    /**
     * Validasi query search
     */
    public static function validateSearchQuery(?string $q): ?string
    {
        if ($q === null || trim($q) === '') return null;
        $q = trim($q);
        if (mb_strlen($q) > 150) return null; // akan ditangani di router
        return $q;
    }
}
