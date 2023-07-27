import { describe, test, expect, beforeAll } from '@jest/globals';
import OEmbed from '../src/scripts/modules/oembed';

let element: HTMLElement;

beforeAll(() => {
	document.body.innerHTML = `<div data-component="oembed" class="max-w-5xl" data-module="oembed" data-source="consent_embed_youtube" data-type="video" data-cookiebot="" data-disable-notice="">
			<div data-oembed="content">
				<div data-oembed="placeholder" class="flex flex-col items-center justify-center h-full text-center bg-base-200">
					<div class="p-8">
						<p class="mb-7">This content is available if you agree to the cookies from Youtube.</p>
						<button class="js-consent-button">
							<span>
								I agree
							</span>
						</button>
					</div>
				</div>
			</div>

			<template>
				<div data-component="oembed-video" data-oembed="video" class="cursor-pointer aspect-video">
					<img data-oembed="thumbnail" class="absolute object-cover w-full h-full" src="https://i.ytimg.com/vi/124VzSLy7d0/maxresdefault.jpg" alt="" loading="lazy">
				<div class="w-full h-full iframe:absolute iframe:inset-0 iframe:w-full iframe:h-full">
					<iframe loading="lazy" title="Nero - Crush On You" width="500" height="281" data-src="https://www.youtube.com/embed/124VzSLy7d0?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
					<div class="absolute transform -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2">
						<button class="btn--js-video-play" aria-label="Play video">
							<span class="icon w-6 h-6" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="m2 0 17 10L2 20z"></path></svg></span>
						</button>
					</div>
				</div>
			</template>
		</div>`;
});

describe('OEmbed', () => {
	beforeAll(() => {
		element = document.querySelector('[data-module="oembed"]')!;
		new OEmbed(element);
	});

	test(`YouTube: consent to allow cookies.`, () => {
		const consentButton = element.querySelector<HTMLElement>('.js-consent-button')!;

		// Before giving consent the 'consent_embed_youtube' property should be null in localstorage
		expect(window.localStorage.getItem('consent_embed_youtube')).toBeNull();

		// Click to simulate consent
		consentButton.click();

		// Consent for YouTube embeds should now be set to true in the localstorage
		expect(window.localStorage.getItem('consent_embed_youtube')).toBe('true');
	});

	test(`YouTube: embed is visible and after click the correct src is loaded incl. autoplay and disabled keyboard.`, () => {
		const video = element.querySelector<HTMLElement>('[data-oembed="video"]')!;
		const placeholder = element.querySelector('[data-oembed="placeholder"]');

		// Check if consent placeholder is removed and video is visible
		expect(placeholder).toBeNull();
		expect(video).toBeDefined();

		const iframe = video.querySelector('iframe')!;
		const playButton = video.querySelector('.btn--js-video-play');
		const thumbnail = video.querySelector('[data-oembed="thumbnail"]');

		// Src should be null before clicking
		expect(iframe.getAttribute('src')).toBeNull();

		// Video play button and thumbnail should be visible
		expect(playButton).toBeDefined();
		expect(thumbnail).toBeDefined();

		// Play video
		video.click();

		// Src is now set with additional parameters and video is loaded
		expect(iframe.getAttribute('src')).toMatch('&autoplay=1&disablekb=1&keyboard=0');
	});
});
