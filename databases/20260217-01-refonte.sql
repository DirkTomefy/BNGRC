-- =====================================================
-- REFONTE COMPLETE DE LA CONCEPTION
-- =====================================================
-- Flux: Dons/Achats → Stock global → Distribution → Villes
-- Stock = (Dons + Achats) - Distributions
-- =====================================================

-- 1. Supprimer les anciennes tables (ordre inverse des dépendances)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS bn_stock;
DROP TABLE IF EXISTS bn_distribution;
DROP TABLE IF EXISTS bn_achat;
DROP TABLE IF EXISTS bn_don;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 2. NOUVELLE TABLE bn_don (sans idVille - stock global)
-- =====================================================
CREATE TABLE IF NOT EXISTS bn_don (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  idelement INT UNSIGNED NOT NULL,
  quantite INT UNSIGNED NOT NULL,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  description TEXT NULL,
  PRIMARY KEY (id),
  KEY idx_don_idelement (idelement),
  KEY idx_don_date (`date`),
  CONSTRAINT fk_don_element
    FOREIGN KEY (idelement) REFERENCES bn_element(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. NOUVELLE TABLE bn_achat (achats avec dons en argent)
-- =====================================================
CREATE TABLE IF NOT EXISTS bn_achat (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  idelement INT UNSIGNED NOT NULL,
  quantite INT UNSIGNED NOT NULL,
  prixUnitaire DECIMAL(15,2) NOT NULL,
  montantHT DECIMAL(15,2) NOT NULL,
  tauxFrais DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  montantFrais DECIMAL(15,2) NOT NULL,
  montantTTC DECIMAL(15,2) NOT NULL,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  description TEXT NULL,
  PRIMARY KEY (id),
  KEY idx_achat_idelement (idelement),
  KEY idx_achat_date (`date`),
  CONSTRAINT fk_achat_element
    FOREIGN KEY (idelement) REFERENCES bn_element(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. NOUVELLE TABLE bn_distribution (attribution aux villes)
-- =====================================================
CREATE TABLE IF NOT EXISTS bn_distribution (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  idVille INT UNSIGNED NOT NULL,
  idelement INT UNSIGNED NOT NULL,
  quantite INT UNSIGNED NOT NULL,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  source ENUM('don', 'achat') NOT NULL DEFAULT 'don',
  id_source INT UNSIGNED NULL COMMENT 'ID du don ou achat source (optionnel)',
  PRIMARY KEY (id),
  KEY idx_distribution_idVille (idVille),
  KEY idx_distribution_idelement (idelement),
  KEY idx_distribution_date (`date`),
  CONSTRAINT fk_distribution_ville
    FOREIGN KEY (idVille) REFERENCES bn_ville(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_distribution_element
    FOREIGN KEY (idelement) REFERENCES bn_element(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. VUE: Stock disponible par élément
-- Stock = Dons + Achats - Distributions
-- =====================================================
DROP VIEW IF EXISTS vue_stock;
CREATE VIEW vue_stock AS
SELECT 
    e.id AS idelement,
    e.libele AS element_libele,
    tb.id AS idTypeBesoin,
    tb.libele AS type_besoin,
    e.pu AS prix_unitaire,
    COALESCE(dons.total_dons, 0) AS quantite_dons,
    COALESCE(achats.total_achats, 0) AS quantite_achats,
    COALESCE(distrib.total_distribue, 0) AS quantite_distribuee,
    (COALESCE(dons.total_dons, 0) + COALESCE(achats.total_achats, 0) - COALESCE(distrib.total_distribue, 0)) AS quantite_stock
FROM bn_element e
JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
LEFT JOIN (
    SELECT idelement, SUM(quantite) AS total_dons
    FROM bn_don
    GROUP BY idelement
) dons ON e.id = dons.idelement
LEFT JOIN (
    SELECT idelement, SUM(quantite) AS total_achats
    FROM bn_achat
    GROUP BY idelement
) achats ON e.id = achats.idelement
LEFT JOIN (
    SELECT idelement, SUM(quantite) AS total_distribue
    FROM bn_distribution
    GROUP BY idelement
) distrib ON e.id = distrib.idelement
HAVING quantite_stock > 0 OR quantite_dons > 0 OR quantite_achats > 0;

-- =====================================================
-- 6. VUE: Besoins par ville avec satisfaits/restants
-- Satisfait = Distributions reçues pour cet élément dans cette ville
-- =====================================================
DROP VIEW IF EXISTS vue_besoins_ville;
CREATE VIEW vue_besoins_ville AS
SELECT 
    b.id AS id_besoin,
    b.idVille,
    v.libele AS ville_libele,
    r.id AS idRegion,
    r.libele AS region_libele,
    b.idelement,
    e.libele AS element_libele,
    tb.id AS idTypeBesoin,
    tb.libele AS type_besoin,
    e.pu AS prix_unitaire,
    b.quantite AS quantite_demandee,
    COALESCE(dist.quantite_recue, 0) AS quantite_recue,
    GREATEST(0, b.quantite - COALESCE(dist.quantite_recue, 0)) AS quantite_restante,
    b.`date` AS date_besoin
FROM bn_besoin b
JOIN bn_ville v ON b.idVille = v.id
JOIN bn_region r ON v.idRegion = r.id
JOIN bn_element e ON b.idelement = e.id
JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
LEFT JOIN (
    SELECT idVille, idelement, SUM(quantite) AS quantite_recue
    FROM bn_distribution
    GROUP BY idVille, idelement
) dist ON b.idVille = dist.idVille AND b.idelement = dist.idelement
ORDER BY b.`date` ASC, b.id ASC;

-- =====================================================
-- 7. VUE: Récapitulatif global
-- =====================================================
DROP VIEW IF EXISTS vue_recap_global;
CREATE VIEW vue_recap_global AS
SELECT
    (SELECT COALESCE(SUM(quantite * e.pu), 0) FROM bn_besoin b JOIN bn_element e ON b.idelement = e.id) AS montant_besoins_total,
    (SELECT COALESCE(SUM(d.quantite * e.pu), 0) FROM bn_don d JOIN bn_element e ON d.idelement = e.id) AS montant_dons_total,
    (SELECT COALESCE(SUM(montantTTC), 0) FROM bn_achat) AS montant_achats_total,
    (SELECT COALESCE(SUM(dist.quantite * e.pu), 0) FROM bn_distribution dist JOIN bn_element e ON dist.idelement = e.id) AS montant_distribue_total;
