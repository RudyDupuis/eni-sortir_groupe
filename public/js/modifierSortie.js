const lieu = document.getElementById("lieu");
const rue = document.getElementById("rue");
const codePostal = document.getElementById("codePostal");
const latitude = document.getElementById("latitude");
const longitude = document.getElementById("longitude");

let donnee;

const TraiterSelectionLieux = (selectedLieuId) => {
    fetch(`/lieu/${selectedLieuId}`)
        .then((response) => response.json())
        .then((data) => {
            // Mettre à jour les champs input avec les données du lieu sélectionné
            rue.value = data.rue;
            codePostal.value = data.codePostal;
            latitude.value = data.latitude;
            longitude.value = data.longitude;
        })
        .catch((error) => console.error("Erreur :", error));
};

//Traiter les choix de l'utilisateur
lieu.addEventListener("change", function (e) {
    const selectedLieuId = e.target.value;

    TraiterSelectionLieux(selectedLieuId);
});
