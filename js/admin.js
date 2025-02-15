document.addEventListener('DOMContentLoaded', function () {
    const toggleBlockStatus = (element) => {
        element.classList.toggle('block');
        const img = element.querySelector('img');
        if (element.classList.contains('block')) {
            img.src = '../img/svg/no.svg';
            element.innerHTML = 'Bloqué<img src="../img/svg/no.svg" alt="croix">';
        } else {
            img.src = '../img/svg/check.svg';
            element.innerHTML = 'Non bloqué<img src="../img/svg/check.svg" alt="check">';
        }
    };

    const toggleVipStatus = (element) => {
        element.classList.toggle('novip');
        const img = element.querySelector('img');
        if (element.classList.contains('novip')) {
            img.src = '../img/svg/no.svg';
            element.innerHTML = 'Non<img src="../img/svg/no.svg" alt="croix">';
        } else {
            img.src = '../img/svg/etoile.svg';
            element.innerHTML = 'VIP<img src="../img/svg/etoile.svg" alt="etoile">';
        }
    };

    document.querySelectorAll('.unblock').forEach(element => {
        element.addEventListener('click', () => toggleBlockStatus(element));
    });

    document.querySelectorAll('.vip').forEach(element => {
        element.addEventListener('click', () => toggleVipStatus(element));
    });
});