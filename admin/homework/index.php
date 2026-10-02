<?php
$page_title = 'Homework & Assignments';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

// Delete Homework action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $hw_id = (int)$_GET['id'];
    $csrf = $_GET['csrf'] ?? '';
    if (verify_csrf_token($csrf)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM homework WHERE id = :id");
            $stmt->execute(['id' => $hw_id]);
            set_flash('success', 'Homework record deleted successfully.');
        } catch (PDOException $e) {
            set_flash('error', 'Error deleting homework: ' . $e->getMessage());
        }
    }
    header('Location: ' . base_url('admin/homework/index.php'));
    exit();
}

$homework = $pdo->query("SELECT h.*, c.class_name, c.section, sub.subject_name, t.name as teacher_name 
    FROM homework h 
    LEFT JOIN classes c ON h.class_id = c.id 
    LEFT JOIN subjects sub ON h.subject_id = sub.id 
    LEFT JOIN teachers t ON h.teacher_id = t.id 
    ORDER BY h.issue_date DESC")->fetchAll();
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Homework & Assignment Oversight</h1>
            <p class="text-sm text-slate-500 mt-1">Review active and past homework published by subject teachers.</p>
        </div>
        <div>
            <a href="<?= base_url('teacher/homework/add.php') ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Publish Assignment</span>
            </a>
        </div>
    </div>

    <!-- Homework Directory Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base">Published Homework Directory (<?= count($homework) ?>)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Title & Details</th>
                        <th class="py-3.5 px-4">Class</th>
                        <th class="py-3.5 px-4">Subject</th>
                        <th class="py-3.5 px-4">Teacher</th>
                        <th class="py-3.5 px-4">Issue Date</th>
                        <th class="py-3.5 px-4">Due Date</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($homework)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No homework records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($homework as $hw): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 max-w-xs">
                                    <span class="font-bold text-slate-900 block"><?= e($hw['title']) ?></span>
                                    <span class="text-xs text-slate-500 line-clamp-1 block mt-0.5"><?= e($hw['description']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-semibold text-slate-800">
                                    <?= e($hw['class_name'] ?? 'N/A') ?> (Sec <?= e($hw['section'] ?? '-') ?>)
                                </td>
                                <td class="py-3.5 px-4 text-xs font-bold text-blue-700">
                                    <?= e($hw['subject_name'] ?? 'General') ?>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-medium text-slate-700">
                                    <?= e($hw['teacher_name'] ?? 'Admin') ?>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600"><?= date('d M Y', strtotime($hw['issue_date'])) ?></td>
                                <td class="py-3.5 px-4 text-xs font-bold text-rose-600"><?= date('d M Y', strtotime($hw['due_date'])) ?></td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="<?= base_url('admin/homework/index.php?action=delete&id=' . $hw['id'] . '&csrf=' . csrf_token()) ?>" onclick="return confirm('Delete homework assignment?')" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors inline-block" title="Delete Homework">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
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
