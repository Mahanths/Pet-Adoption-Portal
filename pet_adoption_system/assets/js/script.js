// script.js

document.addEventListener('DOMContentLoaded', () => {
    // Example: Console log every button click
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', () => {
            // console.log(`Clicked: ${button.textContent.trim()}`);
        });
    });

    // Example: Smooth scroll (if used for anchors)
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
