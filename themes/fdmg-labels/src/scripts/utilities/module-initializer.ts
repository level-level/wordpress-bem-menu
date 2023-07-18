const ModuleInitializer = (modules: { [key: string]: any }) => {
	document.querySelectorAll<HTMLElement>('[data-module]').forEach((element) => {
		if (element === undefined) {
			return;
		}

		const name = element.dataset.module as string;
		const isInitialized = element.getAttribute('data-initialized');

		if (!name || isInitialized === 'true') {
			return;
		}

		const module = modules[name];

		if (module) {
			new module(element);
			element.setAttribute('data-initialized', 'true');
		}
	});
};

export default ModuleInitializer;
