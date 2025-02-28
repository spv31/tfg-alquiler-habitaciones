export const tryCatch = async <T>(fn: () => Promise<T>): Promise<{ data?: T; error?: any}> => {
  try {
    const data = await fn();
    return { data };
  } catch (error) {
    return { error };
  }
}