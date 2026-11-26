document.addEventListener("DOMContentLoaded", function () {
    const card = document.querySelector(".maintenance-card");
    if (card) {
        requestAnimationFrame(function () {
            card.classList.add("is-visible");
        });
    }

    const logo = document.getElementById("maintenanceLogo");
    if (logo) {
        const fallbacks = JSON.parse(logo.dataset.fallbacks || "[]");
        let idx = 0;

        logo.addEventListener("error", function () {
            if (idx < fallbacks.length) {
                logo.src = fallbacks[idx];
                idx += 1;
            } else {
                logo.style.display = "none";
            }
        });
    }
});