import { IModule } from '../../globals';
import Cookies from 'js-cookie';

class TagManager implements IModule {
	element: HTMLElement;
	FDMGUserId: string | undefined;
	FDMGOrganisationId: string | undefined;

	constructor(element: HTMLElement) {
		this.element = element;
		this.FDMGUserId = Cookies.get('fdmg_user_id');
		this.FDMGOrganisationId = Cookies.get('fdmg_organisation_id');

		this.initialize();
	}

	initialize() {
		const data: {
			userId?: string;
			companyId?: string;
		} = {};

		if (this.FDMGUserId) {
			data.userId = this.FDMGUserId;
		}
		if (this.FDMGOrganisationId) {
			data.companyId = this.FDMGOrganisationId;
		}

		window.dataLayer.push(data);
	}
}

export default TagManager;
