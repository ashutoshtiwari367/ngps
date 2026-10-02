<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Fetch Statistics Dynamic Counts from MySQL Database
try {
    // 1. Total Students
    $total_students = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn() ?: 0;
    
    // 2. Total Teachers
    $total_teachers = $pdo->query("SELECT COUNT(*) FROM teachers WHERE status = 'Active'")->fetchColumn() ?: 0;
    
    // 3. Total Classes
    $total_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn() ?: 0;
    
    // 4. Fees Summary
    $fees_summary = $pdo->query("SELECT SUM(paid_amount) AS total_collected, SUM(remaining_amount) AS total_pending FROM fees")->fetch() ?: ['total_collected' => 0, 'total_pending' => 0];
    $total_collected = $fees_summary['total_collected'] ?: 0;
    $total_pending = $fees_summary['total_pending'] ?: 0;

    // 5. Today's Attendance
    $today_attendance = $pdo->query("SELECT 
        COUNT(CASE WHEN status = 'Present' THEN 1 END) AS present_count,
        COUNT(*) AS total_marked 
        FROM attendance 
        WHERE attendance_date = CURDATE()")->fetch() ?: ['present_count' => 0, 'total_marked' => 0];
    
    $today_present = $today_attendance['present_count'] ?: 0;
    $today_marked = $today_attendance['total_marked'] ?: 0;
    $attendance_pct = ($today_marked > 0) ? round(($today_present / $today_marked) * 100) : 0;

    // Fetch 5 Recent Students
    $recent_students_stmt = $pdo->query("SELECT s.*, c.class_name 
        FROM students s 
        LEFT JOIN classes c ON s.class_id = c.id 
        ORDER BY s.id DESC LIMIT 5");
    $recent_students = $recent_students_stmt->fetchAll();

    // Fetch Class Fee Collection Breakdown
    $class_fees_stmt = $pdo->query("SELECT c.class_name, c.section, SUM(f.paid_amount) as paid, SUM(f.remaining_amount) as pending 
        FROM classes c 
        LEFT JOIN fees f ON c.id = f.class_id 
        GROUP BY c.id ORDER BY c.class_name LIMIT 4");
    $class_fees = $class_fees_stmt->fetchAll();

    // 6. Recent Notices
    $recent_notices = $pdo->query("SELECT * FROM notices ORDER BY id DESC LIMIT 3")->fetchAll();

} catch (PDOException $e) {
    echo "<div class='p-4 bg-rose-50 text-rose-700 rounded-xl mb-4'>Error fetching statistics: " . e($e->getMessage()) . "</div>";
    $total_students = $total_teachers = $total_classes = $total_collected = $total_pending = $today_present = $attendance_pct = 0;
    $recent_students = [];
    $class_fees = [];
    $recent_notices = [];
}
?>

<div class="space-y-6">

    <!-- Page Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Next Generation Public School • Admin Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Welcome back, <span class="font-semibold text-slate-700"><?= e($_SESSION['full_name']) ?></span>! Central control panel for school records and academic operations.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= base_url('admin/accounts/admission.php') ?>" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>New Admission</span>
            </a>
            <a href="<?= base_url('admin/exams/add.php') ?>" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-file-pen text-xs"></i>
                <span>Create Exam</span>
            </a>
            <a href="<?= base_url('admin/notices/add.php') ?>" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-bullhorn text-xs"></i>
                <span>Post Notice</span>
            </a>
            <a href="<?= base_url('admin/settings/index.php') ?>" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-sliders text-xs"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>


    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5">
        
        <!-- Total Students -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Students</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-graduate text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-slate-900"><?= number_format($total_students) ?></span>
                <span class="block text-[11px] text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-arrow-trend-up"></i> Active Enrolled</span>
            </div>
        </div>

        <!-- Total Teachers -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Teachers</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-chalkboard-user text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-slate-900"><?= number_format($total_teachers) ?></span>
                <span class="block text-[11px] text-indigo-600 font-medium mt-0.5"><i class="fa-solid fa-check-circle"></i> Active Faculty</span>
            </div>
        </div>

        <!-- Total Classes -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Classes</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-school text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-slate-900"><?= number_format($total_classes) ?></span>
                <span class="block text-[11px] text-amber-600 font-medium mt-0.5">Active Sections</span>
            </div>
        </div>

        <!-- Total Fees Collected -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fees Collected</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-indian-rupee-sign text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-slate-900">₹<?= number_format($total_collected) ?></span>
                <span class="block text-[11px] text-emerald-600 font-medium mt-0.5">Paid Received</span>
            </div>
        </div>

        <!-- Pending Fees -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Fees</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-rose-600">₹<?= number_format($total_pending) ?></span>
                <span class="block text-[11px] text-rose-500 font-medium mt-0.5">Outstanding Dues</span>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Attendance</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-check text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-slate-900"><?= $attendance_pct ?>%</span>
                <span class="block text-[11px] text-purple-600 font-medium mt-0.5"><?= $today_present ?> Present Today</span>
            </div>
        </div>

    </div>

    <!-- Main Content Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Columns: Recent Students Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Recent Student Admissions</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Latest students added to the portal</p>
                </div>
                <a href="<?= base_url('admin/students/index.php') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center space-x-1">
                    <span>View All</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Admission No</th>
                            <th class="py-3 px-4">Class</th>
                            <th class="py-3 px-4">Mobile</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                        <?php if (empty($recent_students)): ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-sm">No recent student admissions found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_students as $s): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-xs overflow-hidden border border-blue-200">
                                                <?php if (!empty($s['photo']) && file_exists(__DIR__ . '/../assets/uploads/' . $s['photo'])): ?>
                                                    <img src="<?= base_url('assets/uploads/' . e($s['photo'])) ?>" alt="Photo" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs md:text-sm"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></span>
                                                <span class="text-[11px] text-slate-400 block">Father: <?= e($s['father_name']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs text-slate-600 font-semibold"><?= e($s['admission_no']) ?></td>
                                    <td class="py-3 px-4 text-xs font-semibold text-slate-800"><?= e($s['class_name'] ?? 'N/A') ?> (<?= e($s['section']) ?>)</td>
                                    <td class="py-3 px-4 text-xs text-slate-600"><?= e($s['mobile']) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <?= e($s['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="<?= base_url('admin/students/view.php?id=' . $s['id']) ?>" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Profile">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right 1 Column: Widgets (Attendance Summary & Quick Fee Progress) -->
        <div class="space-y-6">
            
            <!-- Today's Attendance Overview Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 text-base">Today's Attendance</h3>
                    <a href="<?= base_url('admin/attendance/index.php') ?>" class="text-xs text-blue-600 font-semibold hover:underline">Mark Now</a>
                </div>

                <div class="p-4 bg-purple-50/60 rounded-xl border border-purple-100 flex items-center justify-between mb-4">
                    <div>
                        <span class="text-xs text-purple-700 font-bold block uppercase tracking-wider">Attendance Rate</span>
                        <span class="text-3xl font-extrabold text-purple-900 mt-1 block"><?= $attendance_pct ?>%</span>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-purple-600 text-white flex items-center justify-center font-extrabold text-sm shadow-md shadow-purple-500/30">
                        <i class="fa-solid fa-chart-pie text-xl"></i>
                    </div>
                </div>

                <div class="space-y-2 text-xs font-semibold text-slate-600">
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                        <span class="flex items-center space-x-2"><i class="fa-solid fa-circle text-[8px] text-emerald-500"></i><span>Present Today</span></span>
                        <span class="font-mono font-bold text-slate-800"><?= $today_present ?></span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                        <span class="flex items-center space-x-2"><i class="fa-solid fa-circle text-[8px] text-rose-500"></i><span>Absent Today</span></span>
                        <span class="font-mono font-bold text-slate-800"><?= max(0, $today_marked - $today_present) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-1.5">
                        <span class="flex items-center space-x-2"><i class="fa-solid fa-circle text-[8px] text-slate-400"></i><span>Total Marked</span></span>
                        <span class="font-mono font-bold text-slate-800"><?= $today_marked ?></span>
                    </div>
                </div>
            </div>

            <!-- Fee Collection Summary Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 text-base">Fee Collections Summary</h3>
                    <a href="<?= base_url('admin/fees/index.php') ?>" class="text-xs text-blue-600 font-semibold hover:underline">View Records</a>
                </div>

                <?php 
                    $grand_total = $total_collected + $total_pending;
                    $collected_pct = ($grand_total > 0) ? round(($total_collected / $grand_total) * 100) : 0;
                ?>

                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-slate-600">Collection Progress</span>
                            <span class="text-emerald-700 font-mono font-bold"><?= $collected_pct ?>%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full rounded-full transition-all duration-500" style="width: <?= $collected_pct ?>%"></div>
                        </div>
                    </div>

                    <div class="pt-3 grid grid-cols-2 gap-3 text-center">
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Collected</span>
                            <span class="text-sm font-extrabold text-emerald-800 font-mono mt-0.5 block">₹<?= number_format($total_collected) ?></span>
                        </div>
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 block">Pending</span>
                            <span class="text-sm font-extrabold text-rose-800 font-mono mt-0.5 block">₹<?= number_format($total_pending) ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
