-- =====================================================
-- SCRIPT COMPLET (INIT + REFONTE + SEED + RESET)
-- =====================================================

-- =====================================================
-- 0. DROP (ordre inverse des dépendances)
-- =====================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS vue_recap_global;
DROP VIEW IF EXISTS vue_besoins_ville;
DROP VIEW IF EXISTS vue_stock;

DROP TABLE IF EXISTS bn_donsinitial;
DROP TABLE IF EXISTS bn_besoin_initial;

DROP TABLE IF EXISTS bn_stock;
DROP TABLE IF EXISTS bn_distribution;
DROP TABLE IF EXISTS bn_achat;
DROP TABLE IF EXISTS bn_don;

DROP TABLE IF EXISTS bn_besoin;
DROP TABLE IF EXISTS bn_element;
DROP TABLE IF EXISTS bn_typeBesoin;
DROP TABLE IF EXISTS bn_ville;
DROP TABLE IF EXISTS bn_region;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. INIT (tables de base)
-- =====================================================
CREATE TABLE IF NOT EXISTS bn_region (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  libele VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bn_ville (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  idRegion INT UNSIGNED NOT NULL,
  libele VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_ville_idRegion (idRegion),
  CONSTRAINT fk_ville_region
    FOREIGN KEY (idRegion) REFERENCES bn_region(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bn_typeBesoin (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  libele VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bn_element (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  libele VARCHAR(255) NOT NULL,
  idtypebesoin INT UNSIGNED NOT NULL,
  pu DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_element_idtypebesoin (idtypebesoin),
  CONSTRAINT fk_element_typeBesoin
    FOREIGN KEY (idtypebesoin) REFERENCES bn_typeBesoin(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bn_besoin (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  idelement INT UNSIGNED NOT NULL,
  quantite INT UNSIGNED NOT NULL,
  idVille INT UNSIGNED NOT NULL,
  `date` DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_besoin_idelement (idelement),
  KEY idx_besoin_idVille (idVille),
  KEY idx_besoin_date (`date`),
  CONSTRAINT fk_besoin_element
    FOREIGN KEY (idelement) REFERENCES bn_element(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_besoin_ville
    FOREIGN KEY (idVille) REFERENCES bn_ville(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. REFONTE (dons/achats/distribution + vues)
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

DROP VIEW IF EXISTS vue_recap_global;
CREATE VIEW vue_recap_global AS
SELECT
    (SELECT COALESCE(SUM(quantite * e.pu), 0) FROM bn_besoin b JOIN bn_element e ON b.idelement = e.id) AS montant_besoins_total,
    (SELECT COALESCE(SUM(d.quantite * e.pu), 0) FROM bn_don d JOIN bn_element e ON d.idelement = e.id) AS montant_dons_total,
    (SELECT COALESCE(SUM(montantTTC), 0) FROM bn_achat) AS montant_achats_total,
    (SELECT COALESCE(SUM(dist.quantite * e.pu), 0) FROM bn_distribution dist JOIN bn_element e ON dist.idelement = e.id) AS montant_distribue_total;

-- =====================================================
-- 3. SEED (données importantes)
-- =====================================================
-- Régions
INSERT INTO bn_region (libele) VALUES
 ('Centre'),
 ('Nord'),
 ('Sud');

-- Villes
INSERT INTO bn_ville (idRegion, libele) VALUES
 ((SELECT id FROM bn_region WHERE libele = 'Centre'), 'Antananarivo'),
 ((SELECT id FROM bn_region WHERE libele = 'Centre'), 'Antsirabe'),
 ((SELECT id FROM bn_region WHERE libele = 'Nord'), 'Antsiranana'),
 ((SELECT id FROM bn_region WHERE libele = 'Sud'), 'Toliara');

-- Types de besoins
INSERT INTO bn_typeBesoin (libele) VALUES
 ('Nature'),
 ('Materiel'),
 ('Argent');

-- Éléments
INSERT INTO bn_element (libele, idtypebesoin, pu) VALUES
 ('Riz (kg)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nature'), 2500.00),
 ('Huile (L)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nature'), 5000.00),
 ('Savon (unité)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 1500.00),
 ('Gants médicaux (paire)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 2000.00),
 ('Carburant (L)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 6000.00);

-- Besoins
INSERT INTO bn_besoin (idelement, quantite, idVille, `date`) VALUES
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 300, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antananarivo'), '2026-02-10 09:30:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Huile (L)'), 120, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antsirabe'), '2026-02-11 14:15:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Savon (unité)'), 500, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antsiranana'), '2026-02-12 08:00:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Gants médicaux (paire)'), 200, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Toliara'), '2026-02-13 16:45:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Carburant (L)'), 400, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antananarivo'), '2026-02-14 11:20:00');

-- Dons (stock global : pas de ville)
INSERT INTO bn_don (`date`, idelement, description, quantite) VALUES
 ('2026-02-10 10:00:00', (SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 'Don de riz pour les ménages vulnérables', 200),
 ('2026-02-11 15:00:00', (SELECT e.id FROM bn_element e WHERE e.libele = 'Huile (L)'), 'Don d''huile de cuisine', 80),
 ('2026-02-12 09:00:00', (SELECT e.id FROM bn_element e WHERE e.libele = 'Savon (unité)'), 'Lot de savon', 300),
 ('2026-02-13 17:00:00', (SELECT e.id FROM bn_element e WHERE e.libele = 'Gants médicaux (paire)'), 'Gants médicaux pour le centre de santé', 150);

-- =====================================================
-- 4. RESET (snapshot + procédures)
-- =====================================================

-- Snapshot dons
DROP TABLE IF EXISTS bn_donsinitial;
CREATE TABLE bn_donsinitial LIKE bn_don;
TRUNCATE TABLE bn_donsinitial;
INSERT INTO bn_donsinitial
SELECT * FROM bn_don;

DROP PROCEDURE IF EXISTS reset_dons;
DELIMITER $$
CREATE PROCEDURE reset_dons()
BEGIN
  DELETE FROM bn_don;
  INSERT INTO bn_don
  SELECT * FROM bn_donsinitial;
END$$
DELIMITER ;

-- Snapshot besoins
DROP TABLE IF EXISTS bn_besoin_initial;
CREATE TABLE bn_besoin_initial LIKE bn_besoin;
TRUNCATE TABLE bn_besoin_initial;
INSERT INTO bn_besoin_initial
SELECT * FROM bn_besoin;

DROP PROCEDURE IF EXISTS reset_besoin;
DELIMITER $$
CREATE PROCEDURE reset_besoin()
BEGIN
  DELETE FROM bn_besoin;
  INSERT INTO bn_besoin
  SELECT * FROM bn_besoin_initial;
END$$
DELIMITER ;

-- Reset complet
DROP PROCEDURE IF EXISTS reset_all;
DELIMITER $$
CREATE PROCEDURE reset_all()
BEGIN
  CALL reset_besoin();
  CALL reset_dons();

  DELETE FROM bn_achat;
  DELETE FROM bn_distribution;
END$$
DELIMITER ;
