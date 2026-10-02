<?php
$page_title = 'Class Management';
require_once __DIR__ . '/../../includes/header.php';

try {
    $sql = "SELECT c.*, t.name as teacher_name, t.teacher_id as teacher_code,
            (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id) as student_count
            FROM classes c 
            LEFT JOIN teachers t ON c.teacher_id = t.id 
            ORDER BY c.class_name ASC, c.section ASC";
    $classes = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    set_flash('error', 'Error fetching classes: ' . $e->getMessage());
    $classes = [];
}
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Class & Section Management</h1>
            <p class="text-sm text-slate-500 mt-1">Configure academic classes, classroom assignments, and class teachers.</p>
        </div>
        <a href="add.php" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all self-start sm:self-auto">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add New Class</span>
        </a>
    </div>

    <!-- Classes Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Class Name</th>
                        <th class="py-3.5 px-4">Section</th>
                        <th class="py-3.5 px-4">Class Teacher</th>
                        <th class="py-3.5 px-4">Room No</th>
                        <th class="py-3.5 px-4">Enrolled Students</th>
                        <th class="py-3.5 px-4">Academic Session</th>
                        <th class="py-3.5 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($classes)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">No classes configured yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($classes as $c): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-900 text-base"><?= e($c['class_name']) ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Section <?= e($c['section']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($c['teacher_name'])): ?>
                                        <div class="flex items-center space-x-2">
                                            <i class="fa-solid fa-chalkboard-user text-blue-500"></i>
                                            <span class="font-semibold text-slate-800"><?= e($c['teacher_name']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-semibold text-slate-700"><?= e($c['room_number']) ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        <i class="fa-solid fa-users text-[10px] mr-1"></i> <?= $c['student_count'] ?> Students
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono text-slate-600"><?= e($c['academic_session']) ?></td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <a href="edit.php?id=<?= $c['id'] ?>" class="p-2 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Class">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $c['id'] ?>&csrf=<?= csrf_token() ?>" 
                                           onclick="return confirm('Are you sure you want to delete class <?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>)?');" 
                                           class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete Class">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </a>
                                    </div>
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
