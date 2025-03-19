<?php
// Check if system is installed
if (!file_exists('includes/.installed')) {
    header('Location: install/index.php');
    exit();
}

require_once 'includes/config.php';

// Get banner data
try {
    $stmt = $pdo->query("SELECT * FROM banner ORDER BY id DESC LIMIT 1");
    $banner = $stmt->fetch();
} catch (PDOException $e) {
    $banner = [
        'image_url' => 'https://images.unsplash.com/photo-1522542550221-31fd19575a2d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1000&q=80',
        'title' => '¡Grandes Premios te Esperan!'
    ];
}

// Get logo data
try {
    $stmt = $pdo->query("SELECT * FROM logo ORDER BY id DESC LIMIT 1");
    $logo = $stmt->fetch();
} catch (PDOException $e) {
    $logo = [
        'image_url' => 'https://via.placeholder.com/200x100?text=Logo'
    ];
}

// Get system configuration
try {
    $stmt = $pdo->query("SELECT * FROM system_config ORDER BY id DESC LIMIT 1");
    $config = $stmt->fetch();
} catch (PDOException $e) {
    $config = [
        'country_code' => '+57',
        'whatsapp_number' => '3229009051',
        'auto_release_enabled' => false,
        'auto_release_minutes' => 10
    ];
}

// Get all raffle items with their balls
try {
    $stmt = $pdo->query("
        SELECT 
            ri.*,
            COUNT(CASE WHEN b.status = 'available' THEN 1 END) as available_balls,
            COUNT(CASE WHEN b.status = 'reserved' THEN 1 END) as reserved_balls,
            COUNT(CASE WHEN b.status = 'blocked' THEN 1 END) as blocked_balls
        FROM raffle_items ri
        LEFT JOIN balls b ON ri.id = b.item_id
        GROUP BY ri.id
        ORDER BY ri.created_at DESC
    ");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
}

// Convert PHP variables to JavaScript
$jsConfig = json_encode([
    'countryCode' => $config['country_code'],
    'whatsappNumber' => $config['whatsapp_number'],
    'autoReleaseEnabled' => (bool)$config['auto_release_enabled'],
    'autoReleaseMinutes' => (int)$config['auto_release_minutes']
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Rifas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .banner-container {
            height: 300px;
            background-size: cover;
            background-position: center;
        }
        .ball-grid {
            max-height: 300px;
            overflow-y: auto;
        }
        .rounded-button {
            border-radius: 0.375rem;
        }
        input[type="number"]::-webkit-inner-spin-button, 
        input[type="number"]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
        .mt-16 {
            margin-top: 4rem;
        }
        .mt-20 {
            margin-top: 5rem;
        }
        .container {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body class="bg-gray-50">
    <header class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <img id="site-logo" src="<?php echo htmlspecialchars($logo['image_url']); ?>" alt="Logo" class="h-8 mr-3">
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>
                        Sistema de Rifas
                    </h1>
                </div>
                <button onclick="showLoginModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-user mr-2"></i>
                    Admin Login
                </button>
            </div>
        </div>
    </header>

    <div id="banner-section" class="banner-container mt-16 relative bg-gray-200" 
         style="background-image: url('<?php echo htmlspecialchars($banner['image_url']); ?>');">
        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
            <h2 class="text-4xl font-bold text-white text-center px-4">
                <?php echo htmlspecialchars($banner['title']); ?>
            </h2>
        </div>
    </div>

    <main class="container mx-auto px-4 py-8 mt-8">
        <div id="raffle-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as $item): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all hover:scale-105" data-item-id="<?php echo $item['id']; ?>">
                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($item['name']); ?></h2>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($item['description']); ?></p>
                    
                    <!-- Search Bar -->
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text" 
                                placeholder="Buscar número de balota..." 
                                onkeyup="searchBall('<?php echo $item['id']; ?>', this.value)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Ball Range Selector -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Rango:</label>
                        <select onchange="updateBallRange('<?php echo $item['id']; ?>', this.value)" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <?php
                            $total = $item['total_balls'];
                            $rangeSize = 50;
                            for ($i = 0; $i < $total; $i += $rangeSize) {
                                $start = $i + 1;
                                $end = min($i + $rangeSize, $total);
                                echo "<option value=\"$start-$end\">Balotas " . str_pad($start, 5, '0', STR_PAD_LEFT) . "-" . str_pad($end, 5, '0', STR_PAD_LEFT) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Balotas Grid -->
                    <div class="ball-grid grid grid-cols-5 gap-2 mb-4" id="ball-grid-<?php echo $item['id']; ?>">
                        <!-- Balls will be loaded dynamically via JavaScript -->
                    </div>

                    <!-- Controls -->
                    <div class="flex flex-col space-y-3">
                        <button onclick="showLuckyMachineOptions('<?php echo $item['id']; ?>')"
                            class="w-full bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 transition-colors">
                            <i class="fas fa-dice mr-2"></i>
                            Maquinita de la Suerte
                        </button>
                        <div class="flex space-x-2">
                            <button onclick="handleApartar('<?php echo $item['id']; ?>')"
                                class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors"
                                id="apartar-<?php echo $item['id']; ?>" disabled>
                                <i class="fas fa-check-circle mr-2"></i>
                                Apartar Seleccionados
                            </button>
                            <button onclick="clearSelection('<?php echo $item['id']; ?>')"
                                class="bg-red-500 text-white px-4 py-3 rounded-lg hover:bg-red-600 transition-colors"
                                id="clear-<?php echo $item['id']; ?>" disabled>
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Login Modal -->
    <div id="login-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Admin Login</h3>
                <form id="login-form" class="space-y-4" action="admin/index.php" method="POST">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Usuario</label>
                        <input type="text" id="username" name="username" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" id="password" name="password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeLoginModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Ingresar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reservation Modal -->
    <div id="reservation-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Reservar Balota(s)</h3>
                <div id="selected-balls" class="mb-4 p-3 bg-gray-100 rounded">
                    <!-- Selected balls will be listed here -->
                </div>
                <form id="reservation-form" class="space-y-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="apellido" class="block text-sm font-medium text-gray-700">Apellido</label>
                        <input type="text" id="apellido" name="apellido" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" required
                            pattern="[0-9]*" inputmode="numeric"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeReservationModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Reservar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lucky Machine Modal -->
    <div id="lucky-machine-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white text-center">
            <h3 class="text-xl font-bold mb-4">¡Maquinita de la Suerte!</h3>
            <div class="mb-4">
                <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExcDI5Y3Zwd2N4NXF1ZWR1M2JyNHd0ZmRnOWF1bWxqbXF1aWR2NzB1eCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/26uf6qaxqHpYXgjWU/giphy.gif" 
                     alt="Slot Machine" 
                     class="w-48 h-48 mx-auto">
            </div>
            <div id="lucky-number" class="text-sm font-bold text-blue-600 mb-4">
                <!-- Lucky number will appear here -->
            </div>
            <button onclick="closeLuckyMachineModal()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Cerrar
            </button>
        </div>
    </div>

    <script>
        // Pass PHP configuration to JavaScript
        const systemConfig = <?php echo $jsConfig; ?>;
    </script>
    <script src="assets/js/raffle.js"></script>
</body>
</html>