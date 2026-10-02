<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

// Fetch all portal users by role
$admins     = $pdo->query("SELECT * FROM users WHERE role = 'admin' ORDER BY id ASC")->fetchAll();
$accountants = $pdo->query("SELECT * FROM users WHERE role = 'accountant' ORDER BY id ASC")->fetchAll();
$teachers   = $pdo->query("SELECT * FROM teachers ORDER BY name ASC")->fetchAll();
$students   = $pdo->query("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.status = 'Active' ORDER BY s.first_name ASC")->fetchAll();

$page_title = 'User Accounts & Roles';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">User Accounts &amp; Role Permissions</h1>
            <p class="text-sm text-slate-500 mt-1">Manage all system users — Administrators, Accountants, Teachers, and Students.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= base_url('admin/users/create.php') ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>Create Admin / Accountant</span>
            </a>
            <a href="<?= base_url('admin/teachers/add.php') ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-chalkboard-user text-xs"></i>
                <span>Add Teacher</span>
            </a>
            <a href="<?= base_url('admin/accounts/admission.php') ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-user-graduate text-xs"></i>
                <span>Admit Student</span>
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php
        $stat_cards = [
            ['label' => 'Administrators', 'count' => count($admins),      'icon' => 'fa-user-shield',       'color' => 'blue'],
            ['label' => 'Accountants',    'count' => count($accountants),  'icon' => 'fa-calculator',        'color' => 'amber'],
            ['label' => 'Teachers',       'count' => count($teachers),     'icon' => 'fa-chalkboard-user',   'color' => 'indigo'],
            ['label' => 'Active Students','count' => count($students),     'icon' => 'fa-user-graduate',     'color' => 'emerald'],
        ];
        $palette = ['blue' => 'bg-blue-50 border-blue-200 text-blue-700', 'amber' => 'bg-amber-50 border-amber-200 text-amber-700', 'indigo' => 'bg-indigo-50 border-indigo-200 text-indigo-700', 'emerald' => 'bg-emerald-50 border-emerald-200 text-emerald-700'];
        foreach ($stat_cards as $sc): ?>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex items-center space-x-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 <?= $palette[$sc['color']] ?> border">
                <i class="fa-solid <?= $sc['icon'] ?> text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold text-slate-900"><?= $sc['count'] ?></p>
                <p class="text-xs text-slate-500 font-medium"><?= $sc['label'] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Administrators Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs"><i class="fa-solid fa-user-shield"></i></span>
                <h3 class="font-bold text-slate-900 text-base">System Administrators (<?= count($admins) ?>)</h3>
            </div>
            <a href="<?= base_url('admin/users/create.php?role=admin') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center space-x-1">
                <i class="fa-solid fa-plus text-[10px]"></i><span>Add Admin</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Full Name</th>
                        <th class="py-3.5 px-4">Username</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Role</th>
                        <th class="py-3.5 px-4">Created</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($admins)): ?>
                        <tr><td colspan="6" class="py-8 text-center text-slate-400 text-xs">No administrators found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($admins as $adm): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-extrabold text-[11px] flex items-center justify-center shrink-0">
                                        <?= strtoupper(substr($adm['full_name'], 0, 1)) ?>
                                    </div>
                                    <span><?= e($adm['full_name']) ?></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-700"><?= e($adm['username']) ?></td>
                            <td class="py-3.5 px-4 text-xs text-slate-600"><?= e($adm['email']) ?></td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fa-solid fa-shield-halved mr-1 text-[10px]"></i>
                                    <?= ucfirst(e($adm['role'])) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-500"><?= date('d M Y', strtotime($adm['created_at'])) ?></td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="<?= base_url('admin/users/edit.php?id=' . $adm['id']) ?>" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </a>
                                <?php if ($adm['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                <a href="<?= base_url('admin/users/delete.php?id=' . $adm['id']) ?>" onclick="return confirm('Delete this admin account?')" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors inline-block" title="Delete">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Accountants Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xs"><i class="fa-solid fa-calculator"></i></span>
                <h3 class="font-bold text-slate-900 text-base">Accountant Accounts (<?= count($accountants) ?>)</h3>
            </div>
            <a href="<?= base_url('admin/users/create.php?role=accountant') ?>" class="text-xs font-semibold text-amber-600 hover:text-amber-800 flex items-center space-x-1">
                <i class="fa-solid fa-plus text-[10px]"></i><span>Add Accountant</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Full Name</th>
                        <th class="py-3.5 px-4">Username</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Role</th>
                        <th class="py-3.5 px-4">Created</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($accountants)): ?>
                        <tr>
                            <td colspan="6" class="py-10 text-center">
                                <div class="text-slate-400 text-xs mb-3">No accountant accounts yet.</div>
                                <a href="<?= base_url('admin/users/create.php?role=accountant') ?>" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-xl transition-all">
                                    <i class="fa-solid fa-user-plus text-xs"></i>
                                    <span>Create First Accountant Account</span>
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                    <?php foreach ($accountants as $acc): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-700 font-extrabold text-[11px] flex items-center justify-center shrink-0">
                                        <?= strtoupper(substr($acc['full_name'], 0, 1)) ?>
                                    </div>
                                    <span><?= e($acc['full_name']) ?></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-700"><?= e($acc['username']) ?></td>
                            <td class="py-3.5 px-4 text-xs text-slate-600"><?= e($acc['email']) ?></td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fa-solid fa-calculator mr-1 text-[10px]"></i>
                                    Accountant
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-500"><?= date('d M Y', strtotime($acc['created_at'])) ?></td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="<?= base_url('admin/users/edit.php?id=' . $acc['id']) ?>" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </a>
                                <a href="<?= base_url('admin/users/delete.php?id=' . $acc['id']) ?>" onclick="return confirm('Delete this accountant account?')" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors inline-block" title="Delete">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Teacher Staff Accounts Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs"><i class="fa-solid fa-chalkboard-user"></i></span>
                <h3 class="font-bold text-slate-900 text-base">Teacher Accounts (<?= count($teachers) ?>)</h3>
            </div>
            <a href="<?= base_url('admin/teachers/add.php') ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center space-x-1">
                <i class="fa-solid fa-plus text-[10px]"></i><span>Add Teacher</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Teacher Name</th>
                        <th class="py-3.5 px-4">Teacher ID / Username</th>
                        <th class="py-3.5 px-4">Subject</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4">Login Status</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($teachers)): ?>
                        <tr><td colspan="6" class="py-8 text-center text-slate-400 text-xs">No teachers found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($teachers as $t): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-extrabold text-[11px] flex items-center justify-center shrink-0">
                                        <?= strtoupper(substr($t['name'], 0, 1)) ?>
                                    </div>
                                    <span><?= e($t['name']) ?></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-700">
                                <span class="text-indigo-700"><?= e($t['teacher_id']) ?></span>
                                <?php if ($t['username']): ?>
                                    <span class="text-slate-400 mx-1">/</span>
                                    <span><?= e($t['username']) ?></span>
                                <?php else: ?>
                                    <span class="text-rose-400 ml-1 text-[10px]">no login</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-xs font-bold text-indigo-700"><?= e($t['subject']) ?></td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                <div><?= e($t['mobile']) ?></div>
                                <div class="text-slate-400"><?= e($t['email']) ?></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <?php if ($t['username'] && $t['password']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Active Login
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                                        <i class="fa-solid fa-circle-xmark mr-1 text-[10px]"></i> No Login Set
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="<?= base_url('admin/teachers/edit.php?id=' . $t['id']) ?>" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="Edit Account">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Student Portal Accounts -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs"><i class="fa-solid fa-user-graduate"></i></span>
                <h3 class="font-bold text-slate-900 text-base">Student Portal Accounts (<?= count($students) ?> active)</h3>
            </div>
            <a href="<?= base_url('admin/accounts/admission.php') ?>" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 flex items-center space-x-1">
                <i class="fa-solid fa-plus text-[10px]"></i><span>Admit New Student</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Student Name</th>
                        <th class="py-3.5 px-4">Admission No (Login)</th>
                        <th class="py-3.5 px-4">Class</th>
                        <th class="py-3.5 px-4">Portal Password</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    <?php if (empty($students)): ?>
                        <tr><td colspan="5" class="py-8 text-center text-slate-400 text-xs">No active students found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($students as $st): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 font-extrabold text-[11px] flex items-center justify-center shrink-0">
                                        <?= strtoupper(substr($st['first_name'], 0, 1)) ?>
                                    </div>
                                    <span><?= e($st['first_name'] . ' ' . $st['last_name']) ?></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-bold text-emerald-700"><?= e($st['admission_no']) ?></td>
                            <td class="py-3.5 px-4 text-xs text-slate-600"><?= e($st['class_name'] . ' ' . $st['section']) ?></td>
                            <td class="py-3.5 px-4">
                                <?php if ($st['password']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-lock mr-1 text-[10px]"></i> Set
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-50 text-slate-500 border border-slate-200">
                                        <i class="fa-solid fa-lock-open mr-1 text-[10px]"></i> Not Set
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="<?= base_url('admin/students/edit.php?id=' . $st['id']) ?>" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="Edit / Set Password">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
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
