<?php
/**
 * Live Database Reset Tool
 * Next Generation Public School
 * Clears all dummy test transactions (Students, Fees, Attendance, Marks, Notices, Homework)
 * Preserves Users (Admin / Accountant), Classes (PG to 12th), Teachers, Subjects, and School Settings.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$status = '';

// Check if request is confirmed
$is_confirmed = isset($_POST['confirm_reset']) || (isset($_GET['confirm']) && $_GET['confirm'] === 'yes');

if ($is_confirmed) {
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

        $tables_to_truncate = [
            'fees',
            'attendance',
            'exam_marks',
            'examinations',
            'homework',
            'notices',
            'students'
        ];

        foreach ($tables_to_truncate as $tbl) {
            $pdo->exec("TRUNCATE TABLE `$tbl`");
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // Clean dummy uploaded student photos if any
        $upload_dir = __DIR__ . '/assets/uploads';
        if (is_dir($upload_dir)) {
            $files = glob($upload_dir . '/stu_*.*');
            if ($files) {
                foreach ($files as $f) {
                    if (is_file($f)) @unlink($f);
                }
            }
        }

        $status = 'success';
        $message = "Live database has been successfully reset to 0! All dummy student and fee records are cleared. Users, Classes (PG to 12th), and Settings are completely safe.";

    } catch (Exception $e) {
        $status = 'error';
        $message = "Database reset error: " . $e->getMessage();
    }
}

// Fetch current counts
$tables = ['students', 'fees', 'attendance', 'exam_marks', 'notices', 'homework', 'classes', 'users'];
$counts = [];
foreach ($tables as $t) {
    try {
        $counts[$t] = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    } catch (Exception $e) {
        $counts[$t] = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Live Database - Next Generation Public School</title>
    <link rel="icon" type="image/png" href="<?= get_school_logo() ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-xl w-full bg-slate-800 rounded-3xl border border-slate-700 shadow-2xl p-6 sm:p-10 space-y-6">
        
        <div class="text-center space-y-2">
            <img src="<?= get_school_logo() ?>" alt="Next Generation Public School" class="w-20 h-20 mx-auto object-contain drop-shadow-lg">
            <h1 class="text-2xl font-black tracking-tight text-white uppercase">Next Generation Public School</h1>
            <p class="text-xs font-bold text-amber-400 tracking-wider">विद्या ददाति विनयं &bull; Live Database Reset Tool</p>
        </div>

        <?php if ($status === 'success'): ?>
            <div class="p-5 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-300 text-sm space-y-2 text-center">
                <i class="fa-solid fa-circle-check text-3xl text-emerald-400"></i>
                <h3 class="font-bold text-base text-white">Database Reset Complete!</h3>
                <p class="text-xs"><?= e($message) ?></p>
            </div>
        <?php elseif ($status === 'error'): ?>
            <div class="p-5 rounded-2xl bg-rose-950/80 border border-rose-500/50 text-rose-300 text-sm space-y-2 text-center">
                <i class="fa-solid fa-circle-exclamation text-3xl text-rose-400"></i>
                <h3 class="font-bold text-base text-white">Reset Encountered an Issue</h3>
                <p class="text-xs"><?= e($message) ?></p>
            </div>
        <?php endif; ?>

        <!-- Current Database Statistics -->
        <div class="bg-slate-900/80 p-5 rounded-2xl border border-slate-700/80 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Current Live Database Status</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="bg-slate-800 p-2.5 rounded-xl border border-slate-700">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Students</span>
                    <span class="text-xl font-bold font-mono <?= $counts['students'] > 0 ? 'text-amber-400' : 'text-emerald-400' ?>"><?= $counts['students'] ?></span>
                </div>
                <div class="bg-slate-800 p-2.5 rounded-xl border border-slate-700">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Fees</span>
                    <span class="text-xl font-bold font-mono <?= $counts['fees'] > 0 ? 'text-amber-400' : 'text-emerald-400' ?>"><?= $counts['fees'] ?></span>
                </div>
                <div class="bg-slate-800 p-2.5 rounded-xl border border-slate-700">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Attendance</span>
                    <span class="text-xl font-bold font-mono <?= $counts['attendance'] > 0 ? 'text-amber-400' : 'text-emerald-400' ?>"><?= $counts['attendance'] ?></span>
                </div>
                <div class="bg-slate-800 p-2.5 rounded-xl border border-slate-700">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Marks</span>
                    <span class="text-xl font-bold font-mono <?= $counts['exam_marks'] > 0 ? 'text-amber-400' : 'text-emerald-400' ?>"><?= $counts['exam_marks'] ?></span>
                </div>
                <div class="bg-slate-800 p-2.5 rounded-xl border border-slate-700">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Notices</span>
                    <span class="text-xl font-bold font-mono"><?= $counts['notices'] ?></span>
                </div>
                <div class="bg-slate-800 p-2.5 rounded-xl border border-slate-700">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Homework</span>
                    <span class="text-xl font-bold font-mono"><?= $counts['homework'] ?></span>
                </div>
                <div class="bg-slate-800 p-2.5 rounded-xl border border-slate-700">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Classes (Safe)</span>
                    <span class="text-xl font-bold font-mono text-cyan-400"><?= $counts['classes'] ?></span>
                </div>
                <div class="bg-slate-800 p-2.5 rounded-xl border border-slate-700">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Users (Safe)</span>
                    <span class="text-xl font-bold font-mono text-cyan-400"><?= $counts['users'] ?></span>
                </div>
            </div>
        </div>

        <form method="POST" action="reset_live_database.php" class="space-y-4">
            <input type="hidden" name="confirm_reset" value="1">
            <div class="p-4 bg-amber-950/40 border border-amber-500/30 rounded-xl text-amber-200 text-xs space-y-1">
                <p class="font-bold flex items-center space-x-1.5 text-amber-400">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Protected Safety Guarantee:</span>
                </p>
                <p>This action ONLY resets transaction dummy data (Students, Fees, Attendance, Marks, Notices) to 0. It does NOT delete your Admin/Accountant Logins, Classes (PG to 12th), or School Logo & Settings.</p>
            </div>

            <button type="submit" onclick="return confirm('Are you sure you want to clear all dummy students, fees, and attendance records on this server?');"
                class="w-full py-3.5 px-4 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-black rounded-xl shadow-lg shadow-rose-600/30 transition-all flex items-center justify-center space-x-2 text-sm">
                <i class="fa-solid fa-trash-can"></i>
                <span>One-Click Clear Live Test Data (Reset to 0)</span>
            </button>
        </form>

        <div class="pt-4 border-t border-slate-700 flex items-center justify-between text-xs text-slate-400">
            <a href="<?= base_url('admin/dashboard.php') ?>" class="hover:text-cyan-400 flex items-center space-x-1">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Admin Dashboard</span>
            </a>
            <a href="<?= base_url('login.php') ?>" class="hover:text-cyan-400 flex items-center space-x-1">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>Portal Login</span>
            </a>
        </div>

    </div>

</body>
</html>
