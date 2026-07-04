// ============ Navigation ============
const menuToggle = document.getElementById('menuToggle');
const mobileMenu = document.getElementById('mobileMenu');

menuToggle.addEventListener('click', () => {
  menuToggle.classList.toggle('open');
  mobileMenu.classList.toggle('open');
});

function scrollToSection(id) {
  const el = document.getElementById(id);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
}

document.querySelectorAll('[data-target]').forEach((el) => {
  el.addEventListener('click', (e) => {
    e.preventDefault();
    const target = el.getAttribute('data-target');
    scrollToSection(target);
    menuToggle.classList.remove('open');
    mobileMenu.classList.remove('open');
  });
});

// ============ Icons for skills ============
const icons = {
  code: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
  palette: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 0 0 20c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.2 0-1.1.9-2 2-2h2.3A2.2 2.2 0 0 0 19.5 13c0-6.1-4.9-11-11.5-11z"/></svg>',
  zap: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h7l-1 8 11-14h-7l0-6z"/></svg>',
  database: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg>',
};

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

// ============ Load Skills from API ============
const skillsGrid = document.getElementById('skillsGrid');

async function loadSkills() {
  try {
    const res = await fetch('/api/skills');
    const skills = await res.json();
    skillsGrid.innerHTML = '';
    skills.forEach((skill, idx) => {
      const card = document.createElement('div');
      card.className = 'skill-card fade-up';
      card.style.animationDelay = `${idx * 0.05}s`;
      card.innerHTML = `
        <div class="skill-icon">${icons[skill.icon] || icons.code}</div>
        <h3 class="skill-name">${escapeHtml(skill.name)}</h3>
        <p class="skill-category">${escapeHtml(skill.category)}</p>
      `;
      skillsGrid.appendChild(card);
    });
    observeFadeUps();
  } catch (err) {
    skillsGrid.innerHTML = '<p class="loading-text">โหลดทักษะไม่สำเร็จ</p>';
  }
}

// ============ Load Projects from API ============
const projectsGrid = document.getElementById('projectsGrid');

async function loadProjects() {
  try {
    const res = await fetch('/api/projects');
    const projects = await res.json();
    projectsGrid.innerHTML = '';
    projects.forEach((project, idx) => {
      const card = document.createElement('div');
      card.className = 'project-card fade-up';
      card.style.animationDelay = `${idx * 0.1}s`;
      const tags = (project.tags || [])
        .map((t) => `<span class="tag small">${escapeHtml(t)}</span>`)
        .join('');
      card.innerHTML = `
        <div class="project-image-wrap">
          <img src="${escapeHtml(project.image)}" alt="${escapeHtml(project.title)}" class="project-image" loading="lazy">
        </div>
        <div class="project-body">
          <h3 class="project-title">${escapeHtml(project.title)}</h3>
          <p class="project-desc">${escapeHtml(project.description)}</p>
          <div class="tags">${tags}</div>
          <a href="${escapeHtml(project.link || '#')}" class="project-link">
            ดูแอป
            <svg class="icon" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>
      `;
      projectsGrid.appendChild(card);
    });
    observeFadeUps();
  } catch (err) {
    projectsGrid.innerHTML = '<p class="loading-text">โหลดผลงานไม่สำเร็จ</p>';
  }
}

// ============ Contact Form ============
const contactForm = document.getElementById('contactForm');
const formStatus = document.getElementById('formStatus');

contactForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(contactForm);
  const payload = {
    name: formData.get('name'),
    email: formData.get('email'),
    message: formData.get('message'),
  };

  formStatus.textContent = 'กำลังส่ง...';
  try {
    const res = await fetch('/api/contact', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    if (!res.ok) throw new Error('send failed');
    formStatus.textContent = 'ส่งข้อความเรียบร้อยแล้ว ขอบคุณครับ!';
    contactForm.reset();
  } catch (err) {
    formStatus.textContent = 'ส่งข้อความไม่สำเร็จ กรุณาลองใหม่อีกครั้ง';
  }
  setTimeout(() => { formStatus.textContent = ''; }, 4000);
});

// ============ Scroll-triggered fade-up ============
let fadeObserver;
function observeFadeUps() {
  if (!fadeObserver) {
    fadeObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.animationPlayState = 'running';
          fadeObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
  }
  document.querySelectorAll('.fade-up').forEach((el) => {
    fadeObserver.observe(el);
  });
}

// ============ Init ============
loadSkills();
loadProjects();
observeFadeUps();
