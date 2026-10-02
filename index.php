<?php
require_once __DIR__ . '/includes/auth.php';

$is_logged = is_logged_in();
$school_name = get_school_setting('school_name', 'Next Generation Public School');
$school_email = get_school_setting('school_email', 'contact@nextgenps.edu.in');
$school_phone = get_school_setting('school_phone', '+91 98765 43210');
$school_address = get_school_setting('school_address', 'Next Generation Campus, Knowledge Enclave, New Delhi - 110001');
$academic_session = get_school_setting('academic_session', '2026-2027');
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($school_name) ?> - AI-Powered Next Generation Education</title>
    
    <!-- Google Fonts: Space Grotesk & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Space Grotesk', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace']
                    },
                    colors: {
                        cyber: {
                            50: '#ecfeff',
                            100: '#cffafe',
                            200: '#a5f3fc',
                            300: '#67e8f9',
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                            700: '#0e7490',
                            800: '#155e75',
                            900: '#164e63',
                            950: '#083344'
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'glow': 'glow 3s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-12px)' },
                        },
                        glow: {
                            '0%': { filter: 'drop-shadow(0 0 15px rgba(6, 182, 212, 0.3))' },
                            '100%': { filter: 'drop-shadow(0 0 35px rgba(99, 102, 241, 0.6))' }
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #030712; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; border: 1px solid #334155; }
        ::-webkit-scrollbar-thumb:hover { background: #06b6d4; }
        
        /* Cyber Matrix Background Grid */
        .cyber-grid {
            background-size: 50px 50px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
        
        .cyber-dots {
            background-image: radial-gradient(rgba(6, 182, 212, 0.15) 1px, transparent 0);
            background-size: 24px 24px;
        }

        /* Glassmorphism Styles */
        .glass-card {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .glass-card-glow {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(6, 182, 212, 0.25);
            box-shadow: 0 0 30px rgba(6, 182, 212, 0.08);
        }

        .glass-card-glow:hover {
            border-color: rgba(6, 182, 212, 0.6);
            box-shadow: 0 0 35px rgba(6, 182, 212, 0.2);
        }
        
        /* Shimmering gradient border */
        .neon-border-box {
            position: relative;
            background: #0b1120;
            border-radius: 1.25rem;
        }
        .neon-border-box::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 1.35rem;
            background: linear-gradient(135deg, #06b6d4, #6366f1, #a855f7, #06b6d4);
            z-index: -1;
            opacity: 0.4;
            transition: opacity 0.3s ease;
        }
        .neon-border-box:hover::before {
            opacity: 0.9;
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-200 bg-[#030712] selection:bg-cyan-500 selection:text-black overflow-x-hidden">

    <!-- Top AI Status Beacon & Live Ticker -->
    <div class="bg-gradient-to-r from-slate-950 via-cyan-950/40 to-slate-950 border-b border-cyan-500/20 text-slate-300 text-xs py-2 px-4">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center space-x-2 font-mono text-[11px]">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
                </span>
                <span class="text-cyan-400 font-bold tracking-wider">AI SYSTEM ONLINE</span>
                <span class="text-slate-600">|</span>
                <span class="text-slate-300">Session <?= e($academic_session) ?> Admissions Active</span>
                <span class="hidden md:inline text-slate-500">• CBSE Affiliation No: 2130098</span>
            </div>

            <div class="flex items-center space-x-4 text-[11px]">
                <a href="#admission-inquiry" class="text-cyan-400 hover:text-cyan-300 font-semibold flex items-center space-x-1 transition-colors">
                    <i class="fa-solid fa-sparkles text-[10px]"></i>
                    <span>Apply Online</span>
                </a>
                <span class="text-slate-700">|</span>
                <a href="<?= base_url('login.php') ?>" class="text-slate-300 hover:text-white font-medium flex items-center space-x-1.5 transition-colors">
                    <i class="fa-solid fa-key text-[10px] text-cyan-400"></i>
                    <span>Portal Sign In</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-50 bg-[#030712]/90 backdrop-blur-xl border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- School Brand Logo -->
            <a href="index.php" class="flex items-center space-x-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 via-blue-600 to-indigo-600 p-[1px] transition-transform duration-300 group-hover:scale-105 shadow-[0_0_20px_rgba(6,182,212,0.35)]">
                    <div class="w-full h-full bg-[#070d1d] rounded-2xl flex items-center justify-center text-cyan-400">
                        <i class="fa-solid fa-atom text-2xl group-hover:rotate-180 transition-transform duration-700"></i>
                    </div>
                </div>
                <div>
                    <span class="font-display font-black text-lg sm:text-xl text-white tracking-tight block leading-tight group-hover:text-cyan-400 transition-colors">
                        <?= e($school_name) ?>
                    </span>
                    <span class="text-[10px] font-mono tracking-widest text-cyan-400 font-semibold uppercase flex items-center space-x-1">
                        <span>PIONEERING AI & ACADEMIC EXCELLENCE</span>
                    </span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden lg:flex items-center space-x-7 text-xs font-semibold uppercase tracking-wider text-slate-300">
                <a href="#home" class="hover:text-cyan-400 transition-colors">Home</a>
                <a href="#about" class="hover:text-cyan-400 transition-colors">About</a>
                <a href="#pillars" class="hover:text-cyan-400 transition-colors">AI Curriculum</a>
                <a href="#wings" class="hover:text-cyan-400 transition-colors">Academics</a>
                <a href="#facilities" class="hover:text-cyan-400 transition-colors">Innovation Labs</a>
                <a href="#calculator" class="hover:text-cyan-400 transition-colors">Eligibility</a>
                <a href="#admissions" class="hover:text-cyan-400 transition-colors">Admissions</a>
                <a href="#contact" class="hover:text-cyan-400 transition-colors">Contact</a>
            </nav>

            <!-- Actions & Portal CTA -->
            <div class="flex items-center space-x-3">
                <a href="#calculator" class="hidden sm:inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-cyan-950/60 border border-cyan-500/30 text-cyan-300 text-xs font-semibold hover:bg-cyan-900/40 hover:border-cyan-400 transition-all">
                    <i class="fa-solid fa-microchip text-[11px] text-cyan-400"></i>
                    <span>AI Assistant</span>
                </a>

                <?php if ($is_logged): ?>
                    <?php if (is_admin()): ?>
                        <a href="<?= base_url('admin/dashboard.php') ?>" class="px-4 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-black font-extrabold text-xs rounded-xl shadow-[0_0_20px_rgba(6,182,212,0.4)] transition-all flex items-center space-x-2">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Admin Portal</span>
                        </a>
                    <?php elseif (is_teacher()): ?>
                        <a href="<?= base_url('teacher/dashboard.php') ?>" class="px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-extrabold text-xs rounded-xl shadow-lg transition-all flex items-center space-x-2">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span>Teacher Portal</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('student/dashboard.php') ?>" class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-extrabold text-xs rounded-xl shadow-lg transition-all flex items-center space-x-2">
                            <i class="fa-solid fa-user-graduate"></i>
                            <span>Student Portal</span>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= base_url('login.php') ?>" class="px-4 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-slate-950 font-black text-xs rounded-xl shadow-[0_0_25px_rgba(6,182,212,0.35)] transition-all flex items-center space-x-2">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                        <span>Portal Sign In</span>
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileNav()" class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div id="mobile-nav" class="hidden lg:hidden bg-slate-950/95 border-b border-slate-800 px-6 py-6 space-y-4 font-semibold text-sm">
            <a href="#home" onclick="toggleMobileNav()" class="block text-slate-300 hover:text-cyan-400">Home</a>
            <a href="#about" onclick="toggleMobileNav()" class="block text-slate-300 hover:text-cyan-400">About NGPS</a>
            <a href="#pillars" onclick="toggleMobileNav()" class="block text-slate-300 hover:text-cyan-400">AI Curriculum</a>
            <a href="#wings" onclick="toggleMobileNav()" class="block text-slate-300 hover:text-cyan-400">Academic Wings</a>
            <a href="#facilities" onclick="toggleMobileNav()" class="block text-slate-300 hover:text-cyan-400">Innovation Labs</a>
            <a href="#calculator" onclick="toggleMobileNav()" class="block text-slate-300 hover:text-cyan-400">AI Eligibility Checker</a>
            <a href="#admissions" onclick="toggleMobileNav()" class="block text-slate-300 hover:text-cyan-400">Admissions 2026-27</a>
            <a href="#contact" onclick="toggleMobileNav()" class="block text-slate-300 hover:text-cyan-400">Contact Us</a>
            <div class="pt-4 border-t border-slate-800 flex flex-col gap-2">
                <a href="<?= base_url('login.php') ?>" class="w-full text-center py-2.5 bg-cyan-500 text-black font-extrabold rounded-xl">Portal Sign In</a>
            </div>
        </div>
    </header>


    <!-- ========================================================================= -->
    <!-- HERO SECTION: AI QUANTUM CORE (WITHOUT IMAGES - PURE HIGH-TECH CSS/SVG)   -->
    <!-- ========================================================================= -->
    <section id="home" class="relative pt-16 pb-24 lg:pt-24 lg:pb-32 cyber-grid overflow-hidden">
        
        <!-- Ambient Cyber Glow Orbs -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[500px] bg-gradient-to-tr from-cyan-600/20 via-blue-600/20 to-purple-600/20 blur-[130px] rounded-full pointer-events-none"></div>
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-cyan-500/10 blur-[100px] rounded-full pointer-events-none"></div>
        <div class="absolute top-1/2 -right-32 w-96 h-96 bg-purple-500/10 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                
                <!-- Left: Headline & AI School Identity -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    
                    <!-- Futuristic Status Tag -->
                    <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-cyan-950/70 border border-cyan-500/30 text-cyan-300 text-xs font-mono font-medium shadow-[0_0_20px_rgba(6,182,212,0.15)]">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                        <span>NEXT-GEN AI ECOSYSTEM &bull; CBSE AFFILIATED</span>
                    </div>

                    <!-- Main Hero Title -->
                    <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1]">
                        Where <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-sky-300 to-indigo-400">Artificial Intelligence</span> Sparks Human Genius
                    </h1>

                    <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        Welcome to <strong class="text-white font-semibold"><?= e($school_name) ?></strong> — India’s avant-garde K-12 institution integrating adaptive machine learning, robotics & IoT innovation, deep sciences, and character leadership into standard CBSE academics.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-3">
                        <a href="#admissions" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-cyan-400 via-cyan-500 to-blue-600 hover:from-cyan-300 hover:to-blue-500 text-black font-display font-extrabold text-sm rounded-2xl shadow-[0_0_30px_rgba(6,182,212,0.4)] transition-all transform hover:-translate-y-0.5 flex items-center justify-center space-x-3">
                            <span>Apply for Admission 2026-27</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                        <a href="#facilities" class="w-full sm:w-auto px-7 py-4 glass-card hover:bg-slate-800/80 text-slate-200 hover:text-white font-semibold text-sm rounded-2xl border border-slate-700/80 transition-all flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-microchip text-cyan-400"></i>
                            <span>Explore Innovation Labs</span>
                        </a>
                    </div>

                    <!-- Live Key Metrics Ribbon -->
                    <div class="pt-8 border-t border-slate-800/80 grid grid-cols-2 sm:grid-cols-4 gap-4 text-left">
                        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                            <div class="text-2xl font-black text-cyan-400 font-mono">100%</div>
                            <div class="text-[11px] text-slate-400 font-medium">CBSE Merit Record</div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                            <div class="text-2xl font-black text-indigo-400 font-mono">1,850+</div>
                            <div class="text-[11px] text-slate-400 font-medium">Future Scholars</div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                            <div class="text-2xl font-black text-purple-400 font-mono">25+</div>
                            <div class="text-[11px] text-slate-400 font-medium">Robotics & AI Labs</div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                            <div class="text-2xl font-black text-emerald-400 font-mono">1:1</div>
                            <div class="text-[11px] text-slate-400 font-medium">Adaptive AI Mentor</div>
                        </div>
                    </div>

                </div>

                <!-- Right: High-Tech "NGPS Neural Quantum Core" Visual Simulator (Pure CSS/SVG UI) -->
                <div class="lg:col-span-5 relative">
                    
                    <!-- Decorative Ambient Ring -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-cyan-500 to-indigo-600 rounded-3xl blur-xl opacity-30 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>

                    <!-- The Core Console -->
                    <div class="relative rounded-3xl bg-slate-900/90 border border-cyan-500/30 p-6 shadow-2xl backdrop-blur-2xl space-y-6">
                        
                        <!-- Top Console Header -->
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-3 h-3 rounded-full bg-rose-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                                <span class="font-mono text-xs text-slate-400 font-bold ml-2">NGPS-AI-CORE.SYS</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-mono text-[10px] font-bold">
                                    LIVE TELEMETRY
                                </span>
                            </div>
                        </div>

                        <!-- Neural Wave & Radar SVG Graphic -->
                        <div class="relative bg-slate-950/80 rounded-2xl p-5 border border-slate-800/80 overflow-hidden">
                            <div class="flex items-center justify-between mb-3 text-xs">
                                <span class="text-slate-400 font-mono">Cognitive Learning Stream</span>
                                <span class="text-cyan-400 font-mono font-bold">99.4% OPTIMAL</span>
                            </div>

                            <!-- Animated SVG Waves and Neural Graph -->
                            <svg class="w-full h-32" viewBox="0 0 400 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="cyberGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#06b6d4" stop-opacity="0.8" />
                                        <stop offset="50%" stop-color="#6366f1" stop-opacity="0.9" />
                                        <stop offset="100%" stop-color="#a855f7" stop-opacity="0.8" />
                                    </linearGradient>
                                    <linearGradient id="areaGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#06b6d4" stop-opacity="0.25" />
                                        <stop offset="100%" stop-color="#06b6d4" stop-opacity="0.0" />
                                    </linearGradient>
                                </defs>
                                <!-- Grid Lines -->
                                <line x1="0" y1="30" x2="400" y2="30" stroke="#1e293b" stroke-dasharray="3 3"/>
                                <line x1="0" y1="60" x2="400" y2="60" stroke="#1e293b" stroke-dasharray="3 3"/>
                                <line x1="0" y1="90" x2="400" y2="90" stroke="#1e293b" stroke-dasharray="3 3"/>
                                
                                <!-- Filled Wave Area -->
                                <path d="M0,80 Q50,20 100,55 T200,45 T300,30 T400,60 L400,120 L0,120 Z" fill="url(#areaGrad)"/>
                                
                                <!-- Main Wave Stroke -->
                                <path d="M0,80 Q50,20 100,55 T200,45 T300,30 T400,60" stroke="url(#cyberGrad)" stroke-width="3" fill="none"/>
                                
                                <!-- Neural Nodes -->
                                <circle cx="100" cy="55" r="4" fill="#06b6d4" class="animate-ping"/>
                                <circle cx="100" cy="55" r="3" fill="#ffffff"/>
                                <circle cx="200" cy="45" r="4" fill="#6366f1"/>
                                <circle cx="300" cy="30" r="5" fill="#a855f7" class="animate-pulse"/>
                                <circle cx="300" cy="30" r="3" fill="#ffffff"/>
                            </svg>

                            <div class="mt-2 grid grid-cols-3 gap-2 text-center font-mono text-[10px]">
                                <div class="bg-slate-900 p-1.5 rounded-lg border border-slate-800">
                                    <span class="text-slate-400 block">Classrooms</span>
                                    <span class="text-white font-bold">48 Connected</span>
                                </div>
                                <div class="bg-slate-900 p-1.5 rounded-lg border border-slate-800">
                                    <span class="text-slate-400 block">AI Tutors</span>
                                    <span class="text-cyan-400 font-bold">Synced</span>
                                </div>
                                <div class="bg-slate-900 p-1.5 rounded-lg border border-slate-800">
                                    <span class="text-slate-400 block">Bandwidth</span>
                                    <span class="text-emerald-400 font-bold">10 Gbps Fiber</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3 Realtime AI Modules Status -->
                        <div class="space-y-2.5">
                            
                            <!-- Module 1 -->
                            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 text-sm">
                                        <i class="fa-solid fa-robot"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-white">Robotics & IoT FabLab</div>
                                        <div class="text-[10px] text-slate-400">Humanoid & Drone Hardware Active</div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-cyan-950 text-cyan-300 border border-cyan-800">READY</span>
                            </div>

                            <!-- Module 2 -->
                            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-sm">
                                        <i class="fa-solid fa-brain"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-white">Adaptive Learning Engine</div>
                                        <div class="text-[10px] text-slate-400">CBSE Curriculum Personalization</div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-indigo-950 text-indigo-300 border border-indigo-800">ACTIVE</span>
                            </div>

                            <!-- Module 3 -->
                            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-sm">
                                        <i class="fa-solid fa-satellite-dish"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-white">Smart Bus GPS & CCTV Shield</div>
                                        <div class="text-[10px] text-slate-400">Live Student Safety Telematics</div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 border border-emerald-800">SECURE</span>
                            </div>

                        </div>

                        <!-- Console Footer Info -->
                        <div class="pt-2 flex items-center justify-between text-[11px] text-slate-400 font-mono">
                            <span>Campus: Knowledge Enclave</span>
                            <span class="text-cyan-400">● 100% SECURE NETWORK</span>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- ABOUT SECTION: PHILOSOPHY & VISION OF NEXT GENERATION PUBLIC SCHOOL        -->
    <!-- ========================================================================= -->
    <section id="about" class="py-24 bg-[#050b18] border-y border-slate-800/80 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left: High-Tech Cyber Badge Showcase -->
                <div class="lg:col-span-5">
                    <div class="relative glass-card-glow rounded-3xl p-8 space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs text-cyan-400 tracking-wider">FOUNDED FOR FUTURE LEADERSHIP</span>
                            <i class="fa-solid fa-certificate text-cyan-400 text-lg"></i>
                        </div>

                        <!-- Vision Quote Block -->
                        <blockquote class="text-slate-300 text-sm sm:text-base leading-relaxed italic border-l-2 border-cyan-500 pl-4 py-1">
                            "At <?= e($school_name) ?>, we believe education is not about rote memorization of the past, but the active architecture of the future. By intertwining scientific logic, artificial intelligence, ethical humanities, and critical empathy, we sculpt global visionaries."
                        </blockquote>

                        <div class="pt-4 border-t border-slate-800 flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 to-indigo-600 flex items-center justify-center font-bold text-slate-950 text-lg">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-base">Office of the Principal</h4>
                                <p class="text-xs text-cyan-400 font-medium">Directorate of Academics &bull; <?= e($school_name) ?></p>
                            </div>
                        </div>

                        <!-- Accreditation Badges -->
                        <div class="grid grid-cols-2 gap-3 pt-2 font-mono text-[11px]">
                            <div class="bg-slate-900/80 p-3 rounded-xl border border-slate-800">
                                <span class="text-cyan-400 block font-bold">CBSE AFFILIATED</span>
                                <span class="text-slate-400 text-[10px]">National Curriculum Standards</span>
                            </div>
                            <div class="bg-slate-900/80 p-3 rounded-xl border border-slate-800">
                                <span class="text-indigo-400 block font-bold">STEM & AI COUNCIL</span>
                                <span class="text-slate-400 text-[10px]">Accredited Innovation Hub</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: About Narrative -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-cyan-950/60 border border-cyan-500/30 text-cyan-400 text-xs font-mono font-semibold">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>OUR INSTITUTIONAL CREED</span>
                    </div>

                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Pioneering the Next Generation of Thinkers, Creators & Changemakers
                    </h2>

                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        <strong class="text-white"><?= e($school_name) ?></strong> was founded on the transformative principle that conventional pedagogy must evolve for an AI-augmented world. We harmonize the intellectual depth of CBSE academic standards with forward-thinking experiential learning — from algorithmic problem-solving to ethical philosophical debate.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2 text-cyan-400 font-bold text-sm">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Human-AI Collaborative Pedagogy</span>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Our master teachers leverage algorithmic insights to identify unique student proficiencies and bridge conceptual gaps instantly.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center space-x-2 text-cyan-400 font-bold text-sm">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Holistic Character & Moral Compass</span>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                High technology without moral foundation is perilous. We instill classical empathy, environmental consciousness, and selfless integrity.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center space-x-2 text-cyan-400 font-bold text-sm">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Global Olympiad & Competitive Rigor</span>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Integrated preparation for JEE, NEET, CUET, International Informatics, and Robotics Olympiads built into the school timetable.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center space-x-2 text-cyan-400 font-bold text-sm">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Future-Proof Campus Infrastructure</span>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Clean, sustainable, air-purified, eco-smart campus with high-performance computing, sports arenas, and creative studios.
                            </p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="#wings" class="inline-flex items-center space-x-2 text-cyan-400 hover:text-cyan-300 font-bold text-xs uppercase tracking-wider group">
                            <span>Explore Our Academic Wings & Curriculum</span>
                            <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- 6 AI PILLARS: THE NEXTGEN EDUCATIONAL FRAMEWORK                           -->
    <!-- ========================================================================= -->
    <section id="pillars" class="py-24 bg-[#030712] relative cyber-dots">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-mono font-bold uppercase tracking-widest text-cyan-400 bg-cyan-950/80 px-3.5 py-1.5 rounded-full border border-cyan-500/30">
                    NEXTGEN METHODOLOGY
                </span>
                <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Six Pillars of AI-Enhanced Education
                </h2>
                <p class="text-slate-400 text-sm">
                    How <?= e($school_name) ?> bridges traditional classroom excellence with next-century technological empowerment.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Pillar 1 -->
                <div class="glass-card-glow p-8 rounded-3xl transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-500/20 to-blue-500/20 border border-cyan-500/40 text-cyan-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-brain-circuit"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-cyan-400 transition-colors">Adaptive AI Tutoring</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-4">
                        Proprietary algorithmic diagnostics analyze each student’s speed and comprehension nuances, generating customized problem sets and instant concept refreshers.
                    </p>
                    <div class="font-mono text-[11px] text-cyan-400 flex items-center space-x-1.5">
                        <i class="fa-solid fa-code text-[10px]"></i>
                        <span>Zero Student Left Behind</span>
                    </div>
                </div>

                <!-- Pillar 2 -->
                <div class="glass-card-glow p-8 rounded-3xl transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500/20 to-purple-500/20 border border-indigo-500/40 text-indigo-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors">Robotics, Drones & IoT FabLab</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-4">
                        Hands-on mechatronics engineering starting from Grade 3. Students program microcontrollers, build autonomous obstacle-navigating robots, and prototype smart city sensors.
                    </p>
                    <div class="font-mono text-[11px] text-indigo-400 flex items-center space-x-1.5">
                        <i class="fa-solid fa-gear text-[10px]"></i>
                        <span>Physical Computing & Maker Culture</span>
                    </div>
                </div>

                <!-- Pillar 3 -->
                <div class="glass-card-glow p-8 rounded-3xl transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-purple-500/20 to-pink-500/20 border border-purple-500/40 text-purple-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-vr-cardboard"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-400 transition-colors">AR/VR Neural Smart Classrooms</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-4">
                        Complex abstract concepts become tangible reality. Students walk through subatomic electron orbitals, explore Mars rovers, and dissect 3D heart chambers through VR headsets.
                    </p>
                    <div class="font-mono text-[11px] text-purple-400 flex items-center space-x-1.5">
                        <i class="fa-solid fa-cubes text-[10px]"></i>
                        <span>Spatial 3D Experiential Learning</span>
                    </div>
                </div>

                <!-- Pillar 4 -->
                <div class="glass-card-glow p-8 rounded-3xl transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-teal-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Predictive Growth Analytics</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-4">
                        Comprehensive longitudinal metrics replace high-stress exam surprises. Parents receive proactive weekly growth maps evaluating curiosity, problem retention, and grit.
                    </p>
                    <div class="font-mono text-[11px] text-emerald-400 flex items-center space-x-1.5">
                        <i class="fa-solid fa-chart-pie text-[10px]"></i>
                        <span>Continuous Mastery Tracking</span>
                    </div>
                </div>

                <!-- Pillar 5 -->
                <div class="glass-card-glow p-8 rounded-3xl transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-cyan-500/20 border border-blue-500/40 text-blue-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-code"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-blue-400 transition-colors">Python, Data Science & AI Literacy</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-4">
                        Computational thinking from early childhood. Senior students build supervised machine learning models, understand prompt engineering, and deploy neural text classifiers.
                    </p>
                    <div class="font-mono text-[11px] text-blue-400 flex items-center space-x-1.5">
                        <i class="fa-solid fa-terminal text-[10px]"></i>
                        <span>Industry-Standard Coding Competency</span>
                    </div>
                </div>

                <!-- Pillar 6 -->
                <div class="glass-card-glow p-8 rounded-3xl transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-orange-500/20 border border-amber-500/40 text-amber-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-amber-400 transition-colors">Cyber Ethics & Human Leadership</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-4">
                        We ground technical power in ethical responsibility. Students master data privacy, bias prevention, compassionate human leadership, and sustainable green technology.
                    </p>
                    <div class="font-mono text-[11px] text-amber-400 flex items-center space-x-1.5">
                        <i class="fa-solid fa-shield-heart text-[10px]"></i>
                        <span>Responsible Digital Citizenship</span>
                    </div>
                </div>

            </div>

        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- ACADEMIC WINGS: PRE-K TO GRADE 12 (CBSE SYLLABUS + AI INTEGRATION)         -->
    <!-- ========================================================================= -->
    <section id="wings" class="py-24 bg-[#050b18] border-t border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-mono font-bold uppercase tracking-widest text-cyan-400 bg-cyan-950/80 px-3.5 py-1.5 rounded-full border border-cyan-500/30">
                    ACADEMIC STRUCTURE
                </span>
                <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Progressive Learning Wings (Grades Pre-K to 12)
                </h2>
                <p class="text-slate-400 text-sm">
                    Rigorous CBSE curriculum harmonized with stage-appropriate digital and scientific mastery.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Foundational Wing -->
                <div class="glass-card p-8 rounded-3xl border border-slate-800 relative hover:border-cyan-500/50 transition-all duration-300">
                    <div class="inline-block px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-400 text-[10px] font-mono font-bold mb-4 border border-cyan-500/20">
                        GRADES NURSERY - 5
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-shapes"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Foundational & Primary Wing</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                        Igniting organic curiosity through sensory learning, Montessori play-methodology, early phonics mastery, foundational numeracy, and gamified computational logic.
                    </p>
                    <ul class="space-y-2.5 text-xs text-slate-300 border-t border-slate-800/80 pt-4">
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-cyan-400 text-[10px]"></i>
                            <span>Activity-Based Experiential Math</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-cyan-400 text-[10px]"></i>
                            <span>Scratch & Block-Based Coding</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-cyan-400 text-[10px]"></i>
                            <span>Art, Music, Physical Literacy & Yoga</span>
                        </li>
                    </ul>
                </div>

                <!-- Middle Wing -->
                <div class="glass-card-glow p-8 rounded-3xl relative border border-indigo-500/40 shadow-xl transition-all duration-300 transform md:-translate-y-2">
                    <div class="absolute -top-3 right-6 px-3 py-0.5 rounded-full bg-indigo-500 text-white font-mono text-[10px] font-extrabold uppercase tracking-wider">
                        STEM HUB
                    </div>
                    <div class="inline-block px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 text-[10px] font-mono font-bold mb-4 border border-indigo-500/20">
                        GRADES 6 - 8
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-atom"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Middle Wing</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                        Transition to disciplined scientific inquiry, analytical problem solving, Python programming fundamentals, intermediate mechatronics, and multilingual proficiency.
                    </p>
                    <ul class="space-y-2.5 text-xs text-slate-300 border-t border-slate-800/80 pt-4">
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-indigo-400 text-[10px]"></i>
                            <span>Python, Web Dev & Algorithm Design</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-indigo-400 text-[10px]"></i>
                            <span>Robotics, Sensors & Arduino Kits</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-indigo-400 text-[10px]"></i>
                            <span>Integrated STEM Lab Experiments</span>
                        </li>
                    </ul>
                </div>

                <!-- Senior Wing -->
                <div class="glass-card p-8 rounded-3xl border border-slate-800 relative hover:border-purple-500/50 transition-all duration-300">
                    <div class="inline-block px-3 py-1 rounded-full bg-purple-500/10 text-purple-400 text-[10px] font-mono font-bold mb-4 border border-purple-500/20">
                        GRADES 9 - 12
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-user-astronaut"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Senior Secondary Wing</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                        Specialized Science (PCM/PCB), Commerce with FinTech, and Humanities with International Relations. Integrated competitive coaching for JEE, NEET, CUET & NDA.
                    </p>
                    <ul class="space-y-2.5 text-xs text-slate-300 border-t border-slate-800/80 pt-4">
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                            <span>AI & Machine Learning Stream Option</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                            <span>Dedicated JEE / NEET / CUET Mentors</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                            <span>Career Counseling & Global Admissions</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- INNOVATION LABS & CAMPUS INFRASTRUCTURE (HIGH-TECH SVG VISUALS)           -->
    <!-- ========================================================================= -->
    <section id="facilities" class="py-24 bg-[#030712] relative cyber-grid">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-mono font-bold uppercase tracking-widest text-cyan-400 bg-cyan-950/80 px-3.5 py-1.5 rounded-full border border-cyan-500/30">
                    CAMPUS SPACES
                </span>
                <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    World-Class Innovation Infrastructure
                </h2>
                <p class="text-slate-400 text-sm">
                    Designed to inspire scientific wonder, athletic vigor, and technological innovation.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Facility 1: AI & Robotics Lab -->
                <div class="neon-border-box p-6 space-y-4">
                    <div class="h-44 rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-950/60 p-4 border border-cyan-500/20 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-cyan-400 uppercase tracking-widest">FACILITY 01</span>
                            <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                                <i class="fa-solid fa-robot"></i>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <!-- High-Tech Schematics SVG -->
                            <svg class="w-full h-16 text-cyan-400" viewBox="0 0 200 60" fill="none">
                                <rect x="10" y="10" width="40" height="40" rx="6" stroke="#06b6d4" stroke-width="2"/>
                                <circle cx="30" cy="30" r="8" fill="#06b6d4" fill-opacity="0.3"/>
                                <line x1="50" y1="30" x2="90" y2="30" stroke="#06b6d4" stroke-width="2" stroke-dasharray="2 2"/>
                                <rect x="90" y="15" width="50" height="30" rx="4" stroke="#6366f1" stroke-width="2"/>
                                <line x1="140" y1="30" x2="180" y2="30" stroke="#a855f7" stroke-width="2"/>
                                <circle cx="180" cy="30" r="5" fill="#a855f7"/>
                            </svg>
                            <span class="text-xs font-mono text-cyan-300">ROBOTICS & MECHATRONICS LAB</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Humanoid & Autonomous Drone Arena</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Equipped with 3D printers, solder stations, Arduino & Raspberry Pi benches, and drone obstacle training cages.</p>
                    </div>
                </div>

                <!-- Facility 2: Quantum Computing Lab -->
                <div class="neon-border-box p-6 space-y-4">
                    <div class="h-44 rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950/60 p-4 border border-indigo-500/20 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-indigo-400 uppercase tracking-widest">FACILITY 02</span>
                            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                                <i class="fa-solid fa-network-wired"></i>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <svg class="w-full h-16 text-indigo-400" viewBox="0 0 200 60" fill="none">
                                <circle cx="40" cy="30" r="16" stroke="#6366f1" stroke-width="2"/>
                                <circle cx="100" cy="30" r="16" stroke="#06b6d4" stroke-width="2"/>
                                <circle cx="160" cy="30" r="16" stroke="#a855f7" stroke-width="2"/>
                                <path d="M40 30 Q100 0 160 30" stroke="#6366f1" stroke-width="1.5" stroke-dasharray="3 3"/>
                                <path d="M40 30 Q100 60 160 30" stroke="#06b6d4" stroke-width="1.5" stroke-dasharray="3 3"/>
                            </svg>
                            <span class="text-xs font-mono text-indigo-300">HIGH-PERFORMANCE COMPUTE</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">NextGen AI & Data Science Terminal</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Dedicated workstation pods with GPU acceleration, dual-monitor programming terminals, and Gigabit cloud access.</p>
                    </div>
                </div>

                <!-- Facility 3: Composite Science Labs -->
                <div class="neon-border-box p-6 space-y-4">
                    <div class="h-44 rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-purple-950/60 p-4 border border-purple-500/20 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-purple-400 uppercase tracking-widest">FACILITY 03</span>
                            <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                                <i class="fa-solid fa-flask-vial"></i>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <svg class="w-full h-16 text-purple-400" viewBox="0 0 200 60" fill="none">
                                <path d="M30 50 L45 20 L55 20 L70 50 Z" stroke="#a855f7" stroke-width="2"/>
                                <circle cx="50" cy="42" r="4" fill="#a855f7"/>
                                <line x1="85" y1="35" x2="115" y2="35" stroke="#ec4899" stroke-width="2"/>
                                <path d="M130 20 Q150 50 170 20" stroke="#06b6d4" stroke-width="2"/>
                            </svg>
                            <span class="text-xs font-mono text-purple-300">RESEARCH-GRADE LABS</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Physics, Chemistry & Biotech Labs</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Adhering to international safety norms with fume extractors, high-magnification microscopes, and spectrophotometers.</p>
                    </div>
                </div>

                <!-- Facility 4: Cyber Sports Arena -->
                <div class="neon-border-box p-6 space-y-4">
                    <div class="h-44 rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/60 p-4 border border-emerald-500/20 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-emerald-400 uppercase tracking-widest">FACILITY 04</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                <i class="fa-solid fa-trophy"></i>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <svg class="w-full h-16 text-emerald-400" viewBox="0 0 200 60" fill="none">
                                <rect x="20" y="10" width="160" height="40" rx="8" stroke="#10b981" stroke-width="2"/>
                                <circle cx="100" cy="30" r="14" stroke="#10b981" stroke-width="2"/>
                                <line x1="100" y1="10" x2="100" y2="50" stroke="#10b981" stroke-width="1.5"/>
                            </svg>
                            <span class="text-xs font-mono text-emerald-300">OLYMPIC SPORTS TURF</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Multi-Sport Complex & Indoor Arena</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Synthetic basketball court, FIFA-certified soccer turf, 4-lane cricket practice net, and indoor badminton arena.</p>
                    </div>
                </div>

                <!-- Facility 5: Digital Library Cloud -->
                <div class="neon-border-box p-6 space-y-4">
                    <div class="h-44 rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950/60 p-4 border border-blue-500/20 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-blue-400 uppercase tracking-widest">FACILITY 05</span>
                            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                                <i class="fa-solid fa-book-open-reader"></i>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <svg class="w-full h-16 text-blue-400" viewBox="0 0 200 60" fill="none">
                                <rect x="30" y="15" width="60" height="35" rx="3" stroke="#3b82f6" stroke-width="2"/>
                                <rect x="110" y="15" width="60" height="35" rx="3" stroke="#3b82f6" stroke-width="2"/>
                                <path d="M30 45 L90 45 M110 45 L170 45" stroke="#06b6d4" stroke-width="2"/>
                            </svg>
                            <span class="text-xs font-mono text-blue-300">KNOWLEDGE REPOSITORY</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Hybrid Digital Library & Media Center</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">20,000+ physical volumes coupled with Kindle stations and open subscriptions to JSTOR and academic research journals.</p>
                    </div>
                </div>

                <!-- Facility 6: GPS Smart Bus Transport -->
                <div class="neon-border-box p-6 space-y-4">
                    <div class="h-44 rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-amber-950/60 p-4 border border-amber-500/20 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-amber-400 uppercase tracking-widest">FACILITY 06</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <svg class="w-full h-16 text-amber-400" viewBox="0 0 200 60" fill="none">
                                <rect x="25" y="15" width="150" height="30" rx="6" stroke="#f59e0b" stroke-width="2"/>
                                <circle cx="55" cy="45" r="7" fill="#f59e0b"/>
                                <circle cx="145" cy="45" r="7" fill="#f59e0b"/>
                                <rect x="40" y="20" width="25" height="12" rx="2" stroke="#f59e0b"/>
                                <rect x="75" y="20" width="25" height="12" rx="2" stroke="#f59e0b"/>
                                <rect x="110" y="20" width="25" height="12" rx="2" stroke="#f59e0b"/>
                            </svg>
                            <span class="text-xs font-mono text-amber-300">TELEMATICS & BIOMETRIC SAFETY</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Smart AC Transport Fleet with GPS</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Live parent bus tracking via mobile app, speed governor limits, on-board CCTV, and certified lady attendants.</p>
                    </div>
                </div>

            </div>

        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- INTERACTIVE AI ADMISSIONS & ELIGIBILITY CALCULATOR WIDGET                 -->
    <!-- ========================================================================= -->
    <section id="calculator" class="py-24 bg-[#050b18] border-t border-slate-800 relative">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="glass-card-glow rounded-3xl p-8 sm:p-12 relative overflow-hidden">
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-cyan-500/10 blur-[100px] rounded-full pointer-events-none"></div>

                <div class="text-center max-w-2xl mx-auto mb-10 space-y-2">
                    <span class="font-mono text-xs font-bold text-cyan-400 uppercase tracking-widest bg-cyan-950 px-3 py-1 rounded-full border border-cyan-800">
                        INTERACTIVE ADMISSION EVALUATOR
                    </span>
                    <h2 class="font-display text-2xl sm:text-3xl font-extrabold text-white">
                        AI Grade Eligibility & Pathway Calculator
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400">
                        Select your child’s target grade to instantly generate age criteria, curriculum focus, and laboratory allocation.
                    </p>
                </div>

                <!-- Calculator Controls -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono text-cyan-400 uppercase font-bold mb-2">Select Target Grade (Session 2026-27)</label>
                            <select id="grade-selector" onchange="updatePathwayInfo()" class="w-full py-3.5 px-4 bg-slate-950 border border-slate-700 rounded-2xl text-sm font-semibold text-white focus:outline-none focus:border-cyan-400 transition-all">
                                <option value="primary">Primary (Grades 1 to 5) — Age 6-10</option>
                                <option value="middle" selected>Middle Wing (Grades 6 to 8) — Age 11-13</option>
                                <option value="secondary">Secondary (Grades 9 & 10) — Age 14-15</option>
                                <option value="senior_sci">Senior Secondary Science (Grades 11 & 12 PCM/PCB)</option>
                                <option value="senior_com">Senior Secondary Commerce (Grades 11 & 12 with FinTech)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-mono text-cyan-400 uppercase font-bold mb-2">Student's Year of Birth</label>
                            <select id="birth-year" onchange="updatePathwayInfo()" class="w-full py-3.5 px-4 bg-slate-950 border border-slate-700 rounded-2xl text-sm font-semibold text-white focus:outline-none focus:border-cyan-400 transition-all">
                                <option value="2012">2012 (Approx Age 14)</option>
                                <option value="2013">2013 (Approx Age 13)</option>
                                <option value="2014" selected>2014 (Approx Age 12)</option>
                                <option value="2015">2015 (Approx Age 11)</option>
                                <option value="2016">2016 (Approx Age 10)</option>
                                <option value="2017">2017 (Approx Age 9)</option>
                                <option value="2018">2018 (Approx Age 8)</option>
                            </select>
                        </div>

                        <div class="p-4 rounded-2xl bg-cyan-950/40 border border-cyan-500/20 text-xs text-slate-300 space-y-1">
                            <span class="font-bold text-cyan-400 flex items-center space-x-1.5">
                                <i class="fa-solid fa-sparkles"></i>
                                <span>AI Assessment Note:</span>
                            </span>
                            <p>Every student receives a diagnostic cognitive orientation to personalize their learning stream.</p>
                        </div>
                    </div>

                    <!-- Output Card -->
                    <div id="pathway-output" class="bg-slate-950/90 rounded-2xl p-6 border border-cyan-500/30 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <span id="output-wing-badge" class="px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400 font-mono text-[10px] font-bold uppercase">
                                Middle Wing Pathway
                            </span>
                            <span class="text-emerald-400 font-mono text-xs font-bold flex items-center space-x-1">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>ELIGIBLE FOR 2026-27</span>
                            </span>
                        </div>

                        <div>
                            <h4 id="output-title" class="font-bold text-white text-base">Middle Wing (Grades 6-8)</h4>
                            <p id="output-desc" class="text-xs text-slate-400 mt-1 leading-relaxed">
                                Core CBSE syllabus combined with Python coding, Arduino mechatronics, and analytical social sciences.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs font-mono">
                            <div class="bg-slate-900 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block text-[10px]">Coding Tech</span>
                                <span id="output-tech" class="text-cyan-400 font-bold">Python & Robotics</span>
                            </div>
                            <div class="bg-slate-900 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block text-[10px]">Lab Access</span>
                                <span id="output-labs" class="text-indigo-400 font-bold">6 Hours/Week</span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a href="#admissions" class="w-full py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-slate-950 font-bold text-xs rounded-xl flex items-center justify-center space-x-2 shadow-lg shadow-cyan-500/20 hover:from-cyan-400 hover:to-blue-500 transition-all">
                                <span>Proceed to Online Application</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- ADMISSIONS 2026-2027: 4-STEP PROCESS & INQUIRY FORM                       -->
    <!-- ========================================================================= -->
    <section id="admissions" class="py-24 bg-[#030712] relative cyber-dots">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-mono font-bold uppercase tracking-widest text-cyan-400 bg-cyan-950/80 px-3.5 py-1.5 rounded-full border border-cyan-500/30">
                    JOIN NEXT GENERATION PUBLIC SCHOOL
                </span>
                <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Admissions Open for Academic Session 2026-2027
                </h2>
                <p class="text-slate-400 text-sm">
                    A seamless, transparent admission protocol designed to discover and cultivate every child's genius.
                </p>
            </div>

            <!-- 4-Step Process Roadmap -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
                
                <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-3 relative">
                    <span class="font-mono text-2xl font-black text-cyan-400 block">01</span>
                    <h4 class="font-bold text-white text-base">Online Registration</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Fill out the inquiry form or visit our admissions office to register for the 2026-27 session.</p>
                </div>

                <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-3 relative">
                    <span class="font-mono text-2xl font-black text-indigo-400 block">02</span>
                    <h4 class="font-bold text-white text-base">Diagnostic Interaction</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Gentle baseline interaction to understand learner strengths and experiential curiosities.</p>
                </div>

                <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-3 relative">
                    <span class="font-mono text-2xl font-black text-purple-400 block">03</span>
                    <h4 class="font-bold text-white text-base">Parent Counseling</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Detailed interaction with the academic coordinator regarding curriculum & transport.</p>
                </div>

                <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-3 relative">
                    <span class="font-mono text-2xl font-black text-emerald-400 block">04</span>
                    <h4 class="font-bold text-white text-base">Welcome to NGPS</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Formal verification, issuance of student credentials, and orientation welcome pack.</p>
                </div>

            </div>

            <!-- Contact & Inquiry Form Grid -->
            <div id="admission-inquiry" class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left: Contact Details -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="space-y-2">
                        <span class="font-mono text-xs text-cyan-400 font-bold uppercase tracking-wider">OFFICIAL ADMISSIONS DESK</span>
                        <h3 class="font-display text-2xl sm:text-3xl font-extrabold text-white">We're Here to Guide You</h3>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                            Our admissions counselors are available Monday through Saturday from 8:30 AM to 4:30 PM.
                        </p>
                    </div>

                    <div class="space-y-4 text-xs text-slate-300 font-medium">
                        
                        <div class="p-4 rounded-2xl glass-card flex items-start space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center shrink-0 text-base">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <span class="font-bold text-white block text-sm">Campus Address</span>
                                <span class="text-slate-400"><?= e($school_address) ?></span>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl glass-card flex items-start space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0 text-base">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <span class="font-bold text-white block text-sm">Direct Telephones</span>
                                <span class="text-slate-400"><?= e($school_phone) ?> | Admissions: +91 98765 43211</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl glass-card flex items-start space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 text-base">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <span class="font-bold text-white block text-sm">Official Inquiries</span>
                                <span class="text-slate-400"><?= e($school_email) ?></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right: Online Admission Inquiry Form -->
                <div class="lg:col-span-7">
                    <div class="glass-card-glow p-8 sm:p-10 rounded-3xl space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <h3 class="font-display font-bold text-lg text-white">Online Admission Registration 2026-27</h3>
                            <span class="font-mono text-[10px] text-cyan-400 bg-cyan-950 px-2 py-0.5 rounded border border-cyan-800">
                                INSTANT DISPATCH
                            </span>
                        </div>

                        <form onsubmit="handleInquirySubmit(event)" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-mono text-slate-400 uppercase font-semibold mb-1.5">Parent / Guardian Name *</label>
                                    <input type="text" id="parent-name" required placeholder="Full Name" class="w-full py-3 px-4 bg-slate-950 border border-slate-800 rounded-xl text-xs font-medium text-white focus:outline-none focus:border-cyan-400 transition-all placeholder-slate-600">
                                </div>
                                <div>
                                    <label class="block text-xs font-mono text-slate-400 uppercase font-semibold mb-1.5">Student Full Name *</label>
                                    <input type="text" id="student-name" required placeholder="Student's Name" class="w-full py-3 px-4 bg-slate-950 border border-slate-800 rounded-xl text-xs font-medium text-white focus:outline-none focus:border-cyan-400 transition-all placeholder-slate-600">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-mono text-slate-400 uppercase font-semibold mb-1.5">Mobile Phone *</label>
                                    <input type="tel" id="mobile-phone" required placeholder="+91 98765 00000" class="w-full py-3 px-4 bg-slate-950 border border-slate-800 rounded-xl text-xs font-medium text-white focus:outline-none focus:border-cyan-400 transition-all placeholder-slate-600">
                                </div>
                                <div>
                                    <label class="block text-xs font-mono text-slate-400 uppercase font-semibold mb-1.5">Grade Seeking Admission *</label>
                                    <select id="grade-sought" required class="w-full py-3 px-4 bg-slate-950 border border-slate-800 rounded-xl text-xs font-medium text-white focus:outline-none focus:border-cyan-400 transition-all">
                                        <option value="">-- Select Grade --</option>
                                        <option value="Pre-Primary (Nursery, LKG, UKG)">Pre-Primary (Nursery, LKG, UKG)</option>
                                        <option value="Primary (Grades 1 to 5)">Primary (Grades 1 to 5)</option>
                                        <option value="Middle Wing (Grades 6 to 8)">Middle Wing (Grades 6 to 8)</option>
                                        <option value="Secondary (Grades 9 & 10)">Secondary (Grades 9 & 10)</option>
                                        <option value="Senior Science (PCM/PCB)">Senior Science (PCM/PCB with AI)</option>
                                        <option value="Senior Commerce">Senior Commerce (with FinTech)</option>
                                        <option value="Senior Humanities">Senior Humanities</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-mono text-slate-400 uppercase font-semibold mb-1.5">Email Address</label>
                                <input type="email" id="email-address" placeholder="parent@domain.com" class="w-full py-3 px-4 bg-slate-950 border border-slate-800 rounded-xl text-xs font-medium text-white focus:outline-none focus:border-cyan-400 transition-all placeholder-slate-600">
                            </div>

                            <div>
                                <label class="block text-xs font-mono text-slate-400 uppercase font-semibold mb-1.5">Any Specific Questions or Notes</label>
                                <textarea id="inquiry-message" rows="2" placeholder="Tell us about your child's interests or any transport requirements..." class="w-full p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-medium text-white focus:outline-none focus:border-cyan-400 transition-all placeholder-slate-600"></textarea>
                            </div>

                            <button type="submit" class="w-full py-4 bg-gradient-to-r from-cyan-400 via-cyan-500 to-blue-600 hover:from-cyan-300 hover:to-blue-500 text-slate-950 font-display font-extrabold text-xs sm:text-sm rounded-xl shadow-[0_0_25px_rgba(6,182,212,0.35)] transition-all flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                                <span>Submit Admission Application</span>
                            </button>
                        </form>

                        <div id="inquiry-success" class="hidden p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-xs text-center space-y-1">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                            <div class="font-bold">Application Received Successfully!</div>
                            <p class="text-[11px] text-emerald-200/80">Thank you. An admissions coordinator from <?= e($school_name) ?> will connect with you within 24 hours.</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- CONTACT MATRIX & CAMPUS COORDINATES                                       -->
    <!-- ========================================================================= -->
    <section id="contact" class="py-20 bg-[#050b18] border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-slate-950 p-8 sm:p-12 border border-slate-800 grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                
                <div class="space-y-3">
                    <span class="font-mono text-xs text-cyan-400 uppercase font-bold tracking-widest">CAMPUS LOCATION</span>
                    <h3 class="font-display text-2xl font-bold text-white"><?= e($school_name) ?></h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        <?= e($school_address) ?>
                    </p>
                    <div class="font-mono text-[11px] text-cyan-400">
                        Geo-Coordinates: 28.6139° N, 77.2090° E
                    </div>
                </div>

                <div class="space-y-3">
                    <span class="font-mono text-xs text-indigo-400 uppercase font-bold tracking-widest">HOURS OF OPERATION</span>
                    <ul class="text-xs text-slate-300 space-y-1.5 font-mono">
                        <li class="flex justify-between"><span>Mon - Fri:</span> <span class="text-white">08:00 AM - 04:30 PM</span></li>
                        <li class="flex justify-between"><span>Saturday:</span> <span class="text-white">08:30 AM - 01:30 PM</span></li>
                        <li class="flex justify-between"><span>Sunday:</span> <span class="text-rose-400">Closed (Campus Secure)</span></li>
                    </ul>
                </div>

                <div class="space-y-3 md:text-right">
                    <span class="font-mono text-xs text-emerald-400 uppercase font-bold tracking-widest">VERIFIED CHANNELS</span>
                    <p class="text-xs text-slate-300 font-mono block"><?= e($school_phone) ?></p>
                    <p class="text-xs text-slate-300 font-mono block"><?= e($school_email) ?></p>
                    <div class="pt-2">
                        <a href="<?= base_url('login.php') ?>" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs font-semibold text-white hover:border-cyan-400 transition-colors">
                            <i class="fa-solid fa-lock text-[10px] text-cyan-400"></i>
                            <span>Authorized Portal Sign In</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- FUTURISTIC AI FOOTER: STRICTLY NEXT GENERATION PUBLIC SCHOOL              -->
    <!-- ========================================================================= -->
    <footer class="bg-[#030712] text-slate-400 py-16 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-12 gap-10">
            
            <!-- Col 1: Brand Info -->
            <div class="md:col-span-5 space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-indigo-600 flex items-center justify-center font-bold text-slate-950 text-xl shadow-[0_0_15px_rgba(6,182,212,0.4)]">
                        <i class="fa-solid fa-atom"></i>
                    </div>
                    <div>
                        <span class="font-display font-extrabold text-white text-lg tracking-tight block">
                            <?= e($school_name) ?>
                        </span>
                        <span class="font-mono text-[10px] text-cyan-400 uppercase tracking-widest block">NextGen AI Academic Ecosystem</span>
                    </div>
                </div>
                <p class="text-xs leading-relaxed text-slate-500 max-w-sm">
                    Affiliated with CBSE. Dedicated to preparing young scholars for global impact through academic rigor, artificial intelligence literacy, and ethical stewardship.
                </p>
                <div class="flex items-center space-x-3 pt-2">
                    <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-cyan-400 hover:border-cyan-400 flex items-center justify-center text-xs transition-colors"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-cyan-400 hover:border-cyan-400 flex items-center justify-center text-xs transition-colors"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-cyan-400 hover:border-cyan-400 flex items-center justify-center text-xs transition-colors"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-cyan-400 hover:border-cyan-400 flex items-center justify-center text-xs transition-colors"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <!-- Col 2: Navigation Shortcuts -->
            <div class="md:col-span-2 space-y-3">
                <h4 class="font-mono text-white text-xs font-bold uppercase tracking-wider">Navigation</h4>
                <ul class="space-y-2 text-xs font-medium">
                    <li><a href="#home" class="hover:text-cyan-400 transition-colors">Home</a></li>
                    <li><a href="#about" class="hover:text-cyan-400 transition-colors">About NGPS</a></li>
                    <li><a href="#pillars" class="hover:text-cyan-400 transition-colors">AI Curriculum</a></li>
                    <li><a href="#wings" class="hover:text-cyan-400 transition-colors">Academic Wings</a></li>
                    <li><a href="#facilities" class="hover:text-cyan-400 transition-colors">Innovation Labs</a></li>
                </ul>
            </div>

            <!-- Col 3: Portals & Access -->
            <div class="md:col-span-2 space-y-3">
                <h4 class="font-mono text-white text-xs font-bold uppercase tracking-wider">Digital Portals</h4>
                <ul class="space-y-2 text-xs font-medium">
                    <li><a href="<?= base_url('login.php') ?>" class="text-cyan-400 hover:underline flex items-center space-x-1.5"><i class="fa-solid fa-key text-[10px]"></i><span>Staff / Admin</span></a></li>
                    <li><a href="<?= base_url('login.php') ?>" class="hover:text-white transition-colors">Teacher Portal</a></li>
                    <li><a href="<?= base_url('login.php') ?>" class="hover:text-white transition-colors">Student & Parent Portal</a></li>
                    <li><a href="#admissions" class="hover:text-white transition-colors">Admissions 2026-27</a></li>
                </ul>
            </div>

            <!-- Col 4: Campus Coordinates -->
            <div class="md:col-span-3 space-y-3">
                <h4 class="font-mono text-white text-xs font-bold uppercase tracking-wider">Campus Desk</h4>
                <p class="text-xs text-slate-400 leading-relaxed font-sans">
                    <?= e($school_address) ?>
                </p>
                <p class="text-xs text-slate-400 font-mono">Ph: <?= e($school_phone) ?></p>
                <p class="text-xs text-cyan-400 font-mono"><?= e($school_email) ?></p>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 mt-12 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 font-mono">
            <p>&copy; <?= date('Y') ?> <?= e($school_name) ?>. All rights reserved.</p>
            <p class="mt-2 sm:mt-0 flex items-center space-x-3">
                <span class="text-cyan-400">CBSE Affiliation: 2130098</span>
                <span>•</span>
                <a href="<?= base_url('login.php') ?>" class="hover:text-slate-300">Portal Login &rarr;</a>
            </p>
        </div>
    </footer>

    <!-- Interactive Script for AI Calculator and Form Submission -->
    <script>
        function toggleMobileNav() {
            const nav = document.getElementById('mobile-nav');
            nav.classList.toggle('hidden');
        }

        const pathwayData = {
            primary: {
                badge: 'Primary Wing (Grades 1-5)',
                title: 'Primary Wing (Grades 1 to 5)',
                desc: 'Foundational numeracy, linguistic phonetics, Scratch coding, and Montessori-style discovery experiments.',
                tech: 'Scratch & Logic Blocks',
                labs: '3 Hours/Week'
            },
            middle: {
                badge: 'Middle Wing (Grades 6-8)',
                title: 'Middle Wing (Grades 6 to 8)',
                desc: 'Core CBSE syllabus combined with Python coding, Arduino mechatronics, and analytical social sciences.',
                tech: 'Python & Robotics',
                labs: '6 Hours/Week'
            },
            secondary: {
                badge: 'Secondary Wing (Grades 9-10)',
                title: 'Secondary Wing (Grades 9 & 10)',
                desc: 'Intensive CBSE Board preparation, Advanced STEM projects, Artificial Intelligence elective, and Career foundation.',
                tech: 'AI Elective & Web Tech',
                labs: '8 Hours/Week'
            },
            senior_sci: {
                badge: 'Senior Science Stream (Grades 11-12)',
                title: 'Senior Secondary Science (PCM / PCB)',
                desc: 'Rigorous Physics, Chemistry, Math/Bio with integrated JEE / NEET coaching and Machine Learning research labs.',
                tech: 'PyTorch / Data Science',
                labs: '12 Hours/Week'
            },
            senior_com: {
                badge: 'Senior Commerce Stream (Grades 11-12)',
                title: 'Senior Secondary Commerce with FinTech',
                desc: 'Accountancy, Business Economics, Financial Markets, Applied Math, and Algorithmic Trading foundations.',
                tech: 'FinTech & Analytics',
                labs: '6 Hours/Week'
            }
        };

        function updatePathwayInfo() {
            const selector = document.getElementById('grade-selector');
            const data = pathwayData[selector.value] || pathwayData.middle;

            document.getElementById('output-wing-badge').textContent = data.badge;
            document.getElementById('output-title').textContent = data.title;
            document.getElementById('output-desc').textContent = data.desc;
            document.getElementById('output-tech').textContent = data.tech;
            document.getElementById('output-labs').textContent = data.labs;
        }

        function handleInquirySubmit(e) {
            e.preventDefault();
            const parent = document.getElementById('parent-name').value;
            const student = document.getElementById('student-name').value;
            const phone = document.getElementById('mobile-phone').value;
            const grade = document.getElementById('grade-sought').value;

            if (!parent || !student || !phone || !grade) {
                alert('Please fill out all required fields.');
                return;
            }

            document.getElementById('inquiry-success').classList.remove('hidden');
            e.target.reset();
        }
    </script>

</body>
</html>
