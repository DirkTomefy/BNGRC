-- =====================================================
-- RESET DONS: snapshot initial + procédure de reset
-- =====================================================

-- 1) Crée une table snapshot avec la même structure que bn_don
DROP TABLE IF EXISTS bn_donsinitial;
CREATE TABLE bn_donsinitial LIKE bn_don;

-- 2) Capture l'état initial (au moment d'exécution de ce script)
TRUNCATE TABLE bn_donsinitial;
INSERT INTO bn_donsinitial
SELECT * FROM bn_don;

-- 3) Procédure: restaure bn_don depuis bn_donsinitial
DROP PROCEDURE IF EXISTS reset_dons;
DELIMITER $$
CREATE PROCEDURE reset_dons()
BEGIN
  -- Réinitialise la table bn_don à son état initial
  DELETE FROM bn_don;

  -- Réinsère en conservant les IDs et dates d'origine
  INSERT INTO bn_don
  SELECT * FROM bn_donsinitial;
END$$
DELIMITER ;

-- =====================================================
-- RESET BESOINS: snapshot initial + procédure de reset
-- =====================================================

-- 1) Crée une table snapshot avec la même structure que bn_besoin
DROP TABLE IF EXISTS bn_besoin_initial;
CREATE TABLE bn_besoin_initial LIKE bn_besoin;

-- 2) Capture l'état initial (au moment d'exécution de ce script)
TRUNCATE TABLE bn_besoin_initial;
INSERT INTO bn_besoin_initial
SELECT * FROM bn_besoin;

-- 3) Procédure: restaure bn_besoin depuis bn_besoin_initial
DROP PROCEDURE IF EXISTS reset_besoin;
DELIMITER $$
CREATE PROCEDURE reset_besoin()
BEGIN
  -- Réinitialise la table bn_besoin à son état initial
  DELETE FROM bn_besoin;

  -- Réinsère en conservant les IDs et dates d'origine
  INSERT INTO bn_besoin
  SELECT * FROM bn_besoin_initial;
END$$
DELIMITER ;

-- =====================================================
-- RESET COMPLET
-- =====================================================
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
