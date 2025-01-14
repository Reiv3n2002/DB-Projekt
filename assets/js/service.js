document.addEventListener("DOMContentLoaded", function () {
  // Modal öffnen
  document.querySelectorAll("[data-modal]").forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();
      const modalId = button.getAttribute("data-modal");
      const modal = document.getElementById(modalId);
      modal.classList.add("show");
      document.body.style.overflow = "hidden"; // Verhindert Scrollen im Hintergrund
    });
  });

  // Modal schließen
  document.querySelectorAll(".close-modal").forEach((closeButton) => {
    closeButton.addEventListener("click", () => {
      closeButton.closest(".modal").classList.remove("show");
      document.body.style.overflow = ""; // Scrollen wieder erlauben
    });
  });

  // Modal schließen wenn außerhalb geklickt wird
  document.querySelectorAll(".modal").forEach((modal) => {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.remove("show");
        document.body.style.overflow = "";
      }
    });
  });

  // ESC Taste zum Schließen
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      document.querySelectorAll(".modal.show").forEach((modal) => {
        modal.classList.remove("show");
        document.body.style.overflow = "";
      });
    }
  });

  // Accordion Funktionalität
  document.querySelectorAll(".accordion-header").forEach((header) => {
    header.addEventListener("click", () => {
      toggleAccordion(header);
    });
  });
});

function toggleAccordion(element) {
  const content = element.querySelector(".accordion-content");
  const parent = element.closest(".accordion-item");
  const accordionType = element.closest(
    ".prices-accordion, .standard-accordion, .support-accordion"
  );

  // Alle anderen Accordions in der gleichen Gruppe schließen
  const siblings = accordionType.querySelectorAll(".accordion-item.active");
  siblings.forEach((sibling) => {
    if (sibling !== parent) {
      sibling.classList.remove("active");
    }
  });

  // Toggle aktiven Status
  parent.classList.toggle("active");
}
