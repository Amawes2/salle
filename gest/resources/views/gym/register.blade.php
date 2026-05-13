<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer mon espace salle - {{ config('app.name', 'GymManager Africa') }}</title>
    <style>
        :root {
            --bg-1: #0f172a;
            --bg-2: #1e293b;
            --primary: #f97316;
            --primary-2: #f43f5e;
            --text: #0f172a;
            --muted: #64748b;
            --card: #ffffff;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            color: var(--text);
            background:
                radial-gradient(1200px 500px at 10% -10%, rgba(249, 115, 22, 0.35), transparent 65%),
                radial-gradient(1200px 500px at 90% 110%, rgba(244, 63, 94, 0.25), transparent 65%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .shell {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1fr;
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 22px 65px rgba(2, 6, 23, 0.5);
            animation: floatIn 480ms ease-out;
        }

        .hero {
            padding: 24px 24px 14px;
            color: white;
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.92));
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            letter-spacing: 0.3px;
            font-weight: 600;
            padding: 7px 12px;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(90deg, var(--primary), var(--primary-2));
            margin-bottom: 14px;
            animation: pulseGlow 1.9s ease-in-out infinite;
        }

        .hero h1 {
            margin: 0 0 8px;
            font-size: clamp(24px, 4vw, 34px);
            line-height: 1.15;
        }

        .hero p {
            margin: 0;
            color: rgba(255, 255, 255, 0.85);
            font-size: 15px;
            line-height: 1.5;
            max-width: 52ch;
        }

        .content {
            padding: 22px 20px 24px;
            background: white;
        }

        .alert {
            margin: 0 0 16px;
            border: 1px solid #fecaca;
            background: #fff1f2;
            color: #9f1239;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
        }

        .alert p {
            margin: 0 0 4px;
            font-weight: 700;
        }

        .alert ul {
            margin: 0;
            padding-left: 16px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }

        .field input {
            width: 100%;
            border: 1px solid var(--border);
            background: #f8fafc;
            color: #0f172a;
            border-radius: 12px;
            padding: 11px 12px;
            font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .field input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15);
            transform: translateY(-1px);
        }

        .hint {
            margin: 2px 0 0;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.45;
        }

        .actions {
            margin-top: 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .back-link {
            font-size: 13px;
            color: #475569;
            text-decoration: none;
            font-weight: 500;
            text-align: center;
        }

        .back-link:hover {
            color: #0f172a;
        }

        .btn {
            border: 0;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: 700;
            color: white;
            cursor: pointer;
            background: linear-gradient(90deg, var(--primary), var(--primary-2));
            box-shadow: 0 10px 24px rgba(244, 63, 94, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(244, 63, 94, 0.34);
            filter: saturate(1.08);
        }

        .btn:active {
            transform: translateY(0);
        }

        @media (min-width: 860px) {
            .shell {
                grid-template-columns: 0.95fr 1.05fr;
            }

            .hero {
                padding: 36px 30px;
                border-right: 1px solid rgba(255, 255, 255, 0.08);
                border-bottom: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .content {
                padding: 32px 30px;
            }

            .two-col {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 14px;
            }

            .actions {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            .back-link {
                text-align: left;
            }
        }

        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.985);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.28);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(249, 115, 22, 0);
            }
        }
    </style>
</head>
<body>
<main class="shell">
    <section class="hero">
        <span class="badge">Nouveau • Salle de gym</span>
        <h1>Créez votre espace de gestion en quelques clics</h1>
        <p>
            Commencez avec un espace vide, puis ajoutez vos clients, vos abonnements et vos paiements.
            Chaque salle garde ses propres données.
        </p>
    </section>

    <section class="content">
        @if ($errors->any())
            <div class="alert">
                <p>Veuillez corriger :</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('gyms.register.store') }}" method="POST" class="grid">
            @csrf

            <div class="field">
                <label for="gym_name">Nom de la salle</label>
                <input id="gym_name" name="gym_name" type="text" required value="{{ old('gym_name') }}" placeholder="Ex. Fitness Hub Dakar">
            </div>

            <div class="field">
                <label for="name">Votre nom complet</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}" placeholder="Ex. Mamadou Diop">
            </div>

            <div class="field">
                <label for="email">Email de connexion</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}" placeholder="vous@salle.com">
            </div>

            <div class="two-col">
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" required minlength="12" placeholder="12 caracteres minimum">
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" placeholder="Retaper le mot de passe">
                </div>
            </div>

            <p class="hint">
                Le mot de passe doit contenir au moins 12 caracteres, avec majuscule, minuscule, chiffre et symbole.
            </p>

            <p class="hint">
                Le compte cree aura l acces administrateur de votre salle. En cas de perte d acces,
                utilisez la
                <a href="{{ route('filament.admin.auth.password-reset.request') }}">recuperation de mot de passe</a>
                depuis la page de connexion.
            </p>

            <div class="actions">
                <a href="{{ url('/') }}" class="back-link">← Retour à l’accueil</a>
                <button type="submit" class="btn">Créer mon espace salle</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
