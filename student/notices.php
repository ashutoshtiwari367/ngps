<?php
$page_title = 'Notices & Bulletins';
require_once __DIR__ . '/../includes/auth.php';
require_student();

// Fetch Notices for Student
$notices = $pdo->query("SELECT * FROM notices WHERE target_role IN ('All', 'Student') ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6 max-w-4xl mx-auto">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">School Bulletins & Announcements</h1>
            <p class="text-xs text-slate-500 mt-1">Official communications from school administration</p>
        </div>
        <a href="<?= base_url('student/dashboard.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
            &larr; Back to Dashboard
        </a>
    </div>

    <div class="space-y-4">
        <?php if (empty($notices)): ?>
            <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-sm text-center text-slate-400 font-medium text-xs">
                No active announcements published.
            </div>
        <?php else: ?>
            <?php foreach ($notices as $nt): ?>
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400">
                            <i class="fa-regular fa-calendar-days mr-1"></i> Posted on: <strong class="text-slate-700"><?= date('d M Y, h:i A', strtotime($nt['created_at'])) ?></strong>
                        </span>
                        <?php if ($nt['is_important']): ?>
                            <span class="px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 font-extrabold text-xs rounded-full inline-flex items-center">
                                <i class="fa-solid fa-triangle-exclamation mr-1.5 text-xs"></i> Important Notice
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 font-bold text-xs rounded-full">
                                Announcement
                            </span>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-lg font-extrabold text-slate-900"><?= e($nt['title']) ?></h3>
                    <div class="text-slate-600 text-xs leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <?= e($nt['content']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
