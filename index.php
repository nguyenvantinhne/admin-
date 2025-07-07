<?php
require_once __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;

// Firebase mới không dùng ServiceAccount::fromJsonFile
$firebase = (new Factory)
    ->withServiceAccount(__DIR__ . '/serviceAccountKey.json')
    ->create();

$db = $firebase->getFirestore();

// Duyệt
if (isset($_POST['approve'])) {
    $userId = $_POST['userId'];
    $db->collection('users')->document($userId)->update([
        ['path' => 'approved', 'value' => true],
        ['path' => 'approvedAt', 'value' => new \DateTime()]
    ]);
}

// Từ chối
if (isset($_POST['reject'])) {
    $userId = $_POST['userId'];
    $db->collection('users')->document($userId)->delete();
}

// Danh sách chờ duyệt
$users = $db->collection('users')
    ->where('approved', '==', false)
    ->documents();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Duyệt Tài Khoản</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .card-hover:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .gradient-bg {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
  <div class="min-h-screen flex flex-col">
    <header class="gradient-bg text-white py-5 px-4 shadow">
      <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-2xl font-bold"><i class="fas fa-user-shield mr-2"></i>MrTính iOS - Admin Panel</h1>
        <button class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded shadow flex items-center">
          <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
        </button>
      </div>
    </header>

    <main class="flex-1 container mx-auto px-4 py-8">
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-semibold mb-6 flex items-center">
          <i class="fas fa-user-clock mr-2 text-blue-600"></i> Danh sách tài khoản chờ duyệt
        </h2>

        <?php if ($users->isEmpty()): ?>
          <div class="text-center py-10 text-gray-600">
            <i class="fas fa-check-circle text-green-500 text-4xl mb-2"></i>
            <p>Không có tài khoản nào đang chờ duyệt</p>
          </div>
        <?php else: ?>
          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($users as $user): $data = $user->data(); ?>
              <div class="card-hover bg-white border rounded-lg p-5 shadow transition">
                <div class="mb-3 flex items-center">
                  <div class="bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                    <i class="fas fa-user text-lg"></i>
                  </div>
                  <div>
                    <h3 class="text-lg font-bold"><?= htmlspecialchars($data['name'] ?? 'Không có tên') ?></h3>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($data['email'] ?? '') ?></p>
                  </div>
                </div>
                <p class="text-sm mb-1"><strong>Thời gian đăng ký:</strong> <?= isset($data['createdAt']) ? $data['createdAt']->format('d/m/Y H:i') : 'N/A' ?></p>
                <p class="text-sm mb-4"><strong>Device ID:</strong> <?= htmlspecialchars($data['deviceId'] ?? 'N/A') ?></p>

                <div class="flex space-x-2">
                  <form method="post" class="flex-1">
                    <input type="hidden" name="userId" value="<?= $user->id() ?>">
                    <button name="approve" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded flex items-center justify-center">
                      <i class="fas fa-check mr-2"></i> Duyệt
                    </button>
                  </form>
                  <form method="post" class="flex-1">
                    <input type="hidden" name="userId" value="<?= $user->id() ?>">
                    <button name="reject" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded flex items-center justify-center">
                      <i class="fas fa-times mr-2"></i> Từ chối
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>

    <footer class="bg-gray-800 text-white py-4 text-center text-sm">
      © 2025 MrTính iOS - Phiên bản Admin 1.0
    </footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    <?php if (isset($_POST['approve'])): ?>
    Swal.fire({ icon: 'success', title: 'Đã duyệt tài khoản!', showConfirmButton: false, timer: 1500 });
    setTimeout(() => location.href = location.href, 1500);
    <?php elseif (isset($_POST['reject'])): ?>
    Swal.fire({ icon: 'success', title: 'Đã từ chối tài khoản!', showConfirmButton: false, timer: 1500 });
    setTimeout(() => location.href = location.href, 1500);
    <?php endif; ?>
  </script>
</body>
    </html>
