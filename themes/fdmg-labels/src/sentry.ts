import {
	BrowserClient,
	BrowserTracing,
	getCurrentHub,
	makeFetchTransport,
	defaultStackParser,
	GlobalHandlers,
} from '@sentry/browser';

if (window.sentry) {
	const client = new BrowserClient({
		dsn: window.sentry.dsn,
		environment: window.sentry.environment,
		sampleRate: +window.sentry.error_sample_rate,
		tracesSampleRate: +window.sentry.trace_sample_rate,
		release: window.sentry.release,
		autoSessionTracking: false,
		sendClientReports: false,
		transport: makeFetchTransport,
		stackParser: defaultStackParser,
		integrations: [
			new BrowserTracing({
				// See: https://github.com/getsentry/sentry-javascript/issues/6077
				// See: https://docs.sentry.io/platforms/javascript/guides/react/performance/instrumentation/automatic-instrumentation/#tracepropagationtargets
				tracePropagationTargets: [],
			}),
			new GlobalHandlers(),
		],
	});

	const hub = getCurrentHub();
	hub.bindClient(client);

	const screenType = () => {
		let screenType = 'unknown';
		const width = window.screen.width;

		if (width >= 1024) {
			screenType = 'desktop';
		}

		if (width < 1024) {
			screenType = 'mobile';
		}

		return screenType;
	};

	const transactionData: {
		name: string;
		tags: {
			[key: string]: number | string | boolean | bigint | symbol | null | undefined;
		};
		context: {
			[x: string]: unknown;
		};
	} = window.sentry.transaction_data;

	if (transactionData !== null) {
		hub.configureScope((scope) => scope.setTransactionName(`${transactionData.name} (${screenType()})`));
		hub.configureScope((scope) => scope.setTags(transactionData.tags));
		hub.configureScope((scope) => scope.setContext('Object', transactionData.context));
	}
}
