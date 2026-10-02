<?php
$page_title = 'My Attendance';
require_once __DIR__ . '/../includes/auth.php';
require_student();

$student_id_pk = get_logged_student_id();

// Fetch Attendance records
$stmt = $pdo->prepare("SELECT a.*, c.class_name, c.section 
    FROM attendance a 
    LEFT JOIN classes c ON a.class_id = c.id 
    WHERE a.student_id = :sid 
    ORDER BY a.attendance_date DESC");
$stmt->execute(['sid' => $student_id_pk]);
$records = $stmt->fetchAll();

// Statistics
$total_sessions = count($records);
$present_count = 0;
$absent_count = 0;
$late_count = 0;

foreach ($records as $r) {
    if ($r['status'] === 'Present') $present_count++;
    elseif ($r['status'] === 'Absent') $absent_count++;
    elseif ($r['status'] === 'Late') $late_count++;
}

$pct = ($total_sessions > 0) ? round(($present_count / $total_sessions) * 100) : 100;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Attendance Record</h1>
            <p class="text-xs text-slate-500 mt-1">Complete history of daily attendance marks</p>
        </div>
        <a href="<?= base_url('student/dashboard.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
            &larr; Back to Dashboard
        </a>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Attendance Rate</span>
            <span class="text-3xl font-extrabold text-emerald-600 block mt-1"><?= $pct ?>%</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Days Present</span>
            <span class="text-3xl font-extrabold text-slate-900 block mt-1"><?= $present_count ?></span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Days Absent</span>
            <span class="text-3xl font-extrabold text-rose-600 block mt-1"><?= $absent_count ?></span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Late Arrival</span>
            <span class="text-3xl font-extrabold text-amber-600 block mt-1"><?= $late_count ?></span>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Attendance Log History</h3>
            <span class="text-xs font-medium text-slate-400"><?= $total_sessions ?> total recorded days</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="p-4">Date</th>
                        <th class="p-4">Day</th>
                        <th class="p-4">Class</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400 font-medium">No attendance records found yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="p-4 font-bold text-slate-900"><?= date('d M Y', strtotime($r['attendance_date'])) ?></td>
                                <td class="p-4 text-slate-500 font-medium"><?= date('l', strtotime($r['attendance_date'])) ?></td>
                                <td class="p-4 font-semibold text-slate-800">Class <?= e($r['class_name'] ?? '-') ?> (<?= e($r['section'] ?? '-') ?>)</td>
                                <td class="p-4">
                                    <?php if ($r['status'] === 'Present'): ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Present
                                        </span>
                                    <?php elseif ($r['status'] === 'Absent'): ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-circle-xmark mr-1 text-[10px]"></i> Absent
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-clock mr-1 text-[10px]"></i> Late
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
