import { describe, test, expect, beforeEach } from '@jest/globals';
import PrimaryNav from '../src/scripts/modules/primary-nav';

const createDOM = () => {
	document.body.innerHTML = `<header>
			<nav class="navigation" aria-label="Menu" data-module="primary-nav">
				<ul class="primary-nav">
					<li class="primary-nav__item">
						<a href="#" class="primary-nav__link">Home</a>
					</li>
					<li class="primary-nav__item primary-nav__item--parent primary-nav__item--active primary-nav__item--parent--active primary-nav__item--ancestor--active">
						<a href="#" class="primary-nav__link">Page</a>
						<ul class="primary-nav-sub">
							<li class="primary-nav-sub__item">
								<a href="#" class="primary-nav-sub__link">Page</a>
							</li>
							<li class="primary-nav__item--parent primary-nav__item--active primary-nav__item--parent--active primary-nav__item--ancestor--active primary-nav-sub__item">
								<a href="#" class="primary-nav-sub__link">Page</a>
								<ul class="primary-nav-sub">
									<li class="primary-nav-sub__item">
										<a href="#" class="primary-nav-sub__link">Voorbeeld pagina 2</a>
									</li>
									<li class="primary-nav__item--parent primary-nav__item--ancestor--active primary-nav-sub__item">
										<a href="#" class="primary-nav-sub__link">Page</a>
										<ul class="primary-nav-sub">
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

	const element: HTMLElement = document.querySelector(
		'[data-module="primary-nav"]',
	);
	return new PrimaryNav(element);
};

beforeEach(() => {
	return createDOM();
});

describe('Primary Nav', () => {
	test(`Primary Nav: Adds submenu buttons`, () => {
		const element = document.querySelector('[data-module="primary-nav"]');
		const submenuButton = element.querySelector('.primary-nav__arrow');

		expect(submenuButton).toBeDefined();
		expect(submenuButton.getAttribute('aria-expanded')).toEqual('false');
	});

	test(`Primary Nav: Adds aria attributes`, () => {
		const element = document.querySelector('[data-module="primary-nav"]');
		const submenu = element.querySelector('.primary-nav-sub');

		expect(submenu.getAttribute('aria-hidden')).toBeDefined();
		expect(submenu.getAttribute('aria-hidden')).toEqual('true');
	});

	test(`Primary Nav: Arrow click: handle aria values`, () => {
		const element = document.querySelector('[data-module="primary-nav"]');
		const submenuButton = element.querySelector<HTMLElement>(
			'.primary-nav__arrow',
		);

		submenuButton.click();
		expect(submenuButton.getAttribute('aria-expanded')).toEqual('true');
		expect(
			submenuButton.nextElementSibling.getAttribute('aria-hidden'),
		).toEqual('false');

		submenuButton.click();
		expect(submenuButton.getAttribute('aria-expanded')).toEqual('false');
		expect(
			submenuButton.nextElementSibling.getAttribute('aria-hidden'),
		).toEqual('true');
	});

	test(`Primary Nav: Close all with escape key`, () => {
		const element = document.querySelector('[data-module="primary-nav"]');
		const submenuButton = element.querySelector<HTMLElement>(
			'.primary-nav__arrow',
		);

		// First open a submenu
		submenuButton.click();

		// Close it with the escape key
		const event = new KeyboardEvent('keydown', {
			key: 'Escape',
		});
		element.dispatchEvent(event);

		expect(submenuButton.getAttribute('aria-expanded')).toEqual('false');
	});
});
