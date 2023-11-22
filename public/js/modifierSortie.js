const lieu = document.getElementById("lieu");
const rue = document.getElementById("rue");
const codePostal = document.getElementById("codePostal");
const latitude = document.getElementById("latitude");
const longitude = document.getElementById("longitude");

let donnee;

const TraiterSelectionLieux = (selectedLieuId) => {
    fetch(`/sortie/modifier/${selectedLieuId}`)
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

const RemplirListeLieux = () => {
    fetch(`/sortie/modifier`)
        .then((response) => response.json())
        .then((data) => {
            // Remplir le sélecteur de lieux avec les options
            data.forEach((lieuItem) => {
                const option = document.createElement("option");
                option.value = lieuItem.id;
                option.textContent = lieuItem.nom;
                lieu.appendChild(option);
            });
        })
        .catch((error) => console.error("Erreur :", error));
};

fetch(`/lieu/1`) // Remplacez "1" par l'ID du lieu par défaut
    .then((response) => response.json())
    .then((data) => {
        rue.value = data.rue;
        codePostal.value = data.codePostal;
        latitude.value = data.latitude;
        longitude.value = data.longitude;
    })
    .catch((error) => console.error("Erreur :", error));

RemplirListeLieux();

lieu.addEventListener("change", function (e) {
    const selectedLieuId = e.target.value;
    TraiterSelectionLieux(selectedLieuId);
});
