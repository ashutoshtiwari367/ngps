<?php
require_once __DIR__ . '/../../includes/auth.php';
require_accountant_or_admin();

$errors = [];
$distinct_classes = get_distinct_class_names($pdo);

// Auto-generate next admission number
$max_id_stmt = $pdo->query("SELECT MAX(id) FROM students");
$next_id = ($max_id_stmt->fetchColumn() ?: 0) + 1;
$auto_adm_no = "STU-2026-" . str_pad($next_id, 3, '0', STR_PAD_LEFT);

// Handle POST request BEFORE HTML header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Invalid security token.";
    } else {
        // 1. Student Personal & Academic Data
        $admission_no   = trim($_POST['admission_no'] ?? '');
        $class_name     = trim($_POST['class_name'] ?? '');
        $section        = strtoupper(trim($_POST['section'] ?? 'A'));
        $first_name     = trim($_POST['first_name'] ?? '');
        $last_name      = trim($_POST['last_name'] ?? '');
        $father_name    = trim($_POST['father_name'] ?? '');
        $mother_name    = trim($_POST['mother_name'] ?? '');
        $dob            = trim($_POST['dob'] ?? '');
        $gender         = trim($_POST['gender'] ?? 'Male');
        $mobile         = trim($_POST['mobile'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $address        = trim($_POST['address'] ?? '');
        $admission_date = trim($_POST['admission_date'] ?? date('Y-m-d'));
        $status         = 'Active';

        // 2. Exact 11 Fee Heads from User Requirements
        $registration_fee = floatval($_POST['registration_fee'] ?? 0);
        $admission_fee    = floatval($_POST['admission_fee'] ?? 0);
        $annual_fee       = floatval($_POST['annual_fee'] ?? 0);
        $tuition_fee      = floatval($_POST['tuition_fee'] ?? 0);
        $library_fee      = floatval($_POST['library_fee'] ?? 0);
        $computer_fee     = floatval($_POST['computer_fee'] ?? 0);
        $activity_fee     = floatval($_POST['activity_fee'] ?? 0);
        $magazine_fee     = floatval($_POST['magazine_fee'] ?? 0);
        $exam_fee         = floatval($_POST['exam_fee'] ?? 0);
        $penalty_fee      = floatval($_POST['penalty_fee'] ?? 0);
        $other_fee        = floatval($_POST['other_fee'] ?? 0);
        $discount_amount  = floatval($_POST['discount_amount'] ?? 0);

        // Calculated Total Fee
        $gross_total  = $registration_fee + $admission_fee + $annual_fee + $tuition_fee + 
                        $library_fee + $computer_fee + $activity_fee + $magazine_fee + 
                        $exam_fee + $penalty_fee + $other_fee;
        $total_amount = max(0, $gross_total - $discount_amount);

        // 3. Payment Collection Data
        $paid_amount      = floatval($_POST['paid_amount'] ?? $total_amount);
        $remaining_amount = max(0, $total_amount - $paid_amount);
        $payment_date     = trim($_POST['payment_date'] ?? date('Y-m-d'));
        $payment_method   = trim($_POST['payment_method'] ?? 'Cash');
        $payment_status   = trim($_POST['payment_status'] ?? ($remaining_amount <= 0 ? 'Paid' : ($paid_amount > 0 ? 'Partial' : 'Pending')));
        $remarks          = trim($_POST['remarks'] ?? 'New Admission Fee Cleared');

        // Validation
        if (empty($admission_no)) $errors[] = "Admission number is required.";
        if (empty($first_name)) $errors[] = "Student first name is required.";
        if (empty($last_name)) $errors[] = "Student last name is required.";
        if (empty($class_name)) $errors[] = "Please select an assigned class.";
        if (empty($mobile)) $errors[] = "Mobile number is required.";
        if ($total_amount <= 0) $errors[] = "Total fee amount must be greater than zero.";

        // Find or create class_id matching class_name and section
        $class_id = find_or_create_class_id($class_name, $section, $pdo);

        // Handle Photo Upload
        $photo_name = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_orig = $_FILES['photo']['name'];
            $file_ext = strtolower(pathinfo($file_orig, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                $photo_name = "stu_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
                $upload_dir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                move_uploaded_file($file_tmp, $upload_dir . $photo_name);
            } else {
                $errors[] = "Invalid photo format. Only JPG, PNG, and WEBP images are allowed.";
            }
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Step A: Insert Student Profile (password defaults to student123)
                $default_student_pwd = password_hash('student123', PASSWORD_BCRYPT);
                $stu_stmt = $pdo->prepare("INSERT INTO students 
                    (admission_no, password, first_name, last_name, father_name, mother_name, dob, gender, class_id, section, mobile, email, address, admission_date, photo, status) 
                    VALUES 
                    (:admission_no, :password, :first_name, :last_name, :father_name, :mother_name, :dob, :gender, :class_id, :section, :mobile, :email, :address, :admission_date, :photo, :status)");
                
                $stu_stmt->execute([
                    'admission_no'   => $admission_no,
                    'password'       => $default_student_pwd,
                    'first_name'     => $first_name,
                    'last_name'      => $last_name,
                    'father_name'    => $father_name,
                    'mother_name'    => $mother_name,
                    'dob'            => $dob ?: '2012-01-01',
                    'gender'         => $gender,
                    'class_id'       => $class_id,
                    'section'        => $section,
                    'mobile'         => $mobile,
                    'email'          => $email,
                    'address'        => $address,
                    'admission_date' => $admission_date,
                    'photo'          => $photo_name,
                    'status'         => $status
                ]);

                $student_id = $pdo->lastInsertId();

                // Step B: Insert Admission Fee Voucher with All 11 Heads
                $fee_stmt = $pdo->prepare("INSERT INTO fees 
                    (student_id, class_id, registration_fee, admission_fee, annual_fee, tuition_fee, library_fee, computer_fee, activity_fee, magazine_fee, exam_fee, penalty_fee, other_fee, discount_amount, fee_type, total_amount, paid_amount, remaining_amount, paid_months, payment_date, payment_method, payment_status, remarks) 
                    VALUES 
                    (:student_id, :class_id, :registration_fee, :admission_fee, :annual_fee, :tuition_fee, :library_fee, :computer_fee, :activity_fee, :magazine_fee, :exam_fee, :penalty_fee, :other_fee, :discount_amount, :fee_type, :total_amount, :paid_amount, :remaining_amount, :paid_months, :payment_date, :payment_method, :payment_status, :remarks)");
                
                $fee_stmt->execute([
                    'student_id'       => $student_id,
                    'class_id'         => $class_id,
                    'registration_fee' => $registration_fee,
                    'admission_fee'    => $admission_fee,
                    'annual_fee'       => $annual_fee,
                    'tuition_fee'      => $tuition_fee,
                    'library_fee'      => $library_fee,
                    'computer_fee'     => $computer_fee,
                    'activity_fee'     => $activity_fee,
                    'magazine_fee'     => $magazine_fee,
                    'exam_fee'         => $exam_fee,
                    'penalty_fee'      => $penalty_fee,
                    'other_fee'        => $other_fee,
                    'discount_amount'  => $discount_amount,
                    'fee_type'         => 'Admission & Registration',
                    'total_amount'     => $total_amount,
                    'paid_amount'      => $paid_amount,
                    'remaining_amount' => $remaining_amount,
                    'paid_months'      => 'Admission / 1st Term',
                    'payment_date'     => $payment_date,
                    'payment_method'   => $payment_method,
                    'payment_status'   => $payment_status,
                    'remarks'          => $remarks
                ]);

                $fee_id = $pdo->lastInsertId();

                $pdo->commit();

                set_flash('success', 'Student admitted successfully and official admission slip generated!');
                header('Location: ' . base_url('admin/accounts/admission_receipt.php?id=' . $fee_id));
                exit();

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = "Transaction failed: " . $e->getMessage();
            }
        }
    }
}

$page_title = 'New Student Admission & Fee Entry';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-5xl mx-auto pb-12">

    <!-- Top Navigation & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center space-x-4">
            <img src="<?= get_school_logo() ?>" alt="Next Generation Public School" class="w-16 h-16 object-contain rounded-full border border-amber-300 p-0.5 shadow-sm shrink-0 bg-white">
            <div>
                <div class="inline-flex items-center space-x-2 text-xs font-bold text-amber-700 uppercase tracking-wider mb-0.5">
                    <span>Next Generation Public School &bull; विद्या ददाति विनयं</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">New Student Admission Form</h1>
                <p class="text-xs text-slate-500 mt-0.5">Enroll student profile and calculate itemized fee breakdown.</p>
            </div>
        </div>
        <div class="flex items-center space-x-2 self-start sm:self-auto">
            <a href="<?= base_url('admin/accounts/index.php') ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Accounts Dashboard</span>
            </a>
            <a href="<?= base_url('admin/students/index.php') ?>" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition-all flex items-center space-x-1.5">
                <i class="fa-solid fa-users"></i>
                <span>Student List</span>
            </a>
        </div>
    </div>

    <!-- Main Admission Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden p-6 sm:p-10">

        <?php if (!empty($errors)): ?>
            <div class="mb-8 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                <?php foreach ($errors as $err): ?>
                    <p class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                        <span class="font-bold"><?= e($err) ?></span>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="admission.php" method="POST" enctype="multipart/form-data" class="space-y-8">
            <?= csrf_field() ?>

            <!-- SECTION 1: ACADEMIC & ENROLLMENT (DISTINCT CLASSES + SECTION) -->
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-amber-700 mb-4 pb-2 border-b border-amber-100 flex items-center space-x-2">
                    <i class="fa-solid fa-school text-amber-600"></i>
                    <span>1. Academic Grade & Section Enrollment</span>
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5 text-xs">
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Admission No *</label>
                        <input type="text" name="admission_no" required value="<?= e($_POST['admission_no'] ?? $auto_adm_no) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Class Assigned *</label>
                        <select name="class_name" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="">-- Select Class (PG to 12th) --</option>
                            <?php foreach ($distinct_classes as $dc): ?>
                                <option value="<?= e($dc) ?>" <?= (($_POST['class_name'] ?? '') === $dc) ? 'selected' : '' ?>>
                                    <?= e($dc) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Section Assigned *</label>
                        <select name="section" id="section" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <?php foreach (['A', 'B', 'C', 'D', 'E'] as $sec): ?>
                                <option value="<?= $sec ?>" <?= (($_POST['section'] ?? 'A') === $sec) ? 'selected' : '' ?>>
                                    Section <?= $sec ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Admission Date *</label>
                        <input type="date" name="admission_date" value="<?= e($_POST['admission_date'] ?? date('Y-m-d')) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: STUDENT & PARENT PERSONAL DETAILS -->
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-amber-700 mb-4 pb-2 border-b border-amber-100 flex items-center space-x-2">
                    <i class="fa-solid fa-user-graduate text-amber-600"></i>
                    <span>2. Student Profile & Parent Details</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-xs">
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">First Name *</label>
                        <input type="text" name="first_name" required value="<?= e($_POST['first_name'] ?? '') ?>" placeholder="e.g. Aarav"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Last Name *</label>
                        <input type="text" name="last_name" required value="<?= e($_POST['last_name'] ?? '') ?>" placeholder="e.g. Sharma"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Gender *</label>
                        <select name="gender" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Date of Birth</label>
                        <input type="date" name="dob" value="<?= e($_POST['dob'] ?? '') ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Father / Guardian Name *</label>
                        <input type="text" name="father_name" required value="<?= e($_POST['father_name'] ?? '') ?>" placeholder="Father's full name"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mother's Name</label>
                        <input type="text" name="mother_name" value="<?= e($_POST['mother_name'] ?? '') ?>" placeholder="Mother's full name"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Primary Mobile Contact *</label>
                        <input type="text" name="mobile" required value="<?= e($_POST['mobile'] ?? '') ?>" placeholder="10-digit mobile number"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="parent.student@gmail.com"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Student Photo</label>
                        <input type="file" name="photo" accept="image/*"
                            class="w-full py-1.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Residential Address</label>
                        <textarea name="address" rows="2" placeholder="Full residential postal address..."
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all"><?= e($_POST['address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: ITEMIZED FEE BREAKDOWN (ALL 11 HEADS FROM USER LIST) -->
            <div class="bg-amber-50/50 p-6 rounded-2xl border border-amber-200">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-amber-200">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-900 flex items-center space-x-2">
                        <i class="fa-solid fa-calculator text-amber-600"></i>
                        <span>3. Admission Fee Particulars (11 Itemized Heads)</span>
                    </h3>
                    <span class="text-[11px] font-bold text-amber-700">Enter applicable fees for this student</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <!-- 1. Registration Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">1. Registration Fee (₹)</label>
                        <input type="number" step="0.01" id="registration_fee" name="registration_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['registration_fee'] ?? '500.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 2. Admission Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">2. Admission Fee (₹)</label>
                        <input type="number" step="0.01" id="admission_fee" name="admission_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['admission_fee'] ?? '2500.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 3. Annual Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">3. Annual Fee (₹)</label>
                        <input type="number" step="0.01" id="annual_fee" name="annual_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['annual_fee'] ?? '1500.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 4. Tuition Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">4. Tuition Fee (₹)</label>
                        <input type="number" step="0.01" id="tuition_fee" name="tuition_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['tuition_fee'] ?? '2000.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 5. Library Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">5. Library Fee (₹)</label>
                        <input type="number" step="0.01" id="library_fee" name="library_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['library_fee'] ?? '500.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 6. Computer Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">6. Computer Fee (₹)</label>
                        <input type="number" step="0.01" id="computer_fee" name="computer_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['computer_fee'] ?? '500.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 7. Activity Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">7. Activity Fee (₹)</label>
                        <input type="number" step="0.01" id="activity_fee" name="activity_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['activity_fee'] ?? '500.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 8. Magazine Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">8. Magazine Fee (₹)</label>
                        <input type="number" step="0.01" id="magazine_fee" name="magazine_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['magazine_fee'] ?? '200.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 9. Examination Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">9. Examination Fee (₹)</label>
                        <input type="number" step="0.01" id="exam_fee" name="exam_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['exam_fee'] ?? '500.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 10. Penalty Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">10. Penalty Fee (Late/Other) (₹)</label>
                        <input type="number" step="0.01" id="penalty_fee" name="penalty_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['penalty_fee'] ?? '0.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- 11. Other Fee -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">11. Other Fee (₹)</label>
                        <input type="number" step="0.01" id="other_fee" name="other_fee" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['other_fee'] ?? '0.00') ?>" 
                            class="fee-calc-input w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <!-- Discount / Scholarship -->
                    <div>
                        <label class="block font-bold text-rose-700 mb-1">Discount / Scholarship (-) (₹)</label>
                        <input type="number" step="0.01" id="discount_amount" name="discount_amount" onkeyup="calculateFeeTotal()" onchange="calculateFeeTotal()" value="<?= e($_POST['discount_amount'] ?? '0.00') ?>" 
                            class="w-full py-2 px-3 bg-rose-50 border border-rose-300 rounded-xl text-sm font-mono font-bold text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>
                </div>

                <!-- Grand Total Display Box -->
                <div class="mt-4 pt-3 border-t border-amber-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <span class="text-xs font-bold text-amber-900">
                        Net Calculated Fee = (Sum of All 11 Fee Heads) &minus; (Discount)
                    </span>
                    <div class="bg-white px-5 py-2.5 rounded-xl border-2 border-amber-500 flex items-center space-x-3 shadow-sm">
                        <span class="text-xs font-black uppercase text-slate-600">Net Admission Total:</span>
                        <span id="grand_total_display" class="text-2xl font-black text-amber-700 font-mono">₹8,700.00</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: PAYMENT COLLECTION & VOUCHER SETTINGS -->
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-amber-700 mb-4 pb-2 border-b border-amber-100 flex items-center space-x-2">
                    <i class="fa-solid fa-money-bill-wave text-amber-600"></i>
                    <span>4. Payment Collection & Voucher Settlement</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-5 text-xs">
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Net Payable Fee (₹) *</label>
                        <input type="number" step="0.01" id="total_amount" name="total_amount" readonly value="8700.00" 
                            class="w-full py-2.5 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-900 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Amount Paid Now (₹) *</label>
                        <input type="number" step="0.01" id="paid_amount" name="paid_amount" required onkeyup="calculateBalance()" onchange="calculateBalance()" value="8700.00" 
                            class="w-full py-2.5 px-3 bg-white border-2 border-emerald-500 rounded-xl text-sm font-mono font-bold text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Remaining Balance (₹)</label>
                        <input type="number" step="0.01" id="remaining_amount" name="remaining_amount" readonly value="0.00" 
                            class="w-full py-2.5 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono font-bold text-rose-600 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Payment Status</label>
                        <select id="payment_status" name="payment_status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="Paid" selected>Paid</option>
                            <option value="Partial">Partial</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Payment Method *</label>
                        <select name="payment_method" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                            <option value="Cash" selected>Cash</option>
                            <option value="UPI">UPI / Google Pay / PhonePe / Paytm</option>
                            <option value="Card">Credit / Debit Card</option>
                            <option value="Bank Transfer">Bank Transfer / NEFT</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Payment Date *</label>
                        <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1.5">Transaction Remarks / Note</label>
                        <input type="text" name="remarks" value="New Admission Fee Collection" placeholder="e.g. Transaction Ref No, receipt note..."
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition-all">
                    </div>
                </div>
            </div>

            <!-- Submit Button Footer -->
            <div class="pt-6 border-t border-slate-200 flex items-center justify-end space-x-3">
                <a href="<?= base_url('admin/accounts/index.php') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-7 py-3 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-amber-600/30 transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Complete Admission & Generate Slip</span>
                </button>
            </div>
        </form>

    </div>

</div>

<script>
function calculateFeeTotal() {
    const reg = parseFloat(document.getElementById('registration_fee').value) || 0;
    const adm = parseFloat(document.getElementById('admission_fee').value) || 0;
    const ann = parseFloat(document.getElementById('annual_fee').value) || 0;
    const tui = parseFloat(document.getElementById('tuition_fee').value) || 0;
    const lib = parseFloat(document.getElementById('library_fee').value) || 0;
    const cmp = parseFloat(document.getElementById('computer_fee').value) || 0;
    const act = parseFloat(document.getElementById('activity_fee').value) || 0;
    const mag = parseFloat(document.getElementById('magazine_fee').value) || 0;
    const exm = parseFloat(document.getElementById('exam_fee').value) || 0;
    const pen = parseFloat(document.getElementById('penalty_fee').value) || 0;
    const oth = parseFloat(document.getElementById('other_fee').value) || 0;
    const dsc = parseFloat(document.getElementById('discount_amount').value) || 0;

    const gross = reg + adm + ann + tui + lib + cmp + act + mag + exm + pen + oth;
    const net = Math.max(0, gross - dsc);

    document.getElementById('total_amount').value = net.toFixed(2);
    document.getElementById('grand_total_display').innerText = '₹' + net.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    // Auto-update paid amount if user hasn't explicitly entered a different amount
    const paidInput = document.getElementById('paid_amount');
    if (!paidInput.dataset.manualEdited) {
        paidInput.value = net.toFixed(2);
    }

    calculateBalance();
}

function calculateBalance() {
    const net = parseFloat(document.getElementById('total_amount').value) || 0;
    const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    const rem = Math.max(0, net - paid);

    document.getElementById('remaining_amount').value = rem.toFixed(2);

    const statusSelect = document.getElementById('payment_status');
    if (rem <= 0) {
        statusSelect.value = 'Paid';
    } else if (paid > 0) {
        statusSelect.value = 'Partial';
    } else {
        statusSelect.value = 'Pending';
    }
}

// Track if user manually edits paid amount
document.getElementById('paid_amount').addEventListener('input', function() {
    this.dataset.manualEdited = "true";
});

// Run initial calculation on page load
document.addEventListener("DOMContentLoaded", function() {
    calculateFeeTotal();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
