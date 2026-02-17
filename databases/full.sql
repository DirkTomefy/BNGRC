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
 ('Est'),
 ('Nord'),
 ('Ouest');

-- Villes
INSERT INTO bn_ville (idRegion, libele) VALUES
 ((SELECT id FROM bn_region WHERE libele = 'Est'), 'Toamasina'),
 ((SELECT id FROM bn_region WHERE libele = 'Est'), 'Mananjary'),
 ((SELECT id FROM bn_region WHERE libele = 'Est'), 'Farafangana'),
 ((SELECT id FROM bn_region WHERE libele = 'Nord'), 'Nosy Be'),
 ((SELECT id FROM bn_region WHERE libele = 'Ouest'), 'Morondava');

-- Types de besoins
INSERT INTO bn_typeBesoin (libele) VALUES
 ('Nature'),
 ('Materiel'),
 ('Argent');

-- Éléments
INSERT INTO bn_element (libele, idtypebesoin, pu) VALUES
 ('Riz (kg)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nature'), 3000.00),
 ('Eau (L)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nature'), 1000.00),
 ('Huile (L)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nature'), 6000.00),
 ('Haricots', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nature'), 4000.00),
 ('Tôle', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 25000.00),
 ('Bâche', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 15000.00),
 ('Clous (kg)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 8000.00),
 ('Bois', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 10000.00),
 ('groupe', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 6750000.00),
 ('Argent', (SELECT id FROM bn_typeBesoin WHERE libele = 'Argent'), 1.00);

-- Besoins
INSERT INTO bn_besoin (id, idelement, quantite, idVille, `date`) VALUES
 (17, (SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 800, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Toamasina'), '2026-02-16 00:00:00'),
 (4, (SELECT e.id FROM bn_element e WHERE e.libele = 'Eau (L)'), 1500, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Toamasina'), '2026-02-15 00:00:00'),
 (23, (SELECT e.id FROM bn_element e WHERE e.libele = 'Tôle'), 120, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Toamasina'), '2026-02-16 00:00:00'),
 (1, (SELECT e.id FROM bn_element e WHERE e.libele = 'Bâche'), 200, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Toamasina'), '2026-02-15 00:00:00'),
 (12, (SELECT e.id FROM bn_element e WHERE e.libele = 'Argent'), 12000000, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Toamasina'), '2026-02-16 00:00:00'),
 (9, (SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 500, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Mananjary'), '2026-02-15 00:00:00'),
 (25, (SELECT e.id FROM bn_element e WHERE e.libele = 'Huile (L)'), 120, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Mananjary'), '2026-02-16 00:00:00'),
 (6, (SELECT e.id FROM bn_element e WHERE e.libele = 'Tôle'), 80, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Mananjary'), '2026-02-15 00:00:00'),
 (19, (SELECT e.id FROM bn_element e WHERE e.libele = 'Clous (kg)'), 60, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Mananjary'), '2026-02-16 00:00:00'),
 (3, (SELECT e.id FROM bn_element e WHERE e.libele = 'Argent'), 6000000, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Mananjary'), '2026-02-15 00:00:00'),
 (21, (SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 600, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Farafangana'), '2026-02-16 00:00:00'),
 (14, (SELECT e.id FROM bn_element e WHERE e.libele = 'Eau (L)'), 1000, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Farafangana'), '2026-02-15 00:00:00'),
 (8, (SELECT e.id FROM bn_element e WHERE e.libele = 'Bâche'), 150, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Farafangana'), '2026-02-16 00:00:00'),
 (26, (SELECT e.id FROM bn_element e WHERE e.libele = 'Bois'), 100, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Farafangana'), '2026-02-15 00:00:00'),
 (10, (SELECT e.id FROM bn_element e WHERE e.libele = 'Argent'), 8000000, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Farafangana'), '2026-02-16 00:00:00'),
 (5, (SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 300, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Nosy Be'), '2026-02-15 00:00:00'),
 (18, (SELECT e.id FROM bn_element e WHERE e.libele = 'Haricots'), 200, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Nosy Be'), '2026-02-16 00:00:00'),
 (2, (SELECT e.id FROM bn_element e WHERE e.libele = 'Tôle'), 40, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Nosy Be'), '2026-02-15 00:00:00'),
 (24, (SELECT e.id FROM bn_element e WHERE e.libele = 'Clous (kg)'), 30, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Nosy Be'), '2026-02-16 00:00:00'),
 (7, (SELECT e.id FROM bn_element e WHERE e.libele = 'Argent'), 4000000, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Nosy Be'), '2026-02-15 00:00:00'),
 (11, (SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 700, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Morondava'), '2026-02-16 00:00:00'),
 (20, (SELECT e.id FROM bn_element e WHERE e.libele = 'Eau (L)'), 1200, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Morondava'), '2026-02-15 00:00:00'),
 (15, (SELECT e.id FROM bn_element e WHERE e.libele = 'Bâche'), 180, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Morondava'), '2026-02-16 00:00:00'),
 (22, (SELECT e.id FROM bn_element e WHERE e.libele = 'Bois'), 150, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Morondava'), '2026-02-15 00:00:00'),
 (13, (SELECT e.id FROM bn_element e WHERE e.libele = 'Argent'), 10000000, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Morondava'), '2026-02-16 00:00:00'),
 (16, (SELECT e.id FROM bn_element e WHERE e.libele = 'groupe'), 3, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Toamasina'), '2026-02-15 00:00:00');

 -- Dons (stock global : pas de ville)
 INSERT INTO bn_don (`date`, idelement, description, quantite) VALUES
  ('2026-02-15 08:00:00', (SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 'Don de riz', 200),
 ('2026-02-15 10:00:00', (SELECT e.id FROM bn_element e WHERE e.libele = 'Eau (L)'), 'Don d''eau', 500),
 ('2026-02-15 14:00:00', (SELECT e.id FROM bn_element e WHERE e.libele = 'Bâche'), 'Don de bâches', 50),
 ('2026-02-15 16:00:00', (SELECT e.id FROM bn_element e WHERE e.libele = 'Argent'), 'Don en argent', 2000000);

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


DROP VIEW IF EXISTS vue_ville_recap;

CREATE VIEW vue_ville_recap AS
SELECT 
    v.id AS ville_id,
    v.libele AS ville_libele,
    r.id AS region_id,
    r.libele AS region_libele,
    e.id AS element_id,
    e.libele AS element_libele,
    e.pu AS element_pu,
    tb.id AS type_besoin_id,
    tb.libele AS type_besoin_libele,
    COALESCE(b.quantite, 0) AS quantite_besoin,
    COALESCE(d.quantite_recue, 0) AS quantite_donnee,
    GREATEST(0, COALESCE(b.quantite, 0) - COALESCE(d.quantite_recue, 0)) AS quantite_restante,
    COALESCE(b.quantite, 0) * e.pu AS montant_besoin,
    GREATEST(0, COALESCE(b.quantite, 0) - COALESCE(d.quantite_recue, 0)) * e.pu AS montant_restant
FROM bn_ville v
JOIN bn_region r ON v.idRegion = r.id
LEFT JOIN (
    SELECT idVille, idelement, SUM(quantite) AS quantite
    FROM bn_besoin
    GROUP BY idVille, idelement
) b ON v.id = b.idVille
LEFT JOIN (
    SELECT idVille, idelement, SUM(quantite) AS quantite_recue
    FROM bn_distribution
    GROUP BY idVille, idelement
) d ON v.id = d.idVille AND b.idelement = d.idelement
LEFT JOIN bn_element e ON b.idelement = e.id
LEFT JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
WHERE b.idelement IS NOT NULL
ORDER BY r.libele, v.libele, e.libele;