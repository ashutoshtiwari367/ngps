<?php
$page_title = 'Notices & Bulletins';
require_once __DIR__ . '/../../includes/auth.php';
require_teacher();

$notices = $pdo->query("SELECT * FROM notices WHERE target_role IN ('All', 'Teacher') ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Staff Notices & Announcements</h1>
            <p class="text-sm text-slate-500 mt-1">School circulars and announcements relevant to faculty members.</p>
        </div>
    </div>

    <!-- Notices Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if (empty($notices)): ?>
            <div class="md:col-span-2 bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400">
                No active staff notices published.
            </div>
        <?php else: ?>
            <?php foreach ($notices as $n): ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4 flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
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
                        </div>
                        <div class="flex items-center space-x-1">
                            <i class="fa-regular fa-clock text-slate-400"></i>
                            <span><?= date('d M Y', strtotime($n['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
