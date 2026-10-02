<?php
$page_title = 'My Examination Results';
require_once __DIR__ . '/../includes/auth.php';
require_student();

$student_id_pk = get_logged_student_id();

// Fetch student class
$stmt = $pdo->prepare("SELECT s.class_id, c.class_name, c.section 
    FROM students s 
    JOIN classes c ON s.class_id = c.id 
    WHERE s.id = :sid LIMIT 1");
$stmt->execute(['sid' => $student_id_pk]);
$student_info = $stmt->fetch();
$class_id = $student_info['class_id'] ?? 0;

// Fetch Examinations held for this class
$exams = $pdo->query("SELECT * FROM examinations ORDER BY id DESC")->fetchAll();

$selected_exam_id = (int)($_GET['exam_id'] ?? ($exams[0]['id'] ?? 0));

// Fetch marks for selected exam
$marks = [];
if ($selected_exam_id > 0) {
    $m_stmt = $pdo->prepare("SELECT em.*, sub.subject_name, sub.subject_code 
        FROM exam_marks em 
        JOIN subjects sub ON em.subject_id = sub.id 
        WHERE em.exam_id = :eid AND em.student_id = :sid");
    $m_stmt->execute(['eid' => $selected_exam_id, 'sid' => $student_id_pk]);
    $marks = $m_stmt->fetchAll();
}

// Compute Summary
$total_obtained = 0;
$total_max = 0;
foreach ($marks as $m) {
    $total_obtained += (float)$m['marks_obtained'];
    $total_max += (float)$m['max_marks'];
}

$pct = ($total_max > 0) ? round(($total_obtained / $total_max) * 100, 1) : 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Academic Results & Marksheet</h1>
            <p class="text-xs text-slate-500 mt-1">Official examination performance for Class <?= e($student_info['class_name'] ?? '') ?> (Section <?= e($student_info['section'] ?? '') ?>)</p>
        </div>
        <div class="flex items-center space-x-3">
            <?php if ($selected_exam_id > 0 && !empty($marks)): ?>
                <a href="<?= base_url('admin/exams/report_card.php?student_id=' . $student_id_pk . '&exam_id=' . $selected_exam_id) ?>" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-500/20 transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-print"></i>
                    <span>Print Report Card</span>
                </a>
            <?php endif; ?>
            <a href="<?= base_url('student/dashboard.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                &larr; Back
            </a>
        </div>
    </div>

    <!-- Exam Selector Tabs -->
    <?php if (!empty($exams)): ?>
        <div class="flex items-center space-x-2 overflow-x-auto pb-2 border-b border-slate-200">
            <?php foreach ($exams as $ex): ?>
                <a href="?exam_id=<?= $ex['id'] ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all <?= ($selected_exam_id == $ex['id']) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                    <?= e($ex['exam_name']) ?> (<?= e($ex['session']) ?>)
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Overview Banner -->
    <?php if ($selected_exam_id > 0 && !empty($marks)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Marks</span>
                <span class="text-3xl font-extrabold text-slate-900 block mt-1"><?= $total_obtained ?> <span class="text-xs text-slate-400 font-normal">/ <?= $total_max ?></span></span>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Percentage</span>
                <span class="text-3xl font-extrabold text-emerald-600 block mt-1"><?= $pct ?>%</span>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Result Status</span>
                <span class="text-3xl font-extrabold <?= $pct >= 40 ? 'text-emerald-600' : 'text-rose-600' ?> block mt-1">
                    <?= $pct >= 40 ? 'PASSED' : 'FAILED' ?>
                </span>
            </div>
        </div>

        <!-- Subject Wise Marks Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Subject Wise Marks Breakdown</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                            <th class="p-4">Subject Name</th>
                            <th class="p-4">Code</th>
                            <th class="p-4 text-center">Max Marks</th>
                            <th class="p-4 text-center">Marks Obtained</th>
                            <th class="p-4 text-center">Grade</th>
                            <th class="p-4">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        <?php foreach ($marks as $m): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="p-4 font-bold text-slate-900"><?= e($m['subject_name']) ?></td>
                                <td class="p-4 text-slate-500 font-mono"><?= e($m['subject_code']) ?></td>
                                <td class="p-4 text-center font-semibold text-slate-600"><?= (float)$m['max_marks'] ?></td>
                                <td class="p-4 text-center font-extrabold text-emerald-700 text-sm"><?= (float)$m['marks_obtained'] ?></td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 rounded-md font-extrabold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        <?= e($m['grade'] ?: 'A') ?>
                                    </span>
                                </td>
                                <td class="p-4 text-slate-500 font-medium"><?= e($m['remarks'] ?: 'Satisfactory') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-sm text-center text-slate-400 font-medium text-xs">
            No exam marks published for the selected examination session yet.
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
