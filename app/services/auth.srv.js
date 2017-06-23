app.service('auth', authService);
function authService($window) {
  var self = this;

  self.parseJwt = function(token) {
    var base64Url = token.split('.')[1];
    var base64 = base64Url.replace('-', '+').replace('_', '/');
    return JSON.parse($window.atob(base64));
  }
  self.isAuthed = function(){
    var token = self.getToken();
    if(token != null){
      return true;
    }
    return false;
  }

  self.saveToken = function(token) {
    $window.localStorage['jwtToken'] = token;
  }
  self.getToken = function() {
    return $window.localStorage['jwtToken'];
  }
}