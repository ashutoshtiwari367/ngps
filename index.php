<?php
require_once __DIR__ . '/includes/auth.php';

$is_logged = is_logged_in();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Next Generation Public School - School Management System</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="font-sans antialiased text-slate-800 bg-white selection:bg-blue-600 selection:text-white">

    <!-- Announcement Bar -->
    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white text-xs font-semibold py-2.5 px-4 text-center flex items-center justify-center space-x-2">
        <span class="bg-blue-500/30 text-blue-200 px-2.5 py-0.5 rounded-full text-[10px] uppercase font-bold tracking-wider">NGPS ERP</span>
        <span>Centralized Digital School Management System</span>
        <a href="login.php" class="underline hover:text-blue-200 ml-1 font-bold">Sign In to Portal &rarr;</a>
    </div>

    <!-- Navigation Bar -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- School Brand Logo -->
            <a href="index.php" class="flex items-center space-x-3 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-700 via-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20 group-hover:scale-105 transition-transform duration-300">
                    <i class="fa-solid fa-graduation-cap text-xl"></i>
                </div>
                <div>
                    <span class="font-black text-xl text-slate-900 tracking-tight block leading-none">Next Generation Public School</span>
                    <span class="text-[10px] font-bold text-blue-600 tracking-widest uppercase">School Management System</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold text-slate-700">
                <a href="#home" class="hover:text-blue-600 transition-colors">Home</a>
                <a href="#about" class="hover:text-blue-600 transition-colors">About</a>
                <a href="#academics" class="hover:text-blue-600 transition-colors">Academics</a>
                <a href="#facilities" class="hover:text-blue-600 transition-colors">Facilities</a>
                <a href="#contact" class="hover:text-blue-600 transition-colors">Contact</a>
            </nav>

            <!-- Login System CTA Button -->
            <div class="flex items-center space-x-3">
                <?php if ($is_logged): ?>
                    <?php if (is_admin()): ?>
                        <a href="<?= base_url('admin/dashboard.php') ?>" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center space-x-2">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Admin Dashboard</span>
                        </a>
                    <?php elseif (is_teacher()): ?>
                        <a href="<?= base_url('teacher/dashboard.php') ?>" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span>Teacher Dashboard</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('student/dashboard.php') ?>" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2">
                            <i class="fa-solid fa-user-graduate"></i>
                            <span>Student Portal</span>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= base_url('login.php') ?>" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2">
                        <i class="fa-solid fa-lock text-xs text-blue-400"></i>
                        <span>Portal Sign In</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>


    <!-- School Hero Section -->
    <section id="home" class="relative pt-12 pb-20 lg:pt-16 lg:pb-28 bg-slate-900 overflow-hidden">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Hero Content -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold">
                        <i class="fa-solid fa-award text-amber-400"></i>
                        <span>Affiliated with CBSE &bull; Excellence in Education Since 2010</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight">
                        Empowering Young Minds for <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-300 to-purple-400">Global Leadership</span>
                    </h1>

                    <p class="text-slate-300 text-base sm:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                        EduPulse International School offers a holistic learning environment combining academic rigor, modern STEM facilities, character building, and creative arts.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                        <a href="#admissions" class="w-full sm:w-auto px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center space-x-2">
                            <span>Apply for Admission</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                        <a href="#contact" class="w-full sm:w-auto px-8 py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-sm rounded-xl transition-all flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-phone text-blue-400"></i>
                            <span>Schedule Campus Tour</span>
                        </a>
                    </div>

                    <!-- Statistics Bar -->
                    <div class="pt-8 border-t border-slate-800 grid grid-cols-3 gap-6 text-center lg:text-left">
                        <div>
                            <span class="text-2xl sm:text-3xl font-black text-white font-mono block">1,500+</span>
                            <span class="text-xs text-slate-400 font-medium">Enrolled Students</span>
                        </div>
                        <div>
                            <span class="text-2xl sm:text-3xl font-black text-blue-400 font-mono block">100%</span>
                            <span class="text-xs text-slate-400 font-medium">Board Exam Success</span>
                        </div>
                        <div>
                            <span class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono block">60+</span>
                            <span class="text-xs text-slate-400 font-medium">Qualified Teachers</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Campus Photo Showcase -->
                <div class="lg:col-span-5 relative">
                    <div class="rounded-3xl p-3 bg-slate-800/80 border border-slate-700/80 shadow-2xl overflow-hidden group">
                        <img src="<?= base_url('assets/uploads/school_hero_banner.png') ?>" alt="Campus Building" class="w-full h-[420px] object-cover rounded-2xl group-hover:scale-105 transition-transform duration-700">
                        
                        <div class="absolute bottom-6 left-6 right-6 p-4 rounded-2xl bg-slate-950/90 backdrop-blur-xl border border-slate-800 text-white flex items-center justify-between shadow-xl">
                            <div>
                                <h4 class="text-xs font-bold">State-of-the-Art Green Campus</h4>
                                <p class="text-[11px] text-slate-400">New Delhi Academic Enclave</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">ECO-CAMPUS</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Principal's Message Section -->
    <section id="about" class="py-20 bg-slate-50 border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <div class="lg:col-span-5">
                    <div class="relative rounded-3xl p-3 bg-white border border-slate-200 shadow-xl">
                        <img src="<?= base_url('assets/uploads/school_students_banner.png') ?>" alt="Students Learning" class="w-full h-[380px] object-cover rounded-2xl">
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-5">
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">Welcome Message</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Message from Our Principal</h2>
                    <blockquote class="text-slate-600 text-base leading-relaxed italic border-l-4 border-blue-600 pl-4 py-1">
                        "At EduPulse International School, we believe education is not merely the accumulation of knowledge, but the awakening of wisdom, character, and empathy. Our mission is to prepare students to lead with integrity in an ever-evolving global society."
                    </blockquote>
                    <div>
                        <span class="font-extrabold text-slate-900 block text-base">Dr. Sunita Deshmukh</span>
                        <span class="text-xs text-blue-600 font-semibold">Principal & Academic Director (M.A., Ph.D. Education)</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Academic Programs -->
    <section id="academics" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">Academic Excellence</span>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Our Academic Wings</h2>
                <p class="text-slate-500 text-sm">Comprehensive curriculum tailored for foundational growth, critical inquiry, and competitive readiness.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Wing 1 -->
                <div class="bg-slate-50 p-8 rounded-3xl border border-slate-200/80 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-xl mb-6 shadow-md shadow-blue-500/20">
                        <i class="fa-solid fa-shapes"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Primary Wing (Grades 1-5)</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">Focus on experiential learning, foundational numeracy, linguistic literacy, and interactive play-based learning modules.</p>
                    <span class="text-xs font-bold text-blue-600 flex items-center space-x-1"><span>CBSE Primary Curriculum</span> &rarr;</span>
                </div>

                <!-- Wing 2 -->
                <div class="bg-slate-50 p-8 rounded-3xl border border-slate-200/80 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl mb-6 shadow-md shadow-indigo-500/20">
                        <i class="fa-solid fa-atom"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Middle Wing (Grades 6-8)</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">Focus on scientific inquiry, analytical problem solving, social sciences, digital literacy, and STEM project work.</p>
                    <span class="text-xs font-bold text-indigo-600 flex items-center space-x-1"><span>STEM & Robotics Integrated</span> &rarr;</span>
                </div>

                <!-- Wing 3 -->
                <div class="bg-slate-50 p-8 rounded-3xl border border-slate-200/80 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl mb-6 shadow-md shadow-purple-500/20">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Senior Secondary (Grades 9-12)</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">Specialized Science (PCM/PCB), Commerce, and Humanities streams with dedicated competitive exam coaching (JEE/NEET/CUET).</p>
                    <span class="text-xs font-bold text-purple-600 flex items-center space-x-1"><span>Stream Specialization</span> &rarr;</span>
                </div>

            </div>

        </div>
    </section>

    <!-- Campus Infrastructure -->
    <section id="facilities" class="py-20 bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-400 bg-blue-500/10 px-3 py-1 rounded-full border border-blue-500/20">World-Class Campus</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Facilities & Infrastructure</h2>
                <p class="text-slate-400 text-sm">Empowering students with state-of-the-art facilities designed for holistic growth.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-desktop"></i>
                    </div>
                    <h4 class="font-bold text-base text-white">Smart IT Labs</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">High-speed computer labs equipped with coding software and digital learning tools.</p>
                </div>

                <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-flask"></i>
                    </div>
                    <h4 class="font-bold text-base text-white">Composite Science Labs</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Fully equipped Physics, Chemistry, and Biology laboratories adhering to safety standards.</p>
                </div>

                <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-600/20 text-purple-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-volleyball"></i>
                    </div>
                    <h4 class="font-bold text-base text-white">Sports Complex</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Basketball courts, football field, cricket turf, and indoor badminton courts.</p>
                </div>

                <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-600/20 text-amber-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-bus"></i>
                    </div>
                    <h4 class="font-bold text-base text-white">GPS Bus Transport</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Fleet of air-conditioned school buses with live GPS tracking and CCTV security.</p>
                </div>

            </div>

        </div>
    </section>

    <!-- Admissions Banner CTA -->
    <section id="admissions" class="py-16 bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 text-white text-center">
        <div class="max-w-4xl mx-auto px-4 space-y-4">
            <h2 class="text-3xl font-extrabold tracking-tight">Admissions Open for Session 2026-2027</h2>
            <p class="text-blue-100 text-sm max-w-xl mx-auto">Join the EduPulse family and give your child the foundation for a bright, successful future.</p>
            <div class="pt-3">
                <a href="#contact" class="px-8 py-3.5 bg-white text-blue-900 font-extrabold text-sm rounded-xl shadow-xl hover:bg-slate-100 transition-all inline-block">
                    Contact Admissions Office
                </a>
            </div>
        </div>
    </section>

    <!-- Contact & Location Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                
                <!-- Contact Details -->
                <div class="space-y-6">
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">Get In Touch</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Campus Location & Inquiries</h2>
                    <p class="text-slate-500 text-sm leading-relaxed">Our admissions office is open Monday through Saturday, from 8:30 AM to 4:00 PM.</p>

                    <div class="space-y-4 text-sm font-medium text-slate-700">
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <span class="font-bold block text-slate-900">Campus Address</span>
                                <span>12 Academic Enclave, Sector 15, New Delhi - 110001</span>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <span class="font-bold block text-slate-900">Phone Contacts</span>
                                <span>Admissions: +91 11 2345 6789 | General: +91 11 9876 5432</span>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <span class="font-bold block text-slate-900">Email Address</span>
                                <span>admissions@edupulse-school.com</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inquiry Form -->
                <div class="bg-slate-50 p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                    <h3 class="text-lg font-bold text-slate-900">Online Admission Inquiry</h3>
                    <form onsubmit="alert('Thank you for your inquiry! Our admissions officer will contact you shortly.'); return false;" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Parent Name *</label>
                            <input type="text" required placeholder="Enter full name" class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Mobile *</label>
                                <input type="text" required placeholder="Mobile number" class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Grade Seeking *</label>
                                <select required class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                                    <option value="">-- Select Grade --</option>
                                    <option value="Primary">Primary (Grades 1-5)</option>
                                    <option value="Middle">Middle (Grades 6-8)</option>
                                    <option value="Senior">Senior (Grades 9-12)</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Message / Question</label>
                            <textarea rows="2" placeholder="Your inquiry message..." class="w-full p-3 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"></textarea>
                        </div>
                        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition-all">
                            Submit Inquiry
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-12 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <span class="font-extrabold text-white text-lg tracking-tight">EduPulse School</span>
                </div>
                <p class="text-xs leading-relaxed text-slate-500">Affiliated with CBSE. Committed to nurturing intellectual curiosity and moral character.</p>
            </div>

            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4">Quick Links</h4>
                <ul class="space-y-2 text-xs font-medium">
                    <li><a href="#home" class="hover:text-white transition-colors">Home</a></li>
                    <li><a href="#about" class="hover:text-white transition-colors">About School</a></li>
                    <li><a href="#academics" class="hover:text-white transition-colors">Academics</a></li>
                    <li><a href="#facilities" class="hover:text-white transition-colors">Campus Life</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4">Portals</h4>
                <ul class="space-y-2 text-xs font-medium">
                    <li><a href="<?= base_url('login.php') ?>" class="text-blue-400 hover:underline font-bold flex items-center space-x-1"><i class="fa-solid fa-lock text-[10px]"></i><span>Staff / Admin Portal Login</span></a></li>
                    <li><span class="text-slate-500">Parent Portal (Coming Soon)</span></li>
                    <li><span class="text-slate-500">Student Portal (Coming Soon)</span></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4">School Office</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    12 Academic Enclave, Sector 15, New Delhi &bull; Ph: +91 11 2345 6789
                </p>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 mt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500">
            <p>&copy; <?= date('Y') ?> EduPulse International School. All rights reserved.</p>
            <p class="mt-2 sm:mt-0"><a href="<?= base_url('login.php') ?>" class="hover:text-slate-300">Staff Portal Login &rarr;</a></p>
        </div>
    </footer>

</body>
</html>
