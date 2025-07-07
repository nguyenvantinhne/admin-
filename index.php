<?php
require_once __DIR__ . '/vendor/autoload.php'; // Đường dẫn đến autoload của Firebase

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

// Khởi tạo Firebase
$serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/serviceAccountKey.json');
$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->create();

$db = $firebase->getFirestore();

// Xử lý duyệt tài khoản
if (isset($_POST['approve'])) {
    $userId = $_POST['userId'];
    $db->collection('users')->document($userId)->update([
        ['path' => 'approved', 'value' => true],
        ['path' => 'approvedAt', 'value' => new \DateTime()]
    ]);
}

// Xử lý từ chối tài khoản
if (isset($_POST['reject'])) {
    $userId = $_POST['userId'];
    $db->collection('users')->document($userId)->delete();
}

// Lấy danh sách tài khoản chờ duyệt
$users = $db->collection('users')
    ->where('approved', '==', false)
    ->documents();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Duyệt Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="gradient-bg text-white shadow-lg">
            <div class="container mx-auto px-4 py-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold">MrTính iOS - Admin Panel</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm">Xin chào, Admin</span>
                        <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">
                        <i class="fas fa-user-clock mr-2"></i> Danh sách tài khoản chờ duyệt
                    </h2>
                    
                    <?php if ($users->isEmpty()): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
                            <p class="text-gray-600">Không có tài khoản nào chờ duyệt</p>
                        </div>
                    <?php else: ?>
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($users as $user): ?>
                                <?php $userData = $user->data(); ?>
                                <div class="card-hover bg-white border border-gray-200 rounded-lg shadow transition duration-300">
                                    <div class="p-6">
                                        <div class="flex items-center mb-4">
                                            <div class="bg-blue-100 text-blue-800 rounded-full p-3 mr-4">
                                                <i class="fas fa-user text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-lg"><?= htmlspecialchars($userData['name'] ?? 'Không có tên') ?></h3>
                                                <p class="text-gray-600 text-sm"><?= htmlspecialchars($userData['email']) ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 mb-4">
                                            <p class="text-sm">
                                                <span class="font-medium">Đăng ký lúc:</span> 
                                                <?= isset($userData['createdAt']) ? $userData['createdAt']->format('d/m/Y H:i') : 'N/A' ?>
                                            </p>
                                            <p class="text-sm">
                                                <span class="font-medium">Device ID:</span> 
                                                <?= htmlspecialchars($userData['deviceId'] ?? 'N/A') ?>
                                            </p>
                                        </div>
                                        
                                        <div class="flex space-x-2">
                                            <form method="post" class="flex-1">
                                                <input type="hidden" name="userId" value="<?= $user->id() ?>">
                                                <button type="submit" name="approve" 
                                                    class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition flex items-center justify-center">
                                                    <i class="fas fa-check mr-2"></i> Duyệt
                                                </button>
                                            </form>
                                            <form method="post" class="flex-1">
                                                <input type="hidden" name="userId" value="<?= $user->id() ?>">
                                                <button type="submit" name="reject" 
                                                    class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition flex items-center justify-center">
                                                    <i class="fas fa-times mr-2"></i> Từ chối
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-6 mt-12">
            <div class="container mx-auto px-4 text-center">
                <p>© 2023 MrTính iOS - Tool Game Tài Xíu Uy Tín</p>
                <p class="text-sm text-gray-400 mt-2">Phiên bản Admin 1.0.0</p>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Hiển thị thông báo khi duyệt/từ chối
        <?php if (isset($_POST['approve'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Đã duyệt tài khoản!',
                showConfirmButton: false,
                timer: 1500
            });
            setTimeout(() => window.location.reload(), 1500);
        <?php elseif (isset($_POST['reject'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Đã từ chối tài khoản!',
                showConfirmButton: false,
                timer: 1500
            });
            setTimeout(() => window.location.reload(), 1500);
        <?php endif; ?>
    </script>
</body>
</html>
