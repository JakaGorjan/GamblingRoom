<?php
session_start();

// Inicializacija seje
if (!isset($_SESSION['uporabniki'])) $_SESSION['uporabniki'] = [];
if (!isset($_SESSION['stevilo_rund'])) $_SESSION['stevilo_rund'] = 3;

// Reset
if (isset($_GET['reset'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Dodaj uporabnika v dvodimenzionalno tabelo
$napaka = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj'])) {
    $ime = trim($_POST['ime'] ?? '');
    $priimek = trim($_POST['priimek'] ?? '');
    $naslov = trim($_POST['naslov'] ?? '');
    if ($ime === '' || $priimek === '' || $naslov === '') {
        $napaka = 'Vsa polja so obvezna!';
    } elseif (count($_SESSION['uporabniki']) >= 3) {
        $napaka = 'Vneseni so že 3 uporabniki.';
    } else {
        $_SESSION['uporabniki'][] = [$ime, $priimek, $naslov];
        header('Location: index.php');
        exit;
    }
}

// Nastavi format
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nastavi_runde'])) {
    $r = (int)($_POST['stevilo_rund'] ?? 3);
    if (in_array($r, [1, 3, 5, 7], true)) $_SESSION['stevilo_rund'] = $r;
    header('Location: index.php');
    exit;
}

$stevilo = count($_SESSION['uporabniki']);
$rund = $_SESSION['stevilo_rund'];
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>GAMBLING - Vnos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="vnos-body">
    <div class="container">
        <header class="hero">
            <h1 class="naslov">GAMBLING</h1>
            <div class="podnaslov">★ IGRA S KOCKAMI ★</div>
        </header>

        <?php if ($stevilo < 3): ?>
        <section class="card">
            <h2>Vnos uporabnika <span class="counter"><?= $stevilo ?>/3</span></h2>
            <?php if ($napaka !== ''): ?>
                <div class="napaka"><?= htmlspecialchars($napaka) ?></div>
            <?php endif; ?>
            <form method="POST" action="index.php" class="obrazec">
                <input type="text" name="ime" placeholder="Ime" required maxlength="50">
                <input type="text" name="priimek" placeholder="Priimek" required maxlength="50">
                <input type="text" name="naslov" placeholder="Naslov" required maxlength="100" class="full">
                <button type="submit" name="dodaj" class="btn btn-primary full">+ Dodaj uporabnika</button>
            </form>
        </section>
        <?php endif; ?>

        <?php if ($stevilo > 0): ?>
        <section class="card">
            <h2>Vneseni uporabniki</h2>
            <table class="tabela">
                <thead><tr><th>#</th><th>Ime</th><th>Priimek</th><th>Naslov</th></tr></thead>
                <tbody>
                <?php foreach ($_SESSION['uporabniki'] as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($u[0]) ?></td>
                        <td><?= htmlspecialchars($u[1]) ?></td>
                        <td><?= htmlspecialchars($u[2]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <?php if ($stevilo === 3): ?>
        <section class="card">
            <h2>Format igre</h2>
            <div class="runde-izbira">
                <?php foreach ([1, 3, 5, 7] as $r): ?>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="stevilo_rund" value="<?= $r ?>">
                        <button type="submit" name="nastavi_runde" value="1" class="runda-opcija <?= $rund === $r ? 'izbrana' : '' ?>">
                            <span class="runda-stevilo">Best of <?= $r ?></span>
                            <span class="runda-info"><?= $r === 1 ? '1 runda' : 'do ' . (int)ceil($r/2) . ' zmag' ?></span>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <div class="akcije">
            <?php if ($stevilo === 3): ?>
                <a href="igra.php" class="btn btn-success">🎲 ZAČNI IGRO</a>
            <?php endif; ?>
            <?php if ($stevilo > 0): ?>
                <a href="index.php?reset=1" class="btn btn-danger">↻ Ponastavi</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
