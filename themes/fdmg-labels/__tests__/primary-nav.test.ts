import { describe, test, expect, beforeAll } from '@jest/globals';
import PrimaryNav from '../src/scripts/modules/primary-nav';

let element: HTMLElement;

beforeAll(() => {
	document.body.innerHTML = `<header>
			<nav class="navigation" aria-label="Menu" data-module="primary-nav">
				<ul class="primary-nav">
					<li class="primary-nav__item">
						<a href="#" class="primary-nav__link">Home</a>
					</li>
					<li class="primary-nav__item primary-nav__item--parent primary-nav__item--active primary-nav__item--parent--active primary-nav__item--ancestor--active">
						<a href="#" class="primary-nav__link">Page</a>
						<button class="btn primary-nav__toggle" aria-expanded="false" aria-label="Submenu"><button>
						<ul class="primary-nav-sub" aria-hidden="true">
							<li class="primary-nav-sub__item">
								<a href="#" class="primary-nav-sub__link">Page</a>
							</li>
							<li class="primary-nav__item--parent primary-nav__item--active primary-nav__item--parent--active primary-nav__item--ancestor--active primary-nav-sub__item">
								<a href="#" class="primary-nav-sub__link">Page</a>
								<button class="btn primary-nav__toggle" aria-expanded="false" aria-label="Submenu"><button>
								<ul class="primary-nav-sub" aria-hidden="true">
									<li class="primary-nav-sub__item">
										<a href="#" class="primary-nav-sub__link">Voorbeeld pagina 2</a>
									</li>
									<li class="primary-nav__item--parent primary-nav__item--ancestor--active primary-nav-sub__item">
										<a href="#" class="primary-nav-sub__link">Page</a>
										<button class="btn primary-nav__toggle" aria-expanded="false" aria-label="Submenu"><button>
										<ul class="primary-nav-sub" aria-hidden="true">
											<li class="primary-nav-sub__item">
												<a href="#" class="primary-nav-sub__link">Page</a>
											</li>
											<li class="primary-nav-sub__item">
												<a href="#" class="primary-nav-sub__link">Page</a>
											</li>
										</ul>
									</li>
									<li class="primary-nav__item--active  primary-nav-sub__item">
										<a href="#" class="primary-nav-sub__link">Page</a>
									</li>
								</ul>
							</li>
							<li class="primary-nav__item--active  primary-nav-sub__item">
								<a href="#" class="primary-nav-sub__link">Page</a>
							</li>
						</ul>
					</li>
					<li class="primary-nav__item">
						<a href="#" class="primary-nav__link">Page</a>
					</li>
				</ul>
			</nav>
		</header>`;
});

describe('Primary Nav', () => {
	beforeAll(() => {
		element = document.querySelector('[data-module="primary-nav"]')!;
		new PrimaryNav(element);
	});

	test(`Primary Nav: Arrow click: handle aria values`, () => {
		const submenuButton = element.querySelector<HTMLElement>('.primary-nav__toggle')!;

		submenuButton.click();
		expect(submenuButton.getAttribute('aria-expanded')).toEqual('true');
		expect(submenuButton.nextElementSibling?.getAttribute('aria-hidden')).toEqual('false');

		submenuButton.click();
		expect(submenuButton.getAttribute('aria-expanded')).toEqual('false');
		expect(submenuButton.nextElementSibling?.getAttribute('aria-hidden')).toEqual('true');
	});

	test(`Primary Nav: Close all with escape key`, () => {
		const submenuButton = element.querySelector<HTMLElement>('.primary-nav__toggle')!;

		// First open a submenu
		submenuButton.click();

		// Close it with the escape key
		const event = new KeyboardEvent('keydown', {
			key: 'Escape',
		});
		document.dispatchEvent(event);

		expect(submenuButton.getAttribute('aria-expanded')).toEqual('false');
	});
});
