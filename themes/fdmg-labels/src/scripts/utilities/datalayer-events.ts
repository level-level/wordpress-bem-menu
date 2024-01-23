const DataLayerEvents = () => {
	document.querySelectorAll<HTMLElement>('a').forEach( (element) => {
		if ( element === undefined ) {
			return;
		}

		// return if link does not have a data module
		if ( element.hasAttribute( 'data-module' ) ) {
			return;
		}

		const href = element.getAttribute( 'href' ) ?? '';
		const text = ( element.textContent || element.innerText || '' ).replace(/(\r\n|\n|\r|\t)/gm, '');

		// if element has hash links add internal button click event
		if ( href.indexOf( '#' ) === 0 ) {
			element.addEventListener('click', () => {
				window.dataLayer.push({
					'event': 'click',
						'event_info': {
						'category': 'internal click',
						'action': 'button click',
						'label': `${text}`, // e.g. linkedin | het financieele dagblad
						'value': null,
						'event_context': {
							'component': 'link',
							'index': '',
							'section_title': '',
							'click_text': `${text}`
						}
					}
				});
			} );
		}

		// if element is external link add external link click event
		else if ( href.indexOf( '/' ) === 0 || href.includes( location.origin ) ) {
			element.addEventListener('click', () => {
				window.dataLayer.push({
					'event': 'click',
						'event_info': {
						'category': 'internal click',
						'action': 'link click',
						'label': `${text}`, // e.g. linkedin | het financieele dagblad
						'value': null,
						'event_context': {
							'component': 'link',
							'section_title': '',
							"click_text": `${text}`
						}
					}
				});
			} );
		}

		// else add internal link click event
		else {
			element.addEventListener('click', () => {
				window.dataLayer.push({
					'event': 'click',
						'event_info': {
						'category': 'outbound click',
						'action': 'link click',
						'label': `${text}`, // e.g. linkedin | het financieele dagblad
						'value': null,
						'event_context': {
							'component': 'link',
							"section_title": '',
							"click_text": `${text}`
						}
					}
				});
			} );
		}

	});
};

export default DataLayerEvents;
