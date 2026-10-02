<?php
$page_title = 'Academic Reports';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

$exam_id = (int)($_GET['exam_id'] ?? 0);
$class_id = (int)($_GET['class_id'] ?? 0);

$exams = $pdo->query("SELECT * FROM examinations ORDER BY id DESC")->fetchAll();
if (!$exam_id && !empty($exams)) {
    $exam_id = $exams[0]['id'];
}

$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();
if (!$class_id && !empty($classes)) {
    $class_id = $classes[0]['id'];
}

// Fetch performance records
$query = "SELECT s.id as student_id, s.admission_no, s.first_name, s.last_name,
            COUNT(m.id) as subjects_appeared,
            SUM(m.marks_obtained) as total_obtained,
            SUM(m.max_marks) as total_max
          FROM students s
          JOIN exam_marks m ON s.id = m.student_id
          WHERE m.exam_id = :eid AND s.class_id = :cid
          GROUP BY s.id
          ORDER BY total_obtained DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(['eid' => $exam_id, 'cid' => $class_id]);
$records = $stmt->fetchAll();
?>

<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Academic Performance Report</h1>
            <p class="text-sm text-slate-500 mt-1">Class-wise examination score summaries, grade rankings, and printable marks sheets.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex space-x-2 border-b border-slate-200 no-print">
        <a href="<?= base_url('admin/reports/students.php') ?>" class="py-2.5 px-4 text-xs font-bold text-slate-500 hover:text-slate-900 border-b-2 border-transparent">Student Directory</a>
        <a href="<?= base_url('admin/reports/attendance.php') ?>" class="py-2.5 px-4 text-xs font-bold text-slate-500 hover:text-slate-900 border-b-2 border-transparent">Attendance Reports</a>
        <a href="<?= base_url('admin/reports/fees.php') ?>" class="py-2.5 px-4 text-xs font-bold text-slate-500 hover:text-slate-900 border-b-2 border-transparent">Fee Reports</a>
        <a href="<?= base_url('admin/reports/academic.php') ?>" class="py-2.5 px-4 text-xs font-bold text-blue-600 border-b-2 border-blue-600">Academic Reports</a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm no-print">
        <form method="GET" action="academic.php" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Select Examination</label>
                <select name="exam_id" onchange="this.form.submit()" class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    <?php foreach ($exams as $ex): ?>
                        <option value="<?= $ex['id'] ?>" <?= $ex['id'] == $exam_id ? 'selected' : '' ?>><?= e($ex['exam_name']) ?> (<?= e($ex['academic_session']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Select Class</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $class_id ? 'selected' : '' ?>><?= e($c['class_name']) ?> (Sec <?= e($c['section']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base">Ranked Student Scores (<?= count($records) ?>)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Rank</th>
                        <th class="py-3.5 px-4">Admission No</th>
                        <th class="py-3.5 px-4">Student Name</th>
                        <th class="py-3.5 px-4 text-center">Subjects Appeared</th>
                        <th class="py-3.5 px-4 text-center">Marks Obtained</th>
                        <th class="py-3.5 px-4 text-center">Max Marks</th>
                        <th class="py-3.5 px-4 text-center">Percentage</th>
                        <th class="py-3.5 px-4 text-center">Overall Grade</th>
                        <th class="py-3.5 px-4 text-right no-print">Report Card</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">No marks recorded for this exam & class combination yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php $rank = 1; foreach ($records as $r): ?>
                            <?php 
                                $pct = ($r['total_max'] > 0) ? round(($r['total_obtained'] / $r['total_max']) * 100, 2) : 0;
                                if ($pct >= 90) $grade = 'A+';
                                elseif ($pct >= 80) $grade = 'A';
                                elseif ($pct >= 70) $grade = 'B+';
                                elseif ($pct >= 60) $grade = 'B';
                                elseif ($pct >= 50) $grade = 'C';
                                elseif ($pct >= 40) $grade = 'D';
                                else $grade = 'F';
                            ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-extrabold text-xs <?= $rank <= 3 ? 'text-amber-600' : 'text-slate-400' ?>">#<?= $rank++ ?></td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-600 font-semibold"><?= e($r['admission_no']) ?></td>
                                <td class="py-3.5 px-4 font-bold text-slate-900"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                <td class="py-3.5 px-4 text-center text-xs text-slate-600"><?= $r['subjects_appeared'] ?></td>
                                <td class="py-3.5 px-4 text-center font-bold text-blue-900"><?= number_format($r['total_obtained'], 1) ?></td>
                                <td class="py-3.5 px-4 text-center text-slate-600"><?= number_format($r['total_max'], 1) ?></td>
                                <td class="py-3.5 px-4 text-center font-extrabold text-slate-900"><?= $pct ?>%</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <?= $grade ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right no-print">
                                    <a href="<?= base_url('admin/exams/report_card.php?student_id=' . $r['student_id'] . '&exam_id=' . $exam_id) ?>" target="_blank" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="Report Card">
                                        <i class="fa-solid fa-file-invoice text-xs"></i>
                                    </a>
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
