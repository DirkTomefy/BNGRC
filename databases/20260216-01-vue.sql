-- Vue pour relier les villes et les besoins avec calcul des montants
CREATE OR REPLACE VIEW vue_ville_besoins AS
SELECT 
    v.id as ville_id,
    v.libele as ville_libele,
    r.id as region_id,
    r.libele as region_libele,
    b.id as besoin_id,
    b.quantite,
    b.date as besoin_date,
    e.id as element_id,
    e.libele as element_libele,
    e.pu as element_pu,
    tb.id as type_besoin_id,
    tb.libele as type_besoin_libele,
    (b.quantite * e.pu) as montant_total
FROM bn_ville v
LEFT JOIN bn_region r ON v.idRegion = r.id
LEFT JOIN bn_besoin b ON v.id = b.idVille
LEFT JOIN bn_element e ON b.idelement = e.id
LEFT JOIN bn_typeBesoin tb ON e.idtypebesoin = tb.id;

-- Vue pour relier les villes et les dons
CREATE OR REPLACE VIEW vue_ville_dons AS
SELECT 
    v.id as ville_id,
    v.libele as ville_libele,
    r.id as region_id,
    r.libele as region_libele,
    d.id as don_id,
    d.date as don_date,
    d.description,
    d.quantite as don_quantite
FROM bn_ville v
LEFT JOIN bn_region r ON v.idRegion = r.id
LEFT JOIN bn_don d ON v.id = d.idVille;
