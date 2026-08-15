<?php
// ============================================
// Admin Panel — User & Balance Management
// ============================================
require_once __DIR__ . '/auth-check.php';

$user = requireAuth();

// Check admin permission (If user is not admin, deny access)
$db = getDB();
$stmt = $db->prepare("SELECT is_admin FROM users WHERE id = :id");
$stmt->execute([':id' => $user['id']]);
$isAdmin = (bool) $stmt->fetchColumn();

if (!$isAdmin) {
    echo "<!DOCTYPE html><html lang='bn'><head><meta charset='UTF-8'><title>Access Denied</title><link href='css/custom-app.css' rel='stylesheet'></head><body class='auth-wrapper'><div class='auth-card text-center'><h3 class='text-danger mb-3'>🚫 অ্যাক্সেস প্রত্যাখ্যাত</h3><p class='text-muted'>আপনার এই পেজে প্রবেশের অনুমতি নেই।</p><a href='index' class='btn-app btn-app-primary mt-3'>🏠 ড্যাশবোর্ডে ফিরে যান</a></div></body></html>";
    exit;
}

$message = '';
$error = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Add/Recharge Balance
    if ($action === 'add_balance') {
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $amount = (float) ($_POST['amount'] ?? 0);
        $desc = trim($_POST['description'] ?? 'এডমিন কর্তৃক ব্যালেন্স যোগ');

        if ($targetUserId > 0 && $amount > 0) {
            try {
                addBalance($targetUserId, $amount, $desc);
                $message = "সফলভাবে ৳" . number_format($amount, 2) . " ব্যালেন্স যোগ করা হয়েছে।";
            } catch (Exception $e) {
                $error = "ব্যালেন্স যোগ করতে সমস্যা হয়েছে: " . $e->getMessage();
            }
        } else {
            $error = "সঠিক ইউজার এবং টাকার পরিমাণ দিন।";
        }
    }

    // 2. Deduct Balance
    elseif ($action === 'deduct_balance') {
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $amount = (float) ($_POST['amount'] ?? 0);
        $desc = trim($_POST['description'] ?? 'এডমিন কর্তৃক ব্যালেন্স কর্তন');

        if ($targetUserId > 0 && $amount > 0) {
            try {
                if (deductBalance($targetUserId, $amount, $desc)) {
                    $message = "সফলভাবে ৳" . number_format($amount, 2) . " ব্যালেন্স কর্তন করা হয়েছে।";
                } else {
                    $error = "ব্যবহারকারীর পর্যাপ্ত ব্যালেন্স নেই।";
                }
            } catch (Exception $e) {
                $error = "ব্যালেন্স কর্তন করতে সমস্যা হয়েছে: " . $e->getMessage();
            }
        } else {
            $error = "সঠিক ইউজার এবং টাকার পরিমাণ দিন।";
        }
    }

    // 3. Create New User
    elseif ($action === 'create_user') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $initialBalance = (float) ($_POST['initial_balance'] ?? 0);
        $isUserAdmin = isset($_POST['is_admin']) ? 1 : 0;

        if ($name === '' || $email === '' || $password === '') {
            $error = "নাম, ইমেইল এবং পাসওয়ার্ড আবশ্যক।";
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password, balance, is_admin) VALUES (:name, :email, :pass, :bal, :admin)");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':pass' => $hash,
                    ':bal' => $initialBalance,
                    ':admin' => $isUserAdmin
                ]);
                $newId = getLastInsertId($db, 'users_id_seq');
                if ($initialBalance > 0) {
                    $db->prepare("INSERT INTO balance_transactions (user_id, amount, type, description) VALUES (:uid, :amount, 'credit', 'প্রাথমিক ব্যালেন্স (এডমিন)')")
                       ->execute([':uid' => $newId, ':amount' => $initialBalance]);
                }
                $message = "নতুন ব্যবহারকারী '$name' সফলভাবে তৈরি করা হয়েছে।";
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), 'uq_email')) {
                    $error = "এই ইমেইল দিয়ে ইতোমধ্যে একটি একাউন্ট বিদ্যমান।";
                } else {
                    $error = "ডাটাবেজ ত্রুটি: " . $e->getMessage();
                }
            }
        }
    }

    // 4. Change Password
    elseif ($action === 'change_password') {
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if ($targetUserId > 0 && strlen($newPassword) >= 6) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password = :pass WHERE id = :id");
            $stmt->execute([':pass' => $hash, ':id' => $targetUserId]);
            $message = "ইউজার আইডি #$targetUserId পাসওয়ার্ড পরিবর্তন করা হয়েছে।";
        } else {
            $error = "পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।";
        }
    }

    // 5. Delete User
    elseif ($action === 'delete_user') {
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        if ($targetUserId === (int)$user['id']) {
            $error = "আপনি নিজের এডমিন একাউন্ট মুছতে পারবেন না।";
        } elseif ($targetUserId > 0) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $targetUserId]);
            $message = "ব্যবহারকারী সফলভাবে মোছা হয়েছে।";
        }
    }

    // 6. Update Scrolling Notice
    elseif ($action === 'update_notice') {
        $noticeText = trim($_POST['notice_text'] ?? '');
        setSetting('scrolling_notice', $noticeText);
        $message = "স্ক্রলিং নোটিশ সফলভাবে হালনাগাদ করা হয়েছে।";
    }
}

// Fetch Stats
$totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalBalance = (float) $db->query("SELECT SUM(balance) FROM users")->fetchColumn();
$totalDakhila = (int) $db->query("SELECT COUNT(*) FROM dakhila")->fetchColumn();

// Fetch All Users
$usersStmt = $db->query("
    SELECT u.*, COUNT(d.id) AS dakhila_count 
    FROM users u 
    LEFT JOIN dakhila d ON d.user_id = u.id 
    GROUP BY u.id 
    ORDER BY u.id DESC
");
$allUsers = $usersStmt->fetchAll();

// Fetch Recent Balance Transactions
$transStmt = $db->query("
    SELECT t.*, u.name AS user_name, u.email AS user_email 
    FROM balance_transactions t 
    JOIN users u ON u.id = t.user_id 
    ORDER BY t.id DESC 
    LIMIT 20
");
$recentTransactions = $transStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>এডমিন প্যানেল — <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom-app.css" rel="stylesheet">
</head>
<body>

<div class="app-container">
    <!-- Navbar -->
    <header class="app-navbar">
        <a href="index" class="brand-heading">
            <div class="brand-icon">⚙️</div>
            <span>এডমিন প্যানেল (ইউজার ও ব্যালেন্স)</span>
        </a>
        <div class="nav-actions">
            <div class="user-badge">👑 <?= h($user['name']) ?> (এডমিন)</div>
            <a href="index" class="btn-app btn-app-secondary">🏠 মূল ড্যাশবোর্ড</a>
            <a href="logout" class="btn-app btn-app-danger">🚪 লগআউট</a>
        </div>
    </header>

    <?php 
    $scrollingNotice = getSetting('scrolling_notice', 'ব্যালেন্স রিচার্জ করতে টেলিগ্রামে যোগাযোগ করুন (@sebaguru)');
    if (!empty(trim($scrollingNotice))): 
    ?>
    <div class="notice-bar-container mb-4">
        <div class="notice-badge">📢 নোটিশ</div>
        <marquee class="notice-marquee" behavior="scroll" direction="left" scrollamount="5" onmouseover="this.stop();" onmouseout="this.start();">
            <?= h($scrollingNotice) ?>
        </marquee>
    </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success py-2.5 px-3 rounded-3 mb-4 fw-semibold"><?= h($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2.5 px-3 rounded-3 mb-4 fw-semibold"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- Stat Grid -->
    <div class="stat-grid">
        <div class="stat-box">
            <div class="stat-box-num"><?= number_format($totalUsers) ?></div>
            <div class="stat-box-title">👥 মোট নিবন্ধিত ব্যবহারকারী</div>
        </div>
        <div class="stat-box" style="border-top-color: #0088cc;">
            <div class="stat-box-num">৳<?= number_format($totalBalance, 2) ?></div>
            <div class="stat-box-title">💰 মোট ব্যবহারকারী ব্যালেন্স</div>
        </div>
        <div class="stat-box" style="border-top-color: #f59e0b;">
            <div class="stat-box-num"><?= number_format($totalDakhila) ?></div>
            <div class="stat-box-title">📄 মোট দাখিলা রেকর্ড</div>
        </div>
    </div>

    <!-- Create User Card -->
    <div class="form-card mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
            <h5 class="fw-bold m-0 text-dark">➕ নতুন ব্যবহারকারী তৈরি করুন</h5>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create_user">
            <div class="form-grid-4 mb-3">
                <div>
                    <label class="form-label-custom">ব্যবহারকারীর নাম *</label>
                    <input type="text" name="name" required class="form-control-custom" placeholder="উদা: মোঃ আব্দুর রহিম">
                </div>
                <div>
                    <label class="form-label-custom">ইমেইল ঠিকানা *</label>
                    <input type="email" name="email" required class="form-control-custom" placeholder="user@gmail.com">
                </div>
                <div>
                    <label class="form-label-custom">পাসওয়ার্ড *</label>
                    <input type="password" name="password" required class="form-control-custom" placeholder="••••••••">
                </div>
                <div>
                    <label class="form-label-custom">প্রাথমিক ব্যালেন্স (৳)</label>
                    <input type="number" step="0.01" name="initial_balance" value="0.00" class="form-control-custom">
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="isAdminCheck">
                    <label class="form-check-label fw-semibold small text-dark" for="isAdminCheck">এডমিন পারমিশন দিন</label>
                </div>
                <button type="submit" class="btn-app btn-app-primary">➕ ব্যবহারকারী তৈরি করুন</button>
            </div>
        </form>
    </div>

    <!-- Edit Scrolling Notice Card -->
    <div class="form-card mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
            <h5 class="fw-bold m-0 text-dark">📢 স্ক্রলিং নোটিশ বোর্ড পরিবর্তন</h5>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update_notice">
            <div class="mb-3">
                <label class="form-label-custom">নোটিশের বিষয়বস্তু (সকল পেজে হেডার এর নিচে স্ক্রলিং হবে)</label>
                <input type="text" name="notice_text" value="<?= h(getSetting('scrolling_notice', 'ব্যালেন্স রিচার্জ করতে টেলিগ্রামে যোগাযোগ করুন (@sebaguru)')) ?>" required class="form-control-custom" placeholder="যেমন: ব্যালেন্স রিচার্জ করতে টেলিগ্রামে যোগাযোগ করুন (@sebaguru)">
            </div>
            <div class="text-end">
                <button type="submit" class="btn-app btn-app-primary">💾 নোটিশ আপডেট করুন</button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="form-card mb-4">
        <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">👥 সমস্ত ব্যবহারকারী তালিকা</h5>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:50px;">ID</th>
                        <th>নাম</th>
                        <th>ইমেইল</th>
                        <th>বর্তমান ব্যালেন্স</th>
                        <th>দাখিলা</th>
                        <th>রোল</th>
                        <th class="text-center" style="width:320px;">অ্যাকশন (ব্যালেন্স কন্ট্রোল)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allUsers as $u): ?>
                    <tr>
                        <td><strong>#<?= $u['id'] ?></strong></td>
                        <td><strong class="text-dark"><?= h($u['name']) ?></strong></td>
                        <td><?= h($u['email']) ?></td>
                        <td>
                            <span class="balance-pill py-1 px-2.5" style="font-size:0.85rem;">
                                ৳<?= number_format((float)$u['balance'], 2) ?>
                            </span>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= number_format($u['dakhila_count']) ?> টি</span></td>
                        <td>
                            <?php if ($u['is_admin']): ?>
                                <span class="badge bg-warning text-dark">এডমিন</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">ইউজার</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <!-- Recharge Balance Button -->
                                <button type="button" class="btn-app btn-app-primary py-1 px-2" style="font-size:0.78rem;" onclick="openRechargeModal(<?= $u['id'] ?>, '<?= h($u['name']) ?>')">
                                    💰 যোগ করুন
                                </button>
                                <!-- Deduct Balance Button -->
                                <button type="button" class="btn-app btn-app-warning py-1 px-2" style="font-size:0.78rem;" onclick="openDeductModal(<?= $u['id'] ?>, '<?= h($u['name']) ?>', <?= (float)$u['balance'] ?>)">
                                    ➖ কর্তন
                                </button>
                                <!-- Delete User -->
                                <?php if ($u['id'] != $user['id']): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ব্যবহারকারীটি মুছবেন?');">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn-app btn-app-danger py-1 px-2" style="font-size:0.78rem;">🗑️ ডিলিট</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Balance Transactions History -->
    <div class="table-wrapper mb-4">
        <div class="p-3 border-bottom background-light">
            <h5 class="fw-bold m-0 text-dark">📜 সাম্প্রতিক ব্যালেন্স লেনদেন ইতিহাস</h5>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ব্যবহারকারী</th>
                        <th>টাইপ</th>
                        <th>টাকার পরিমাণ</th>
                        <th>বিবরণ</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTransactions)): ?>
                        <tr><td colspan="6" class="text-center py-3 text-muted">কোনো লেনদেন রেকর্ড পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $t): ?>
                        <tr>
                            <td>#<?= $t['id'] ?></td>
                            <td><strong><?= h($t['user_name']) ?></strong> <small class="text-muted">(<?= h($t['user_email']) ?>)</small></td>
                            <td>
                                <?php if ($t['type'] === 'credit'): ?>
                                    <span class="badge bg-success py-1 px-2">➕ Credit (যোগ)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger py-1 px-2">➖ Debit (কর্তন)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="<?= $t['type'] === 'credit' ? 'text-success' : 'text-danger' ?>">
                                    <?= $t['type'] === 'credit' ? '+' : '-' ?>৳<?= number_format((float)$t['amount'], 2) ?>
                                </strong>
                            </td>
                            <td><?= h($t['description']) ?></td>
                            <td class="small text-muted"><?= date('d M Y, h:i A', strtotime($t['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Balance -->
<div class="modal fade" id="addBalanceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form method="POST">
        <input type="hidden" name="action" value="add_balance">
        <input type="hidden" name="user_id" id="modalAddUserId">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold">💰 ব্যালেন্স রিচার্জ করুন</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <p class="mb-3 text-dark fw-semibold">গ্রাহক: <span id="modalAddUserName" class="text-primary"></span></p>
          <div class="mb-3">
             <label class="form-label-custom">যোগ করার পরিমাণ (টাকা) *</label>
             <input type="number" step="0.01" min="1" name="amount" required class="form-control-custom" placeholder="উদা: 500">
          </div>
          <div class="mb-3">
             <label class="form-label-custom">বিবরণ / নোট</label>
             <input type="text" name="description" value="এডমিন রিচার্জ" class="form-control-custom">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-app btn-app-secondary" data-bs-dismiss="modal">বাতিল</button>
          <button type="submit" class="btn-app btn-app-primary">➕ ব্যালেন্স যোগ করুন</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Deduct Balance -->
<div class="modal fade" id="deductBalanceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form method="POST">
        <input type="hidden" name="action" value="deduct_balance">
        <input type="hidden" name="user_id" id="modalDeductUserId">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title fw-bold">➖ ব্যালেন্স কর্তন করুন</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <p class="mb-1 text-dark fw-semibold">গ্রাহক: <span id="modalDeductUserName" class="text-primary"></span></p>
          <p class="mb-3 text-muted small">বর্তমান ব্যালেন্স: <span id="modalDeductCurrentBal" class="fw-bold text-dark"></span></p>
          <div class="mb-3">
             <label class="form-label-custom">কর্তনের পরিমাণ (টাকা) *</label>
             <input type="number" step="0.01" min="1" name="amount" required class="form-control-custom" placeholder="উদা: 50">
          </div>
          <div class="mb-3">
             <label class="form-label-custom">বিবরণ / কারণ</label>
             <input type="text" name="description" value="এডমিন কর্তন" class="form-control-custom">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-app btn-app-secondary" data-bs-dismiss="modal">বাতিল</button>
          <button type="submit" class="btn-app btn-app-warning">➖ ব্যালেন্স কর্তন করুন</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openRechargeModal(userId, userName) {
    document.getElementById('modalAddUserId').value = userId;
    document.getElementById('modalAddUserName').innerText = userName;
    var modal = new bootstrap.Modal(document.getElementById('addBalanceModal'));
    modal.show();
}

function openDeductModal(userId, userName, currentBal) {
    document.getElementById('modalDeductUserId').value = userId;
    document.getElementById('modalDeductUserName').innerText = userName;
    document.getElementById('modalDeductCurrentBal').innerText = '৳' + currentBal.toFixed(2);
    var modal = new bootstrap.Modal(document.getElementById('deductBalanceModal'));
    modal.show();
}
</script>

<!-- Floating Telegram Chat Bubble -->
<a href="https://t.me/sebaguru" target="_blank" class="telegram-bubble" title="টেলিগ্রাম সাপোর্ট (@sebaguru)">
    <svg viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.56 8.16l-2.02 9.51c-.15.68-.55.85-1.12.53l-3.08-2.27-1.49 1.43c-.16.16-.3.3-.61.3l.22-3.14 5.72-5.17c.25-.22-.05-.34-.38-.12l-7.07 4.45-3.04-.95c-.66-.21-.67-.66.14-.98l11.89-4.58c.55-.2 1.03.13.84.99z"/></svg>
</a>

</body>
</html>
