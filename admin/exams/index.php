<?php
$page_title = 'Examinations Management';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

// Delete Exam Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $exam_id = (int)$_GET['id'];
    $csrf = $_GET['csrf'] ?? '';
    if (verify_csrf_token($csrf)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM examinations WHERE id = :id");
            $stmt->execute(['id' => $exam_id]);
            set_flash('success', 'Examination schedule deleted successfully.');
        } catch (PDOException $e) {
            set_flash('error', 'Error deleting exam: ' . $e->getMessage());
        }
    }
    header('Location: ' . base_url('admin/exams/index.php'));
    exit();
}

$exams = $pdo->query("SELECT * FROM examinations ORDER BY start_date DESC")->fetchAll();
?>

<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Examinations & Result Management</h1>
            <p class="text-sm text-slate-500 mt-1">Schedule exams, record student marks, evaluate grades, and generate student report cards.</p>
        </div>
        <div>
            <a href="<?= base_url('admin/exams/add.php') ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Schedule New Exam</span>
            </a>
        </div>
    </div>

    <!-- Examinations List Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base">Examination Schedules (<?= count($exams) ?>)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Exam Name</th>
                        <th class="py-3.5 px-4">Session</th>
                        <th class="py-3.5 px-4">Start Date</th>
                        <th class="py-3.5 px-4">End Date</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No examinations created yet. Click 'Schedule New Exam' to start.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($exams as $ex): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    <?= e($ex['exam_name']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-semibold text-slate-600 font-mono"><?= e($ex['academic_session']) ?></td>
                                <td class="py-3.5 px-4 text-xs text-slate-600"><i class="fa-regular fa-calendar text-slate-400 mr-1"></i><?= date('d M Y', strtotime($ex['start_date'])) ?></td>
                                <td class="py-3.5 px-4 text-xs text-slate-600"><i class="fa-regular fa-calendar text-slate-400 mr-1"></i><?= date('d M Y', strtotime($ex['end_date'])) ?></td>
                                <td class="py-3.5 px-4">
                                    <?php 
                                        $st = $ex['status'];
                                        $badge_cls = ($st === 'Published') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (($st === 'Ongoing') ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-700 border-slate-200');
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border <?= $badge_cls ?>">
                                        <?= e($st) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <a href="<?= base_url('admin/exams/marks.php?exam_id=' . $ex['id']) ?>" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-lg transition-colors inline-flex items-center space-x-1">
                                        <i class="fa-solid fa-file-pen text-xs"></i>
                                        <span>Marks Entry</span>
                                    </a>
                                    <a href="<?= base_url('admin/exams/index.php?action=delete&id=' . $ex['id'] . '&csrf=' . csrf_token()) ?>" onclick="return confirm('Are you sure you want to delete this exam schedule?')" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors inline-block" title="Delete Exam">
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
