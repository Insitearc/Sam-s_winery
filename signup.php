<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up | Get A Free Wine Bottle Voucher | Sam's Wine</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <style>
    /* ===================== SIGNUP PAGE CSS ===================== */
    :root {
      --gold: #d4af37;
      --gold-soft: #e6c76b;
      --dark-bg: #070707;
      --card-bg: #121212;
      --border-gold: rgba(212, 175, 55, 0.3);
      --error: #e05555;
      --success: #4caf7d;
    }

    html {
      scroll-behavior: smooth;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Outfit", sans-serif;
      background: linear-gradient(to bottom, #070707, #030303);
      color: #ffffff;
      min-height: 100vh;
    }

    /* ================= PAGE CONTAINER ================= */
    .signup-page {
      max-width: 1200px;
      margin: 0 auto;
      padding: 140px 24px 80px;
      display: grid;
      grid-template-columns: 1fr 1.1fr;
      gap: 60px;
      align-items: center;
    }

    /* ================= LEFT CONTENT / BRAND STORY ================= */
    .signup-info-panel {
      padding-right: 20px;
    }

    .badge {
      display: inline-block;
      font-size: 11px;
      letter-spacing: 3px;
      color: var(--gold);
      text-transform: uppercase;
      margin-bottom: 16px;
      font-weight: 500;
    }

    .signup-info-panel h1 {
      font-size: 3.2rem;
      font-weight: 400;
      line-height: 1.15;
      margin-bottom: 24px;
      letter-spacing: -0.5px;
    }

    .signup-info-panel p {
      font-size: 1.05rem;
      line-height: 1.8;
      color: rgba(255, 255, 255, 0.75);
      margin-bottom: 35px;
      font-weight: 300;
    }

    .perks-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .perk-item {
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 0.98rem;
      color: rgba(255, 255, 255, 0.9);
    }

    .perk-icon {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: rgba(212, 175, 55, 0.12);
      border: 1px solid var(--gold-soft);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--gold);
      font-size: 16px;
      flex-shrink: 0;
    }

    /* ================= RIGHT FORM CARD ================= */
    .signup-card-wrapper {
      background: var(--card-bg);
      border: 1px solid var(--border-gold);
      border-radius: 16px;
      padding: 48px 40px;
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8);
      position: relative;
    }

    .signup-card-header {
      text-align: center;
      margin-bottom: 35px;
    }

    .signup-card-header h2 {
      font-size: 2rem;
      font-weight: 400;
      margin-bottom: 8px;
    }

    .signup-card-header p {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.6);
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-group label {
      display: block;
      font-size: 11px;
      letter-spacing: 2px;
      color: var(--gold-soft);
      margin-bottom: 8px;
      text-transform: uppercase;
      font-weight: 500;
    }

    .form-group input {
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      background: #181818;
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      padding: 14px 18px;
      color: #fff;
      font-size: 15px;
      font-family: "Outfit", sans-serif;
      outline: none;
      transition: all 0.3s ease;
    }

    .form-group input[type="file"] {
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      overflow: hidden;
      cursor: pointer;
    }

    .form-group input:focus {
      border-color: var(--gold);
      background: #1c1c1c;
      box-shadow: 0 0 12px rgba(212, 175, 55, 0.2);
    }

    .form-group input.is-error {
      border-color: var(--error);
    }

    .field-error-msg {
      display: none;
      font-size: 12px;
      color: var(--error);
      margin-top: 6px;
    }

    .submit-btn {
      width: 100%;
      margin-top: 15px;
      padding: 16px;
      background: var(--gold);
      border: none;
      color: #000;
      font-weight: 600;
      font-size: 13px;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: "Outfit", sans-serif;
      text-decoration: none !important;
    }

    .submit-btn:hover {
      background: #ffffff;
      color: #000000;
      text-decoration: none !important;
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
    }

    /* ================= SUCCESS STATE ================= */
    .success-box {
      display: none;
      text-align: center;
      padding: 20px 10px;
    }

    .success-icon {
      font-size: 50px;
      margin-bottom: 15px;
    }

    .success-box h3 {
      font-size: 1.8rem;
      color: var(--gold);
      font-weight: 400;
      margin-bottom: 12px;
    }

    .success-box p {
      font-size: 0.95rem;
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 25px;
      line-height: 1.6;
    }

    .coupon-display-card {
      background: #181818;
      border: 1px dashed var(--gold);
      border-radius: 12px;
      padding: 22px;
      margin-bottom: 25px;
    }

    .coupon-label {
      font-size: 10px;
      letter-spacing: 2px;
      color: #aaa;
      text-transform: uppercase;
      display: block;
      margin-bottom: 8px;
    }

    .coupon-code {
      font-size: 1.8rem;
      color: var(--gold);
      letter-spacing: 4px;
      font-family: monospace;
      font-weight: 700;
    }

    .redemption-note {
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.5);
      line-height: 1.5;
      margin-bottom: 25px;
    }

    /* ================= WINE CLUB SECTION (Below Signup) ================= */
    .wc-zone {
      width: 100%;
      overflow-x: hidden;
      scroll-margin-top: 80px;
    }

    .wc-zone * {
      box-sizing: border-box;
    }

    /* ===== WINE CLUB HERO ===== */
    .wc-hero {
      min-height: 80vh;
      display: flex;
      scroll-margin-top: 80px;
      align-items: center;
      justify-content: center;
      text-align: center;
      background-image:
        linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.85)),
        url("images/wine5.JPG");
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      padding: 60px 20px;
    }

    .wc-hero h1 {
      font-size: 4.5rem;
      letter-spacing: 2px;
      color: #fff;
      font-weight: 400;
    }

    .wc-hero span {
      display: block;
      margin-top: 1rem;
      color: #c6a75e;
      letter-spacing: 4px;
      font-weight: 300;
    }

    .wc-hero-desc {
      font-family: "Outfit", sans-serif;
      font-size: 1.1rem;
      color: #ccc;
      margin-top: 15px;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
      font-weight: 300;
      line-height: 1.6;
    }

    .wc-hero-signup-btn {
      display: inline-block;
      margin-top: 28px;
      padding: 13px 38px;
      font-family: "Outfit", sans-serif;
      font-size: clamp(0.75rem, 1.5vw, 0.85rem);
      font-weight: 400;
      letter-spacing: 3px;
      text-transform: uppercase;
      text-decoration: none;
      color: #ffffff;
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.65);
      border-radius: 2px;
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      transition: all 0.35s ease;
      cursor: pointer;
    }

    .wc-hero-signup-btn:hover {
      background: #ffffff;
      color: #000000;
      border-color: #ffffff;
      box-shadow: 0 8px 25px rgba(255, 255, 255, 0.25);
      transform: translateY(-2px);
    }

    /* ===== JOURNEY ===== */
    .wc-journey {
      color: #ffffff;
      padding: 4rem 2rem;
      text-align: center;
      background: linear-gradient(to bottom, #080808, #151515);
      font-family: "Outfit", sans-serif;
    }

    .wc-journey>span {
      font-size: 1rem;
      letter-spacing: 3px;
      color: #c6a75e;
      font-weight: 300;
    }

    .wc-journey h2 {
      font-size: 2.5rem;
      font-weight: 400;
      margin-top: 0.5rem;
    }

    .wc-step-container {
      display: flex;
      justify-content: center;
      gap: 2rem;
      max-width: 1100px;
      margin: 4rem auto 0;
    }

    .wc-step {
      color: #ffffff;
      flex: 1;
      padding: 2rem;
      border-top: 1px solid #c6a75e;
      text-align: left;
    }

    .wc-step-number {
      display: block;
      font-family: "Outfit", sans-serif;
      color: #c6a75e;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    .wc-step h3 {
      letter-spacing: 2px;
      margin-bottom: 1rem;
      font-weight: 400;
      font-size: 1.3rem;
    }

    .wc-step p {
      font-size: 1rem;
      font-weight: 300;
      opacity: 0.85;
      line-height: 1.6;
    }

    /* ===== WINE CLUB ESSENCE ===== */
    .wc-essence {
      background: linear-gradient(to bottom, #080808, #151515);
      padding: 4rem 2rem;
    }

    .wc-essence-inner {
      max-width: 900px;
      margin: 0 auto;
      text-align: center;
    }

    .wc-essence-label {
      display: inline-block;
      font-size: 0.8rem;
      letter-spacing: 4px;
      color: #c6a75e;
      margin-bottom: 1.5rem;
    }

    .wc-essence-title {
      font-family: "Outfit", sans-serif;
      font-size: 3.6rem;
      line-height: 1.15;
      margin-bottom: 2.5rem;
      font-weight: 400;
      color: white;
    }

    .wc-essence-text {
      font-family: "Outfit", sans-serif;
      font-size: 1.1rem;
      line-height: 1.9;
      max-width: 760px;
      margin: 0 auto 4rem;
      font-weight: 300;
      color: white;
    }

    .wc-essence-divider {
      width: 80px;
      height: 1px;
      background: #c6a75e;
      margin: 0 auto 4rem;
      opacity: 0.8;
    }

    .wc-essence-highlights {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 3rem;
    }

    .wc-highlight {
      padding: 1rem;
      border: 1px solid #c6a75e;
      border-radius: 10px;
    }

    .wc-highlight h3 {
      font-weight: 600;
      font-size: 1.25rem;
      letter-spacing: 1px;
      margin-bottom: 0.8rem;
      color: #ffd700;
    }

    .wc-highlight p {
      font-weight: 300;
      font-size: 0.95rem;
      color: #f0f0f0;
      line-height: 1.6;
    }

    /* ===== WINE COLLECTION ===== */
    .wc-collection {
      padding: 6rem 2rem 4rem;
      background: #080808;
      color: #fff;
    }

    .wc-collection-header {
      text-align: center;
      margin-bottom: 8rem;
    }

    .wc-collection-header h2 {
      font-family: "Outfit", sans-serif;
      font-size: 3.5rem;
      font-weight: 400;
      letter-spacing: 2px;
    }

    .wc-collection-header p {
      color: #c6a75e;
      letter-spacing: 3px;
      text-transform: uppercase;
      font-size: 0.9rem;
      margin-top: 1rem;
    }

    .wc-showcase-item {
      display: flex;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto 12rem;
      gap: 8rem;
    }

    .wc-showcase-item:nth-child(even) {
      flex-direction: row-reverse;
    }

    .wc-showcase-item:last-child {
      margin-bottom: 4rem;
    }

    .wc-image-area {
      flex: 1;
    }

    .wc-image-area img {
      max-width: 100%;
      height: 550px;
      object-fit: contain;
      filter: drop-shadow(0 30px 60px rgba(0, 0, 0, 0.9));
      transition: transform 0.5s ease;
    }

    .wc-image-area:hover img {
      transform: scale(1.05);
    }

    .wc-content-area {
      flex: 1;
    }

    .wc-content-area>span {
      color: #c6a75e;
      letter-spacing: 3px;
      font-size: 0.8rem;
      font-weight: 400;
    }

    .wc-content-area h3 {
      font-family: "Outfit", sans-serif;
      font-size: 3.2rem;
      margin: 1.5rem 0;
      line-height: 1.1;
      text-transform: uppercase;
      font-weight: 400;
    }

    .wc-content-area p {
      font-family: "Outfit", sans-serif;
      font-size: 1.1rem;
      line-height: 1.8;
      opacity: 0.8;
      margin-bottom: 2rem;
      font-weight: 300;
    }

    .wc-know-more {
      text-decoration: none;
      color: #c6a75e;
      letter-spacing: 3px;
      font-size: 0.85rem;
      border-bottom: 1px solid #c6a75e;
      padding-bottom: 8px;
      transition: 0.4s ease;
    }

    .wc-know-more:hover {
      letter-spacing: 5px;
    }

    /* ===== PREMIUM SHOWCASE ===== */
    .wc-premium-showcase {
      background: linear-gradient(to bottom, #0b0b0b, #141414);
      padding: 100px 5%;
      font-family: "Outfit", sans-serif;
    }

    .wc-showcase-inner {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: center;
    }

    .wc-image-stack {
      position: relative;
      display: flex;
      justify-content: center;
    }

    .wc-main-frame {
      position: relative;
      width: 90%;
      border: 1px solid rgba(255, 255, 255, 0.08);
      padding: 15px;
      background: #0f0f0f;
    }

    .wc-hero-img {
      width: 100%;
      height: 500px;
      object-fit: cover;
      display: block;
    }

    .wc-badge {
      position: absolute;
      top: -10px;
      left: -10px;
      background: #c6a75e;
      color: #000;
      padding: 10px 20px;
      font-size: 0.8rem;
      letter-spacing: 2px;
    }

    .wc-info-float {
      position: absolute;
      bottom: -30px;
      right: -20px;
      background: #0c0c0c;
      border: 1px solid rgba(255, 255, 255, 0.08);
      color: white;
      padding: 30px;
      max-width: 220px;
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8);
    }

    .wc-info-float .gold-text {
      color: #c6a75e;
      font-size: 0.75rem;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .wc-info-float p {
      margin-top: 8px;
      font-size: 0.85rem;
      line-height: 1.5;
    }

    .wc-brand-subtitle {
      color: #c6a75e;
      text-transform: uppercase;
      letter-spacing: 4px;
      margin-bottom: 15px;
      font-weight: 400;
    }

    .wc-brand-title {
      font-size: 3.5rem;
      line-height: 1.2;
      color: #ffffff;
      margin-bottom: 25px;
      font-weight: 400;
    }

    .wc-brand-title strong {
      color: #c6a75e;
    }

    .wc-brand-text {
      color: rgba(255, 255, 255, 0.85);
      font-weight: 300;
      font-size: 1.05rem;
      line-height: 1.8;
    }

    .wc-features-list {
      margin: 40px 0;
    }

    .wc-f-item {
      display: flex;
      gap: 20px;
      margin-bottom: 25px;
    }

    .wc-f-num {
      font-size: 1.2rem;
      color: #c6a75e;
      font-weight: bold;
      border-bottom: 2px solid #c6a75e;
      height: fit-content;
    }

    .wc-f-desc strong {
      color: #ffffff;
      font-weight: 500;
      display: block;
      margin-bottom: 4px;
    }

    .wc-f-desc p {
      color: rgba(255, 255, 255, 0.75);
    }

    .wc-gold-button {
      display: inline-block;
      padding: 15px 40px;
      background: transparent;
      border: 2px solid #d4af37;
      color: #d4af37;
      text-decoration: none;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 3px;
      position: relative;
      overflow: hidden;
      transition: color 0.4s ease;
      isolation: isolate;
      z-index: 0;
    }

    .wc-gold-button::before {
      content: "";
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: #d4af37;
      transition: left 0.4s ease;
      z-index: -1;
    }

    .wc-gold-button:hover {
      color: #000;
    }

    .wc-gold-button:hover::before {
      left: 0;
    }

    /* ===== WINE CLUB RESPONSIVE ===== */
    @media (max-width: 968px) {
      .signup-page {
        grid-template-columns: 1fr;
        gap: 40px;
        padding: 120px 20px 60px;
      }

      .signup-info-panel h1 {
        font-size: 2.5rem;
      }

      .signup-card-wrapper {
        padding: 35px 25px;
      }
    }

    @media (max-width: 900px) {
      .wc-hero h1 {
        font-size: 3rem;
      }

      .wc-step-container {
        flex-direction: column;
      }

      .wc-showcase-item,
      .wc-showcase-item:nth-child(even) {
        flex-direction: column;
        text-align: center;
        gap: 3rem;
        margin-bottom: 8rem;
      }

      .wc-essence-highlights {
        grid-template-columns: 1fr;
        gap: 2.5rem;
      }

      .wc-essence-title {
        font-size: 2.6rem;
      }
    }

    @media (max-width: 992px) {
      .wc-showcase-inner {
        grid-template-columns: 1fr;
        gap: 60px;
      }

      .wc-brand-title {
        font-size: 3rem;
      }

      .wc-image-area img {
        height: 400px;
      }

      .wc-hero-img {
        height: 400px;
      }
    }

    @media (max-width: 768px) {
      .wc-premium-showcase {
        padding: 60px 20px;
      }

      .wc-brand-title {
        font-size: 2.5rem;
      }
    }

    @media (max-width: 480px) {
      .wc-brand-title {
        font-size: 2rem;
      }

      .wc-collection-header h2 {
        font-size: 2.2rem;
      }

      .wc-essence-title {
        font-size: 2rem;
      }

      .wc-content-area h3 {
        font-size: 2rem;
      }

      .wc-info-float {
        position: relative;
        bottom: 0;
        right: 0;
        margin-top: 20px;
        max-width: 100%;
        text-align: center;
      }

      .wc-hero-img {
        height: 250px;
      }
    }

    /* ===== SIGNUP PAGE RESPONSIVE ===== */
    @media (max-width: 960px) {
      .signup-page {
        grid-template-columns: 1fr;
        padding: 105px 20px 60px;
        gap: 40px;
      }
      .signup-info-panel {
        padding-right: 0;
        text-align: center;
      }
      .signup-info-panel h1 {
        font-size: 2.4rem;
      }
      .perks-list {
        text-align: left;
        max-width: 480px;
        margin: 0 auto;
      }
      .signup-card-wrapper {
        padding: 34px 24px;
      }
    }

    @media (max-width: 480px) {
      .signup-page {
        padding: 85px 14px 40px;
        gap: 30px;
      }
      .signup-info-panel h1 {
        font-size: 1.85rem;
      }
      .signup-card-wrapper {
        padding: 24px 16px;
        border-radius: 12px;
      }
      .signup-card-header h2 {
        font-size: 1.6rem;
      }
      .form-group input {
        padding: 12px 14px;
        font-size: 14px;
      }
      .form-group input[type="file"] {
        padding: 10px 12px;
        font-size: 12px;
      }
      .submit-btn {
        padding: 14px;
        font-size: 12px;
        letter-spacing: 1.5px;
      }
      .coupon-code {
        font-size: 1.4rem;
        letter-spacing: 2px;
      }
    }
  </style>
</head>

<body>
  <!-- HEADER IMPORT -->
  <div id="header-container"></div>

  <!-- ==================== SIGNUP SECTION ==================== -->
  <main class="signup-page">
    <!-- LEFT: BRAND & PERKS -->
    <div class="signup-info-panel">
      <span class="badge">EXCLUSIVE WELCOME OFFER</span>
      <h1>Get A Free Wine Bottle</h1>
      <p>
        Sign up today and receive an instant voucher code for a complimentary
        bottle of Sam's Wine on your next visit or purchase!
      </p>

      <ul class="perks-list">
        <li class="perk-item">
          <div class="perk-icon"><i class="fa-solid fa-gift"></i></div>
          <div>
            <strong>Complimentary Wine Bottle</strong><br />
            <span style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.6)">An exclusive welcome gift voucher issued
              instantly upon
              signup.</span>
          </div>
        </li>
        <li class="perk-item">
          <div class="perk-icon"><i class="fa-solid fa-wine-glass"></i></div>
          <div>
            <strong>Private Tasting Room Privileges</strong><br />
            <span style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.6)">Priority booking and member invitations at
              Dindori,
              Nashik.</span>
          </div>
        </li>
        <li class="perk-item">
          <div class="perk-icon"><i class="fa-solid fa-store"></i></div>
          <div>
            <strong>Retail Partner Redemption</strong><br />
            <span style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.6)">Redeemable across Dorabjee & Co. &
              Nature's Basket
              outlets.</span>
          </div>
        </li>
      </ul>
    </div>

    <!-- RIGHT: SIGNUP FORM CARD -->
    <div class="signup-card-wrapper">
      <div id="signup-form-box">
        <div class="signup-card-header">
          <h2>Customer Sign Up</h2>
          <p>Please enter your details to claim your free bottle voucher</p>
        </div>

        <form id="customerSignupForm" novalidate>
          <div class="form-group">
            <label for="nameInput">FULL NAME</label>
            <input type="text" id="nameInput" placeholder="Enter your full name" autocomplete="off" required />
            <div class="field-error-msg" id="nameError">
              Please enter a valid full name.
            </div>
          </div>

          <div class="form-group">
            <label for="emailInput">EMAIL ADDRESS</label>
            <input type="email" id="emailInput" placeholder="name@example.com" autocomplete="off" required />
            <div class="field-error-msg" id="emailError">
              Please enter a valid email address.
            </div>
          </div>

          <div class="form-group">
            <label for="mobileInput">MOBILE NUMBER</label>
            <input type="tel" id="mobileInput" placeholder="e.g. 9876543210" maxlength="10" autocomplete="off"
              required />
            <div class="field-error-msg" id="mobileError">
              Enter a valid 10-digit mobile number.
            </div>
          </div>

          <div class="form-group">
            <label for="dobInput">DATE OF BIRTH</label>
            <input type="date" id="dobInput" required />
            <div class="field-error-msg" id="dobError">
              Please select your date of birth.
            </div>
          </div>

          <div class="form-group">
            <label for="aadhaarInput">AADHAAR CARD UPLOAD (JPG, PNG, PDF - MAX 5MB)</label>
            <input type="file" id="aadhaarInput" name="aadhaar_file" accept=".jpg,.jpeg,.png,.pdf" required />
            <div class="field-error-msg" id="aadhaarError">
              Please upload a valid Aadhaar document (JPG, PNG, or PDF up to 5MB).
            </div>
          </div>

          <button type="submit" class="submit-btn" id="btn-submit-gift">
            GET FREE BOTTLE VOUCHER →
          </button>
        </form>
      </div>

      <!-- SUCCESS VOUCHER STATE -->
      <div id="successBox" class="success-box">
        <div class="success-icon" style="color:var(--gold); font-size:36px; margin-bottom:12px;"><i class="fa-solid fa-gift"></i></div>
        <h3>Welcome Voucher Issued!</h3>
        <p>
          Welcome to Sam's Wine! Your confirmation email and free bottle
          voucher code have been dispatched.
        </p>

        <div class="coupon-display-card">
          <span class="coupon-label">YOUR FREE BOTTLE VOUCHER CODE</span>
          <div class="coupon-code" id="couponCodeOutput">SAMS-FREE-9876</div>
        </div>

        <p class="redemption-note">
          Present this voucher code at our Dindori Winery Tasting Room in
          Nashik or any authorized Dorabjee & Nature's Basket retail outlet.
        </p>

        <a href="wineclub.html" class="submit-btn" style="
              display: inline-block;
              text-decoration: none;
              width: auto;
              padding: 14px 40px;
            ">EXPLORE WINES →</a>
      </div>
    </div>
  </main>

  <!-- ==================== WINE CLUB CONTENT (Below Signup) ==================== -->
  <div class="wc-zone" id="wineclub">
    <!-- WC HERO -->
    <section class="wc-hero" id="wine-club-content">
      <div>
        <h1>Wine Club</h1>
        <span>EXCLUSIVE MEMBER ACCESS</span>
        <p class="wc-hero-desc">
          Sign up to become an exclusive member to get discounts & a free
          bottle on your Birthday.
        </p>
        <a href="#customerSignupForm" class="wc-hero-signup-btn" onclick="
              document
                .getElementById('customerSignupForm')
                ?.scrollIntoView({ behavior: 'smooth' });
              return false;
            ">Sign Up</a>
      </div>
    </section>

    <!-- WC JOURNEY -->
    <section class="wc-journey">
      <span>OUR CRAFT</span>
      <h2>From Orchard to Glass</h2>
      <div class="wc-step-container">
        <div class="wc-step">
          <span class="wc-step-number">01</span>
          <h3>Sustainable Harvest</h3>
          <p>
            We source residue-free fruits and fresh blossoms from our natural
            farming Orchards.
          </p>
        </div>
        <div class="wc-step">
          <span class="wc-step-number">02</span>
          <h3>Artisanal Fermentation</h3>
          <p>
            Our unique process preserves antioxidants & nutrients to create
            the world's first premium wines.
          </p>
        </div>
        <div class="wc-step">
          <span class="wc-step-number">03</span>
          <h3>Cellar Experience</h3>
          <p>
            Visit our tasting room for a guided tour of nature's art, where
            sweet meets tart in every sip.
          </p>
        </div>
      </div>
    </section>

    <!-- WC ESSENCE -->
    <section class="wc-essence">
      <div class="wc-essence-inner">
        <span class="wc-essence-label">WINE CLUB</span>
        <h2 class="wc-essence-title">
          A Space for Those<br />Who Understand Wine
        </h2>
        <p class="wc-essence-text">
          The Sam's Wine is not a program — it is a destination. A place where
          rare flower wines, native fruit expressions, and sustainable craft
          are explored at their purest.
          <br /><br />
          Here, wine is not rushed. It is tasted, discussed, and discovered
          through seasonal releases, guided experiences, and an uncompromising
          respect for origin.
        </p>
        <div class="wc-essence-divider"></div>
        <div class="wc-essence-highlights">
          <div class="wc-highlight">
            <h3>World-First Creations</h3>
            <p>
              <b>INDIA'S</b> only fruit & flower wines, crafted from native
              blossoms and fruit wine.
            </p>
          </div>
          <div class="wc-highlight">
            <h3>Seasonal Exploration</h3>
            <p>Rotating releases shaped by harvest, climate, and terroir.</p>
          </div>
          <div class="wc-highlight">
            <h3>Rooted in Origin</h3>
            <p>
              Residue-free farming, sustainable Orchards, and slow
              fermentation.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- WC WINE COLLECTION -->
    <section class="wc-collection">
      <div class="wc-collection-header">
        <h2>Signature Releases</h2>
        <p>World-Best Flower & Fruit Innovations</p>
      </div>

      <div class="wc-showcase-item">
        <div class="wc-image-area">
          <img src="images/sam's winery/Pomegranate Orchard Wine Showcase.png" alt="Ruby Rich Fruit Wine" />
        </div>
        <div class="wc-content-area">
          <span>01 / RESIDUE FREE</span>
          <h3>Pomegranate <br />WINE</h3>
          <p>
            RUBY RICH RESERVE is a deep, velvety fruit wine crafted from
            residue-free pomegranates grown in the fertile soils of
            <b>INDIA</b>. Carefully fermented to preserve its natural
            vitality, this wine embodies richness, balance, and refinement in
            every glass. The palate reveals layers of dark berry intensity,
            subtle tartness, and a smooth, structured finish that unfolds
            gracefully. Naturally high in antioxidants and celebrated for its
            anti-aging properties, Ruby Rich Reserve is where wellness meets
            indulgence — a wine made for the modern, health-conscious
            connoisseur.
          </p>
          <a href="pome.html" class="wc-know-more">DISCOVER WINE</a>
        </div>
      </div>

      <div class="wc-showcase-item">
        <div class="wc-image-area">
          <img src="images/edited/Sam's Product Creative-39.jpg" alt="Jamun Fruit Wine" />
        </div>
        <div class="wc-content-area">
          <span>02 / HEALTH CONSCIOUS</span>
          <h3>JAMUN WINE<br />(Plum)</h3>
          <p>
            VITALITY JAMUN is a robust fruit wine crafted from hand-harvested
            Jamun sourced from sustainable orchards across <b>INDIA</b>. Known
            for its deep color and mineral-rich character, this wine captures
            the raw intensity of dark fruit balanced with refined
            craftsmanship. On the palate, it delivers bold blackberry-like
            notes, subtle earthy undertones, with tarty, tannic notes that
            leave a pleasantly dry finish. Rich in natural minerals and
            traditionally valued for its anti-diabetic properties, Jamun Wine
            is both grounding and invigorating.
          </p>
          <a href="jamun.html" class="wc-know-more">DISCOVER WINE</a>
        </div>
      </div>

      <div class="wc-showcase-item">
        <div class="wc-image-area">
          <img src="images/sam's winery/Golden Hour Strawberry Wine Picnic (1).png" alt="Scarlet Silk" />
        </div>
        <div class="wc-content-area">
          <span>03 / REFRESHING</span>
          <h3>STRAWBERRY<br />Wine</h3>
          <p>
            SCARLET SILK is Sam's Wine's special edition strawberry wine,
            crafted to showcase the boldest fruit expressions of the season.
            Made from select, peak-ripeness strawberries, this wine embodies
            the house philosophy of "sweet meets tart" — vibrant, playful, and
            impeccably balanced. The palate opens with bright red-fruit aromas
            and fresh berry sweetness, followed by a lively natural acidity
            that brings structure and elegance. Its smooth, lingering finish
            makes Scarlet Silk both indulgent and refreshing, a wine designed
            to surprise and delight the discerning collector. This wine is
            ideal for celebratory moments, refined dessert pairings, and those
            who seek something distinct yet sophisticated.
          </p>
          <a href="Strawberry.html" class="wc-know-more">DISCOVER WINE</a>
        </div>
      </div>

      <div class="wc-showcase-item">
        <div class="wc-image-area">
          <img src="images/mahua2.png" alt="Mahua Flower Wine" />
        </div>
        <div class="wc-content-area">
          <span>04 / WORLD'S FIRST</span>
          <h3>MAHUA <br />FLOWER WINE</h3>
          <p>
            MAHUA FLOWER WINE is a world-first expression of
            <b>INDIA'S</b> native blossoms, crafted with rare botanical
            precision from hand-picked Mahua flowers gathered from wild
            forests across <b>INDIA</b>. Fermented slowly to preserve its
            natural character, this wine captures the soul of the flower —
            floral, gently sweet, and intensly aromatic. On the palate, it
            opens with delicate honeyed notes and soft tropical florals,
            followed by a warm, rounded mouthfeel that lingers elegantly.
            Naturally rich in antioxidants, Mahua Flower Wine is not just an
            indulgence, but a celebration of wellness, tradition, and modern
            craftsmanship.
          </p>
          <a href="mahua.html" class="wc-know-more">DISCOVER WINE</a>
        </div>
      </div>
    </section>

    <!-- WC PREMIUM SHOWCASE -->
    <section class="wc-premium-showcase">
      <div class="wc-showcase-inner">
        <div class="wc-visual-side">
          <div class="wc-image-stack">
            <div class="wc-main-frame">
              <div class="wc-badge">TASTING ROOM</div>
              <img src="https://images.unsplash.com/photo-1547595628-c61a29f496f0?auto=format&fit=crop&w=800&q=80"
                alt="Sam's Wine Wine Tasting" class="wc-hero-img" />
            </div>
            <div class="wc-info-float">
              <span class="gold-text">By Appointment</span>
              <p>
                Guided tastings with curated pairings<br />& sustainable craft
                insights
              </p>
            </div>
          </div>
        </div>

        <div class="wc-content-side">
          <h4 class="wc-brand-subtitle">Taste. Learn. Discover</h4>
          <h2 class="wc-brand-title">
            The Sam's Wine <br /><strong>Tasting Experience</strong>
          </h2>
          <p class="wc-brand-text">
            Step into our tasting room and explore the world of fruit and
            flower wines. Sample seasonal releases, limited editions, and
            signature bottles, guided by our wine specialists who reveal the
            aromas, textures, and stories behind every pour.
          </p>

          <div class="wc-features-list">
            <div class="wc-f-item">
              <span class="wc-f-num">01</span>
              <div class="wc-f-desc">
                <strong>Signature Tastings</strong>
                <p>Seasonal, limited-edition & reserve wines.</p>
              </div>
            </div>
            <div class="wc-f-item">
              <span class="wc-f-num">02</span>
              <div class="wc-f-desc">
                <strong>Thoughtful Pairings</strong>
                <p>Carefully paired gourmet accompaniments.</p>
              </div>
            </div>
            <div class="wc-f-item">
              <span class="wc-f-num">03</span>
              <div class="wc-f-desc">
                <strong>Craft & Origin</strong>
                <p>Insights into sustainable farming and winemaking.</p>
              </div>
            </div>
          </div>

          <div class="wc-tasting-cta-group" style="
                display: flex;
                align-items: center;
                gap: 30px;
                margin-top: 40px;
                flex-wrap: wrap;
              ">
            <a href="visit.html" class="wc-gold-button" style="margin: 0">VISIT US →</a>
            <span class="wc-tasting-phone" style="
                  font-family: &quot;Outfit&quot;, sans-serif;
                  font-size: 1.05rem;
                  color: #fff;
                  letter-spacing: 1px;
                  display: inline-flex;
                  align-items: center;
                  gap: 8px;
                  flex-wrap: wrap;
                ">
              <span style="color: #d4af37">OR CALL:</span>
              <a href="tel:+919767177577" style="
                    color: #fff;
                    text-decoration: none;
                    font-weight: 700;
                    font-size: 1.1rem;
                  ">+91 97671 77577</a>
              <a href="https://wa.me/919767177577" target="_blank" rel="noopener noreferrer" style="
                    color: #25d366;
                    text-decoration: none;
                    font-weight: 600;
                    font-size: 0.85rem;
                    background: rgba(37, 211, 102, 0.12);
                    padding: 2px 8px;
                    border-radius: 12px;
                    border: 1px solid rgba(37, 211, 102, 0.35);
                  " title="WhatsApp">WhatsApp</a>
              <span style="color: rgba(255, 255, 255, 0.65); font-size: 0.88rem">/
                <a href="tel:+919503283577" style="
                      color: rgba(255, 255, 255, 0.85);
                      text-decoration: none;
                    ">+91 95032 83577</a></span>
            </span>
          </div>
        </div>
      </div>
    </section>
  </div>
  <!-- ==================== WINE CLUB CONTENT END ==================== -->

  <!-- FOOTER IMPORT -->
  <div id="footer-container"></div>

  <script>
    // Import header.html
    fetch("header.html")
      .then((res) => res.text())
      .then((data) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(data, "text/html");
        const nav = doc.querySelector("nav");
        const menuOverlay = doc.querySelector(".menu-overlay");
        const styles = doc.querySelector("style");
        const scripts = doc.querySelector("script");

        if (styles) document.head.appendChild(styles);
        if (nav) {
          nav.style.background = "transparent";
          nav.style.backdropFilter = "none";
          const menuToggleSpans = nav.querySelectorAll(".menu-toggle span");
          menuToggleSpans.forEach(
            (span) => (span.style.background = "white"),
          );
        }
        document.getElementById("header-container").appendChild(nav);
        if (menuOverlay)
          document
            .getElementById("header-container")
            .appendChild(menuOverlay);
        if (scripts) eval(scripts.textContent);
      })
      .catch((err) => console.error("Error loading header:", err));

    // Import footer.html
    fetch("footer.html")
      .then((res) => res.text())
      .then((data) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(data, "text/html");
        const footer = doc.querySelector("footer");
        const styles = doc.querySelector("style");
        if (styles) document.head.appendChild(styles);
        document.getElementById("footer-container").appendChild(footer);
      })
      .catch((err) => console.error("Error loading footer:", err));
  </script>

  <script src="mail_handler.js"></script>
  <script>
    // Form Handling with PHP Session Authentication & Aadhaar Upload
    const form = document.getElementById("customerSignupForm");
    const nameInput = document.getElementById("nameInput");
    const emailInput = document.getElementById("emailInput");
    const mobileInput = document.getElementById("mobileInput");
    const dobInput = document.getElementById("dobInput");
    const aadhaarInput = document.getElementById("aadhaarInput");
    const submitBtn = document.getElementById("btn-submit-gift");

    let isUserLoggedIn = false;

    // Check PHP login session on page load
    async function initAuthCheck() {
      try {
        const res = await fetch("api/auth.php?action=check");
        const data = await res.json();
        if (data && data.loggedIn && data.user) {
          isUserLoggedIn = true;
          // Pre-populate fields from MySQL
          if (nameInput) nameInput.value = data.user.full_name || "";
          if (emailInput) emailInput.value = data.user.email || "";
          if (mobileInput) mobileInput.value = data.user.mobile || "";
          if (dobInput && data.user.dob) dobInput.value = data.user.dob;
        } else {
          isUserLoggedIn = false;
        }
      } catch (e) {
        console.error("Auth check failed:", e);
      }
    }
    initAuthCheck();

    // Mobile non-numeric prevention
    if (mobileInput) {
      mobileInput.addEventListener("keypress", (e) => {
        if (!/[0-9]/.test(e.key)) e.preventDefault();
      });
    }

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      // CHANGE 3: User must be logged in before claiming Free Gift
      if (!isUserLoggedIn) {
        if (confirm("You must login or register to claim your complimentary Free Wine Bottle.\n\nClick OK to proceed to Login/Register.")) {
          window.location.href = "login.php?redirect=" + encodeURIComponent(window.location.href);
        }
        return;
      }

      let isValid = true;

      // Validate Name
      if (nameInput.value.trim().length < 2) {
        document.getElementById("nameError").style.display = "block";
        nameInput.classList.add("is-error");
        isValid = false;
      } else {
        document.getElementById("nameError").style.display = "none";
        nameInput.classList.remove("is-error");
      }

      // Validate Mobile
      if (!/^[6-9]\d{9}$/.test(mobileInput.value.trim())) {
        document.getElementById("mobileError").style.display = "block";
        mobileInput.classList.add("is-error");
        isValid = false;
      } else {
        document.getElementById("mobileError").style.display = "none";
        mobileInput.classList.remove("is-error");
      }

      // Validate DOB
      if (!dobInput.value) {
        document.getElementById("dobError").style.display = "block";
        dobInput.classList.add("is-error");
        isValid = false;
      } else {
        document.getElementById("dobError").style.display = "none";
        dobInput.classList.remove("is-error");
      }

      // CHANGE 4: Validate Aadhaar File Upload
      const aadhaarErr = document.getElementById("aadhaarError");
      if (!aadhaarInput.files || aadhaarInput.files.length === 0) {
        aadhaarErr.style.display = "block";
        aadhaarErr.textContent = "Please upload your Aadhaar document for DOB verification.";
        isValid = false;
      } else {
        const file = aadhaarInput.files[0];
        const validExts = ["jpg", "jpeg", "png", "pdf"];
        const ext = file.name.split(".").pop().toLowerCase();
        if (!validExts.includes(ext)) {
          aadhaarErr.style.display = "block";
          aadhaarErr.textContent = "Invalid format. Allowed formats: JPG, JPEG, PNG, PDF.";
          isValid = false;
        } else if (file.size > 5 * 1024 * 1024) {
          aadhaarErr.style.display = "block";
          aadhaarErr.textContent = "File size exceeds 5MB limit.";
          isValid = false;
        } else {
          aadhaarErr.style.display = "none";
        }
      }

      if (isValid) {
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = "SUBMITTING & UPLOADING AADHAAR...";
        }

        const formData = new FormData();
        formData.append("name", nameInput.value.trim());
        formData.append("email", emailInput.value.trim());
        formData.append("mobile", mobileInput.value.trim());
        formData.append("dob", dobInput.value);
        formData.append("aadhaar_file", aadhaarInput.files[0]);

        try {
          const response = await fetch("api/free_gift.php", {
            method: "POST",
            body: formData
          });

          const result = await response.json();

          if (response.ok && result.status === "success") {
            const couponPlaceholder = `SAM-APP-${result.applicationId}`;
            const couponCodeEl = document.getElementById("couponCodeOutput");
            if (couponCodeEl) couponCodeEl.textContent = couponPlaceholder;

            document.getElementById("signup-form-box").style.display = "none";
            document.getElementById("successBox").style.display = "block";

            alert(result.message || "Free Gift application submitted successfully!");
          } else {
            alert(result.message || "Could not submit application. Please try again.");
          }
        } catch (error) {
          console.error("Free Gift Submission Error:", error);
          alert("Server error submitting application. Please try again.");
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = "GET FREE BOTTLE VOUCHER →";
          }
        }
      }
    });

    // Smooth scroll down to Wine Club content section
    function checkWineClubScroll() {
      if (
        window.location.hash === "#wineclub" ||
        window.location.hash === "#wine-club" ||
        window.location.hash === "#wine-club-content"
      ) {
        const target =
          document.getElementById("wineclub") ||
          document.querySelector(".wc-zone");
        if (target) {
          setTimeout(() => {
            target.scrollIntoView({ behavior: "smooth" });
          }, 150);
        }
      }
    }

    window.addEventListener("DOMContentLoaded", checkWineClubScroll);
    window.addEventListener("load", checkWineClubScroll);
    window.addEventListener("hashchange", checkWineClubScroll);

    // Global interceptor for any wineclub link clicked while on signup.html
    document.addEventListener("click", function (e) {
      const link = e.target.closest(
        'a[href*="#wineclub"], a[href*="#wine-club"]',
      );
      if (link) {
        e.preventDefault();
        const menuToggle = document.querySelector(".menu-toggle");
        const menuOverlay = document.querySelector(".menu-overlay");
        const navbar = document.querySelector(".navbar");
        if (menuToggle) menuToggle.classList.remove("active");
        if (menuOverlay) menuOverlay.classList.remove("active");
        if (navbar) navbar.classList.remove("menu-active");

        const target =
          document.getElementById("wineclub") ||
          document.querySelector(".wc-zone");
        if (target) {
          target.scrollIntoView({ behavior: "smooth" });
          if (window.history && window.history.pushState) {
            window.history.pushState(null, null, "#wineclub");
          }
        }
      }
    });
  </script>
</body>

</html>