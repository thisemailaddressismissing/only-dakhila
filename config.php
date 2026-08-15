<?php
// ============================================
// Database Configuration (Environment / Neon PostgreSQL)
// ============================================
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'pgsql'); // 'pgsql' for Neon Postgres, 'mysql' for MySQL
define('DB_HOST', getenv('DB_HOST') ?: 'ep-steep-field-azbpni91-pooler.c-3.ap-southeast-1.aws.neon.tech');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'neondb');
define('DB_USER', getenv('DB_USER') ?: 'neondb_owner');
define('DB_PASS', getenv('DB_PASS') ?: 'npg_9pzPHG2dftiU');
define('DB_SSLMODE', getenv('DB_SSLMODE') ?: 'require');
$defaultEndpoint = explode('.', DB_HOST)[0] ?? 'ep-steep-field-azbpni91-pooler';
define('DB_ENDPOINT', getenv('DB_ENDPOINT') ?: $defaultEndpoint);

// ============================================
// Application Configuration
// ============================================
define('SITE_NAME', getenv('SITE_NAME') ?: 'ভূমি উন্নয়ন কর দাখিলা');
define('COST_PER_SUBMIT', getenv('COST_PER_SUBMIT') ? (float)getenv('COST_PER_SUBMIT') : 50);
define('DEFAULT_BALANCE', getenv('DEFAULT_BALANCE') ? (float)getenv('DEFAULT_BALANCE') : 0);
define('SESSION_LIFETIME', 86400);  // 24 hours

// ============================================
// Database Connection
// ============================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        if (DB_DRIVER === 'pgsql') {
            $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';sslmode=' . DB_SSLMODE;
            if (DB_ENDPOINT) {
                $dsn .= ";options='endpoint=" . DB_ENDPOINT . "'";
            }
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => true,
            ]);
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
    }
    return $pdo;
}

// Database-agnostic last insert ID helper
function getLastInsertId(PDO $db, string $sequenceName): int {
    if (DB_DRIVER === 'pgsql') {
        return (int) $db->lastInsertId($sequenceName);
    }
    return (int) $db->lastInsertId();
}

// HTML escape helper
if (!function_exists('h')) {
    function h(?string $s): string {
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Settings Helper Functions
function getSetting(string $key, string $default = ''): string {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT value FROM settings WHERE key = :k");
        $stmt->execute([':k' => $key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? (string)$val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function setSetting(string $key, string $value): bool {
    try {
        $db = getDB();
        if (DB_DRIVER === 'pgsql') {
            $stmt = $db->prepare("
                INSERT INTO settings (key, value, updated_at) VALUES (:k, :v, CURRENT_TIMESTAMP)
                ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value, updated_at = CURRENT_TIMESTAMP
            ");
        } else {
            $stmt = $db->prepare("
                INSERT INTO settings (`key`, `value`) VALUES (:k, :v)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
            ");
        }
        return $stmt->execute([':k' => $key, ':v' => $value]);
    } catch (Exception $e) {
        return false;
    }
}
