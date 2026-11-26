/** The shell-wired axios client (window.modJs.apiClient) and its error shape. */
export const api = () => window.modJs.apiClient;

export function errorMessage(err, fallback) {
  const r = err && err.response && err.response.data;
  if (r && r.error && r.error[0] && r.error[0][0] && r.error[0][0].message) return r.error[0][0].message;
  return fallback;
}

/**
 * People who can be @mentioned, matching a typed query. The term travels in the
 * PATH — IceApiClient proxies through api/index.php?url=/<endpoint>, so a "?"
 * inside the endpoint breaks the route match.
 */
export function searchPeople(term) {
  const q = String(term || '').trim();
  const url = q ? `discussions/people/${encodeURIComponent(q)}` : 'discussions/people';
  return api().get(url).then((r) => {
    const b = r && r.data;
    const d = (b && b.status && b.data !== undefined) ? b.data : b;
    return Array.isArray(d) ? d : [];
  });
}
