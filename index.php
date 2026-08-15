<?php
// ============================================
// Dashboard — View All Submitted Dakhilas (Executive Design)
// ============================================
require_once __DIR__ . '/auth-check.php';
$user = requireAuth();

$db = getDB();

// Pagination
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

// Total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM dakhila WHERE user_id = :uid");
$countStmt->execute([':uid' => $user['id']]);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

// Fetch records with owners and dags
$stmt = $db->prepare("
    SELECT d.*
    FROM dakhila d
    WHERE d.user_id = :uid
    ORDER BY d.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':uid', $user['id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$records = $stmt->fetchAll();

// Month names
$monthNames = [
    1 => 'বৈশাখ', 2 => 'জ্যৈষ্ঠ', 3 => 'আষাঢ়', 4 => 'শ্রাবণ',
    5 => 'ভাদ্র', 6 => 'আশ্বিন', 7 => 'কার্তিক', 8 => 'অগ্রহায়ণ',
    9 => 'পৌষ', 10 => 'মাঘ', 11 => 'ফাল্গুন', 12 => 'চৈত্র',
];

$balance = refreshBalance();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্যাশবোর্ড — <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom-app.css" rel="stylesheet">
</head>
<body>

<div class="app-container">

    <!-- Header / Navbar -->
    <header class="app-navbar">
        <a href="index" class="brand-heading">
            <div class="brand-icon">🏛️</div>
            <span><?= SITE_NAME ?></span>
        </a>
        <div class="nav-actions">
            <span class="balance-pill">💳 ব্যালেন্স: ৳<?= number_format($balance, 2) ?></span>
            <span class="user-badge">👤 <?= h($user['name']) ?></span>
            <?php if (!empty($user['is_admin'])): ?>
                <a href="admin" class="btn-app btn-app-warning">⚙️ এডমিন প্যানেল</a>
            <?php endif; ?>
            <a href="add-dakhila" class="btn-app btn-app-primary">➕ নতুন দাখিলা</a>
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

    <!-- Stats Row -->
    <div class="stat-grid">
        <div class="stat-box">
            <div class="stat-box-num"><?= number_format($totalRows) ?></div>
            <div class="stat-box-title">মোট প্রস্তুতকৃত দাখিলা</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-num">৳<?= number_format($balance, 2) ?></div>
            <div class="stat-box-title">বর্তমান ওয়ালেট ব্যালেন্স</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-num">৳<?= number_format(COST_PER_SUBMIT, 2) ?></div>
            <div class="stat-box-title">প্রতি দাখিলা মাশুল</div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success py-2.5 px-3 rounded-3 mb-4 d-flex align-items-center gap-2" style="font-size:0.9rem; border-color:#a7f3d0; background-color:#ecfdf5; color:#065f46;">
            <span><?= h($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger py-2.5 px-3 rounded-3 mb-4 d-flex align-items-center gap-2" style="font-size:0.9rem; border-color:#fecaca; background-color:#fef2f2; color:#991b1b;">
            <span><?= h($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Table Section -->
    <div class="table-wrapper">
        <?php if (empty($records)): ?>
            <div class="text-center py-5 px-3">
                <div style="font-size:3rem; margin-bottom:0.75rem;">📭</div>
                <h5 class="fw-bold text-dark mb-1">এখনো কোনো দাখিলা জমা হয়নি</h5>
                <p class="text-muted small mb-3">নতুন দাখিলা তৈরি করতে নিচের বাটনে ক্লিক করুন।</p>
                <a href="add-dakhila" class="btn-app btn-app-primary">➕ প্রথম দাখিলা তৈরি করুন</a>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:220px;">অ্যাকশন</th>
                        <th>রেজিঃ নং</th>
                        <th>হোল্ডিং নং</th>
                        <th>খতিয়ান নং</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                    <tr>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="view-dakhila?id=<?= $record['id'] ?>" target="_blank" class="btn-app btn-app-primary py-1 px-2" style="font-size:0.78rem;">📄 প্রিন্ট</a>
                                <a href="edit-dakhila?id=<?= $record['id'] ?>" class="btn-app btn-app-warning py-1 px-2" style="font-size:0.78rem;">✏️ এডিট</a>
                                <a href="delete-dakhila?id=<?= $record['id'] ?>" onclick="return confirm('আপনি কি নিশ্চিত যে রেজিঃ #<?= h($record['registry_no']) ?> দাখিলাটি মুছতে চান?');" class="btn-app btn-app-danger py-1 px-2" style="font-size:0.78rem;">🗑️ ডিলিট</a>
                            </div>
                        </td>
                        <td><strong class="text-dark">#<?= h($record['registry_no']) ?></strong></td>
                        <td><?= h($record['holding_no']) ?></td>
                        <td><?= h($record['khatian_no']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-wrap">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="page-link-app">← পূর্ববর্তী</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="page-link-app <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="page-link-app">পরবর্তী →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
<!-- Floating Telegram Chat Bubble -->
<a href="https://t.me/sebaguru" target="_blank" class="telegram-bubble" title="টেলিগ্রাম সাপোর্ট (@sebaguru)">
    <svg viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.56 8.16l-2.02 9.51c-.15.68-.55.85-1.12.53l-3.08-2.27-1.49 1.43c-.16.16-.3.3-.61.3l.22-3.14 5.72-5.17c.25-.22-.05-.34-.38-.12l-7.07 4.45-3.04-.95c-.66-.21-.67-.66.14-.98l11.89-4.58c.55-.2 1.03.13.84.99z"/></svg>
</a>

</body>
</html>
