
INSERT INTO bn_region (libele) VALUES
 ('Centre'),
 ('Nord'),
 ('Sud');

INSERT INTO bn_ville (idRegion, libele) VALUES
 ((SELECT id FROM bn_region WHERE libele = 'Centre'), 'Ouagadougou'),
 ((SELECT id FROM bn_region WHERE libele = 'Centre'), 'Koudougou'),
 ((SELECT id FROM bn_region WHERE libele = 'Nord'), 'Ouahigouya'),
 ((SELECT id FROM bn_region WHERE libele = 'Sud'), 'Gaoua');

INSERT INTO bn_typeBesoin (libele) VALUES
 ('Nourriture'),
 ('Santé'),
 ('Logistique');

INSERT INTO bn_element (libele, idtypebesoin, pu) VALUES
 ('Riz (kg)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nourriture'), 650.00),
 ('Huile (L)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Nourriture'), 1200.00),
 ('Savon (unité)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Santé'), 250.00),
 ('Gants médicaux (paire)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Santé'), 300.00),
 ('Carburant (L)', (SELECT id FROM bn_typeBesoin WHERE libele = 'Logistique'), 900.00);

INSERT INTO bn_besoin (idelement, quantite, idVille, `date`) VALUES
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Riz (kg)'), 300, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Ouagadougou'), '2026-02-10 09:30:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Huile (L)'), 120, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Koudougou'), '2026-02-11 14:15:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Savon (unité)'), 500, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Ouahigouya'), '2026-02-12 08:00:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Gants médicaux (paire)'), 200, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Gaoua'), '2026-02-13 16:45:00'),
 ((SELECT e.id FROM bn_element e WHERE e.libele = 'Carburant (L)'), 400, (SELECT v.id FROM bn_ville v WHERE v.libele = 'Ouagadougou'), '2026-02-14 11:20:00');

INSERT INTO bn_don (`date`, idVille, description, quantite) VALUES
 ('2026-02-10 10:00:00', (SELECT v.id FROM bn_ville v WHERE v.libele = 'Ouagadougou'), 'Don de riz pour les ménages vulnérables', 200),
 ('2026-02-11 15:00:00', (SELECT v.id FROM bn_ville v WHERE v.libele = 'Koudougou'), 'Don d''huile de cuisine', 80),
 ('2026-02-12 09:00:00', (SELECT v.id FROM bn_ville v WHERE v.libele = 'Ouahigouya'), 'Lot de savon', 300),
 ('2026-02-13 17:00:00', (SELECT v.id FROM bn_ville v WHERE v.libele = 'Gaoua'), 'Gants médicaux pour le centre de santé', 150);

