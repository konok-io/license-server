<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $s['site_name'] ?? config('app.name') }}</title>
    <meta name="description" content="{{ $s['meta_description'] ?? '' }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0c0c14;
            --bg-secondary: #0f0f1a;
            --bg-card: #12121c;
            --accent-gold: #f5a623;
            --accent-gold-dark: #e09600;
            --accent-gold-glow: rgba(245, 166, 35, 0.3);
            --accent-gold-subtle: rgba(245, 166, 35, 0.1);
            --accent-blue: #4f8cff;
            --accent-green: #4ade80;
            --text-primary: #ffffff;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            --border-color: rgba(245, 166, 35, 0.2);
            --border-color-light: rgba(245, 166, 35, 0.1);
            --ink: var(--bg-primary);
            --ink-2: var(--bg-secondary);
            --accent: var(--accent-gold);
            --accent-2: var(--accent-gold-dark);
            --accent-glow: var(--accent-gold-glow);
            --muted: var(--text-secondary);
            --line: var(--border-color);
            --soft: var(--bg-card);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { background: var(--bg-primary); color: var(--text-primary); font-family: 'Inter', system-ui, sans-serif; line-height: 1.65; overflow-x: hidden; }
        h1,h2,h3,h4,.font-head { font-family: 'Inter', system-ui, sans-serif; }
        a { text-decoration: none; }
        .container { max-width: 1180px; }

        /* Reveal animation */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.in { opacity: 1; transform: none; }

        /* Buttons */
        .btn-accent { background: var(--accent-gold); border: none; color: var(--bg-primary); font-weight: 700; padding: .7rem 1.5rem; border-radius: 10px; transition: .3s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-accent:hover { background: var(--accent-gold-dark); color: var(--bg-primary); transform: translateY(-2px); box-shadow: 0 10px 24px rgba(245,166,35,.35); }
        .btn-ghost { border: 1px solid var(--border-color); color: var(--text-primary); font-weight: 600; padding: .7rem 1.5rem; border-radius: 10px; transition: .2s; background: transparent; }
        .btn-ghost:hover { border-color: var(--accent-gold); color: var(--accent-gold); background: var(--accent-gold-subtle); }

        /* ===== Navbar ===== */
        .nav-wrap { position: sticky; top: 0; z-index: 200; background: rgba(12, 12, 20, 0.9); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border-color); }
        .brand { display: flex; align-items: center; gap: .6rem; font-family: 'Inter', sans-serif; font-weight: 700; color: var(--accent-gold); font-size: 1rem; letter-spacing: 1px; }
        .brand .mark { width: 34px; height: 34px; border-radius: 9px; background: var(--accent-gold-subtle); display: grid; place-items: center; color: var(--accent-gold); font-size: 1.1rem; }
        .nav-link-c { color: var(--text-secondary); font-weight: 500; padding: .4rem .9rem; font-size: .95rem; }
        .nav-link-c:hover { color: var(--accent-gold); }

        /* ===== Hero ===== */
        .hero { position: relative; color: var(--text-primary); padding: 8rem 0 7.5rem; overflow: hidden; background: var(--bg-primary); }
        .hero-grid-bg { position: absolute; inset: 0; background-image: linear-gradient(rgba(245,166,35,.03) 1px, transparent 1px), linear-gradient(90deg, rgba(245,166,35,.03) 1px, transparent 1px); background-size: 46px 46px; }
        .hero .glow { position: absolute; width: 520px; height: 520px; border-radius: 50%; filter: blur(60px); opacity: .4; }
        .hero .glow.g1 { background: radial-gradient(circle, var(--accent-gold-glow), transparent 60%); top: -160px; right: -120px; }
        .hero .glow.g2 { background: radial-gradient(circle, rgba(245,166,35,.15), transparent 60%); bottom: -200px; left: -140px; }
        .hero-badge { display: inline-flex; align-items: center; gap: .5rem; background: var(--accent-gold-subtle); border: 1px solid var(--border-color); color: var(--accent-gold); padding: .4rem 1rem; border-radius: 100px; font-size: .82rem; font-weight: 600; }
        .hero h1 { font-size: clamp(2.2rem, 4.6vw, 3.6rem); font-weight: 800; line-height: 1.1; letter-spacing: -.02em; }
        .hero .lead { color: var(--text-secondary); font-size: 1.16rem; max-width: 600px; }

        /* Terminal mock */
        .terminal { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0 30px 70px rgba(0,0,0,.5), 0 0 100px rgba(245,166,35,.05); overflow: hidden; }
        .terminal .bar { display: flex; align-items: center; gap: .4rem; padding: .7rem 1rem; background: var(--bg-primary); border-bottom: 1px solid var(--border-color); }
        .terminal .bar span { width: 11px; height: 11px; border-radius: 50%; }
        .terminal .bar .r { background: #ff5f56; } .terminal .bar .y { background: #ffbd2e; } .terminal .bar .g { background: #27c93f; }
        .terminal .bar .t { margin-left: auto; color: var(--text-muted); font-size: .78rem; font-family: monospace; }
        .terminal pre { margin: 0; padding: 1.2rem; font-family: 'Fira Code', monospace; font-size: .85rem; color: var(--text-secondary); line-height: 1.8; overflow-x: auto; }
        .terminal .cmt { color: var(--text-muted); } .terminal .grn { color: var(--accent-green); } .terminal .ylw { color: var(--accent-gold); } .terminal .cyn { color: var(--accent-blue); }

        /* ===== Sections ===== */
        section { padding: 5.5rem 0; position: relative; z-index: 1; }
        .eyebrow { color: var(--accent-gold); font-weight: 700; letter-spacing: .1em; text-transform: uppercase; font-size: .78rem; }
        .sec-title { font-size: clamp(1.7rem, 3.2vw, 2.5rem); font-weight: 800; letter-spacing: -.02em; }
        .sec-sub { color: var(--text-secondary); max-width: 660px; }

        /* Stats */
        .stats { background: var(--bg-card); color: var(--text-primary); padding: 3.2rem 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
        .stat-v { font-size: 2.4rem; font-weight: 800; color: var(--accent-gold); }
        .stat-l { color: var(--text-secondary); font-weight: 500; font-size: .85rem; text-transform: uppercase; letter-spacing: .08em; }

        /* Feature cards */
        .f-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.9rem; height: 100%; transition: .3s; }
        .f-card:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(0,0,0,.4); border-color: var(--accent-gold); }
        .f-icon { width: 52px; height: 52px; border-radius: 13px; background: var(--accent-gold-subtle); color: var(--accent-gold); display: grid; place-items: center; font-size: 1.5rem; margin-bottom: 1.1rem; }
        .f-card h5 { font-weight: 700; margin-bottom: .5rem; }
        .f-card p { color: var(--text-secondary); font-size: .93rem; margin: 0; }

        /* How it works */
        .how-wrap { background: var(--bg-secondary); }
        .step { position: relative; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem 1.7rem; height: 100%; }
        .step-num { position: absolute; top: -18px; left: 1.7rem; width: 40px; height: 40px; border-radius: 11px; background: linear-gradient(135deg,var(--accent-gold),var(--accent-gold-dark)); color: var(--bg-primary); font-weight: 700; display: grid; place-items: center; box-shadow: 0 8px 18px var(--accent-gold-glow); }
        .step h5 { margin-top: .8rem; font-weight: 700; }
        .step p { color: var(--text-secondary); font-size: .93rem; margin: 0; }

        /* Security */
        .sec-dark { background: var(--bg-card); color: var(--text-primary); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
        .sec-list li { display: flex; align-items: flex-start; gap: .7rem; padding: .55rem 0; color: var(--text-secondary); }
        .sec-list i { color: var(--accent-gold); font-size: 1.15rem; margin-top: .1rem; }
        .shield-badge { width: 180px; height: 180px; border-radius: 50%; background: var(--accent-gold-subtle); border: 2px solid var(--border-color); display: grid; place-items: center; font-size: 5rem; color: var(--accent-gold); box-shadow: 0 0 80px var(--accent-gold-glow); }

        /* FAQ */
        .faq-item { border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: .8rem; overflow: hidden; background: var(--bg-card); }
        .faq-q { padding: 1.1rem 1.3rem; font-weight: 600; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: .98rem; }
        .faq-q i { transition: .25s; color: var(--accent-gold); }
        .faq-a { max-height: 0; overflow: hidden; transition: max-height .3s ease; color: var(--text-secondary); padding: 0 1.3rem; }
        .faq-item.open .faq-a { max-height: 240px; padding: 0 1.3rem 1.2rem; }
        .faq-item.open .faq-q i { transform: rotate(45deg); }

        /* CTA */
        .cta-band { background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 24px; padding: 4rem 2rem; text-align: center; position: relative; overflow: hidden; }
        .cta-band:after { content:''; position:absolute; width:340px; height:340px; border-radius:50%; background:var(--accent-gold-subtle); top:-120px; right:-80px; }

        /* Professional Footer */
        footer { background: var(--bg-card); color: var(--text-secondary); border-top: 1px solid var(--border-color); }
        .footer-main { padding: 4rem 0 3rem; }
        .footer-brand { margin-bottom: 1.5rem; }
        .footer-brand .brand { font-size: 1.1rem; }
        .footer-brand p { font-size: 0.9rem; line-height: 1.7; max-width: 320px; }
        .footer-heading { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-primary); margin-bottom: 1.25rem; }
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 0.75rem; }
        .footer-links a { color: var(--text-secondary); font-size: 0.9rem; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 0.5rem; }
        .footer-links a:hover { color: var(--accent-gold); transform: translateX(4px); }
        .footer-links a i { font-size: 0.75rem; }
        .footer-social { display: flex; gap: 1rem; margin-top: 0.5rem; }
        .footer-social a { width: 40px; height: 40px; border-radius: 10px; background: var(--bg-primary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); transition: all 0.3s ease; }
        .footer-social a:hover { background: var(--accent-gold); border-color: var(--accent-gold); color: var(--bg-primary); transform: translateY(-3px); }
        .footer-bottom { border-top: 1px solid var(--border-color); padding: 1.5rem 0; }
        .footer-bottom-content { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .footer-copyright { font-size: 0.85rem; }
        .footer-legal { display: flex; gap: 1.5rem; }
        .footer-legal a { font-size: 0.85rem; color: var(--text-muted); }
        .footer-legal a:hover { color: var(--accent-gold); }
        @media (max-width: 768px) {
            .footer-main { padding: 3rem 0 2rem; }
            .footer-bottom-content { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

{{-- ===== Navbar ===== --}}
<nav class="nav-wrap">
    <div class="container d-flex align-items-center justify-content-between py-3">
        <a href="{{ url('/') }}" class="brand">
            <span class="mark"><i class="bi bi-shield-lock-fill"></i></span>
            {{ $s['site_name'] ?? config('app.name') }}
        </a>
        <div class="d-none d-lg-flex align-items-center">
            <a href="#features" class="nav-link-c">Features</a>
            <a href="#how" class="nav-link-c">How it works</a>
            <a href="#security" class="nav-link-c">Security</a>
            <a href="#faq" class="nav-link-c">FAQ</a>
            <a href="{{ $s['hero_primary_url'] ?? '/login' }}" class="btn btn-accent btn-sm ms-3">{{ $s['hero_primary_text'] ?? 'Admin Login' }}</a>
        </div>
        <a href="{{ $s['hero_primary_url'] ?? '/login' }}" class="btn btn-accent btn-sm d-lg-none">{{ $s['hero_primary_text'] ?? 'Login' }}</a>
    </div>
</nav>

{{-- ===== Hero ===== --}}
<header class="hero">
    <div class="hero-grid-bg"></div>
    <div class="glow g1"></div>
    <div class="glow g2"></div>
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                @if($s['hero_badge'] ?? false)
                    <span class="hero-badge mb-4"><i class="bi bi-stars"></i>{{ $s['hero_badge'] }}</span>
                @endif
                <h1 class="mb-3">{{ $s['hero_title'] ?? 'License Your Software with Total Control' }}</h1>
                <p class="lead mb-4">{{ $s['hero_subtitle'] ?? '' }}</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ $s['hero_primary_url'] ?? '/login' }}" class="btn btn-accent btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>{{ $s['hero_primary_text'] ?? 'Admin Login' }}
                    </a>
                    @if($s['hero_secondary_text'] ?? false)
                        <a href="{{ $s['hero_secondary_url'] ?? '#how' }}" class="btn btn-ghost btn-lg">{{ $s['hero_secondary_text'] }}</a>
                    @endif
                </div>
            </div>
            <div class="col-lg-6 reveal">
                <div class="terminal">
                    <div class="bar"><span class="r"></span><span class="y"></span><span class="g"></span><span class="t">license-verify</span></div>
                    <pre><span class="cmt"># client checks in with the server</span>
$ php artisan <span class="cyn">mrh-license:verify</span> --force

<span class="grn">✓</span> Signed response verified
  status      <span class="grn">active</span>
  domain      <span class="ylw">client-app.com</span>
  expires_at  <span class="ylw">2026-12-31</span>
  installs    <span class="ylw">1 / 1</span>

<span class="cmt"># expired or revoked keys are locked out</span>
<span class="grn">✓</span> Enforcement applied locally</pre>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- ===== Stats ===== --}}
@if(($s['show_stats'] ?? '1') === '1')
<div class="stats">
    <div class="container">
        <div class="row text-center g-4">
            @foreach([1,2,3,4] as $i)
                @if(($s["stat_{$i}_value"] ?? false))
                    <div class="col-6 col-md-3">
                        <div class="stat-v">{{ $s["stat_{$i}_value"] }}</div>
                        <div class="stat-l">{{ $s["stat_{$i}_label"] ?? '' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ===== Features ===== --}}
@if(($s['show_features'] ?? '1') === '1')
<section id="features">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <div class="eyebrow mb-2">Features</div>
            <h2 class="sec-title mb-3">{{ $s['features_title'] ?? 'Everything you need' }}</h2>
            <p class="sec-sub mx-auto">{{ $s['features_subtitle'] ?? '' }}</p>
        </div>
        <div class="row g-4">
            @foreach([1,2,3,4,5,6] as $i)
                @if(($s["feature_{$i}_title"] ?? false))
                    <div class="col-md-6 col-lg-4 reveal">
                        <div class="f-card">
                            <div class="f-icon"><i class="bi {{ $s["feature_{$i}_icon"] ?? 'bi-check-circle' }}"></i></div>
                            <h5>{{ $s["feature_{$i}_title"] }}</h5>
                            <p>{{ $s["feature_{$i}_text"] ?? '' }}</p>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== How it works ===== --}}
@if(($s['show_how'] ?? '1') === '1')
<section id="how" class="how-wrap">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <div class="eyebrow mb-2">Workflow</div>
            <h2 class="sec-title mb-3">{{ $s['how_title'] ?? 'How it works' }}</h2>
            <p class="sec-sub mx-auto">{{ $s['how_subtitle'] ?? '' }}</p>
        </div>
        <div class="row g-4">
            @foreach([1,2,3] as $i)
                @if(($s["how_{$i}_title"] ?? false))
                    <div class="col-md-4 reveal">
                        <div class="step">
                            <div class="step-num">{{ $i }}</div>
                            <h5>{{ $s["how_{$i}_title"] }}</h5>
                            <p>{{ $s["how_{$i}_text"] ?? '' }}</p>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== Security ===== --}}
@if(($s['show_security'] ?? '1') === '1')
<section id="security" class="sec-dark">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 reveal">
                <div class="eyebrow mb-2" style="color:var(--accent-gold)">Security</div>
                <h2 class="sec-title mb-3">{{ $s['security_title'] ?? 'Security you can audit' }}</h2>
                <p class="mb-4" style="color:#aebfd2">{{ $s['security_text'] ?? '' }}</p>
                <ul class="sec-list list-unstyled mb-0">
                    @foreach([1,2,3,4] as $i)
                        @if(($s["security_{$i}"] ?? false))
                            <li><i class="bi bi-check-circle-fill"></i>{{ $s["security_{$i}"] }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div class="col-lg-5 text-center reveal">
                <div class="shield-badge mx-auto"><i class="bi bi-shield-check"></i></div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ===== About ===== --}}
@if($s['about_title'] ?? false)
<section id="about">
    <div class="container">
        <div class="row justify-content-center text-center reveal">
            <div class="col-lg-8">
                <div class="eyebrow mb-2">About</div>
                <h2 class="sec-title mb-3">{{ $s['about_title'] }}</h2>
                <p class="sec-sub mx-auto">{{ $s['about_text'] ?? '' }}</p>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ===== FAQ ===== --}}
@if(($s['show_faq'] ?? '1') === '1')
<section id="faq" class="how-wrap">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <div class="eyebrow mb-2">FAQ</div>
            <h2 class="sec-title">{{ $s['faq_title'] ?? 'Frequently asked questions' }}</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8 reveal">
                @foreach([1,2,3,4] as $i)
                    @if(($s["faq_{$i}_q"] ?? false))
                        <div class="faq-item">
                            <div class="faq-q">{{ $s["faq_{$i}_q"] }}<i class="bi bi-plus-lg"></i></div>
                            <div class="faq-a"><p class="pt-0">{{ $s["faq_{$i}_a"] ?? '' }}</p></div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ===== CTA ===== --}}
<section>
    <div class="container">
        <div class="cta-band reveal">
            <h2 class="fw-bold mb-2 position-relative">{{ $s['cta_title'] ?? 'Ready to secure your software?' }}</h2>
            <p class="mb-4 opacity-75 position-relative">{{ $s['cta_text'] ?? '' }}</p>
            <a href="{{ $s['cta_button_url'] ?? '/login' }}" class="btn btn-accent btn-lg position-relative">
                <i class="bi bi-box-arrow-in-right me-2"></i>{{ $s['cta_button_text'] ?? 'Go to Admin' }}
            </a>
        </div>
    </div>
</section>

{{-- ===== Footer ===== --}}
<footer>
    <div class="footer-main">
        <div class="container">
            <div class="row g-5">
                <!-- Brand Column -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">
                        <div class="brand mb-3">
                            <span class="brand-icon"><i class="bi bi-shield-lock-fill"></i></span>
                            {{ $s['site_name'] ?? config('app.name') }}
                        </div>
                        <p>{{ $s['footer_text'] ?? 'A secure, self-hosted licensing solution for your software products.' }}</p>
                    </div>
                    <div class="footer-social">
                        @if($s['social_github'] ?? false)
                            <a href="{{ $s['social_github'] }}" target="_blank" aria-label="GitHub"><i class="bi bi-github"></i></a>
                        @endif
                        @if($s['social_linkedin'] ?? false)
                            <a href="{{ $s['social_linkedin'] }}" target="_blank" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                        @endif
                        @if($s['social_twitter'] ?? false)
                            <a href="{{ $s['social_twitter'] }}" target="_blank" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                        @endif
                        @if($s['contact_email'] ?? false)
                            <a href="mailto:{{ $s['contact_email'] }}" aria-label="Email"><i class="bi bi-envelope"></i></a>
                        @endif
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="#features"><i class="bi bi-chevron-right"></i> Features</a></li>
                        <li><a href="#how"><i class="bi bi-chevron-right"></i> How It Works</a></li>
                        <li><a href="#security"><i class="bi bi-chevron-right"></i> Security</a></li>
                        <li><a href="#faq"><i class="bi bi-chevron-right"></i> FAQ</a></li>
                    </ul>
                </div>
                
                <!-- Support -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Support</h6>
                    <ul class="footer-links">
                        <li><a href="{{ $s['hero_primary_url'] ?? '/login' }}"><i class="bi bi-chevron-right"></i> Admin Login</a></li>
                        @if($s['contact_email'] ?? false)
                            <li><a href="mailto:{{ $s['contact_email'] }}"><i class="bi bi-chevron-right"></i> Contact Us</a></li>
                        @endif
                    </ul>
                </div>
                
                <!-- Contact -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-heading">Get In Touch</h6>
                    <ul class="footer-links">
                        @if($s['contact_email'] ?? false)
                            <li>
                                <a href="mailto:{{ $s['contact_email'] }}">
                                    <i class="bi bi-envelope"></i>
                                    {{ $s['contact_email'] }}
                                </a>
                            </li>
                        @endif
                        <li>
                            <span style="color: var(--text-secondary); display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
                                <i class="bi bi-shield-check"></i>
                                RSA-4096 Encrypted
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <div class="footer-copyright">
                    <span>© {{ date('Y') }} {{ $s['site_name'] ?? config('app.name') }}. All rights reserved.</span>
                </div>
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
    // Reveal on scroll
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: .12 });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));

    // FAQ accordion
    document.querySelectorAll('.faq-q').forEach(q => {
        q.addEventListener('click', () => {
            const item = q.closest('.faq-item');
            const open = item.classList.contains('open');
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
            if (!open) item.classList.add('open');
        });
    });
</script>
</body>
</html>
