app.service('user', userService);
function userService($http, API, auth) {
  var self = this;

  self.login = function (username, password) {
    return $http.post(API + '/auth/login', {
      username: username,
      password: password
    })
  };
  self.register = function (username, password, email) {
    return $http.post(API + '/auth/register', {
      username: username,
      password: password,
      email: email
    })
  };
  self.forgotPassword = function (email) {
    return $http.post(API + '/auth/forgotpassword', {
      email: email
    })
  };
  self.recoverPassword = function (password, requestID) {
    return $http.post(API + '/auth/recoverpassword', {
      password: password,
      requestID: requestID
    })
  };
  self.getSeries = function () {
    return $http.get(API + '/series')
  }
  self.getSeriesAll = function () {
    return $http.get(API + '/series/list')
  }
  self.createSeries = function (body) {
    if (body.preexisting_id != null) {
      return $http.post(API + '/series/' + body.preexisting_id, body);
    } else {
      return $http.post(API + '/series', body);
    }
  };
  self.updateSeries = function (id, body) {
    return $http.put(API + '/series/' + id, body)
  };
  self.deleteSeries = function (id) {
    return $http.delete(API + '/series/' + id)
  };
  self.createEpisode = function (id, body) {
    return $http.post(API + '/series/' + id + '/episodes', body)
  };
  self.updateEpisode = function (id, body) {
    return $http.put(API + '/episodes/' + id, body)
  };
  self.deleteEpisode = function (id) {
    return $http.delete(API + '/episodes/' + id)
  };
  self.claimEpisode = function (id) {
    return $http.put(API + '/episodes/' + id + '/claim')
  };
  self.unClaimEpisode = function (id) {
    return $http.delete(API + '/episodes/' + id + '/claim')
  };
  self.watchEpisode = function (id) {
    return $http.put(API + '/episodes/' + id + '/watch')
  };
  self.unWatchEpisode = function (id) {
    return $http.delete(API + '/episodes/' + id + '/watch')
  };
  self.watchSeason = function (series_id, id) {
    return $http.put(API + '/seasons/' + id + '/watch', {
      'series_id': series_id
    })
  };
   self.unWatchSeason = function (series_id, id) {
    return $http.delete(API + '/seasons/' + id + '/watch', {
      'series_id': series_id
    })
  };
  self.searchPrexistingSeries = function (queryParam) {
    return $http.get(API + '/external/search/' + queryParam)
  };
  self.refreshEpisodes = function (series_id, body) {
    return $http.put(API + '/external/episodes/'+series_id+'/refresh', body)
  };
  self.updateInfo = function (body) {
    return $http.put(API + '/user', body)
  };
}