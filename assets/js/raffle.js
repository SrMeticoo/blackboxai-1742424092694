// Utility function to format numbers with leading zeros
const formatNumber = (num, length = 5) => String(num).padStart(length, '0');

// Initialize selected balls object
let selectedBalls = {};

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
const generateBallButtons = async (itemId, start, end) => {
    try {
        const response = await fetch(`api/balls.php?item_id=${itemId}&start=${start}&end=${end}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message);
        }

        return data.balls.map(ball => {
            const isSelected = selectedBalls[itemId]?.includes(ball.number);
            
            let buttonClass = 'w-full py-2 text-sm rounded-lg transition-colors ';
            if (ball.status === 'blocked') {
                buttonClass += 'bg-gray-300 text-gray-500 cursor-not-allowed';
            } else if (ball.status === 'reserved') {
                buttonClass += 'bg-yellow-200 text-yellow-800 cursor-not-allowed';
            } else if (isSelected) {
                buttonClass += 'bg-blue-500 text-white';
            } else {
                buttonClass += 'bg-gray-100 hover:bg-blue-100 text-gray-800 cursor-pointer';
            }

            return `
                <button 
                    onclick="selectBall('${itemId}', '${ball.number}')"
                    class="${buttonClass}"
                    ${ball.status !== 'available' ? 'disabled' : ''}
                    data-number="${ball.number}">
                    ${ball.number}
                </button>
            `;
        }).join('');
    } catch (error) {
        console.error('Error generating ball buttons:', error);
        return '<div class="col-span-5 text-red-600">Error loading balls</div>';
    }
};

// Handle ball selection
const selectBall = async (itemId, number) => {
    try {
        const response = await fetch(`api/check-ball.php?item_id=${itemId}&number=${number}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        if (data.status !== 'available') {
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
const searchBall = async (itemId, query) => {
    if (!query) {
        updateBallRange(itemId, '1-50'); // Reset to first range
        return;
    }

    try {
        const response = await fetch(`api/search-ball.php?item_id=${itemId}&number=${query}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const grid = document.getElementById(`ball-grid-${itemId}`);
        const ball = data.ball;
        
        if (ball) {
            let status = 'disponible';
            let buttonClass = 'bg-gray-100 text-gray-800';
            
            if (ball.status === 'blocked') {
                status = 'bloqueada';
                buttonClass = 'bg-gray-300 text-gray-500';
            } else if (ball.status === 'reserved') {
                status = 'reservada';
                buttonClass = 'bg-yellow-200 text-yellow-800';
                if (ball.user_name) {
                    status += ` por ${ball.user_name} ${ball.user_lastname}`;
                }
            }

            grid.innerHTML = `
                <div class="col-span-5 p-4 rounded-lg ${buttonClass}">
                    <p class="font-medium">Balota ${ball.number}</p>
                    <p class="text-sm">Estado: ${status}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error searching ball:', error);
    }
};

// Update ball range display
const updateBallRange = async (itemId, range) => {
    const [start, end] = range.split('-').map(Number);
    const grid = document.getElementById(`ball-grid-${itemId}`);
    if (grid) {
        grid.innerHTML = await generateBallButtons(itemId, start, end);
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
const generateLuckyNumbers = async (itemId) => {
    try {
        const count = parseInt(document.getElementById('lucky-count').value);
        const response = await fetch(`api/lucky-numbers.php?item_id=${itemId}&count=${count}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const luckyNumber = document.getElementById('lucky-number');
        luckyNumber.innerHTML = '<div class="text-sm mb-4">Generando números...</div>';

        setTimeout(async () => {
            const numbers = data.numbers;
            
            // Select the generated numbers
            for (const number of numbers) {
                await selectBall(itemId, number);
            }

            luckyNumber.innerHTML = `
                <div class="text-sm mb-4">¡Sus números de la suerte son!</div>
                <div class="grid grid-cols-5 gap-2">
                    ${numbers.map(number => `
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

    selectedBallsList.innerHTML = `
        <p class="font-medium mb-2">Balotas seleccionadas:</p>
        <div class="flex flex-wrap gap-2">
            ${selectedBalls[itemId].map(number => 
                `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">${number}</span>`
            ).join('')}
        </div>
        ${systemConfig.autoReleaseEnabled ? `
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-800 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Importante: Después de ${systemConfig.autoReleaseMinutes} minutos de no reportar el pago, 
                    la balota pasará a estar disponible nuevamente.
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

        let reservationSuccess = false;
        let errorMessage = '';

        try {
            // Submit reservation
            const response = await fetch('api/reserve.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    item_id: currentItemId,
                    numbers: selectedNumbers,
                    user: {
                        nombre,
                        apellido,
                        telefono
                    }
                })
            });

            const data = await response.json();
            reservationSuccess = data.success;
            if (!data.success) {
                errorMessage = data.message;
                console.error('Error en la reserva:', data.message);
            }
        } catch (error) {
            console.error('Error processing reservation:', error);
            errorMessage = error.message;
        }

        // Construct WhatsApp message based on reservation result
        const message = reservationSuccess
            ? `¡Hola! Quiero reservar las siguientes balotas:\n${selectedNumbers.join(', ')}\nMis datos son:\nNombre: ${nombre} ${apellido}\nTeléfono: ${telefono}`
            : `¡Hola! Estoy intentando reservar las siguientes balotas:\n${selectedNumbers.join(', ')}\nMis datos son:\nNombre: ${nombre} ${apellido}\nTeléfono: ${telefono}\n\nNota: Hubo un inconveniente en el sistema${errorMessage ? ': ' + errorMessage : ''}, por favor confirmar disponibilidad.`;

        // Construct WhatsApp URL
        const whatsappUrl = `https://wa.me/${systemConfig.countryCode.replace('+', '')}${systemConfig.whatsappNumber}?text=${encodeURIComponent(message)}`;

        // Close modal and reset form
        modal.classList.add('hidden');
        document.getElementById('reservation-form').reset();
        delete selectedBalls[currentItemId];

        // Refresh display
        await updateBallRange(currentItemId, '1-50');

        // Open WhatsApp
        window.location.href = whatsappUrl;
    } catch (error) {
        console.error('Error in form submission:', error);
        
        // If there's an error in the outer try-catch (form handling),
        // still redirect to WhatsApp as a fallback
        const message = `¡Hola! Estoy intentando reservar las siguientes balotas:\n${selectedNumbers.join(', ')}\nMis datos son:\nNombre: ${nombre} ${apellido}\nTeléfono: ${telefono}\n\nNota: Hubo un inconveniente en el sistema, por favor confirmar disponibilidad.`;
        const whatsappUrl = `https://wa.me/${systemConfig.countryCode.replace('+', '')}${systemConfig.whatsappNumber}?text=${encodeURIComponent(message)}`;
        window.location.href = whatsappUrl;
    }
});

// Login modal handling
const showLoginModal = () => {
    document.getElementById('login-modal').classList.remove('hidden');
};

const closeLoginModal = () => {
    document.getElementById('login-modal').classList.add('hidden');
    document.getElementById('login-form').reset();
};

// Phone number validation
document.getElementById('telefono').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', async () => {
    console.log('DOM Content Loaded');
    // Load initial ball ranges for all items
    const items = document.querySelectorAll('[data-item-id]');
    for (const item of items) {
        const itemId = item.dataset.itemId;
        await updateBallRange(itemId, '1-50');
    }
});