import { IModule } from '../../globals';
import User from '../model/user';

class TagManager implements IModule {
	element: HTMLElement;
	FDMGUserId: number;
	FDMGOrganisationId: number;

	constructor(element: HTMLElement) {
		this.element = element;
		const user = User.getCurrentUser();
		this.FDMGUserId = user ? user.getFDMGUserId() : 0;
		this.FDMGOrganisationId = user ? user.getFDMGOrganisationId() : 0;

		this.initialize();
	}

	initialize() {
		const data: {
			userId?: string;
			companyId?: string;
		} = {};

		if (this.FDMGUserId && this.FDMGUserId) {
			data.userId = String(this.FDMGUserId);
		}
		if (this.FDMGOrganisationId && this.FDMGOrganisationId) {
			data.companyId = String(this.FDMGOrganisationId);
		}

		if (Object.keys(data).length !== 0) {
			window.dataLayer.push(data);
		}
	}
}

export default TagManager;
