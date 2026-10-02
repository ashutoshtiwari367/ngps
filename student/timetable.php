<?php
$page_title = 'My Class Timetable';
require_once __DIR__ . '/../includes/auth.php';
require_student();

$student_id_pk = get_logged_student_id();

// Fetch Student Class
$stmt = $pdo->prepare("SELECT s.class_id, c.class_name, c.section, c.room_number 
    FROM students s 
    JOIN classes c ON s.class_id = c.id 
    WHERE s.id = :sid LIMIT 1");
$stmt->execute(['sid' => $student_id_pk]);
$student_info = $stmt->fetch();

$class_id = $student_info['class_id'] ?? 0;

// Fetch Timetable slots
$tt_stmt = $pdo->prepare("SELECT t.*, sub.subject_name, sub.subject_code, tch.name as teacher_name 
    FROM timetables t 
    JOIN subjects sub ON t.subject_id = sub.id 
    LEFT JOIN teachers tch ON t.teacher_id = tch.id 
    WHERE t.class_id = :cid 
    ORDER BY t.start_time ASC");
$tt_stmt->execute(['cid' => $class_id]);
$all_slots = $tt_stmt->fetchAll();

// Group by Day
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$schedule = [];
foreach ($days as $day) {
    $schedule[$day] = [];
}

foreach ($all_slots as $slot) {
    $d = ucfirst(strtolower($slot['day_of_week']));
    if (isset($schedule[$d])) {
        $schedule[$d][] = $slot;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Class Timetable</h1>
            <p class="text-xs text-slate-500 mt-1">Weekly subject schedule for Class <?= e($student_info['class_name'] ?? '') ?> (Section <?= e($student_info['section'] ?? '') ?>)</p>
        </div>
        <a href="<?= base_url('student/dashboard.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
            &larr; Back to Dashboard
        </a>
    </div>

    <!-- Timetable Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($days as $day): ?>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-900 text-sm"><?= $day ?></h3>
                    <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                        <?= count($schedule[$day]) ?> Periods
                    </span>
                </div>

                <div class="p-4 space-y-3 flex-1">
                    <?php if (empty($schedule[$day])): ?>
                        <div class="p-6 text-center text-xs text-slate-400 italic">No scheduled periods for <?= $day ?>.</div>
                    <?php else: ?>
                        <?php foreach ($schedule[$day] as $slot): ?>
                            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80 space-y-1.5 hover:border-emerald-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                        <i class="fa-regular fa-clock mr-1"></i>
                                        <?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Room <?= e($slot['room_number'] ?? $student_info['room_number'] ?? '101') ?></span>
                                </div>
                                <h4 class="font-extrabold text-slate-900 text-xs"><?= e($slot['subject_name']) ?> <span class="text-slate-400 font-normal">(<?= e($slot['subject_code']) ?>)</span></h4>
                                <div class="text-[11px] text-slate-500 font-medium flex items-center">
                                    <i class="fa-solid fa-user-tie text-slate-400 mr-1.5 text-xs"></i>
                                    <span>Teacher: <?= e($slot['teacher_name'] ?? 'Faculty') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
