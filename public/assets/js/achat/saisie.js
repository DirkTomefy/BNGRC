// Saisie des achats - calcul temps réel
document.addEventListener('DOMContentLoaded', function () {
    const selectBesoin = document.getElementById('besoin');
    const selectFiltreVille = document.getElementById('filtreVille');
    const inputQuantite = document.getElementById('quantite');
    const besoinInfo = document.getElementById('besoinInfo');
    const tableBesoins = document.getElementById('tableBesoins');
    
    // Éléments d'info
    const infoElement = document.getElementById('infoElement');
    const infoType = document.getElementById('infoType');
    const infoVille = document.getElementById('infoVille');
    const infoQteRestante = document.getElementById('infoQteRestante');
    const infoPu = document.getElementById('infoPu');
    const maxQte = document.getElementById('maxQte');
    
    // Éléments de calcul
    const montantHT = document.getElementById('montantHT');
    const montantFrais = document.getElementById('montantFrais');
    const montantTTC = document.getElementById('montantTTC');
    
    // Taux de frais (récupéré depuis l'input de configuration)
    const tauxFraisInput = document.getElementById('tauxFraisInput');
    const labelTaux = document.getElementById('labelTaux');
    let tauxFrais = parseFloat(tauxFraisInput ? tauxFraisInput.value : 10) || 10;

    // Mettre à jour le calcul quand le taux change
    if (tauxFraisInput) {
        tauxFraisInput.addEventListener('input', function () {
            tauxFrais = parseFloat(this.value) || 0;
            if (labelTaux) labelTaux.textContent = tauxFrais;
            updateCalcul();
        });
    }

    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    // Filtre des besoins par ville
    selectFiltreVille.addEventListener('change', function () {
        const villeId = this.value;
        
        // Filtrer le tableau
        if (tableBesoins) {
            const rows = tableBesoins.querySelectorAll('tr');
            rows.forEach(row => {
                if (villeId === '' || row.dataset.ville === villeId) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Filtrer le select des besoins
        const options = selectBesoin.querySelectorAll('option');
        options.forEach(option => {
            if (option.value === '' || villeId === '' || option.dataset.ville === villeId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset la sélection
        selectBesoin.value = '';
        besoinInfo.classList.add('d-none');
        inputQuantite.value = '';
        updateCalcul();
    });

    // Afficher les infos du besoin sélectionné
    selectBesoin.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        
        if (this.value && selected.dataset) {
            infoElement.textContent = selected.dataset.element || '-';
            infoType.textContent = selected.dataset.type || '-';
            infoVille.textContent = selected.dataset.villeLibele || '-';
            infoQteRestante.textContent = formatNumber(selected.dataset.qteRestante || 0);
            infoPu.textContent = formatNumber(selected.dataset.pu || 0);
            maxQte.textContent = formatNumber(selected.dataset.qteRestante || 0);
            
            inputQuantite.max = selected.dataset.qteRestante || 0;
            
            besoinInfo.classList.remove('d-none');
        } else {
            besoinInfo.classList.add('d-none');
            maxQte.textContent = '-';
        }
        
        updateCalcul();
    });

    // Calculer le montant
    inputQuantite.addEventListener('input', updateCalcul);
    inputQuantite.addEventListener('change', updateCalcul);

    function updateCalcul() {
        const selected = selectBesoin.options[selectBesoin.selectedIndex];
        const pu = selected && selected.dataset ? parseFloat(selected.dataset.pu) || 0 : 0;
        const qte = parseInt(inputQuantite.value, 10) || 0;
        
        const ht = pu * qte;
        const frais = ht * (tauxFrais / 100);
        const ttc = ht + frais;
        
        montantHT.textContent = formatNumber(ht);
        montantFrais.textContent = formatNumber(frais);
        montantTTC.textContent = formatNumber(ttc);
    }

    // Validation du formulaire
    document.getElementById('formAchat').addEventListener('submit', function (e) {
        const selected = selectBesoin.options[selectBesoin.selectedIndex];
        const maxQteVal = parseInt(selected.dataset.qteRestante || 0, 10);
        const qte = parseInt(inputQuantite.value, 10) || 0;
        
        if (qte > maxQteVal) {
            e.preventDefault();
            alert('La quantité ne peut pas dépasser ' + maxQteVal);
            return false;
        }
    });
});
