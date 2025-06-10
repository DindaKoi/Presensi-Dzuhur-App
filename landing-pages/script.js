document.getElementById('menuBtn').addEventListener('click', () => {
  const menu = document.getElementById('mobileMenu');
  menu.classList.toggle('hidden');
});
document.addEventListener('DOMContentLoaded', () => {
  const animatedElements = document.querySelectorAll('[class*="animate-"]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        entry.target.classList.add('opacity-100', 'translate-y-0', 'translate-x-0');
      }
    });
  }, { threshold: 0.2 });

  animatedElements.forEach(el => observer.observe(el));
});
