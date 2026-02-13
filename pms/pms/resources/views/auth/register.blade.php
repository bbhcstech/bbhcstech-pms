<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Create account · OfficeCore</title>

  <!-- Google Fonts (Inter) – all weights now BOLD -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <!-- Using 600,700,800 for extra boldness – everything appears bold & crisp -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet" />

  <!-- Box Icons -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <style>
    /* ------------------------------------------------------------------------
       PURPLE & WHITE · BOLD FONTS · BUBBLE BACKGROUND · NO FUNCTIONAL CHANGE
       Everything is now bold, readable, luxurious purple/white combo
    ------------------------------------------------------------------------ */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      /* Purple & white base – soft gradient */
      background: radial-gradient(circle at 10% 30%, #faf5ff, #f3eaff);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.2rem;
      position: relative;
      overflow-x: hidden;
    }

    /* -------------------- BUBBLE FIELD – PURPLE TONES, WHITE SHIMMER ------------------- */
    .bubble-field {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      overflow: hidden;
    }

    .bubble {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(6px);
      box-shadow: 0 15px 30px rgba(147, 51, 234, 0.15);
      animation: floatBubble 16s infinite alternate ease-in-out;
    }

    @keyframes floatBubble {
      0% { transform: translate(0, 0) scale(1); opacity: 0.5; }
      100% { transform: translate(25px, -20px) scale(1.15); opacity: 0.85; }
    }

    /* Purple-centric bubbles – white + purple nuances */
    .bubble1 {
      width: 280px; height: 280px; top: -80px; right: -50px;
      background: radial-gradient(circle at 20% 30%, #ffffff, #e9d5ff);
      animation-duration: 19s;
    }
    .bubble2 {
      width: 350px; height: 350px; bottom: -90px; left: -70px;
      background: radial-gradient(circle at 70% 60%, #ffffff, #d8b4fe);
      animation-duration: 23s;
      animation-delay: 1s;
    }
    .bubble3 {
      width: 190px; height: 190px; top: 12%; left: 12%;
      background: radial-gradient(circle at 40% 70%, #fff, #f3e8ff);
      animation-duration: 18s;
      animation-delay: 0.5s;
    }
    .bubble4 {
      width: 240px; height: 240px; bottom: 18%; right: 8%;
      background: radial-gradient(circle at 65% 35%, #fff, #e2d1ff);
      animation-duration: 21s;
      animation-delay: 2.5s;
    }
    .bubble5 {
      width: 150px; height: 150px; top: 70%; left: 25%;
      background: radial-gradient(circle at 30% 80%, #ffffff, #f5edff);
      animation-duration: 17s;
      animation-delay: 1.2s;
    }
    .bubble6 {
      width: 210px; height: 210px; top: 15%; right: 18%;
      background: radial-gradient(circle at 75% 25%, #fff, #eeddff);
      animation-duration: 20s;
      animation-delay: 3s;
    }
    .bubble7 {
      width: 130px; height: 130px; bottom: 15%; left: 40%;
      background: radial-gradient(circle at 55% 45%, #fff, #f4eaff);
      animation-duration: 22s;
      animation-delay: 0.8s;
    }
    .bubble8 {
      width: 180px; height: 180px; top: 40%; right: 30%;
      background: radial-gradient(circle at 80% 15%, #ffffff, #f1e6ff);
      animation-duration: 24s;
      animation-delay: 1.8s;
    }
    .bubble9 {
      width: 110px; height: 110px; bottom: 30%; right: 45%;
      background: radial-gradient(circle at 45% 55%, #fff, #faf0ff);
      animation-duration: 15s;
      animation-delay: 2.2s;
    }

    /* soft purple ambient glow – dreamy */
    .ambient-glow {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: radial-gradient(circle at 30% 50%, rgba(192, 132, 252, 0.08) 0%, transparent 45%),
                  radial-gradient(circle at 80% 70%, rgba(168, 85, 247, 0.06) 0%, transparent 50%);
      z-index: 1;
      pointer-events: none;
      animation: softGlow 12s infinite alternate;
    }
    @keyframes softGlow { 0% { opacity: 0.5; } 100% { opacity: 1; } }

    /* whisper grid – barely visible purple */
    .whisper-grid {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background-image:
        linear-gradient(rgba(147, 51, 234, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(147, 51, 234, 0.04) 1px, transparent 1px);
      background-size: 32px 32px;
      z-index: 1; pointer-events: none;
    }

    /* -------------------- MAIN CARD – WHITE WITH PURPLE SHADOWS -------------------- */
    .auth-wrapper {
      position: relative;
      z-index: 30;
      width: 100%;
      max-width: 460px;
      margin: 0 auto;
      animation: cardRise 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes cardRise {
      0% { opacity: 0; transform: translateY(20px) scale(0.96); }
      100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    .auth-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-radius: 40px;
      box-shadow:
        0 25px 45px -18px rgba(107, 33, 168, 0.24),
        0 10px 28px -6px rgba(126, 34, 206, 0.16);
      padding: 2.2rem 2rem;
      border: 1.5px solid rgba(255, 255, 255, 0.8);
      transition: box-shadow 0.3s ease;
    }

    .auth-card:hover {
      box-shadow: 0 32px 55px -20px rgba(139, 92, 246, 0.28);
      background: rgba(255, 255, 255, 0.98);
    }

    /* -------------------- BRAND – PURPLE & WHITE, BOLDER -------------------- */
    .app-brand {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 1.4rem;
      text-decoration: none;
    }

    .app-brand-link {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }

    .brand-logo-svg {
      width: 38px;
      height: auto;
      color: #8b5cf6;    /* vivid purple */
      filter: drop-shadow(0 3px 6px rgba(139, 92, 246, 0.22));
      transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .app-brand:hover .brand-logo-svg {
      transform: rotate(5deg) scale(1.04);
    }

    .brand-text {
      font-size: 1.7rem;
      font-weight: 800;   /* ultra bold */
      letter-spacing: -0.03em;
      background: linear-gradient(145deg, #4c1d95, #6d28d9);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .brand-text span {
      -webkit-text-fill-color: #a78bfa;  /* light purple */
      font-weight: 800;
    }

    /* -------------------- TYPOGRAPHY – ALL BOLD, CRYSTAL CLEAR -------------------- */
    .auth-title {
      font-size: 1.9rem;
      font-weight: 800;    /* max boldness */
      line-height: 1.1;
      margin-bottom: 0.2rem;
      color: #2e1065;      /* deep purple */
      letter-spacing: -0.02em;
      text-shadow: 0 1px 2px rgba(139,92,246,0.08);
    }

    .auth-subtitle {
      font-size: 1rem;
      font-weight: 700;    /* bold */
      color: #6b21a8;      /* strong purple */
      margin-bottom: 2rem;
      border-left: 5px solid #c084fc;
      padding-left: 0.9rem;
      background: linear-gradient(to right, #faf5ff, transparent);
    }

    /* -------------------- FORM LABELS – BOLDER & PURPLE -------------------- */
    .form-label {
      display: block;
      font-size: 0.8rem;
      font-weight: 700;    /* bold */
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #5b21b6;      /* purple */
      margin-bottom: 0.45rem;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 16px;
      color: #a78bfa;      /* soft purple */
      font-size: 1.2rem;
      pointer-events: none;
      transition: color 0.2s;
    }

    .form-control {
      width: 100%;
      padding: 0.9rem 1rem 0.9rem 3rem;
      font-size: 0.98rem;
      font-weight: 600;    /* semi-bold but visible as bold */
      border: 2px solid #f1e6ff;
      border-radius: 28px;
      background: white;
      transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1);
      color: #2d1b4e;
      box-shadow: 0 2px 4px rgba(139,92,246,0.04);
    }

    .form-control:focus {
      border-color: #a78bfa;
      background: white;
      box-shadow: 0 0 0 5px rgba(168, 85, 247, 0.1), 0 4px 10px rgba(139,92,246,0.1);
      outline: none;
    }

    .form-control::placeholder {
      color: #c7b2e2;
      font-weight: 600;    /* bold placeholder as well */
      opacity: 0.8;
    }

    /* password toggle – purple */
    .password-toggle-icon {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #8b5cf6;
      font-size: 1.3rem;
      cursor: pointer;
      z-index: 20;
      transition: color 0.2s, transform 0.2s;
    }

    .password-toggle-icon:hover {
      color: #6d28d9;
      transform: translateY(-50%) scale(1.1);
    }

    /* -------------------- CHECKBOX – PURPLE ACCENTS -------------------- */
    .form-check {
      display: flex;
      align-items: center;
      gap: 0.7rem;
      margin-top: 0.6rem;
      margin-bottom: 1.8rem;
    }

    .form-check-input {
      width: 1.25rem;
      height: 1.25rem;
      border: 2.5px solid #e9d5ff;
      border-radius: 6px;
      background: white;
      appearance: none;
      transition: all 0.15s;
      cursor: pointer;
      position: relative;
    }

    .form-check-input:checked {
      background: #8b5cf6;
      border-color: #8b5cf6;
    }

    .form-check-input:checked::after {
      content: '\2713';
      position: absolute;
      color: white;
      font-size: 0.9rem;
      font-weight: 800;
      top: -2px;
      left: 3px;
    }

    .form-check-input:focus {
      border-color: #8b5cf6;
      box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.12);
    }

    .form-check-label {
      font-size: 0.93rem;
      font-weight: 700;    /* bold */
      color: #4c1d95;
    }

    .form-check-label a {
      color: #7e22ce;
      text-decoration: none;
      font-weight: 800;
      border-bottom: 2px dotted #c084fc;
      transition: border 0.2s, color 0.2s;
    }

    .form-check-label a:hover {
      color: #5b21b6;
      border-bottom: 2px solid #8b5cf6;
    }

    /* -------------------- BUTTON – PURPLE GRADIENT, BOLD TEXT -------------------- */
    .btn {
      background: linear-gradient(145deg, #7e22ce, #8b5cf6);
      border: none;
      border-radius: 40px;
      padding: 0.95rem 1.8rem;
      font-weight: 800;    /* extra bold */
      font-size: 1.05rem;
      letter-spacing: 0.3px;
      color: white;
      width: 100%;
      cursor: pointer;
      transition: all 0.25s ease;
      box-shadow: 0 12px 22px -8px rgba(124, 58, 237, 0.4);
      position: relative;
      overflow: hidden;
      text-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.7s ease;
    }

    .btn:hover {
      background: linear-gradient(145deg, #9333ea, #7e22ce);
      transform: translateY(-3px);
      box-shadow: 0 20px 28px -10px #8b5cf6b3;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn:active {
      transform: translateY(2px);
      box-shadow: 0 8px 18px -6px #6d28d9;
    }

    /* -------------------- FOOTER – PURPLE, BOLDER -------------------- */
    .auth-footer {
      text-align: center;
      margin-top: 1.8rem;
      font-size: 0.95rem;
      font-weight: 700;    /* bold */
      color: #5b21b6;
    }

    .auth-footer a {
      color: #6d28d9;
      font-weight: 800;
      text-decoration: none;
      margin-left: 6px;
      border-bottom: 2px solid transparent;
      transition: border 0.2s, color 0.2s;
    }

    .auth-footer a:hover {
      color: #8b5cf6;
      border-bottom-color: #8b5cf6;
    }

    /* decorative micro divider – purple details */
    .micro-divider {
      margin-top: 1.5rem;
      font-size: 0.72rem;
      font-weight: 700;
      color: #7c3aed;
      display: flex;
      justify-content: center;
      gap: 18px;
      border-top: 2px solid #f3e8ff;
      padding-top: 1.2rem;
    }

    .micro-divider i {
      font-size: 0.95rem;
      vertical-align: middle;
      color: #a78bfa;
    }

    /* -------------------- BOLD EVERYTHING: ADDITIONAL -------------------- */
    .brand-text, .auth-title, .auth-subtitle, .form-label, .form-check-label, .auth-footer, .micro-divider {
      font-weight: 700;
    }
    /* force all text elements to be bold */
    .auth-card, .auth-card * {
      font-weight: 600;
    }
    h2, p, a, span, label, div, input, button {
      font-weight: 600;
    }

    /* error styling – preserve */
    .input-error { font-size: 0.7rem; color: #b91c1c; margin-top: 0.3rem; padding-left: 0.8rem; font-weight: 700; }
    x-input-error { display: none; }
    .mt-2 { margin-top: 0.25rem; }

    /* responsive */
    @media (max-width: 480px) {
      .auth-card { padding: 1.8rem 1.5rem; }
      .brand-text { font-size: 1.5rem; }
      .auth-title { font-size: 1.7rem; }
    }

    /* subtle animation for button */
    .float-subtle {
      animation: softHover 5s infinite ease-in-out;
    }
    @keyframes softHover {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-2px); }
    }
  </style>
</head>
<body>

  <!-- PURPLE & WHITE BUBBLE BACKGROUND – DREAMY, BOLD VIBE -->
  <div class="bubble-field">
    <div class="bubble bubble1"></div>
    <div class="bubble bubble2"></div>
    <div class="bubble bubble3"></div>
    <div class="bubble bubble4"></div>
    <div class="bubble bubble5"></div>
    <div class="bubble bubble6"></div>
    <div class="bubble bubble7"></div>
    <div class="bubble bubble8"></div>
    <div class="bubble bubble9"></div>
  </div>
  <div class="ambient-glow"></div>
  <div class="whisper-grid"></div>

  <!-- CARD – ALL PURPLE/WHITE, SUPER BOLD TYPOGRAPHY -->
  <div class="auth-wrapper">
    <div class="auth-card">

      <!-- BRAND – PURPLE DOMINANT -->
      <a href="{{ route('login') }}" class="app-brand app-brand-link">
        <span class="brand-logo">
          <svg class="brand-logo-svg" width="42" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <defs>
              <path d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z" id="path-1"></path>
              <path d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z" id="path-3"></path>
              <path d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.052314e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429 Z" id="path-4"></path>
              <path d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333 Z" id="path-5"></path>
            </defs>
            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
              <g transform="translate(-27.000000, -15.000000)">
                <g transform="translate(27.000000, 15.000000)">
                  <g transform="translate(0.000000, 8.000000)">
                    <mask id="mask-2" fill="white"><use xlink:href="#path-1"></use></mask>
                    <use fill="currentColor" xlink:href="#path-1"></use>
                    <g mask="url(#mask-2)"><use fill="currentColor" xlink:href="#path-3"></use><use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use></g>
                    <g mask="url(#mask-2)"><use fill="currentColor" xlink:href="#path-4"></use><use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use></g>
                  </g>
                  <g transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) ">
                    <use fill="currentColor" xlink:href="#path-5"></use>
                    <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                  </g>
                </g>
              </g>
            </g>
          </svg>
        </span>
        <span class="brand-text">Office<span>Core</span></span>
      </a>

      <!-- BOLD TITLES – DEEP PURPLE -->
      <h2 class="auth-title">Create account</h2>
      <p class="auth-subtitle">Welcome to your workspace</p>

      <!-- FORM – SAME EXACT FUNCTIONALITY, ONLY VISUAL -->
      <form id="formAuthentication" method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <!-- NAME -->
        <div class="form-group">
          <label for="name" class="form-label">Full name</label>
          <div class="input-wrapper">
            <i class='bx bx-user-circle input-icon'></i>
            <input class="form-control" type="text" id="name" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="John Mitchell">
          </div>
          <div class="input-error mt-2" style="display: none;">@error('name') {{ $message }} @enderror</div>
        </div>

        <!-- EMAIL -->
        <div class="form-group">
          <label for="email" class="form-label">Work email</label>
          <div class="input-wrapper">
            <i class='bx bx-envelope input-icon'></i>
            <input type="email" name="email" class="form-control" id="email" required placeholder="you@company.com">
          </div>
          <x-input-error :messages="$errors->get('email')" class="mt-2" style="display: none;" />
        </div>

        <!-- PASSWORD -->
        <div class="form-group password-group">
          <label for="yourPassword" class="form-label">Password</label>
          <div class="input-wrapper">
            <i class='bx bx-lock-alt input-icon'></i>
            <input type="password" name="password" class="form-control" id="yourPassword" placeholder="9+ characters" required>
            <i class='bx bx-hide password-toggle-icon' id="togglePassword" onclick="togglePasswordVisibility('yourPassword', this)"></i>
          </div>
          <x-input-error :messages="$errors->get('password')" class="mt-2" style="display: none;" />
        </div>

        <!-- CONFIRM PASSWORD -->
        <div class="form-group password-group">
          <label for="password_confirmation" class="form-label">Confirm password</label>
          <div class="input-wrapper">
            <i class='bx bx-lock input-icon'></i>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Re-enter password" required autocomplete="new-password">
            <i class='bx bx-hide password-toggle-icon' id="toggleConfirm" onclick="togglePasswordVisibility('password_confirmation', this)"></i>
          </div>
          <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" style="display: none;" />
        </div>

        <!-- TERMS (PURPLE) -->
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" checked>
          <label class="form-check-label" for="terms-conditions">
            I agree to <a href="javascript:void(0);">Privacy</a> and <a href="javascript:void(0);">Terms</a>
          </label>
        </div>

        <!-- BUTTON – BOLD PURPLE -->
        <button type="submit" class="btn float-subtle">
          <span style="display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class='bx bx-log-in-circle' style="font-size: 1.2rem;"></i> Create account
          </span>
        </button>
      </form>

      <!-- FOOTER -->
      <div class="auth-footer">
        <span>Already have an account?</span>
        <a href="{{ route('login') }}">Sign in →</a>
      </div>

      <!-- DECORATIVE -->
      <div class="micro-divider">
        <span><i class='bx bx-shield-quarter'></i> AES‑256</span>
        <span><i class='bx bx-globe'></i> SSO ready</span>
      </div>
    </div>
  </div>

  <!-- PASSWORD TOGGLE – ORIGINAL CODE, UNTOUCHED -->
  <script>
    window.togglePasswordVisibility = function(fieldId, iconElement) {
      const input = document.getElementById(fieldId);
      if (input.type === "password") {
        input.type = "text";
        iconElement.classList.remove('bx-hide');
        iconElement.classList.add('bx-show');
      } else {
        input.type = "password";
        iconElement.classList.remove('bx-show');
        iconElement.classList.add('bx-hide');
      }
    }

    // subtle purple focus effect
    document.querySelectorAll('.form-control').forEach(input => {
      input.addEventListener('focus', function(e) {
        this.parentElement.querySelector('.input-icon')?.style.setProperty('color', '#7e22ce');
      });
      input.addEventListener('blur', function(e) {
        this.parentElement.querySelector('.input-icon')?.style.setProperty('color', '#a78bfa');
      });
    });
  </script>

  <!-- PRESERVE LARAVEL DIRECTIVES -->
  <style>
    x-input-error { display: none; }
  </style>
</body>
</html>
