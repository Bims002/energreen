/**
 * Script pour la barre de progression et les animations du formulaire carbone
 */

(function() {
    'use strict';

    // Configuration
    const sections = ['logement', 'numerique', 'electro', 'alim', 'transports', 'textile'];
    let currentSectionIndex = 0;

    // Éléments DOM
    const progressSteps = document.querySelectorAll('.progress-step');
    const progressFill = document.getElementById('progressFill');
    const accordionButtons = document.querySelectorAll('.accordion-button');
    const form = document.getElementById('carbonForm');

    /**
     * Met à jour la barre de progression
     */
    function updateProgress() {
        const progressPercent = ((currentSectionIndex + 1) / sections.length) * 100;
        
        if (progressFill) {
            progressFill.style.width = progressPercent + '%';
        }

        // Mettre à jour les étapes
        progressSteps.forEach((step, index) => {
            step.classList.remove('active', 'completed');
            
            if (index < currentSectionIndex) {
                step.classList.add('completed');
            } else if (index === currentSectionIndex) {
                step.classList.add('active');
            }
        });
    }

    /**
     * Gère le clic sur une étape de progression
     */
    function handleStepClick(event) {
        const step = event.currentTarget;
        const sectionName = step.dataset.section;
        const sectionIndex = sections.indexOf(sectionName);

        if (sectionIndex !== -1) {
            currentSectionIndex = sectionIndex;
            updateProgress();

            // Ouvrir l'accordéon correspondant
            const targetCollapse = document.getElementById('collapse' + capitalize(sectionName));
            if (targetCollapse) {
                // Fermer tous les accordéons
                document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                    if (collapse !== targetCollapse) {
                        const bsCollapse = bootstrap.Collapse.getInstance(collapse);
                        if (bsCollapse) {
                            bsCollapse.hide();
                        }
                    }
                });

                // Ouvrir l'accordéon ciblé
                const bsCollapse = new bootstrap.Collapse(targetCollapse, {
                    toggle: true
                });

                // Scroll vers l'accordéon
                setTimeout(() => {
                    targetCollapse.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 300);
            }
        }
    }

    /**
     * Gère l'ouverture/fermeture des accordéons
     */
    function handleAccordionToggle(event) {
        const button = event.currentTarget;
        const targetId = button.getAttribute('data-bs-target');
        
        // Extraire le nom de la section depuis l'ID
        const sectionName = targetId.replace('#collapse', '').toLowerCase();
        const sectionIndex = sections.findIndex(s => sectionName.includes(s));

        if (sectionIndex !== -1 && !button.classList.contains('collapsed')) {
            currentSectionIndex = sectionIndex;
            updateProgress();
        }
    }

    /**
     * Capitalise la première lettre
     */
    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Validation en temps réel des champs
     */
    function validateField(field) {
        if (field.hasAttribute('required') && !field.value) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else if (field.value) {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        } else {
            field.classList.remove('is-valid', 'is-invalid');
        }
    }

    /**
     * Sauvegarde automatique dans localStorage
     */
    function saveFormData() {
        if (!form) return;

        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        try {
            localStorage.setItem('carbonFormData', JSON.stringify(data));
            console.log('Formulaire sauvegardé automatiquement');
        } catch (e) {
            console.error('Erreur lors de la sauvegarde:', e);
        }
    }

    /**
     * Restaure les données du formulaire depuis localStorage
     */
    function restoreFormData() {
        if (!form) return;

        try {
            const savedData = localStorage.getItem('carbonFormData');
            if (savedData) {
                const data = JSON.parse(savedData);

                for (let [key, value] of Object.entries(data)) {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) {
                        if (field.type === 'checkbox') {
                            field.checked = value === 'on' || value === field.value;
                        } else {
                            field.value = value;
                        }
                    }
                }

                console.log('Données du formulaire restaurées');
            }
        } catch (e) {
            console.error('Erreur lors de la restauration:', e);
        }
    }

    /**
     * Animations au scroll
     */
    function handleScrollAnimations() {
        const elements = document.querySelectorAll('.accordion-item');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1
        });

        elements.forEach(el => {
            el.classList.add('fade-in-on-scroll');
            observer.observe(el);
        });
    }

    /**
     * Initialisation
     */
    function init() {
        // Événements sur les étapes de progression
        progressSteps.forEach(step => {
            step.addEventListener('click', handleStepClick);
        });

        // Événements sur les accordéons
        accordionButtons.forEach(button => {
            button.addEventListener('click', handleAccordionToggle);
        });

        // Validation en temps réel
        if (form) {
            const fields = form.querySelectorAll('input, select, textarea');
            fields.forEach(field => {
                field.addEventListener('blur', () => validateField(field));
                field.addEventListener('change', () => {
                    validateField(field);
                    saveFormData();
                });
            });

            // Sauvegarde automatique toutes les 30 secondes
            setInterval(saveFormData, 30000);

            // Restaurer les données au chargement
            restoreFormData();

            // Nettoyer localStorage après soumission
            form.addEventListener('submit', () => {
                localStorage.removeItem('carbonFormData');
            });
        }

        // Animations au scroll
        handleScrollAnimations();

        // Initialiser la progression
        updateProgress();
    }

    // Lancer l'initialisation au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
