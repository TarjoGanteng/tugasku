<?php
/**
 * TugasKu - Response Helper
 * Format sukses: {"data": ..., "message": "..."}
 * Format error:  {"error": {"code": "...", "message": "...", "fields": {...}}}
 */

class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff'); // §7
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data, string $message = '', int $status = 200, array $meta = []): void
    {
        $payload = ['data' => $data, 'message' => $message];
        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }
        self::json($payload, $status);
    }

    public static function error(string $code, string $message, int $status = 400, array $fields = []): void
    {
        $err = ['code' => $code, 'message' => $message];
        if (!empty($fields)) {
            $err['fields'] = $fields;
        }
        self::json(['error' => $err], $status);
    }

    public static function validationError(array $fields, string $message = 'Data tidak valid.'): void
    {
        self::error('VALIDATION_ERROR', $message, 422, $fields);
    }

    public static function notFound(string $message = 'Data tidak ditemukan.'): void
    {
        self::error('NOT_FOUND', $message, 404);
    }

    public static function serverError(string $devMessage = ''): void
    {
        if ($devMessage) {
            error_log('[TugasKu] Server Error: ' . $devMessage);
        }
        // Pesan aman - tanpa SQL, path, atau stack trace (§6, §7)
        self::error('SERVER_ERROR', 'Terjadi kesalahan pada server. Silakan coba lagi.', 500);
    }

    public static function badRequest(string $message = 'Permintaan tidak valid.'): void
    {
        self::error('BAD_REQUEST', $message, 400);
    }
}
