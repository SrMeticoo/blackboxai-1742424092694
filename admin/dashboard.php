<?php
require_once '../includes/config.php';

// Check if user is logged in
requireLogin();

// Get system configuration
try {
    $stmt = $pdo->query("SELECT * FROM system_config ORDER BY id DESC LIMIT 1");
    $config = $stmt->fetch();
} catch (PDOException $e) {
    $config = [
        'country_code' => '+57',
        'whatsapp_number' => '',
        'auto_release_enabled' => false,
        'auto_release_minutes' => 10
    ];
}

// Get banner data
try {
    $stmt = $pdo->query("SELECT * FROM banner ORDER BY id DESC LIMIT 1");
    $banner = $stmt->fetch();
} catch (PDOException $e) {
    $banner = [
        'image_url' => '',
        'title' => ''
    ];
}

// Get logo data
try {
    $stmt = $pdo->query("SELECT * FROM logo ORDER BY id DESC LIMIT 1");
    $logo = $stmt->fetch();
} catch (PDOException $e) {
    $logo = [
        'image_url' => ''
    ];
}

// Get all raffle items with their statistics
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema de Rifas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .banner-preview, .logo-preview {
            height: 200px;
            background-size: cover;
            background-position: center;
        }
        .logo-preview {
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-cog text-blue-600 mr-2"></i>
                    Panel de Administración
                </h1>
                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-home mr-1"></i>
                        Ir al Portal
                    </a>
                    <form action="logout.php" method="POST" class="inline">
                        <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">
                            <i class="fas fa-sign-out-alt mr-1"></i>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- System Configuration Section -->
        <section class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-cogs text-blue-600 mr-2"></i>
                Configuración del Sistema
            </h2>
            <form id="system-config-form" action="actions/update-config.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Número de WhatsApp del Propietario
                    </label>
                    <div class="flex space-x-2">
                        <div class="w-1/4">
                            <select id="country-code" name="country_code" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="+57" <?php echo $config['country_code'] === '+57' ? 'selected' : ''; ?>>🇨🇴 +57 (Colombia)</option>
                                <option value="+1" <?php echo $config['country_code'] === '+1' ? 'selected' : ''; ?>>🇺🇸 +1 (USA)</option>
                                <!-- Add more country options as needed -->
                            </select>
                        </div>
                        <input type="tel" id="whatsapp-number" name="whatsapp_number" 
                            value="<?php echo htmlspecialchars($config['whatsapp_number']); ?>" required
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Número sin el código del país">
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input type="checkbox" id="auto-release-enabled" name="auto_release_enabled"
                            <?php echo $config['auto_release_enabled'] ? 'checked' : ''; ?>
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="auto-release-enabled" class="ml-2 block text-sm text-gray-700">
                            Habilitar liberación automática de balotas
                        </label>
                    </div>
                    <div>
                        <label for="auto-release-minutes" class="block text-sm font-medium text-gray-700">
                            Tiempo de espera (minutos)
                        </label>
                        <input type="number" id="auto-release-minutes" name="auto_release_minutes" min="1"
                            value="<?php echo (int)$config['auto_release_minutes']; ?>"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            <?php echo !$config['auto_release_enabled'] ? 'disabled' : ''; ?>>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Configuración
                    </button>
                </div>
            </form>
        </section>

        <!-- Logo Management Section -->
        <section class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-image text-blue-600 mr-2"></i>
                Gestionar Logo
            </h2>
            <form id="logo-form" action="actions/update-logo.php" method="POST" class="space-y-4">
                <div>
                    <label for="logo-image" class="block text-sm font-medium text-gray-700 mb-1">
                        URL del Logo
                    </label>
                    <input type="url" id="logo-image" name="image_url" 
                        value="<?php echo htmlspecialchars($logo['image_url']); ?>" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Vista Previa del Logo
                    </label>
                    <div id="logo-preview" class="logo-preview rounded-lg border border-gray-300"
                         style="background-image: url('<?php echo htmlspecialchars($logo['image_url']); ?>')"></div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Logo
                    </button>
                </div>
            </form>
        </section>

        <!-- Banner Management Section -->
        <section class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-image text-blue-600 mr-2"></i>
                Gestionar Banner
            </h2>
            <form id="banner-form" action="actions/update-banner.php" method="POST" class="space-y-4">
                <div>
                    <label for="banner-image" class="block text-sm font-medium text-gray-700 mb-1">
                        URL de la Imagen del Banner
                    </label>
                    <input type="url" id="banner-image" name="image_url" 
                        value="<?php echo htmlspecialchars($banner['image_url']); ?>" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="banner-title" class="block text-sm font-medium text-gray-700 mb-1">
                        Título del Banner
                    </label>
                    <input type="text" id="banner-title" name="title" 
                        value="<?php echo htmlspecialchars($banner['title']); ?>" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Vista Previa del Banner
                    </label>
                    <div id="banner-preview" class="banner-preview rounded-lg border border-gray-300"
                         style="background-image: url('<?php echo htmlspecialchars($banner['image_url']); ?>')"></div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Banner
                    </button>
                </div>
            </form>
        </section>

        <!-- Create New Item Section -->
        <section class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                Crear Nuevo Item
            </h2>
            <form id="create-item-form" action="actions/create-item.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="item-name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del Item
                        </label>
                        <input type="text" id="item-name" name="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="item-image" class="block text-sm font-medium text-gray-700 mb-1">
                            URL de la Imagen
                        </label>
                        <input type="url" id="item-image" name="image_url" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label for="item-description" class="block text-sm font-medium text-gray-700 mb-1">
                        Descripción
                    </label>
                    <textarea id="item-description" name="description" rows="3" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label for="total-balls" class="block text-sm font-medium text-gray-700 mb-1">
                        Cantidad de Balotas
                    </label>
                    <input type="number" id="total-balls" name="total_balls" required min="1" max="99999"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Crear Item
                    </button>
                </div>
            </form>
        </section>

        <!-- Manage Items Section -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-list text-blue-600 mr-2"></i>
                Gestionar Items
            </h2>
            <div id="admin-items-container" class="space-y-6">
                <?php if (empty($items)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>No hay items creados aún.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="border rounded-lg p-6 mb-6">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="w-24 h-24 object-cover rounded">
                            </div>

                            <!-- Statistics -->
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="bg-green-100 p-4 rounded-lg">
                                    <p class="text-green-800 font-medium">Disponibles</p>
                                    <p class="text-2xl font-bold text-green-600"><?php echo $item['available_balls']; ?></p>
                                </div>
                                <div class="bg-yellow-100 p-4 rounded-lg">
                                    <p class="text-yellow-800 font-medium">Reservadas</p>
                                    <p class="text-2xl font-bold text-yellow-600"><?php echo $item['reserved_balls']; ?></p>
                                </div>
                                <div class="bg-gray-100 p-4 rounded-lg">
                                    <p class="text-gray-800 font-medium">Bloqueadas</p>
                                    <p class="text-2xl font-bold text-gray-600"><?php echo $item['blocked_balls']; ?></p>
                                </div>
                            </div>

                            <!-- Reserved Balls Section -->
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT user_name, user_lastname, user_phone, GROUP_CONCAT(number) as numbers, 
                                       GROUP_CONCAT(status) as statuses
                                FROM balls 
                                WHERE item_id = ? AND status IN ('reserved', 'blocked')
                                GROUP BY user_name, user_lastname, user_phone
                            ");
                            $stmt->execute([$item['id']]);
                            $reservations = $stmt->fetchAll();
                            ?>

                            <?php if (!empty($reservations)): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-800 mb-4">Balotas Reservadas</h4>
                                    <div class="space-y-3">
                                        <?php foreach ($reservations as $reservation): ?>
                                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="font-medium">
                                                            <?php echo htmlspecialchars($reservation['user_name'] . ' ' . $reservation['user_lastname']); ?>
                                                        </p>
                                                        <p class="text-sm text-gray-600">
                                                            <?php echo htmlspecialchars($reservation['user_phone']); ?>
                                                        </p>
                                                        <div class="flex flex-wrap gap-2 mt-2">
                                                            <?php 
                                                            $numbers = explode(',', $reservation['numbers']);
                                                            $statuses = explode(',', $reservation['statuses']);
                                                            foreach ($numbers as $i => $number): 
                                                            ?>
                                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                                                    <?php echo htmlspecialchars($number); ?>
                                                                    <?php if ($statuses[$i] === 'blocked'): ?>
                                                                        <span class="ml-1 text-green-600 font-medium">✓ PAGO</span>
                                                                    <?php endif; ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <?php if (in_array('reserved', $statuses)): ?>
                                                        <button 
                                                            onclick="confirmBlockBalls(<?php echo $item['id']; ?>, '<?php echo implode(',', array_filter($numbers, function($k) use ($statuses) { return $statuses[$k] === 'reserved'; }, ARRAY_FILTER_USE_KEY)); ?>')"
                                                            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                                                            <i class="fas fa-lock mr-2"></i>
                                                            Marcar como Pagado
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-gray-500 bg-gray-50 rounded-lg">
                                    <p>No hay balotas reservadas</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Confirmar Acción</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="modal-message">
                        ¿Está seguro que desea realizar esta acción?
                    </p>
                </div>
                <div class="flex justify-center space-x-4 mt-3">
                    <button id="confirm-cancel"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancelar
                    </button>
                    <button id="confirm-action"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>