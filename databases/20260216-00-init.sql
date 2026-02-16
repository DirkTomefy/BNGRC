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

CREATE TABLE IF NOT EXISTS bn_don (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATETIME NOT NULL,
  idVille INT UNSIGNED NOT NULL,
  description TEXT NULL,
  quantite INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_don_idVille (idVille),
  KEY idx_don_date (`date`),
  CONSTRAINT fk_don_ville
    FOREIGN KEY (idVille) REFERENCES bn_ville(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;