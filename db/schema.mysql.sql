-- nartam müzayede şeması (MySQL / MariaDB — üretim)
-- Zaman alanları ISO-8601 dizesi olarak saklanır (kod DateTimeImmutable ile üretir/okur).

CREATE TABLE IF NOT EXISTS kullanicilar (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    eposta      VARCHAR(255) NOT NULL UNIQUE,
    ad          VARCHAR(255) NOT NULL,
    sifre_hash  VARCHAR(255) NOT NULL,
    rol         VARCHAR(20)  NOT NULL DEFAULT 'uye',
    olusturuldu VARCHAR(40)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ilanlar (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    baslik             VARCHAR(255) NOT NULL,
    baslangic_fiyati   INT          NOT NULL,
    saatlik_dusus      INT          NOT NULL,
    rezerv_fiyat       INT          NOT NULL,
    baslangic_zamani   VARCHAR(40)  NOT NULL,
    ilk_teklif_zamani  VARCHAR(40)  NULL,
    bitis_zamani       VARCHAR(40)  NULL,
    guncel_teklif      INT          NULL,
    son_teklif_sahibi  VARCHAR(255) NULL,
    olusturuldu        VARCHAR(40)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS teklifler (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ilan_id      INT NOT NULL,
    kullanici_id INT NOT NULL,
    miktar       INT NOT NULL,
    zaman        VARCHAR(40) NOT NULL,
    INDEX idx_teklifler_ilan (ilan_id),
    CONSTRAINT fk_teklif_ilan FOREIGN KEY (ilan_id) REFERENCES ilanlar(id) ON DELETE CASCADE,
    CONSTRAINT fk_teklif_kullanici FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
