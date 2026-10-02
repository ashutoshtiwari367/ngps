<?php
$page_title = 'Class Timetables';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

$class_id = (int)($_GET['class_id'] ?? 0);

// Fetch classes
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();
if (!$class_id && !empty($classes)) {
    $class_id = $classes[0]['id'];
}

// Delete slot action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $slot_id = (int)$_GET['id'];
    $csrf = $_GET['csrf'] ?? '';
    if (verify_csrf_token($csrf)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM timetables WHERE id = :id");
            $stmt->execute(['id' => $slot_id]);
            set_flash('success', 'Timetable slot removed.');
        } catch (PDOException $e) {
            set_flash('error', 'Error removing slot: ' . $e->getMessage());
        }
    }
    header('Location: ' . base_url('admin/timetable/index.php?class_id=' . $class_id));
    exit();
}

// Fetch Timetable slots for class
$tt_stmt = $pdo->prepare("SELECT tt.*, sub.subject_name, sub.subject_code, t.name as teacher_name 
    FROM timetables tt 
    LEFT JOIN subjects sub ON tt.subject_id = sub.id 
    LEFT JOIN teachers t ON tt.teacher_id = t.id 
    WHERE tt.class_id = :cid 
    ORDER BY FIELD(tt.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), tt.start_time ASC");
$tt_stmt->execute(['cid' => $class_id]);
$timetable_slots = $tt_stmt->fetchAll();

// Group slots by Day
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$schedule_by_day = [];
foreach ($days as $d) {
    $schedule_by_day[$d] = [];
}
foreach ($timetable_slots as $slot) {
    $schedule_by_day[$slot['day_of_week']][] = $slot;
}
?>

<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Class Timetable Management</h1>
            <p class="text-sm text-slate-500 mt-1">Configure weekly period schedules, assigned subjects, teachers, and classroom locations.</p>
        </div>
        <div>
            <a href="<?= base_url('admin/timetable/add.php?class_id=' . $class_id) ?>" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Timetable Slot</span>
            </a>
        </div>
    </div>

    <!-- Class Selector Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <form method="GET" action="index.php" class="flex items-center space-x-4">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-700">Select Class:</label>
            <select name="class_id" onchange="this.form.submit()" class="py-2 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $class_id ? 'selected' : '' ?>><?= e($c['class_name']) ?> (Sec <?= e($c['section']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </form>
        <span class="text-xs text-slate-500 font-medium">Total Weekly Slots: <strong class="text-slate-900"><?= count($timetable_slots) ?></strong></span>
    </div>

    <!-- Weekly Timetable Schedule Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($days as $day): ?>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                    <span class="font-extrabold text-sm tracking-wide"><?= $day ?></span>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-800 text-slate-300"><?= count($schedule_by_day[$day]) ?> Periods</span>
                </div>

                <div class="p-4 flex-1 space-y-3">
                    <?php if (empty($schedule_by_day[$day])): ?>
                        <div class="p-6 text-center text-xs text-slate-400 font-medium">No periods scheduled for <?= $day ?>.</div>
                    <?php else: ?>
                        <?php foreach ($schedule_by_day[$day] as $slot): ?>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5 relative group hover:border-blue-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100"><?= e($slot['period_name']) ?></span>
                                    <span class="text-[11px] font-mono font-semibold text-slate-500">
                                        <?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <div>
                                        <span class="font-extrabold text-slate-900 text-sm block leading-tight"><?= e($slot['subject_name'] ?? 'Subject') ?></span>
                                        <span class="text-xs text-slate-500 font-medium block mt-0.5"><i class="fa-solid fa-user-tie text-[10px] mr-1"></i><?= e($slot['teacher_name'] ?? 'Teacher Unassigned') ?></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-slate-600 bg-slate-200/80 px-2 py-0.5 rounded font-mono font-bold block mb-1"><?= e($slot['room_number'] ?? 'Room 101') ?></span>
                                        <a href="<?= base_url('admin/timetable/index.php?action=delete&id=' . $slot['id'] . '&class_id=' . $class_id . '&csrf=' . csrf_token()) ?>" onclick="return confirm('Remove period?')" class="text-slate-400 hover:text-rose-600 transition-colors" title="Delete Slot">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
