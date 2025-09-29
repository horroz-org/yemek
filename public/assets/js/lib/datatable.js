/**
 * 2025-09-28
 * Toplama bilgisayar maşallah
 * 
 * İnternetten şurdan burdan toplanmıştır,
 * benim yapmadığımı belirtmek ve gönüllere
 * su serpmek isterim.
 * 								~ Pro
 * 
 * 2025-09-29
 * Birkaç ekleme yaptığımı belirtmek isterim.
 * 								~ Pro
 * 
 * 2025-09-29 15:34
 * Buna hiç gerek olmadığını anlamış olmaktayım. 
 * Ulan ben bunu niye yaptım? Hiç gerek yokmuş?
 * Sen mal mısın efendi?
 * 								~ Pro
 */

class DTable {
	constructor(tableId, options = {}) {
		this.table = document.getElementById(tableId);
		this.tbody = this.table.querySelector('tbody');
		this.originalRows = Array.from(this.tbody.querySelectorAll('tr'));
		this.filteredRows = [...this.originalRows];

		this.currentPage = 1;
		this.pageSize = options.pageSize || 10;
		this.currentSort = { column: -1, direction: 'asc' };

		this.searchInput = this.table.querySelector('.dt-search-input');
		this.entriesSelect = this.table.querySelector('.dt-entries-select');
		this.pagination = this.table.querySelector('.dt-pagination');
		this.tableInfo = this.table.querySelector('.dt-table-info');

		this.init();
	}

	init() {
		this.setupEventListeners();
		this.updateDisplay();
	}

	setupEventListeners() {
		this.searchInput.addEventListener('input', (e) => {
			this.search(e.target.value);
		});

		this.entriesSelect.addEventListener('change', (e) => {
			this.pageSize = parseInt(e.target.value);
			this.currentPage = 1;
			this.updateDisplay();
		});

		this.table.querySelectorAll('th').forEach(th => {
			th.addEventListener('click', () => {
				const column = parseInt(th.dataset.column);
				this.sort(column);
			});
		});
	}

	search(query) {
		if (!query.trim()) {
			this.filteredRows = [...this.originalRows];
		} else {
			const searchTerm = query.toLowerCase();
			this.filteredRows = this.originalRows.filter(row => {
				return Array.from(row.cells).some(cell =>
					cell.textContent.toLowerCase().includes(searchTerm)
				);
			});
		}

		this.currentPage = 1;
		this.updateDisplay();
	}

	sort(columnIndex) {
		const isCurrentColumn = this.currentSort.column === columnIndex;
		const newDirection = isCurrentColumn && this.currentSort.direction === 'asc' ? 'desc' : 'asc';

		this.currentSort = { column: columnIndex, direction: newDirection };

		this.table.querySelectorAll('th').forEach(th => {
			th.classList.remove('sort-asc', 'sort-desc');
		});

		const currentHeader = this.table.querySelector(`th[data-column="${columnIndex}"]`);
		currentHeader.classList.add(newDirection === 'asc' ? 'sort-asc' : 'sort-desc');

		this.filteredRows.sort((a, b) => {
			const aVal = a.cells[columnIndex].textContent.trim();
			const bVal = b.cells[columnIndex].textContent.trim();

			const aNum = parseFloat(aVal);
			const bNum = parseFloat(bVal);

			let comparison = 0;

			if (!isNaN(aNum) && !isNaN(bNum)) {
				comparison = aNum - bNum;
			} else {
				const aDate = new Date(aVal);
				const bDate = new Date(bVal);

				if (!isNaN(aDate.getTime()) && !isNaN(bDate.getTime())) {
					comparison = aDate - bDate;
				} else {
					comparison = aVal.localeCompare(bVal);
				}
			}

			return newDirection === 'asc' ? comparison : -comparison;
		});

		this.currentPage = 1;
		this.updateDisplay();
	}

	updateDisplay() {
		this.displayCurrentPage();
		this.updatePagination();
		this.updateInfo();
	}

	displayCurrentPage() {
		this.tbody.innerHTML = '';

		if (this.filteredRows.length === 0) {
			const tr = document.createElement('tr');
			tr.innerHTML = '<td>Kayıt bulunamadı.</td>';
			this.tbody.appendChild(tr);
			return;
		}

		const startIndex = (this.currentPage - 1) * this.pageSize;
		const endIndex = Math.min(startIndex + this.pageSize, this.filteredRows.length);

		for (let i = startIndex; i < endIndex; i++) {
			this.tbody.appendChild(this.filteredRows[i].cloneNode(true));
		}
	}

	updatePagination() {
		const totalPages = Math.ceil(this.filteredRows.length / this.pageSize);
		this.pagination.innerHTML = '';

		if (totalPages <= 1) return;

		const prevBtn = this.createButton('Önceki', this.currentPage > 1, () => {
			if (this.currentPage > 1) {
				this.currentPage--;
				this.updateDisplay();
			}
		});
		this.pagination.appendChild(prevBtn);

		const startPage = Math.max(1, this.currentPage - 2);
		const endPage = Math.min(totalPages, this.currentPage + 2);

		if (startPage > 1) {
			this.pagination.appendChild(this.createButton('1', true, () => this.goToPage(1)));
			if (startPage > 2) {
				this.pagination.appendChild(this.createButton('...', false));
			}
		}

		for (let i = startPage; i <= endPage; i++) {
			const btn = this.createButton(i, true, () => this.goToPage(i));
			if (i === this.currentPage) btn.classList.add('dt-page-active');
			this.pagination.appendChild(btn);
		}

		if (endPage < totalPages) {
			if (endPage < totalPages - 1) {
				this.pagination.appendChild(this.createButton('...', false));
			}
			this.pagination.appendChild(this.createButton(totalPages, true, () => this.goToPage(totalPages)));
		}

		const nextBtn = this.createButton('Sonraki', this.currentPage < totalPages, () => {
			if (this.currentPage < totalPages) {
				this.currentPage++;
				this.updateDisplay();
			}
		});
		this.pagination.appendChild(nextBtn);
	}

	createButton(text, enabled, onClick = null) {
		const btn = document.createElement('button');
		btn.textContent = text;
		btn.disabled = !enabled;
		if (onClick && enabled) btn.addEventListener('click', onClick);
		return btn;
	}

	goToPage(page) {
		this.currentPage = page;
		this.updateDisplay();
	}

	updateInfo() {
		const startIndex = this.filteredRows.length === 0 ? 0 : (this.currentPage - 1) * this.pageSize + 1;
		const endIndex = Math.min(this.currentPage * this.pageSize, this.filteredRows.length);
		const total = this.filteredRows.length;
		const totalOriginal = this.originalRows.length;

		let infoText = '';
		if (total === totalOriginal) {
			infoText = `${totalOriginal} kayıttan ${startIndex}-${endIndex} arası gösteriliyor`;
		} else {
			infoText = `${totalOriginal} kayıttan filtrelenmiş ${total} kayıttan ${startIndex}-${endIndex} arası gösteriliyor`;
		}

		this.tableInfo.textContent = infoText;
	}

	addRow(rowData){
		let trElement = document.createElement("tr");
		for(let i = 0; i < rowData.length; i++){
			let currentTd = document.createElement("td");
			currentTd.innerText = rowData[i];

			trElement.appendChild(currentTd);
		}

		this.originalRows.push(trElement);
		this.filteredRows.push(trElement);
		this.updateDisplay();
	}
}