/** The shell-wired axios client (window.modJs.apiClient) and its error shape. */
export const api = () => window.modJs.apiClient;

export function errorMessage(err, fallback) {
  const r = err && err.response && err.response.data;
  if (r && r.error && r.error[0] && r.error[0][0] && r.error[0][0].message) return r.error[0][0].message;
  return fallback;
}
