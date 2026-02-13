<!doctype html>
<html lang="en" class="layout-wide customizer-hide"
      data-assets-path="../assets/"
      data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>PMS · prismatic enterprise</title>
    <meta name="description" content="Project management system – vibrant purple & white — medium format" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,500;14..32,600;14..32,700;14..32,800&family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Icon libraries -->
    <link rel="stylesheet" href="admin/assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" />

    <!-- Core CSS (overridden) -->
    <link rel="stylesheet" href="admin/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="admin/assets/css/demo.css" />
    <link rel="stylesheet" href="admin/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="admin/assets/vendor/css/pages/page-auth.css" />

    <script src="admin/assets/vendor/js/helpers.js"></script>
    <script src="admin/assets/js/config.js"></script>

    <style>
        /* -------------------------------
           🌈 PURPLE+WHITE SPECTRUM · BUBBLE PARADISE
           OFFICE PREMIUM – MEDIUM SIZE (same look, scaled to medium)
           everything kept identical – only sizing adjusted to medium
        ------------------------------- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Public Sans', sans-serif;
            background: radial-gradient(circle at 10% 30%, #f9ebff, #f1e6ff, #f4f0ff, #ffffff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;

            /* VIBRANT PURPLE-WHITE GRADIENT – same energy */
            background: linear-gradient(145deg, #faf0ff 0%, #f5eaff 20%, #f3e7ff 40%, #efe2ff 60%, #f7edff 80%, #fcf4ff 100%);
            background-size: 300% 300%;
            animation: prismFlow 18s ease infinite;
        }

        @keyframes prismFlow {
            0% { background-position: 0% 0%; }
            25% { background-position: 50% 30%; }
            50% { background-position: 100% 70%; }
            75% { background-position: 30% 80%; }
            100% { background-position: 0% 0%; }
        }

        /* ---------- 🫧 BUBBLE UNIVERSE – exactly same, just scaled to medium ---------- */
        .bubble-universe {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
            overflow: hidden;
        }

        .bubble {
            position: absolute;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-radius: 50%;
            box-shadow: 0 8px 25px rgba(146, 84, 255, 0.2), inset 0 0 15px rgba(255,255,255,0.7);
            border: 1.5px solid rgba(255, 255, 255, 0.7);
            animation: bubbleFloat 14s infinite ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(175, 125, 255, 0.3);
            font-size: 1.3rem;  /* slightly reduced for medium balance */
            filter: drop-shadow(0 6px 10px rgba(159, 0, 255, 0.1));
        }

        .bubble i, .bubble svg {
            opacity: 0.4;
            color: #aa88ff;
        }

        /* bubble sizes & positions – same spirit, now medium scale */
        .b1 { width: 110px; height: 110px; top: 6%; left: 4%; background: rgba(215, 195, 255, 0.35); animation-delay: 0s; }
        .b2 { width: 170px; height: 170px; top: 72%; left: 80%; background: rgba(235, 215, 255, 0.4); animation-delay: 2s; }
        .b3 { width: 80px; height: 80px; top: 44%; left: 10%; background: rgba(255, 225, 250, 0.5); animation-delay: 4s; }
        .b4 { width: 140px; height: 140px; top: 12%; left: 76%; background: rgba(245, 215, 255, 0.4); animation-delay: 1s; }
        .b5 { width: 120px; height: 120px; top: 82%; left: 16%; background: rgba(225, 195, 255, 0.45); animation-delay: 3s; }
        .b6 { width: 100px; height: 100px; top: 52%; left: 70%; background: rgba(208, 188, 255, 0.4); animation-delay: 5s; }
        .b7 { width: 160px; height: 160px; top: 86%; left: 52%; background: rgba(238, 210, 255, 0.35); animation-delay: 2.5s; }
        .b8 { width: 75px; height: 75px; top: 22%; left: 90%; background: rgba(248, 228, 255, 0.5); animation-delay: 6s; }
        .b9 { width: 130px; height: 130px; top: 36%; left: 42%; background: rgba(218, 192, 255, 0.4); animation-delay: 0.5s; }
        .b10 { width: 105px; height: 105px; top: 64%; left: 28%; background: rgba(255, 235, 250, 0.45); animation-delay: 7s; }
        .b11 { width: 190px; height: 190px; top: -15px; right: -20px; background: rgba(228, 204, 255, 0.3); animation-delay: 8s; }
        .b12 { width: 90px; height: 90px; top: 9%; left: 52%; background: rgba(248, 210, 255, 0.5); animation-delay: 9s; }
        .b13 { width: 150px; height: 150px; top: 74%; left: 6%; background: rgba(205, 175, 255, 0.4); animation-delay: 10s; }
        .b14 { width: 115px; height: 115px; top: 26%; left: 18%; background: rgba(255, 205, 245, 0.5); animation-delay: 11s; }
        .b15 { width: 135px; height: 135px; top: 48%; left: 86%; background: rgba(218, 200, 255, 0.4); animation-delay: 12s; }

        @keyframes bubbleFloat {
            0% { transform: translateY(0px) translateX(0) scale(1); }
            25% { transform: translateY(-30px) translateX(18px) scale(1.05); }
            50% { transform: translateY(-12px) translateX(-14px) scale(0.98); }
            75% { transform: translateY(22px) translateX(10px) scale(1.03); }
            100% { transform: translateY(0px) translateX(0) scale(1); }
        }

        /* sparkle particles – medium presence */
        .sparkle {
            position: fixed;
            width: 7px;
            height: 7px;
            background: white;
            border-radius: 50%;
            filter: blur(2px);
            opacity: 0.5;
            box-shadow: 0 0 18px #ffd5ff, 0 0 8px #cb9eff;
            animation: twinkle 6s infinite alternate;
            pointer-events: none;
            z-index: 12;
        }
        @keyframes twinkle {
            0% { opacity: 0.2; transform: scale(0.8); }
            100% { opacity: 0.9; transform: scale(1.3); }
        }
        .s1 { top: 18%; left: 22%; width: 12px; height: 12px; background: #f0dbff; }
        .s2 { top: 76%; left: 45%; width: 9px; height: 9px; background: #ffe6f0; }
        .s3 { top: 34%; left: 68%; width: 10px; height: 10px; background: #eaceff; }
        .s4 { top: 55%; left: 12%; width: 8px; height: 8px; background: #ffe0ff; }
        .s5 { top: 82%; left: 76%; width: 10px; height: 10px; background: #ddc3ff; }
        .s6 { top: 43%; left: 91%; width: 11px; height: 11px; background: #ffdcf5; }
        .s7 { top: 9%; left: 58%; width: 7px; height: 7px; background: #ffffff; }
        .s8 { top: 67%; left: 31%; width: 13px; height: 13px; background: #ffd5fc; }

        /* --- CONTAINER – MEDIUM SIZE (perfect balance) --- */
        .pms-login-container {
            width: 100%;
            max-width: 420px;  /* reduced from 480 → medium */
            margin: 0 auto;
            position: relative;
            z-index: 50;
        }

        /* --- CARD – medium scale, all proportions refined --- */
        .pms-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px) saturate(220%);
            -webkit-backdrop-filter: blur(20px) saturate(220%);
            border: 2px solid rgba(255, 255, 255, 0.9);
            border-radius: 48px;  /* slightly smaller, still luscious */
            box-shadow: 0 25px 45px -10px rgba(159, 80, 255, 0.25), 0 0 0 1px rgba(255, 215, 255, 0.6);
            padding: 2rem 1.9rem;  /* medium inner spacing */
            transition: all 0.3s;
            animation: cardRise 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardRise {
            0% { opacity: 0; transform: scale(0.94) translateY(30px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        .pms-card:hover {
            background: rgba(255, 255, 255, 0.9);
            border-color: #ffffff;
            box-shadow: 0 32px 55px -12px #a07bf0, 0 0 0 2px rgba(255, 230, 255, 0.8);
        }

        /* --- brand – medium typography, same bold gradient --- */
        .brand-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .pms-logo-svg {
            color: #7d4be7;
            width: 52px;   /* reduced from 58 → medium */
            height: auto;
            filter: drop-shadow(0 8px 15px rgba(170, 90, 255, 0.3));
            transition: transform 0.25s;
        }
        .pms-logo-svg:hover {
            transform: scale(1.06) rotate(1.5deg);
        }

        .pms-brand-name {
            font-size: 2.2rem;   /* from 2.6 → medium */
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(145deg, #3f2799, #6236c9, #8550e8, #b17cfd);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-top: 0.1rem;
            line-height: 1.1;
            text-shadow: 0 2px 12px rgba(175, 100, 255, 0.2);
        }

        .pms-tagline {
            font-size: 0.9rem;   /* medium */
            font-weight: 700;
            color: #6236a8;
            letter-spacing: 0.6px;
            margin-top: 0.2rem;
            background: rgba(255, 255, 255, 0.6);
            padding: 0.3rem 1.5rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 5px 12px rgba(180, 130, 255, 0.1);
        }

        /* headings – medium */
        .pms-welcome {
            font-size: 1.7rem;   /* from 2rem → medium */
            font-weight: 750;
            margin-bottom: 0.2rem;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #2b1c4a, #462b74);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .pms-sub {
            color: #5a41a0;
            font-weight: 600;
            font-size: 0.95rem;   /* medium */
            margin-bottom: 1.5rem;
            border-left: 6px solid #b28dff;
            padding-left: 0.9rem;
            background: linear-gradient(to right, rgba(200, 170, 255, 0.15), transparent);
        }

        /* form labels – medium, bold */
        .form-label {
            font-weight: 800;
            color: #392a62;
            font-size: 0.8rem;   /* slightly medium */
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-label i {
            color: #9a6eff;
            font-size: 0.9rem;
        }

        .form-control {
            border: 2.5px solid #e8deff;
            border-radius: 32px;
            padding: 0.8rem 1.4rem;  /* medium padding */
            font-size: 0.95rem;
            font-weight: 600;
            background: white;
            transition: all 0.2s;
            box-shadow: 0 4px 10px rgba(162, 113, 255, 0.05);
            color: #1d1238;
        }

        .form-control:focus {
            border-color: #a07aff;
            background: white;
            box-shadow: 0 0 0 8px rgba(154, 110, 255, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #b6a5d6;
            font-weight: 500;
            opacity: 0.9;
        }

        .input-group {
            border-radius: 32px;
        }
        .input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-right: none;
        }
        .input-group-text {
            background: white;
            border: 2.5px solid #e8deff;
            border-left: none;
            border-radius: 0 32px 32px 0;
            padding: 0 1.4rem;
            color: #7a51d4;
            font-size: 1.3rem;
        }
        .input-group-text:hover {
            background: #f9f2ff;
            color: #5d31b0;
        }

        /* remember – medium */
        .form-check-input {
            border: 2.5px solid #d7c2ff;
            border-radius: 7px;
            width: 1.2em;
            height: 1.2em;
            margin-top: 0.1em;
        }
        .form-check-input:checked {
            background-color: #8f5aff;
            border-color: #8f5aff;
            box-shadow: 0 0 0 4px rgba(170, 130, 255, 0.2);
        }
        .form-check-label {
            font-weight: 700;
            color: #3b2c62;
            font-size: 0.9rem;
        }
        .forgot-link {
            font-weight: 700;
            color: #764ce0;
            font-size: 0.85rem;
            border-bottom: 2px solid transparent;
        }
        .forgot-link:hover {
            border-bottom-color: #764ce0;
        }

        /* --- BUTTON: medium size, still prismatic --- */
        .btn-pms {
            background: linear-gradient(125deg, #6f40d0, #9662f9, #b280ff, #c69cff);
            background-size: 300% 300%;
            border: none;
            border-radius: 50px !important;
            padding: 0.9rem 1.6rem;  /* medium */
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: 0.8px;
            color: white;
            box-shadow: 0 16px 28px -8px #845adf, 0 0 12px rgba(219, 190, 255, 0.5);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: gradientWash 7s ease infinite;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }

        @keyframes gradientWash {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .btn-pms i {
            font-size: 1.2rem;
            transition: transform 0.25s;
        }
        .btn-pms:hover {
            background: linear-gradient(125deg, #5e38b0, #7e55d6, #9e78f0);
            transform: translateY(-3px);
            box-shadow: 0 24px 40px -10px #7c4ed2;
            animation: none;
        }
        .btn-pms:hover i {
            transform: translateX(8px);
        }
        .btn-pms::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 200%;
            height: 200%;
            background: rgba(255,255,255,0.3);
            transform: rotate(30deg);
            transition: all 0.5s;
            opacity: 0;
        }
        .btn-pms:hover::after {
            opacity: 1;
            left: 100%;
        }

        /* alerts – medium */
        .alert {
            border: none;
            border-radius: 26px;
            padding: 1rem 1.3rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border-left: 8px solid #b28eff;
            box-shadow: 0 10px 20px rgba(154, 108, 255, 0.1);
            font-weight: 600;
            margin-bottom: 1.6rem;
            font-size: 0.9rem;
        }
        .alert-danger { border-left-color: #f07c8b; background: rgba(255, 241, 243, 0.8); }
        .alert-success { border-left-color: #58cf9a; }
        .error-badge {
            background: rgba(212, 180, 255, 0.18);
            border-radius: 20px;
            padding: 0.5rem 0.9rem;
            margin-bottom: 0.6rem;
            border: 1px solid rgba(202, 152, 255, 0.4);
            backdrop-filter: blur(4px);
            font-size: 0.9rem;
        }

        /* create account – medium */
        .create-section {
            margin-top: 1.8rem;
            padding-top: 1.1rem;
            border-top: 3px dashed #d7befc;
            text-align: center;
        }
        .create-label {
            font-weight: 700;
            color: #50407a;
            font-size: 0.95rem;
        }
        .create-link {
            font-weight: 800;
            font-size: 1.05rem;
            background: linear-gradient(145deg, #6441c2, #9772f0);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-left: 6px;
            border-bottom: 4px solid #b696ff;
            padding-bottom: 2px;
        }
        .create-link:hover { border-bottom-color: #6f4bcb; }

        .pms-footer {
            margin-top: 1.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: #8070aa;
            letter-spacing: 1.2px;
            display: flex;
            justify-content: center;
            gap: 1.2rem;
        }

        /* responsive touches */
        @media (max-width: 460px) {
            .pms-login-container { max-width: 360px; }
            .pms-card { padding: 1.6rem 1.3rem; }
            .pms-brand-name { font-size: 1.9rem; }
        }

        /* clean legacy */
        .authentication-wrapper, .container-xxl { all: unset; display: block; width: 100%; }
        .pms-orb, .pms-particle { display: none; }
        .bg-white { background: transparent; }
        .fw-extra { font-weight: 800; }
    </style>
</head>
<body>

    <!-- ========== 🫧🫧🫧 BUBBLE UNIVERSE – PURPLE & WHITE SPECTACLE 🫧🫧🫧 ========== -->
    <div class="bubble-universe">
        <div class="bubble b1"><i class="fas fa-tasks"></i></div>
        <div class="bubble b2"><i class="fas fa-rocket"></i></div>
        <div class="bubble b3"><i class="fas fa-code-branch"></i></div>
        <div class="bubble b4"><i class="fas fa-project-diagram"></i></div>
        <div class="bubble b5"><i class="fas fa-clipboard-check"></i></div>
        <div class="bubble b6"><i class="fas fa-users"></i></div>
        <div class="bubble b7"><i class="fas fa-chart-pie"></i></div>
        <div class="bubble b8"><i class="fas fa-shield-alt"></i></div>
        <div class="bubble b9"><i class="fas fa-cogs"></i></div>
        <div class="bubble b10"><i class="fas fa-layer-group"></i></div>
        <div class="bubble b11"><i class="fas fa-flag"></i></div>
        <div class="bubble b12"><i class="fas fa-calendar-alt"></i></div>
        <div class="bubble b13"><i class="fas fa-puzzle-piece"></i></div>
        <div class="bubble b14"><i class="fas fa-magic"></i></div>
        <div class="bubble b15"><i class="fas fa-gem"></i></div>
    </div>

    <!-- sparkles -->
    <div class="sparkle s1"></div>
    <div class="sparkle s2"></div>
    <div class="sparkle s3"></div>
    <div class="sparkle s4"></div>
    <div class="sparkle s5"></div>
    <div class="sparkle s6"></div>
    <div class="sparkle s7"></div>
    <div class="sparkle s8"></div>

    <!-- MAIN LOGIN CARD – MEDIUM, EVERYTHING SAME -->
    <div class="pms-login-container">
        <div class="pms-card">

            <!-- BRAND -->
            <div class="brand-area">
                <span class="app-brand-logo pms-logo-svg">
                    <svg width="52" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <path d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z" id="path-1"></path>
                            <path d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225Z" id="path-3"></path>
                            <path d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.05231404e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429Z" id="path-4"></path>
                            <path d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333Z" id="path-5"></path>
                        </defs>
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g transform="translate(-27.000000, -15.000000)">
                                <g transform="translate(27.000000, 15.000000)">
                                    <g transform="translate(0.000000, 8.000000)">
                                        <mask id="mask2" fill="white"><use xlink:href="#path-1"></use></mask>
                                        <use fill="#8a5af0" xlink:href="#path-1"></use>
                                        <g mask="url(#mask2)"><use fill="#8a5af0" xlink:href="#path-3"></use><use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use></g>
                                        <g mask="url(#mask2)"><use fill="#8a5af0" xlink:href="#path-4"></use><use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use></g>
                                    </g>
                                    <g transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000)">
                                        <use fill="#8a5af0" xlink:href="#path-5"></use>
                                        <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </svg>
                </span>
                <span class="pms-brand-name">PMS</span>
                <span class="pms-tagline">✨ prismatic · agile · secure</span>
            </div>

            <h1 class="pms-welcome">Welcome back</h1>
            <div class="pms-sub">
                <i class="fas fa-shield-virus me-2" style="color: #9b70ff;"></i> purple‑white enterprise portal
            </div>

            <!-- ALERTS – blade preserved -->
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-start" role="alert">
                    <i class="fas fa-check-circle me-3 fs-5" style="color: #1e8a5e;"></i>
                    <div class="fw-semibold">{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger p-3" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-exclamation-triangle me-2 fs-5" style="color: #b13e4a;"></i>
                        <strong class="fs-6 fw-bold">Authentication requires attention</strong>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div>
                        @foreach ($errors->all() as $error)
                            @if (str_contains($error, 'active but login is blocked'))
                                <div class="error-badge d-flex align-items-start">
                                    <i class="fas fa-user-lock me-2 mt-1" style="color: #b45309;"></i>
                                    <div><strong class="fw-bold">{{ $error }}</strong><br /><small class="text-muted">Admin has disabled login access.</small></div>
                                </div>
                            @elseif (str_contains($error, 'account is inactive') && !str_contains($error, 'and login is blocked'))
                                <div class="error-badge d-flex align-items-start">
                                    <i class="fas fa-user-slash me-2 mt-1" style="color: #306cbe;"></i>
                                    <div><strong class="fw-bold">{{ $error }}</strong><br /><small class="text-muted">Employment status inactive.</small></div>
                                </div>
                            @elseif (str_contains($error, 'inactive and login is blocked'))
                                <div class="error-badge d-flex align-items-start">
                                    <i class="fas fa-ban me-2 mt-1" style="color: #5b5281;"></i>
                                    <div><strong class="fw-bold">{{ $error }}</strong><br /><small class="text-muted">Access blocked & inactive.</small></div>
                                </div>
                            @elseif (str_contains($error, 'credentials do not match') || str_contains($error, 'auth.failed'))
                                <div class="error-badge d-flex align-items-start">
                                    <i class="fas fa-key me-2 mt-1" style="color: #cc4b5a;"></i>
                                    <div><strong class="fw-bold">Invalid email or password</strong><br /><small class="text-muted">Check credentials and try again.</small></div>
                                </div>
                            @else
                                <div class="error-badge d-flex align-items-start">
                                    <i class="fas fa-exclamation-circle me-2 mt-1" style="color: #7f5af0;"></i>
                                    <div><strong class="fw-bold">{{ $error }}</strong></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- LOGIN FORM -->
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label"><i class="fas fa-envelope"></i> PMS account</label>
                    <input type="text" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="user@project.office" autocomplete="email">
                    @if ($errors->has('email'))
                        <div class="mt-2 small fw-semibold" style="color: #bc3b4c;">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label"><i class="fas fa-lock"></i> Password</label>
                        <a href="auth-forgot-password-basic.html" class="forgot-link small">Forgot?</a>
                    </div>
                    <div class="input-group input-group-merge">
                        <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••" autocomplete="current-password">
                        <span class="input-group-text cursor-pointer" id="toggle-password">
                            <i class="bx bx-hide" id="toggle-icon" style="font-size: 1.4rem;"></i>
                        </span>
                    </div>
                    @if ($errors->has('password'))
                        <div class="mt-2 small fw-semibold" style="color: #bc3b4c;">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                        <label class="form-check-label fw-semibold" for="remember_me">Keep me signed in</label>
                    </div>
                    <!-- <a href="{{ route('register') }}" class="fw-bold" style="color: #6f48d0; border-bottom: 3px solid #c7adff; padding-bottom: 3px;">Request access</a> -->
                </div>

                <button type="submit" class="btn-pms">
                    <span>ACCESS PMS</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- enterprise note – medium, bubble style -->
            <div class="d-flex align-items-center mt-4 p-3 rounded-5" style="background: rgba(235, 220, 255, 0.4); border: 2px solid rgba(255, 255, 255, 0.8); backdrop-filter: blur(8px);">
                <i class="fas fa-crystal-ball fs-4 me-3" style="color: #9a6cf1;"></i>
                <div>
                    <span class="fw-bold" style="color: #34255a; font-size: 0.95rem;">PMS prismatic · v5.0</span>
                    <span class="d-block small fw-semibold" style="color: #6850a8; font-size: 0.75rem;">✨ 24/7 PMO · bubble secure</span>
                </div>
            </div>

            <!-- CREATE ACCOUNT -->
            <div class="create-section">
                <span class="create-label">🪽 New to PMS prismatic?</span>
                <a href="{{ route('register') }}" class="create-link">
                    Create an account <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="pms-footer">
                <span>PMS</span>
                <span>◆</span>
                <span>agile+</span>
                <span>◆</span>
                <span>purple white</span>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        (function() {
            'use strict';
            const togglePassword = document.getElementById('toggle-password');
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggle-icon');
            if (togglePassword && passwordField && toggleIcon) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    toggleIcon.classList.toggle('bx-show');
                    toggleIcon.classList.toggle('bx-hide');
                });
            }
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-light)');
                alerts.forEach(function(alertEl) {
                    if (alertEl && typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        const bsAlert = new bootstrap.Alert(alertEl);
                        bsAlert.close();
                    } else {
                        alertEl.style.transition = 'opacity 0.3s';
                        alertEl.style.opacity = '0';
                        setTimeout(() => alertEl.style.display = 'none', 350);
                    }
                });
            }, 7000);
        })();
    </script>

    <style>
        body .authentication-wrapper, body .container-xxl { all: unset; display: block; }
        .btn-check:focus+.btn, .btn:focus { box-shadow: none; }
        .pms-card .btn-close { filter: brightness(0.9); background: rgba(255,255,255,0.8); border-radius: 50%; }
    </style>
</body>
</html>
