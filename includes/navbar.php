<!-- Top Navbar Component -->
<?php
if (is_admin()) {
    $nav_dash_link = base_url('admin/dashboard.php');
    $nav_role_title = 'Administrator';
    $nav_bg_gradient = 'from-blue-600 to-indigo-600';
} elseif (is_accountant()) {
    $nav_dash_link = base_url('admin/accounts/index.php');
    $nav_role_title = 'Accountant';
    $nav_bg_gradient = 'from-amber-600 to-yellow-600';
} elseif (is_teacher()) {
    $nav_dash_link = base_url('teacher/dashboard.php');
    $nav_role_title = 'Teacher';
    $nav_bg_gradient = 'from-indigo-600 to-purple-600';
} else {
    $nav_dash_link = base_url('student/dashboard.php');
    $nav_role_title = 'Student / Parent';
    $nav_bg_gradient = 'from-emerald-600 to-teal-600';
}
$current_academic_session = get_school_setting('academic_session', '2026-2027');
?>
<header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200/80 no-print">
    <div class="flex items-center justify-between h-16 px-4 md:px-6">
        
        <!-- Left: Mobile Toggle & Page Title / Breadcrumb -->
        <div class="flex items-center space-x-3">
            <button onclick="toggleSidebar()" class="md:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            
            <div class="hidden sm:flex items-center space-x-2 text-xs text-slate-500 font-medium">
                <a href="<?= $nav_dash_link ?>" class="hover:text-blue-600">Home</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <span class="text-slate-800 font-semibold"><?= e($page_title ?? 'Dashboard') ?></span>
            </div>
        </div>

        <!-- Center: Search Box for Admin / Accountant / Teacher -->
        <div class="hidden md:flex items-center flex-1 max-w-md mx-6">
            <?php if (!is_student()): ?>
            <form action="<?= base_url(is_teacher() ? 'teacher/students/index.php' : 'admin/students/index.php') ?>" method="GET" class="w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" placeholder="Search student name, admission no..." 
                    class="w-full pl-10 pr-4 py-2 text-sm bg-slate-100/80 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder-slate-400">
            </form>
            <?php else: ?>
            <div class="text-xs text-slate-500 font-medium">
                <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full border border-emerald-200 font-bold">
                    <i class="fa-solid fa-graduation-cap text-emerald-600 mr-1"></i> Class: <?= e($_SESSION['class_name'] ?? 'Registered Student') ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Actions & User Menu -->
        <div class="flex items-center space-x-3">
            
            <!-- Session Pill -->
            <div class="hidden lg:flex items-center space-x-1.5 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold border border-blue-100">
                <i class="fa-solid fa-calendar-days text-blue-500"></i>
                <span>Session: <?= e($current_academic_session) ?></span>
            </div>

            <!-- Notifications Icon -->
            <div class="relative">
                <a href="<?= base_url(is_admin() || is_accountant() ? 'admin/notices/index.php' : (is_teacher() ? 'teacher/notices/index.php' : 'student/notices.php')) ?>" class="relative p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors inline-block" title="Notices">
                    <i class="fa-regular fa-bell text-lg"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                </a>
            </div>

            <div class="h-6 w-px bg-slate-200"></div>

            <!-- User Menu -->
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr <?= $nav_bg_gradient ?> text-white flex items-center justify-center font-bold text-sm shadow-md">
                    <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="hidden md:block text-left">
                    <span class="block text-xs font-bold text-slate-800 leading-tight"><?= e($_SESSION['full_name'] ?? 'User') ?></span>
                    <span class="block text-[11px] text-slate-500 font-medium"><?= $nav_role_title ?></span>
                </div>
            </div>

        </div>
    </div>
</header>
