<?php
$page_title = 'My Published Homework';
require_once __DIR__ . '/../../includes/auth.php';
require_teacher();

$teacher_id_pk = get_logged_teacher_id();

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $hw_id = (int)$_GET['id'];
    $csrf = $_GET['csrf'] ?? '';
    if (verify_csrf_token($csrf)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM homework WHERE id = :id AND teacher_id = :tid");
            $stmt->execute(['id' => $hw_id, 'tid' => $teacher_id_pk]);
            set_flash('success', 'Homework assignment deleted.');
        } catch (PDOException $e) {
            set_flash('error', 'Error deleting homework: ' . $e->getMessage());
        }
    }
    header('Location: ' . base_url('teacher/homework/index.php'));
    exit();
}

$homework = [];
if ($teacher_id_pk) {
    $stmt = $pdo->prepare("SELECT h.*, c.class_name, c.section, sub.subject_name 
        FROM homework h 
        LEFT JOIN classes c ON h.class_id = c.id 
        LEFT JOIN subjects sub ON h.subject_id = sub.id 
        WHERE h.teacher_id = :tid 
        ORDER BY h.issue_date DESC");
    $stmt->execute(['tid' => $teacher_id_pk]);
    $homework = $stmt->fetchAll();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Homework & Assignments</h1>
            <p class="text-sm text-slate-500 mt-1">Publish daily homework tasks and practice assignments for your classes.</p>
        </div>
        <div>
            <a href="<?= base_url('teacher/homework/add.php') ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Publish Homework</span>
            </a>
        </div>
    </div>

    <!-- Homework Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if (empty($homework)): ?>
            <div class="md:col-span-2 bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400">
                You have not published any homework assignments yet. Click 'Publish Homework' to create one.
            </div>
        <?php else: ?>
            <?php foreach ($homework as $hw): ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4 flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <?= e($hw['class_name'] ?? 'Class') ?> (Sec <?= e($hw['section'] ?? '-') ?>) &bull; <?= e($hw['subject_name'] ?? 'General') ?>
                            </span>
                            <span class="text-xs font-extrabold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200">
                                Due: <?= date('d M Y', strtotime($hw['due_date'])) ?>
                            </span>
                        </div>

                        <h3 class="text-lg font-extrabold text-slate-900 leading-snug"><?= e($hw['title']) ?></h3>
                        <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line"><?= e($hw['description']) ?></p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                        <div class="flex items-center space-x-2">
                            <i class="fa-regular fa-calendar text-slate-400"></i>
                            <span>Issued: <?= date('d M Y', strtotime($hw['issue_date'])) ?></span>
                        </div>
                        <a href="<?= base_url('teacher/homework/index.php?action=delete&id=' . $hw['id'] . '&csrf=' . csrf_token()) ?>" onclick="return confirm('Delete homework?')" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
