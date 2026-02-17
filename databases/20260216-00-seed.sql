-- Régions
INSERT INTO bn_region (libele) VALUES
 ('Centre'),
 ('Nord'),
 ('Sud');

-- Villes (traduction en malgache)
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

INSERT INTO bn_element (libele, idtypebesoin, pu) VALUES
 ('Riz (kg)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nature'), 2500.00),
 ('Huile (L)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nature'), 5000.00),
 ('Savon (unité)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 1500.00),
 ('Gants médicaux (paire)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 2000.00),
 ('Carburant (L)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Materiel'), 6000.00);

INSERT INTO bn_element (libele, idtypebesoin, pu) VALUES
 ('Dollar', (SELECT id FROM bn_typeBesoin WHERE libele = 'Argent'), 5000);

-- Besoins
INSERT INTO bn_besoin (idelement, quantite, idVille, `date`) VALUES
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 300, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antananarivo'), '2026-02-10 09:30:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Huile (L)'), 120, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antsirabe'), '2026-02-11 14:15:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Savon (unité)'), 500, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antsiranana'), '2026-02-12 08:00:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Gants médicaux (paire)'), 200, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Toliara'), '2026-02-13 16:45:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Carburant (L)'), 400, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antananarivo'), '2026-02-14 11:20:00');

INSERT INTO bn_don (`date`, idVille, idelement, description, quantite) VALUES
 ('2026-02-10 10:00:00', (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antsirabe'), (SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 'Don de riz pour les ménages vulnérables', 200),
 ('2026-02-11 15:00:00', (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antsirabe'), (SELECT e.id FROM bn_element e WHERE e.libele = 'Huile (L)'), 'Don d''huile de cuisine', 80),
 ('2026-02-12 09:00:00', (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antsiranana'), (SELECT e.id FROM bn_element e WHERE e.libele = 'Savon (unité)'), 'Lot de savon', 300),
 ('2026-02-13 17:00:00', (SELECT v.id FROM bn_ville v WHERE v.libele = 'Antananarivo'), (SELECT e.id FROM bn_element e WHERE e.libele = 'Gants médicaux (paire)'), 'Gants médicaux pour le centre de santé', 150);

