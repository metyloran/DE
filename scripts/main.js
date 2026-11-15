(() => {
  const slides = [...document.querySelectorAll('.slide')];
  const dots = [...document.querySelectorAll('.dot')];
  const title = document.getElementById('slideTitle');
  const titles = [
    'Открывай новые возможности',
    'Учись. Развивайся. Достигай.',
    'Твои знания имеют значение',
    'Создавай своё будущее',
    'Пусть старания будут замечены',
    'Время двигаться к новым целям'
  ];
  if (slides.length) {
    let current = 0, timer;
    function show(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach((s, i) => s.classList.toggle('active', i === current));
      dots.forEach((d, i) => d.classList.toggle('active', i === current));
      if (title) title.textContent = titles[current];
    }
    function restart() {
      clearInterval(timer);
      timer = setInterval(() => show(current + 1), 5000);
    }
    document.getElementById('prev')?.addEventListener('click', () => { show(current - 1); restart(); });
    document.getElementById('next')?.addEventListener('click', () => { show(current + 1); restart(); });
    dots.forEach((d, i) => d.addEventListener('click', () => { show(i); restart(); }));
    const slider = document.querySelector('.slider');
    slider?.addEventListener('mouseenter', () => clearInterval(timer));
    slider?.addEventListener('mouseleave', restart);
    show(0); restart();
  }
  const burger = document.getElementById('burger');
  const menu = document.getElementById('menu');
  burger?.addEventListener('click', () => menu.classList.toggle('open'));
})();
