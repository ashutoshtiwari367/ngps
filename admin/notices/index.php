<?php
$page_title = 'Notices & Announcements';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

// Delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $notice_id = (int)$_GET['id'];
    $csrf = $_GET['csrf'] ?? '';
    if (verify_csrf_token($csrf)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM notices WHERE id = :id");
            $stmt->execute(['id' => $notice_id]);
            set_flash('success', 'Notice deleted successfully.');
        } catch (PDOException $e) {
            set_flash('error', 'Error deleting notice: ' . $e->getMessage());
        }
    }
    header('Location: ' . base_url('admin/notices/index.php'));
    exit();
}

$notices = $pdo->query("SELECT * FROM notices ORDER BY id DESC")->fetchAll();
?>

<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Notices & Digital Bulletins</h1>
            <p class="text-sm text-slate-500 mt-1">Publish official circulars, event announcements, and staff directives.</p>
        </div>
        <div>
            <a href="<?= base_url('admin/notices/add.php') ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Post New Notice</span>
            </a>
        </div>
    </div>

    <!-- Notices Directory -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if (empty($notices)): ?>
            <div class="md:col-span-2 bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400">
                No school notices published yet. Click 'Post New Notice' to publish one.
            </div>
        <?php else: ?>
            <?php foreach ($notices as $n): ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4 flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                Target: <?= e($n['target_role']) ?>
                            </span>
                            <?php if ($n['is_important']): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider">
                                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Important
                                </span>
                            <?php endif; ?>
                        </div>

                        <h3 class="text-lg font-extrabold text-slate-900 leading-snug"><?= e($n['title']) ?></h3>
                        <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line"><?= e($n['content']) ?></p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                        <div class="flex items-center space-x-2">
                            <i class="fa-regular fa-user text-slate-400"></i>
                            <span><?= e($n['author_name'] ?? 'School Admin') ?></span>
                            <span>&bull;</span>
                            <i class="fa-regular fa-clock text-slate-400"></i>
                            <span><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></span>
                        </div>
                        <a href="<?= base_url('admin/notices/index.php?action=delete&id=' . $n['id'] . '&csrf=' . csrf_token()) ?>" onclick="return confirm('Delete this notice?')" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Delete Notice">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
