// COUNT UP
document.querySelectorAll(".count").forEach((el) => {
  let target = +el.dataset.target;
  let count = 0;
  let inc = target / 50;
  let update = () => {
    if (count < target) {
      count += inc;
      el.innerText = Math.ceil(count);
      setTimeout(update, 20);
    } else {
      el.innerText = target;
    }
  };
  update();
});

// PROGRESS
const progress = window.dashboardData?.persen || 0;
const bar = document.getElementById("progressBar");
const text = document.querySelector(".progress-text");

let i = 0;
function animate() {
  if (i < progress) {
    i++;
    text.innerText = i + "%";
    bar.style.width = i + "%";
    setTimeout(animate, 15);
  } else {
    text.innerText = progress + "%";
  }
}
animate();

if (progress < 50) bar.classList.add("low");
else if (progress < 80) bar.classList.add("medium");
else bar.classList.add("high");

// SEARCH
document.getElementById("searchBox")?.addEventListener("keyup", function () {
  let v = this.value.toLowerCase();
  document.querySelectorAll(".card-wrapper").forEach((c) => {
    c.style.display = c.innerText.toLowerCase().includes(v) ? "" : "none";
  });
});

// LINK
function updateLinks() {
  document.querySelectorAll(".card").forEach((card) => {
    let s = card.querySelector(".tahun-select");
    let b = card.querySelector(".btn-lihat");
    if (s && b && !s.disabled) {
      b.href = `monograph.php?kelurahan=${encodeURIComponent(s.dataset.kelurahan)}&tahun=${s.value}`;
    }
  });
}
updateLinks();

// FILTER
document.getElementById("globalTahun")?.addEventListener("change", function () {
  let val = this.value;
  document.querySelectorAll(".card-wrapper").forEach((w) => {
    let s = w.querySelector(".tahun-select");
    if (!s || s.disabled) return;
    let found = [...s.options].some((o) => o.value === val);
    w.style.display = !val || found ? "" : "none";
    if (val && found) s.value = val;
  });
  updateLinks();
});
