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
        .card img { max-width: 350px; max-height: 350px; object-fit: contain; pointer-events: none; }
        
        /* テキストメモ用のスタイル */
        .card-text { 
        width: 200px;
        min-height: 100px;
        max-height: 200px;
        overflow-y: auto;
        background: #fffad2; 
        outline: none;
        word-break: break-all;
        white-space: pre-wrap;
        overflow-wrap: break-word;
        user-select: text;
        }

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

    <div id="trash-zone" class="absolute top-4 right-4 w-24 h-24 flex items-center justify-center rounded-full opacity-0 pointer-events-none transition-opacity duration-300 z-40 shadow-lg">
        <img src="trash_can.png" class="w-16 h-16 object-contain pointer-events-none">
    </div>

    <div id="viewer-modal" class="fixed inset-0 bg-black/90 z-[100] hidden flex items-center justify-center">
        <button id="viewer-close" class="absolute top-4 left-4 text-white text-4xl p-2 hover:text-gray-400 z-[110]">&lt;</button>
        
    <div class="relative w-full h-full flex items-center justify-center overflow-hidden cursor-grab" id="viewer-drag-area">
            <img id="viewer-img" src="" class="max-w-full max-h-full object-contain transition-transform duration-200" style="transform: scale(1) translate(0px, 0px);">
        </div>

        <div class="absolute bottom-6 right-6 flex flex-col gap-2 z-[110]">
            <button id="zoom-reset" class="bg-gray-800 text-white p-3 rounded shadow hover:bg-gray-700">✖</button>
            <button id="zoom-in" class="bg-gray-800 text-white p-3 rounded shadow hover:bg-gray-700">＋</button>
            <button id="zoom-out" class="bg-gray-800 text-white p-3 rounded shadow hover:bg-gray-700">ー</button>
        </div>
    </div>


    <!-- URL入力モーダル -->
    <div id="url-modal" class="fixed inset-0 bg-black/90 z-[100] hidden flex items-center justify-center">
        <div class="bg-gray-900 p-6 rounded-xl shadow-2xl w-96">
            <h3 class="text-lg font-bold mb-4 text-yellow-500">🔗 リンクを追加</h3>
            <input type="text" id="url-input" placeholder="https://..." 
                class="w-full bg-gray-800 text-white border border-gray-600 p-3 mb-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
            <div class="flex justify-end gap-3">
                <button id="url-cancel-btn" class="px-4 py-2 bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">キャンセル</button>
                <button id="url-confirm-btn" class="px-4 py-2 bg-yellow-500 text-gray-950 rounded-lg hover:bg-yellow-400 transition font-bold">追加</button>
            </div>
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
            isViewerDragging: false,
            viewerStartX: 0,
            viewerStartY: 0
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
            viewerDragArea: document.getElementById('viewer-drag-area'),
            urlModal: document.getElementById('url-modal'),
            urlInput: document.getElementById('url-input'),
            urlCancelBtn: document.getElementById('url-cancel-btn'),
            urlConfirmBtn: document.getElementById('url-confirm-btn'),
            addLinkBtn: document.getElementById('add-link-btn')
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

        DOM.fileInput.addEventListener('change', async (e) => {
            const files = Array.from(e.target.files);
            const boardId = new URLSearchParams(location.search).get('id');

            for (const file of files) {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('board_id', boardId);

                const res = await fetch('php/cards_upload.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.error) {
                    alert(data.error);
                    continue;
                }

                createCard('image', data.file_path, data.card_id);
            }
            e.target.value = '';
});

        // --- テキストメモ追加 ---
        DOM.addTextBtn.addEventListener('click', async () => {
            const boardId = new URLSearchParams(location.search).get('id');
            const res = await fetch('php/cards_create_text.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ board_id: boardId })
            });
            const data = await res.json();
            if (data.error) { alert(data.error); return; }
            createCard('text', '', data.card_id);
            DOM.dropdown.classList.remove('show');
});

// --- リンク追加モーダルの制御 ---
DOM.addLinkBtn.addEventListener('click', () => {
    DOM.urlInput.value = '';
    DOM.urlModal.classList.remove('hidden');
    DOM.urlInput.focus();
    DOM.dropdown.classList.remove('show');
});

const closeUrlModal = () => DOM.urlModal.classList.add('hidden');
DOM.urlCancelBtn.addEventListener('click', closeUrlModal);
DOM.urlModal.addEventListener('click', (e) => {
    if (e.target === DOM.urlModal) closeUrlModal();
});


// --- URLを確定してカードを作成 ---
DOM.urlConfirmBtn.addEventListener('click', async () => {
    const url = DOM.urlInput.value.trim();
    if (!url) return;

    const boardId = new URLSearchParams(location.search).get('id');
    const res = await fetch('php/cards_add_url.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ board_id: boardId, url })
    });
    const data = await res.json();

    if (data.error) {
        alert(data.error);
        return;
    }

    createCard('url', { url: data.url, title: data.title, thumbnail: data.thumbnail }, data.card_id);
    closeUrlModal();
});

         // --- カード生成ロジック ---
            function createCard(type, content, cardId = null, x = null, y = null,z = 0) {
        const card = document.createElement('div');
        card.className = 'card';
        card.dataset.cardId = cardId;

        const posX = x !== null ? x : (window.innerWidth / 2) - state.canvasX - 100;
        const posY = y !== null ? y : (window.innerHeight / 2) - state.canvasY - 100;
        card.style.left = `${posX}px`;
        card.style.top = `${posY}px`;
        card.style.zIndex = z;  // ← 追加


        if (type === 'image') {
            const img = document.createElement('img');
            img.src = content;
            card.appendChild(img);
            card.addEventListener('dblclick', (e) => {
                if (state.draggingCard) return;
                openViewer(content);
            });
        } else if (type === 'text') {
            card.classList.add('card-text');
            card.contentEditable = true;
            if (content) card.innerText = content;
            card.addEventListener('pointerdown', (e) => e.stopPropagation());
            card.addEventListener('blur', async () => {
                const cardId = card.dataset.cardId;
                if (!cardId) return;
                await fetch('php/cards_update_text.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ card_id: cardId, content: card.innerText })
                });
            });
        }else if (type === 'url') {
            card.style.width = '300px';
            card.style.padding = '0';
            card.style.overflow = 'hidden';

            if (content.thumbnail) {
                const img = document.createElement('img');
                img.src = content.thumbnail;
                img.style.width = '100%';
                img.style.height = '169px';
                img.style.objectFit = 'cover';
                img.style.pointerEvents = 'none';
                card.appendChild(img);
            }

            const info = document.createElement('div');
            info.style.padding = '8px';
            info.style.fontSize = '12px';
            info.style.color = '#333';
            info.style.overflow = 'hidden';
            info.style.whiteSpace = 'nowrap';
            info.style.textOverflow = 'ellipsis';
            info.innerText = content.title || content.url;
            card.appendChild(info);

            card.addEventListener('dblclick', () => {
                window.open(content.url, '_blank');
            });
        }
        card.addEventListener('pointerdown', startCardDrag);
        DOM.canvas.appendChild(card);
    }




        // startCardDragを差し替え
            function startCardDrag(e) {
                if (e.button !== 0) return;
                
                const now = Date.now();
                if (now - (e.currentTarget._lastClick || 0) < 300) {
                    e.currentTarget._lastClick = 0;
                    return;
                }
                e.currentTarget._lastClick = now;

                state.draggingCard = e.currentTarget;
                
                // 現在の最大z_indexを取得して+1する
                const maxZ = Math.max(...Array.from(document.querySelectorAll('.card')).map(c => parseInt(c.style.zIndex) || 0));
                state.draggingCard.style.zIndex = maxZ + 1;
                
                const rect = state.draggingCard.getBoundingClientRect();
                state.startX = e.clientX - rect.left;
                state.startY = e.clientY - rect.top;
                
                DOM.trashZone.style.opacity = '1';
                e.stopPropagation();
}

        window.addEventListener('pointermove', (e) => {
    if (state.isPanning) {
        state.canvasX = e.clientX - state.startX;
        state.canvasY = e.clientY - state.startY;
        DOM.canvas.style.transform = `translate(${state.canvasX}px, ${state.canvasY}px)`;
    } else if (state.draggingCard) {
        let newX = e.clientX - state.startX - state.canvasX;
        let newY = e.clientY - state.startY - state.canvasY;

        // キャンバスの境界制限
        const cardW = state.draggingCard.offsetWidth;
        const cardH = state.draggingCard.offsetHeight;
        newX = Math.max(0, Math.min(newX, 3840 - cardW));
        newY = Math.max(0, Math.min(newY, 2160 - cardH));

        state.draggingCard.style.left = `${newX}px`;
        state.draggingCard.style.top = `${newY}px`;


        const trashRect = DOM.trashZone.getBoundingClientRect();
        if (e.clientX > trashRect.left && e.clientX < trashRect.right &&
            e.clientY > trashRect.top && e.clientY < trashRect.bottom) {
            DOM.trashZone.classList.add('bg-red-700', 'scale-110');
        } else {
            DOM.trashZone.classList.remove('bg-red-700', 'scale-110');
        }                                        // ← ここで内側のif終わり
    } else if (state.isViewerDragging) {         // ← ここが外側のelse if
        state.viewerX = e.clientX - state.viewerStartX;
        state.viewerY = e.clientY - state.viewerStartY;
        updateViewerTransform();
    }
});

// --- ページ読み込み時にカードを復元 ---
async function loadCards() {
    const boardId = new URLSearchParams(location.search).get('id');
    if (!boardId) return;

    const res = await fetch(`php/cards_load.php?board_id=${boardId}`);
    const cards = await res.json();

    cards.forEach(card => {
    if (card.type === 'image') {
        createCard('image', card.file_path, card.id, card.pos_x, card.pos_y, card.z_index);
    } else if (card.type === 'text') {
        createCard('text', card.content, card.id, card.pos_x, card.pos_y, card.z_index);
    } else if (card.type === 'url') {
        createCard('url', { url: card.url, title: card.title, thumbnail: card.thumbnail_url }, card.id, card.pos_x, card.pos_y, card.z_index);
    }
});
}

loadCards();

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

         // ビューア内ドラッグ
        DOM.viewerDragArea.addEventListener('pointerdown', (e) => {
            state.isViewerDragging = true;
            state.viewerStartX = e.clientX - state.viewerX;
            state.viewerStartY = e.clientY - state.viewerY;
            e.preventDefault();
        });

        window.addEventListener('pointerup', async (e) => {
            state.isViewerDragging = false;  // ← 追加
            if (state.isPanning) {
                state.isPanning = false;
                DOM.container.style.cursor = 'grab';
            }

    if (state.draggingCard) {
        const trashRect = DOM.trashZone.getBoundingClientRect();
        if (e.clientX > trashRect.left && e.clientX < trashRect.right &&
            e.clientY > trashRect.top && e.clientY < trashRect.bottom) {
            const cardId = state.draggingCard.dataset.cardId;
            if (cardId) {
                await fetch('php/cards_delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ card_id: cardId })
                });
            }
            state.draggingCard.remove();
        } else {
            const cardId = state.draggingCard.dataset.cardId;
            const x = parseFloat(state.draggingCard.style.left);
            const y = parseFloat(state.draggingCard.style.top);
            const z = parseInt(state.draggingCard.style.zIndex) || 0;
            if (cardId) {
                await fetch('php/cards_save.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ card_id: cardId, x, y, z })
                });
            }
        }

        state.draggingCard = null;
        DOM.trashZone.style.opacity = '0';
        DOM.trashZone.classList.remove('bg-red-700', 'scale-110');
    }
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