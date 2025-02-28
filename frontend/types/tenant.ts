export interface Tenant {
  name: string;
  email: string;
  user_type: "individual" | "company";
  profile_picture: string;
  phone_number: string;
}
