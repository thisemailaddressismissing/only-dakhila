<?php
// ============================================
// Login Page — Executive Professional Design
// ============================================
require_once __DIR__ . '/auth-check.php';

// If already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'ইমেইল ও পাসওয়ার্ড আবশ্যক।';
    } else {
        try {
            $db  = getDB();
            $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: index');
                exit;
            } else {
                $error = 'ইমেইল অথবা পাসওয়ার্ড ভুল।';
            }
        } catch (PDOException $e) {
            $error = 'ডাটাবেজ ত্রুটি: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন — <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom-app.css" rel="stylesheet">
</head>
<body class="auth-wrapper">
<div class="auth-card">
    <div class="auth-brand">
        <div class="auth-brand-icon">🏛️</div>
        <h1 class="auth-title"><?= SITE_NAME ?></h1>
        <div class="auth-subtitle">ব্যবহারকারী একাউন্ট লগইন</div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger py-2 px-3 rounded-3 mb-4" style="font-size:0.875rem; border-color:#fecaca;">
        <?= h($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label-custom">ইমেইল ঠিকানা</label>
            <input type="email" name="email" class="form-control-custom" placeholder="name@example.com" value="<?= old('email') ?>" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label-custom">পাসওয়ার্ড</label>
            <input type="password" name="password" class="form-control-custom" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-app btn-app-primary w-100 py-2.5 fs-6">
            🔑 প্রবেশ করুন
        </button>
    </form>

    <div class="mt-4 pt-3 border-top text-center">
        <p class="mb-2" style="font-size:0.85rem; color:#64748b;">নতুন একাউন্ট রেজিস্টার করতে টেলিগ্রামে যোগাযোগ করুন:</p>
        <a href="https://t.me/sebaguru" target="_blank" class="btn-app w-100 py-2.5" style="background:#0088cc; color:#ffffff !important; border:none; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; border-radius:0.5rem; box-shadow:0 3px 10px rgba(0,136,204,0.25);">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.56 8.16l-2.02 9.51c-.15.68-.55.85-1.12.53l-3.08-2.27-1.49 1.43c-.16.16-.3.3-.61.3l.22-3.14 5.72-5.17c.25-.22-.05-.34-.38-.12l-7.07 4.45-3.04-.95c-.66-.21-.67-.66.14-.98l11.89-4.58c.55-.2 1.03.13.84.99z"/></svg>
            টেলিগ্রামে রেজিস্ট্রেশন করুন (@sebaguru)
        </a>
</div>

<!-- Floating Telegram Chat Bubble -->
<a href="https://t.me/sebaguru" target="_blank" class="telegram-bubble" title="টেলিগ্রাম সাপোর্ট (@sebaguru)">
    <svg viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.56 8.16l-2.02 9.51c-.15.68-.55.85-1.12.53l-3.08-2.27-1.49 1.43c-.16.16-.3.3-.61.3l.22-3.14 5.72-5.17c.25-.22-.05-.34-.38-.12l-7.07 4.45-3.04-.95c-.66-.21-.67-.66.14-.98l11.89-4.58c.55-.2 1.03.13.84.99z"/></svg>
</a>

</body>
</html>
