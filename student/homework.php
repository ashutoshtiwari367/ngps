<?php
$page_title = 'My Homework & Assignments';
require_once __DIR__ . '/../includes/auth.php';
require_student();

$student_id_pk = get_logged_student_id();

// Fetch Student Class
$stmt = $pdo->prepare("SELECT class_id FROM students WHERE id = :sid LIMIT 1");
$stmt->execute(['sid' => $student_id_pk]);
$st = $stmt->fetch();
$class_id = $st['class_id'] ?? 0;

// Fetch Homework
$hw_stmt = $pdo->prepare("SELECT h.*, sub.subject_name, sub.subject_code, t.name as teacher_name 
    FROM homework h 
    LEFT JOIN subjects sub ON h.subject_id = sub.id 
    LEFT JOIN teachers t ON h.teacher_id = t.id 
    WHERE h.class_id = :cid 
    ORDER BY h.due_date DESC");
$hw_stmt->execute(['cid' => $class_id]);
$homeworks = $hw_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Homework & Assignments</h1>
            <p class="text-xs text-slate-500 mt-1">Review assignments given by subject teachers</p>
        </div>
        <a href="<?= base_url('student/dashboard.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
            &larr; Back to Dashboard
        </a>
    </div>

    <!-- Homework List -->
    <div class="space-y-4">
        <?php if (empty($homeworks)): ?>
            <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-sm text-center text-slate-400 font-medium text-xs">
                No homework or assignments published yet for your class.
            </div>
        <?php else: ?>
            <?php foreach ($homeworks as $hw): ?>
                <?php 
                    $is_overdue = (strtotime($hw['due_date']) < strtotime(date('Y-m-d')));
                ?>
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-3 hover:border-emerald-300 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <?= e($hw['subject_name'] ?? 'General') ?> (<?= e($hw['subject_code'] ?? '') ?>)
                            </span>
                            <span class="text-xs text-slate-400 font-medium">&bull; Assigned by: <strong class="text-slate-700"><?= e($hw['teacher_name'] ?? 'Faculty') ?></strong></span>
                        </div>
                        <div>
                            <?php if ($is_overdue): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                    Past Due (Submission Closed)
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                    Due Date: <?= date('d M Y', strtotime($hw['due_date'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900"><?= e($hw['title']) ?></h3>
                    <p class="text-slate-600 text-xs leading-relaxed whitespace-pre-line"><?= e($hw['description']) ?></p>

                    <div class="pt-2 text-[11px] text-slate-400 flex items-center justify-between border-t border-slate-50">
                        <span>Assigned Date: <?= date('d M Y', strtotime($hw['assigned_date'])) ?></span>
                        <span class="text-emerald-600 font-semibold"><i class="fa-solid fa-file-signature mr-1"></i> Submit directly to subject teacher</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
