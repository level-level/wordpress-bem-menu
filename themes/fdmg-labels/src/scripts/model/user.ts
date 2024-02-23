import Cookies from 'js-cookie';

export type UserData = {
	id: number;
	display_name: string;
	fdmg_user_id: number;
	fdmg_organisation_id: number;
};

class User {
	private static instance: User;
	private data: UserData;

	constructor(data: UserData) {
		this.data = data;
	}

	static getCurrentUser() {
		const userCookie = Cookies.get('fdmg_labels_user');
		const userCookieData = userCookie ? JSON.parse(userCookie) : null;
		const data = userCookieData && typeof userCookieData.data !== 'undefined' ? userCookieData.data as UserData : null;

		if (!data) {
			return null;
		}

		if (!User.instance) {
			User.instance = new User(data);
		}

		return User.instance;
	}

	getId() {
		return this.data.id;
	}

	getDisplayName() {
		return this.data.display_name;
	}

	getFDMGUserId() {
		return this.data.fdmg_user_id;
	}

	getFDMGOrganisationId() {
		return this.data.fdmg_organisation_id;
	}
}

export default User;
