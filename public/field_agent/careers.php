<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Careers - BoardNest</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-variant": "#e8e1dd",
                        "on-primary-fixed": "#2a1707",
                        "surface-tint": "#745943",
                        "outline": "#81756d",
                        "primary": "#715741",
                        "on-secondary-fixed": "#211b0b",
                        "secondary-fixed": "#eee1c7",
                        "on-error": "#ffffff",
                        "on-background": "#1e1b19",
                        "on-primary": "#ffffff",
                        "tertiary-fixed": "#cee6f0",
                        "on-secondary-container": "#6b624d",
                        "surface": "#fff8f5",
                        "secondary": "#665e49",
                        "outline-variant": "#d3c4ba",
                        "on-tertiary-container": "#fafdff",
                        "secondary-fixed-dim": "#d1c5ac",
                        "inverse-on-surface": "#f7efec",
                        "on-surface": "#1e1b19",
                        "surface-bright": "#fff8f5",
                        "on-primary-fixed-variant": "#5a422d",
                        "surface-container-high": "#eee7e3",
                        "on-surface-variant": "#4f453e",
                        "error-container": "#ffdad6",
                        "on-tertiary-fixed": "#061e26",
                        "inverse-primary": "#e3c0a5",
                        "inverse-surface": "#33302e",
                        "surface-container-low": "#faf2ee",
                        "background": "#fff8f5",
                        "on-error-container": "#93000a",
                        "surface-container-highest": "#e8e1dd",
                        "on-secondary": "#ffffff",
                        "on-secondary-fixed-variant": "#4e4633",
                        "on-tertiary-fixed-variant": "#344a52",
                        "tertiary-fixed-dim": "#b3cad4",
                        "tertiary": "#495f68",
                        "secondary-container": "#ebdfc4",
                        "surface-dim": "#e0d9d5",
                        "surface-container": "#f4ece9",
                        "primary-fixed": "#ffdcc2",
                        "error": "#ba1a1a",
                        "on-tertiary": "#ffffff",
                        "primary-container": "#8c6f58",
                        "on-primary-container": "#fffbff",
                        "tertiary-container": "#617881",
                        "primary-fixed-dim": "#e3c0a5",
                        "surface-container-lowest": "#ffffff",
                        "brand-accent": "#a4856d"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "margin-desktop": "40px",
                        "margin-mobile": "16px",
                        "unit": "8px",
                        "container-max": "1280px",
                        "gutter": "24px"
                    },
                    "fontFamily": {
                        "title-lg": ["Manrope"],
                        "display-lg": ["Manrope"],
                        "label-sm": ["Manrope"],
                        "headline-lg-mobile": ["Manrope"],
                        "label-md": ["Manrope"],
                        "headline-md": ["Manrope"],
                        "body-md": ["Manrope"],
                        "headline-lg": ["Manrope"],
                        "body-lg": ["Manrope"]
                    },
                    "fontSize": {
                        "title-lg": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "display-lg": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "800" }],
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "700" }],
                        "headline-lg-mobile": ["24px", { "lineHeight": "32px", "fontWeight": "700" }],
                        "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "600" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }]
                    }
                }
            }
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.filled {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased selection:bg-primary-container selection:text-on-primary-container">
<!-- TopNavBar -->
<nav class="bg-surface/80 dark:bg-inverse-surface/80 backdrop-blur-md text-primary dark:text-inverse-primary docked full-width top-0 sticky z-50 border-b border-outline-variant dark:border-outline flat no shadows">
<div class="flex justify-between items-center w-full h-20 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="flex items-center gap-4">
<span class="text-headline-md font-headline-md font-extrabold" style="color: #6F4E37; font-family: 'Outfit', sans-serif;">BoardNest</span>
</div>
<div class="hidden md:flex gap-8 items-center">
<a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-inverse-primary transition-colors hover:bg-surface-container dark:hover:bg-inverse-surface-variant rounded-full px-4 py-2 text-label-md font-label-md" href="../../index.html#explore">Find Housing</a>
<a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-inverse-primary transition-colors hover:bg-surface-container dark:hover:bg-inverse-surface-variant rounded-full px-4 py-2 text-label-md font-label-md" href="../../index.html">How it Works</a>
<a class="text-primary dark:text-inverse-primary border-b-2 border-primary dark:border-inverse-primary pb-1 hover:bg-surface-container dark:hover:bg-inverse-surface-variant rounded-full px-4 py-2 text-label-md font-label-md opacity-80 scale-95 transition-all" href="#">Careers</a>
</div>
<a href="register.php" class="bg-brand-accent text-white px-6 py-2.5 rounded-full text-label-md font-label-md hover:opacity-90 transition-opacity hidden md:inline-block">
    Apply Now
</a>
<button class="md:hidden text-primary">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
</nav>
<main>
<!-- Hero Section -->
<section class="relative pt-24 pb-32 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto overflow-hidden">
<div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
<div class="z-10">
<span class="inline-block py-1.5 px-4 rounded-full bg-secondary-container text-on-secondary-container text-label-sm font-label-sm mb-6 border border-outline-variant/30">Careers at BoardNest</span>
<h1 class="text-headline-lg-mobile md:text-display-lg font-headline-lg-mobile md:font-display-lg text-on-background mb-6">
                        Join the Nest: <br/><span class="text-brand-accent">Build a Safer Future</span> for Students
                    </h1>
<p class="text-body-lg font-body-lg text-on-surface-variant mb-8 max-w-lg">
                        Become a trusted Field Agent in your city. Audit student accommodations, verify safety standards, and help create a secure environment for students worldwide while earning on your own schedule.
                    </p>
<div class="flex flex-wrap gap-4">
<a href="register.php" class="bg-brand-accent text-white px-8 py-4 rounded-full text-label-md font-label-md hover:bg-primary transition-colors flex items-center gap-2 shadow-sm">
                            Apply to be a Field Agent
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
</a>
</div>
</div>
<div class="relative z-10 hidden md:block">
<div class="absolute inset-0 bg-secondary-fixed rounded-2xl transform rotate-3 scale-105 opacity-50"></div>
<img class="w-full h-auto rounded-2xl shadow-lg relative object-cover z-20" alt="Field agent inspecting a room" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAq3MTc89CECH_1NhEQ7B-Ivt7jQBm-UjOBzXaZRL6qUp2Y-JkhTbhVHuvv44iMcLHT8oCOyybhhG7LqoM0-aDoyUGdeGvhyqA0UewrtOqxkFT8iXiBz_ceRfDO_oSg7mrjWjdy39A_iOvN4nbot2KLsdzwVQOC0g_yAiDtO6O01XW59HVxwmUt_09IiM8gQSiIn9HW2cGej2n4hm5jqPsZl7uN27c4j6LShxEUzqMNdUVHtXLcKIKC" style="aspect-ratio: 4/5;"/>
</div>
</div>
</section>
<!-- Stats Section -->
<section class="bg-surface-container py-16 px-margin-mobile md:px-margin-desktop">
<div class="max-w-container-max mx-auto">
<div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-outline-variant/30">
<div class="flex flex-col items-center">
<span class="text-display-lg font-display-lg text-primary mb-2">500+</span>
<span class="text-label-md font-label-md text-on-surface-variant">Active Agents</span>
</div>
<div class="flex flex-col items-center">
<span class="text-display-lg font-display-lg text-primary mb-2">10k+</span>
<span class="text-label-md font-label-md text-on-surface-variant">Properties Verified</span>
</div>
<div class="flex flex-col items-center">
<span class="text-display-lg font-display-lg text-primary mb-2">50+</span>
<span class="text-label-md font-label-md text-on-surface-variant">Cities Covered</span>
</div>
<div class="flex flex-col items-center">
<span class="text-display-lg font-display-lg text-primary mb-2">4.9/5</span>
<span class="text-label-md font-label-md text-on-surface-variant">Agent Satisfaction</span>
</div>
</div>
</div>
</section>
<!-- Value Proposition (Bento Grid) -->
<section class="py-24 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="text-center mb-16">
<h2 class="text-headline-lg font-headline-lg text-on-background mb-4">Why Become a Field Agent?</h2>
<p class="text-body-lg font-body-lg text-on-surface-variant max-w-2xl mx-auto">Empower yourself with flexible work that makes a real difference in the student community.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<!-- Card 1 -->
<div class="bg-[#FFF2D7] rounded-2xl p-8 hover:-translate-y-1 transition-transform duration-300 shadow-sm border border-outline-variant/20">
<div class="w-12 h-12 rounded-full bg-brand-accent/10 flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-brand-accent">schedule</span>
</div>
<h3 class="text-title-lg font-title-lg text-on-background mb-3">Flexible Schedule</h3>
<p class="text-body-md font-body-md text-on-surface-variant">Take on verification tasks and audits at times that suit you. You are in total control of your time, fitting audits around your life.</p>
</div>
<!-- Card 2 -->
<div class="bg-surface rounded-2xl p-8 hover:-translate-y-1 transition-transform duration-300 shadow-sm border border-outline-variant/50 relative overflow-hidden group">
<div class="absolute inset-0 bg-gradient-to-br from-brand-accent/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
<div class="w-12 h-12 rounded-full bg-brand-accent/10 flex items-center justify-center mb-6 relative z-10">
<span class="material-symbols-outlined text-brand-accent">payments</span>
</div>
<h3 class="text-title-lg font-title-lg text-on-background mb-3 relative z-10">Earn per Audit</h3>
<p class="text-body-md font-body-md text-on-surface-variant relative z-10">Get paid for every physical property verification and dispute resolution report you submit. Transparent pricing, weekly payouts.</p>
</div>
<!-- Card 3 -->
<div class="bg-[#FFF2D7] rounded-2xl p-8 hover:-translate-y-1 transition-transform duration-300 shadow-sm border border-outline-variant/20">
<div class="w-12 h-12 rounded-full bg-brand-accent/10 flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-brand-accent">shield</span>
</div>
<h3 class="text-title-lg font-title-lg text-on-background mb-3">Community Impact</h3>
<p class="text-body-md font-body-md text-on-surface-variant">Ensure student housing is safe and secure. Be the trusted local agent for university students navigating new cities.</p>
</div>
</div>
</section>
<!-- CTA Section -->
<section class="py-24 px-margin-mobile md:px-margin-desktop">
<div class="max-w-4xl mx-auto bg-primary rounded-3xl p-12 text-center text-on-primary relative overflow-hidden">
<div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
<div class="absolute bottom-0 left-0 -mb-16 -ml-16 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
<h2 class="text-headline-lg font-headline-lg mb-6 relative z-10">Ready to Join the Network?</h2>
<p class="text-body-lg font-body-lg mb-10 max-w-2xl mx-auto opacity-90 relative z-10">Start your journey today and become a vital part of making student housing safer and more reliable.</p>
<a href="register.php" class="inline-block bg-surface text-primary px-10 py-4 rounded-full text-label-md font-label-md hover:bg-surface-variant transition-colors relative z-10 shadow-md">
    Apply to be a Field Agent
</a>
</div>
</section>
</main>
<!-- Footer -->
<footer class="bg-surface-container-highest dark:bg-inverse-surface text-on-surface dark:text-inverse-on-surface full-width border-t border-outline-variant dark:border-outline flat no shadows">
<div class="w-full py-16 px-margin-mobile md:px-margin-desktop grid grid-cols-1 md:grid-cols-4 gap-gutter max-w-container-max mx-auto">
<div class="col-span-1 md:col-span-1 mb-8 md:mb-0">
<div class="text-title-lg font-title-lg font-bold text-primary dark:text-inverse-primary mb-4">BoardNest</div>
<p class="text-label-sm font-label-sm text-on-surface-variant dark:text-surface-variant">
                    © 2026 BoardNest. Providing safe havens for students worldwide.
                </p>
</div>
<div class="flex flex-col gap-4">
<h4 class="text-body-md font-body-md font-semibold mb-2">Company</h4>
<a class="text-label-sm font-label-sm text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-inverse-primary transition-colors underline hover:translate-x-1" href="../../index.html">About Us</a>
<a class="text-primary dark:text-inverse-primary font-semibold hover:text-primary dark:hover:text-inverse-primary transition-colors underline hover:translate-x-1" href="#">Careers</a>
</div>
</div>
</footer>
</body>
</html>
