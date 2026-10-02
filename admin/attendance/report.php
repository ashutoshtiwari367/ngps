<?php
$page_title = 'Attendance Report';
require_once __DIR__ . '/../../includes/header.php';

$class_filter = intval($_GET['class_id'] ?? 0);
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

if ($class_filter === 0 && !empty($classes)) {
    $class_filter = $classes[0]['id'];
}

try {
    // Selected Class Info
    $sel_class_stmt = $pdo->prepare("SELECT * FROM classes WHERE id = :id LIMIT 1");
    $sel_class_stmt->execute(['id' => $class_filter]);
    $selected_class = $sel_class_stmt->fetch();

    // Fetch students attendance tally
    $sql = "SELECT s.id, s.admission_no, s.first_name, s.last_name, s.father_name,
            COUNT(a.id) as total_recorded,
            COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as present_count,
            COUNT(CASE WHEN a.status = 'Absent' THEN 1 END) as absent_count
            FROM students s 
            LEFT JOIN attendance a ON s.id = a.student_id 
            WHERE s.class_id = :class_id AND s.status = 'Active' 
            GROUP BY s.id 
            ORDER BY s.first_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['class_id' => $class_filter]);
    $report_data = $stmt->fetchAll();

} catch (PDOException $e) {
    set_flash('error', 'Report calculation error: ' . $e->getMessage());
    $report_data = [];
}
?>

<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Attendance Analytics Report</h1>
            <p class="text-sm text-slate-500 mt-1">Class-wise attendance performance, percentages, and summaries.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="index.php" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-list-check"></i>
                <span>Mark Attendance</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    <!-- Class Selector Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <form method="GET" action="report.php" class="flex flex-col sm:flex-row items-center gap-3">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-600 whitespace-nowrap">Filter Class:</label>
            <select name="class_id" onchange="this.form.submit()" class="w-full sm:w-72 py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($class_filter == $c['id']) ? 'selected' : '' ?>>
                        <?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Printable Report Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6">
        <div class="mb-6 pb-4 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">
                    Attendance Report: <?= e($selected_class['class_name'] ?? 'Class') ?> (Section <?= e($selected_class['section'] ?? '') ?>)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Academic Session 2026-2027</p>
            </div>
            <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                Generated: <?= date('d M Y') ?>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Student Name</th>
                        <th class="py-3.5 px-4">Admission No</th>
                        <th class="py-3.5 px-4 text-center">Total Days</th>
                        <th class="py-3.5 px-4 text-center">Present</th>
                        <th class="py-3.5 px-4 text-center">Absent</th>
                        <th class="py-3.5 px-4">Attendance Rate</th>
                        <th class="py-3.5 px-4 text-center">Status Badge</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">No active student records found for this class.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($report_data as $row): 
                            $total = $row['total_recorded'] ?: 0;
                            $present = $row['present_count'] ?: 0;
                            $absent = $row['absent_count'] ?: 0;
                            $pct = ($total > 0) ? round(($present / $total) * 100) : 0;
                        ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block"><?= e($row['first_name'] . ' ' . $row['last_name']) ?></span>
                                    <span class="text-[11px] text-slate-400">Father: <?= e($row['father_name']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs font-bold text-blue-700"><?= e($row['admission_no']) ?></td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700"><?= $total ?></td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-700"><?= $present ?></td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-rose-600"><?= $absent ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="w-full max-w-xs">
                                        <div class="flex justify-between text-xs font-mono font-bold mb-1">
                                            <span class="<?= $pct >= 75 ? 'text-emerald-700' : 'text-rose-600' ?>"><?= $pct ?>%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-300 <?= $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-rose-500') ?>" style="width: <?= $pct ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $pct >= 75 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($pct >= 50 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') ?>">
                                        <?= $pct >= 75 ? 'Good' : ($pct >= 50 ? 'Average' : 'Low') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
