<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password</title>
  
<link rel="stylesheet" href="css/default.css">
<link rel="stylesheet" href="css/password.css">
</head>
<body>

<div class="container">

  <!-- Progress dots -->
  <div class="progress-dots">
    <div class="dot active" id="dot-1"></div>
    <div class="dot" id="dot-2"></div>
    <div class="dot" id="dot-3"></div>
  </div>

  <!-- ── STEP 1: Enter email ── -->
  <div class="step active" id="step-1">
    <div class="icon-circle orange">
      <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
    </div>

    <h1>Forgot Password?</h1>
    <p class="subtitle">No worries! Enter your email and we'll send you a reset code.</p>

    <div class="field-group">
      <label for="email">Email Address</label>
      <div class="input-wrapper">
        <input type="email" id="email" placeholder="john@example.com" />
        <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
      </div>
    </div>

    <button class="btn-main" onclick="goToStep(2)">Send Reset Code</button>
    <br/>
    <a class="back-link" href="signin.html">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Sign In
    </a>
  </div>

  <!-- ── STEP 2: Enter OTP ── -->
  <div class="step" id="step-2">
    <div class="icon-circle orange">
      <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>

    <h1>Check Your Email</h1>
    <p class="subtitle">We sent a 4-digit code to <span id="email-display">your email</span>. Enter it below.</p>

    <div class="otp-row">
      <input type="text" maxlength="1" class="otp-input" inputmode="numeric" />
      <input type="text" maxlength="1" class="otp-input" inputmode="numeric" />
      <input type="text" maxlength="1" class="otp-input" inputmode="numeric" />
      <input type="text" maxlength="1" class="otp-input" inputmode="numeric" />
    </div>

    <p class="resend-row">
      Didn't receive it?
      <button id="resend-btn" onclick="startTimer()" disabled>Resend in <span id="timer">60s</span></button>
    </p>

    <button class="btn-main" onclick="goToStep(3)">Verify Code</button>
    <br/>
    <button class="back-link" onclick="goToStep(1)">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      Go back
    </button>
  </div>

  <!-- ── STEP 3: New password ── -->
  <div class="step" id="step-3">
    <div class="icon-circle orange">
      <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>

    <h1>New Password</h1>
    <p class="subtitle">Create a strong password you haven't used before.</p>

    <div class="field-group">
      <label for="new-password">New Password</label>
      <div class="input-wrapper">
        <input type="password" id="new-password" placeholder="Enter new password" oninput="checkStrength(this.value)" />
        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <div class="strength-bar">
        <span id="s1"></span><span id="s2"></span><span id="s3"></span><span id="s4"></span>
      </div>
      <div class="strength-label" id="strength-label"></div>
    </div>

    <div class="field-group">
      <label for="confirm-password">Confirm Password</label>
      <div class="input-wrapper">
        <input type="password" id="confirm-password" placeholder="Repeat new password" />
        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
    </div>

    <button class="btn-main" onclick="goToStep(4)">Reset Password</button>
    <br/>
    <button class="back-link" onclick="goToStep(2)">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      Go back
    </button>
  </div>

  <!-- ── STEP 4: Success ── -->
  <div class="step" id="step-4">
    <div class="icon-circle green">
      <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    </div>

    <h1>Password Reset!</h1>
    <p class="subtitle">Your password has been changed successfully. You can now sign in with your new password.</p>

    <button class="btn-main" onclick="location.href='signin.html'">Go to Sign In</button>
  </div>

</div>

<script>
  let timerInterval;

  function goToStep(n) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById('step-' + n).classList.add('active');

    // Update progress dots
    const dots = document.querySelectorAll('.dot');
    const stepMap = { 1: 0, 2: 1, 3: 2, 4: 2 };\,i
    dots.forEach((dot, i) => {
      dot.classList.remove('active', 'done');
      const active = stepMap[n];
      if (i < active) dot.classList.add('done');
      else if (i === active) dot.classList.add('active');
    });

    // Show email in step 2
    if (n === 2) {
      const email = document.getElementById('email').value || 'your email';
      document.getElementById('email-display').textContent = email;
      startTimer();
      setTimeout(() => document.querySelector('.otp-input').focus(), 100);
    }
  }

  // OTP auto-advance
  document.querySelectorAll('.otp-input').forEach((input, i, all) => {
    input.addEventListener('input', () => {
      if (input.value && i < all.length - 1) all[i + 1].focus();
    });
    input.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !input.value && i > 0) all[i - 1].focus();
    });
  });

  // Countdown timer for resend
  function startTimer() {
    clearInterval(timerInterval);
    const btn = document.getElementById('resend-btn');
    const timerEl = document.getElementById('timer');
    let t = 60;
    btn.disabled = true;
    timerEl.textContent = t + 's';

    timerInterval = setInterval(() => {
      t--;
      timerEl.textContent = t + 's';
      if (t <= 0) {
        clearInterval(timerInterval);
        btn.disabled = false;
        btn.innerHTML = 'Resend code';
      }
    }, 1000);
  }

  // Password strength checker
  function checkStrength(val) {
    const bars = [document.getElementById('s1'), document.getElementById('s2'),
                  document.getElementById('s3'), document.getElementById('s4')];
    const label = document.getElementById('strength-label');

    bars.forEach(b => b.style.background = '');

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors = ['#e74c3c', '#e67e22', '#f1c40f', '#27ae60'];
    const labels = ['Weak', 'Fair', 'Good', 'Strong'];

    if (val.length === 0) { label.textContent = ''; return; }

    for (let i = 0; i < score; i++) bars[i].style.background = colors[score - 1];
    label.textContent = labels[score - 1];
    label.style.color = colors[score - 1];
  }


  

</script>

</body>
</html>
