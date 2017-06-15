app.factory('authInterceptor', authInterceptor)
function authInterceptor(API, auth) {
  return {
    // automatically attach Authorization header
    request: function(config) {
      config.headers['Authorization'] = 'Bearer ' + auth.getToken();
      return config;
    },

    // If a token was sent back, save it
    response: function(res) {
      return res;
    },
  }
}