import { I18n, IModule } from '../../globals';

declare const i18n: I18n;

interface IPlatforms {
	twitter: string;
	facebook: string;
	linkedin: string;
	whatsapp: string;
	email?: string;
	copy?: string;
}

class Socials implements IModule {
	element: HTMLElement;
	shareLinks: HTMLElement[];
	platforms: IPlatforms;

	constructor(element: HTMLElement) {
		this.element = element;
		this.shareLinks = Array.from(this.element.querySelectorAll('.js--share'));
		this.platforms = {
			twitter: 'http://twitter.com/share?url=',
			facebook: 'https://www.facebook.com/sharer/sharer.php?u=',
			linkedin: 'https://www.linkedin.com/shareArticle?mini=true&url=',
			whatsapp: 'https://api.whatsapp.com/send?text=',
		};
		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		this.shareLinks.forEach((el) => {
			el.addEventListener('click', this.handleClick.bind(this));
		});
	}

	async handleClick(event: Event) {
		const currentTarget: HTMLElement = <HTMLElement>event.currentTarget;
		const config: {
			type: keyof IPlatforms;
			shareText: string;
		} = currentTarget.dataset.config ? JSON.parse(currentTarget.dataset.config) : {};

		const text: string = config.shareText || i18n.socialShare;
		const body = `${window.location.href}&text=${text}`;

		const location: IPlatforms = {
			twitter: `${this.platforms.twitter}${body}`,
			facebook: `${this.platforms.facebook}${body}`,
			linkedin: `${this.platforms.linkedin}${body}`,
			whatsapp: `${this.platforms.whatsapp}${text}: ${window.location.href}`,
		};

		if (config.type === 'email') {
			currentTarget.setAttribute('href', `mailto:?subject=${text}&body=${window.location.href}`);
		} else if (config.type === 'copy') {
			try {
				await navigator.clipboard.writeText(window.location.href);
			} catch (err) {
				console.error('Failed to copy: ', err); // eslint-disable-line
			}
		} else if (typeof location[config.type] !== 'undefined') {
			const width = 550;
			const height = 450;
			const top = window.screen.height / 2 - height / 2;

			window.open(
				location[config.type],
				location[config.type].charAt(0).toUpperCase() + location[config.type].slice(1),
				`height=${height}, width=${width} top=${top} toolbar=0, location=0, menubar=0, directories=0, scrollbars=0`,
			);

			event.preventDefault();
		}
	}
}

export default Socials;
