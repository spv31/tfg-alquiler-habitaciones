export interface CustomUser {
  name: string;
  email: string;
  user_type: "individual" | "company";
  role: "owner" | "tenant";
}
