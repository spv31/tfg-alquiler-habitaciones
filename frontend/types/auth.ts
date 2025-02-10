export interface RegisterUser {
	name: string,
	email: string,
	password: string,
	user_type: 'individual' | 'company',
	identifier: string;
	profile_picture?: string,
	phone_number: string,
	address: string,
}

export interface CustomUser {
	name: string,
	email: string,
	role: string,
	user_type: 'individual' | 'company';
}