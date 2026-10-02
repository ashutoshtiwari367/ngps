<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$student_id = (int)($_GET['student_id'] ?? 0);
$exam_id = (int)($_GET['exam_id'] ?? 0);

// Fetch Student Info
$stmt = $pdo->prepare("SELECT s.*, c.class_name, c.section 
    FROM students s 
    LEFT JOIN classes c ON s.class_id = c.id 
    WHERE s.id = :sid LIMIT 1");
$stmt->execute(['sid' => $student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student record not found.");
}

// Fetch Examination Info
$exam_stmt = $pdo->prepare("SELECT * FROM examinations WHERE id = :eid LIMIT 1");
$exam_stmt->execute(['eid' => $exam_id]);
$exam = $exam_stmt->fetch();

if (!$exam) {
    die("Examination record not found.");
}

// Fetch Exam Marks for this student across all subjects in the class
$marks_stmt = $pdo->prepare("SELECT sub.subject_name, sub.subject_code, m.marks_obtained, m.max_marks, m.grade, m.remarks
    FROM subjects sub
    LEFT JOIN exam_marks m ON (sub.id = m.subject_id AND m.student_id = :sid AND m.exam_id = :eid)
    WHERE sub.class_id = :cid
    ORDER BY sub.subject_name ASC");
$marks_stmt->execute(['sid' => $student_id, 'eid' => $exam_id, 'cid' => $student['class_id']]);
$marks = $marks_stmt->fetchAll();

// School Info Settings
$school_name = get_school_setting('school_name', 'Next Generation Public School');
$school_address = get_school_setting('school_address', '123 Education Boulevard, New Delhi');
$school_email = get_school_setting('school_email', 'contact@nextgenps.edu.in');
$school_phone = get_school_setting('school_phone', '+91 98765 43210');

// Calculate Totals
$total_obtained = 0;
$total_max = 0;
foreach ($marks as $m) {
    $total_obtained += (float)($m['marks_obtained'] ?? 0);
    $total_max += (float)($m['max_marks'] ?? 100);
}
$overall_pct = ($total_max > 0) ? round(($total_obtained / $total_max) * 100, 2) : 0;
if ($overall_pct >= 90) $overall_grade = 'A+';
elseif ($overall_pct >= 80) $overall_grade = 'A';
elseif ($overall_pct >= 70) $overall_grade = 'B+';
elseif ($overall_pct >= 60) $overall_grade = 'B';
elseif ($overall_pct >= 50) $overall_grade = 'C';
elseif ($overall_pct >= 40) $overall_grade = 'D';
else $overall_grade = 'F';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Report Card - <?= e($student['first_name'] . ' ' . $student['last_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 p-4 md:p-8">

    <!-- Print Control Bar -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-xl transition-all">
            &larr; Back
        </a>
        <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center space-x-2">
            <i class="fa-solid fa-print"></i>
            <span>Print Report Card</span>
        </button>
    </div>

    <!-- Official Report Card Sheet Container -->
    <div class="max-w-4xl mx-auto bg-white rounded-3xl border border-slate-300 shadow-2xl p-8 sm:p-12 relative overflow-hidden">
        
        <!-- Header Banner -->
        <div class="text-center border-b-2 border-slate-900 pb-6 mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white shadow-md mb-3">
                <i class="fa-solid fa-graduation-cap text-2xl"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase"><?= e($school_name) ?></h1>
            <p class="text-xs text-slate-600 font-medium mt-0.5"><?= e($school_address) ?> &bull; Ph: <?= e($school_phone) ?></p>
            <div class="mt-4 inline-block px-4 py-1.5 bg-slate-900 text-white font-bold text-xs uppercase tracking-widest rounded-full">
                OFFICIAL ACADEMIC PROGRESS REPORT CARD
            </div>
        </div>

        <!-- Exam & Session Title -->
        <div class="flex items-center justify-between mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-200">
            <div>
                <span class="text-xs text-slate-500 uppercase font-bold tracking-wider block">Examination Term</span>
                <span class="text-lg font-extrabold text-blue-900 block"><?= e($exam['exam_name']) ?></span>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500 uppercase font-bold tracking-wider block">Academic Session</span>
                <span class="text-sm font-mono font-bold text-slate-800 block"><?= e($exam['academic_session']) ?></span>
            </div>
        </div>

        <!-- Student Profile Details Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-blue-50/50 border border-blue-100 mb-8 text-xs">
            <div>
                <span class="text-slate-500 block font-semibold">Student Name:</span>
                <span class="font-extrabold text-slate-900 text-sm block"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block font-semibold">Admission No:</span>
                <span class="font-mono font-bold text-slate-800 text-sm block"><?= e($student['admission_no']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block font-semibold">Class & Section:</span>
                <span class="font-bold text-slate-900 text-sm block"><?= e($student['class_name']) ?> (<?= e($student['section']) ?>)</span>
            </div>
            <div>
                <span class="text-slate-500 block font-semibold">Father Name:</span>
                <span class="font-bold text-slate-900 text-sm block"><?= e($student['father_name']) ?></span>
            </div>
        </div>

        <!-- Marks Breakdown Table -->
        <div class="mb-8">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3">Scholastic Performance Breakdown</h3>
            <table class="w-full text-left border-collapse border border-slate-200 rounded-xl overflow-hidden">
                <thead>
                    <tr class="bg-slate-900 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Subject</th>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4 text-center">Marks Obtained</th>
                        <th class="py-3 px-4 text-center">Max Marks</th>
                        <th class="py-3 px-4 text-center">Grade</th>
                        <th class="py-3 px-4">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-medium text-slate-800">
                    <?php foreach ($marks as $m): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-bold text-slate-900"><?= e($m['subject_name']) ?></td>
                            <td class="py-3 px-4 font-mono text-slate-600 font-semibold"><?= e($m['subject_code']) ?></td>
                            <td class="py-3 px-4 text-center font-bold text-blue-900"><?= number_format((float)($m['marks_obtained'] ?? 0), 1) ?></td>
                            <td class="py-3 px-4 text-center text-slate-600"><?= number_format((float)($m['max_marks'] ?? 100), 1) ?></td>
                            <td class="py-3 px-4 text-center font-extrabold text-indigo-700"><?= e($m['grade'] ?? '-') ?></td>
                            <td class="py-3 px-4 text-slate-600 italic"><?= e($m['remarks'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-bold text-xs text-slate-900 border-t-2 border-slate-900">
                        <td colspan="2" class="py-3 px-4 uppercase font-extrabold">Grand Total</td>
                        <td class="py-3 px-4 text-center font-black text-sm text-blue-900"><?= number_format($total_obtained, 1) ?></td>
                        <td class="py-3 px-4 text-center font-black text-sm"><?= number_format($total_max, 1) ?></td>
                        <td class="py-3 px-4 text-center font-black text-base text-emerald-700"><?= $overall_grade ?></td>
                        <td class="py-3 px-4 text-emerald-800 font-bold">Overall Percentage: <?= $overall_pct ?>%</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Grade Scale & Signatures Footer -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 pt-8 border-t border-slate-200">
            <div>
                <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Grading Scale Reference</h4>
                <p class="text-[11px] text-slate-600 leading-relaxed font-mono">
                    A+ (90-100%) | A (80-89%) | B+ (70-79%) | B (60-69%) | C (50-59%) | D (40-49%) | F (&lt;40%)
                </p>
            </div>
            <div class="flex items-end justify-between pt-6">
                <div class="text-center">
                    <div class="w-32 border-b border-slate-400 mb-1"></div>
                    <span class="text-[11px] font-bold text-slate-700 block">Class Teacher</span>
                </div>
                <div class="text-center">
                    <div class="w-32 border-b border-slate-400 mb-1"></div>
                    <span class="text-[11px] font-bold text-slate-700 block">Principal Signature</span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
