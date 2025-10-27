<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hors ligne - E-menu</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .offline-container {
            max-width: 400px;
            padding: 2rem;
        }
        .offline-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        .offline-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 300;
        }
        .offline-message {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.5;
            margin-bottom: 2rem;
        }
        .retry-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .retry-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        .wifi-icon {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
        }
        .wifi-icon::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24"><path d="M12 2C17.52 2 22 6.48 22 12s-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2zm0 18c4.42 0 8-3.58 8-8s-3.58-8-8-8-8 3.58-8 8 3.58 8 8 8z"/><path d="M12 6C8.13 6 5 9.13 5 13h2c0-2.76 2.24-5 5-5s5 2.24 5 5h2c0-3.87-3.13-7-7-7z"/><path d="M12 10c-1.66 0-3 1.34-3 3h2c0-.55.45-1 1-1s1 .45 1 1h2c0-1.66-1.34-3-3-3z"/></svg>') no-repeat center;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="wifi-icon"></div>
        <div class="offline-icon">📡</div>
        <h1 class="offline-title">Vous êtes hors ligne</h1>
        <p class="offline-message">
            Il semble que vous n'ayez pas de connexion Internet.
            Vérifiez votre connexion et réessayez.
        </p>
        <button class="retry-btn" onclick="window.location.reload()">
            🔄 Réessayer
        </button>
    </div>

    <script>
        // Vérifier le statut de connexion
        window.addEventListener('online', () => {
            window.location.reload();
        });

        // Animation de l'icône
        const icon = document.querySelector('.offline-icon');
        setInterval(() => {
            icon.style.transform = icon.style.transform === 'scale(1.1)' ? 'scale(1)' : 'scale(1.1)';
        }, 1000);
    </script>
</body>
</html>
