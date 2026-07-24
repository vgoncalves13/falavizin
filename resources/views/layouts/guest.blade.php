<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FD5C3E">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="FalaVizin">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
    <title>{{ config('app.name', 'FalaVizin') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,700;1,9..144,400&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            background: #fafaf9;
        }

        /* ── Animations ─────────────────────────────── */
        @keyframes panelReveal {
            from { opacity: 0; transform: translateX(-24px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes floatA {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-10px); }
        }
        @keyframes floatB {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50%       { transform: translateY(-14px) rotate(3deg); }
        }
        @keyframes windowPulse {
            0%, 100% { opacity: 0.45; }
            50%       { opacity: 0.85; }
        }
        @keyframes starTwinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50%       { opacity: 1;   transform: scale(1.4); }
        }

        .panel-in   { animation: panelReveal 0.9s cubic-bezier(0.16,1,0.3,1) both; }
        .fade-1     { opacity:0; animation: fadeUp .6s cubic-bezier(0.16,1,0.3,1) .1s forwards; }
        .fade-2     { opacity:0; animation: fadeUp .6s cubic-bezier(0.16,1,0.3,1) .2s forwards; }
        .fade-3     { opacity:0; animation: fadeUp .6s cubic-bezier(0.16,1,0.3,1) .3s forwards; }
        .fade-4     { opacity:0; animation: fadeUp .6s cubic-bezier(0.16,1,0.3,1) .4s forwards; }
        .fade-5     { opacity:0; animation: fadeUp .6s cubic-bezier(0.16,1,0.3,1) .5s forwards; }
        .fade-6     { opacity:0; animation: fadeUp .6s cubic-bezier(0.16,1,0.3,1) .6s forwards; }
        .fade-7     { opacity:0; animation: fadeUp .6s cubic-bezier(0.16,1,0.3,1) .7s forwards; }

        .float-slow { animation: floatA 7s ease-in-out infinite; }
        .float-med  { animation: floatB 9s ease-in-out 1.2s infinite; }
        .float-fast { animation: floatA 5.5s ease-in-out 0.6s infinite; }

        .win-pulse  { animation: windowPulse 3.5s ease-in-out infinite; }
        .win-pulse2 { animation: windowPulse 4.2s ease-in-out 1s infinite; }
        .win-pulse3 { animation: windowPulse 2.8s ease-in-out 2s infinite; }
        .star       { animation: starTwinkle 3s ease-in-out infinite; }
        .star2      { animation: starTwinkle 4s ease-in-out .8s infinite; }
        .star3      { animation: starTwinkle 2.5s ease-in-out 1.6s infinite; }

        /* ── Inputs ──────────────────────────────────── */
        .g-input {
            display: block;
            width: 100%;
            padding: .75rem 1rem;
            background: #fff;
            border: 1.5px solid #e7e5e4;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9375rem;
            color: #1c1917;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .g-input:focus {
            border-color: #d97706;
            box-shadow: 0 0 0 3px rgba(217,119,6,.12);
        }
        .g-input::placeholder { color: #a8a29e; }

        .g-btn {
            display: block;
            width: 100%;
            padding: .875rem 1.5rem;
            background: #d97706;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: .9375rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            letter-spacing: .015em;
            transition: background .15s, transform .1s, box-shadow .15s;
        }
        .g-btn:hover {
            background: #b45309;
            box-shadow: 0 6px 20px rgba(180,83,9,.35);
            transform: translateY(-1px);
        }
        .g-btn:active { transform: translateY(0); box-shadow: none; }

        .g-label {
            display: block;
            font-size: .8125rem;
            font-weight: 600;
            color: #57534e;
            letter-spacing: .03em;
            text-transform: uppercase;
            margin-bottom: .45rem;
        }

        /* ── Grid pattern on left panel ─────────────── */
        .grid-pat {
            background-image:
                linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 44px 44px;
        }
    </style>
</head>
<body>
<div style="min-height:100vh; display:flex;">

    {{-- ═══════════════════════════════════════
         LEFT DECORATIVE PANEL
    ═══════════════════════════════════════ --}}
    <div
        class="panel-in grid-pat"
        style="
            display:none;
            width:54%;
            background: linear-gradient(155deg, #78350f 0%, #92400e 28%, #b45309 58%, #d97706 82%, #b45309 100%);
            position:relative;
            overflow:hidden;
            flex-direction:column;
            justify-content:flex-end;
            padding: 3rem 3.25rem;
        "
        id="left-panel"
    >
        {{-- Radial warm glow --}}
        <div style="position:absolute;top:-5%;right:-8%;width:55%;padding-top:55%;border-radius:50%;background:radial-gradient(circle,rgba(251,191,36,.3) 0%,transparent 68%);pointer-events:none;"></div>
        <div style="position:absolute;bottom:25%;left:-5%;width:35%;padding-top:35%;border-radius:50%;background:radial-gradient(circle,rgba(120,53,15,.5) 0%,transparent 70%);pointer-events:none;"></div>

        {{-- Stars --}}
        <div class="star"  style="position:absolute;top:10%;left:22%;width:5px;height:5px;border-radius:50%;background:#fbbf24;"></div>
        <div class="star2" style="position:absolute;top:16%;left:55%;width:3px;height:3px;border-radius:50%;background:#fde68a;"></div>
        <div class="star3" style="position:absolute;top:8%; left:70%;width:4px;height:4px;border-radius:50%;background:#fbbf24;"></div>
        <div class="star"  style="position:absolute;top:22%;left:38%;width:3px;height:3px;border-radius:50%;background:#fde68a;animation-delay:1.5s"></div>
        <div class="star2" style="position:absolute;top:6%; left:84%;width:5px;height:5px;border-radius:50%;background:#fbbf24;animation-delay:.4s"></div>

        {{-- Floating community bubbles --}}
        <div class="float-slow" style="position:absolute;top:14%;left:12%;width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.09);border:1.5px solid rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;">
            <svg width="24" height="24" fill="none" stroke="rgba(255,255,255,.75)" stroke-width="1.6" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <path d="M9 22V12h6v10"/>
            </svg>
        </div>
        <div class="float-med" style="position:absolute;top:22%;right:14%;width:44px;height:44px;border-radius:50%;background:rgba(251,191,36,.15);border:1.5px solid rgba(251,191,36,.35);display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" fill="none" stroke="rgba(251,191,36,.95)" stroke-width="1.6" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="float-fast" style="position:absolute;top:40%;left:62%;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;">
            <svg width="18" height="18" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="1.6" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </div>
        <div class="float-slow" style="position:absolute;top:50%;right:22%;width:36px;height:36px;border-radius:50%;background:rgba(251,191,36,.1);border:1.5px solid rgba(251,191,36,.25);display:flex;align-items:center;justify-content:center;animation-delay:2s">
            <svg width="16" height="16" fill="none" stroke="rgba(251,191,36,.85)" stroke-width="1.6" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>

        {{-- Neighbourhood skyline SVG --}}
        <div style="position:absolute;bottom:0;left:0;right:0;pointer-events:none;">
            <svg viewBox="0 0 900 340" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMax slice" style="width:100%;display:block;">
                {{-- Sky glow at horizon --}}
                <ellipse cx="450" cy="310" rx="420" ry="60" fill="rgba(251,191,36,.08)"/>

                {{-- Ground --}}
                <rect x="0" y="292" width="900" height="48" fill="rgba(78,35,4,.65)"/>
                <rect x="0" y="300" width="900" height="40" fill="rgba(55,25,3,.5)"/>

                {{-- Far left low buildings --}}
                <rect x="0" y="230" width="55" height="62" rx="2" fill="rgba(255,255,255,.06)"/>
                <polygon points="0,230 27,205 55,230" fill="rgba(255,255,255,.08)"/>
                <rect x="8"  y="240" width="10" height="8" rx="1" fill="rgba(251,191,36,.3)"/>
                <rect x="32" y="240" width="10" height="8" rx="1" fill="rgba(251,191,36,.2)"/>

                <rect x="60" y="218" width="48" height="74" rx="2" fill="rgba(255,255,255,.07)"/>
                <polygon points="60,218 84,192 108,218" fill="rgba(255,255,255,.09)"/>
                <rect x="68"  y="230" width="11" height="9" rx="1" class="win-pulse"  fill="rgba(251,191,36,.5)"/>
                <rect x="88"  y="230" width="11" height="9" rx="1" fill="rgba(251,191,36,.2)"/>
                <rect x="68"  y="252" width="11" height="9" rx="1" fill="rgba(251,191,36,.3)"/>
                <rect x="78"  y="263" width="18" height="29" rx="2" fill="rgba(55,25,3,.6)"/>

                {{-- Mid-left --}}
                <rect x="120" y="200" width="72" height="92" rx="3" fill="rgba(255,255,255,.09)"/>
                <polygon points="120,200 156,168 192,200" fill="rgba(255,255,255,.12)"/>
                <rect x="132" y="215" width="14" height="11" rx="1" class="win-pulse2" fill="rgba(251,191,36,.55)"/>
                <rect x="158" y="215" width="14" height="11" rx="1" fill="rgba(251,191,36,.3)"/>
                <rect x="132" y="238" width="14" height="11" rx="1" fill="rgba(251,191,36,.2)"/>
                <rect x="158" y="238" width="14" height="11" rx="1" class="win-pulse3" fill="rgba(251,191,36,.5)"/>
                <rect x="148" y="258" width="24" height="34" rx="2" fill="rgba(55,25,3,.55)"/>

                {{-- Street lamp left --}}
                <line x1="215" y1="195" x2="215" y2="292" stroke="rgba(255,255,255,.18)" stroke-width="2.5"/>
                <line x1="215" y1="195" x2="232" y2="183" stroke="rgba(255,255,255,.18)" stroke-width="2"/>
                <circle cx="235" cy="181" r="4" fill="rgba(251,191,36,.9)"/>
                <circle cx="235" cy="181" r="9" fill="rgba(251,191,36,.15)"/>

                {{-- Centre hero building --}}
                <rect x="240" y="155" width="120" height="137" rx="3" fill="rgba(255,255,255,.12)"/>
                <polygon points="240,155 300,112 360,155" fill="rgba(255,255,255,.15)"/>
                {{-- chimney --}}
                <rect x="268" y="100" width="12" height="22" rx="1" fill="rgba(255,255,255,.1)"/>
                {{-- window grid --}}
                <rect x="254" y="170" width="17" height="13" rx="1" class="win-pulse"  fill="rgba(251,191,36,.6)"/>
                <rect x="282" y="170" width="17" height="13" rx="1" fill="rgba(251,191,36,.3)"/>
                <rect x="310" y="170" width="17" height="13" rx="1" class="win-pulse3" fill="rgba(251,191,36,.55)"/>
                <rect x="254" y="197" width="17" height="13" rx="1" fill="rgba(251,191,36,.25)"/>
                <rect x="282" y="197" width="17" height="13" rx="1" class="win-pulse2" fill="rgba(251,191,36,.6)"/>
                <rect x="310" y="197" width="17" height="13" rx="1" fill="rgba(251,191,36,.2)"/>
                <rect x="254" y="224" width="17" height="13" rx="1" class="win-pulse"  fill="rgba(251,191,36,.45)"/>
                <rect x="310" y="224" width="17" height="13" rx="1" fill="rgba(251,191,36,.35)"/>
                {{-- door --}}
                <rect x="285" y="248" width="30" height="44" rx="3" fill="rgba(55,25,3,.6)"/>
                <circle cx="308" cy="272" r="2.5" fill="rgba(251,191,36,.7)"/>

                {{-- Mid-right houses --}}
                <rect x="375" y="210" width="68" height="82" rx="3" fill="rgba(255,255,255,.09)"/>
                <polygon points="375,210 409,178 443,210" fill="rgba(255,255,255,.11)"/>
                <rect x="387" y="224" width="13" height="11" rx="1" fill="rgba(251,191,36,.35)"/>
                <rect x="412" y="224" width="13" height="11" rx="1" class="win-pulse2" fill="rgba(251,191,36,.55)"/>
                <rect x="387" y="248" width="13" height="11" rx="1" class="win-pulse3" fill="rgba(251,191,36,.4)"/>
                <rect x="397" y="260" width="20" height="32" rx="2" fill="rgba(55,25,3,.55)"/>

                {{-- Street lamp right --}}
                <line x1="453" y1="200" x2="453" y2="292" stroke="rgba(255,255,255,.18)" stroke-width="2.5"/>
                <line x1="453" y1="200" x2="470" y2="188" stroke="rgba(255,255,255,.18)" stroke-width="2"/>
                <circle cx="473" cy="186" r="4" fill="rgba(251,191,36,.9)"/>
                <circle cx="473" cy="186" r="9" fill="rgba(251,191,36,.14)"/>

                <rect x="465" y="198" width="78" height="94" rx="3" fill="rgba(255,255,255,.1)"/>
                <polygon points="465,198 504,162 543,198" fill="rgba(255,255,255,.13)"/>
                <rect x="477" y="213" width="15" height="12" rx="1" class="win-pulse"  fill="rgba(251,191,36,.55)"/>
                <rect x="506" y="213" width="15" height="12" rx="1" fill="rgba(251,191,36,.25)"/>
                <rect x="477" y="238" width="15" height="12" rx="1" fill="rgba(251,191,36,.35)"/>
                <rect x="506" y="238" width="15" height="12" rx="1" class="win-pulse3" fill="rgba(251,191,36,.5)"/>
                <rect x="490" y="255" width="25" height="37" rx="2" fill="rgba(55,25,3,.55)"/>

                {{-- Far right --}}
                <rect x="558" y="222" width="58" height="70" rx="2" fill="rgba(255,255,255,.08)"/>
                <polygon points="558,222 587,197 616,222" fill="rgba(255,255,255,.10)"/>
                <rect x="568" y="235" width="12" height="10" rx="1" class="win-pulse2" fill="rgba(251,191,36,.45)"/>
                <rect x="594" y="235" width="12" height="10" rx="1" fill="rgba(251,191,36,.2)"/>

                <rect x="625" y="232" width="50" height="60" rx="2" fill="rgba(255,255,255,.07)"/>
                <polygon points="625,232 650,209 675,232" fill="rgba(255,255,255,.09)"/>
                <rect x="635" y="244" width="11" height="9" rx="1" fill="rgba(251,191,36,.3)"/>
                <rect x="656" y="244" width="11" height="9" rx="1" class="win-pulse"  fill="rgba(251,191,36,.4)"/>

                <rect x="684" y="240" width="70" height="52" rx="2" fill="rgba(255,255,255,.06)"/>
                <rect x="780" y="248" width="120" height="44" rx="2" fill="rgba(255,255,255,.05)"/>

                {{-- Trees --}}
                <ellipse cx="232" cy="282" rx="14" ry="10" fill="rgba(101,50,5,.5)"/>
                <ellipse cx="232" cy="274" rx="16" ry="13" fill="rgba(130,65,5,.45)"/>

                <ellipse cx="558" cy="280" rx="12" ry="9"  fill="rgba(101,50,5,.5)"/>
                <ellipse cx="558" cy="272" rx="14" ry="12" fill="rgba(130,65,5,.4)"/>

                <ellipse cx="760" cy="276" rx="13" ry="9"  fill="rgba(101,50,5,.45)"/>
                <ellipse cx="760" cy="269" rx="15" ry="11" fill="rgba(130,65,5,.38)"/>
            </svg>
        </div>

        {{-- Text content --}}
        <div style="position:relative;z-index:10;max-width:420px;">
            {{-- Logomark --}}
            <div style="margin-bottom:2.5rem;">
                <img
                    src="{{ asset('assets/images/logotipo.png') }}"
                    alt="{{ config('app.name') }}"
                    style="height:52px;width:auto;display:block;"
                >
            </div>

            <h1 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(2.1rem,3.2vw,2.9rem);font-weight:700;color:#fff;line-height:1.12;margin:0 0 1.1rem;letter-spacing:-.025em;">
                Seu bairro está<br>
                <em style="font-style:italic;font-weight:400;color:#fde68a;">esperando por você.</em>
            </h1>

            <p style="color:rgba(255,255,255,.68);font-size:.9375rem;line-height:1.7;margin:0 0 2.25rem;max-width:340px;">
                Onde vizinhos se ajudam, serviços locais crescem e a comunidade tem voz.
            </p>

            {{-- Live community stats --}}
            @php
                try {
                    $statPosts     = \App\Models\Post::approved()->count();
                    $statBusiness  = \App\Models\Business::where('status','approved')->count();
                    $statUsers     = \App\Models\User::count();
                } catch (\Exception $e) {
                    $statPosts = $statBusiness = $statUsers = 0;
                }
            @endphp

            <div style="display:flex;gap:0;border:1px solid rgba(255,255,255,.12);border-radius:12px;overflow:hidden;backdrop-filter:blur(8px);background:rgba(0,0,0,.12);">
                <div style="flex:1;padding:.9rem 1.1rem;text-align:center;">
                    <div style="font-family:'Fraunces',serif;font-size:1.4rem;font-weight:600;color:#fbbf24;line-height:1;">{{ $statPosts }}</div>
                    <div style="font-size:.6875rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.06em;margin-top:.25rem;">posts</div>
                </div>
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                <div style="flex:1;padding:.9rem 1.1rem;text-align:center;">
                    <div style="font-family:'Fraunces',serif;font-size:1.4rem;font-weight:600;color:#fbbf24;line-height:1;">{{ $statBusiness }}</div>
                    <div style="font-size:.6875rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.06em;margin-top:.25rem;">negócios</div>
                </div>
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                <div style="flex:1;padding:.9rem 1.1rem;text-align:center;">
                    <div style="font-family:'Fraunces',serif;font-size:1.4rem;font-weight:600;color:#fbbf24;line-height:1;">{{ $statUsers }}</div>
                    <div style="font-size:.6875rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.06em;margin-top:.25rem;">vizinhos</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         RIGHT FORM PANEL
    ═══════════════════════════════════════ --}}
    <div style="flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:2rem;min-height:100vh;background:#fafaf9;">

        {{-- Mobile logo --}}
        <div id="mobile-logo" style="margin-bottom:2rem;text-align:center;">
            <a href="{{ route('home') }}" style="display:inline-flex;align-items:center;text-decoration:none;">
                <img
                    src="{{ asset('assets/images/logotipo.png') }}"
                    alt="{{ config('app.name') }}"
                    style="height:42px;width:auto;display:block;"
                >
            </a>
        </div>

        <div style="width:100%;max-width:368px;">
            {{ $slot }}
        </div>

        <p style="margin-top:2.5rem;font-size:.75rem;color:#a8a29e;text-align:center;">
            © {{ date('Y') }} FalaVizin · Feito com carinho para a comunidade
        </p>
    </div>
</div>

<script>
    // Show left panel only on large screens
    (function() {
        const panel = document.getElementById('left-panel');
        const mobileLogo = document.getElementById('mobile-logo');
        if (window.innerWidth >= 1024) {
            panel.style.display = 'flex';
            mobileLogo.style.display = 'none';
        }
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                panel.style.display = 'flex';
                mobileLogo.style.display = 'none';
            } else {
                panel.style.display = 'none';
                mobileLogo.style.display = 'block';
            }
        });
    })();
</script>
</body>
</html>
