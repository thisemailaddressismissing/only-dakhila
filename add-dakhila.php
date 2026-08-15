<?php
// ============================================
// Add Dakhila — Protected Page
// ============================================
require_once __DIR__ . '/auth-check.php';
$user = requireAuth();  // Redirects to login if not authenticated

// ============================================
// Dummy data for testing (only pre-fills on GET)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_POST = [
        'registry_no'          => '657625076803',
        'challan_no'           => '2526-0028886180',
        'office_name'          => '৫নং শাহাবাদ ইউনিয়ন',
        'upazila'              => 'নড়াইল সদর',
        'district'             => 'নড়াইল',
        'holding_no'           => '8',
        'mouja_jl'             => 'সদানন্দ কাটি-24',
        'khatian_no'           => '10',
        'payment_year_en'      => '2025-2026',
        'issue_date'           => '2025-12-19',
        'day'                  => '19',
        'month'                => '9',
        'year'                 => '1432',
        'three_years_plus_due' => '0',
        'last_three_years_due' => '0',
        'due_interest'         => '0',
        'current_demand'       => '10',
        'total_demand'         => '10',
        'total_collection'     => '10',
        'total_due'            => '0',
        'comments'             => '',
        'total_in_words'       => 'দশ টাকা মাত্র',
        'owners' => [
            0 => ['name' => 'আব্দুল মতিন', 'share' => '1'],
        ],
        'dags' => [
            0 => ['dag' => '59',  'type' => 'ডাঙ্গা(কৃষি)', 'amount' => '19'],
            1 => ['dag' => '60',  'type' => 'ডাঙ্গা(কৃষি)', 'amount' => '8'],
            2 => ['dag' => '63',  'type' => 'ডাঙ্গা(কৃষি)', 'amount' => '17'],
            3 => ['dag' => '105', 'type' => 'ডাঙ্গা(কৃষি)', 'amount' => '9'],
            4 => ['dag' => '186', 'type' => 'ধানী(কৃষি)',   'amount' => '81'],
            5 => ['dag' => '192', 'type' => 'ধানী(কৃষি)',   'amount' => '21'],
            6 => ['dag' => '231', 'type' => 'ধানী(কৃষি)',   'amount' => '75'],
            7 => ['dag' => '260', 'type' => 'ধানী(কৃষি)',   'amount' => '32'],
            8 => ['dag' => '264', 'type' => 'ধানী(কৃষি)',   'amount' => '96'],
            9 => ['dag' => '294', 'type' => 'ধানী(কৃষি)',   'amount' => '33'],
        ],
    ];
}

// ============================================
// Process Form Submission
// ============================================
$success = '';
$error   = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Check balance first ---
    $currentBalance = refreshBalance();
    if ($currentBalance < COST_PER_SUBMIT) {
        $errors[] = 'আপনার ব্যালেন্স অপর্যাপ্ত। প্রয়োজন: ' . COST_PER_SUBMIT . ' টাকা, বর্তমান: ' . number_format($currentBalance, 2) . ' টাকা।';
    }

    // --- Validate required fields ---
    if (empty($errors)) {
        $required = [
            'registry_no', 'office_name', 'upazila', 'district',
            'holding_no', 'mouja_jl', 'khatian_no',
            'payment_year_en', 'issue_date',
            'day', 'month', 'year',
            'total_in_words',
        ];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "{$field} ফিল্ডটি আবশ্যক।";
            }
        }
    }

    // Validate numeric fields
    if (empty($errors)) {
        $numeric_fields = [
            'three_years_plus_due', 'last_three_years_due', 'due_interest',
            'current_demand', 'total_demand', 'total_collection', 'total_due',
        ];
        foreach ($numeric_fields as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                $errors[] = "{$field} ফিল্ডটি আবশ্যক।";
            }
        }
    }

    // Validate owners
    if (empty($errors) && (empty($_POST['owners']) || empty($_POST['owners'][0]['name']))) {
        $errors[] = "অন্তত একজন মালিকের নাম আবশ্যক।";
    }

    // Validate dags
    if (empty($errors) && (empty($_POST['dags']) || empty($_POST['dags'][0]['dag']))) {
        $errors[] = "অন্তত একটি দাগ নম্বর আবশ্যক।";
    }

    if (empty($errors)) {
        try {
            $db = getDB();
            $db->beginTransaction();

            $verify_id = (string) mt_rand(10000000, 99999999);

            // Insert main record
            $sql = "INSERT INTO dakhila (
                user_id, verify_id,
                registry_no, challan_no, office_name, upazila, district,
                holding_no, mouja_jl, khatian_no,
                payment_year_bn, payment_year_en, issue_date,
                payment_day, payment_month, payment_year,
                three_years_plus_due, last_three_years_due, due_interest,
                current_demand, total_demand, total_collection, total_due,
                comments, total_in_words
            ) VALUES (
                :user_id, :verify_id,
                :registry_no, :challan_no, :office_name, :upazila, :district,
                :holding_no, :mouja_jl, :khatian_no,
                :payment_year_bn, :payment_year_en, :issue_date,
                :payment_day, :payment_month, :payment_year,
                :three_years_plus_due, :last_three_years_due, :due_interest,
                :current_demand, :total_demand, :total_collection, :total_due,
                :comments, :total_in_words
            )";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':user_id'               => $user['id'],
                ':verify_id'             => $verify_id,
                ':registry_no'           => $_POST['registry_no'],
                ':challan_no'            => $_POST['challan_no'] ?? null,
                ':office_name'           => $_POST['office_name'],
                ':upazila'               => $_POST['upazila'],
                ':district'              => $_POST['district'],
                ':holding_no'            => $_POST['holding_no'],
                ':mouja_jl'              => $_POST['mouja_jl'],
                ':khatian_no'            => $_POST['khatian_no'],
                ':payment_year_bn'       => $_POST['year'],
                ':payment_year_en'       => $_POST['payment_year_en'],
                ':issue_date'            => $_POST['issue_date'],
                ':payment_day'           => (int) $_POST['day'],
                ':payment_month'         => (int) $_POST['month'],
                ':payment_year'          => (int) $_POST['year'],
                ':three_years_plus_due'  => (float) $_POST['three_years_plus_due'],
                ':last_three_years_due'  => (float) $_POST['last_three_years_due'],
                ':due_interest'          => (float) $_POST['due_interest'],
                ':current_demand'        => (float) $_POST['current_demand'],
                ':total_demand'          => (float) $_POST['total_demand'],
                ':total_collection'      => (float) $_POST['total_collection'],
                ':total_due'             => (float) $_POST['total_due'],
                ':comments'              => $_POST['comments'] ?? null,
                ':total_in_words'        => $_POST['total_in_words'],
            ]);


            $dakhila_id = getLastInsertId($db, 'dakhila_id_seq');

            // Insert owners
            if (!empty($_POST['owners'])) {
                $ownerSql = "INSERT INTO dakhila_owners (dakhila_id, name, share, sort_order) VALUES (:dakhila_id, :name, :share, :sort_order)";
                $ownerStmt = $db->prepare($ownerSql);
                foreach ($_POST['owners'] as $i => $owner) {
                    if (!empty(trim($owner['name'] ?? ''))) {
                        $ownerStmt->execute([
                            ':dakhila_id' => $dakhila_id,
                            ':name'       => $owner['name'],
                            ':share'      => (float) ($owner['share'] ?? 0),
                            ':sort_order' => $i + 1,
                        ]);
                    }
                }
            }

            // Insert dags
            if (!empty($_POST['dags'])) {
                $dagSql = "INSERT INTO dakhila_dags (dakhila_id, dag_no, type, amount, sort_order) VALUES (:dakhila_id, :dag_no, :type, :amount, :sort_order)";
                $dagStmt = $db->prepare($dagSql);
                foreach ($_POST['dags'] as $i => $dag) {
                    if (!empty(trim($dag['dag'] ?? ''))) {
                        $dagStmt->execute([
                            ':dakhila_id' => $dakhila_id,
                            ':dag_no'     => $dag['dag'],
                            ':type'       => $dag['type'] ?? '',
                            ':amount'     => (float) ($dag['amount'] ?? 0),
                            ':sort_order' => $i + 1,
                        ]);
                    }
                }
            }

            $db->commit();

            // Deduct balance
            $desc = "দাখিলা সাবমিট: রেজিঃ " . $_POST['registry_no'];
            deductBalance($user['id'], COST_PER_SUBMIT, $desc);

            $_SESSION['flash_success'] = "✅ দাখিলা সফলভাবে সংরক্ষিত হয়েছে! রেজিস্ট্রি নং: " . h($_POST['registry_no']);
            header('Location: index');
            exit;
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $error = "❌ ডাটাবেজ ত্রুটি: " . $e->getMessage();
        }
    }
}

// Helper functions (old, isSelected) are in auth-check.php
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নতুন দাখিলা — <?= SITE_NAME ?></title>
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
            <span class="balance-pill">💳 ব্যালেন্স: ৳<?= number_format(refreshBalance(), 2) ?></span>
            <span class="user-badge">👤 <?= h($user['name']) ?></span>
            <a href="index" class="btn-app btn-app-secondary">← ড্যাশবোর্ড</a>
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

    <div class="form-card">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h4 class="fw-bold text-dark mb-1">📋 নতুন দাখিলা সংযোজন</h4>
                <p class="text-muted small mb-0">ফরমটির সকল ঘর সঠিকভাবে পূরণ করুন। প্রতি সাবমিটে ৳<?= COST_PER_SUBMIT ?> কর্তন হবে।</p>
            </div>
            <a href="index.php" class="btn-app btn-app-secondary">← ফিরে যান</a>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2 px-3 rounded-3 mb-4">
            <strong>⚠️ অনুগ্রহ করে নিম্নলিখিত ত্রুটিগুলি সংশোধন করুন:</strong>
            <ul class="mb-0 mt-1 ps-3 small">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success py-2 px-3 rounded-3 mb-4"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 rounded-3 mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="dakhilaForm">

            <div style="display:flex; flex-direction:column; gap:1.25rem;">

                <!-- Row 1 -->
                <div class="form-grid-5">
                    <div>
                        <label class="form-label-custom">রেজিস্ট্রি নম্বর *</label>
                        <input type="text" name="registry_no" value="<?= old('registry_no') ?>" required class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">চালান নম্বর (ঐচ্ছিক)</label>
                        <input type="text" name="challan_no" value="<?= old('challan_no') ?>" class="form-control-custom">
                    </div>
                    <div class="col-span-2">
                        <label class="form-label-custom">সিটি কর্পোরেশন / পৌরসভা / ইউনিয়ন ভূমি অফিসের নাম *</label>
                        <input type="text" name="office_name" value="<?= old('office_name') ?>" required class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">উপজেলা / থানা *</label>
                        <input type="text" name="upazila" value="<?= old('upazila') ?>" required class="form-control-custom">
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="form-grid-5">
                    <div>
                        <label class="form-label-custom">জেলা *</label>
                        <input type="text" name="district" value="<?= old('district') ?>" required class="form-control-custom">
                    </div>
                    <div class="col-span-2">
                        <label class="form-label-custom">২ নং রেজিস্টার অনুযায়ী হোল্ডিং নম্বর *</label>
                        <input type="text" name="holding_no" value="<?= old('holding_no') ?>" required class="form-control-custom">
                    </div>
                    <div class="col-span-2">
                        <label class="form-label-custom">মৌজা ও জে. এল. নম্বর *</label>
                        <input type="text" name="mouja_jl" value="<?= old('mouja_jl') ?>" required class="form-control-custom">
                    </div>
                </div>

                <!-- Row 3 -->
                <div class="form-grid-5">
                    <div>
                        <label class="form-label-custom">খতিয়ান নম্বর *</label>
                        <input type="text" name="khatian_no" value="<?= old('khatian_no') ?>" required class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">অর্থবছর *</label>
                        <input type="text" name="payment_year_en" id="payment_year_en" value="<?= old('payment_year_en') ?>" required class="form-control-custom" placeholder="2025-2026">
                    </div>
                    <div>
                        <label class="form-label-custom">দাখিলা ইস্যুর তারিখ *</label>
                        <input type="date" name="issue_date" value="<?= old('issue_date') ?>" required class="form-control-custom">
                        <small style="color:#64748b; font-size:0.75rem;">ইংরেজি তারিখ (যেমন 2025-12-19)</small>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label-custom">বাংলা দিন মাস বছর *</label>
                        <div class="form-grid-3" style="gap:0.5rem;">
                            <div>
                                <select name="day" required class="form-select">
                                    <option value="">--দিন--</option>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= $i ?>" <?= isSelected('day', $i) ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <select name="month" required class="form-select">
                                    <option value="">--মাস--</option>
                                    <?php
                                    $months = [
                                        1 => 'বৈশাখ', 2 => 'জ্যৈষ্ঠ', 3 => 'আষাঢ়', 4 => 'শ্রাবণ',
                                        5 => 'ভাদ্র', 6 => 'আশ্বিন', 7 => 'কার্তিক', 8 => 'অগ্রহায়ণ',
                                        9 => 'পৌষ', 10 => 'মাঘ', 11 => 'ফাল্গুন', 12 => 'চৈত্র',
                                    ];
                                    foreach ($months as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= isSelected('month', $k) ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <select name="year" id="year" required class="form-select" onchange="onYearChange()">
                                    <option value="">--বছর--</option>
                                    <?php for ($i = 1400; $i <= 1500; $i++): ?>
                                        <option value="<?= $i ?>" <?= isSelected('year', $i) ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4 - Collection Details -->
                <div class="form-grid-4">
                    <div>
                        <label class="form-label-custom">তিন বৎসরের ঊর্ধ্বের বকেয়া *</label>
                        <input type="number" name="three_years_plus_due" value="<?= old('three_years_plus_due', '0') ?>" step="any" required class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">গত তিন বৎসরের বকেয়া *</label>
                        <input type="number" name="last_three_years_due" value="<?= old('last_three_years_due', '0') ?>" step="any" required class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">বকেয়ার জরিমানা ও ক্ষতিপূরণ *</label>
                        <input type="number" name="due_interest" value="<?= old('due_interest', '0') ?>" step="any" required class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">হাল দাবি *</label>
                        <input type="number" name="current_demand" value="<?= old('current_demand', '0') ?>" step="any" required class="form-control-custom">
                    </div>
                </div>

                <!-- Row 5 - Totals -->
                <div class="form-grid-4">
                    <div>
                        <label class="form-label-custom">মোট দাবি *</label>
                        <input type="number" name="total_demand" value="<?= old('total_demand', '0') ?>" step="any" required class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">মোট আদায় *</label>
                        <input type="number" name="total_collection" value="<?= old('total_collection', '0') ?>" step="any" required class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">মোট বকেয়া *</label>
                        <input type="number" name="total_due" value="<?= old('total_due', '0') ?>" step="any" required class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">মন্তব্য</label>
                        <textarea name="comments" rows="1" class="form-control-custom"><?= old('comments') ?></textarea>
                    </div>
                </div>

                <!-- Owners Section -->
                <div style="margin-top:0.25rem;">
                    <div class="section-header">
                        <span>👤 মালিকের নাম ও সম্পত্তির পরিমাণ</span>
                        <button type="button" class="btn-add" onclick="addOwnerRow()">+</button>
                    </div>
                    <div class="section-body" id="ownerSection">
                        <!-- First owner row (permanent) -->
                        <div class="row-item" style="display:flex; gap:0.75rem; align-items:center;">
                            <input type="text" name="owners[0][name]" value="<?= old('owners.0.name') ?>" placeholder="মালিকের নাম" required class="form-control" style="flex:1;">
                            <input type="number" step="any" name="owners[0][share]" value="<?= old('owners.0.share') ?>" placeholder="সম্পত্তির পরিমাণ" required class="form-control" style="flex:1;">
                        </div>
                        <?php if (!empty($_POST['owners'])): ?>
                            <?php foreach ($_POST['owners'] as $idx => $owner): ?>
                                <?php if ($idx > 0): ?>
                                <div class="row-item" style="display:flex; gap:0.75rem; align-items:center;">
                                    <input type="text" name="owners[<?= $idx ?>][name]" value="<?= h($owner['name'] ?? '') ?>" placeholder="মালিকের নাম" class="form-control" style="flex:1;">
                                    <input type="number" step="any" name="owners[<?= $idx ?>][share]" value="<?= h($owner['share'] ?? '') ?>" placeholder="সম্পত্তির পরিমাণ" class="form-control" style="flex:1;">
                                    <button type="button" class="btn-remove" onclick="this.closest('.row-item').remove()">×</button>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dags Section -->
                <div style="margin-top:0.25rem;">
                    <div class="section-header">
                        <span>📋 দাগের তথ্য</span>
                        <button type="button" class="btn-add" onclick="addDagRow()">+</button>
                    </div>
                    <div class="section-body" id="dagSection">
                        <!-- First dag row (permanent) -->
                        <div class="row-item" style="display:flex; gap:0.75rem; align-items:center;">
                            <input type="text" name="dags[0][dag]" value="<?= old('dags.0.dag') ?>" placeholder="দাগ নম্বর" required class="form-control" style="flex:1;">
                            <input type="text" name="dags[0][type]" value="<?= old('dags.0.type') ?>" placeholder="খতিয়ান শ্রেণি" required class="form-control" style="flex:1;">
                            <input type="text" name="dags[0][amount]" value="<?= old('dags.0.amount') ?>" placeholder="খতিয়ান পরিমাণ" required class="form-control" style="flex:1;">
                        </div>
                        <?php if (!empty($_POST['dags'])): ?>
                            <?php foreach ($_POST['dags'] as $idx => $dag): ?>
                                <?php if ($idx > 0): ?>
                                <div class="row-item" style="display:flex; gap:0.75rem; align-items:center;">
                                    <input type="text" name="dags[<?= $idx ?>][dag]" value="<?= h($dag['dag'] ?? '') ?>" placeholder="দাগ নম্বর" class="form-control" style="flex:1;">
                                    <input type="text" name="dags[<?= $idx ?>][type]" value="<?= h($dag['type'] ?? '') ?>" placeholder="খতিয়ান শ্রেণি" class="form-control" style="flex:1;">
                                    <input type="text" name="dags[<?= $idx ?>][amount]" value="<?= h($dag['amount'] ?? '') ?>" placeholder="খতিয়ান পরিমাণ" class="form-control" style="flex:1;">
                                    <button type="button" class="btn-remove" onclick="this.closest('.row-item').remove()">×</button>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Total in Words -->
                <div>
                    <label class="form-label d-block">সর্বমোট (কথায়) *</label>
                    <input type="text" name="total_in_words" value="<?= old('total_in_words') ?>" required class="form-control" placeholder="উদা: দশ টাকা মাত্র">
                </div>

                <!-- Buttons -->
                <div style="display:flex; align-items:center; gap:1rem; margin-top:0.5rem; padding-top:1.25rem; border-top:1.5px solid rgba(102,126,234,0.12);">
                    <a href="add-dakhila" class="btn-back">⬅️ ফর্ম রিসেট</a>
                    <button type="submit" class="btn-submit" id="submitBtn" style="margin-left:auto;">
                        <span id="submitText">💾 সংরক্ষণ করুন</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
let ownerIndex = <?= !empty($_POST['owners']) ? count($_POST['owners']) : 1 ?>;
let dagIndex   = <?= !empty($_POST['dags']) ? count($_POST['dags']) : 1 ?>;

function addOwnerRow() {
    const container = document.getElementById('ownerSection');
    const div = document.createElement('div');
    div.className = 'row-item';
    div.style.cssText = 'display:flex; gap:0.75rem; align-items:center;';
    div.innerHTML = `
        <input type="text" name="owners[${ownerIndex}][name]" placeholder="মালিকের নাম" class="form-control" style="flex:1;">
        <input type="number" step="any" name="owners[${ownerIndex}][share]" placeholder="সম্পত্তির পরিমাণ" class="form-control" style="flex:1;">
        <button type="button" class="btn-remove" onclick="this.closest('.row-item').remove()">×</button>
    `;
    container.appendChild(div);
    ownerIndex++;
}

function addDagRow() {
    const container = document.getElementById('dagSection');
    const div = document.createElement('div');
    div.className = 'row-item';
    div.style.cssText = 'display:flex; gap:0.75rem; align-items:center;';
    div.innerHTML = `
        <input type="text" name="dags[${dagIndex}][dag]" placeholder="দাগ নম্বর" class="form-control" style="flex:1;">
        <input type="text" name="dags[${dagIndex}][type]" placeholder="খতিয়ান শ্রেণি" class="form-control" style="flex:1;">
        <input type="text" name="dags[${dagIndex}][amount]" placeholder="খতিয়ান পরিমাণ" class="form-control" style="flex:1;">
        <button type="button" class="btn-remove" onclick="this.closest('.row-item').remove()">×</button>
    `;
    container.appendChild(div);
    dagIndex++;
}

function onYearChange() {
    // No-op: fiscal year field is now independent (payment_year_en)
}

// On page load, sync year fields if year is already selected
document.addEventListener('DOMContentLoaded', function() {
    onYearChange();
    // Form submit loading state
    const form = document.getElementById('dakhilaForm');
    form.addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        const txt = document.getElementById('submitText');
        btn.disabled = true;
});
</script>

<!-- Floating Telegram Chat Bubble -->
<a href="https://t.me/sebaguru" target="_blank" class="telegram-bubble" title="টেলিগ্রাম সাপোর্ট (@sebaguru)">
    <svg viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.56 8.16l-2.02 9.51c-.15.68-.55.85-1.12.53l-3.08-2.27-1.49 1.43c-.16.16-.3.3-.61.3l.22-3.14 5.72-5.17c.25-.22-.05-.34-.38-.12l-7.07 4.45-3.04-.95c-.66-.21-.67-.66.14-.98l11.89-4.58c.55-.2 1.03.13.84.99z"/></svg>
</a>

</body>
</html>
