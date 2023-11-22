document.addEventListener("DOMContentLoaded", function () {
    var editableCampuses = document.querySelectorAll('.editable-campus');

    editableCampuses.forEach(function (editableCampus) {
        var editableContent = editableCampus.querySelector('.editable-content');
        var hiddenForm = editableCampus.querySelector('.hidden-form');

        // Masquer le formulaire de modification au chargement de la page
        hiddenForm.style.display = 'none';

        editableContent.addEventListener('click', function () {
            // Inverser l'affichage du contenu statique et du formulaire de modification
            editableContent.style.display = 'none';
            hiddenForm.style.display = 'inline-block';

            // Mettre le focus sur le champ de saisie pour faciliter l'édition
            hiddenForm.querySelector('input').focus();
        });
    });
});
