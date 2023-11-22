const lieu = document.getElementById("lieu");
const rue = document.getElementById("rue");
const codePostal = document.getElementById("codePostal");
const latitude = document.getElementById("latitude");
const longitude = document.getElementById("longitude");

lieu.addEventListener("change", function (e) {
  const selectedOption = e.target.options[e.target.selectedIndex];

  rue.value = selectedOption.dataset.rue;
  codePostal.value = selectedOption.dataset.codepostal;
  latitude.value = selectedOption.dataset.latitude;
  longitude.value = selectedOption.dataset.longitude;
});
