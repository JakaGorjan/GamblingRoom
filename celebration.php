<?php
session_start();

if (!isset($_SESSION['uporabniki']) || !isset($_SESSION['zmage'])) {
    header('Location: index.php');
    exit;
}

$uporabniki = $_SESSION['uporabniki'];
$zmage = $_SESSION['zmage'];
$vsote = $_SESSION['skupne_vsote'];

$podatki = [];
foreach ($uporabniki as $idx => $u) {
    $podatki[] = [
        'ime' => $u[0] . ' ' . $u[1],
        'zmage' => $zmage[$idx],
        'vsota' => $vsote[$idx]
    ];
}

// Razvrščanje: zmage primarno, točke sekundarno
usort($podatki, function($a, $b) {
    if ($b['zmage'] === $a['zmage']) return $b['vsota'] <=> $a['vsota'];
    return $b['zmage'] <=> $a['zmage'];
});

$prvi = $podatki[0];
$drugi = $podatki[1] ?? null;
$tretji = $podatki[2] ?? null;
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>GAMBLING - Podelitev</title>
    <style>
        /* Osnovne nastavitve za celotno stran */
        body {
            margin: 0;
            padding: 0;
            background: radial-gradient(ellipse at top, #1a6b3a 0%, #0a3d1f 50%, #062814 100%);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            text-align: center;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding-top: 30px;
        }

        .naslov {
            font-size: 50px;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-bottom: 60px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }

        /* Glavni vsebnik za stopničke */
        .podium-container {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 30px;
            height: 450px;
            margin-top: 50px;
        }

        /* Posamezna stopnica */
        .podium-step {
            width: 220px;
            background: linear-gradient(to top, rgba(6,40,20,1), rgba(212,175,55,0.25));
            border: 4px solid #d4af37;
            border-bottom: none;
            border-radius: 15px 15px 0 0;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end; /* Številko drži na dnu */
            padding-bottom: 20px;
            animation: slideUp 1.2s ease-out;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.5);
        }

        /* Različne višine */
        .step-1 { height: 320px; border-color: #ffd700; z-index: 3; }
        .step-2 { height: 240px; border-color: #c0c0c0; z-index: 2; }
        .step-3 { height: 180px; border-color: #cd7f32; z-index: 1; }

        /* Ime igralca nad stopnico */
        .winner-name {
            position: absolute;
            top: -65px; /* Postavljeno nad stopnico */
            width: 260px;
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
        }

        /* Statistika znotraj stopnice */
        .winner-stats {
            position: absolute;
            top: 20px; /* Tik pod robom stopnice */
            font-size: 15px;
            color: #f5d76e;
            font-weight: bold;
            letter-spacing: 1px;
            background: rgba(0,0,0,0.3);
            padding: 5px 15px;
            border-radius: 20px;
        }

        /* Velika številka (1, 2, 3) */
        .podium-label {
            font-size: 90px;
            font-weight: 800;
            color: rgba(212, 175, 55, 0.8);
            line-height: 1;
            margin-bottom: 10px;
        }

        /* Krona nad zmagovalcem */
        .crown {
            font-size: 70px;
            position: absolute;
            top: -135px; /* Višje nad imenom */
            filter: drop-shadow(0 0 10px gold);
            animation: float 2s infinite ease-in-out;
        }

        /* Konfeti animacija */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            top: -10px;
            animation: fall linear infinite;
        }

        @keyframes fall {
            to { transform: translateY(100vh) rotate(360deg); }
        }

        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .timer-info {
            margin-top: 60px;
            font-size: 20px;
            color: #f5d76e;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <?php for($i=0; $i<60; $i++): ?>
        <div class="confetti" style="left: <?= rand(0,100) ?>%; animation-duration: <?= rand(2,5) ?>s; background-color: <?= ['#d4af37','#f5d76e','#ffffff','#c0392b','#2ecc71'][rand(0,4)] ?>;"></div>
    <?php endfor; ?>

    <div class="container">
        <h1 class="naslov">🏆 PODELITEV POKALOV 🏆</h1>

        <div class="podium-container">
            <?php if ($drugi): ?>
            <div class="podium-step step-2">
                <div class="winner-name"><?= htmlspecialchars($drugi['ime']) ?></div>
                <div class="winner-stats"><?= $drugi['zmage'] ?> ZMAG / <?= $drugi['vsota'] ?> TČ</div>
                <div class="podium-label">2</div>
            </div>
            <?php endif; ?>

            <div class="podium-step step-1">
                <div class="crown">👑</div>
                <div class="winner-name" style="color:#ffd700; font-size: 30px; top: -80px;">
                    <?= htmlspecialchars($prvi['ime']) ?>
                </div>
                <div class="winner-stats" style="color:#ffd700; border-color: #ffd700;">
                    <?= $prvi['zmage'] ?> ZMAG / <?= $prvi['vsota'] ?> TČ
                </div>
                <div class="podium-label">1</div>
            </div>

            <?php if ($tretji): ?>
            <div class="podium-step step-3">
                <div class="winner-name"><?= htmlspecialchars($tretji['ime']) ?></div>
                <div class="winner-stats"><?= $tretji['zmage'] ?> ZMAG / <?= $tretji['vsota'] ?> TČ</div>
                <div class="podium-label">3</div>
            </div>
            <?php endif; ?>
        </div>

        <p class="timer-info">Nova igra se začne čez <span id="timer">9</span> sekund...</p>
    </div>

    <script>
        let s = 9;
        const timerElement = document.getElementById('timer');
        const interval = setInterval(() => {
            s--;
            if (timerElement) timerElement.textContent = s;
            if (s <= 0) {
                clearInterval(interval);
                window.location.href = 'index.php?reset=1';
            }
        }, 1000);
    </script>
</body>
</html>