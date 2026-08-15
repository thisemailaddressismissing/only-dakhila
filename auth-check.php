<?php
// ============================================
// Authentication Helper
// Include this on every protected page
// ============================================

require_once __DIR__ . '/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}

// ============================================
// Check if user is logged in
// If $redirect is true, redirect to login page
// ============================================
function requireAuth(bool $redirect = true): ?array {
    if (isset($_SESSION['user_id'])) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, name, email, balance, is_admin FROM users WHERE id = :id");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $user = $stmt->fetch();
            if ($user) {
                return $user;
            }
        } catch (PDOException $e) {
            // Fall through to logout
        }
    }

    // Session invalid or expired
    unset($_SESSION['user_id'], $_SESSION['user_name']);
    if ($redirect) {
        header('Location: login');
        exit;
    }
    return null;
}

// ============================================
// Get current user (no redirect)
// ============================================
function currentUser(): ?array {
    return requireAuth(false);
}

// ============================================
// Refresh user balance from DB
// ============================================
function refreshBalance(): float {
    $user = currentUser();
    if (!$user) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT balance FROM users WHERE id = :id");
    $stmt->execute([':id' => $user['id']]);
    return (float) $stmt->fetchColumn();
}

// ============================================
// Deduct balance (returns true on success)
// ============================================
function deductBalance(int $userId, float $amount, string $description = ''): bool {
    $db = getDB();
    $db->beginTransaction();
    try {
        // Lock and check balance
        $stmt = $db->prepare("SELECT balance FROM users WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $userId]);
        $balance = (float) $stmt->fetchColumn();

        if ($balance < $amount) {
            $db->rollBack();
            return false;
        }

        // Deduct
        $stmt = $db->prepare("UPDATE users SET balance = balance - :amount WHERE id = :id");
        $stmt->execute([':amount' => $amount, ':id' => $userId]);

        // Log transaction
        $stmt = $db->prepare("INSERT INTO balance_transactions (user_id, amount, type, description) VALUES (:uid, :amount, 'debit', :desc)");
        $stmt->execute([':uid' => $userId, ':amount' => $amount, ':desc' => $description]);

        $db->commit();
        return true;
    } catch (PDOException $e) {
        $db->rollBack();
        throw $e;
    }
}

// ============================================
// Add balance (admin function)
// ============================================
function addBalance(int $userId, float $amount, string $description = ''): bool {
    $db = getDB();
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("UPDATE users SET balance = balance + :amount WHERE id = :id");
        $stmt->execute([':amount' => $amount, ':id' => $userId]);

        $stmt = $db->prepare("INSERT INTO balance_transactions (user_id, amount, type, description) VALUES (:uid, :amount, 'credit', :desc)");
        $stmt->execute([':uid' => $userId, ':amount' => $amount, ':desc' => $description]);

        $db->commit();
        return true;
    } catch (PDOException $e) {
        $db->rollBack();
        throw $e;
    }
}

// ============================================
// Helper: HTML escape
// ============================================
function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// ============================================
// Helper: old form value (supports dot notation: owners.0.name)
// ============================================
function old(string $key, string $default = ''): string {
    $parts = explode('.', $key);
    $value = $_POST;
    foreach ($parts as $part) {
        if (is_array($value) && array_key_exists($part, $value)) {
            $value = $value[$part];
        } else {
            return $default;
        }
    }
    return is_array($value) ? '' : h((string) $value);
}

// ============================================
// Helper: is selected
// ============================================
function isSelected(string $field, $value): string {
    return (isset($_POST[$field]) && $_POST[$field] == $value) ? 'selected' : '';
}
