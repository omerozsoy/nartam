-- nartam müzayede şeması (SQLite — yerel geliştirme)

CREATE TABLE IF NOT EXISTS kullanicilar (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    eposta      TEXT    NOT NULL UNIQUE,
    ad          TEXT    NOT NULL,
    sifre_hash  TEXT    NOT NULL,
    rol         TEXT    NOT NULL DEFAULT 'uye',
    olusturuldu TEXT    NOT NULL
);

CREATE TABLE IF NOT EXISTS ilanlar (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    baslik             TEXT    NOT NULL,
    baslangic_fiyati   INTEGER NOT NULL,
    saatlik_dusus      INTEGER NOT NULL,
    rezerv_fiyat       INTEGER NOT NULL,
    baslangic_zamani   TEXT    NOT NULL,
    ilk_teklif_zamani  TEXT,
    bitis_zamani       TEXT,
    guncel_teklif      INTEGER,
    son_teklif_sahibi  TEXT,
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
