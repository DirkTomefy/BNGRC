-- Migration: add stock table to track purchased and donated stocks
CREATE TABLE IF NOT EXISTS `bn_stock` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idelement` INT NOT NULL,
  `quantite` INT NOT NULL,
  `source` ENUM('achat','don') NOT NULL,
  `id_ref` INT DEFAULT NULL,
  `assigned` TINYINT(1) NOT NULL DEFAULT 0,
  `idVille_assigned` INT DEFAULT NULL,
  `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_assigned` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`idelement`),
  INDEX (`id_ref`),
  INDEX (`idVille_assigned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
