export interface CustomUser {
  id: number;   
  name: string;
  email: string;
  user_type: "individual" | "company";
  role: "owner" | "tenant";
}
