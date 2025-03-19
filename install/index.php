<?php
session_start();

// Check if system is already installed
if (file_exists('../includes/.installed')) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador del Sistema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <i class="fas fa-wrench text-4xl text-blue-600 mb-4"></i>
                    <h1 class="text-2xl font-bold text-gray-800">Instalador del Sistema</h1>
                    <p class="text-gray-600 mt-2">Configure el acceso de administrador</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form action="process.php" method="POST" class="space-y-6">
                    <!-- Admin Configuration -->
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-700">Configuración del Administrador</h2>
                        
                        <div>
                            <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-1">
                                Usuario Administrador
                            </label>
                            <input type="text" id="admin_username" name="admin_username" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Ingrese el nombre de usuario del administrador">
                        </div>

                        <div>
                            <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña del Administrador
                            </label>
                            <input type="password" id="admin_password" name="admin_password" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Ingrese la contraseña del administrador">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>
                        Instalar Sistema
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>