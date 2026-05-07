<?php
session_start();

if (!isset($_SESSION['uporabniki']) || count($_SESSION['uporabniki']) !== 3) {
    header('Location: index.php');
    exit;
}

$uporabniki = $_SESSION['uporabniki'];
$stevilo_rund = $_SESSION['stevilo_rund'] ?? 3;
$potrebne_zmage = (int)ceil($stevilo_rund / 2);

// Inicializacija stanja igre
if (!isset($_SESSION['runda']) || isset($_GET['nova_igra'])) {
    $_SESSION['runda'] = 0;
    $_SESSION['zmage'] = [0, 0, 0];
    $_SESSION['skupne_vsote'] = [0, 0, 0];
    $_SESSION['zadnji_meti'] = null;
    $_SESSION['zadnje_vsote'] = null;
    $_SESSION['zadnji_zmagovalci'] = null;
}

// Klik na VRZI KOCKE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vrzi'])) {
    // ENODIMENZIONALNA tabela 9 metov
    $meti = [];
    for ($i = 0; $i < 9; $i++) $meti[] = rand(1, 6);

    $vsote = [];
    for ($u = 0; $u < 3; $u++) {
        $vsote[] = $meti[$u*3] + $meti[$u*3+1] + $meti[$u*3+2];
    }

    $max = max($vsote);
    $zmagovalci_runde = [];
    for ($u = 0; $u < 3; $u++) {
        if ($vsote[$u] === $max) $zmagovalci_runde[] = $u;
    }

    if (count($zmagovalci_runde) === 1) {
        $_SESSION['zmage'][$zmagovalci_runde[0]]++;
    }
    for ($u = 0; $u < 3; $u++) $_SESSION['skupne_vsote'][$u] += $vsote[$u];

    $_SESSION['runda']++;
    $_SESSION['zadnji_meti'] = $meti;
    $_SESSION['zadnje_vsote'] = $vsote;
    $_SESSION['zadnji_zmagovalci'] = $zmagovalci_runde;

    header('Location: igra.php');
    exit;
}

$runda_st = $_SESSION['runda'];
$zmage = $_SESSION['zmage'];
$skupne_vsote = $_SESSION['skupne_vsote'];
$max_zmag = max($zmage);
$konec_igre = ($max_zmag >= $potrebne_zmage) || ($runda_st >= $stevilo_rund);

// Določi končne zmagovalce
$koncni_zmagovalci = [];
if ($konec_igre) {
    for ($u = 0; $u < 3; $u++) {
        if ($zmage[$u] === $max_zmag) $koncni_zmagovalci[] = $u;
    }
    if (count($koncni_zmagovalci) > 1) {
        $max_v = 0;
        foreach ($koncni_zmagovalci as $u) {
            if ($skupne_vsote[$u] > $max_v) $max_v = $skupne_vsote[$u];
        }
        $koncni_zmagovalci = array_values(array_filter($koncni_zmagovalci, function($u) use ($skupne_vsote, $max_v) {
            return $skupne_vsote[$u] === $max_v;
        }));
    }
}

$meti = $_SESSION['zadnji_meti'];
$vsote = $_SESSION['zadnje_vsote'];
$zmagovalci_runde = $_SESSION['zadnji_zmagovalci'];
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>GAMBLING - Igra</title>
    <link rel="stylesheet" href="style.css">
    <?php if ($konec_igre): ?>
    <script>
        setTimeout(function () { window.location.href = 'index.php?nova_igra=1'; }, 10000);
        let preostalo = 10;
        window.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('odstevanje');
            const it = setInterval(function () {
                preostalo--;
                if (el) el.textContent = preostalo;
                if (preostalo <= 0) clearInterval(it);
            }, 1000);
        });
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <header class="hero">
            <h1 class="naslov" style="font-size:clamp(28px,4vw,42px)">GAMBLING</h1>
            <div class="status-bar">
                <span class="status-runda">RUNDA <?= max(1, $runda_st) ?> / <?= $stevilo_rund ?></span>
                <span>Best of <?= $stevilo_rund ?></span>
                <a href="index.php?reset=1" class="status-link">↻ Ponastavi</a>
            </div>
        </header>

        <section class="rezultati-bar">
            <?php foreach ($uporabniki as $idx => $u):
                $jeKZ = $konec_igre && in_array($idx, $koncni_zmagovalci, true); ?>
                <div class="rezultat-uporabnika <?= $jeKZ ? 'rezultat-zmagovalec' : '' ?>">
                    <div class="r-ime"><?= $jeKZ ? '👑 ' : '' ?><?= htmlspecialchars($u[0].' '.$u[1]) ?></div>
                    <div>
                        <div class="r-stevilo"><?= $zmage[$idx] ?></div>
                        <span class="r-label">zmag</span>
                    </div>
                    <div class="r-vsota">vsota: <strong><?= $skupne_vsote[$idx] ?></strong></div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="igralci">
            <?php foreach ($uporabniki as $idx => $u):
                $k1 = $meti ? $meti[$idx*3] : null;
                $k2 = $meti ? $meti[$idx*3+1] : null;
                $k3 = $meti ? $meti[$idx*3+2] : null;
                $vsota = $vsote ? $vsote[$idx] : null;
                $jeZR = $zmagovalci_runde && in_array($idx, $zmagovalci_runde, true) && count($zmagovalci_runde) === 1;
            ?>
                <div class="igralec <?= $jeZR ? 'igralec-zmagovalec' : '' ?>">
                    <div class="ig-stevilka">UPORABNIK <?= $idx+1 ?> <?= $jeZR ? '⭐' : '' ?></div>
                    <div class="igralec-info">
                        <?= htmlspecialchars($u[0].' '.$u[1]) ?>
                        <span class="info-naslov"><?= htmlspecialchars($u[2]) ?></span>
                    </div>
                    <div class="kocke">
                        <?php if ($k1 !== null): ?>
                            <img src="http://193.2.139.22/dice/dice<?= $k1 ?>.gif" class="kocka">
                            <img src="http://193.2.139.22/dice/dice<?= $k2 ?>.gif" class="kocka">
                            <img src="http://193.2.139.22/dice/dice<?= $k3 ?>.gif" class="kocka">
                        <?php else: ?>
                            <img src="http://193.2.139.22/dice/dice-anim.gif" class="kocka kocka-cakanje">
                            <img src="http://193.2.139.22/dice/dice-anim.gif" class="kocka kocka-cakanje">
                            <img src="http://193.2.139.22/dice/dice-anim.gif" class="kocka kocka-cakanje">
                        <?php endif; ?>
                    </div>
                    <div class="vsota">
                        <?php if ($vsota !== null): ?>
                            <?= $k1 ?>+<?= $k2 ?>+<?= $k3 ?> = <strong><?= $vsota ?></strong>
                        <?php else: ?>
                            <span class="vsota-cakanje">— čakam —</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="akcijska-vrstica">
            <?php if ($konec_igre): ?>
                <div class="rezultat-kompakt">
                    <?php if (count($koncni_zmagovalci) === 1):
                        $z = $koncni_zmagovalci[0]; ?>
                        <span class="zmaga-naslov">🏆 ZMAGOVALEC TURNIRJA 🏆</span>
                        <span class="zmagovalec-ime"><?= htmlspecialchars($uporabniki[$z][0].' '.$uporabniki[$z][1]) ?></span>
                        <span class="zmaga-vsota"><?= $zmage[$z] ?> zmag · vsota <?= $skupne_vsote[$z] ?></span>
                    <?php else: ?>
                        <span class="zmaga-naslov">🏆 NEODLOČENO 🏆</span>
                        <span class="zmagovalec-ime">
                            <?php
                                $imena = [];
                                foreach ($koncni_zmagovalci as $zi) $imena[] = htmlspecialchars($uporabniki[$zi][0].' '.$uporabniki[$zi][1]);
                                echo implode(' · ', $imena);
                            ?>
                        </span>
                    <?php endif; ?>
                    <span class="odstevanje-mini">Vrnitev čez <span id="odstevanje">10</span>s</span>
                </div>
            <?php else: ?>
                <form method="POST" action="igra.php" style="display:flex;align-items:center;gap:18px">
                    <button type="submit" name="vrzi" value="1" class="btn-vrzi">🎲 VRZI KOCKE 🎲</button>
                    <span class="vrzi-info">
                        <?= $runda_st === 0 ? 'Začni z runde 1' : 'Naslednja runda: ' . ($runda_st+1) . ' / ' . $stevilo_rund ?>
                    </span>
                </form>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
