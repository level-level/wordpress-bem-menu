import { IModule } from '../../globals';
import User from '../model/user';

class PaywallLoginButton implements IModule {
	element: HTMLElement;
	user: User | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.user = User.getCurrentUser();
		this.initialize();
	}

	initialize() {
		if (this.user) {
			this.element.remove();
		}
	}
}

export default PaywallLoginButton;
