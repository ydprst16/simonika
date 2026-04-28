/* ======================
           SWIPER
        ====================== */
const swiper = new Swiper(".mySwiper", {
  slidesPerView: 1,
  spaceBetween: 20,
  loop: true,
  autoplay: {
    delay: 3000,
    disableOnInteraction: false,
  },
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },
  breakpoints: {
    640: { slidesPerView: 2 },
    1024: { slidesPerView: 3 },
  },
});

/* ======================
           NAVBAR (MOBILE)
        ====================== */
const menuBtn = document.getElementById("menuBtn");
const mobileMenu = document.getElementById("mobileMenu");

menuBtn.onclick = () => {
  mobileMenu.classList.toggle("hidden");
};

/* ======================
           MODAL
        ====================== */
const modal = document.getElementById("modal");
const modalImg = document.getElementById("modalImg");

function openModal(src, title) {
  modalImg.src = src;
  document.getElementById("modalCaption").innerText = title;

  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function closeModal() {
  modal.classList.add("hidden");
  modal.classList.remove("flex");
}

// ESC close
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeModal();
});

// klik background close
if (modal) {
  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });
}

/* ======================
           COUNTER
        ====================== */
const statSection = document.querySelector("#statistik");
const counters = statSection.querySelectorAll(".counter");

function runCounter(counter) {
  const target = +counter.getAttribute("data-target");
  const hasPlus = counter.getAttribute("data-plus") === "true";

  let count = 0;
  const increment = Math.max(1, target / 100);

  const update = () => {
    count += increment;

    if (count < target) {
      counter.innerText = Math.ceil(count);
      requestAnimationFrame(update);
    } else {
      counter.innerText = hasPlus ? target + "+" : target;
    }
  };

  update();
}

/* ======================
           OBSERVER (FADE + COUNTER)
        ====================== */
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      // fade animation
      if (entry.isIntersecting) {
        entry.target.classList.add("show");
      }

      // counter trigger
      if (entry.target.id === "statistik" && entry.isIntersecting) {
        counters.forEach((counter) => {
          if (!counter.classList.contains("done")) {
            runCounter(counter);
            counter.classList.add("done");
          }
        });
      }
    });
  },
  { threshold: 0.5 },
);

// observe semua elemen
document.querySelectorAll(".fade").forEach((el) => observer.observe(el));
observer.observe(statSection);
