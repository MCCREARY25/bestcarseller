async function api(action, method = 'GET', data = null) {
    const options = {
        method,
        headers: { 'Content-Type': 'application/json' }
    };
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    try {
        const response = await fetch(`api/${action}`, options);
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        return { error: 'Connection error' };
    }
}

// State
let currentCurrency = localStorage.getItem('currency') || 'USD';
let selectedPaymentMethod = 'credit_card';

// DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    // Header scroll effect
    const header = document.getElementById('header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 10);
        });
    }

    // Mobile menu toggle
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    if (mobileBtn && sidebar) {
        mobileBtn.addEventListener('click', () => sidebar.classList.toggle('active'));
    }

    // Initialize page logic
    const page = document.body.dataset.page;
    if (page === 'home' || page === 'dashboard') {
        loadCarTypes();
        loadFeaturedCars();
        updateCartCount();
    } else if (page === 'cart') {
        loadCart();
    } else if (page === 'admin') {
        loadDashboardStats();
    }

    // Search
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', () => performSearch());
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') performSearch();
        });
    }
});

// Currency Formatting
function formatPrice(price, currency = currentCurrency) {
    const symbols = { USD: '$', EUR: '€', GBP: '£', JPY: '¥', BTC: '₿' };
    return `${symbols[currency] || '$'}${parseFloat(price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

// Load Car Types in Sidebar
async function loadCarTypes() {
    const result = await api('cars.php?action=types');
    const sidebarList = document.getElementById('sidebarTypes');
    if (!sidebarList) return;

    if (result.success) {
        sidebarList.innerHTML = result.types.map(type => `
            <button class="nav-item" onclick="loadCarsByType(${type.id}, '${type.name}')">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                    <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                    <path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5"></path>
                </svg>
                ${type.name}
                <span class="nav-item-count">${type.car_count}</span>
            </button>
        `).join('');
    }
}

// Load Featured Cars
async function loadFeaturedCars() {
    const result = await api(`cars.php?action=featured&currency=${currentCurrency}`);
    const grid = document.getElementById('featuredGrid');
    if (!grid) return;
    if (result.success) grid.innerHTML = result.cars.map(car => renderCarCard(car)).join('');
}

// Load Cars by Type
async function loadCarsByType(typeId, typeName) {
    const result = await api(`cars.php?action=by_type&type_id=${typeId}&currency=${currentCurrency}`);
    const grid = document.getElementById('mainGrid');
    const title = document.getElementById('sectionTitle');
    if (!grid) return;
    
    if (result.success) {
        grid.innerHTML = result.cars.map(car => renderCarCard(car)).join('');
        if (title) title.textContent = `${typeName} Models`;
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Search
async function performSearch() {
    const query = document.getElementById('searchInput').value;
    if (query.length < 2) return;
    const result = await api(`cars.php?action=search&q=${encodeURIComponent(query)}&currency=${currentCurrency}`);
    const grid = document.getElementById('mainGrid');
    const title = document.getElementById('sectionTitle');
    if (result.success) {
        grid.innerHTML = result.cars.length > 0 
            ? result.cars.map(car => renderCarCard(car)).join('')
            : '<div class="empty-state"><h3>No cars found</h3></div>';
        if (title) title.textContent = `Results for "${query}"`;
    }
}

// Render Car Card HTML
function renderCarCard(car) {
    const badgeClass = car.fuel_type === 'Electric' ? 'electric' : (car.featured ? 'featured' : '');
    const badgeText = car.fuel_type === 'Electric' ? 'Electric' : (car.featured ? 'Featured' : car.type_name);
    
    // --- IMAGE FIX LOGIC ---
    // If image exists in DB and is a URL, use it. 
    // Otherwise, generate a dynamic "Car Image" based on the model name so it always looks relevant.
    let imgSrc;
    if (car.image && car.image.startsWith('http')) {
        imgSrc = car.image;
    } else {
        // This service generates a car image based on the model name. Reliable and looks good.
        imgSrc = `https://loremflickr.com/640/480/car,${encodeURIComponent(car.model)}`;
    }

    return `
        <div class="car-card fade-in">
            <div class="car-card-image">
                <img src="${imgSrc}" alt="${car.brand} ${car.model}" 
                     onerror="this.onerror=null; this.src='https://loremflickr.com/640/480/car,${encodeURIComponent(car.brand)}';">
                ${badgeText ? `<span class="car-badge ${badgeClass}">${badgeText}</span>` : ''}
            </div>
            <div class="card-content">
                <div class="car-brand">${car.brand}</div>
                <h3 class="car-model">${car.model}</h3>
                <div class="car-specs">
                    <span class="spec-tag">${car.year}</span>
                    <span class="spec-tag">${car.transmission}</span>
                    <span class="spec-tag">${car.fuel_type}</span>
                </div>
                <p class="car-description">${car.description || 'Premium quality vehicle.'}</p>
                <div class="car-info">
                    <div class="info-item">
                        <div class="info-label">Warranty</div>
                        <div class="info-value">${car.warranty || 'Standard'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Durability</div>
                        <div class="info-value">${car.durability || 'Excellent'}</div>
                    </div>
                </div>
                <div class="car-footer">
                    <div class="car-price">${formatPrice(car.price, car.currency)} <span>USD</span></div>
                    <button class="btn btn-primary btn-sm" onclick="addToCart(${car.id})">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Add to Cart
async function addToCart(carId) {
    const result = await api('cart.php?action=add', 'POST', { car_id: carId, quantity: 1 });
    if (result.success) {
        alert('Car added to cart!');
        updateCartCount();
    } else {
        alert(result.error || 'Please login to add items to cart');
        if (result.error.includes('login')) window.location.href = 'login.php';
    }
}

// Update Cart Count Badge
async function updateCartCount() {
    const result = await api('cart.php?action=get');
    const badge = document.getElementById('cartCount');
    if (badge && result.success) badge.textContent = result.count;
}

// --- CART PAGE LOGIC ---
async function loadCart() {
    const result = await api(`cart.php?action=get&currency=${currentCurrency}`);
    const container = document.getElementById('cartItems');
    const summaryTotal = document.getElementById('summaryTotal');
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    if (!container) return;

    if (result.success && result.items.length > 0) {
        container.innerHTML = result.items.map(item => `
            <div class="cart-item">
                <div class="cart-item-image">
                    <img src="https://loremflickr.com/200/150/${item.brand}" alt="${item.brand}">
                </div>
                <div class="cart-item-details">
                    <div class="cart-item-brand">${item.brand}</div>
                    <div class="cart-item-model">${item.model} (${item.year})</div>
                    <div class="cart-item-price">${formatPrice(item.price)}</div>
                </div>
                <button class="cart-item-remove" onclick="removeFromCart(${item.car_id})">✕</button>
            </div>
        `).join('');
        
        if (summaryTotal) summaryTotal.textContent = formatPrice(result.total);
        if (checkoutBtn) checkoutBtn.disabled = false;
    } else {
        container.innerHTML = `
            <div class="empty-state">
                <h3>Your cart is empty</h3>
                <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">Start Shopping</a>
            </div>
        `;
        if (summaryTotal) summaryTotal.textContent = formatPrice(0);
    }
}

async function removeFromCart(carId) {
    const result = await api(`cart.php?action=remove&car_id=${carId}`, 'DELETE');
    if (result.success) {
        loadCart();
        updateCartCount();
    }
}

// Checkout
function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    document.querySelectorAll('.payment-option').forEach(el => el.classList.toggle('selected', el.dataset.method === method));
}

async function processCheckout() {
    if (!confirm('Place order?')) return;
    const result = await api('cart.php?action=checkout', 'POST', {
        payment_method: selectedPaymentMethod,
        currency: currentCurrency,
        payment_details: {}
    });
    if (result.success) {
        alert(result.message);
        window.location.href = 'dashboard.php';
    } else {
        alert(result.error);
    }
}

// Logout
async function logout() {
    await api('auth.php?action=logout', 'POST');
    window.location.href = 'index.php';
}

// Currency
function changeCurrency(currency) {
    currentCurrency = currency;
    localStorage.setItem('currency', currency);
    document.querySelectorAll('.currency-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.currency === currency));
    if (document.body.dataset.page === 'cart') loadCart();
    else if (document.body.dataset.page === 'home' || document.body.dataset.page === 'dashboard') loadFeaturedCars();
}

// --- ADMIN LOGIC ---
async function loadDashboardStats() {
    const result = await api('admin.php?action=dashboard');
    if (!result.success) return;
    
    const stats = result.stats;
    document.getElementById('statUsers').textContent = stats.total_users;
    document.getElementById('statOrders').textContent = stats.total_orders;
    document.getElementById('statRevenue').textContent = formatPrice(stats.total_revenue);
    document.getElementById('statCars').textContent = stats.total_cars;

    const ordersTable = document.getElementById('ordersTable');
    if (ordersTable && stats.recent_orders) {
        ordersTable.innerHTML = stats.recent_orders.map(order => `
            <tr>
                <td>#${order.id}</td>
                <td>${order.username}</td>
                <td>${formatPrice(order.total_amount)}</td>
                <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                <td>${new Date(order.created_at).toLocaleDateString()}</td>
                <td>
                    ${order.status === 'pending' ? `
                        <button class="btn btn-sm btn-primary" onclick="handleOrder(${order.id}, 'approve')">✓</button>
                        <button class="btn btn-sm btn-danger" onclick="handleOrder(${order.id}, 'reject')">✕</button>
                    ` : '-'}
                </td>
            </tr>
        `).join('');
    }
}

async function handleOrder(orderId, action) {
    const result = await api('admin.php?action=order_action', 'POST', { order_id: orderId, action: action, notes: '' });
    if (result.success) {
        alert(`Order ${result.new_status}!`);
        loadDashboardStats();
    }
}