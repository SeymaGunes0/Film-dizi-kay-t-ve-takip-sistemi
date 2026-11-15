<?php
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem = $_POST['islem'] ?? '';

    if ($islem === 'ekle') icerikEkle($_POST);
    if ($islem === 'guncelle') icerikGuncelle($_POST);
    if ($islem === 'sil') icerikSil($_POST['id'] ?? '');
    if ($islem === 'favori') favoriDegistir($_POST['id'] ?? '');

    $hedef = $_POST['donus'] ?? 'anasayfa';

    header('Location: index.php?sayfa=' . urlencode($hedef));
    exit;
}

$icerikler = tumIcerikler();

$sayfa = $_GET['sayfa'] ?? 'anasayfa';
$arama = trim($_GET['q'] ?? '');
$filtre = $_GET['filtre'] ?? 'Hepsi';

$gosterilen = array_values(array_filter($icerikler, function($i) use ($arama, $filtre, $sayfa) {

    $uygunArama = $arama === '' ||
        mb_stripos(($i['ad'] ?? '') . ' ' . ($i['kategori'] ?? ''), $arama) !== false;

    $durum = $i['durum'] ?? 'İzlenecek';

    $uygunFiltre = $filtre === 'Hepsi' ||
        $filtre === ($i['tur'] ?? '') ||
        $filtre === $durum;

    if ($sayfa === 'favoriler') {
        return $uygunArama && !empty($i['favori']);
    }

    if ($sayfa === 'gunluk') {
        return $uygunArama && !empty($i['tarih']);
    }

    return $uygunArama && $uygunFiltre;
}));

$toplam = count($icerikler);

$filmSayisi = count(array_filter($icerikler, fn($i) => ($i['tur'] ?? '') === 'Film'));

$diziSayisi = count(array_filter($icerikler, fn($i) => ($i['tur'] ?? '') === 'Dizi'));

$izlenenSayisi = count(array_filter($icerikler, fn($i) => ($i['durum'] ?? '') === 'İzlendi'));

$favoriSayisi = count(array_filter($icerikler, fn($i) => !empty($i['favori'])));

$puanlar = array_filter(
    array_map(fn($i) => is_numeric($i['puan'] ?? null) ? (float)$i['puan'] : null, $icerikler),
    fn($p) => $p !== null && $p > 0
);

$ortalama = count($puanlar)
    ? number_format(array_sum($puanlar) / count($puanlar), 1)
    : '—';

$turler = [
    'Korku', 'Romantik', 'Aksiyon', 'Komedi',
    'Bilim Kurgu', 'Dram', 'Gerilim', 'Animasyon', 'Diğer'
];

$durumlar = ['İzlendi', 'İzleniyor', 'İzlenecek'];
?>

<!DOCTYPE html>
<html lang="tr">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Cinema | Kişisel Sinema Arşivim</title>

<link rel="stylesheet" href="style.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

</head>

<body>

<div class="stars"></div>

<header class="topbar">

    <a class="brand" href="index.php">
        <span>✦</span> My Cinema
    </a>

    <nav class="nav-links">

        <a class="<?= $sayfa === 'anasayfa' ? 'active' : '' ?>" href="index.php">
            Ana Sayfa
        </a>

        <a class="<?= $sayfa === 'arsiv' ? 'active' : '' ?>" href="index.php?sayfa=arsiv">
            Arşivim
        </a>

        <button type="button" class="nav-button" id="wheelOpen">
            ✧ Ne İzlesem?
        </button>

        <a class="<?= $sayfa === 'favoriler' ? 'active' : '' ?>" href="index.php?sayfa=favoriler">
            ♡ Favorilerim
        </a>

        <a class="<?= $sayfa === 'istatistik' ? 'active' : '' ?>" href="index.php?sayfa=istatistik">
            İstatistikler
        </a>

        <a class="<?= $sayfa === 'gunluk' ? 'active' : '' ?>" href="index.php?sayfa=gunluk">
            İzleme Günlüğü
        </a>

    </nav>

    <div class="top-actions">

        <button class="add-button" id="addOpen">
            ＋ İçerik Ekle
        </button>

        <label class="color-picker" title="Arka plan rengini değiştir">
            🎨
            <input type="color" id="bgPicker" value="#a594c5">
        </label>

    </div>

    <button class="mobile-menu" id="mobileMenu">☰</button>

</header>


<main class="container">

<?php if ($sayfa === 'anasayfa'): ?>

    <section class="hero">

        <div class="hero-copy">

            <p class="eyebrow">KİŞİSEL SİNEMA ARŞİVİN</p>

            <h1>
                Film ve dizi<br>
                dünyanı <span>keşfet.</span>
            </h1>

            <p class="hero-text">
                İzlediğin, izlemeye devam ettiğin ve keşfetmek istediğin
                tüm yapımları tek bir yerde topla.
            </p>

            <button class="primary-button" id="heroAdd">
                ＋ Yeni İçerik Ekle
            </button>

        </div>

        <div class="hero-art">

            <div class="clapper">🎬</div>

            <small>YOUR PERSONAL</small>

            <strong>Movie Collection</strong>

            <span>Every story has a place.</span>

        </div>

    </section>


    <section class="stats-grid">

        <div class="stat-card">
            <span class="stat-icon">🎞️</span>
            <div>
                <small>Toplam İçerik</small>
                <strong><?= $toplam ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon">🎬</span>
            <div>
                <small>Filmler</small>
                <strong><?= $filmSayisi ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon">📺</span>
            <div>
                <small>Diziler</small>
                <strong><?= $diziSayisi ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon">💜</span>
            <div>
                <small>İzlenenler</small>
                <strong><?= $izlenenSayisi ?></strong>
            </div>
        </div>

    </section>


    <section class="section-heading">

        <div>
            <p class="eyebrow">KOLEKSİYONUNDAN</p>
            <h2>Son eklenenler</h2>
        </div>

        <a class="text-link" href="index.php?sayfa=arsiv">
            Tüm arşivi gör →
        </a>

    </section>


    <?php $kartlar = array_slice($icerikler, 0, 4); ?>

    <?php if ($kartlar): ?>

        <div class="movie-grid">

            <?php foreach ($kartlar as $i) filmKarti($i); ?>

        </div>

    <?php else: ?>

        <div class="empty-state">

            <span>✧</span>

            <h3>Arşivin henüz boş</h3>

            <p>
                İlk film veya dizini ekleyerek koleksiyonunu oluşturmaya başla.
            </p>

            <button class="primary-button" id="emptyAdd">
                ＋ İlk İçeriğini Ekle
            </button>

        </div>

    <?php endif; ?>


<?php elseif ($sayfa === 'istatistik'): ?>

    <section class="page-heading">

        <p class="eyebrow">KENDİ SİNEMA ALIŞKANLIKLARIN</p>

        <h1>İstatistiklerim</h1>

        <p>Koleksiyonunun küçük bir özeti.</p>

    </section>


    <section class="stats-grid stats-grid-page">

        <div class="stat-card">
            <span class="stat-icon">🎞️</span>
            <div>
                <small>Toplam İçerik</small>
                <strong><?= $toplam ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon">💜</span>
            <div>
                <small>Favoriler</small>
                <strong><?= $favoriSayisi ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon">✓</span>
            <div>
                <small>İzlenen</small>
                <strong><?= $izlenenSayisi ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon">⭐</span>
            <div>
                <small>Ortalama Puan</small>
                <strong><?= $ortalama ?></strong>
            </div>
        </div>

    </section>


    <div class="dashboard-grid">

        <section class="panel">

            <h2>İçerik dağılımı</h2>

            <div class="bar-row">
                <span>Filmler</span>
                <div class="bar-track">
                    <i style="width:<?= $toplam ? ($filmSayisi / $toplam * 100) : 0 ?>%"></i>
                </div>
                <b><?= $filmSayisi ?></b>
            </div>

            <div class="bar-row">
                <span>Diziler</span>
                <div class="bar-track">
                    <i style="width:<?= $toplam ? ($diziSayisi / $toplam * 100) : 0 ?>%"></i>
                </div>
                <b><?= $diziSayisi ?></b>
            </div>

        </section>


        <section class="panel">

            <h2>İzleme durumun</h2>

            <?php foreach ($durumlar as $d):

                $adet = count(array_filter(
                    $icerikler,
                    fn($i) => ($i['durum'] ?? '') === $d
                ));

            ?>

                <div class="bar-row">

                    <span><?= guvenli($d) ?></span>

                    <div class="bar-track">
                        <i style="width:<?= $toplam ? ($adet / $toplam * 100) : 0 ?>%"></i>
                    </div>

                    <b><?= $adet ?></b>

                </div>

            <?php endforeach; ?>

        </section>

    </div>


<?php else: ?>

    <section class="archive-heading">

        <div>

            <p class="eyebrow">MY CINEMA KOLEKSİYONU</p>

            <h1>
                <?= $sayfa === 'favoriler'
                    ? 'Favorilerim'
                    : ($sayfa === 'gunluk'
                        ? 'İzleme Günlüğüm'
                        : 'Film & Dizi Arşivim') ?>
            </h1>

            <p>
                <?= $sayfa === 'favoriler'
                    ? 'En sevdiğin yapımlar bir arada.'
                    : ($sayfa === 'gunluk'
                        ? 'İzleme tarihlerini burada takip et.'
                        : 'Bütün içeriklerini keşfet ve düzenle.') ?>
            </p>

        </div>

        <form class="search-form" method="get">

            <input type="hidden" name="sayfa" value="<?= guvenli($sayfa) ?>">

            <input
                name="q"
                value="<?= guvenli($arama) ?>"
                placeholder="🔎 Film veya dizi ara..."
            >

        </form>

    </section>


    <?php if ($sayfa !== 'favoriler' && $sayfa !== 'gunluk'): ?>

        <div class="filters">

            <a class="<?= $filtre === 'Hepsi' ? 'selected' : '' ?>" href="?sayfa=arsiv">
                Hepsi
            </a>

            <a class="<?= $filtre === 'Film' ? 'selected' : '' ?>" href="?sayfa=arsiv&filtre=Film">
                Filmler
            </a>

            <a class="<?= $filtre === 'Dizi' ? 'selected' : '' ?>" href="?sayfa=arsiv&filtre=Dizi">
                Diziler
            </a>

            <?php foreach ($durumlar as $d): ?>

                <a
                    class="<?= $filtre === $d ? 'selected' : '' ?>"
                    href="?sayfa=arsiv&filtre=<?= urlencode($d) ?>"
                >
                    <?= guvenli($d) ?>
                </a>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <?php if ($gosterilen): ?>

        <div class="movie-grid">

            <?php foreach ($gosterilen as $i) filmKarti($i); ?>

        </div>

    <?php else: ?>

        <div class="empty-state">

            <span>✧</span>

            <h3>Burada henüz içerik yok</h3>

            <p>Yeni bir film veya dizi ekleyerek başlayabilirsin.</p>

            <button class="primary-button" id="emptyAdd">
                ＋ İçerik Ekle
            </button>

        </div>

    <?php endif; ?>

<?php endif; ?>

</main>


<!-- YENİ İÇERİK EKLEME PENCERESİ -->

<div class="modal" id="addModal">

    <div class="modal-backdrop" data-close="addModal"></div>

    <section class="modal-box form-modal">

        <button class="modal-close" data-close="addModal">×</button>

        <p class="eyebrow">KOLEKSİYONUNU GENİŞLET</p>

        <h2 id="formTitle">Yeni içerik ekle</h2>

        <form method="post" id="contentForm">

            <input type="hidden" name="islem" id="formAction" value="ekle">

            <input type="hidden" name="id" id="formId">

            <input type="hidden" name="donus" value="arsiv">

            <div class="form-grid">

                <label class="wide">
                    Film / Dizi adı
                    <input required name="ad" id="fAd" placeholder="Örn. Interstellar">
                </label>

                <label>
                    İçerik türü
                    <select name="tur" id="fTur">
                        <option>Film</option>
                        <option>Dizi</option>
                    </select>
                </label>

                <label>
                    Yayın yılı
                    <input name="yil" id="fYil" type="number" min="1900" max="2100" placeholder="2024">
                </label>

                <label>
                    Kategori
                    <select name="kategori" id="fKategori">
                        <?php foreach ($turler as $t): ?>
                            <option><?= guvenli($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    İzleme durumu
                    <select name="durum" id="fDurum">
                        <?php foreach ($durumlar as $d): ?>
                            <option><?= guvenli($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Puan (10 üzerinden)
                    <input name="puan" id="fPuan" type="number" min="0" max="10" step="0.1" placeholder="9.0">
                </label>

                <label>
                    İzleme tarihi
                    <input name="tarih" id="fTarih" type="date">
                </label>

                <label class="wide">
                    Afiş görseli bağlantısı
                    <input name="afis" id="fAfis" placeholder="https://... (isteğe bağlı)">
                </label>

                <label class="wide">
                    Kısa özet
                    <textarea name="ozet" id="fOzet" rows="3" placeholder="Yapımın konusunu yaz..."></textarea>
                </label>

                <label class="wide">
                    Kişisel yorumun
                    <textarea name="yorum" id="fYorum" rows="2" placeholder="Bu yapım hakkında ne düşünüyorsun?"></textarea>
                </label>

                <label>
                    Dizi sezonu
                    <input name="sezon" id="fSezon" placeholder="1">
                </label>

                <label>
                    Son bölüm
                    <input name="bolum" id="fBolum" placeholder="5">
                </label>

            </div>

            <button class="primary-button full-button" type="submit">
                Kaydet
            </button>

        </form>

    </section>

</div>


<!-- FİLM DETAY PENCERESİ -->

<div class="modal" id="detailModal">

    <div class="modal-backdrop" data-close="detailModal"></div>

    <section class="modal-box detail-box">

        <button class="modal-close" data-close="detailModal">×</button>

        <div class="detail-content">

            <img id="detailPoster" src="" alt="Afiş">

            <div>

                <span class="detail-tag" id="detailType"></span>

                <h2 id="detailTitle"></h2>

                <p class="detail-meta" id="detailMeta"></p>

                <p class="detail-summary" id="detailSummary"></p>

                <div class="detail-rating" id="detailRating"></div>

                <h3>Kişisel yorumum</h3>

                <p id="detailComment"></p>

                <div class="detail-actions">

                    <button class="primary-button" id="detailEdit">
                        Düzenle
                    </button>

                    <form method="post" id="detailFavForm">

                        <input type="hidden" name="islem" value="favori">

                        <input type="hidden" name="id" id="detailFavId">

                        <input type="hidden" name="donus" value="arsiv">

                        <button class="soft-button" type="submit">
                            ♡ Favorilere ekle / çıkar
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </section>

</div>


<!-- NE İZLESEM ÇARKI -->

<div class="modal" id="wheelModal">

    <div class="modal-backdrop" data-close="wheelModal"></div>

    <section class="modal-box wheel-box">

        <button class="modal-close" data-close="wheelModal">×</button>

        <p class="eyebrow">KARAR VEREMİYOR MUSUN?</p>

        <h2>Ne izlesem?</h2>

        <p class="muted">
            Koleksiyonundan sana rastgele bir yapım seçelim.
        </p>

        <div class="wheel-controls">

            <select id="wheelType">
                <option value="Hepsi">Film ve diziler</option>
                <option value="Film">Sadece filmler</option>
                <option value="Dizi">Sadece diziler</option>
            </select>

            <select id="wheelGenre">

                <option value="Hepsi">Bütün türler</option>

                <?php foreach ($turler as $t): ?>
                    <option><?= guvenli($t) ?></option>
                <?php endforeach; ?>

            </select>

            <label class="check-line">
                <input type="checkbox" id="unwatchedOnly" checked>
                Sadece izlemediklerim
            </label>

        </div>

        <div class="wheel-disc" id="wheelDisc">

            <span>🎬</span>

            <small>Şansını dene!</small>

        </div>

        <button class="primary-button spin-button" id="spinButton">
            ✧ Çarkı Çevir
        </button>

        <div class="wheel-result" id="wheelResult" hidden>

            <img id="wheelPoster" src="" alt="">

            <div>

                <small>BUGÜNÜN SEÇİMİ</small>

                <h3 id="wheelName"></h3>

                <p id="wheelSummary"></p>

                <button class="soft-button" id="wheelDetails">
                    Detayları gör
                </button>

            </div>

        </div>

    </section>

</div>


<script>

const cinemaItems = <?= json_encode(
    $icerikler,
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
) ?>;

const fallbackPoster = 'https://placehold.co/500x720/eee8f7/8b78b4?text=My+Cinema';


function openModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.classList.add('modal-open');
}


function closeModal(id) {
    document.getElementById(id).classList.remove('show');

    if (!document.querySelector('.modal.show')) {
        document.body.classList.remove('modal-open');
    }
}


document.querySelectorAll('[data-close]').forEach(button => {
    button.addEventListener('click', () => {
        closeModal(button.dataset.close);
    });
});


document.getElementById('addOpen')?.addEventListener('click', openAdd);

document.getElementById('heroAdd')?.addEventListener('click', openAdd);

document.getElementById('emptyAdd')?.addEventListener('click', openAdd);


function openAdd() {

    document.getElementById('contentForm').reset();

    document.getElementById('formAction').value = 'ekle';

    document.getElementById('formId').value = '';

    document.getElementById('formTitle').textContent = 'Yeni içerik ekle';

    openModal('addModal');
}


function editItem(id) {

    const x = cinemaItems.find(i => i.id === id);

    if (!x) return;

    openAdd();

    document.getElementById('formAction').value = 'guncelle';

    document.getElementById('formId').value = x.id;

    document.getElementById('formTitle').textContent = 'İçeriği düzenle';

    const alanlar = [
        'Ad:ad',
        'Tur:tur',
        'Yil:yil',
        'Kategori:kategori',
        'Durum:durum',
        'Puan:puan',
        'Tarih:tarih',
        'Afis:afis',
        'Ozet:ozet',
        'Yorum:yorum',
        'Sezon:sezon',
        'Bolum:bolum'
    ];

    alanlar.forEach(pair => {

        const [a, b] = pair.split(':');

        document.getElementById('f' + a).value = x[b] ?? '';

    });

}


function showDetails(id) {

    const x = cinemaItems.find(i => i.id === id);

    if (!x) return;

    document.getElementById('detailPoster').src = x.afis || fallbackPoster;

    document.getElementById('detailTitle').textContent = x.ad || '';

    document.getElementById('detailType').textContent =
        (x.tur || '') + ' · ' + (x.kategori || '');

    document.getElementById('detailMeta').textContent =
        (x.yil || 'Yıl belirtilmedi') + ' · ' + (x.durum || '');

    document.getElementById('detailSummary').textContent =
        x.ozet || 'Bu içerik için henüz bir özet eklenmemiş.';

    document.getElementById('detailRating').textContent =
        x.puan ? '★ ' + x.puan + '/10' : 'Henüz puanlanmadı';

    document.getElementById('detailComment').textContent =
        x.yorum || 'Henüz kişisel yorum eklenmemiş.';

    document.getElementById('detailFavId').value = x.id;

    document.getElementById('detailEdit').onclick = () => {

        closeModal('detailModal');

        editItem(id);

    };

    openModal('detailModal');

}


document.querySelectorAll('.movie-card').forEach(card => {

    card.addEventListener('click', e => {

        if (e.target.closest('button,form,a')) return;

        showDetails(card.dataset.id);

    });

});


document.getElementById('wheelOpen').addEventListener('click', () => {
    openModal('wheelModal');
});


let selectedWheelItem = null;


document.getElementById('spinButton').addEventListener('click', () => {

    let list = cinemaItems.filter(x => {

        let type = document.getElementById('wheelType').value;

        let genre = document.getElementById('wheelGenre').value;

        let only = document.getElementById('unwatchedOnly').checked;

        return (
            (type === 'Hepsi' || x.tur === type) &&
            (genre === 'Hepsi' || x.kategori === genre) &&
            (!only || x.durum !== 'İzlendi')
        );

    });


    const result = document.getElementById('wheelResult');


    if (!list.length) {

        result.hidden = false;

        document.getElementById('wheelName').textContent =
            'Uygun içerik bulunamadı';

        document.getElementById('wheelSummary').textContent =
            'Filtreleri değiştirebilir veya izlemediklerim seçeneğini kaldırabilirsin.';

        document.getElementById('wheelPoster').src = fallbackPoster;

        selectedWheelItem = null;

        return;

    }


    const disc = document.getElementById('wheelDisc');

    disc.classList.remove('spinning');

    void disc.offsetWidth;

    disc.classList.add('spinning');

    document.getElementById('spinButton').disabled = true;


    setTimeout(() => {

        selectedWheelItem = list[Math.floor(Math.random() * list.length)];

        document.getElementById('wheelPoster').src =
            selectedWheelItem.afis || fallbackPoster;

        document.getElementById('wheelName').textContent =
            selectedWheelItem.ad;

        document.getElementById('wheelSummary').textContent =
            selectedWheelItem.ozet || 'Bu yapım için henüz özet eklenmemiş.';

        result.hidden = false;

        document.getElementById('spinButton').disabled = false;

    }, 900);

});


document.getElementById('wheelDetails').addEventListener('click', () => {

    if (selectedWheelItem) {

        closeModal('wheelModal');

        showDetails(selectedWheelItem.id);

    }

});


document.getElementById('bgPicker').addEventListener('input', e => {

    document.documentElement.style.setProperty('--page-bg', e.target.value);

});


document.getElementById('mobileMenu').addEventListener('click', () => {

    document.querySelector('.nav-links').classList.toggle('mobile-open');

});


document.addEventListener('keydown', e => {

    if (e.key === 'Escape') {

        document.querySelectorAll('.modal.show').forEach(m => {
            closeModal(m.id);
        });

    }

});

</script>

</body>
</html>