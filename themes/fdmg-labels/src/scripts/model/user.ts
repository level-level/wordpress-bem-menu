import Cookies from 'js-cookie';

export type UserData = {
	id: number;
	username: string;
	display_name: string;
	first_name: string;
};

class User {
	private static instance: User;
	private data: UserData;

	constructor(data: UserData) {
		this.data = data;
	}

	static getCurrentUser() {
		const userCookie = Cookies.get('fdmg_labels_user');
		const data = userCookie ? (JSON.parse(userCookie) as UserData) : null;

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

	getUsername() {
		return this.data.username;
	}

	getDisplayName() {
		return this.data.display_name;
	}

	getFirstName() {
		return this.data.first_name;
	}
}

export default User;
