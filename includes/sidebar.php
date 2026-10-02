<?php
$current_page = $_SERVER['SCRIPT_NAME'] ?? '';
function is_active_route($path) {
    global $current_page;
    return (strpos($current_page, $path) !== false);
}

// Compute dashboard URL for logo
if (is_admin()) {
    $dash_url = base_url('admin/dashboard.php');
    $role_badge = 'Admin Panel';
    $role_label = 'Administrator';
} elseif (is_accountant()) {
    $dash_url = base_url('admin/accounts/index.php');
    $role_badge = 'Accounts Dept';
    $role_label = 'Accountant';
} elseif (is_teacher()) {
    $dash_url = base_url('teacher/dashboard.php');
    $role_badge = 'Teacher Panel';
    $role_label = 'Teacher';
} else {
    $dash_url = base_url('student/dashboard.php');
    $role_badge = 'Student & Parent';
    $role_label = 'Student / Parent';
}
?>

<!-- Mobile Sidebar Backdrop -->
<div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm hidden md:hidden transition-opacity"></div>

<!-- Sidebar Container -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 transform -translate-x-full md:translate-x-0 md:static md:inset-0 transition-transform duration-300 ease-in-out flex flex-col justify-between shadow-2xl border-r border-slate-800 no-print">
    
    <!-- Top Branding Logo -->
    <div>
        <div class="h-16 flex items-center justify-between px-5 bg-slate-950/80 border-b border-slate-800/80">
            <a href="<?= $dash_url ?>" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-graduation-cap text-lg"></i>
                </div>
                <div>
                    <span class="font-black text-lg text-white tracking-wide block leading-tight">Next Generation Public School</span>
                    <span class="text-[10px] text-blue-400 font-bold tracking-wider uppercase"><?= $role_badge ?></span>
                </div>
            </a>
            <!-- Mobile Close Button -->
            <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Dynamic Navigation Links -->
        <nav class="mt-4 px-3 space-y-1 overflow-y-auto max-h-[calc(100vh-140px)]">
            
            <?php if (is_admin()): ?>
                <!-- ADMIN MENU -->
                <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Academic Admin</div>

                <a href="<?= base_url('admin/dashboard.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/admin/dashboard.php') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-sm <?= is_active_route('/admin/dashboard.php') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Dashboard</span>
                </a>

                <!-- Accounts Department Group -->
                <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold text-amber-500 uppercase tracking-wider">Accounts & Admission</div>

                <a href="<?= base_url('admin/accounts/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/admin/accounts/index.php') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-calculator w-5 text-center text-sm <?= is_active_route('/admin/accounts/index.php') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Accounts Dashboard</span>
                </a>

                <a href="<?= base_url('admin/accounts/admission.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/admin/accounts/admission.php') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-id-card-clip w-5 text-center text-sm <?= is_active_route('/admin/accounts/admission.php') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Admission & Fee Entry</span>
                </a>

                <a href="<?= base_url('admin/fees/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/fees/') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-receipt w-5 text-center text-sm <?= is_active_route('/fees/') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Fee Collection Log</span>
                </a>

                <a href="<?= base_url('admin/accounts/due_list.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/admin/accounts/due_list.php') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-sm <?= is_active_route('/admin/accounts/due_list.php') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Fee Defaulters & Dues</span>
                </a>

                <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">School Administration</div>

                <a href="<?= base_url('admin/students/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/students/') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-user-graduate w-5 text-center text-sm <?= is_active_route('/students/') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Students Roster</span>
                </a>

                <a href="<?= base_url('admin/teachers/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/teachers/') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-chalkboard-user w-5 text-center text-sm <?= is_active_route('/teachers/') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Teacher & Staff</span>
                </a>

                <a href="<?= base_url('admin/classes/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/classes/') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-school w-5 text-center text-sm <?= is_active_route('/classes/') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Classes & Sections</span>
                </a>

                <a href="<?= base_url('admin/subjects/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/subjects/') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-book-open w-5 text-center text-sm <?= is_active_route('/subjects/') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Subjects</span>
                </a>

                <a href="<?= base_url('admin/attendance/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/attendance/') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-calendar-check w-5 text-center text-sm <?= is_active_route('/attendance/') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Attendance</span>
                </a>

                <a href="<?= base_url('admin/exams/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/exams/') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-file-pen w-5 text-center text-sm <?= is_active_route('/exams/') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Exams & Marks</span>
                </a>

                <a href="<?= base_url('admin/timetable/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/timetable/') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-clock w-5 text-center text-sm <?= is_active_route('/timetable/') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Timetables</span>
                </a>

                <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Reports &amp; System</div>

                <a href="<?= base_url('admin/reports/students.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/reports/') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-chart-column w-5 text-center text-sm <?= is_active_route('/reports/') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Reports Center</span>
                </a>

                <a href="<?= base_url('admin/users/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/users/index') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-user-gear w-5 text-center text-sm <?= is_active_route('/users/index') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Users &amp; Roles</span>
                </a>

                <a href="<?= base_url('admin/users/create.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/users/create') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-user-plus w-5 text-center text-sm <?= is_active_route('/users/create') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">Create User Account</span>
                </a>

                <a href="<?= base_url('admin/settings/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/settings/') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-sliders w-5 text-center text-sm <?= is_active_route('/settings/') ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                    <span class="ml-2.5">School Settings</span>
                </a>

            <?php elseif (is_accountant()): ?>
                <!-- ACCOUNTANT MENU -->
                <div class="px-3 pb-1 text-[10px] font-extrabold text-amber-500 uppercase tracking-wider">Accounts Portal</div>

                <a href="<?= base_url('admin/accounts/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/admin/accounts/index.php') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-calculator w-5 text-center text-sm <?= is_active_route('/admin/accounts/index.php') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Accounts Dashboard</span>
                </a>

                <a href="<?= base_url('admin/accounts/admission.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/admin/accounts/admission.php') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-id-card-clip w-5 text-center text-sm <?= is_active_route('/admin/accounts/admission.php') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Admission & Fee Entry</span>
                </a>

                <a href="<?= base_url('admin/fees/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/fees/') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-receipt w-5 text-center text-sm <?= is_active_route('/fees/') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Collect Fee / Log</span>
                </a>

                <a href="<?= base_url('admin/accounts/due_list.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/admin/accounts/due_list.php') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-sm <?= is_active_route('/admin/accounts/due_list.php') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Fee Defaulters & Dues</span>
                </a>

                <a href="<?= base_url('admin/students/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/students/') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-user-graduate w-5 text-center text-sm <?= is_active_route('/students/') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Student Financial Profiles</span>
                </a>

                <a href="<?= base_url('admin/reports/fees.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/reports/fees.php') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-chart-line w-5 text-center text-sm <?= is_active_route('/reports/fees.php') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Fee Reports</span>
                </a>

                <a href="<?= base_url('admin/notices/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/notices/') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-bullhorn w-5 text-center text-sm <?= is_active_route('/notices/') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' ?>"></i>
                    <span class="ml-2.5">Notices &amp; Circulars</span>
                </a>


            <?php elseif (is_teacher()): ?>
                <!-- TEACHER MENU -->
                <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Teacher Portal</div>

                <a href="<?= base_url('teacher/dashboard.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('dashboard.php') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-gauge-high w-5 text-center text-sm <?= is_active_route('dashboard.php') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' ?>"></i>
                    <span class="ml-2.5">Dashboard</span>
                </a>

                <a href="<?= base_url('teacher/students/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/students/') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-user-graduate w-5 text-center text-sm <?= is_active_route('/students/') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' ?>"></i>
                    <span class="ml-2.5">My Class Roster</span>
                </a>

                <a href="<?= base_url('teacher/attendance/mark.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/attendance/') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-calendar-check w-5 text-center text-sm <?= is_active_route('/attendance/') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' ?>"></i>
                    <span class="ml-2.5">Daily Attendance</span>
                </a>

                <a href="<?= base_url('teacher/exams/marks.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/exams/') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-file-pen w-5 text-center text-sm <?= is_active_route('/exams/') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' ?>"></i>
                    <span class="ml-2.5">Exam Marks Entry</span>
                </a>

                <a href="<?= base_url('teacher/homework/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/homework/') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-book-bookmark w-5 text-center text-sm <?= is_active_route('/homework/') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' ?>"></i>
                    <span class="ml-2.5">Publish Homework</span>
                </a>

                <a href="<?= base_url('teacher/notices/index.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/notices/') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-bullhorn w-5 text-center text-sm <?= is_active_route('/notices/') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' ?>"></i>
                    <span class="ml-2.5">Notices & Bulletins</span>
                </a>

            <?php else: ?>
                <!-- STUDENT / PARENT MENU -->
                <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Student & Parent</div>

                <a href="<?= base_url('student/dashboard.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/student/dashboard.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-gauge-high w-5 text-center text-sm <?= is_active_route('/student/dashboard.php') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-400' ?>"></i>
                    <span class="ml-2.5">Dashboard</span>
                </a>

                <a href="<?= base_url('student/profile.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/student/profile.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-id-card w-5 text-center text-sm <?= is_active_route('/student/profile.php') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-400' ?>"></i>
                    <span class="ml-2.5">Student Profile</span>
                </a>

                <a href="<?= base_url('student/attendance.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/student/attendance.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-calendar-check w-5 text-center text-sm <?= is_active_route('/student/attendance.php') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-400' ?>"></i>
                    <span class="ml-2.5">Attendance Log</span>
                </a>

                <a href="<?= base_url('student/fees.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/student/fees.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-receipt w-5 text-center text-sm <?= is_active_route('/student/fees.php') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-400' ?>"></i>
                    <span class="ml-2.5">Fee Dues & Receipts</span>
                </a>

                <a href="<?= base_url('student/timetable.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/student/timetable.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-clock w-5 text-center text-sm <?= is_active_route('/student/timetable.php') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-400' ?>"></i>
                    <span class="ml-2.5">Class Timetable</span>
                </a>

                <a href="<?= base_url('student/homework.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/student/homework.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-book-bookmark w-5 text-center text-sm <?= is_active_route('/student/homework.php') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-400' ?>"></i>
                    <span class="ml-2.5">Homework & Assignments</span>
                </a>

                <a href="<?= base_url('student/results.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/student/results.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-square-poll-vertical w-5 text-center text-sm <?= is_active_route('/student/results.php') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-400' ?>"></i>
                    <span class="ml-2.5">Exam Results</span>
                </a>

                <a href="<?= base_url('student/notices.php') ?>" class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 group <?= is_active_route('/student/notices.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i class="fa-solid fa-bullhorn w-5 text-center text-sm <?= is_active_route('/student/notices.php') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-400' ?>"></i>
                    <span class="ml-2.5">Notices & Bulletins</span>
                </a>
            <?php endif; ?>

        </nav>
    </div>

    <!-- Bottom Profile / Logout Footer -->
    <div class="p-3 border-t border-slate-800 bg-slate-950/60">
        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800/50">
            <div class="flex items-center space-x-2.5 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-xs shrink-0">
                    <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-white truncate leading-tight"><?= e($_SESSION['full_name'] ?? 'User') ?></p>
                    <p class="text-[10px] text-slate-400 truncate leading-tight"><?= $role_label ?></p>
                </div>
            </div>
            <a href="<?= base_url('logout.php') ?>" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-400 transition-colors">
                <i class="fa-solid fa-right-from-bracket text-sm"></i>
            </a>
        </div>
    </div>
</aside>
