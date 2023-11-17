const ville = document.getElementById("ville");
const lieu = document.getElementById("lieu");

const rue = document.getElementById("rue");
const codePostal = document.getElementById("codePostal");
const latitude = document.getElementById("latitude");
const longitude = document.getElementById("longitude");

let donnee;

const TraiterSelectionLieux = (selectedVilleId) => {
  fetch(`/ville/${selectedVilleId}/lieux`)
    .then((response) => response.json())
    .then((data) => {
      // Supprimer les anciennes options
      lieu.innerHTML = "";
      donnee = data;
      // Ajouter les nouvelles options
      donnee.forEach((lieuItem) => {
        const option = document.createElement("option");
        option.value = lieuItem.id;
        option.textContent = lieuItem.nom;
        lieu.appendChild(option);
      });
    })
    .catch((error) => console.error("Erreur :", error))
    .finally(() => {
      if (donnee[0]) {
        TraiterInfosLieux(donnee[0].id);
      } else {
        rue.value = "";
        codePostal.value = "";
        latitude.value = "";
        longitude.value = "";
      }
    });
};

const TraiterInfosLieux = (selectedLieuId) => {
  // Trouver les données du lieu sélectionné
  const selectedLieu = donnee.find(
    (lieu) => lieu.id === parseInt(selectedLieuId)
  );

  // Mettre à jour les champs input avec les données du lieu sélectionné
  rue.value = selectedLieu.rue;
  codePostal.value = selectedLieu.codePostal;
  latitude.value = selectedLieu.latitude;
  longitude.value = selectedLieu.longitude;
};

//Initialisation
TraiterSelectionLieux(ville[0].value);

//Traiter les choix de l'utilisateur
ville.addEventListener("change", function (e) {
  const selectedVilleId = e.target.value;

  TraiterSelectionLieux(selectedVilleId);
});

lieu.addEventListener("change", function (e) {
  const selectedLieuId = e.target.value;

  TraiterInfosLieux(selectedLieuId);
});
