-- nartam müzayede şeması (SQLite)

CREATE TABLE IF NOT EXISTS kullanicilar (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    eposta      TEXT    NOT NULL UNIQUE,
    ad          TEXT    NOT NULL,
    sifre_hash  TEXT    NOT NULL,
    rol         TEXT    NOT NULL DEFAULT 'uye',   -- 'uye' | 'yonetici'
    olusturuldu TEXT    NOT NULL
);

CREATE TABLE IF NOT EXISTS ilanlar (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    baslik             TEXT    NOT NULL,
    baslangic_fiyati   INTEGER NOT NULL,
    saatlik_dusus      INTEGER NOT NULL,
    rezerv_fiyat       INTEGER NOT NULL,
    baslangic_zamani   TEXT    NOT NULL,
    ilk_teklif_zamani  TEXT,                       -- NULL: hâlâ düşüş fazında
    bitis_zamani       TEXT,                       -- açık artırma bitişi
    guncel_teklif      INTEGER,                    -- en yüksek teklif
    son_teklif_sahibi  TEXT,                       -- en yüksek teklifi verenin adı
    olusturuldu        TEXT    NOT NULL
);

CREATE TABLE IF NOT EXISTS teklifler (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    ilan_id      INTEGER NOT NULL REFERENCES ilanlar(id) ON DELETE CASCADE,
    kullanici_id INTEGER NOT NULL REFERENCES kullanicilar(id),
    miktar       INTEGER NOT NULL,
    zaman        TEXT    NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_teklifler_ilan ON teklifler(ilan_id);
