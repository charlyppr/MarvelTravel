if (window.innerWidth < 800) {
    const pierres = ["rouge", "bleu", "vert", "violet", "orange"];
    pierres.forEach(pierre => {
        const elements = document.getElementsByClassName(`pierre ${pierre}`);
        for (let i = 0; i < elements.length; i++) {
            elements[i].src = "";
            elements[i].alt = "";
        }
    });
}