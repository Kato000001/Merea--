<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// ログインしていない場合はログイン画面に戻す
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merea - Home</title>
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-[#4D4D4D] text-white font-sans antialiased min-h-screen">
    
    <!-- Header -->
    <header class="bg-[#3A3A3A] shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <!-- Logo -->
            <div class="flex-shrink-0 cursor-pointer" id="logo">
                <h1 class="text-[#EBB73E] text-3xl font-bold tracking-wider">Merea</h1>
            </div>
            
            <!-- Search Bar -->
            <div class="flex-1 max-w-xl relative">
                <input type="text" id="search-input" placeholder="ボードを検索" autocomplete="off"
                    class="w-full bg-[#F3F4F6] text-gray-800 rounded-sm py-1.5 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-[#EBB73E] text-sm transition-all duration-200">
            </div>
            

            <!-- ログアウトボタン（Mアイコンの外に出す） -->
            <a href="php/logout.php" class="ml-2 text-gray-400 hover:text-white transition" title="ログアウト">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
            
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 py-10">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-x-6 gap-y-10" id="board-grid">
            
            <!-- ① 新規ボード作成カード (常に先頭) -->
            <div class="flex flex-col group cursor-pointer" id="create-board-card">
                <div class="w-full aspect-square bg-[#3A3A3A] rounded-[2rem] border-2 border-dashed border-gray-500 flex flex-col items-center justify-center transition-all duration-200 group-hover:border-[#EBB73E] group-hover:-translate-y-1 group-hover:shadow-xl shadow-md">
                    <div class="w-12 h-12 rounded-full bg-gray-600 flex items-center justify-center text-gray-300 mb-2 group-hover:bg-[#EBB73E] group-hover:text-gray-900 transition-colors duration-200">
                        <i class="fa-solid fa-plus text-xl"></i>
                    </div>
                    <span class="text-sm font-medium tracking-wide text-gray-400 group-hover:text-white transition-colors duration-200">新規作成</span>
                </div>
                <div class="mt-3 px-3">
                    <span class="text-sm font-medium tracking-wide opacity-0">spacer</span>
                </div>
            </div>

            <!-- 既存のボードカードはここにJavaScriptで動的に追加されます -->

        </div>
    </main>

    <!-- ケバブメニュー用のドロップダウン（使い回し用・隠し要素） -->
    <div id="kebab-dropdown" class="absolute hidden bg-[#2A2A2A] text-white rounded shadow-xl w-40 overflow-hidden z-50 border border-gray-700 py-1">
    <button id="menu-rename" class="w-full text-left px-4 py-2 hover:bg-[#3A3A3A] transition text-sm flex items-center gap-2">
        <i class="fa-solid fa-pen text-xs text-gray-400"></i> 名前変更
    </button>
    <button id="menu-duplicate" class="w-full text-left px-4 py-2 hover:bg-[#3A3A3A] transition text-sm flex items-center gap-2">
        <i class="fa-solid fa-copy text-xs text-gray-400"></i> ボードを複製
    </button>
    <button id="menu-tag" class="w-full text-left px-4 py-2 hover:bg-[#3A3A3A] transition text-sm flex items-center gap-2">
        <i class="fa-solid fa-tag text-xs text-gray-400"></i> タグを編集
    </button>
    <div class="border-t border-gray-700 my-1"></div>
    <button id="menu-delete" class="w-full text-left px-4 py-2 hover:bg-red-600 hover:text-white transition text-sm flex items-center gap-2 text-red-400">
        <i class="fa-solid fa-trash text-xs"></i> 削除
    </button>
    </div>

    <!-- ② 新規作成時のモーダル -->
    <div id="create-modal" class="fixed inset-0 bg-black/70 z-[100] hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-[#3A3A3A] p-6 rounded-2xl shadow-2xl w-96 border border-gray-700">
            <h3 class="text-lg font-bold mb-4 text-[#EBB73E] flex items-center gap-2">
                <i class="fa-solid fa-folder-plus"></i> 新規ボード作成
            </h3>
            <input type="text" id="new-board-title" placeholder="ボードの名前を入力..." class="w-full bg-[#F3F4F6] text-gray-800 border-0 p-3 mb-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#EBB73E] font-medium">
            <p id="create-error" class="text-red-400 text-sm mb-2 hidden"></p>
            <div class="flex justify-end gap-3">
                <button id="modal-cancel-btn" class="px-4 py-2 bg-gray-600 text-gray-200 rounded-xl hover:bg-gray-500 transition font-medium">キャンセル</button>
                <button id="modal-create-btn" class="px-4 py-2 bg-[#EBB73E] text-gray-950 rounded-xl hover:bg-[#d6a430] transition font-bold">作成</button>
            </div>
        </div>
    </div>

    <!-- ③ 削除確認用モーダル -->
    <div id="delete-modal" class="fixed inset-0 bg-black/70 z-[100] hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-[#3A3A3A] p-6 rounded-2xl shadow-2xl w-96 border border-gray-700">
            <h3 class="text-lg font-bold mb-2 text-red-400 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> ボードの削除
            </h3>
            <p class="text-gray-300 text-sm mb-5 leading-relaxed">本当にこのボードを削除しますか？<br><span class="text-xs text-gray-400">※この操作は取り消せません。</span></p>
            <div class="flex justify-end gap-3">
                <button id="delete-cancel-btn" class="px-4 py-2 bg-gray-600 text-gray-200 rounded-xl hover:bg-gray-500 transition font-medium">キャンセル</button>
                <button id="delete-confirm-btn" class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition font-bold">削除する</button>
            </div>
        </div>
    </div>

    <!-- ④　リネームモーダル -->
    <div id="rename-modal" class="fixed inset-0 bg-black/70 z-[100] hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-[#3A3A3A] p-6 rounded-2xl shadow-2xl w-96 border border-gray-700">
            <h3 class="text-lg font-bold mb-4 text-[#EBB73E] flex items-center gap-2">
                <i class="fa-solid fa-pen"></i> ボード名を変更
            </h3>
            <input type="text" id="rename-board-title" placeholder="新しい名前を入力..."
                class="w-full bg-[#F3F4F6] text-gray-800 border-0 p-3 mb-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#EBB73E] font-medium">
            <p id="rename-error" class="text-red-400 text-sm mb-3 hidden"></p>
            <div class="flex justify-end gap-3">
                <button id="rename-cancel-btn" class="px-4 py-2 bg-gray-600 text-gray-200 rounded-xl hover:bg-gray-500 transition font-medium">キャンセル</button>
                <button id="rename-confirm-btn" class="px-4 py-2 bg-[#EBB73E] text-gray-950 rounded-xl hover:bg-[#d6a430] transition font-bold">変更する</button>
            </div>
        </div>
    </div>

    <!-- ⑤ タグ管理モーダル -->
<div id="tag-modal" class="fixed inset-0 bg-black/70 z-[100] hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-[#3A3A3A] p-6 rounded-2xl shadow-2xl w-[480px] border border-gray-700">
        <h3 class="text-lg font-bold mb-4 text-[#EBB73E] flex items-center gap-2">
            <i class="fa-solid fa-tag"></i> タグを管理
        </h3>
        <p class="text-sm text-gray-400 mb-2">このボードのタグ</p>
        <div id="tag-list" class="mb-4 flex flex-col gap-2 max-h-48 overflow-y-auto"></div>
        <div class="border-t border-gray-600 pt-4">
            <p class="text-sm text-gray-400 mb-2">新しいタグを作成</p>
            <div class="flex gap-2 items-center">
                <input type="text" id="tag-name-input" placeholder="タグ名..." class="flex-1 bg-[#F3F4F6] text-gray-800 p-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#EBB73E]">
                <div class="flex gap-1" id="tag-color-picker">
                    <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#EBB73E" data-color="#EBB73E"></button>
                    <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#E05555" data-color="#E05555"></button>
                    <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#55A855" data-color="#55A855"></button>
                    <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#5588E0" data-color="#5588E0"></button>
                    <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#9955E0" data-color="#9955E0"></button>
                </div>
                <button id="tag-create-btn" class="px-3 py-2 bg-[#EBB73E] text-gray-950 rounded-lg text-sm font-bold hover:bg-[#d6a430] transition">作成</button>
            </div>
        </div>
        <div class="flex justify-end mt-4">
            <button id="tag-modal-close-btn" class="px-4 py-2 bg-gray-600 text-gray-200 rounded-xl hover:bg-gray-500 transition font-medium">閉じる</button>
        </div>
    </div>
</div>

<!-- ⑦ タグ編集モーダル -->
<div id="tag-edit-modal" class="fixed inset-0 bg-black/50 z-[110] hidden flex items-center justify-center">
    <div class="bg-[#3A3A3A] p-6 rounded-2xl shadow-2xl w-80 border border-gray-700">
        <h3 class="text-lg font-bold mb-4 text-[#EBB73E] flex items-center gap-2">
            <i class="fa-solid fa-pen"></i> タグを編集
        </h3>
        <input type="text" id="tag-edit-name" placeholder="タグ名..." 
            class="w-full bg-[#F3F4F6] text-gray-800 p-2 rounded-lg text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-[#EBB73E]">
        <div class="flex gap-1 mb-4" id="tag-edit-color-picker">
            <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#EBB73E" data-color="#EBB73E"></button>
            <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#E05555" data-color="#E05555"></button>
            <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#55A855" data-color="#55A855"></button>
            <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#5588E0" data-color="#5588E0"></button>
            <button class="w-6 h-6 rounded-full border-2 border-transparent hover:border-white transition" style="background:#9955E0" data-color="#9955E0"></button>
        </div>
        <div class="flex justify-between">
            <button id="tag-edit-delete-btn" class="px-3 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition">削除</button>
            <div class="flex gap-2">
                <button id="tag-edit-cancel-btn" class="px-3 py-2 bg-gray-600 text-gray-200 rounded-lg text-sm hover:bg-gray-500 transition">キャンセル</button>
                <button id="tag-edit-save-btn" class="px-3 py-2 bg-[#EBB73E] text-gray-950 rounded-lg text-sm font-bold hover:bg-[#d6a430] transition">保存</button>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        // bfcacheから復元された場合、強制的にリロードする
        window.location.reload();
    }
});
</script>
    <!-- Custom JS -->
    <script src="js/app.js"></script>
    
</body>
</html>