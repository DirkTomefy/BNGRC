CREATE OR REPLACE VIEW vue_ville_recap AS
SELECT 
    v.ville_id,
    v.ville_libele,
    v.region_id,
    v.region_libele,
    
    -- Détail de l'élément
    v.element_id,
    v.element_libele,
    v.element_pu,
    v.type_besoin_id,
    v.type_besoin_libele,

    -- Besoins
    COALESCE(v.quantite, 0) AS quantite_besoin,
    COALESCE(v.montant_total, 0) AS montant_besoin,

    -- Dons correspondants
    COALESCE(SUM(d.don_quantite), 0) AS quantite_donnee,

    -- Calcul du reste
    (COALESCE(v.quantite, 0) - COALESCE(SUM(d.don_quantite), 0)) AS quantite_restante,
    ((COALESCE(v.quantite, 0) - COALESCE(SUM(d.don_quantite), 0)) * COALESCE(v.element_pu, 0)) AS montant_restant

FROM vue_ville_besoins v
LEFT JOIN vue_ville_dons d 
    ON v.ville_id = d.ville_id
GROUP BY 
    v.ville_id, 
    v.ville_libele,
    v.region_id,
    v.region_libele,
    v.element_id,
    v.element_libele,
    v.element_pu,
    v.type_besoin_id,
    v.type_besoin_libele,
    v.quantite,
    v.montant_total;
