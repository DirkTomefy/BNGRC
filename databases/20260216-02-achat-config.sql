-- Table de configuration (frais d'achat, etc.)
CREATE TABLE IF NOT EXISTS bn_config (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cle VARCHAR(100) NOT NULL UNIQUE,
  valeur VARCHAR(255) NOT NULL,
  description VARCHAR(255) NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des achats (dons en argent → besoins nature/matériel)
CREATE TABLE IF NOT EXISTS bn_achat (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  idBesoin INT UNSIGNED NOT NULL,
  idVille INT UNSIGNED NOT NULL,
  idelement INT UNSIGNED NOT NULL,
  quantite INT UNSIGNED NOT NULL,
  prixUnitaire DECIMAL(10,2) NOT NULL,
  montantHT DECIMAL(12,2) NOT NULL,
  tauxFrais DECIMAL(5,2) NOT NULL,
  montantFrais DECIMAL(12,2) NOT NULL,
  montantTTC DECIMAL(12,2) NOT NULL,
  `date` DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_achat_idBesoin (idBesoin),
  KEY idx_achat_idVille (idVille),
  KEY idx_achat_idelement (idelement),
  KEY idx_achat_date (`date`),
  CONSTRAINT fk_achat_besoin
    FOREIGN KEY (idBesoin) REFERENCES bn_besoin(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_achat_ville
    FOREIGN KEY (idVille) REFERENCES bn_ville(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_achat_element
    FOREIGN KEY (idelement) REFERENCES bn_element(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion de la configuration par défaut
INSERT INTO bn_config (cle, valeur, description) VALUES
  ('frais_achat_pourcent', '10', 'Pourcentage de frais sur les achats (ex: 10 = 10%)');

-- Vue des besoins restants (non satisfaits par les dons)
CREATE OR REPLACE VIEW vue_besoins_restants AS
SELECT 
    b.id,
    b.idelement,
    e.libele AS element_libele,
    tb.id AS idTypeBesoin,
    tb.libele AS type_besoin,
    b.quantite AS quantite_demandee,
    COALESCE(SUM(d.quantite), 0) AS quantite_donnee,
    COALESCE(SUM(a.quantite), 0) AS quantite_achetee,
    (b.quantite - COALESCE(SUM(d.quantite), 0) - COALESCE(SUM(a.quantite), 0)) AS quantite_restante,
    e.pu AS prix_unitaire,
    ((b.quantite - COALESCE(SUM(d.quantite), 0) - COALESCE(SUM(a.quantite), 0)) * e.pu) AS montant_restant,
    b.idVille,
    v.libele AS ville_libele,
    r.id AS idRegion,
    r.libele AS region_libele,
    b.date
FROM bn_besoin b
JOIN bn_element e ON b.idelement = e.id
JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
JOIN bn_ville v ON b.idVille = v.id
JOIN bn_region r ON v.idRegion = r.id
LEFT JOIN bn_don d ON d.idVille = b.idVille AND d.idelement = b.idelement
LEFT JOIN bn_achat a ON a.idBesoin = b.id
WHERE tb.libele IN ('Nature', 'Materiel')
GROUP BY b.id, b.idelement, e.libele, tb.id, tb.libele, b.quantite, e.pu, b.idVille, v.libele, r.id, r.libele, b.date
HAVING (b.quantite - COALESCE(SUM(d.quantite), 0) - COALESCE(SUM(a.quantite), 0)) > 0
ORDER BY b.date ASC, b.id ASC;

-- Vue des dons en argent disponibles
CREATE OR REPLACE VIEW vue_dons_argent_disponibles AS
SELECT 
    d.id,
    d.idelement,
    e.libele AS element_libele,
    d.quantite,
    e.pu AS prix_unitaire,
    (d.quantite * e.pu) AS montant_total,
    d.idVille,
    v.libele AS ville_libele,
    d.description,
    d.date
FROM bn_don d
JOIN bn_element e ON d.idelement = e.id
JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id
JOIN bn_ville v ON d.idVille = v.id
WHERE tb.libele = 'Argent'
ORDER BY d.date ASC, d.id ASC;

-- Vue récapitulative des besoins
CREATE OR REPLACE VIEW vue_recap_besoins AS
SELECT 
    'total' AS type,
    COUNT(*) AS nb_besoins,
    SUM(b.quantite) AS quantite_totale,
    SUM(b.quantite * e.pu) AS montant_total
FROM bn_besoin b
JOIN bn_element e ON b.idelement = e.id
UNION ALL
SELECT 
    'satisfait' AS type,
    COUNT(DISTINCT b.id) AS nb_besoins,
    COALESCE(SUM(d.quantite), 0) + COALESCE(SUM(a.quantite), 0) AS quantite_totale,
    (COALESCE(SUM(d.quantite), 0) + COALESCE(SUM(a.quantite), 0)) * AVG(e.pu) AS montant_total
FROM bn_besoin b
JOIN bn_element e ON b.idelement = e.id
LEFT JOIN bn_don d ON d.idVille = b.idVille AND d.idelement = b.idelement
LEFT JOIN bn_achat a ON a.idBesoin = b.id
WHERE d.id IS NOT NULL OR a.id IS NOT NULL;
