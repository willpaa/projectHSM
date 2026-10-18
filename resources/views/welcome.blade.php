<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6faf9">
    <meta name="description" content="A connected hospital management system for coordinating appointments, patient records, pharmacy, diagnostics, and finance.">
    <title>OBU Hospital Management System</title>
    <link rel="icon" href="{{ asset('images/odb-logo.jfif') }}">
    <style>
        :root {
            color-scheme: light;
            --ink: #132f35;
            --muted: #526a6f;
            --teal: #08796d;
            --teal-dark: #075f56;
            --mint: #eaf5f2;
            --line: #e1ebe8;
            --paper: #fff;
            --wash: #f6faf9;
            --font-body: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            color: var(--ink);
            background: var(--paper);
            font-family: var(--font-body);
            font-size: 16px;
            font-weight: 450;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        .container { width: min(1160px, calc(100% - 48px)); margin-inline: auto; }
        .skip-link {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 10;
            padding: 10px 14px;
            transform: translateY(-150%);
            border-radius: 8px;
            color: #fff;
            background: var(--teal-dark);
        }
        .skip-link:focus { transform: translateY(0); }
        .topbar { color: #e8f4f1; background: #123e40; font-size: 13px; font-weight: 550; }
        .topbar-inner { display: flex; min-height: 36px; align-items: center; justify-content: space-between; gap: 16px; }
        .topbar a { color: #fff; }
        .header { position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--line); background: rgb(255 255 255 / 96%); backdrop-filter: blur(12px); }
        .nav { display: flex; min-height: 78px; align-items: center; justify-content: space-between; gap: 24px; }
        .brand { display: inline-flex; align-items: center; gap: 11px; font-size: 16px; font-weight: 750; letter-spacing: -.35px; }
        .brand img { width: 166px; height: 44px; border-radius: 0; object-fit: contain; }
        .brand-name { display: block; font-size: 15px; font-weight: 750; line-height: 1.2; }
        .brand small { display: block; margin-top: 3px; color: #5d7377; font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
        .nav-links { display: flex; align-items: center; gap: 30px; color: #435d62; font-size: 14px; font-weight: 600; }
        .nav-links a:hover, .footer-links a:hover { color: var(--teal); }
        .button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 19px;
            border: 1px solid transparent;
            border-radius: 8px;
            color: #fff;
            background: var(--teal);
            box-shadow: 0 8px 18px rgb(8 127 114 / 15%);
            font-size: 14px;
            font-weight: 700;
            transition: background .18s ease, transform .18s ease;
        }
        .button:hover { transform: translateY(-1px); background: var(--teal-dark); }
        .button.secondary { border-color: #c9dfda; color: var(--teal-dark); background: #fff; box-shadow: none; }
        .button.secondary:hover { background: var(--mint); }
        .button:focus-visible, a:focus-visible { outline: 3px solid #72c5b5; outline-offset: 3px; }
        .hero { position: relative; overflow: hidden; background: linear-gradient(112deg, #f3faf8 0%, #f3faf8 54%, #e5f3ef 100%); }
        .hero::after { position: absolute; top: -230px; right: -110px; width: 560px; height: 560px; border: 1px solid rgb(8 127 114 / 10%); border-radius: 50%; box-shadow: 0 0 0 48px rgb(8 127 114 / 3%), 0 0 0 96px rgb(8 127 114 / 2%); content: ""; }
        .hero-grid { position: relative; z-index: 1; display: grid; min-height: 620px; grid-template-columns: 1.05fr .95fr; align-items: center; gap: 56px; padding-block: 68px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; padding: 9px 13px; border-radius: 30px; color: var(--teal-dark); background: #e0f1ed; font-size: 12px; font-weight: 750; letter-spacing: .65px; text-transform: uppercase; }
        .eyebrow::before { width: 7px; height: 7px; border-radius: 50%; background: #36a995; content: ""; }
        h1, h2, h3 { font-family: var(--font-body); }
        h1 { max-width: 680px; margin: 23px 0 21px; font-size: clamp(46px, 5.25vw, 70px); font-weight: 760; letter-spacing: -3.5px; line-height: 1.06; }
        h1 span { color: #087368; }
        .hero-copy { max-width: 565px; margin: 0; color: #4c6267; font-size: clamp(17px, 1.45vw, 19px); font-weight: 450; line-height: 1.8; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 27px; }
        .hero-note { display: flex; align-items: center; gap: 11px; margin-top: 31px; color: #536a6e; font-size: 13px; line-height: 1.6; }
        .note-icon { display: grid; width: 34px; height: 34px; flex: 0 0 auto; place-items: center; border-radius: 50%; color: var(--teal); background: #dff0eb; font-size: 17px; }
        .hero-note strong { color: var(--ink); }
        .hero-visual { position: relative; width: 100%; max-width: 490px; justify-self: end; padding: 22px 15px 25px 0; }
        .hero-photo { width: 100%; height: 388px; border-radius: 18px 18px 76px 18px; object-fit: cover; object-position: center; box-shadow: 0 24px 54px rgb(23 68 63 / 18%); }
        .photo-label { position: absolute; right: 0; bottom: 0; display: flex; align-items: center; gap: 12px; padding: 15px 18px; border: 1px solid rgb(225 235 232 / 85%); border-radius: 12px; background: #fff; box-shadow: 0 14px 35px rgb(29 69 65 / 13%); }
        .label-icon { display: grid; width: 42px; height: 42px; place-items: center; border-radius: 11px; color: var(--teal); background: var(--mint); font-size: 20px; }
        .photo-label strong { display: block; font-size: 14px; font-weight: 700; }
        .photo-label small { display: block; margin-top: 4px; color: var(--muted); font-size: 12px; }
        .platform { padding: 88px 0 94px; }
        .section-heading { max-width: 660px; margin: 0 auto 42px; text-align: center; }
        .section-heading .eyebrow { margin-bottom: 14px; }
        h2 { margin: 0 0 15px; font-size: clamp(32px, 3.8vw, 46px); font-weight: 740; letter-spacing: -1.9px; line-height: 1.16; }
        .section-heading p { margin: 0; color: var(--muted); font-size: 16px; line-height: 1.8; }
        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .feature-card { min-height: 202px; padding: 25px 24px; border: 1px solid var(--line); border-radius: 13px; background: #fff; transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .feature-card:hover { transform: translateY(-3px); border-color: #b5d8d0; box-shadow: 0 14px 34px rgb(31 83 75 / 8%); }
        .feature-icon { display: grid; width: 45px; height: 45px; margin-bottom: 19px; place-items: center; border-radius: 12px; color: var(--teal); background: var(--mint); font-size: 20px; }
        .feature-card h3 { margin: 0 0 9px; font-size: 18px; font-weight: 700; letter-spacing: -.4px; }
        .feature-card p { margin: 0; color: var(--muted); font-size: 14px; line-height: 1.75; }
        .about { padding: 76px 0; background: var(--wash); }
        .about-grid { display: grid; grid-template-columns: .9fr 1.1fr; align-items: center; gap: 72px; }
        .about-mark { position: relative; display: grid; min-height: 310px; overflow: hidden; place-items: center; border: 1px solid #dcebe6; border-radius: 18px; background: linear-gradient(135deg, #e3f2ee, #f7fbfa); }
        .about-mark::before, .about-mark::after { position: absolute; width: 270px; height: 270px; border: 1px solid rgb(8 127 114 / 15%); border-radius: 50%; content: ""; }
        .about-mark::after { width: 190px; height: 190px; }
        .plus { z-index: 1; display: grid; width: 86px; height: 86px; place-items: center; border-radius: 24px; color: #fff; background: var(--teal); box-shadow: 0 16px 34px rgb(8 127 114 / 24%); font-size: 58px; font-weight: 350; }
        .about-copy .eyebrow { margin-bottom: 16px; }
        .about-copy h2 { max-width: 500px; }
        .about-copy p { color: var(--muted); font-size: 16px; line-height: 1.85; }
        .check-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 13px; margin-top: 22px; }
        .check-grid span { display: flex; align-items: center; gap: 9px; font-size: 14px; font-weight: 600; }
        .check { display: grid; width: 21px; height: 21px; flex: 0 0 auto; place-items: center; border-radius: 50%; color: var(--teal); background: #e0f1ed; font-size: 12px; }
        .mission { padding: 76px 0; }
        .mission-grid { display: grid; grid-template-columns: .8fr 1.2fr; align-items: stretch; gap: 24px; }
        .mission-intro { display: flex; flex-direction: column; align-items: flex-start; justify-content: center; }
        .mission-intro .eyebrow { margin-bottom: 15px; }
        .mission-intro h2 { max-width: 420px; }
        .mission-intro p { max-width: 420px; margin: 0; color: var(--muted); font-size: 16px; line-height: 1.8; }
        .mission-card { padding: 33px; border: 1px solid var(--line); border-radius: 15px; background: var(--wash); }
        .mission-card h3 { margin: 0 0 14px; font-size: 19px; font-weight: 700; letter-spacing: -.3px; }
        .mission-card p { margin: 0; color: #465e63; font-size: 16px; line-height: 1.85; }
        .mission-priorities { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 21px; }
        .mission-priorities span { padding: 8px 11px; border: 1px solid #d4e8e2; border-radius: 30px; color: var(--teal-dark); background: #fff; font-size: 12px; font-weight: 650; }
        .mission-source { margin-top: 22px; }
        .contact { padding: 0 0 82px; background: var(--wash); }
        .contact-box { position: relative; display: flex; overflow: hidden; align-items: center; justify-content: space-between; gap: 25px; padding: 39px 46px; border-radius: 16px; color: #fff; background: #123e40; }
        .contact-box::after { position: absolute; top: -190px; right: 26%; width: 280px; height: 280px; border: 1px solid rgb(255 255 255 / 12%); border-radius: 50%; box-shadow: 0 0 0 35px rgb(255 255 255 / 3%), 0 0 0 70px rgb(255 255 255 / 2%); content: ""; }
        .contact-box h2 { position: relative; z-index: 1; margin-bottom: 8px; font-size: 28px; }
        .contact-box p { position: relative; z-index: 1; margin: 0; color: #d4e6e2; font-size: 15px; }
        .contact-box .button { position: relative; z-index: 1; flex: 0 0 auto; color: var(--teal-dark); background: #fff; box-shadow: none; }
        .contact-box .button:hover { background: #eaf5f2; }
        .footer { color: #d9e9e6; background: #0b3032; }
        .footer-top { display: grid; grid-template-columns: 1.35fr repeat(4, 1fr); align-items: start; gap: 28px; padding: 38px 0 29px; }
        .footer-brand p { max-width: 380px; margin: 14px 0 0; color: #c0d5d1; font-size: 13px; line-height: 1.75; }
        .footer .brand { color: #fff; }
        .footer .brand small { color: #a9c4bf; }
        .footer-group h3 { margin: 4px 0 14px; color: #fff; font-size: 14px; font-weight: 700; letter-spacing: .1px; }
        .footer-links { display: grid; gap: 11px; color: #d0e1de; font-size: 13px; }
        .social-links { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 17px; }
        .social-link { display: grid; width: 36px; height: 36px; place-items: center; border: 1px solid rgb(255 255 255 / 20%); border-radius: 50%; color: #e5f2ef; transition: color .18s ease, background .18s ease, border-color .18s ease; }
        .social-link:hover { border-color: #fff; color: #123e40; background: #fff; }
        .social-link svg { width: 17px; height: 17px; fill: currentColor; }
        .footer-bottom { display: flex; justify-content: space-between; gap: 14px; padding: 17px 0 20px; border-top: 1px solid rgb(255 255 255 / 18%); color: #c0d5d1; font-size: 12px; }

        @media (max-width: 900px) {
            .hero-grid { grid-template-columns: 1fr 1fr; gap: 30px; }
            .hero-photo { height: 330px; }
            .about-grid { gap: 40px; }
            .footer-top { grid-template-columns: repeat(3, 1fr); }
            .footer-brand { grid-column: 1 / -1; }
        }
        @media (max-width: 700px) {
            .container { width: min(100% - 36px, 540px); }
            .topbar-inner { justify-content: center; }
            .topbar-inner > span { display: none; }
            .nav { min-height: 68px; gap: 12px; }
            .brand { gap: 7px; font-size: 12px; }
            .brand img { width: 96px; height: 38px; }
            .brand-name { font-size: 11px; }
            .brand small { font-size: 8px; letter-spacing: .7px; }
            .nav-links { display: none; }
            .nav > .button { min-height: 40px; padding-inline: 13px; font-size: 12px; }
            .hero-grid { min-height: auto; grid-template-columns: 1fr; gap: 28px; padding-block: 56px 48px; }
            h1 { max-width: 560px; font-size: clamp(42px, 10.5vw, 58px); letter-spacing: -2.5px; }
            .hero-copy { font-size: 17px; line-height: 1.75; }
            .hero-visual { max-width: 520px; justify-self: center; padding-right: 9px; }
            .hero-photo { height: 290px; }
            .platform { padding-block: 66px 70px; }
            .section-heading { margin-bottom: 30px; }
            .feature-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
            .feature-card { min-height: 200px; padding: 19px 17px; }
            .feature-icon { margin-bottom: 14px; }
            .about { padding-block: 60px; }
            .about-grid { grid-template-columns: 1fr; gap: 30px; }
            .about-mark { min-height: 235px; }
            .check-grid { gap: 12px 8px; }
            .mission { padding-block: 60px; }
            .mission-grid { grid-template-columns: 1fr; gap: 23px; }
            .mission-card { padding: 25px 21px; }
            .contact { padding-bottom: 58px; }
            .contact-box { align-items: flex-start; flex-direction: column; padding: 29px 25px; }
            .contact-box h2 { font-size: 27px; }
            .footer-top { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 27px 20px; }
            .footer-brand { grid-column: 1 / -1; }
        }
        @media (max-width: 420px) {
            .container { width: calc(100% - 30px); }
            .feature-grid { grid-template-columns: 1fr; }
            .feature-card { min-height: auto; }
            .check-grid { grid-template-columns: 1fr; }
            .footer-top { grid-template-columns: 1fr 1fr; }
            .footer-group h3 { font-size: 13px; }
            .footer-links { font-size: 12px; }
            .footer-bottom { flex-direction: column; }
            .hero-actions .button { flex: 1 1 auto; }
            .nav > .button { gap: 6px; padding-inline: 9px; font-size: 11px; }
            .brand img { width: 78px; height: 34px; }
            .brand-name { font-size: 10px; }
            .brand small { font-size: 7px; letter-spacing: .45px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="topbar">
        <div class="container topbar-inner">
            <span>Connected teams. More coordinated care.</span>
            <a href="{{ url('/admin/login') }}">Hospital team? Sign in to your workspace <span aria-hidden="true">→</span></a>
        </div>
    </div>
    <header class="header">
        <nav class="container nav" aria-label="Main navigation">
            <a class="brand" href="{{ url('/') }}">
                <img src="{{ asset('images/odb-logo.jfif') }}" alt="">
                <span><strong class="brand-name">OBU HMS</strong><small>Hospital Management System</small></span>
            </a>
            <div class="nav-links">
                <a href="#platform">Platform</a>
                <a href="#mission">OBU mission</a>
                <a href="#access">Staff access</a>
            </div>
            <a class="button" href="{{ url('/admin/login') }}">Staff sign in <span aria-hidden="true">→</span></a>
        </nav>
    </header>
    <main id="main">
        <section class="hero" aria-labelledby="hero-title">
            <div class="container hero-grid">
                <div>
                    <span class="eyebrow">Hospital management, made clear</span>
                    <h1 id="hero-title">One connected system for <span>better coordinated care.</span></h1>
                    <p class="hero-copy">Bring essential hospital operations together in one place. Support your teams as they coordinate appointments, patient records, pharmacy, diagnostics, and finance.</p>
                    <div class="hero-actions">
                        <a class="button" href="{{ url('/admin/login') }}">Enter the staff portal <span aria-hidden="true">→</span></a>
                        <a class="button secondary" href="#platform">Explore the platform</a>
                    </div>
                    <div class="hero-note">
                        <span class="note-icon" aria-hidden="true">✚</span>
                        <span><strong>Designed around connected workflows.</strong><br>Clear tools for the teams behind every visit.</span>
                    </div>
                </div>
                <div class="hero-visual">
                    <img class="hero-photo" src="{{ asset('images/login-bg.jpg') }}" alt="Clinicians working together in a modern hospital">
                    <div class="photo-label">
                        <span class="label-icon" aria-hidden="true">♡</span>
                        <span><strong>Care teams, connected</strong><small>One place for essential workflows</small></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="platform" id="platform" aria-labelledby="platform-title">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">One platform, essential services</span>
                    <h2 id="platform-title">The tools your hospital teams need</h2>
                    <p>Keep day-to-day work organized with connected modules built for the different services that support patient care.</p>
                </div>
                <div class="feature-grid">
                    <article class="feature-card">
                        <span class="feature-icon" aria-hidden="true">◷</span>
                        <h3>Appointments &amp; care</h3>
                        <p>Coordinate patient visits and support clinical teams across outpatient and inpatient care.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-icon" aria-hidden="true">♡</span>
                        <h3>Patient records</h3>
                        <p>Keep patient information and care history organized for authorized team members.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-icon" aria-hidden="true">⌁</span>
                        <h3>Pharmacy &amp; prescriptions</h3>
                        <p>Manage medication workflows, prescriptions, and pharmacy inventory in one system.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-icon" aria-hidden="true">⌕</span>
                        <h3>Laboratory &amp; radiology</h3>
                        <p>Support diagnostic services and keep related requests and results connected to care.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-icon" aria-hidden="true">$</span>
                        <h3>Billing &amp; finance</h3>
                        <p>Organize payments, receipts, and financial reporting for hospital operations.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-icon" aria-hidden="true">▤</span>
                        <h3>Reports &amp; operations</h3>
                        <p>Give teams a clearer view of activity across the services they help manage.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="about" id="about" aria-labelledby="about-title">
            <div class="container about-grid">
                <div class="about-mark" aria-hidden="true"><span class="plus">+</span></div>
                <div class="about-copy">
                    <span class="eyebrow">Built for the work behind care</span>
                    <h2 id="about-title">When hospital teams work in sync, care moves forward.</h2>
                    <p>This system brings key administrative and clinical workflows into a shared workspace. Staff can focus on their responsibilities while staying connected to the services around them.</p>
                    <div class="check-grid">
                        <span><i class="check">✓</i> Connected services</span>
                        <span><i class="check">✓</i> Organized workflows</span>
                        <span><i class="check">✓</i> Role-based access</span>
                        <span><i class="check">✓</i> Operational reporting</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="mission" id="mission" aria-labelledby="mission-title">
            <div class="container mission-grid">
                <div class="mission-intro">
                    <span class="eyebrow">Oda Bultum University</span>
                    <h2 id="mission-title">A mission rooted in service and learning.</h2>
                    <p>OBU’s mission brings together education, research, and community engagement under its motto, “Productive Mind for Development.”</p>
                </div>
                <div class="mission-card">
                    <h3>Our university mission</h3>
                    <p>Produce ethical, competent graduates; conduct problem-solving research; and render demand-based community engagement for sustainable social, economic and political goals.</p>
                    <div class="mission-priorities" aria-label="University priorities">
                        <span>Quality education</span>
                        <span>Problem-solving research</span>
                        <span>Community engagement</span>
                    </div>
                    <a class="button secondary mission-source" href="https://obu.edu.et/?page_id=924" target="_blank" rel="noopener noreferrer">Read OBU mission &amp; vision <span aria-hidden="true">↗</span></a>
                </div>
            </div>
        </section>

        <section class="contact" id="access" aria-label="Staff access">
            <div class="container">
                <div class="contact-box">
                    <div>
                        <h2>Ready to get to work?</h2>
                        <p>Sign in to access your hospital team's workspace.</p>
                    </div>
                    <a class="button" href="{{ url('/admin/login') }}">Sign in to the system <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>
    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <a class="brand" href="{{ url('/') }}">
                        <img src="{{ asset('images/odb-logo.jfif') }}" alt="Oda Bultum University logo">
                        <span><strong class="brand-name">OBU HMS</strong><small>Hospital Management System</small></span>
                    </a>
                    <p>Oda Bultum University — “Productive Mind for Development.” A connected workspace for the people and services that support hospital care.</p>
                    <div class="social-links" aria-label="Oda Bultum University social media">
                        <a class="social-link" href="https://www.facebook.com/OBUEthiopia" target="_blank" rel="noopener noreferrer" aria-label="Oda Bultum University on Facebook">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.4 21v-8.2h2.8l.4-3.2h-3.2v-2c0-.9.3-1.6 1.6-1.6h1.7V3.2c-.3 0-1.3-.2-2.5-.2-2.5 0-4.2 1.5-4.2 4.3v2.3H7.2v3.2H10V21h3.4Z"/></svg>
                        </a>
                        <a class="social-link" href="https://x.com/OdaBultumUnive" target="_blank" rel="noopener noreferrer" aria-label="Oda Bultum University on X">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23.2 22h-6.3L12 14.8 5.7 22H2.5l7.3-8.4L1.8 2h6.5l4.5 6.7L18.9 2Zm-1.1 17.8h1.7L7.3 4.1H5.5l12.3 15.7Z"/></svg>
                        </a>
                        <a class="social-link" href="https://www.youtube.com/@OdaBultumUniversity-j4n" target="_blank" rel="noopener noreferrer" aria-label="Oda Bultum University on YouTube">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.3 3.6-6.3 3.6Z"/></svg>
                        </a>
                        <a class="social-link" href="https://www.instagram.com/odabultumuniversity/" target="_blank" rel="noopener noreferrer" aria-label="Oda Bultum University on Instagram">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2.2A2.8 2.8 0 0 0 4.2 7v10A2.8 2.8 0 0 0 7 19.8h10a2.8 2.8 0 0 0 2.8-2.8V7A2.8 2.8 0 0 0 17 4.2H7Zm5 3.1a4.7 4.7 0 1 1 0 9.4 4.7 4.7 0 0 1 0-9.4Zm0 2.2a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5Zm5.1-3.7a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2Z"/></svg>
                        </a>
                    </div>
                </div>
                <div class="footer-group">
                    <h3>University</h3>
                    <div class="footer-links">
                        <a href="https://obu.edu.et/?page_id=3149" target="_blank" rel="noopener noreferrer">About OBU</a>
                        <a href="https://obu.edu.et/?page_id=2538" target="_blank" rel="noopener noreferrer">Leadership</a>
                        <a href="https://obu.edu.et/?page_id=924" target="_blank" rel="noopener noreferrer">Mission &amp; vision</a>
                        <a href="https://obu.edu.et/?page_id=3905" target="_blank" rel="noopener noreferrer">Contact</a>
                    </div>
                </div>
                <div class="footer-group">
                    <h3>Academics</h3>
                    <div class="footer-links">
                        <a href="https://obu.edu.et/?page_id=2627" target="_blank" rel="noopener noreferrer">Institutes</a>
                        <a href="https://obu.edu.et/?page_id=2642" target="_blank" rel="noopener noreferrer">Colleges</a>
                        <a href="https://obu.edu.et/?page_id=2652" target="_blank" rel="noopener noreferrer">Schools</a>
                        <a href="https://obu.edu.et/?page_id=3527" target="_blank" rel="noopener noreferrer">Departments</a>
                        <a href="https://obu.edu.et/?page_id=11" target="_blank" rel="noopener noreferrer">Programs</a>
                    </div>
                </div>
                <div class="footer-group">
                    <h3>Research &amp; service</h3>
                    <div class="footer-links">
                        <a href="https://obu.edu.et/?page_id=3777" target="_blank" rel="noopener noreferrer">Research projects</a>
                        <a href="https://obu.edu.et/?page_id=3102" target="_blank" rel="noopener noreferrer">Publications</a>
                        <a href="https://obu.edu.et/?page_id=3268" target="_blank" rel="noopener noreferrer">Community engagement</a>
                    </div>
                </div>
                <div class="footer-group">
                    <h3>Digital services</h3>
                    <div class="footer-links">
                        <a href="https://obu.edu.et/lp-profile/" target="_blank" rel="noopener noreferrer">Student portal</a>
                        <a href="https://obu.edu.et/?page_id=3177" target="_blank" rel="noopener noreferrer">Staff portal</a>
                        <a href="https://ndl.ethernet.edu.et/" target="_blank" rel="noopener noreferrer">Digital library</a>
                        <a href="https://obu.edu.et" target="_blank" rel="noopener noreferrer">Official website</a>
                        <a href="{{ url('/admin/login') }}">Hospital system sign in</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© {{ date('Y') }} Oda Bultum University Hospital Management System.</span>
                <span>Productive Mind for Development.</span>
            </div>
        </div>
    </footer>
</body>
</html>
