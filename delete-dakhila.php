<?php
// ============================================
// Delete Dakhila — Protected Page
// ============================================
require_once __DIR__ . '/auth-check.php';
$user = requireAuth();

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM dakhila WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $id, ':uid' => $user['id']]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['flash_success'] = "✅ দাখিলা সফলভাবে মুছে ফেলা হয়েছে!";
        } else {
            $_SESSION['flash_error'] = "❌ দাখিলা পাওয়া যায়নি অথবা মোছার অনুমতি নেই।";
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "❌ ডাটাবেজ ত্রুটি: " . $e->getMessage();
    }
}

header('Location: index');
exit;
