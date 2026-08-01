<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($s['site_name'] ?? 'Saudi License Server'); ?></title>
    <meta name="description" content="<?php echo e($s['meta_description'] ?? ''); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0a1420;
            --ink-2: #0f1f33;
            --accent: #2e8b6f;
            --accent-2: #3fb389;
            --accent-glow: rgba(63,179,137,.4);
            --muted: #6b7a8d;
            --line: #e6ebf1;
            --soft: #eef6f3;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { background: #fff; color: var(--ink); font-family: 'Inter', system-ui, sans-serif; line-height: 1.65; overflow-x: hidden; }
        h1,h2,h3,h4,.font-head { font-family: 'Sora', system-ui, sans-serif; }
        a { text-decoration: none; }
        .container { max-width: 1180px; }

        /* Reveal animation */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.in { opacity: 1; transform: none; }

        /* Buttons */
        .btn-accent { background: var(--accent); border: none; color: #fff; font-weight: 600; padding: .7rem 1.5rem; border-radius: 10px; transition: .2s; }
        .btn-accent:hover { background: #256f59; color: #fff; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(46,139,111,.35); }
        .btn-ghost { border: 1px solid rgba(255,255,255,.25); color: #eaf1f8; font-weight: 600; padding: .7rem 1.5rem; border-radius: 10px; transition: .2s; background: transparent; }
        .btn-ghost:hover { border-color: var(--accent-2); color: #fff; background: rgba(63,179,137,.12); }

        /* ===== Navbar ===== */
        .nav-wrap { position: sticky; top: 0; z-index: 200; background: rgba(255,255,255,.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--line); }
        .brand { display: flex; align-items: center; gap: .6rem; font-family: 'Sora'; font-weight: 800; color: var(--ink); font-size: 1.12rem; }
        .brand .mark { width: 34px; height: 34px; border-radius: 9px; background: linear-gradient(135deg, var(--accent), var(--accent-2)); display: grid; place-items: center; color: #fff; font-size: 1.1rem; box-shadow: 0 4px 14px var(--accent-glow); }
        .nav-link-c { color: var(--muted); font-weight: 500; padding: .4rem .9rem; font-size: .95rem; }
        .nav-link-c:hover { color: var(--accent); }

        /* ===== Hero ===== */
        .hero { position: relative; background: radial-gradient(1200px 600px at 80% -10%, #16324d 0%, transparent 60%), linear-gradient(165deg, var(--ink) 0%, var(--ink-2) 100%); color: #eaf1f8; padding: 6.5rem 0 7.5rem; overflow: hidden; }
        .hero-grid-bg { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 46px 46px; mask-image: radial-gradient(700px 400px at 30% 20%, #000 30%, transparent 75%); }
        .hero .glow { position: absolute; width: 520px; height: 520px; border-radius: 50%; filter: blur(40px); opacity: .5; }
        .hero .glow.g1 { background: radial-gradient(circle, var(--accent-glow), transparent 60%); top: -160px; right: -120px; }
        .hero .glow.g2 { background: radial-gradient(circle, rgba(46,139,111,.3), transparent 60%); bottom: -200px; left: -140px; }
        .hero-badge { display: inline-flex; align-items: center; gap: .5rem; background: rgba(63,179,137,.14); border: 1px solid rgba(63,179,137,.3); color: #8fe3c6; padding: .4rem 1rem; border-radius: 100px; font-size: .82rem; font-weight: 600; }
        .hero h1 { font-size: clamp(2.2rem, 4.6vw, 3.6rem); font-weight: 800; line-height: 1.1; letter-spacing: -.02em; }
        .hero .lead { color: #aebfd2; font-size: 1.16rem; max-width: 600px; }

        /* Terminal mock */
        .terminal { background: #0c1826; border: 1px solid rgba(255,255,255,.1); border-radius: 14px; box-shadow: 0 30px 70px rgba(0,0,0,.45); overflow: hidden; }
        .terminal .bar { display: flex; align-items: center; gap: .4rem; padding: .7rem 1rem; background: #0a1420; border-bottom: 1px solid rgba(255,255,255,.06); }
        .terminal .bar span { width: 11px; height: 11px; border-radius: 50%; }
        .terminal .bar .r { background: #ff5f56; } .terminal .bar .y { background: #ffbd2e; } .terminal .bar .g { background: #27c93f; }
        .terminal .bar .t { margin-left: auto; color: #5f708a; font-size: .78rem; font-family: monospace; }
        .terminal pre { margin: 0; padding: 1.2rem; font-family: 'Fira Code', monospace; font-size: .82rem; color: #cbd6e6; line-height: 1.8; overflow-x: auto; }
        .terminal .cmt { color: #5f708a; } .terminal .grn { color: #3fb389; } .terminal .ylw { color: #e6c07b; } .terminal .cyn { color: #56b6c2; }

        /* ===== Sections ===== */
        section { padding: 5.5rem 0; }
        .eyebrow { color: var(--accent); font-weight: 700; letter-spacing: .1em; text-transform: uppercase; font-size: .78rem; }
        .sec-title { font-size: clamp(1.7rem, 3.2vw, 2.5rem); font-weight: 800; letter-spacing: -.02em; }
        .sec-sub { color: var(--muted); max-width: 660px; }

        /* Stats */
        .stats { background: var(--ink); color: #fff; padding: 3.2rem 0; }
        .stat-v { font-family: 'Sora'; font-size: 2.4rem; font-weight: 800; background: linear-gradient(120deg,#fff,#8fe3c6); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-l { color: #91a2b7; font-weight: 500; font-size: .85rem; text-transform: uppercase; letter-spacing: .08em; }

        /* Feature cards */
        .f-card { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 1.9rem; height: 100%; transition: .22s; position: relative; overflow: hidden; }
        .f-card:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(10,20,32,.09); border-color: rgba(46,139,111,.4); }
        .f-icon { width: 52px; height: 52px; border-radius: 13px; background: var(--soft); color: var(--accent); display: grid; place-items: center; font-size: 1.5rem; margin-bottom: 1.1rem; }
        .f-card h5 { font-weight: 700; margin-bottom: .5rem; }
        .f-card p { color: var(--muted); font-size: .93rem; margin: 0; }

        /* How it works */
        .how-wrap { background: linear-gradient(180deg, #fff 0%, var(--soft) 100%); }
        .step { position: relative; background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 2rem 1.7rem; height: 100%; }
        .step-num { position: absolute; top: -18px; left: 1.7rem; width: 40px; height: 40px; border-radius: 11px; background: linear-gradient(135deg,var(--accent),var(--accent-2)); color: #fff; font-family: 'Sora'; font-weight: 700; display: grid; place-items: center; box-shadow: 0 8px 18px var(--accent-glow); }
        .step h5 { margin-top: .8rem; font-weight: 700; }
        .step p { color: var(--muted); font-size: .93rem; margin: 0; }

        /* Security */
        .sec-dark { background: radial-gradient(900px 500px at 90% 10%, #16324d 0%, transparent 55%), linear-gradient(165deg, var(--ink) 0%, var(--ink-2) 100%); color: #eaf1f8; }
        .sec-list li { display: flex; align-items: flex-start; gap: .7rem; padding: .55rem 0; color: #c4d2e2; }
        .sec-list i { color: var(--accent-2); font-size: 1.15rem; margin-top: .1rem; }
        .shield-badge { width: 150px; height: 150px; border-radius: 50%; background: rgba(63,179,137,.1); border: 1px solid rgba(63,179,137,.3); display: grid; place-items: center; font-size: 4rem; color: var(--accent-2); box-shadow: inset 0 0 40px rgba(63,179,137,.15); }

        /* FAQ */
        .faq-item { border: 1px solid var(--line); border-radius: 12px; margin-bottom: .8rem; overflow: hidden; background: #fff; }
        .faq-q { padding: 1.1rem 1.3rem; font-weight: 600; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-family: 'Sora'; font-size: .98rem; }
        .faq-q i { transition: .25s; color: var(--accent); }
        .faq-a { max-height: 0; overflow: hidden; transition: max-height .3s ease; color: var(--muted); padding: 0 1.3rem; }
        .faq-item.open .faq-a { max-height: 240px; padding: 0 1.3rem 1.2rem; }
        .faq-item.open .faq-q i { transform: rotate(45deg); }

        /* CTA */
        .cta-band { background: linear-gradient(135deg, var(--accent) 0%, #1f6650 100%); color: #fff; border-radius: 24px; padding: 4rem 2rem; text-align: center; position: relative; overflow: hidden; }
        .cta-band:after { content:''; position:absolute; width:340px; height:340px; border-radius:50%; background:rgba(255,255,255,.08); top:-120px; right:-80px; }

        footer { background: var(--ink); color: #91a2b7; padding: 3rem 0 2rem; }
        footer a { color: #91a2b7; } footer a:hover { color: var(--accent-2); }
        .foot-mark { width: 30px; height: 30px; border-radius: 8px; background: linear-gradient(135deg,var(--accent),var(--accent-2)); display:grid; place-items:center; color:#fff; }
    </style>
</head>
<body>


<nav class="nav-wrap">
    <div class="container d-flex align-items-center justify-content-between py-3">
        <a href="<?php echo e(url('/')); ?>" class="brand">
            <span class="mark"><i class="bi bi-shield-lock-fill"></i></span>
            <?php echo e($s['site_name'] ?? 'Saudi License Server'); ?>

        </a>
        <div class="d-none d-lg-flex align-items-center">
            <a href="#features" class="nav-link-c">Features</a>
            <a href="#how" class="nav-link-c">How it works</a>
            <a href="#security" class="nav-link-c">Security</a>
            <a href="#faq" class="nav-link-c">FAQ</a>
            <a href="<?php echo e($s['hero_primary_url'] ?? '/login'); ?>" class="btn btn-accent btn-sm ms-3"><?php echo e($s['hero_primary_text'] ?? 'Admin Login'); ?></a>
        </div>
        <a href="<?php echo e($s['hero_primary_url'] ?? '/login'); ?>" class="btn btn-accent btn-sm d-lg-none"><?php echo e($s['hero_primary_text'] ?? 'Login'); ?></a>
    </div>
</nav>


<header class="hero">
    <div class="hero-grid-bg"></div>
    <div class="glow g1"></div>
    <div class="glow g2"></div>
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <?php if($s['hero_badge'] ?? false): ?>
                    <span class="hero-badge mb-4"><i class="bi bi-stars"></i><?php echo e($s['hero_badge']); ?></span>
                <?php endif; ?>
                <h1 class="mb-3"><?php echo e($s['hero_title'] ?? 'License Your Software with Total Control'); ?></h1>
                <p class="lead mb-4"><?php echo e($s['hero_subtitle'] ?? ''); ?></p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo e($s['hero_primary_url'] ?? '/login'); ?>" class="btn btn-accent btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i><?php echo e($s['hero_primary_text'] ?? 'Admin Login'); ?>

                    </a>
                    <?php if($s['hero_secondary_text'] ?? false): ?>
                        <a href="<?php echo e($s['hero_secondary_url'] ?? '#how'); ?>" class="btn btn-ghost btn-lg"><?php echo e($s['hero_secondary_text']); ?></a>
                    <?php endif; ?>
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


<?php if(($s['show_stats'] ?? '1') === '1'): ?>
<div class="stats">
    <div class="container">
        <div class="row text-center g-4">
            <?php $__currentLoopData = [1,2,3,4]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(($s["stat_{$i}_value"] ?? false)): ?>
                    <div class="col-6 col-md-3">
                        <div class="stat-v"><?php echo e($s["stat_{$i}_value"]); ?></div>
                        <div class="stat-l"><?php echo e($s["stat_{$i}_label"] ?? ''); ?></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php endif; ?>


<?php if(($s['show_features'] ?? '1') === '1'): ?>
<section id="features">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <div class="eyebrow mb-2">Features</div>
            <h2 class="sec-title mb-3"><?php echo e($s['features_title'] ?? 'Everything you need'); ?></h2>
            <p class="sec-sub mx-auto"><?php echo e($s['features_subtitle'] ?? ''); ?></p>
        </div>
        <div class="row g-4">
            <?php $__currentLoopData = [1,2,3,4,5,6]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(($s["feature_{$i}_title"] ?? false)): ?>
                    <div class="col-md-6 col-lg-4 reveal">
                        <div class="f-card">
                            <div class="f-icon"><i class="bi <?php echo e($s["feature_{$i}_icon"] ?? 'bi-check-circle'); ?>"></i></div>
                            <h5><?php echo e($s["feature_{$i}_title"]); ?></h5>
                            <p><?php echo e($s["feature_{$i}_text"] ?? ''); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>


<?php if(($s['show_how'] ?? '1') === '1'): ?>
<section id="how" class="how-wrap">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <div class="eyebrow mb-2">Workflow</div>
            <h2 class="sec-title mb-3"><?php echo e($s['how_title'] ?? 'How it works'); ?></h2>
            <p class="sec-sub mx-auto"><?php echo e($s['how_subtitle'] ?? ''); ?></p>
        </div>
        <div class="row g-4">
            <?php $__currentLoopData = [1,2,3]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(($s["how_{$i}_title"] ?? false)): ?>
                    <div class="col-md-4 reveal">
                        <div class="step">
                            <div class="step-num"><?php echo e($i); ?></div>
                            <h5><?php echo e($s["how_{$i}_title"]); ?></h5>
                            <p><?php echo e($s["how_{$i}_text"] ?? ''); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>


<?php if(($s['show_security'] ?? '1') === '1'): ?>
<section id="security" class="sec-dark">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 reveal">
                <div class="eyebrow mb-2" style="color:var(--accent-2)">Security</div>
                <h2 class="sec-title mb-3"><?php echo e($s['security_title'] ?? 'Security you can audit'); ?></h2>
                <p class="mb-4" style="color:#aebfd2"><?php echo e($s['security_text'] ?? ''); ?></p>
                <ul class="sec-list list-unstyled mb-0">
                    <?php $__currentLoopData = [1,2,3,4]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(($s["security_{$i}"] ?? false)): ?>
                            <li><i class="bi bi-check-circle-fill"></i><?php echo e($s["security_{$i}"]); ?></li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <div class="col-lg-5 text-center reveal">
                <div class="shield-badge mx-auto"><i class="bi bi-shield-check"></i></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<?php if($s['about_title'] ?? false): ?>
<section id="about">
    <div class="container">
        <div class="row justify-content-center text-center reveal">
            <div class="col-lg-8">
                <div class="eyebrow mb-2">About</div>
                <h2 class="sec-title mb-3"><?php echo e($s['about_title']); ?></h2>
                <p class="sec-sub mx-auto"><?php echo e($s['about_text'] ?? ''); ?></p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<?php if(($s['show_faq'] ?? '1') === '1'): ?>
<section id="faq" class="how-wrap">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <div class="eyebrow mb-2">FAQ</div>
            <h2 class="sec-title"><?php echo e($s['faq_title'] ?? 'Frequently asked questions'); ?></h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8 reveal">
                <?php $__currentLoopData = [1,2,3,4]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(($s["faq_{$i}_q"] ?? false)): ?>
                        <div class="faq-item">
                            <div class="faq-q"><?php echo e($s["faq_{$i}_q"]); ?><i class="bi bi-plus-lg"></i></div>
                            <div class="faq-a"><p class="pt-0"><?php echo e($s["faq_{$i}_a"] ?? ''); ?></p></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<section>
    <div class="container">
        <div class="cta-band reveal">
            <h2 class="fw-bold mb-2 position-relative"><?php echo e($s['cta_title'] ?? 'Ready to secure your software?'); ?></h2>
            <p class="mb-4 opacity-75 position-relative"><?php echo e($s['cta_text'] ?? ''); ?></p>
            <a href="<?php echo e($s['cta_button_url'] ?? '/login'); ?>" class="btn btn-light btn-lg fw-semibold position-relative">
                <i class="bi bi-box-arrow-in-right me-2"></i><?php echo e($s['cta_button_text'] ?? 'Go to Admin'); ?>

            </a>
        </div>
    </div>
</section>


<footer>
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-md-6">
                <div class="brand text-white mb-2" style="color:#fff!important">
                    <span class="foot-mark"><i class="bi bi-shield-lock-fill"></i></span>
                    <?php echo e($s['site_name'] ?? 'Saudi License Server'); ?>

                </div>
                <div class="small"><?php echo e($s['footer_text'] ?? ''); ?></div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="mb-2">
                    <?php if($s['social_github'] ?? false): ?><a href="<?php echo e($s['social_github']); ?>" class="me-3" target="_blank"><i class="bi bi-github fs-5"></i></a><?php endif; ?>
                    <?php if($s['social_linkedin'] ?? false): ?><a href="<?php echo e($s['social_linkedin']); ?>" class="me-3" target="_blank"><i class="bi bi-linkedin fs-5"></i></a><?php endif; ?>
                    <?php if($s['social_twitter'] ?? false): ?><a href="<?php echo e($s['social_twitter']); ?>" class="me-3" target="_blank"><i class="bi bi-twitter-x fs-5"></i></a><?php endif; ?>
                    <?php if($s['contact_email'] ?? false): ?><a href="mailto:<?php echo e($s['contact_email']); ?>"><i class="bi bi-envelope fs-5"></i></a><?php endif; ?>
                </div>
                <a href="<?php echo e($s['hero_primary_url'] ?? '/login'); ?>" class="small">Admin Login <i class="bi bi-arrow-right"></i></a>
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
<?php /**PATH C:\laragon\www\license-server\resources\views/home.blade.php ENDPATH**/ ?>