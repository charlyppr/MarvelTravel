const pierres = document.querySelectorAll(".pierre");

document.addEventListener("mousemove", function (event) {
  mouseX = event.clientX;
  mouseY = event.clientY;

  const text = document.querySelector(".hero-text h1");
  const rect = text.getBoundingClientRect();
  const mouseTextX = mouseX - rect.left;
  const mouseTextY = mouseY - rect.top;

  let shadowX = (mouseTextX / rect.width) * 20;
  let shadowY = (mouseTextY / rect.height) * 20;

  const maxShadow = 30;
  shadowX = Math.min(Math.max(shadowX, -maxShadow), maxShadow);
  shadowY = Math.min(Math.max(shadowY, -maxShadow), maxShadow);

  text.style.textShadow = `${-shadowX / 8}px  ${
    -shadowY / 10
  }px 10px var(--couleur-secondaire),
                           ${-shadowX - 20}px ${
    -shadowY + 0
  }px 60px rgba(238, 102, 102, 0.4),
                           ${-shadowX - 0}px ${
    -shadowY + 20
  }px 80px rgba(141, 254, 2, 0.4)`;
});

let mouseX = 0;
let mouseY = 0;
let rotationAngle = 0;
let incrementing = true;

function rotatePierres() {
  pierres.forEach(function (pierre) {
    const pierreRect = pierre.getBoundingClientRect();
    const pierreX = pierreRect.left + pierreRect.width / 2;
    const pierreY = pierreRect.top + pierreRect.height / 2;

    const distanceX = mouseX - pierreX;
    const distanceY = mouseY - pierreY;

    const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);

    const moveFactor = distance / 20;

    const deltaX = distanceX / (5 + moveFactor);
    const deltaY = distanceY / (5 + moveFactor);

    if (incrementing) {
      rotationAngle += 0.03;
      if (rotationAngle >= 1000) {
        incrementing = false;
      }
    } else {
      rotationAngle -= 0.03;
      if (rotationAngle <= 0) {
        incrementing = true;
      }
    }

    pierre.style.transform = `translate(${deltaX}px, ${deltaY}px) rotate(${rotationAngle}deg)`;
  });

  requestAnimationFrame(rotatePierres);
}

rotatePierres();

const cards = document.querySelectorAll(".card");

cards.forEach((card) => {
  card.addEventListener("mousemove", (e) => {
    let rect = card.getBoundingClientRect();
    let x = e.clientX - rect.left;
    let y = e.clientY - rect.top;

    let centerX = rect.width / 2;
    let centerY = rect.height / 2;

    let rotateX = (y - centerY) / 20;
    let rotateY = (centerX - x) / 20;

    let shadowX = (centerX - x) / 25;
    let shadowY = (centerY - y) / 25;

    card.style.transition = `transform 0.2s linear`;
    card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    card.style.boxShadow = `${-shadowX}px ${-shadowY}px 20px #f2dbaf30`;
  });

  card.addEventListener("mouseleave", () => {
    card.style.transform = `rotateX(0deg) rotateY(0deg)`;
    card.style.transition = `transform 0.2s linear, box-shadow 1s ease-in-out`;
    setTimeout(() => {
      card.style.boxShadow = `0px 0px 20px #f2dbaf30`;
    }, 100);
  });
});
