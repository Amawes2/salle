<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'GymManager Africa') }} - Gestion de salle</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="relative overflow-hidden bg-slate-950">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.15),transparent_25%),radial-gradient(circle_at_bottom_right,_rgba(251,146,60,0.18),transparent_22%)]"></div>
        <div class="relative mx-auto max-w-7xl px-5 py-6 lg:px-8">
            <header class="relative z-10 flex flex-col gap-4 rounded-[2rem] border border-slate-800/90 bg-slate-950/95 px-6 py-4 shadow-2xl shadow-slate-950/40 backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-cyan-400/80">Gym SaaS</p>
                    <h1 class="mt-2 text-2xl font-black tracking-[-0.04em] text-white sm:text-3xl">{{ config('app.name', 'GymManager Africa') }}</h1>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <a href="{{ url('/admin/login') }}"
                       class="rounded-full border border-slate-700/90 px-4 py-2 font-semibold text-slate-300 transition hover:border-slate-600 hover:bg-slate-900/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-300">
                        Connexion
                    </a>
                    <a href="{{ route('gyms.register') }}" class="inline-flex rounded-full bg-cyan-500 px-5 py-2.5 font-semibold text-white shadow-[0_18px_40px_rgba(34,211,238,0.18)] transition hover:bg-cyan-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-200">
                        Créer une salle
                    </a>
                </div>
            </header>

            <main class="relative z-10 mt-10 grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-start">
                <section class="space-y-8">
                    <div class="max-w-3xl space-y-6">
                        <span class="inline-flex rounded-full bg-cyan-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-cyan-300">
                            Gestion de salle</span>
                        <h2 class="text-5xl font-black tracking-[-0.05em] leading-tight text-white sm:text-6xl">
                            Gère ta salle comme un chef, sans perdre de temps.
                        </h2>
                        <p class="max-w-2xl text-lg leading-8 text-slate-300">
                            Un tableau de bord clair pour suivre tes clients, abonnements, paiements et opérations quotidiennes avec style et vitesse.
                        </p>
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <a href="{{ route('gyms.register') }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-6 py-3 text-sm font-bold text-white shadow-[0_18px_40px_rgba(34,211,238,0.2)] transition hover:bg-cyan-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-200">
                                Essayer gratuitement
                            </a>
                        </div>
                    </div>
                </section>

                <section class="relative overflow-hidden rounded-[2.5rem] border border-slate-800/80 bg-slate-900/95 p-6 shadow-2xl shadow-slate-950/40 sm:p-8">
                    <div class="absolute -right-10 top-4 h-28 w-28 rounded-full bg-cyan-500/20 blur-3xl"></div>
                    <div class="absolute -left-10 bottom-6 h-36 w-36 rounded-full bg-orange-500/10 blur-3xl"></div>
                    <div class="relative space-y-6">
                        

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-[1.75rem] border border-slate-800/70 bg-slate-950/95 p-5">
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Clients actifs</p>
                                <p class="mt-3 text-3xl font-black text-white">1,250</p>
                                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-2 w-4/5 rounded-full bg-cyan-500"></div>
                                </div>
                            </div>
                            <div class="rounded-[1.75rem] border border-slate-800/70 bg-slate-950/95 p-5">
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Paiements</p>
                                <p class="mt-3 text-3xl font-black text-white">4,380</p>
                                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-2 w-3/5 rounded-full bg-orange-400"></div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-slate-800/70 bg-gradient-to-br from-cyan-500/10 via-slate-900/60 to-orange-500/10 p-5 text-slate-200">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.28em] text-cyan-200">Vue prioritaire</p>
                                    <p class="mt-2 text-xl font-black text-white">Rapports et alertes</p>
                                </div>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase text-white/80">Live</span>
                            </div>
                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-3xl bg-slate-950/80 p-3 text-center">
                                    <p class="text-2xl font-black">24</p>
                                    <p class="mt-1 text-xs uppercase text-slate-400">Nouvelles</p>
                                </div>
                                <div class="rounded-3xl bg-slate-950/80 p-3 text-center">
                                    <p class="text-2xl font-black">12</p>
                                    <p class="mt-1 text-xs uppercase text-slate-400">Alertes</p>
                                </div>
                                <div class="rounded-3xl bg-slate-950/80 p-3 text-center">
                                    <p class="text-2xl font-black">18</p>
                                    <p class="mt-1 text-xs uppercase text-slate-400">Actions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <section class="relative z-10 mt-10 rounded-[2rem] border border-slate-800/80 bg-slate-900/95 p-6 shadow-2xl shadow-slate-950/30 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.28em] text-cyan-300">Offres SaaS</p>
                        <h3 class="mt-3 text-3xl font-black text-white">Choisis un plan adapté à ta salle.</h3>
                    </div>
                    <a href="{{ route('gyms.register') }}" class="inline-flex items-center justify-center rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-cyan-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-200">
                        Démarrer maintenant
                    </a>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <article class="rounded-2xl border border-slate-800 bg-slate-950/90 p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-slate-500">Basic</p>
                        <p class="mt-3 text-3xl font-black text-white">29€<span class="text-base font-semibold text-slate-400">/mois</span></p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-300">
                            <li>• Gestion membres & abonnements</li>
                            <li>• Suivi des paiements</li>
                            <li>• 1 salle</li>
                            <li>• Alertes intelligentes</li>
                            <li>• Rapports avancés</li>
                            <li>• Multi-utilisateurs</li>
                        </ul>
                    </article>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
