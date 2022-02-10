import { describe, test, expect, beforeEach } from '@jest/globals';
import OEmbed from '../src/scripts/modules/oembed';

const createDOM = () => {
	document.body.innerHTML =
		`<div class="oembed-component" data-module="oembed" data-source="consent_embed_youtube" data-type="video" data-cookiebot="" data-disable-notice="">
			<div class="oembed-component__content">
				<div class="oembed-placeholder">
					<div class="oembed-placeholder__content">
						<p>This content is available if you agree to the cookies from Youtube.</p>
						<button class="btn btn--primary js-consent-button">
							<span class="btn__text">
								I agree
							</span>
						</button>
					</div>
				</div>
			</div>
			<template>
				<div class="oembed-video">
					<img class="oembed-video__thumbnail" src="https://i.ytimg.com/vi/124VzSLy7d0/maxresdefault.jpg" alt="" loading="lazy">
					<div class="oembed-video__content">
						<iframe loading="lazy" title="Nero - Crush On You" width="500" height="281" data-src="https://www.youtube.com/embed/124VzSLy7d0?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
						<div class="oembed-video__button">
							<button class="btn btn--play btn--js-video-play" aria-label="Play video">
								<span class="btn__icon">
									<span class="icon-inline icon-play" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="m2 0 17 10L2 20z"></path></svg></span>
								</span>
							</button>
						</div>
					</div>
				</div>
			</template>
		</div>`;

	const element: HTMLElement = document.querySelector( '[data-module="oembed"]' );
	return new OEmbed( element );
};

beforeEach( () => {
	return createDOM();
} );

describe( 'OEmbed', () => {

	test( `YouTube: consent to allow cookies.`, () => {
		const element = document.querySelector( '[data-module="oembed"]' );
		const consentButton = element.querySelector<HTMLElement>( '.js-consent-button' );

		// Before giving consent the 'consent_embed_youtube' property should be null in localstorage
		expect( window.localStorage.getItem( 'consent_embed_youtube' ) ).toBeNull();

		// Click to simulate consent
		consentButton.click();

		// Consent for YouTube embeds should now be set to true in the localstorage
		expect( window.localStorage.getItem( 'consent_embed_youtube' ) ).toBe( 'true' );
	} );

	test( `YouTube: embed is visible and after click the correct src is loaded incl. autoplay and disabled keyboard.`, () => {
		const element = document.querySelector( '[data-module="oembed"]' );
		const video = element.querySelector<HTMLElement>( '.oembed-video' );
		const placeholder = element.querySelector( '.oembed-placeholder' );

		// Check if consent placeholder is removed and video is visible
		expect( placeholder ).toBeNull();
		expect( video ).toBeDefined();

		const iframe = video.querySelector( 'iframe' );
		const playButton = video.querySelector( '.btn--js-video-play' );
		const thumbnail = video.querySelector( '.oembed-video__thumbnail' );

		// Src should be null before clicking
		expect( iframe.getAttribute( 'src' ) ).toBeNull();

		// Video play button and thumbnail should be visible
		expect( playButton ).toBeDefined();
		expect( thumbnail ).toBeDefined();

		// Play video
		video.click();

		// Src is now set with additional parameters and video is loaded
		expect( iframe.getAttribute( 'src' ) ).toMatch( '&autoplay=1&disablekb=1&keyboard=0' );
	} );
} );
