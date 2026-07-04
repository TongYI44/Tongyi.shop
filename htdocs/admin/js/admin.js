// ============ Elements ============
const loginScreen = document.getElementById('loginScreen');
const dashboard = document.getElementById('dashboard');
const loginForm = document.getElementById('loginForm');
const loginError = document.getElementById('loginError');
const logoutBtn = document.getElementById('logoutBtn');

// ============ Auth ============
async function checkAuth() {
  const res = await fetch('/api/auth/status');
  const data = await res.json();
  if (data.loggedIn) {
    showDashboard();
  } else {
    showLogin();
  }
}

function showLogin() {
  loginScreen.classList.remove('hidden');
  dashboard.classList.add('hidden');
}

function showDashboard() {
  loginScreen.classList.add('hidden');
  dashboard.classList.remove('hidden');
  loadProjects();
  loadSkills();
  loadProducts();
  loadMessages();
}

loginForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  loginError.textContent = '';
  const username = document.getElementById('username').value;
  const password = document.getElementById('password').value;

  try {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password }),
    });
    const data = await res.json();
    if (!res.ok) {
      loginError.textContent = data.error || 'เข้าสู่ระบบไม่สำเร็จ';
      return;
    }
    showDashboard();
  } catch (err) {
    loginError.textContent = 'เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ';
  }
});

logoutBtn.addEventListener('click', async () => {
  await fetch('/api/auth/logout', { method: 'POST' });
  showLogin();
});

// ============ Tabs ============
document.querySelectorAll('.tab-btn').forEach((btn) => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach((b) => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach((p) => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(`tab-${btn.dataset.tab}`).classList.add('active');
  });
});

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

// ============ Projects ============
const projectsList = document.getElementById('projectsList');
const projectModal = document.getElementById('projectModal');
const projectForm = document.getElementById('projectForm');
const projectModalTitle = document.getElementById('projectModalTitle');

document.getElementById('addProjectBtn').addEventListener('click', () => openProjectModal());
document.getElementById('cancelProjectBtn').addEventListener('click', () => closeProjectModal());

function openProjectModal(project) {
  projectForm.reset();
  document.getElementById('projectId').value = project?.id || '';
  document.getElementById('projectTitle').value = project?.title || '';
  document.getElementById('projectDesc').value = project?.description || '';
  document.getElementById('projectImage').value = project?.image || '';
  document.getElementById('projectTags').value = (project?.tags || []).join(', ');
  document.getElementById('projectLink').value = project?.link || '';
  projectModalTitle.textContent = project ? 'แก้ไขผลงาน' : 'เพิ่มผลงาน';
  projectModal.classList.remove('hidden');
}

function closeProjectModal() {
  projectModal.classList.add('hidden');
}

projectForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id = document.getElementById('projectId').value;
  const payload = {
    title: document.getElementById('projectTitle').value,
    description: document.getElementById('projectDesc').value,
    image: document.getElementById('projectImage').value,
    tags: document.getElementById('projectTags').value,
    link: document.getElementById('projectLink').value,
  };

  const url = id ? `/api/projects/${id}` : '/api/projects';
  const method = id ? 'PUT' : 'POST';

  const res = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  if (res.ok) {
    closeProjectModal();
    loadProjects();
  } else {
    const data = await res.json();
    alert(data.error || 'บันทึกไม่สำเร็จ');
  }
});

async function loadProjects() {
  projectsList.innerHTML = '<p class="loading-text">กำลังโหลด...</p>';
  const res = await fetch('/api/projects');
  const projects = await res.json();

  if (projects.length === 0) {
    projectsList.innerHTML = '<p class="loading-text">ยังไม่มีผลงาน กด "+ เพิ่มผลงาน" เพื่อเริ่มต้น</p>';
    return;
  }

  projectsList.innerHTML = '';
  projects.forEach((project) => {
    const card = document.createElement('div');
    card.className = 'item-card';
    const tags = (project.tags || []).map((t) => `<span class="item-tag">${escapeHtml(t)}</span>`).join('');
    card.innerHTML = `
      <div class="item-info">
        <h3>${escapeHtml(project.title)}</h3>
        <p>${escapeHtml(project.description)}</p>
        <div class="item-tags">${tags}</div>
      </div>
      <div class="item-actions">
        <button class="icon-btn" data-action="edit">แก้ไข</button>
        <button class="icon-btn danger" data-action="delete">ลบ</button>
      </div>
    `;
    card.querySelector('[data-action="edit"]').addEventListener('click', () => openProjectModal(project));
    card.querySelector('[data-action="delete"]').addEventListener('click', () => deleteProject(project.id));
    projectsList.appendChild(card);
  });
}

async function deleteProject(id) {
  if (!confirm('ต้องการลบผลงานนี้ใช่ไหม?')) return;
  const res = await fetch(`/api/projects/${id}`, { method: 'DELETE' });
  if (res.ok) loadProjects();
}

// ============ Skills ============
const skillsList = document.getElementById('skillsList');
const skillModal = document.getElementById('skillModal');
const skillForm = document.getElementById('skillForm');
const skillModalTitle = document.getElementById('skillModalTitle');

document.getElementById('addSkillBtn').addEventListener('click', () => openSkillModal());
document.getElementById('cancelSkillBtn').addEventListener('click', () => closeSkillModal());

function openSkillModal(skill) {
  skillForm.reset();
  document.getElementById('skillId').value = skill?.id || '';
  document.getElementById('skillName').value = skill?.name || '';
  document.getElementById('skillCategory').value = skill?.category || '';
  document.getElementById('skillIcon').value = skill?.icon || 'code';
  skillModalTitle.textContent = skill ? 'แก้ไขทักษะ' : 'เพิ่มทักษะ';
  skillModal.classList.remove('hidden');
}

function closeSkillModal() {
  skillModal.classList.add('hidden');
}

skillForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id = document.getElementById('skillId').value;
  const payload = {
    name: document.getElementById('skillName').value,
    category: document.getElementById('skillCategory').value,
    icon: document.getElementById('skillIcon').value,
  };

  const url = id ? `/api/skills/${id}` : '/api/skills';
  const method = id ? 'PUT' : 'POST';

  const res = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  if (res.ok) {
    closeSkillModal();
    loadSkills();
  } else {
    const data = await res.json();
    alert(data.error || 'บันทึกไม่สำเร็จ');
  }
});

async function loadSkills() {
  skillsList.innerHTML = '<p class="loading-text">กำลังโหลด...</p>';
  const res = await fetch('/api/skills');
  const skills = await res.json();

  if (skills.length === 0) {
    skillsList.innerHTML = '<p class="loading-text">ยังไม่มีทักษะ กด "+ เพิ่มทักษะ" เพื่อเริ่มต้น</p>';
    return;
  }

  skillsList.innerHTML = '';
  skills.forEach((skill) => {
    const card = document.createElement('div');
    card.className = 'item-card';
    card.innerHTML = `
      <div class="item-info">
        <h3>${escapeHtml(skill.name)}</h3>
        <p>${escapeHtml(skill.category)}</p>
      </div>
      <div class="item-actions">
        <button class="icon-btn" data-action="edit">แก้ไข</button>
        <button class="icon-btn danger" data-action="delete">ลบ</button>
      </div>
    `;
    card.querySelector('[data-action="edit"]').addEventListener('click', () => openSkillModal(skill));
    card.querySelector('[data-action="delete"]').addEventListener('click', () => deleteSkill(skill.id));
    skillsList.appendChild(card);
  });
}

async function deleteSkill(id) {
  if (!confirm('ต้องการลบทักษะนี้ใช่ไหม?')) return;
  const res = await fetch(`/api/skills/${id}`, { method: 'DELETE' });
  if (res.ok) loadSkills();
}

// ============ Products (ร้านค้า) ============
const productsList = document.getElementById('productsList');
const productModal = document.getElementById('productModal');
const productForm = document.getElementById('productForm');
const productModalTitle = document.getElementById('productModalTitle');

document.getElementById('addProductBtn').addEventListener('click', () => openProductModal());
document.getElementById('cancelProductBtn').addEventListener('click', () => closeProductModal());

function openProductModal(product) {
  productForm.reset();
  document.getElementById('productId').value = product?.id || '';
  document.getElementById('productTitle').value = product?.title || '';
  document.getElementById('productDesc').value = product?.description || '';
  document.getElementById('productImage').value = product?.image || '';
  document.getElementById('productPrice').value = product?.price ?? '';
  document.getElementById('productDownloadLink').value = product?.downloadLink || '';
  productModalTitle.textContent = product ? 'แก้ไขสินค้า' : 'เพิ่มสินค้า';
  productModal.classList.remove('hidden');
}

function closeProductModal() {
  productModal.classList.add('hidden');
}

productForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id = document.getElementById('productId').value;
  const payload = {
    title: document.getElementById('productTitle').value,
    description: document.getElementById('productDesc').value,
    image: document.getElementById('productImage').value,
    price: parseFloat(document.getElementById('productPrice').value) || 0,
    downloadLink: document.getElementById('productDownloadLink').value,
  };

  const url = id ? `/api/products/${id}` : '/api/products';
  const method = id ? 'PUT' : 'POST';

  const res = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  if (res.ok) {
    closeProductModal();
    loadProducts();
  } else {
    const data = await res.json();
    alert(data.error || 'บันทึกไม่สำเร็จ');
  }
});

function formatPrice(price) {
  const num = Number(price) || 0;
  return num.toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

async function loadProducts() {
  productsList.innerHTML = '<p class="loading-text">กำลังโหลด...</p>';
  const res = await fetch('/api/products');
  const products = await res.json();

  if (products.length === 0) {
    productsList.innerHTML = '<p class="loading-text">ยังไม่มีสินค้า กด "+ เพิ่มสินค้า" เพื่อเริ่มต้น</p>';
    return;
  }

  productsList.innerHTML = '';
  products.forEach((product) => {
    const card = document.createElement('div');
    card.className = 'item-card';
    card.innerHTML = `
      <div class="item-info">
        <h3>${escapeHtml(product.title)}</h3>
        <p>${escapeHtml(product.description)}</p>
        <p><strong>฿${formatPrice(product.price)}</strong></p>
      </div>
      <div class="item-actions">
        <button class="icon-btn" data-action="edit">แก้ไข</button>
        <button class="icon-btn danger" data-action="delete">ลบ</button>
      </div>
    `;
    card.querySelector('[data-action="edit"]').addEventListener('click', () => openProductModal(product));
    card.querySelector('[data-action="delete"]').addEventListener('click', () => deleteProduct(product.id));
    productsList.appendChild(card);
  });
}

async function deleteProduct(id) {
  if (!confirm('ต้องการลบสินค้านี้ใช่ไหม?')) return;
  const res = await fetch(`/api/products/${id}`, { method: 'DELETE' });
  if (res.ok) loadProducts();
}

// ============ Messages ============
const messagesList = document.getElementById('messagesList');

async function loadMessages() {
  messagesList.innerHTML = '<p class="loading-text">กำลังโหลด...</p>';
  const res = await fetch('/api/messages');
  if (!res.ok) {
    messagesList.innerHTML = '<p class="loading-text">โหลดข้อความไม่สำเร็จ</p>';
    return;
  }
  const messages = await res.json();

  if (messages.length === 0) {
    messagesList.innerHTML = '<p class="loading-text">ยังไม่มีข้อความติดต่อเข้ามา</p>';
    return;
  }

  messagesList.innerHTML = '';
  messages.forEach((msg) => {
    const card = document.createElement('div');
    card.className = 'item-card message-card';
    const date = new Date(msg.receivedAt).toLocaleString('th-TH');
    card.innerHTML = `
      <div class="message-meta">
        <span>${escapeHtml(msg.name)} • ${escapeHtml(msg.email)}</span>
        <span>${date}</span>
      </div>
      <p>${escapeHtml(msg.message)}</p>
    `;
    messagesList.appendChild(card);
  });
}

// ============ Init ============
checkAuth();
