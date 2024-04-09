const pushToDataLayer = (type: string, text: string) => {
	window.dataLayer.push({
		'event': 'click',
			'event_info': {
			'category': `${type}`,
			'action': 'link click',
			'label': `${text}`, // e.g. linkedin | het financieele dagblad
			'value': null,
			'event_context': {
				'component': 'button',
				'index': '',
				'section_title': '',
				'click_text': `${text}`
			}
		}
	});
}

const handleClicks = () => {
	document.querySelectorAll<HTMLElement>('a').forEach( (element) => {
		if ( element === undefined ) {
			return;
		}

		// return if link does not have a data module
		if ( element.hasAttribute( 'data-datalayer' ) ) {
			return;
		}

		const href = element.getAttribute( 'href' ) ?? '';
		const text = ( element.textContent || element.innerText || '' ).replace(/(\r\n|\n|\r|\t)/gm, '');

		// if element has hash links add internal button click event
		if ( href.indexOf( '#' ) === 0 ) {
			element.addEventListener('click', () => {
				pushToDataLayer('internal click', text);
			} );
		}

		// if element is external link add external link click event
		else if ( href.indexOf( '/' ) === 0 || href.includes( location.origin ) ) {
			element.addEventListener('click', () => {
				pushToDataLayer('internal click', text);
			} );
		}

		// else add internal link click event
		else {
			element.addEventListener('click', () => {
				pushToDataLayer('external click', text);
			} );
		}

	});
};

export default handleClicks;
