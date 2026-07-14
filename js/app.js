document.addEventListener('DOMContentLoaded', () => {
    // --- 状態管理（DBから取得したデータを保持） ---
    let boards = [];
    let activeBoardId = null;

    const DOM = {
        grid: document.getElementById('board-grid'),
        createCard: document.getElementById('create-board-card'),
        searchInput: document.getElementById('search-input'),
        createModal: document.getElementById('create-modal'),
        newBoardTitle: document.getElementById('new-board-title'),
        modalCancelBtn: document.getElementById('modal-cancel-btn'),
        modalCreateBtn: document.getElementById('modal-create-btn'),
        kebabDropdown: document.getElementById('kebab-dropdown'),
        menuRename: document.getElementById('menu-rename'),
        menuDuplicate: document.getElementById('menu-duplicate'),
        menuDelete: document.getElementById('menu-delete'),
        deleteModal: document.getElementById('delete-modal'),
        deleteCancelBtn: document.getElementById('delete-cancel-btn'),
        deleteConfirmBtn: document.getElementById('delete-confirm-btn'),
        renameModal: document.getElementById('rename-modal'),
        renameBoardTitle: document.getElementById('rename-board-title'),
        renameCancelBtn: document.getElementById('rename-cancel-btn'),
        renameConfirmBtn: document.getElementById('rename-confirm-btn')
    };

    // --- ① DBからボード一覧を取得する ---
    async function fetchBoards() {
        try {
            const res = await fetch('php/boards_list.php');
            const data = await res.json();
            if (data.error) {
                console.error(data.error);
                return;
            }
            // DBのカラム名(name)をJS側で使っているtitleに合わせる
            boards = data.map(b => ({ id: b.id, title: b.name }));
            renderBoards();
        } catch (err) {
            console.error('ボード取得エラー:', err);
        }
    }

    // --- ボード一覧のレンダリング ---
    function renderBoards(filterText = '') {
        const existingCards = DOM.grid.querySelectorAll('.board-card');
        existingCards.forEach(card => card.remove());

            // 変更後
        const normalize = str => str.trim().toLowerCase().normalize('NFKC');
        const normalizedQuery = normalize(filterText);

        boards.forEach(board => {
            if (normalizedQuery && !normalize(board.title).includes(normalizedQuery)) {
                return;
            }

            const card = document.createElement('div');
            card.className = 'flex flex-col group cursor-pointer board-card';
            card.dataset.id = board.id;

            const thumb = document.createElement('div');
            thumb.className = 'w-full aspect-square bg-[#3A3A3A] rounded-[2rem] overflow-hidden transition-transform duration-200 group-hover:-translate-y-1 group-hover:shadow-xl shadow-md relative flex items-center justify-center border border-gray-700';

            const initial = document.createElement('div');
            initial.className = 'text-gray-400 font-bold text-2xl group-hover:text-[#EBB73E] transition-colors duration-200';
            initial.innerText = board.title.charAt(0) || 'B';
            thumb.appendChild(initial);
            card.appendChild(thumb);

            const infoArea = document.createElement('div');
            infoArea.className = 'mt-3 flex justify-between items-center w-full px-3';

            const titleSpan = document.createElement('span');
            titleSpan.className = 'text-sm font-medium tracking-wide truncate board-title-text';
            titleSpan.innerText = board.title;
            infoArea.appendChild(titleSpan);

            const kebabBtn = document.createElement('button');
            kebabBtn.className = 'text-gray-400 hover:text-white p-3 menu-btn';
            kebabBtn.innerHTML = '<i class="fa-solid fa-ellipsis-vertical"></i>';

            kebabBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                openKebabMenu(e, board.id);
            });

            infoArea.appendChild(kebabBtn);
            card.appendChild(infoArea);

            card.addEventListener('click', () => {
                window.location.href = `board.php?id=${board.id}`;
            });

            DOM.grid.appendChild(card);
        });

        if (normalizedQuery) {
            DOM.createCard.style.display = 'none';
        } else {
            DOM.createCard.style.display = 'flex';
        }
    }

    // --- 新規作成モーダルの制御 ---
    DOM.createCard.addEventListener('click', () => {
        DOM.newBoardTitle.value = '';
        DOM.createModal.classList.remove('hidden');
        DOM.newBoardTitle.focus();
    });

    const closeCreateModal = () => {
    DOM.createModal.classList.add('hidden');
    document.getElementById('create-error').classList.add('hidden');
    };
    DOM.modalCancelBtn.addEventListener('click', closeCreateModal);
    DOM.createModal.addEventListener('click', (e) => {
        if (e.target === DOM.createModal) closeCreateModal();
    });

    // --- ② DBにボードを新規作成する ---
    DOM.modalCreateBtn.addEventListener('click', async () => {
        const title = DOM.newBoardTitle.value.trim();
        if (!title) return;

        try {
            const res = await fetch('php/boards_create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: title })
            });
            const data = await res.json();

            if (data.error) {
            const errEl = document.getElementById('create-error');
            errEl.textContent = data.error;
            errEl.classList.remove('hidden');
            return;
        }

            // 作成成功したらリストに追加
            boards.push({ id: data.id, title: data.name });
            renderBoards(DOM.searchInput.value);
            closeCreateModal();
        } catch (err) {
            console.error('ボード作成エラー:', err);
        }
    });

    // --- インクリメンタルサーチ ---
    DOM.searchInput.addEventListener('input', (e) => {
        renderBoards(e.target.value);
    });

    // --- ケバブメニュー（リネーム・複製・削除はまだDB未対応） ---
    function openKebabMenu(e, boardId) {
        activeBoardId = boardId;
        const rect = e.currentTarget.getBoundingClientRect();
        DOM.kebabDropdown.style.top = `${window.scrollY + rect.bottom + 5}px`;
        DOM.kebabDropdown.style.left = `${window.scrollX + rect.left - 120}px`;
        DOM.kebabDropdown.classList.remove('hidden');
    }

    document.addEventListener('click', (e) => {
        if (!DOM.kebabDropdown.contains(e.target)) {
            DOM.kebabDropdown.classList.add('hidden');
        }
    });
// --- ケバブメニュー（リネーム・複製・削除はまだDB未対応） ---
    function openKebabMenu(e, boardId) {
        activeBoardId = boardId;
        const rect = e.currentTarget.getBoundingClientRect();
        DOM.kebabDropdown.style.top = `${window.scrollY + rect.bottom + 5}px`;
        DOM.kebabDropdown.style.left = `${window.scrollX + rect.left - 120}px`;
        DOM.kebabDropdown.classList.remove('hidden');
    }

    document.addEventListener('click', (e) => {
        if (!DOM.kebabDropdown.contains(e.target)) {
            DOM.kebabDropdown.classList.add('hidden');
        }
    });

    // --- ここから追加 ---

    // --- 削除モーダルを開く ---
    DOM.menuDelete.addEventListener('click', () => {
        DOM.kebabDropdown.classList.add('hidden');
        if (!activeBoardId) return;
        DOM.deleteModal.classList.remove('hidden');
    });

    const closeDeleteModal = () => DOM.deleteModal.classList.add('hidden');
    DOM.deleteCancelBtn.addEventListener('click', closeDeleteModal);
    DOM.deleteModal.addEventListener('click', (e) => {
        if (e.target === DOM.deleteModal) closeDeleteModal();
    });

    // --- 削除を確定（DBに削除リクエストを送る） ---
    DOM.deleteConfirmBtn.addEventListener('click', async () => {
        if (!activeBoardId) {
            closeDeleteModal();
            return;
        }

        try {
            const res = await fetch('php/boards_delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: activeBoardId })
            });
            const data = await res.json();

            if (data.error) {
                alert(data.error);
                return;
            }

            boards = boards.filter(b => b.id !== activeBoardId);
            renderBoards(DOM.searchInput.value);
        } catch (err) {
            console.error('ボード削除エラー:', err);
        } finally {
            closeDeleteModal();
        }
    });

// --- リネームモーダルを開く ---
DOM.menuRename.addEventListener('click', () => {
    DOM.kebabDropdown.classList.add('hidden');
    if (!activeBoardId) return;
    const board = boards.find(b => b.id === activeBoardId);
    DOM.renameBoardTitle.value = board ? board.title : '';
    DOM.renameModal.classList.remove('hidden');
    DOM.renameBoardTitle.focus();
});

const closeRenameModal = () => DOM.renameModal.classList.add('hidden');
DOM.renameCancelBtn.addEventListener('click', closeRenameModal);
DOM.renameModal.addEventListener('click', (e) => {
    if (e.target === DOM.renameModal) closeRenameModal();
});

// --- リネームを確定（DBに更新リクエストを送る） ---
DOM.renameConfirmBtn.addEventListener('click', async () => {
    const newTitle = DOM.renameBoardTitle.value.trim();
    if (!newTitle || !activeBoardId) return;

    try {
        const res = await fetch('php/boards_rename.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: activeBoardId, name: newTitle })
        });
        const data = await res.json();

        if (data.error) {
            alert(data.error);
            return;
        }

        const board = boards.find(b => b.id === activeBoardId);
        if (board) board.title = newTitle;
        renderBoards(DOM.searchInput.value);
    } catch (err) {
        console.error('ボードリネームエラー:', err);
    } finally {
        closeRenameModal();
    }
});


// --- ボードを複製（DBに更新リクエストを送る） ---
DOM.menuDuplicate.addEventListener('click', async () => {
    if (!activeBoardId) return;

    try {
        const res = await fetch('php/boards_duplicate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: activeBoardId })
        });
        const data = await res.json();

        if (data.error) {
            alert(data.error);
            return;
        }
        boards.push({ id: data.id, title: data.name });
        renderBoards(DOM.searchInput.value);
        DOM.kebabDropdown.classList.add('hidden');
    } catch (err) {
        console.error('ボード複製エラー:', err);
    } 
});


    // --- 初期実行：DBから一覧取得 ---
    fetchBoards();
});
