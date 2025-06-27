// Enhanced JavaScript for better interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Counter Animation
    function animateCounters() {
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current);
                }
            }, 16);
        });
    }

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';

                // Trigger counter animation when stats section is visible
                if (entry.target.querySelector('.counter')) {
                    animateCounters();
                }
            }
        });
    }, observerOptions);

    // Observe all animated elements
    document.querySelectorAll('.animate-fade-in').forEach(el => {
        observer.observe(el);
    });

    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add hover effects to facility items
    document.querySelectorAll('.facility-item').forEach(item => {
        item.addEventListener('mouseover', () => {
            item.style.transform = 'translateY(-10px)';
            item.style.border = '2px solid #2E8B57';
            item.style.boxShadow = '0 15px 30px rgba(102, 126, 234, 0.2)';
        });

        item.addEventListener('mouseout', () => {
            item.style.transform = 'translateY(0)';
            item.style.border = 'none';
            item.style.boxShadow = 'none';
        });
    });
});
