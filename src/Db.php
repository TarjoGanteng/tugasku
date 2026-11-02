<?php
/**
 * TugasKu - Database Connection (PDO)
 * NFR-05: prepared statements, ATTR_EMULATE_PREPARES=false, ERRMODE_EXCEPTION
 */

class Db
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                defined('DB_PORT') ? DB_PORT : '3306',
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // NFR-05
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                // Set timezone WIB (A06)
                self::$instance->exec("SET time_zone = '+07:00'");
                self::$instance->exec("SET NAMES utf8mb4");
            } catch (PDOException $e) {
                // Jangan expose detail ke client (§7)
                error_log('[TugasKu] DB Connection Error: ' . $e->getMessage());
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => ['code' => 'SERVER_ERROR', 'message' => 'Koneksi database gagal.']]);
                exit;
            }
        }

        return self::$instance;
    }

    // Singleton - tidak boleh di-clone atau di-unserialize
    private function __clone() {}
}
