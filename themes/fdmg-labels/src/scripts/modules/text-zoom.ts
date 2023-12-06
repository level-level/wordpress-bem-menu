import { IModule } from '../../globals';

class TextZoom implements IModule {
	element: HTMLElement;
	initialSize: number;
	default: number;
	zoomFactor: number;
	zoomLevels: { factor: number; class: string }[];

	constructor(element: HTMLElement) {
		this.element = element;
		this.initialSize = this.element.offsetWidth;
		this.default = 16;
		this.zoomFactor = this.element.offsetWidth / this.default;
		this.zoomLevels = [
			{ factor: 1.75, class: 'text-zoom-175' },
			{ factor: 1.5, class: 'text-zoom-150' },
			{ factor: 1.25, class: 'text-zoom-125' },
			{ factor: 1, class: 'text-zoom-100' },
		];

		this.initialize();
	}

	setZoomClass() {
		this.zoomFactor = this.element.offsetWidth / this.default;

		this.zoomLevels.forEach((zoomLevel) => {
			document.body.classList.remove(zoomLevel.class);

			if (this.zoomFactor >= zoomLevel.factor) {
				document.body.classList.add(zoomLevel.class);
			}
		});
	}

	initialize() {
		this.setZoomClass();

		setInterval(() => {
			if (this.element.offsetWidth !== this.initialSize) {
				this.initialSize = this.element.offsetWidth;
				this.setZoomClass();
			}
		}, 500);
	}
}

export default TextZoom;
