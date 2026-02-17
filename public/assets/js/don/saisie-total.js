// Aperçu du total estimé pour le don (élément * quantité)
document.addEventListener('DOMContentLoaded', function () {
    const selectElement = document.getElementById('element');
    const inputQuantite = document.getElementById('quantite');
    const totalMontant = document.getElementById('totalMontant');
    const apercuTotal = document.getElementById('apercuTotal');

    // Vérifier que les éléments existent
    if (!selectElement || !inputQuantite || !totalMontant) {
        console.warn('Éléments du formulaire non trouvés');
        return;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function updateTotal() {
        const selected = selectElement.options[selectElement.selectedIndex];
        const pu = selected ? parseFloat(selected.getAttribute('data-pu')) || 0 : 0;
        const qte = parseInt(inputQuantite.value, 10) || 0;
        const total = pu * qte;

        totalMontant.textContent = total > 0 ? formatNumber(total) : '0';

        // Changer le style selon le montant
        if (apercuTotal) {
            if (total > 0) {
                apercuTotal.classList.remove('alert-secondary');
                apercuTotal.classList.add('alert-success');
            } else {
                apercuTotal.classList.remove('alert-success');
                apercuTotal.classList.add('alert-secondary');
            }
        }
    }

    selectElement.addEventListener('change', updateTotal);
    inputQuantite.addEventListener('input', updateTotal);
    inputQuantite.addEventListener('change', updateTotal);

    // Initialisation si valeurs déjà présentes
    updateTotal();
});
