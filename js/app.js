document.addEventListener('DOMContentLoaded', () => {
    // --- 状態管理とデータ永続化 (LocalStorage) ---
    // ユーザー指定により、初期状態は「新規作成のみ（ボード配列は空）」にする
    let boards = JSON.parse(localStorage.getItem('merea_boards')) || [];

    // 選択中のターゲットボードIDを保持する変数
    let activeBoardId = null;

    const DOM = {
        grid: document.getElementById('board-grid'),
        createCard: document.getElementById('create-board-card'),
        searchInput: document.getElementById('search-input'),
        // 新規作成モーダル
        createModal: document.getElementById('create-modal'),
        newBoardTitle: document.getElementById('new-board-title'),
        modalCancelBtn: document.getElementById('modal-cancel-btn'),
        modalCreateBtn: document.getElementById('modal-create-btn'),
        // ケバブドロップダウン
        kebabDropdown: document.getElementById('kebab-dropdown'),
        menuRename: document.getElementById('menu-rename'),
        menuDuplicate: document.getElementById('menu-duplicate'),
        menuDelete: document.getElementById('menu-delete'),
        // 削除モーダル
        deleteModal: document.getElementById('delete-modal'),
        deleteCancelBtn: document.getElementById('delete-cancel-btn'),
        deleteConfirmBtn: document.getElementById('delete-confirm-btn')
    };

    // --- ボード一覧のレンダリング ---
    function renderBoards(filterText = '') {
        // 新規作成カード以外を一度すべて削除
        const existingCards = DOM.grid.querySelectorAll('.board-card');
        existingCards.forEach(card => card.remove());

        const normalizedQuery = filterText.trim().toLowerCase();

        boards.forEach(board => {
            // インクリメンタルサーチの判定
            if (normalizedQuery && !board.title.toLowerCase().includes(normalizedQuery)) {
                return; // 検索文字に不一致なら表示スキップ
            }

            // カードの外枠要素
            const card = document.createElement('div');
            card.className = 'flex flex-col group cursor-pointer board-card';
            card.dataset.id = board.id;

            // サムネイル部分
            const thumb = document.createElement('div');
            thumb.className = 'w-full aspect-square bg-[#3A3A3A] rounded-[2rem] overflow-hidden transition-transform duration-200 group-hover:-translate-y-1 group-hover:shadow-xl shadow-md relative flex items-center justify-center border border-gray-700';
            
            // ボード名の頭文字を仮のグラフィックとして真ん中に配置
            const initial = document.createElement('div');
            initial.className = 'text-gray-400 font-bold text-2xl group-hover:text-[#EBB73E] transition-colors duration-200';
            initial.innerText = board.title.charAt(0) || 'B';
            thumb.appendChild(initial);
            card.appendChild(thumb);

            // ボードタイトルとケバブメニューのコンテナ
            const infoArea = document.createElement('div');
            infoArea.className = 'mt-3 flex justify-between items-center w-full px-3';

            const titleSpan = document.createElement('span');
            titleSpan.className = 'text-sm font-medium tracking-wide truncate board-title-text';
            titleSpan.innerText = board.title;
            infoArea.appendChild(titleSpan);

            const kebabBtn = document.createElement('button');
            kebabBtn.className = 'text-gray-400 hover:text-white p-1 menu-btn';
            kebabBtn.innerHTML = '<i class="fa-solid fa-ellipsis-vertical"></i>';
            
            // ケバブボタンクリック時のイベント
            kebabBtn.addEventListener('click', (e) => {
                e.stopPropagation(); // ボード画面への遷移を防ぐ
                openKebabMenu(e, board.id);
            });
            
            infoArea.appendChild(kebabBtn);
            card.appendChild(infoArea);

            // ボードカード自体をクリックした時の画面遷移処理
            card.addEventListener('click', () => {
            window.location.href = `board.php?id=${board.id}`;
            });

            DOM.grid.appendChild(card);
        });

        // 検索文字が入力されている時は、新規作成カードを隠す仕様（設計通り）
        if (normalizedQuery) {
            DOM.createCard.style.display = 'none';
        } else {
            DOM.createCard.style.display = 'flex';
        }
    }

    // --- データ保存処理 ---
    function saveToLocalStorage() {
        localStorage.setItem('merea_boards', JSON.stringify(boards));
    }

    // --- 新規作成モーダルの制御 ---
    DOM.createCard.addEventListener('click', () => {
        DOM.newBoardTitle.value = '';
        DOM.createModal.classList.remove('hidden');
        DOM.newBoardTitle.focus();
    });

    const closeCreateModal = () => DOM.createModal.classList.add('hidden');
    DOM.modalCancelBtn.addEventListener('click', closeCreateModal);
    DOM.createModal.addEventListener('click', (e) => {
        if (e.target === DOM.createModal) closeCreateModal();
    });

    DOM.modalCreateBtn.addEventListener('click', () => {
        const title = DOM.newBoardTitle.value.trim();
        if (!title) return;

        const newBoard = {
            id: 'board_' + Date.now(),
            title: title
        };

        boards.push(newBoard);
        saveToLocalStorage();
        renderBoards(DOM.searchInput.value);
        closeCreateModal();
    });

    // --- ③ インクリメンタルサーチ（リアルタイムフィルタリング） ---
    DOM.searchInput.addEventListener('input', (e) => {
        renderBoards(e.target.value);
    });

    // --- ④ ケバブメニューのポップアップ制御 ---
    function openKebabMenu(e, boardId) {
        activeBoardId = boardId;
        const rect = e.currentTarget.getBoundingClientRect();
        
        // メニューの表示位置を計算（ボタンの直下に配置）
        DOM.kebabDropdown.style.top = `${window.scrollY + rect.bottom + 5}px`;
        DOM.kebabDropdown.style.left = `${window.scrollX + rect.left - 120}px`;
        DOM.kebabDropdown.classList.remove('hidden');
    }

    // 画面の何もないところをクリックしたらケバブメニューを閉じる
    document.addEventListener('click', (e) => {
        if (!DOM.kebabDropdown.contains(e.target)) {
            DOM.kebabDropdown.classList.add('hidden');
        }
    });

    // --- A. ボード名変更 (Rename) ---
    DOM.menuRename.addEventListener('click', () => {
        DOM.kebabDropdown.classList.add('hidden');
        if (!activeBoardId) return;

        const card = DOM.grid.querySelector(`.board-card[data-id="${activeBoardId}"]`);
        if (!card) return;

        const titleText = card.querySelector('.board-title-text');
        const currentTitle = titleText.innerText;

        // タイトル表示部分を一時的にインプットボックスに切り替える
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'inline-rename-input';
        input.value = currentTitle;

        // 元のテキストを隠してインプットを挿入
        titleText.style.display = 'none';
        titleText.parentNode.insertBefore(input, titleText);
        input.focus();
        input.select();

        // 確定処理を行う関数
        const commitRename = () => {
            const newTitle = input.value.trim();
            if (newTitle && newTitle !== currentTitle) {
                const targetBoard = boards.find(b => b.id === activeBoardId);
                if (targetBoard) {
                    targetBoard.title = newTitle;
                    saveToLocalStorage();
                }
            }
            // 再描画してインプットを消去
            renderBoards(DOM.searchInput.value);
        };

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') commitRename();
            if (e.key === 'Escape') renderBoards(DOM.searchInput.value); // キャンセル
        });

        input.addEventListener('blur', commitRename);
    });

    // --- B. ボードの複製 (Duplicate) ---
    DOM.menuDuplicate.addEventListener('click', () => {
        DOM.kebabDropdown.classList.add('hidden');
        if (!activeBoardId) return;

        const sourceBoard = boards.find(b => b.id === activeBoardId);
        if (!sourceBoard) return;

        const duplicatedBoard = {
            id: 'board_' + Date.now(),
            title: `${sourceBoard.title} のコピー`
        };

        // 元のボードのすぐ隣（配列の直後）に挿入する
        const sourceIndex = boards.findIndex(b => b.id === activeBoardId);
        boards.splice(sourceIndex + 1, 0, duplicatedBoard);

        saveToLocalStorage();
        renderBoards(DOM.searchInput.value);
    });

    // --- C. ボード削除 (Delete) ---
    DOM.menuDelete.addEventListener('click', () => {
        DOM.kebabDropdown.classList.add('hidden');
        if (!activeBoardId) return;

        // 確認用のカスタムモーダルを展開
        DOM.deleteModal.classList.remove('hidden');
    });

    const closeDeleteModal = () => DOM.deleteModal.classList.add('hidden');
    DOM.deleteCancelBtn.addEventListener('click', closeDeleteModal);
    DOM.deleteModal.addEventListener('click', (e) => {
        if (e.target === DOM.deleteModal) closeDeleteModal();
    });

    DOM.deleteConfirmBtn.addEventListener('click', () => {
        if (activeBoardId) {
            boards = boards.filter(b => b.id !== activeBoardId);
            saveToLocalStorage();
            renderBoards(DOM.searchInput.value);
        }
        closeDeleteModal();
    });

    // --- 初期実行 ---
    renderBoards();
});


// ==========================================
// ログイン画面用の処理（app.jsの末尾に追記）
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
});