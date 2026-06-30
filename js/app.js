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
        deleteConfirmBtn: document.getElementById('delete-confirm-btn')
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

        const normalizedQuery = filterText.trim().toLowerCase();

        boards.forEach(board => {
            if (normalizedQuery && !board.title.toLowerCase().includes(normalizedQuery)) {
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
            kebabBtn.className = 'text-gray-400 hover:text-white p-1 menu-btn';
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

    const closeCreateModal = () => DOM.createModal.classList.add('hidden');
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
                alert(data.error);
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

    // --- 初期実行：DBから一覧取得 ---
    fetchBoards();
});