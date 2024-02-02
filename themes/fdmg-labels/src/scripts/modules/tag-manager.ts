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

		if (this.FDMGUserId && this.FDMGUserId !== '0') {
			data.userId = this.FDMGUserId;
		}
		if (this.FDMGOrganisationId && this.FDMGOrganisationId !== '0') {
			data.companyId = this.FDMGOrganisationId;
		}

		if (Object.keys(data).length !== 0) {
			window.dataLayer.push(data);
		}
	}
}

export default TagManager;
