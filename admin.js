// Utility function to format numbers with leading zeros
const formatNumber = (num, length = 5) => String(num).padStart(length, '0');

// Initialize admin panel
const initAdmin = () => {
    try {
        const existingData = localStorage.getItem('raffleItems');
        const existingBanner = localStorage.getItem('bannerData');
        const existingLogo = localStorage.getItem('logoData');
        const existingConfig = localStorage.getItem('systemConfig');
        
        if (!existingData) {
            localStorage.setItem('raffleItems', JSON.stringify([]));
        }
        
        if (!existingBanner) {
            const defaultBanner = {
                image: 'https://images.unsplash.com/photo-1522542550221-31fd19575a2d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1000&q=80',
                title: '¡Grandes Premios te Esperan!'
            };
            localStorage.setItem('bannerData', JSON.stringify(defaultBanner));
        }

        if (!existingLogo) {
            const defaultLogo = {
                image: 'https://via.placeholder.com/200x100?text=Logo'
            };
            localStorage.setItem('logoData', JSON.stringify(defaultLogo));
        }

        if (!existingConfig) {
            const defaultConfig = {
                countryCode: '+57',
                whatsappNumber: '3229009051',
                autoReleaseEnabled: false,
                autoReleaseMinutes: 10
            };
            localStorage.setItem('systemConfig', JSON.stringify(defaultConfig));
        }

        loadBannerData();
        loadLogoData();
        loadSystemConfig();
        renderAdminItems();
    } catch (error) {
        console.error('Error initializing admin panel:', error);
        showError('Error al inicializar el panel de administración');
    }
};

// Load system configuration
const loadSystemConfig = () => {
    try {
        const config = JSON.parse(localStorage.getItem('systemConfig'));
        if (config) {
            document.getElementById('country-code').value = config.countryCode;
            document.getElementById('whatsapp-number').value = config.whatsappNumber;
            document.getElementById('auto-release-enabled').checked = config.autoReleaseEnabled;
            document.getElementById('auto-release-minutes').value = config.autoReleaseMinutes;
            document.getElementById('auto-release-minutes').disabled = !config.autoReleaseEnabled;
        }
    } catch (error) {
        console.error('Error loading system config:', error);
    }
};

// Handle system configuration form submission
document.getElementById('system-config-form').addEventListener('submit', (e) => {
    e.preventDefault();
    
    try {
        const countryCode = document.getElementById('country-code').value.trim();
        const whatsappNumber = document.getElementById('whatsapp-number').value.trim();
        const autoReleaseEnabled = document.getElementById('auto-release-enabled').checked;
        const autoReleaseMinutes = parseInt(document.getElementById('auto-release-minutes').value);

        if (!countryCode || !whatsappNumber || (autoReleaseEnabled && (!autoReleaseMinutes || autoReleaseMinutes <= 0))) {
            showError('Por favor complete todos los campos correctamente');
            return;
        }

        const config = {
            countryCode,
            whatsappNumber,
            autoReleaseEnabled,
            autoReleaseMinutes
        };
        localStorage.setItem('systemConfig', JSON.stringify(config));
        showSuccess('Configuración actualizada exitosamente');
    } catch (error) {
        console.error('Error saving system config:', error);
        showError('Error al guardar la configuración');
    }
});

// Toggle auto-release minutes input
document.getElementById('auto-release-enabled').addEventListener('change', (e) => {
    document.getElementById('auto-release-minutes').disabled = !e.target.checked;
});

// Load and display logo data
const loadLogoData = () => {
    try {
        const logoData = JSON.parse(localStorage.getItem('logoData'));
        if (logoData) {
            document.getElementById('logo-image').value = logoData.image;
            updateLogoPreview(logoData.image);
        }
    } catch (error) {
        console.error('Error loading logo data:', error);
    }
};

// Update logo preview
const updateLogoPreview = (imageUrl) => {
    const preview = document.getElementById('logo-preview');
    preview.style.backgroundImage = `url(${imageUrl})`;
};

// Handle logo form submission
document.getElementById('logo-form').addEventListener('submit', (e) => {
    e.preventDefault();
    
    try {
        const image = document.getElementById('logo-image').value.trim();

        if (!image) {
            showError('Por favor ingrese la URL del logo');
            return;
        }

        const logoData = { image };
        localStorage.setItem('logoData', JSON.stringify(logoData));
        showSuccess('Logo actualizado exitosamente');
    } catch (error) {
        console.error('Error saving logo:', error);
        showError('Error al guardar el logo');
    }
});

// Load and display banner data
const loadBannerData = () => {
    try {
        const bannerData = JSON.parse(localStorage.getItem('bannerData'));
        if (bannerData) {
            document.getElementById('banner-image').value = bannerData.image;
            document.getElementById('banner-title').value = bannerData.title;
            updateBannerPreview(bannerData.image);
        }
    } catch (error) {
        console.error('Error loading banner data:', error);
    }
};

// Update banner preview
const updateBannerPreview = (imageUrl) => {
    const preview = document.getElementById('banner-preview');
    preview.style.backgroundImage = `url(${imageUrl})`;
};

// Handle banner form submission
document.getElementById('banner-form').addEventListener('submit', (e) => {
    e.preventDefault();
    
    try {
        const image = document.getElementById('banner-image').value.trim();
        const title = document.getElementById('banner-title').value.trim();

        if (!image || !title) {
            showError('Por favor complete todos los campos del banner');
            return;
        }

        const bannerData = { image, title };
        localStorage.setItem('bannerData', JSON.stringify(bannerData));
        showSuccess('Banner actualizado exitosamente');
    } catch (error) {
        console.error('Error saving banner:', error);
        showError('Error al guardar el banner');
    }
});

// Render all items in admin view
const renderAdminItems = () => {
    try {
        const container = document.getElementById('admin-items-container');
        const items = JSON.parse(localStorage.getItem('raffleItems') || '[]');
        
        if (items.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>No hay items creados aún.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = items.map(item => `
            <div class="border rounded-lg p-6 mb-6" data-item-id="${item.id}">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800">${item.name}</h3>
                        <p class="text-gray-600 mt-1">${item.description}</p>
                    </div>
                    <img src="${item.image}" alt="${item.name}" class="w-24 h-24 object-cover rounded">
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-green-100 p-4 rounded-lg">
                        <p class="text-green-800 font-medium">Disponibles</p>
                        <p class="text-2xl font-bold text-green-600">${item.availableBalls.length}</p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-lg">
                        <p class="text-yellow-800 font-medium">Reservadas</p>
                        <p class="text-2xl font-bold text-yellow-600">${item.reservedBalls.length}</p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <p class="text-gray-800 font-medium">Bloqueadas</p>
                        <p class="text-2xl font-bold text-gray-600">${item.blockedBalls.length}</p>
                    </div>
                </div>

                <!-- Reserved Balls Section -->
                ${renderReservedBalls(item)}
            </div>
        `).join('');
    } catch (error) {
        console.error('Error rendering admin items:', error);
        showError('Error al mostrar los items');
    }
};

// Group reservations by user
const groupReservationsByUser = (reservedBalls) => {
    const groups = {};
    reservedBalls.forEach(ball => {
        const key = `${ball.user.nombre}_${ball.user.apellido}_${ball.user.telefono}`;
        if (!groups[key]) {
            groups[key] = {
                user: ball.user,
                balls: []
            };
        }
        groups[key].balls.push(ball.number);
    });
    return Object.values(groups);
};

// Render reserved balls section
const renderReservedBalls = (item) => {
    if (item.reservedBalls.length === 0) {
        return `
            <div class="text-center py-4 text-gray-500 bg-gray-50 rounded-lg">
                <p>No hay balotas reservadas</p>
            </div>
        `;
    }

    const groupedReservations = groupReservationsByUser(item.reservedBalls);

    return `
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-800 mb-4">Balotas Reservadas</h4>
            <div class="space-y-3">
                ${groupedReservations.map(group => `
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">${group.user.nombre} ${group.user.apellido}</p>
                                <p class="text-sm text-gray-600">${group.user.telefono}</p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    ${group.balls.map(number => `
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                            ${number}
                                            ${item.blockedBalls.includes(number) ? 
                                                '<span class="ml-1 text-green-600 font-medium">✓ PAGO</span>' : 
                                                ''}
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                            ${group.balls.some(number => !item.blockedBalls.includes(number)) ? `
                                <button 
                                    onclick="confirmBlockBalls('${item.id}', ${JSON.stringify(group.balls)})"
                                    class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                                    <i class="fas fa-lock mr-2"></i>
                                    Marcar como Pagado
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
};

// Create new raffle item
document.getElementById('create-item-form').addEventListener('submit', (e) => {
    e.preventDefault();
    
    try {
        const name = document.getElementById('item-name').value.trim();
        const image = document.getElementById('item-image').value.trim();
        const description = document.getElementById('item-description').value.trim();
        const totalBalls = parseInt(document.getElementById('total-balls').value);

        if (!name || !image || !description || isNaN(totalBalls) || totalBalls <= 0) {
            showError('Por favor complete todos los campos correctamente');
            return;
        }

        // Create new item object
        const newItem = {
            id: Date.now(),
            name,
            image,
            description,
            totalBalls,
            availableBalls: Array.from({length: totalBalls}, (_, i) => formatNumber(i + 1)),
            reservedBalls: [],
            blockedBalls: []
        };

        // Update localStorage
        const items = JSON.parse(localStorage.getItem('raffleItems') || '[]');
        items.push(newItem);
        localStorage.setItem('raffleItems', JSON.stringify(items));

        // Reset form and refresh display
        e.target.reset();
        renderAdminItems();
        showSuccess('Item creado exitosamente');
    } catch (error) {
        console.error('Error creating item:', error);
        showError('Error al crear el item');
    }
});

// Confirmation modal handling
const confirmModal = document.getElementById('confirm-modal');
const confirmAction = document.getElementById('confirm-action');
const confirmCancel = document.getElementById('confirm-cancel');
let pendingAction = null;

const confirmBlockBalls = (itemId, balls) => {
    pendingAction = () => blockBalls(itemId, balls);
    document.getElementById('modal-title').textContent = 'Confirmar Pago';
    document.getElementById('modal-message').textContent = 
        `¿Está seguro que desea marcar las balotas ${balls.join(', ')} como pagadas? Esta acción no se puede deshacer.`;
    confirmModal.classList.remove('hidden');
};

confirmAction.addEventListener('click', () => {
    if (pendingAction) {
        pendingAction();
        pendingAction = null;
    }
    confirmModal.classList.add('hidden');
});

confirmCancel.addEventListener('click', () => {
    pendingAction = null;
    confirmModal.classList.add('hidden');
});

// Block multiple balls (mark as paid)
const blockBalls = (itemId, ballNumbers) => {
    try {
        const items = JSON.parse(localStorage.getItem('raffleItems'));
        const itemIndex = items.findIndex(i => i.id === parseInt(itemId));
        
        if (itemIndex === -1) {
            throw new Error('Item not found');
        }

        const item = items[itemIndex];
        ballNumbers.forEach(number => {
            if (!item.blockedBalls.includes(number)) {
                item.blockedBalls.push(number);
            }
        });

        // Update localStorage and refresh display
        localStorage.setItem('raffleItems', JSON.stringify(items));
        renderAdminItems();
        showSuccess(`Balotas ${ballNumbers.join(', ')} marcadas como pagadas exitosamente`);
    } catch (error) {
        console.error('Error marking balls as paid:', error);
        showError('Error al marcar las balotas como pagadas');
    }
};

// Utility functions for notifications
const showSuccess = (message) => {
    alert(message); // You could implement a more sophisticated notification system here
};

const showError = (message) => {
    alert('Error: ' + message); // You could implement a more sophisticated notification system here
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', initAdmin);