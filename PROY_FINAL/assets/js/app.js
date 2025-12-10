(() => {
    const links = document.querySelectorAll('nav a');
    links.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const target = document.querySelector(link.getAttribute('href'));
            target?.scrollIntoView({ behavior: 'smooth' });
        });
    });

    const forms = document.querySelectorAll('form');
    forms.forEach((form) => {
        form.addEventListener('submit', () => {
            form.classList.add('loading');
        });
    });
})();