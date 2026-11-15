<?php
const DATA_FILE = __DIR__ . '/filmler.json';

function tumIcerikler(): array {
    if (!file_exists(DATA_FILE)) {
        file_put_contents(DATA_FILE, '[]', LOCK_EX);
    }

    $json = file_get_contents(DATA_FILE);
    $veri = json_decode($json ?: '[]', true);

    return is_array($veri) ? $veri : [];
}

function kaydetIcerikler(array $icerikler): bool {
    return file_put_contents(
        DATA_FILE,
        json_encode(array_values($icerikler), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}

function guvenli($deger): string {
    return htmlspecialchars((string)$deger, ENT_QUOTES, 'UTF-8');
}

function icerikEkle(array $post): void {
    $icerikler = tumIcerikler();

    $yeni = [
        'id' => uniqid('mc_', true),
        'ad' => trim($post['ad'] ?? ''),
        'tur' => $post['tur'] ?? 'Film',
        'kategori' => trim($post['kategori'] ?? 'Diğer'),
        'yil' => trim($post['yil'] ?? ''),
        'puan' => trim($post['puan'] ?? ''),
        'durum' => $post['durum'] ?? 'İzlenecek',
        'afis' => trim($post['afis'] ?? ''),
        'ozet' => trim($post['ozet'] ?? ''),
        'yorum' => trim($post['yorum'] ?? ''),
        'tarih' => trim($post['tarih'] ?? ''),
        'sezon' => trim($post['sezon'] ?? ''),
        'bolum' => trim($post['bolum'] ?? ''),
        'favori' => false,
        'eklenme' => date('Y-m-d H:i:s')
    ];

    if ($yeni['ad'] !== '') {
        array_unshift($icerikler, $yeni);
        kaydetIcerikler($icerikler);
    }
}

function icerikGuncelle(array $post): void {
    $icerikler = tumIcerikler();

    foreach ($icerikler as &$icerik) {
        if (($icerik['id'] ?? '') === ($post['id'] ?? '')) {
            foreach ([
                'ad', 'tur', 'kategori', 'yil', 'puan',
                'durum', 'afis', 'ozet', 'yorum',
                'tarih', 'sezon', 'bolum'
            ] as $alan) {
                if (isset($post[$alan])) {
                    $icerik[$alan] = trim((string)$post[$alan]);
                }
            }
            break;
        }
    }

    unset($icerik);
    kaydetIcerikler($icerikler);
}

function favoriDegistir(string $id): void {
    $icerikler = tumIcerikler();

    foreach ($icerikler as &$icerik) {
        if (($icerik['id'] ?? '') === $id) {
            $icerik['favori'] = !($icerik['favori'] ?? false);
            break;
        }
    }

    unset($icerik);
    kaydetIcerikler($icerikler);
}

function icerikSil(string $id): void {
    $icerikler = array_filter(
        tumIcerikler(),
        fn($i) => ($i['id'] ?? '') !== $id
    );

    kaydetIcerikler($icerikler);
}

function filmKarti(array $i): void {
    $id = guvenli($i['id'] ?? '');
    $afis = guvenli($i['afis'] ?? '');
    $ad = guvenli($i['ad'] ?? '');
    $tur = guvenli($i['tur'] ?? 'Film');
    $kategori = guvenli($i['kategori'] ?? 'Diğer');
    $puan = guvenli(($i['puan'] ?? '') !== '' ? $i['puan'] : '—');
    $yil = guvenli($i['yil'] ?? 'Yıl belirtilmedi');
    $durum = guvenli($i['durum'] ?? 'İzlenecek');
    $favori = !empty($i['favori']);
    $yorum = guvenli($i['yorum'] ?? '');
    $sayfa = guvenli($_GET['sayfa'] ?? 'anasayfa');
    $sezon = guvenli($i['sezon'] ?? '—');
    $bolum = guvenli($i['bolum'] ?? '—');
    ?>

    <article class="movie-card" data-id="<?= $id ?>">

        <div class="poster-wrap">
            <img
                src="<?= $afis ?: 'https://placehold.co/500x720/eee8f7/8b78b4?text=My+Cinema' ?>"
                alt="<?= $ad ?>"
                onerror="this.src='https://placehold.co/500x720/eee8f7/8b78b4?text=My+Cinema'"
            >

            <span class="type-pill"><?= $tur ?></span>

            <form method="post" class="favorite-form">
                <input type="hidden" name="islem" value="favori">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="donus" value="<?= $sayfa ?>">

                <button
                    type="submit"
                    class="heart-button <?= $favori ? 'is-favorite' : '' ?>"
                    title="Favorilere ekle"
                >♥</button>
            </form>
        </div>

        <div class="movie-info">

            <div class="movie-topline">
                <span><?= $kategori ?></span>
                <b>★ <?= $puan ?></b>
            </div>

            <h3><?= $ad ?></h3>

            <p class="movie-year"><?= $yil ?></p>

            <span class="status-pill"><?= $durum ?></span>

            <?php if ($yorum !== ''): ?>
                <p class="movie-comment"><?= $yorum ?></p>
            <?php endif; ?>

            <?php if ($tur === 'Dizi'): ?>
                <p class="episode-line">
                    Sezon <?= $sezon ?> · Bölüm <?= $bolum ?>
                </p>
            <?php endif; ?>

            <div class="card-actions">

                <button
                    type="button"
                    class="edit-link"
                    onclick="editItem('<?= $id ?>')"
                >Düzenle</button>

                <form method="post" onsubmit="return confirm('Bu kaydı silmek istediğine emin misin?')">
                    <input type="hidden" name="islem" value="sil">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="donus" value="<?= $sayfa ?>">

                    <button class="delete-button" type="submit">
                        Kaydı Sil
                    </button>
                </form>

            </div>
        </div>
    </article>

    <?php
}