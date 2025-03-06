export const tryCatch = async <T>(
  fn: () => Promise<T>,
  loadingRef?: Ref<Boolean>
): Promise<{ data?: T; error?: any}> => {
  if (loadingRef) loadingRef.value = true;
  try {
    const data = await fn();
    return { data };
  } catch (error) {
    return { error };
  } finally {
    if (loadingRef) loadingRef.value = false;
  }
}