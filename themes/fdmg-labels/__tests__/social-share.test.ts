import { describe, test, expect, beforeEach, afterEach } from '@jest/globals';
import Socials from '../src/scripts/modules/social-share';

const createDOM = () => {
	document.body.innerHTML = `<ul class="list-social-share" data-module="social-share">
			<li class="list-social-share__item">
				<button class="btn btn--share js--share" data-config="{&quot;type&quot;: &quot;facebook&quot;, &quot;shareText&quot;: &quot;Post without image&quot;}" aria-label="Share via Facebook — This button opens a share-on-Facebook dialog in a new window.">
					Facebook
				</button>
			</li>
			<li class="list-social-share__item">
				<button class="btn btn--share js--share" data-config="{&quot;type&quot;: &quot;twitter&quot;, &quot;shareText&quot;: &quot;Post without image&quot;}" aria-label="Share via Twitter — This button opens a share-on-Twitter dialog in a new window.">
					Twitter
				</button>
			</li>
			<li class="list-social-share__item">
				<button class="btn btn--share js--share" data-config="{&quot;type&quot;: &quot;linkedin&quot;, &quot;shareText&quot;: &quot;Post without image&quot;}" aria-label="Share via LinkedIn — This button opens a share-on-LinkedIn dialog in a new window.">
					LinkedIn
				</button>
			</li>
			<li class="list-social-share__item">
				<a href="javascript:void(0);" class="btn btn--share js--share" target="_blank" rel="noopener" aria-label="Share via E-mail — This opens your e-mail client" data-config="{&quot;type&quot;: &quot;email&quot;, &quot;shareText&quot;: &quot;Post without image&quot;}">
					E-mail
				</a>
			</li>
			<li class="list-social-share__item">
				<button class="btn btn--share js--share" data-config="{&quot;type&quot;: &quot;whatsapp&quot;, &quot;shareText&quot;: &quot;Post without image&quot;}" aria-label="Share via WhatsApp — This button opens a share-on-WhatsApp dialog in a new window.">
					WhatsApp
				</button>
			</li>
		</ul>`;

	const element: HTMLElement = document.querySelector('[data-module="social-share"]');
	return new Socials(element);
};

let windowSpy;

beforeEach(() => {
	windowSpy = jest.spyOn(window, 'window', 'get');

	return createDOM();
});

afterEach(() => {
	windowSpy.mockRestore();
});

describe('Social Share', () => {
	test(`Share button: Should call the window.open function`, () => {
		const element = document.querySelector('[data-module="social-share"]');

		const mockedOpen = jest.fn();

		const originalWindow = { ...window };
		windowSpy.mockImplementation(() => ({
			...originalWindow,
			open: mockedOpen,
		}));

		element.querySelector<HTMLElement>('.js--share').click();

		expect(mockedOpen).toBeCalled();
	});

	test(`Email button: href should contain javascript:void(0); by default`, () => {
		const element = document.querySelector('[data-module="social-share"]');

		let emailButton: HTMLElement;
		element.querySelectorAll<HTMLElement>('.js--share').forEach((el) => {
			if (el.textContent.trim() === 'E-mail') {
				emailButton = el;
			}
		});

		const href = emailButton.getAttribute('href');
		expect(href).not.toMatch(/mailto/);
	});

	test(`Email button: After click, href should change URL from javascript:void to mailto`, () => {
		const element = document.querySelector('[data-module="social-share"]');

		let emailButton: HTMLElement;
		element.querySelectorAll<HTMLElement>('.js--share').forEach((el) => {
			if (el.textContent.trim() === 'E-mail') {
				emailButton = el;
				emailButton.click();
			}
		});

		const href = emailButton.getAttribute('href');
		expect(href).toMatch(/mailto/);
	});
});
