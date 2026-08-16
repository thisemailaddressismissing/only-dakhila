<?php
// ============================================
// Public Dakhila Verification / Print Page
// Exact copy-paste design of view-dakhila.php
// Available without authentication middleware
// ============================================
require_once __DIR__ . '/config.php';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if (empty($id)) {
    die('Invalid ID');
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM dakhila WHERE verify_id = :id OR id = :id_num");
$stmt->execute([':id' => $id, ':id_num' => is_numeric($id) ? (int)$id : 0]);
$r = $stmt->fetch();

if (!$r) {
    die('Record not found.');
}

// Fetch owners
$ownerStmt = $db->prepare("SELECT * FROM dakhila_owners WHERE dakhila_id = :id ORDER BY sort_order");
$ownerStmt->execute([':id' => $r['id']]);
$owners = $ownerStmt->fetchAll();

// Fetch dags
$dagStmt = $db->prepare("SELECT * FROM dakhila_dags WHERE dakhila_id = :id ORDER BY sort_order");
$dagStmt->execute([':id' => $r['id']]);
$dags = $dagStmt->fetchAll();

// Compute total land
$totalLand = 0;
foreach ($dags as $dag) {
    $totalLand += (float) $dag['amount'];
}

// Split dags into two halves (max 5 per column to match template)
$dagLeft  = array_slice($dags, 0, 5);
$dagRight = array_slice($dags, 5, 5);

// Bangla month names
$monthNames = [
    1 => 'বৈশাখ', 2 => 'জ্যৈষ্ঠ', 3 => 'আষাঢ়', 4 => 'শ্রাবণ',
    5 => 'ভাদ্র', 6 => 'আশ্বিন', 7 => 'কার্তিক', 8 => 'অগ্রহায়ণ',
    9 => 'পৌষ', 10 => 'মাঘ', 11 => 'ফাল্গুন', 12 => 'চৈত্র',
];
$monthName = $monthNames[(int)$r['payment_month']] ?? '';

// ============================================
// Helper: Convert English numerals to Bengali Unicode
// ============================================ 
function bn(?string $num): string {
    if ($num === null || $num === '') return '';
    $eng = ['0','1','2','3','4','5','6','7','8','9'];
    $bng = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return str_replace($eng, $bng, (string) $num);
}

// ============================================
// Helper: Remove .00 from decimal if it's a whole number
// ============================================
function bn_clean(?string $num): string {
    if ($num === null || $num === '') return '০';
    $num = (string) $num;
    if (strpos($num, '.') !== false) {
        $num = rtrim(rtrim($num, '0'), '.');
    }
    return bn($num);
}
// ============================================
// Helper: Bangla date line (e.g. ৫ পৌষ ১৪৩২)
function formatBanglaDate($r, $monthName): string {
    return bn((string)$r['payment_day']) . ' ' . $monthName . ' ' . bn((string)$r['payment_year']);
}
$banglaLine = formatBanglaDate($r, $monthName);

// ============================================
// Helper: Gregorian date line (e.g. ১৯ ডিসেম্বর, ২০২৫)
// Stored in column issue_date (YYYY-MM-DD) if available, fallback to created_at
$gregMonthsBn = [
    1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
    5 => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
    9 => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর',
];
$issueDateRaw = $r['issue_date'] ?? null;
// For old records without issue_date, derive from created_at
if (empty($issueDateRaw)) { $issueDateRaw = $r['created_at']; }
$ts = strtotime($issueDateRaw);
$gregDay   = bn(date('j', $ts));
$gregMonth = $gregMonthsBn[(int)date('n', $ts)] ?? '';
$gregYear  = bn(date('Y', $ts));
$englishLine = $gregDay . ' ' . $gregMonth . ', ' . $gregYear;

// Fiscal year (note line) — keep English digits as in template (2025-2026)
$fiscalYearEn = h($r['payment_year_en']);

// 3rd party QR code generation via qrserver API
$verifyId  = $r['verify_id'] ?? (string)$r['id'];
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost:7000';
$verifyUrl = $protocol . '://' . $host . '/dakhila-print/' . $verifyId;
$qrApiUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($verifyUrl);
?>
<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <meta charset="utf-8"/>    <title> ভূমি উন্নয়ন কর:
            Dakhila</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta content="width=600, initial-scale=1.0, minimum-scale=1.0, maximum-scale=3.0, user-scalable=no" name="viewport"/>

    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://dakhila.ldtax.gov.bd/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="https://dakhila.ldtax.gov.bd/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="https://dakhila.ldtax.gov.bd/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="https://dakhila.ldtax.gov.bd/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="https://dakhila.ldtax.gov.bd/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>

    <link href="https://dakhila.ldtax.gov.bd/assets/global/css/components-rounded.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="https://dakhila.ldtax.gov.bd/assets/global/css/plugins-md.css" rel="stylesheet" type="text/css"/>
    <link href="https://dakhila.ldtax.gov.bd/assets/admin/layout4/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="https://dakhila.ldtax.gov.bd/assets/admin/layout4/css/themes/light.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="https://dakhila.ldtax.gov.bd/assets/admin/layout4/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="https://dakhila.ldtax.gov.bd/css/common.css" rel="stylesheet" type="text/css"/>
    <link href="https://dakhila.ldtax.gov.bd/css/style.css" rel="stylesheet" type="text/css"/>
        <!-- END THEME STYLES -->
        <meta http-equiv="Content-Security-Policy" content="font-src 'self' data:;">
        <style>
            /* Override remote font-face with local files to avoid CORS issues */
            @font-face {
                font-family: 'kalpurushregular';
                src: url('kalpurush-kalpurush.woff') format('woff'),
                     url('kalpurush-kalpurush.ttf') format('truetype');
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
            @font-face {
                font-family: 'boishakhi';
                src: url('Boishkhi.ttf') format('truetype');
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
        </style>
                                        <link rel="shortcut icon" href="https://dakhila.ldtax.gov.bd/img/favicon.ico"/>
    <script src="https://dakhila.ldtax.gov.bd/js/jquery-2.1.1.min.js" type="ff70f717ae9b554500e5ea20-text/javascript"></script>
</head>

<body class="page-md page-sidebar-page-sidebar-closed-hide-logo page-header-fixed page-footer-fixed">

        <div class="clearfix"></div>
    <div class="page-container">
        <div class="page-content">
            <div class="page-content">
                                <!-- end -->

<div class="row">
    <div class="col-md-12">
        <div class="">
            <div class="no-print" style="
            display: flex;
            margin-bottom: 1.25rem;
            justify-content: center;
            align-items: center;
            ">
        <button type="button" onclick="printDiv('printArea')" style="
          padding: 0.35rem 1.25rem;
          border-radius: 0.35rem; 
          color: #ffffff; 
          background-color: #3B82F6; border: 1px solid #3B82F6; font-weight: 600; font-size: 0.95rem; cursor: pointer; box-shadow: 0 2px 6px rgba(59,130,246,0.3);">
          🖨️ প্রিন্ট
        </button>
      </div>



            <div class="portlet-body">
                <div id="printArea" class="content" style="width: 815px; margin: 0 auto;">
                    <div class="col-md-12">
                        <style type="text/css">
                            body{
                                font-family: "kalpurush",Arial,sans-serif;
                                font-size: 13px !important;
                                line-height: 1.2;
                                color: #333;
                                background-color: #fff;
                            }
                            .dotted_botton{
                                border:none;
                                border-bottom:1px dotted #000;
                                background-color:#fff;
                            }
                            .border_none{
                                border-top: none !important;
                            }
                            .table-bordered {
                                border: 1px solid #ddd;
                            }
                            .qrcode-print{
                               width:100%;
                               display: list-item;
                               list-style-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEoAAABKAQMAAAAmHlAyAAAABlBMVEX///8AAABVwtN+AAAACXBIWXMAAA7EAAAOxAGVKw4bAAAA60lEQVQokZXSvaqDMQgGYCGr4K0IWYXcuuAqeCtCVyHN1x5K/c7Ud3om408AfghtpR28qxHB40Gu2Fk2ZEv4jVHM+I+GyXwnBBT81f2Qtl959/AhwBhM+W7zQ6oNjHtyIw6Ex5asRsJytDBuBBDwxamNSKGLbUEjzTlssUrjqV8SI7kRz+Ruido4SGHwFu6URVls0ojDbeoZtVMZcqZoI5UmYAh3LlKVeRX7IpLxHvRa1BfP9st4aedUKNyFjde5tJC88RyrZohnI4JBhpN2lmvy+R53oiZJ3cmPUNNOcANj6jwP54TXUr/4Q54Smg3CNNNdeAAAAABJRU5ErkJggg==);
                               list-style-position: inside;
                               background-repeat: no-repeat;
                            }
                            .b1 {border: 1px dotted; padding: 2px;}
                            .text-left{text-align:left}
                            .text-right{text-align:right}
                            .text-center{text-align:center}
                        </style>
                        <style type="text/css" media="print">
                            .no-print { display: none !important; }
                            @page {
                                size: a4 portrait;
                                margin: 0mm;
                            }
                            html, body {
                                background-color: #FFFFFF !important;
                                margin: 0 !important;
                                padding: 0 !important;
                                width: 100% !important;
                            }
                            .page-container, .page-content, .row, .col-md-12, .portlet-body {
                                margin: 0 !important;
                                padding: 0 !important;
                                width: 100% !important;
                            }
                            #printArea {
                                position: relative !important;
                                left: 0 !important;
                                top: 0 !important;
                                width: 100% !important;
                                margin: 0 auto !important;
                                padding: 0 !important;
                                display: flex !important;
                                justify-content: center !important;
                            }
                            .dakhila-box {
                                float: none !important;
                                margin: 15px auto !important;
                                display: block !important;
                            }
                        </style>



                        <div class="dakhila-box" style="font-family:'kalpurush',Arial,sans-serif; font-size:14px !important; line-height:1.2;color: #333; background-color: #fff; width: 7.5in; height: 10.6in; border-radius: 10px; border: dotted 1px; padding: 10px; margin: 15px auto; position: relative; box-sizing: border-box;">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="from-controll">
                                        <table style="width: 100%;">
                                            <tr>
                                                <td class="text-left">বাংলাদেশ ফরম নং ১০৭৭</td>
                                                <td class="text-right">(পরিশিষ্ট: ৩৮)</td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">(সংশোধিত)</td>
                                                <td class="text-right input_bangla">ক্রমিক নং <?= bn(h($r['registry_no'])) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" colspan="2">
                                                    ভূমি উন্নয়ন কর পরিশোধ রসিদ                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" colspan="2">
                                                    (অনুচ্ছেদ ৩৯২ দ্রষ্টব্য)                                                </td>
                                            </tr>
                                        </table>
                                        <div style="width: 100%; height: 20px;"></div>
                                        <table style="width:100%;">
                                            <tr>
                                                <td style="width: 320px;">সিটি কর্পোরেশন/ পৌর/ ইউনিয়ন ভূমি অফিসের নাম:</td>
                                                <td class="dotted_botton"><?= h($r['office_name']) ?></td>
                                            </tr>
                                        </table>
                                        <table style="margin-top:5px; width:100%;">
                                            <tr>
                                                <td style="width: 170px;">মৌজার নাম ও জে. এল. নং:</td>
                                                <td class="dotted_botton input_bangla" style="padding: 0 10px 0 5px;"><?= h($r['mouja_jl']) ?></td>
                                                <td style="width: 105px">উপজেলা/থানা:</td>
                                                <td class="dotted_botton" style="padding: 0 10px 0 5px;"><?= h($r['upazila']) ?></td>
                                                <td style="width: 40px">জেলা:</td>
                                                <td class="dotted_botton" style="padding: 0 10px 0 5px;"><?= h($r['district']) ?></td>
                                            </tr>
                                        </table>
                                        
                                        <table style="margin-top:5px; width:100%;">
                                            <tr>
                                                <td style="width: 225px"><!-- ২ নং রেজিস্টার অনুযায়ী হোল্ডিং নম্বর -->
                                                ২ নং রেজিস্টার অনুযায়ী হোল্ডিং নম্বর:</td>
                                                <td class="dotted_botton numeric_bangla" style="padding-left: 10px;">
                                                    <?= bn(h($r['holding_no'])) ?>                                                </td>
                                            </tr>
                                        </table>

                                        <table style="margin-top:5px; width:100%;">
                                            <tr>
                                                <td style="width: 75px">খতিয়ান নং:</td>
                                                <td class="dotted_botton numeric_bangla" style="padding-left: 10px;">
                                                    <?= bn(h($r['khatian_no'])) ?>                                                </td>
                                            </tr>
                                        </table>
                                        <div style="height: 10px"></div>
                                    </div>

                                    <div class="from-controll">
                                        <p style="font-weight: bold; font-size: 12px; text-align: center; margin: 0px; padding: 0px;"><u>মালিকের বিবরণ</u></p>
                                    </div>
                                    <?php if (count($owners) <= 1): ?>
                                    <table style="border: 1px dotted; border-collapse: collapse; margin:10px 2px ; width: 100%; font-size: 11px; float: left;">
                                        <thead>
                                            <tr>
                                                <th style="width:10%;text-align: center;" class="b1">ক্রমঃ</th>
                                                <th style="width:60%;text-align: center;" class="b1">মালিকের নাম</th>
                                                <th style="width:25%;text-align: center;" class="b1">মালিকের অংশ</th>
                                            </tr>
                                        </thead>
                                        <tbody style="height: 21px;">
                                            <?php foreach ($owners as $i => $owner): ?>
                                                <tr>
                                                <td class="b1 input_bangla text-center"><?= bn($i + 1) ?></td>
                                                <td class="b1 input_bangla"><?= h($owner['name']) ?></td>
                                                <td class="b1 input_bangla text-center"><?= bn_clean(h($owner['share'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php else: 
                                        $ownerHalf = (int)ceil(count($owners) / 2);
                                        $ownersLeft = array_slice($owners, 0, $ownerHalf);
                                        $ownersRight = array_slice($owners, $ownerHalf);
                                    ?>
                                    <table style="border: 1px dotted; border-collapse: collapse; margin:10px 2px ; width: 49%; font-size: 11px; float: left;">
                                        <thead>
                                            <tr>
                                                <th style="width:12%;text-align: center;" class="b1">ক্রমঃ</th>
                                                <th style="width:58%;text-align: center;" class="b1">মালিকের নাম</th>
                                                <th style="width:30%;text-align: center;" class="b1">মালিকের অংশ</th>
                                            </tr>
                                        </thead>
                                        <tbody style="height: 21px;">
                                            <?php foreach ($ownersLeft as $i => $owner): ?>
                                            <tr>
                                                <td class="b1 input_bangla text-center"><?= bn($i + 1) ?></td>
                                                <td class="b1 input_bangla"><?= h($owner['name']) ?></td>
                                                <td class="b1 input_bangla text-center"><?= bn_clean(h($owner['share'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <table style="border: 1px dotted; border-collapse: collapse; margin:10px 2px ; width: 49%; font-size: 11px; float: right;">
                                        <thead>
                                            <tr>
                                                <th style="width:12%;text-align: center;" class="b1">ক্রমঃ</th>
                                                <th style="width:58%;text-align: center;" class="b1">মালিকের নাম</th>
                                                <th style="width:30%;text-align: center;" class="b1">মালিকের অংশ</th>
                                            </tr>
                                        </thead>
                                        <tbody style="height: 21px;">
                                            <?php foreach ($ownersRight as $i => $owner): ?>
                                            <tr>
                                                <td class="b1 input_bangla text-center"><?= bn(count($ownersLeft) + $i + 1) ?></td>
                                                <td class="b1 input_bangla"><?= h($owner['name']) ?></td>
                                                <td class="b1 input_bangla text-center"><?= bn_clean(h($owner['share'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php endif; ?>
                            </div></div><div class="col-md-12">
                                                <p style="font-weight: bold; font-size: 12px; text-align: center; margin: 0px; padding: 0px;"><u>জমির বিবরণ</u></p>
                                            </div><div class="row"><div class="col-md-12">                                    <?php if (count($dags) <= 1): ?>
                                    <table style="border: 1px dotted; border-collapse: collapse; margin:10px 2px ; width: 100%; font-size: 11px; float: left;">
                                        <thead>
                                            <tr>
                                                <th style="width:10%;text-align: center;" class="b1">ক্রমঃ</th>
                                                <th style="width:30%;text-align: center;" class="b1">দাগ নং</th>
                                                <th style="width:35%;text-align: center;" class="b1">জমির শ্রেণি</th>
                                                <th style="width:25%;text-align: center;" class="b1">জমির পরিমাণ (শতাংশ)</th>
                                            </tr>
                                        </thead>
                                        <tbody style="height: 21px;">
                                            <?php foreach ($dags as $i => $dag): ?>
                                            <tr>
                                                <td class="b1 input_bangla text-center"><?= bn($i + 1) ?></td>
                                                <td class="b1 input_bangla text-center"><?= bn(h($dag['dag_no'])) ?></td>
                                                <td class="b1 text-center"><?= h($dag['type']) ?></td>
                                                <td class="b1 input_bangla text-center"><?= bn_clean(h($dag['amount'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php else: 
                                        $dagHalf = (int)ceil(count($dags) / 2);
                                        $dagLeft = array_slice($dags, 0, $dagHalf);
                                        $dagRight = array_slice($dags, $dagHalf);
                                    ?>
                                    <table style="border: 1px dotted; border-collapse: collapse; margin:10px 2px ; width: 49%; font-size: 11px; float: left;">
                                        <thead>
                                            <tr>
                                                <th style="width:12%;text-align: center;" class="b1">ক্রমঃ</th>
                                                <th style="width:28%;text-align: center;" class="b1">দাগ নং</th>
                                                <th style="width:32%;text-align: center;" class="b1">জমির শ্রেণি</th>
                                                <th style="width:28%;text-align: center;" class="b1">জমির পরিমাণ (শতাংশ)</th>
                                            </tr>
                                        </thead>
                                        <tbody style="height: 21px;">
                                            <?php foreach ($dagLeft as $i => $dag): ?>
                                            <tr>
                                                <td class="b1 input_bangla text-center"><?= bn($i + 1) ?></td>
                                                <td class="b1 input_bangla text-center"><?= bn(h($dag['dag_no'])) ?></td>
                                                <td class="b1 text-center"><?= h($dag['type']) ?></td>
                                                <td class="b1 input_bangla text-center"><?= bn_clean(h($dag['amount'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <table style="border: 1px dotted; border-collapse: collapse; margin:10px 2px ; width: 49%; font-size: 11px; float: right;">
                                        <thead>
                                            <tr>
                                                <th style="width:12%;text-align: center;" class="b1">ক্রমঃ</th>
                                                <th style="width:28%;text-align: center;" class="b1">দাগ নং</th>
                                                <th style="width:32%;text-align: center;" class="b1">জমির শ্রেণি</th>
                                                <th style="width:28%;text-align: center;" class="b1">জমির পরিমাণ (শতাংশ)</th>
                                            </tr>
                                        </thead>
                                        <tbody style="height: 21px;">
                                            <?php foreach ($dagRight as $i => $dag): ?>
                                            <tr>
                                                <td class="b1 input_bangla text-center"><?= bn(count($dagLeft) + $i + 1) ?></td>
                                                <td class="b1 input_bangla text-center"><?= bn(h($dag['dag_no'])) ?></td>
                                                <td class="b1 text-center"><?= h($dag['type']) ?></td>
                                                <td class="b1 input_bangla text-center"><?= bn_clean(h($dag['amount'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php endif; ?>
                                   
                            
                                    <table style="border: 1px dotted; border-collapse: collapse; margin:10px 2px ; width: 100%; font-size: 12px;">
                                        <tr>
                                            <td class="b1 text-center" style="width: 50%;">সর্বমোট জমি (শতাংশ)</td>
                                            <td class="b1 input_bangla" style="width: 50%;"><?= bn_clean($totalLand) ?></td>
                                        </tr>
                                    </table>
                                    <div style="height: 10px;"></div>
                                    <table class="table table-striped table-bordered table-hover" style="margin:10px 2px ; width:100% !important;">
                                        <tr>
                                            <th style="text-align: center;" colspan="8">আদায়ের বিবরণ </th>
                                        </tr>

                                        <tr>
                                            <td style="text-align: center;" >তিন বৎসরের ঊর্ধ্বের বকেয়া</td>
                                            <td style="text-align: center;">গত তিন বৎসরের বকেয়া</td>
                                            <td style="text-align: center;">বকেয়ার জরিমানা ও ক্ষতিপূরণ</td>
                                            <td style="text-align: center;" >হাল দাবি</td>
                                            <td style="text-align: center;" >মোট দাবি</td>
                                            <td style="text-align: center;" >মোট আদায়</td>
                                            <td style="text-align: center;" >মোট বকেয়া</td>
                                            <td style="text-align: center;" >মন্তব্য</td>
                                        </tr>

                                        <tr>
                                            <td align="center"><?= bn_clean(h($r['three_years_plus_due'])) ?></td>
                                            <td align="center"><?= bn_clean(h($r['last_three_years_due'])) ?></td>
                                            <td align="center"><?= bn_clean(h($r['due_interest'])) ?></td>
                                            <td align="center"><?= bn_clean(h($r['current_demand'])) ?></td>
                                            <td align="center">
                                                <?= bn_clean(h($r['total_demand'])) ?></td>

                                            <td align="center"><?= bn_clean(h($r['total_collection'])) ?></td>
                                            <td align="center">
                                                <?= bn_clean(h($r['total_due'])) ?>                                            </td>
                                            <td align="center"><?= h($r['comments']) ?></td>
                                        </tr>
                                    </table>
                                    <div style="width:100% !important; margin:10px 2px ;">
                                        <p class="dotted_botton"> সর্বমোট (কথায়):
                                                                                        <?= h($r['total_in_words']) ?> ।
                                        </p>
                                    </div>
    <!--         <div style="width:100% !important; margin:5px;">
            <div style="width:70% !important; margin:0px auto; text-align: center;">
                <p style="background-color: #ffeb3b91; border: dotted 1px; border-radius: 25px; padding: 10px"> 
                    <b>এই হোল্ডিংটি ইউনিয়ন ভূমি অফিস কর্তৃক আর্কাইভ / স্থগিত করা আছে।</b>
                </p>
            </div>
        </div>
     -->
                                    <table style="width: 100%; margin-top: 5px; border-collapse: collapse;">
                                        <tr>
                                            <td style="width: 40%; vertical-align: top; text-align: left;">
                                                <p style="margin: 0 0 3px 0 !important; font-size: 12px;">
                                                    নোট: সর্বশেষ কর পরিশোধের সাল - <?= $fiscalYearEn ?> (অর্থবছর)
                                                </p>
                                                <p class="input_bangla" style="margin: 0 0 3px 0 !important; font-size: 12px;">
                                                    চালান নং : <?= bn(h($r['challan_no'])) ?>
                                                </p>
                                                <table style="border-collapse: collapse; font-size: 12px; margin-top: 2px;">
                                                    <tr>
                                                        <td style="vertical-align: middle; padding-right: 5px; white-space: nowrap;">তারিখ :</td>
                                                        <td style="text-align: center; vertical-align: middle;">
                                                            <div style="margin-bottom: 2px;"><?= $banglaLine ?></div>
                                                            <div style="border-top: 1px solid #000; padding-top: 1px;"><?= $englishLine ?></div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td style="width: 20%; vertical-align: top; text-align: center;">
                                                <img src="<?= $qrApiUrl ?>" alt="QR Code" style="width: 75px; height: 75px; display: inline-block; margin: 0 auto;" title="Verify: <?= h($verifyUrl) ?>" />
                                            </td>
                                            <td style="width: 40%; vertical-align: top; text-align: center; font-size: 12px; font-family: 'kalpurush',Arial,sans-serif; line-height: 1.4;">
                                                <div style="margin-top: 0; padding: 0;">
                                                    এই দাখিলা ইলেক্ট্রনিকভাবে তৈরি করা হয়েছে,<br>কোন স্বাক্ষর প্রয়োজন নেই।
                                                </div>
                                            </td>
                                        </tr>
                                    </table>   
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right" style="width: 100%; position: absolute; bottom: 0; right: 0;">
                                    <div style="width: 100%; border-top: 1px dotted gray; margin-top:15px;"></div>
                                    <div class="from-controll">1/1</div>
                                </div>
                            </div>                 
                        </div>
                    </div>
                </div>
                <!-- // -->
                <!--
                <div class="row">
                    <div class="col-md-12">
                        <div style="text-align: center !important; display: block !important;">
                            <input style="margin-top: 10px; padding: 10px;" id="print" class="btn btn-md blue" type="button" onclick="printDiv('printArea')" value="প্রিন্ট" />
                             <a class="btn btn-md btn-success" href="https://dakhila.ldtax.gov.bd/ldtax-holdings/individual-rashid-print-offline-preview/NUIxcVBBbTF2VXJzRDdlUERvSGtCQT09" target="_blank" style="margin-top: 10px;padding:10px;">PDF</a> 
                        </div>
                    </div>
                </div>
                -->
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function printDiv(divName) {
        window.print();
    }
</script>

            </div>
        </div>
    </div>
</html>
