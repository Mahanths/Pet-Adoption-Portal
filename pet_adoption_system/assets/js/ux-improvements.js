document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Back to Top Button
    const backToTopButton = document.createElement('button');
    backToTopButton.className = 'back-to-top';
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    document.body.appendChild(backToTopButton);

    // Show/hide back to top button
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });

    // Back to top functionality
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add tooltips to pet cards
    // document.querySelectorAll('.pet-card').forEach(card => {
    //     const name = card.querySelector('h3').textContent;
    //     const species = card.querySelector('.pet-meta span:first-child').textContent;
    //     const gender = card.querySelector('.pet-meta span:nth-child(2)').textContent;
    //     const age = card.querySelector('.pet-meta span:last-child').textContent;
        
    //     // Only add tooltip to the card content, not the button
    //     const cardContent = card.querySelector('.pet-info');
    //     cardContent.setAttribute('data-bs-toggle', 'tooltip');
    //     cardContent.setAttribute('data-bs-placement', 'top');
    //     cardContent.setAttribute('title', `${name}\n${species}\n${gender}\n${age}`);
    // });
}); 