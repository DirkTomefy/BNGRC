document.addEventListener('DOMContentLoaded', function() {
    // Afficher les informations de l'élément sélectionné
    const elementSelect = document.getElementById('element');
    const elementInfo = document.getElementById('elementInfo');
    const elementType = document.getElementById('elementType');
    const elementPu = document.getElementById('elementPu');
    const quantiteInput = document.getElementById('quantite');
    
    elementSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const type = selectedOption.getAttribute('data-type');
            const pu = selectedOption.getAttribute('data-pu');
            
            elementType.textContent = type || 'Non spécifié';
            elementPu.textContent = pu ? parseFloat(pu).toLocaleString('fr-FR') : '0';
            elementInfo.classList.remove('d-none');
            
            // Calculer le montant total automatiquement
            calculateTotal();
        } else {
            elementInfo.classList.add('d-none');
        }
    });
    
    // Calculer le montant total
    quantiteInput.addEventListener('input', calculateTotal);
    
    function calculateTotal() {
        const selectedOption = elementSelect.options[elementSelect.selectedIndex];
        const pu = parseFloat(selectedOption.getAttribute('data-pu') || 0);
        const quantite = parseInt(quantiteInput.value) || 0;
        
        if (pu > 0 && quantite > 0) {
            const total = pu * quantite;
            console.log('Montant total:', total.toLocaleString('fr-FR') + ' Ar');
            
            // Optionnel: afficher le total dans l'interface
            updateTotalDisplay(total);
        }
    }
    
    function updateTotalDisplay(total) {
        // Créer ou mettre à jour l'affichage du total
        let totalDisplay = document.getElementById('totalDisplay');
        if (!totalDisplay) {
            totalDisplay = document.createElement('div');
            totalDisplay.id = 'totalDisplay';
            totalDisplay.className = 'alert alert-info mt-3';
            quantiteInput.parentNode.appendChild(totalDisplay);
        }
        
        totalDisplay.innerHTML = `
            <i class="bi bi-calculator me-2"></i>
            <strong>Montant total:</strong> ${total.toLocaleString('fr-FR')} Ar
        `;
    }
    
    // Animation des cartes au chargement
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Validation en temps réel
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input[required], select[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
    
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Ce champ est obligatoire';
        } else if (field.type === 'number' && value) {
            const numValue = parseInt(value);
            if (numValue <= 0) {
                isValid = false;
                errorMessage = 'La valeur doit être supérieure à 0';
            }
        }
        
        // Afficher/masquer les messages d'erreur
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            feedback.style.display = 'none';
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            feedback.textContent = errorMessage;
            feedback.style.display = 'block';
        }
        
        return isValid;
    }
    
    // Validation du formulaire avant soumission
    form.addEventListener('submit', function(e) {
        let isFormValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });
        
        if (!isFormValid) {
            e.preventDefault();
            
            // Afficher un message d'erreur général
            let generalError = document.getElementById('generalError');
            if (!generalError) {
                generalError = document.createElement('div');
                generalError.id = 'generalError';
                generalError.className = 'alert alert-danger alert-custom mb-4';
                generalError.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Veuillez corriger les erreurs dans le formulaire.
                `;
                form.parentNode.insertBefore(generalError, form);
            }
            
            // Faire défiler vers la première erreur
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
});
