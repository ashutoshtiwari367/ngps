<?php
$page_title = 'Examination Marks Entry';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

$exam_id = (int)($_GET['exam_id'] ?? 0);
$class_id = (int)($_GET['class_id'] ?? 0);
$subject_id = (int)($_GET['subject_id'] ?? 0);

// Fetch all exams
$exams = $pdo->query("SELECT * FROM examinations ORDER BY id DESC")->fetchAll();

if (!$exam_id && !empty($exams)) {
    $exam_id = $exams[0]['id'];
}

// Fetch classes
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

if (!$class_id && !empty($classes)) {
    $class_id = $classes[0]['id'];
}

// Fetch subjects for selected class
$subjects_stmt = $pdo->prepare("SELECT * FROM subjects WHERE class_id = :cid ORDER BY subject_name ASC");
$subjects_stmt->execute(['cid' => $class_id]);
$subjects = $subjects_stmt->fetchAll();

if (!$subject_id && !empty($subjects)) {
    $subject_id = $subjects[0]['id'];
}

// Handle Form POST Submission (Save Marks)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($csrf)) {
        $marks_data = $_POST['marks'] ?? [];
        $max_marks_val = (float)($_POST['max_marks'] ?? 100.00);

        try {
            $pdo->beginTransaction();

            $upsert_stmt = $pdo->prepare("INSERT INTO exam_marks (exam_id, student_id, subject_id, marks_obtained, max_marks, grade, remarks)
                VALUES (:eid, :sid, :subid, :mobt, :mmax, :grade, :remarks)
                ON DUPLICATE KEY UPDATE 
                marks_obtained = VALUES(marks_obtained),
                max_marks = VALUES(max_marks),
                grade = VALUES(grade),
                remarks = VALUES(remarks)");

            foreach ($marks_data as $student_id => $info) {
                $obtained = (float)($info['obtained'] ?? 0.00);
                $remarks = trim($info['remarks'] ?? '');

                // Calculate Grade
                $pct = ($max_marks_val > 0) ? ($obtained / $max_marks_val) * 100 : 0;
                if ($pct >= 90) $grade = 'A+';
                elseif ($pct >= 80) $grade = 'A';
                elseif ($pct >= 70) $grade = 'B+';
                elseif ($pct >= 60) $grade = 'B';
                elseif ($pct >= 50) $grade = 'C';
                elseif ($pct >= 40) $grade = 'D';
                else $grade = 'F';

                $upsert_stmt->execute([
                    'eid' => $exam_id,
                    'sid' => (int)$student_id,
                    'subid' => $subject_id,
                    'mobt' => $obtained,
                    'mmax' => $max_marks_val,
                    'grade' => $grade,
                    'remarks' => $remarks
                ]);
            }

            $pdo->commit();
            set_flash('success', 'Examination marks saved successfully!');
        } catch (PDOException $e) {
            $pdo->rollBack();
            set_flash('error', 'Error saving marks: ' . $e->getMessage());
        }
    }
}

// Fetch Students in selected class
$students_stmt = $pdo->prepare("SELECT s.*, m.marks_obtained, m.max_marks, m.grade, m.remarks 
    FROM students s 
    LEFT JOIN exam_marks m ON (s.id = m.student_id AND m.exam_id = :eid AND m.subject_id = :subid)
    WHERE s.class_id = :cid AND s.status = 'Active'
    ORDER BY s.first_name ASC, s.last_name ASC");
$students_stmt->execute(['eid' => $exam_id, 'subid' => $subject_id, 'cid' => $class_id]);
$students = $students_stmt->fetchAll();
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Marks Entry Sheet</h1>
            <p class="text-sm text-slate-500 mt-1">Select Examination, Class, and Subject to record student performance.</p>
        </div>
        <a href="<?= base_url('admin/exams/index.php') ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">
            &larr; Back to Exams
        </a>
    </div>

    <!-- Filter Form Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="marks.php" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Select Examination</label>
                <select name="exam_id" onchange="this.form.submit()" class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    <?php foreach ($exams as $ex): ?>
                        <option value="<?= $ex['id'] ?>" <?= $ex['id'] == $exam_id ? 'selected' : '' ?>><?= e($ex['exam_name']) ?> (<?= e($ex['status']) ?>)</option>
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

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Select Subject</label>
                <select name="subject_id" onchange="this.form.submit()" class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                    <?php if (empty($subjects)): ?>
                        <option value="">No subjects found for class</option>
                    <?php else: ?>
                        <?php foreach ($subjects as $sb): ?>
                            <option value="<?= $sb['id'] ?>" <?= $sb['id'] == $subject_id ? 'selected' : '' ?>><?= e($sb['subject_name']) ?> (<?= e($sb['subject_code']) ?>)</option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

        </form>
    </div>

    <!-- Marks Entry Grid Form -->
    <?php if ($exam_id && $class_id && $subject_id): ?>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            
            <form action="marks.php?exam_id=<?= $exam_id ?>&class_id=<?= $class_id ?>&subject_id=<?= $subject_id ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="save_marks" value="1">

                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Maximum Marks:</span>
                        <input type="number" step="0.5" name="max_marks" value="<?= $students[0]['max_marks'] ?? 100 ?>" required class="w-24 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-800 text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Save All Marks</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4">#</th>
                                <th class="py-3 px-4">Admission No</th>
                                <th class="py-3 px-4">Student Name</th>
                                <th class="py-3 px-4 w-40">Marks Obtained</th>
                                <th class="py-3 px-4">Calculated Grade</th>
                                <th class="py-3 px-4">Remarks</th>
                                <th class="py-3 px-4 text-right">Report Card</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">No active students enrolled in this class.</td>
                                </tr>
                            <?php else: ?>
                                <?php $idx = 1; foreach ($students as $s): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-4 text-xs font-bold text-slate-400"><?= $idx++ ?></td>
                                        <td class="py-3 px-4 font-mono text-xs text-slate-600 font-semibold"><?= e($s['admission_no']) ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-900"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td>
                                        <td class="py-3 px-4">
                                            <input type="number" step="0.5" min="0" max="100" name="marks[<?= $s['id'] ?>][obtained]" value="<?= $s['marks_obtained'] ?? '0' ?>" required
                                                class="w-full py-1.5 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                                        </td>
                                        <td class="py-3 px-4">
                                            <?php if (!empty($s['grade'])): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    <?= e($s['grade']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400 italic">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4">
                                            <input type="text" name="marks[<?= $s['id'] ?>][remarks]" value="<?= e($s['remarks'] ?? '') ?>" placeholder="Optional remark"
                                                class="w-full py-1.5 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="<?= base_url('admin/exams/report_card.php?student_id=' . $s['id'] . '&exam_id=' . $exam_id) ?>" target="_blank" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="Generate Report Card">
                                                <i class="fa-solid fa-print text-xs"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Save All Marks</span>
                    </button>
                </div>

            </form>

        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
