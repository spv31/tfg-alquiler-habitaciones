export interface User {
  id: number;
  name: string;
  email: string;
  user_type: "individual" | "company";
  role: "owner" | "tenant";
  phone: string;
  address: string;
  profile_image: string;
  created_at: string;
}
