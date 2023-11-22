const buttons = document.querySelectorAll(".edit-btn");

buttons.forEach(function (button) {
  let isSecondTime = false;

  button.addEventListener("click", function (e) {
    const id = e.target.dataset.formId;

    const inputs = document.querySelectorAll(".input" + id);
    const spans = document.querySelectorAll(".span" + id);

    inputs.forEach(function (input) {
      input.classList.remove("hidden-form");
    });

    spans.forEach(function (span) {
      span.classList.add("hidden-form");
    });

    buttons.forEach(function (button) {
      if (button.dataset.formId != id) {
        button.classList.add("disabled-btn");
      }
    });

    if (isSecondTime) {
      button.type = "submit";
    } else {
      isSecondTime = true;
    }
  });
});
