// Utility function to format numbers with leading zeros
const formatNumber = (num, length = 5) => String(num).padStart(length, '0');

// Initialize raffle data in localStorage if not exists
const initializeRaffleData = () => {
    console.log('Initializing raffle data...');
    try {
        // Clear localStorage for testing
        localStorage.clear();

        const demoItem = {
            id: Date.now(),
            name: 'PlayStation 5',
            description: 'La última consola de Sony con gráficos 4K, ray tracing y control DualSense',
            image: 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1000&q=80',
            totalBalls: 100,
            availableBalls: Array.from({length: 100}, (_, i) => formatNumber(i + 1)),
            reservedBalls: [],
            blockedBalls: []
        };

        const defaultBanner = {
            image: 'https://images.unsplash.com/photo-1522542550221-31fd19575a2d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1000&q=80',
            title: '¡Grandes Premios te Esperan!'
        };

        const defaultLogo = {
            image: 'https://via.placeholder.com/200x100?text=Logo'
        };

        const defaultConfig = {
            countryCode: '+57',
            whatsappNumber: '3229009051',
            autoReleaseEnabled: false,
            autoReleaseMinutes: 10
        };

        localStorage.setItem('raffleItems', JSON.stringify([demoItem]));
        localStorage.setItem('bannerData', JSON.stringify(defaultBanner));
        localStorage.setItem('logoData', JSON.stringify(defaultLogo));
        localStorage.setItem('systemConfig', JSON.stringify(defaultConfig));

        console.log('Data initialized successfully');
    } catch (error) {
        console.error('Error initializing raffle data:', error);
    }
};

// Update banner and logo
const updateBanner = () => {
    console.log('Updating banner...');
    try {
        const bannerData = JSON.parse(localStorage.getItem('bannerData'));
        const logoData = JSON.parse(localStorage.getItem('logoData'));
        
        if (bannerData) {
            const bannerSection = document.getElementById('banner-section');
            bannerSection.style.backgroundImage = `url(${bannerData.image})`;
            const title = bannerSection.querySelector('h2');
            title.textContent = bannerData.title;
        }

        if (logoData) {
            const siteLogo = document.getElementById('site-logo');
            siteLogo.src = logoData.image;
        }
        console.log('Banner updated successfully');
    } catch (error) {
        console.error('Error updating banner:', error);
    }
};

// Generate range options
const generateRangeOptions = (total) => {
    const ranges = [];
    const rangeSize = 50; // Show 50 numbers per range
    for (let i = 0; i < total; i += rangeSize) {
        const start = i + 1;
        const end = Math.min(i + rangeSize, total);
        ranges.push(`<option value="${start}-${end}">Balotas ${formatNumber(start)}-${formatNumber(end)}</option>`);
    }
    return ranges.join('');
};

// Generate HTML for ball buttons
const generateBallButtons = (item, start, end) => {
    return Array.from({length: end - start + 1}, (_, i) => {
        const number = formatNumber(start + i);
        const isAvailable = item.availableBalls.includes(number);
        const isReserved = item.reservedBalls.some(ball => ball.number === number);
        const isBlocked = item.blockedBalls.includes(number);
        const isSelected = selectedBalls[item.id]?.includes(number);
        
        let buttonClass = 'w-full py-2 text-sm rounded-lg transition-colors ';
        if (isBlocked) {
            buttonClass += 'bg-gray-300 text-gray-500 cursor-not-allowed';
        } else if (isReserved) {
            buttonClass += 'bg-yellow-200 text-yellow-800 cursor-not-allowed';
        } else if (isSelected) {
            buttonClass += 'bg-blue-500 text-white';
        } else {
            buttonClass += 'bg-gray-100 hover:bg-blue-100 text-gray-800 cursor-pointer';
        }

        return `
            <button 
                onclick="selectBall('${item.id}', '${number}')"
                class="${buttonClass}"
                ${!isAvailable || isReserved || isBlocked ? 'disabled' : ''}
                data-number="${number}">
                ${number}
            </button>
        `;
    }).join('');
};

// Render all raffle items
const renderRaffleItems = () => {
    console.log('Rendering raffle items...');
    try {
        const container = document.getElementById('raffle-container');
        const items = JSON.parse(localStorage.getItem('raffleItems') || '[]');
        console.log('Items to render:', items);
        
        container.innerHTML = items.map(item => `
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all hover:scale-105" data-item-id="${item.id}">
                <img src="${item.image}" alt="${item.name}" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">${item.name}</h2>
                    <p class="text-gray-600 mb-4">${item.description}</p>
                    
                    <!-- Search Bar -->
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text" 
                                placeholder="Buscar número de balota..." 
                                onkeyup="searchBall('${item.id}', this.value)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Ball Range Selector -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Rango:</label>
                        <select onchange="updateBallRange('${item.id}', this.value)" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            ${generateRangeOptions(item.totalBalls)}
                        </select>
                    </div>

                    <!-- Balotas Grid -->
                    <div class="ball-grid grid grid-cols-5 gap-2 mb-4" id="ball-grid-${item.id}">
                        ${generateBallButtons(item, 1, Math.min(50, item.totalBalls))}
                    </div>

                    <!-- Controls -->
                    <div class="flex flex-col space-y-3">
                        <button onclick="showLuckyMachineOptions('${item.id}')"
                            class="w-full bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 transition-colors">
                            <i class="fas fa-dice mr-2"></i>
                            Maquinita de la Suerte
                        </button>
                        <div class="flex space-x-2">
                            <button onclick="handleApartar('${item.id}')"
                                class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors"
                                id="apartar-${item.id}" ${!selectedBalls[item.id]?.length ? 'disabled' : ''}>
                                <i class="fas fa-check-circle mr-2"></i>
                                Apartar Seleccionados
                            </button>
                            <button onclick="clearSelection('${item.id}')"
                                class="bg-red-500 text-white px-4 py-3 rounded-lg hover:bg-red-600 transition-colors"
                                id="clear-${item.id}" ${!selectedBalls[item.id]?.length ? 'disabled' : ''}>
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        console.log('Items rendered successfully');
    } catch (error) {
        console.error('Error rendering raffle items:', error);
    }
};

// Clear ball selection
const clearSelection = (itemId) => {
    if (selectedBalls[itemId]) {
        selectedBalls[itemId] = [];
        renderRaffleItems();
    }
};

// Handle ball selection
let selectedBalls = {};
const selectBall = (itemId, number) => {
    try {
        const items = JSON.parse(localStorage.getItem('raffleItems'));
        const item = items.find(i => i.id === parseInt(itemId));
        
        if (!item || !item.availableBalls.includes(number)) {
            return;
        }

        // Initialize array for this item if it doesn't exist
        if (!selectedBalls[itemId]) {
            selectedBalls[itemId] = [];
        }

        const index = selectedBalls[itemId].indexOf(number);
        if (index === -1) {
            // Add selection
            selectedBalls[itemId].push(number);
        } else {
            // Remove selection
            selectedBalls[itemId].splice(index, 1);
        }

        // Update UI
        const button = document.querySelector(`[data-item-id="${itemId}"] [data-number="${number}"]`);
        if (button) {
            button.classList.toggle('bg-blue-500');
            button.classList.toggle('text-white');
            button.classList.toggle('bg-gray-100');
            button.classList.toggle('text-gray-800');
        }

        // Enable/disable buttons
        const hasSelection = selectedBalls[itemId].length > 0;
        document.getElementById(`apartar-${itemId}`).disabled = !hasSelection;
        document.getElementById(`clear-${itemId}`).disabled = !hasSelection;
    } catch (error) {
        console.error('Error selecting ball:', error);
    }
};

// Search for a specific ball number
const searchBall = (itemId, query) => {
    if (!query) {
        updateBallRange(itemId, '1-50'); // Reset to first range
        return;
    }

    try {
        const items = JSON.parse(localStorage.getItem('raffleItems'));
        const item = items.find(i => i.id === parseInt(itemId));
        
        if (!item) return;

        const grid = document.getElementById(`ball-grid-${itemId}`);
        const number = formatNumber(parseInt(query));
        
        if (number) {
            let status = 'disponible';
            let buttonClass = 'bg-gray-100 text-gray-800';
            
            if (item.blockedBalls.includes(number)) {
                status = 'bloqueada';
                buttonClass = 'bg-gray-300 text-gray-500';
            } else if (item.reservedBalls.some(ball => ball.number === number)) {
                status = 'reservada';
                buttonClass = 'bg-yellow-200 text-yellow-800';
                const reservation = item.reservedBalls.find(ball => ball.number === number);
                if (reservation) {
                    status += ` por ${reservation.user.nombre} ${reservation.user.apellido}`;
                }
            }

            grid.innerHTML = `
                <div class="col-span-5 p-4 rounded-lg ${buttonClass}">
                    <p class="font-medium">Balota ${number}</p>
                    <p class="text-sm">Estado: ${status}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error searching ball:', error);
    }
};

// Update ball range display
const updateBallRange = (itemId, range) => {
    const [start, end] = range.split('-').map(Number);
    const grid = document.getElementById(`ball-grid-${itemId}`);
    const items = JSON.parse(localStorage.getItem('raffleItems'));
    const item = items.find(i => i.id === parseInt(itemId));
    if (item && grid) {
        grid.innerHTML = generateBallButtons(item, start, end);
    }
};

// Show lucky machine options
const showLuckyMachineOptions = (itemId) => {
    const modal = document.getElementById('lucky-machine-modal');
    const luckyNumber = document.getElementById('lucky-number');
    modal.classList.remove('hidden');
    luckyNumber.innerHTML = `
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                ¿Cuántos números desea generar?
            </label>
            <select id="lucky-count" class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="1">1 número</option>
                <option value="5">5 números</option>
                <option value="10">10 números</option>
                <option value="20">20 números</option>
                <option value="50">50 números</option>
            </select>
        </div>
        <button onclick="generateLuckyNumbers('${itemId}')"
                class="w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors text-sm">
            <i class="fas fa-dice mr-2"></i>
            ¡Generar Números!
        </button>
    `;
};

// Generate lucky numbers
const generateLuckyNumbers = (itemId) => {
    try {
        const items = JSON.parse(localStorage.getItem('raffleItems'));
        const item = items.find(i => i.id === parseInt(itemId));
        const count = parseInt(document.getElementById('lucky-count').value);
        
        if (!item || !item.availableBalls.length) {
            alert('No hay balotas disponibles para este item.');
            return;
        }

        const luckyNumber = document.getElementById('lucky-number');
        luckyNumber.innerHTML = '<div class="text-sm mb-4">Generando números...</div>';

        setTimeout(() => {
            const selectedNumbers = [];
            const availableNumbers = [...item.availableBalls];

            for (let i = 0; i < Math.min(count, availableNumbers.length); i++) {
                const randomIndex = Math.floor(Math.random() * availableNumbers.length);
                const number = availableNumbers.splice(randomIndex, 1)[0];
                selectedNumbers.push(number);
                selectBall(itemId, number);
            }

            luckyNumber.innerHTML = `
                <div class="text-sm mb-4">¡Sus números de la suerte son!</div>
                <div class="grid grid-cols-5 gap-2">
                    ${selectedNumbers.map(number => `
                        <div class="bg-blue-500 text-white p-2 rounded-lg text-sm text-center">${number}</div>
                    `).join('')}
                </div>
            `;
        }, 2000);
    } catch (error) {
        console.error('Error generating lucky numbers:', error);
    }
};

// Close lucky machine modal
const closeLuckyMachineModal = () => {
    document.getElementById('lucky-machine-modal').classList.add('hidden');
};

// Handle reservation modal
const modal = document.getElementById('reservation-modal');
let currentItemId = null;

const handleApartar = (itemId) => {
    currentItemId = itemId;
    const selectedBallsList = document.getElementById('selected-balls');
    const config = JSON.parse(localStorage.getItem('systemConfig'));
    const autoReleaseEnabled = config?.autoReleaseEnabled || false;
    const autoReleaseMinutes = config?.autoReleaseMinutes || 10;

    selectedBallsList.innerHTML = `
        <p class="font-medium mb-2">Balotas seleccionadas:</p>
        <div class="flex flex-wrap gap-2">
            ${selectedBalls[itemId].map(number => 
                `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">${number}</span>`
            ).join('')}
        </div>
        ${autoReleaseEnabled ? `
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-800 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Importante: Después de ${autoReleaseMinutes} minutos de no reportar el pago, la balota pasará a estar disponible nuevamente.
                </p>
            </div>
        ` : ''}
    `;
    modal.classList.remove('hidden');
    document.getElementById('nombre').focus();
};

// Close reservation modal
const closeReservationModal = () => {
    modal.classList.add('hidden');
    document.getElementById('reservation-form').reset();
};

// Handle form submission
document.getElementById('reservation-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const nombre = document.getElementById('nombre').value.trim();
        const apellido = document.getElementById('apellido').value.trim();
        const telefono = document.getElementById('telefono').value.trim();
        
        if (!nombre || !apellido || !telefono) {
            alert('Por favor complete todos los campos.');
            return;
        }

        const selectedNumbers = selectedBalls[currentItemId];
        if (!selectedNumbers?.length) {
            alert('Por favor seleccione al menos una balota.');
            return;
        }

        // Get system configuration
        const config = JSON.parse(localStorage.getItem('systemConfig'));
        const countryCode = config?.countryCode?.replace('+', '') || '57';
        const whatsappNumber = config?.whatsappNumber || '3229009051';
        const autoReleaseEnabled = config?.autoReleaseEnabled || false;
        const autoReleaseMinutes = config?.autoReleaseMinutes || 10;

        // Update localStorage
        const items = JSON.parse(localStorage.getItem('raffleItems'));
        const itemIndex = items.findIndex(i => i.id === parseInt(currentItemId));
        
        if (itemIndex === -1) {
            throw new Error('Item not found');
        }

        // Move balls from available to reserved
        selectedNumbers.forEach(number => {
            const ballIndex = items[itemIndex].availableBalls.indexOf(number);
            if (ballIndex > -1) {
                items[itemIndex].availableBalls.splice(ballIndex, 1);
                items[itemIndex].reservedBalls.push({
                    number,
                    user: { nombre, apellido, telefono },
                    reservedAt: new Date().toISOString()
                });
            }
        });

        localStorage.setItem('raffleItems', JSON.stringify(items));

        // Set up auto-release timer if enabled
        if (autoReleaseEnabled) {
            setTimeout(() => {
                const currentItems = JSON.parse(localStorage.getItem('raffleItems'));
                const item = currentItems[itemIndex];
                
                selectedNumbers.forEach(number => {
                    const reservedBall = item.reservedBalls.find(b => 
                        b.number === number && 
                        b.user.nombre === nombre && 
                        b.user.apellido === apellido &&
                        b.user.telefono === telefono
                    );

                    if (reservedBall && !item.blockedBalls.includes(number)) {
                        // Move ball back to available
                        const reservedIndex = item.reservedBalls.findIndex(b => b.number === number);
                        if (reservedIndex > -1) {
                            item.reservedBalls.splice(reservedIndex, 1);
                            item.availableBalls.push(number);
                            item.availableBalls.sort();
                        }
                    }
                });

                localStorage.setItem('raffleItems', JSON.stringify(currentItems));
                renderRaffleItems();
            }, autoReleaseMinutes * 60 * 1000);
        }

        // Construct WhatsApp message
        const message = `¡Hola! Quiero reservar las siguientes balotas para ${items[itemIndex].name}:\n${selectedNumbers.join(', ')}\nMis datos son:\nNombre: ${nombre} ${apellido}\nTeléfono: ${telefono}`;
        const whatsappUrl = `https://wa.me/${countryCode}${whatsappNumber}?text=${encodeURIComponent(message)}`;

        // Close modal and reset form
        modal.classList.add('hidden');
        document.getElementById('reservation-form').reset();
        delete selectedBalls[currentItemId];

        // Refresh display
        renderRaffleItems();

        // Open WhatsApp
        window.location.href = whatsappUrl;
    } catch (error) {
        console.error('Error processing reservation:', error);
        alert('Hubo un error al procesar la reserva. Por favor intente nuevamente.');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded');
    initializeRaffleData();
    updateBanner();
    renderRaffleItems();
});