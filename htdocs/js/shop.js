// ============ Navigation (mobile menu toggle) ============
const menuToggle = document.getElementById('menuToggle');
const mobileMenu = document.getElementById('mobileMenu');

menuToggle.addEventListener('click', () => {
  menuToggle.classList.toggle('open');
  mobileMenu.classList.toggle('open');
});

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

function formatPrice(price) {
  const num = Number(price) || 0;
  return num.toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

// ============ Load Products from API ============
const productsGrid = document.getElementById('productsGrid');

async function loadProducts() {
  try {
    const res = await fetch('/api/products');
    const products = await res.json();
    productsGrid.innerHTML = '';

    if (products.length === 0) {
      productsGrid.innerHTML = '<p class="loading-text">ยังไม่มีสินค้าในร้านค้าตอนนี้</p>';
      return;
    }

    products.forEach((product, idx) => {
      const card = document.createElement('div');
      card.className = 'product-card fade-up';
      card.style.animationDelay = `${idx * 0.1}s`;
      card.innerHTML = `
        <div class="product-image-wrap">
          <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.title)}" class="product-image" loading="lazy">
        </div>
        <div class="product-body">
          <h3 class="product-title">${escapeHtml(product.title)}</h3>
          <p class="product-desc">${escapeHtml(product.description)}</p>
          <div class="product-footer">
            <span class="product-price">฿${formatPrice(product.price)}</span>
            <a href="${escapeHtml(product.downloadLink || '#')}" class="product-download-btn" target="_blank" rel="noopener">
              ดาวน์โหลด
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4M4 21h16"/></svg>
            </a>
          </div>
        </div>
      `;
      productsGrid.appendChild(card);
    });
    observeFadeUps();
  } catch (err) {
    productsGrid.innerHTML = '<p class="loading-text">โหลดสินค้าไม่สำเร็จ</p>';
  }
}

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
loadProducts();
observeFadeUps();
