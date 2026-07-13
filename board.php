<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

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
    <title>Merea - Board</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* コルクボード風の背景パターン */
        body, html { margin: 0; padding: 0; overflow: hidden; background-color: #1a1a1a; }
        
        #canvas-container {
        width: 100vw;
        height: 100vh;
        position: relative;
        /* 提供されたコルクボード画像を背景に指定し、リピートさせる */
        background-image: url('CorkBoard02.jpg');
        background-repeat: repeat;
        background-size: 500px auto; /* 画像のサイズ感はお好みで調整してください */
        cursor: grab;
        overflow: hidden;
        }
        #canvas-container:active { cursor: grabbing; }

        #canvas {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            transform-origin: 0 0;
            /* JSで translate を操作して無限キャンバスを実現 */
        }

        /* キャンバス上のカード */
        .card {
            position: absolute;
            background: white;
            padding: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            border-radius: 4px;
            cursor: grab;
            user-select: none;
            transition: transform 0.1s;
        }
        .card:active {
            cursor: grabbing;
            transform: scale(1.05);
            z-index: 50;
            box-shadow: 0 10px 15px rgba(0,0,0,0.4);
        }
        .card img { max-width: 200px; max-height: 200px; object-fit: contain; pointer-events: none; }
        
        /* テキストメモ用のスタイル */
        .card-text { min-width: 150px; min-height: 100px; background: #fff9c4; outline: none; }

        /* メニューのドロップダウン */
        #dropdown-menu { display: none; }
        #dropdown-menu.show { display: block; }
    </style>
</head>
<body class="text-gray-800">

    <div id="canvas-container">
        <div id="canvas">
            </div>
    </div>

    <div class="absolute top-4 left-4 z-50">
        <button id="menu-btn" class="text-2xl font-bold text-yellow-500 bg-gray-900 px-4 py-2 rounded shadow hover:bg-gray-800 transition">
            Merea
        </button>
        <div id="dropdown-menu" class="mt-2 bg-gray-800 text-white rounded shadow-lg w-48 overflow-hidden">
            <a href="home.php" class="block px-4 py-3 hover:bg-gray-700">🏠 ホームに戻る</a>
            <button id="import-img-btn" class="w-full text-left px-4 py-3 hover:bg-gray-700">🖼️ 画像をインポート</button>
            <button id="add-link-btn" class="w-full text-left px-4 py-3 hover:bg-gray-700">🔗 リンクを追加</button>
            <button id="add-text-btn" class="w-full text-left px-4 py-3 hover:bg-gray-700">📝 テキストメモを追加</button>
        </div>
    </div>

    <input type="file" id="file-input" accept="image/png, image/jpeg, image/gif" class="hidden" multiple>

    <div id="trash-zone" class="absolute top-4 right-4 w-16 h-16 bg-red-500 text-white flex items-center justify-center rounded-full opacity-0 pointer-events-none transition-opacity duration-300 z-40 flex-col shadow-lg">
        <span class="text-2xl">🗑️</span>
    </div>

    <div id="viewer-modal" class="fixed inset-0 bg-black/90 z-[100] hidden flex items-center justify-center">
        <button id="viewer-close" class="absolute top-4 left-4 text-white text-4xl p-2 hover:text-gray-400 z-[110]">&lt;</button>
        
        <div class="relative w-full h-full flex items-center justify-center overflow-hidden cursor-move" id="viewer-drag-area">
            <img id="viewer-img" src="" class="max-w-full max-h-full object-contain transition-transform duration-200" style="transform: scale(1) translate(0px, 0px);">
        </div>

        <div class="absolute bottom-6 right-6 flex flex-col gap-2 z-[110]">
            <button id="zoom-reset" class="bg-gray-800 text-white p-3 rounded shadow hover:bg-gray-700">✖</button>
            <button id="zoom-in" class="bg-gray-800 text-white p-3 rounded shadow hover:bg-gray-700">＋</button>
            <button id="zoom-out" class="bg-gray-800 text-white p-3 rounded shadow hover:bg-gray-700">ー</button>
        </div>
    </div>

    <script>
        // --- 状態管理 ---
        const state = {
            canvasX: 0,
            canvasY: 0,
            isPanning: false,
            startX: 0,
            startY: 0,
            draggingCard: null,
            viewerScale: 1,
            viewerX: 0,
            viewerY: 0,
            isViewerDragging: false
        };

        const DOM = {
            container: document.getElementById('canvas-container'),
            canvas: document.getElementById('canvas'),
            menuBtn: document.getElementById('menu-btn'),
            dropdown: document.getElementById('dropdown-menu'),
            importBtn: document.getElementById('import-img-btn'),
            fileInput: document.getElementById('file-input'),
            addTextBtn: document.getElementById('add-text-btn'),
            trashZone: document.getElementById('trash-zone'),
            viewerModal: document.getElementById('viewer-modal'),
            viewerImg: document.getElementById('viewer-img'),
            viewerClose: document.getElementById('viewer-close'),
            zoomIn: document.getElementById('zoom-in'),
            zoomOut: document.getElementById('zoom-out'),
            zoomReset: document.getElementById('zoom-reset'),
            viewerDragArea: document.getElementById('viewer-drag-area')
        };

        // --- ① メニュー制御 ---
        DOM.menuBtn.addEventListener('click', () => {
            DOM.dropdown.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!DOM.menuBtn.contains(e.target) && !DOM.dropdown.contains(e.target)) {
                DOM.dropdown.classList.remove('show');
            }
        });

        // --- 画像追加（エクスプローラー） ---
        DOM.importBtn.addEventListener('click', () => {
            DOM.fileInput.click();
            DOM.dropdown.classList.remove('show');
        });

        DOM.fileInput.addEventListener('change', (e) => {
    const files = Array.from(e.target.files);
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (event) => {
            createCard('image', event.target.result);
        }
        reader.readAsDataURL(file);
    });
});

        // --- テキストメモ追加 ---
        DOM.addTextBtn.addEventListener('click', () => {
            createCard('text', '');
            DOM.dropdown.classList.remove('show');
        });

                // --- カード生成ロジック ---
        function createCard(type, content) {
            const card = document.createElement('div');
            card.className = 'card';
            
            // 画面の中央に出現させるための計算（キャンバスの移動分を相殺）
            const centerX = (window.innerWidth / 2) - state.canvasX - 100;
            const centerY = (window.innerHeight / 2) - state.canvasY - 100;
            card.style.left = `${centerX}px`;
            card.style.top = `${centerY}px`;

            if (type === 'image') {
                const img = document.createElement('img');
                img.src = content;
                card.appendChild(img);
                
                // ★修正点： クリック(click)ではなく、ダブルクリック(dblclick)でビューアを開く
                card.addEventListener('dblclick', (e) => {
                    // ドラッグ中は開かない安全装置（念のため）
                    if (state.draggingCard) return; 
                    openViewer(content);
                });
            } else if (type === 'text') {
                card.classList.add('card-text');
                card.contentEditable = true;
                card.innerText = 'テキストを入力...';
                // テキスト選択の際にドラッグさせないための制御
                card.addEventListener('pointerdown', (e) => e.stopPropagation());
            }

            // ドラッグ用のイベント設定（こちらは1タップ目で発火してOK）
            card.addEventListener('pointerdown', startCardDrag);
            DOM.canvas.appendChild(card);
        }

        // --- 無限キャンバス＆カードのドラッグ制御 ---
        DOM.container.addEventListener('pointerdown', (e) => {
            if (e.target === DOM.container || e.target === DOM.canvas) {
                // キャンバスの移動（パン）開始
                state.isPanning = true;
                state.startX = e.clientX - state.canvasX;
                state.startY = e.clientY - state.canvasY;
                DOM.container.style.cursor = 'grabbing';
            }
        });

        function startCardDrag(e) {
            if (e.button !== 0) return; // 左クリックのみ
            state.draggingCard = e.currentTarget;
            const rect = state.draggingCard.getBoundingClientRect();
            // カード内でのクリック位置のズレを保持
            state.startX = e.clientX - rect.left;
            state.startY = e.clientY - rect.top;
            
            DOM.trashZone.style.opacity = '1'; // ゴミ箱出現
            e.stopPropagation(); // キャンバスのドラッグを発火させない
        }

        window.addEventListener('pointermove', (e) => {
            if (state.isPanning) {
                // キャンバス移動
                state.canvasX = e.clientX - state.startX;
                state.canvasY = e.clientY - state.startY;
                DOM.canvas.style.transform = `translate(${state.canvasX}px, ${state.canvasY}px)`;
            } else if (state.draggingCard) {
                // カード移動 (キャンバスの移動分を考慮して座標を計算)
                const newX = e.clientX - state.startX - state.canvasX;
                const newY = e.clientY - state.startY - state.canvasY;
                state.draggingCard.style.left = `${newX}px`;
                state.draggingCard.style.top = `${newY}px`;

                // ゴミ箱の当たり判定（ホバー状態のフィードバック）
                const trashRect = DOM.trashZone.getBoundingClientRect();
                if (e.clientX > trashRect.left && e.clientX < trashRect.right &&
                    e.clientY > trashRect.top && e.clientY < trashRect.bottom) {
                    DOM.trashZone.classList.add('bg-red-700', 'scale-110');
                } else {
                    DOM.trashZone.classList.remove('bg-red-700', 'scale-110');
                }
            }
        });

        window.addEventListener('pointerup', (e) => {
            if (state.isPanning) {
                state.isPanning = false;
                DOM.container.style.cursor = 'grab';
            }

            if (state.draggingCard) {
                // ゴミ箱の当たり判定でドロップ処理
                const trashRect = DOM.trashZone.getBoundingClientRect();
                if (e.clientX > trashRect.left && e.clientX < trashRect.right &&
                    e.clientY > trashRect.top && e.clientY < trashRect.bottom) {
                    state.draggingCard.remove(); // カード削除
                }
                
                state.draggingCard = null;
                DOM.trashZone.style.opacity = '0'; // ゴミ箱隠す
                DOM.trashZone.classList.remove('bg-red-700', 'scale-110');
                
                // クリック判定との競合を避けるための微小なディレイ
                setTimeout(() => { state.draggingCard = null; }, 50);
            }
        });

        // --- ⑤ 詳細ビューア制御 ---
        function openViewer(src) {
            DOM.viewerImg.src = src;
            DOM.viewerModal.classList.remove('hidden');
            resetViewer();
        }

        function closeViewer() {
            DOM.viewerModal.classList.add('hidden');
            DOM.viewerImg.src = '';
        }

        function updateViewerTransform() {
            DOM.viewerImg.style.transform = `translate(${state.viewerX}px, ${state.viewerY}px) scale(${state.viewerScale})`;
        }

        function resetViewer() {
            state.viewerScale = 1;
            state.viewerX = 0;
            state.viewerY = 0;
            updateViewerTransform();
        }

        DOM.viewerClose.addEventListener('click', closeViewer);
        DOM.viewerModal.addEventListener('click', (e) => {
            if (e.target === DOM.viewerDragArea) closeViewer(); // 背景クリックで閉じる
        });

        DOM.zoomIn.addEventListener('click', () => { state.viewerScale *= 1.2; updateViewerTransform(); });
        DOM.zoomOut.addEventListener('click', () => { state.viewerScale /= 1.2; updateViewerTransform(); });
        DOM.zoomReset.addEventListener('click', resetViewer);

        // ビューア内の画像ドラッグ（拡大時の移動）
        DOM.viewerDragArea.addEventListener('pointerdown', (e) => {
            if (e.target !== DOM.viewerImg) return;
            state.isViewerDragging = true;
            state.startX = e.clientX - state.viewerX;
            state.startY = e.clientY - state.viewerY;
            e.preventDefault();
        });

        window.addEventListener('pointermove', (e) => {
            if (state.isViewerDragging) {
                state.viewerX = e.clientX - state.startX;
                state.viewerY = e.clientY - state.startY;
                updateViewerTransform();
            }
        });

        window.addEventListener('pointerup', () => {
            state.isViewerDragging = false;
        });

    </script>
        <!-- bfcache対策を追加 -->
    <script>
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>
</body>
</html>