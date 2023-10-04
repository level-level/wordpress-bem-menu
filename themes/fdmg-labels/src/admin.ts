// Global variable provided by WordPress
declare const wp: any;

const initDarkModeButton = () => {
	const darkmodeButton = document.getElementById('wp-admin-bar-ll-darkmode')?.querySelector('div');
	if (!darkmodeButton) {
		return;
	}

	const disableText = darkmodeButton.getAttribute('title');
	const enableText = darkmodeButton.textContent;

	darkmodeButton?.addEventListener('click', () => {
		if (document.documentElement.getAttribute('data-mode') === 'dark') {
			document.documentElement.removeAttribute('data-mode');
			darkmodeButton.textContent = enableText;
		} else {
			document.documentElement.setAttribute('data-mode', 'dark');
			darkmodeButton.textContent = disableText;
		}
	});
};

wp.domReady(() => {
	initDarkModeButton();
});
