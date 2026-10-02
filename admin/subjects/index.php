<?php
$page_title = 'Manage Subjects';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $sub_id = (int)$_GET['id'];
    $csrf = $_GET['csrf'] ?? '';
    if (verify_csrf_token($csrf)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = :id");
            $stmt->execute(['id' => $sub_id]);
            set_flash('success', 'Subject deleted successfully.');
        } catch (PDOException $e) {
            set_flash('error', 'Failed to delete subject: ' . $e->getMessage());
        }
    }
    header('Location: ' . base_url('admin/subjects/index.php'));
    exit();
}

// Fetch all subjects with class and teacher details
$query = "SELECT s.*, c.class_name, c.section, t.name as teacher_name 
          FROM subjects s 
          LEFT JOIN classes c ON s.class_id = c.id 
          LEFT JOIN teachers t ON s.teacher_id = t.id 
          ORDER BY c.class_name ASC, s.subject_name ASC";
$subjects = $pdo->query($query)->fetchAll();
?>

<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Subjects Management</h1>
            <p class="text-sm text-slate-500 mt-1">Configure subjects, syllabus codes, and subject teacher assignments per class.</p>
        </div>
        <div>
            <a href="<?= base_url('admin/subjects/add.php') ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add New Subject</span>
            </a>
        </div>
    </div>

    <!-- Subjects Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base">Master Subject Directory (<?= count($subjects) ?>)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Subject Name</th>
                        <th class="py-3.5 px-4">Subject Code</th>
                        <th class="py-3.5 px-4">Class & Section</th>
                        <th class="py-3.5 px-4">Assigned Teacher</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No subjects created yet. Click 'Add New Subject' to begin.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subjects as $s): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block"><?= e($s['subject_name']) ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono text-xs px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg border border-slate-200 font-bold"><?= e($s['subject_code']) ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <?= e($s['class_name'] ?? 'Unassigned') ?> (Section <?= e($s['section'] ?? '-') ?>)
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($s['teacher_name'])): ?>
                                        <span class="text-xs font-semibold text-slate-800 flex items-center space-x-1.5">
                                            <i class="fa-solid fa-chalkboard-user text-indigo-500"></i>
                                            <span><?= e($s['teacher_name']) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-amber-600 font-medium italic">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1">
                                    <a href="<?= base_url('admin/subjects/edit.php?id=' . $s['id']) ?>" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <a href="<?= base_url('admin/subjects/index.php?action=delete&id=' . $s['id'] . '&csrf=' . csrf_token()) ?>" onclick="return confirm('Are you sure you want to delete this subject?')" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors inline-block" title="Delete">
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
