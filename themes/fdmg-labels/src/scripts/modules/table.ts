import { IModule } from '../../globals';

class Table implements IModule {
	element: HTMLElement;
	tableHeader: HTMLTableCellElement[];
	tableRows: HTMLTableCellElement[];

	constructor(element: HTMLElement) {
		this.element = element;
		this.tableHeader = Array.from(this.element.querySelectorAll('th'));
		this.tableRows = Array.from(this.element.querySelectorAll('tbody tr, tfoot tr'));
		this.initialize();
	}

	initialize() {
		this.addTableHeaderProp();
	}

	addTableHeaderProp() {
		const headerItems: string[] = [];

		for (const value of this.tableHeader) {
			if (value.textContent) {
				headerItems.push(value.textContent);
			}
		}

		for (const value of this.tableRows) {
			const td: HTMLElement[] = Array.from(value.querySelectorAll('td'));

			for (let i = 0; i < td.length; i++) {
				const ele = td[i];
				ele.setAttribute('data-th', headerItems[i]);
			}
		}
	}
}

export default Table;
