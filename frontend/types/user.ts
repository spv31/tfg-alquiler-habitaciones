export interface User {
  id: number;
  name: string;
  email: string;
  user_type: "individual" | "company";
  role: "owner" | "tenant";
  phone_number: string;
  address: string;
  email_verified_at: string | null, 
  profile_image_filename: string | null;
  profile_image_url: string | null;
  created_at: string;
}
