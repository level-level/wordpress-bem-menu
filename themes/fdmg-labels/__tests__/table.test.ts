import { describe, test, expect, beforeEach } from '@jest/globals';
import Table from '../src/scripts/modules/table';

const createDOM = () => {
	document.body.innerHTML = `<div data-module="table">
			<table>
				<thead>
					<tr>
						<th>Name</th>
						<th>City</th>
						<th>Country</th>
						<th>Age</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Kevin</td>
						<td>Vlaardingen</td>
						<td>The Netherlands</td>
						<td>32</td>
					</tr>
					<tr>
						<td>Jan</td>
						<td>Rotterdam</td>
						<td>The Netherlands</td>
						<td>29</td>
					</tr>
				</tbody>
			</table>
		</div>`;

	const element: HTMLElement = document.querySelector('[data-module="table"]');
	return new Table(element);
};

beforeEach(() => {
	return createDOM();
});

describe('Table', () => {
	test(`Table: Should add a "data-th" property to all table cells based on the existing table head elements`, () => {
		const element = document.querySelector('[data-module="table"]');

		const firstItem = element.querySelector('tbody tr td').getAttribute('data-th');
		expect(firstItem).toMatch(/Name/);
	});
});
