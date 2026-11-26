/**
 * Thin wrapper over the shell's IceApiClient (GET / POST / DELETE only). Query
 * parameters are appended as "&key=value": the client calls the legacy
 * api/index.php?...&url=/<endpoint>, so they arrive in $_GET next to `url`.
 * Same shape as the Onboarding extension's, so its components can be reused here.
 */
export function makeApi(client) {
  const c = () => client || (window.modJs && window.modJs.apiClient);
  const qs = (params) => Object.keys(params || {})
    .filter((k) => params[k] !== undefined && params[k] !== null && params[k] !== '')
    .map((k) => `&${encodeURIComponent(k)}=${encodeURIComponent(params[k])}`)
    .join('');
  return {
    get: (path, params) => c().get(path + qs(params)).then((r) => r.data),
    post: (path, body) => c().post(path, body || {}).then((r) => r.data),
    del: (path) => c().delete(path).then((r) => r.data),
  };
}

export function errorMessage(err, fallback) {
  const r = err && err.response && err.response.data;
  if (r && r.error && r.error[0] && r.error[0][0] && r.error[0][0].message) return r.error[0][0].message;
  return fallback;
}
