var app = angular.module('app', ['ui.router','ngMaterial', 'ngMessages', 'LocalStorageModule'])
.constant('API', 'http://localhost:8080')
.config(function($httpProvider) {
  $httpProvider.interceptors.push('authInterceptor');
})
.config(function($mdThemingProvider) {
  $mdThemingProvider.theme('default')
    .primaryPalette('indigo')
    .accentPalette('deep-orange')
    .warnPalette('red');
})
.config(function (localStorageServiceProvider) {
  localStorageServiceProvider
    .setPrefix('thewatchlist')
    .setStorageType('localStorage');
})
.directive('compareTo', compareTo);

function compareTo() {
    return {
        require: "ngModel",
        scope: {
            otherModelValue: "=compareTo"
        },
        link: function(scope, element, attributes, ngModel) {
             
            ngModel.$validators.compareTo = function(modelValue) {
                return modelValue == scope.otherModelValue;
            };
 
            scope.$watch("otherModelValue", function() {
                ngModel.$validate();
            });
        }
    };
};
app.config(function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/login');
    $stateProvider
    .state('nav', {
        templateUrl: 'templates/navbar.html',
        controller: 'NavCtrl'
    })
    .state('login', {
        url: '/login',
        params: {username:null,register:null},
        templateUrl: 'templates/login.html',
        controller: 'LoginCtrl'
    })
    .state('register', {
        url: '/register',
        templateUrl: 'templates/register.html',
        controller: 'RegisterCtrl'
    })
    .state('forgot_password', {
        url: '/forgot_password',
        templateUrl: 'templates/forgotpassword.html',
        controller: 'ForgotPasswordCtrl'
    })
    .state('password_recovery', {
        url: '/password_recovery?requestID',
        templateUrl: 'templates/passwordrecovery.html',
        controller: 'PasswordRecoveryCtrl'
    })
    .state('nav.user_info', {
        url: '/user_info',
        templateUrl: 'templates/userinfo.html',
        controller: 'UserInfoCtrl'
    })
    .state('nav.series', {
        url: '/series',
        templateUrl: 'templates/series.html',
        controller: 'SeriesCtrl'
    })
    .state('series_create', {
        url: '/series_create',
        params: {'name':null,'description':null,'preexisting_id':null},
        templateUrl: 'templates/series_create.html',
        controller: 'SeriesCreateCtrl'
    })
    .state('series_edit', {
        url: '/series_edit',
        templateUrl: 'templates/series_edit.html',
        controller: 'SeriesEditCtrl'
    })
     .state('series_preexisting', {
        url: '/series_preexisting',
        templateUrl: 'templates/series_preexisting.html',
        controller: 'SeriesPreexistingCtrl'
    })
    .state('nav.episodes', {
        url: '/episodes',
        templateUrl: 'templates/episodes.html',
        controller: 'EpisodesCtrl'
    })
    .state('episodes_create', {
        url: '/episodes_create',
        templateUrl: 'templates/episodes_create.html',
        controller: 'EpisodesCreateCtrl'
    })
    .state('episodes_edit', {
        url: '/episodes_edit',
        templateUrl: 'templates/episodes_edit.html',
        controller: 'EpisodesEditCtrl'
    })
});

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
    debugger;
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
  self.searchPrexistingSeries = function (queryParam) {
    return $http.get(API + '/external/search/' + queryParam)
  };
  self.refreshEpisodes = function (series_id, body) {
    return $http.put(API + '/external/episodes/'+series_id+'/refresh', body)
  };
}
app.controller('EpisodesCtrl', ['$rootScope','$scope', '$state', 'user', 'auth', '$mdDialog', '$q', 'localStorageService', '$window', '$location',
function ($rootScope, $scope, $state, user, auth, $mdDialog, $q, localStorageService, $window, $location) {
  console.log("EpisodesCtrl initiated");

  $scope.isLoading = true;
  $scope.allowRefresh = true;

  $scope.hgt = $window.innerHeight - 152;

  var getSeriesAll = loadSeriesAll();

  $scope.series_name = localStorageService.get("series_name");
  
  $scope.selectedItem;
  $scope.getSelectedText = function () {
    if ($scope.selectedItem !== undefined) {
      $location.hash('season_'+$scope.selectedItem);
      return "Season " + $scope.selectedItem;
    } else {
      return "Season 1";
    }
  };

  $scope.reload = function(){
    loadSeriesAll();  
  }

  $scope.checkIfOwner = checkIfOwner;
  function checkIfOwner(){
    var current_user_id = localStorageService.get('user_id');
    var series_user_id = localStorageService.get('series_user_id');
    if(current_user_id === series_user_id){
      return true;
    }
    return false;    
  }
  $scope.checkIfPrivate = function(){
    var series_is_private = localStorageService.get('series_is_private');
    if(series_is_private === 'F'){
      return true;
    }
    return false;
  }
  $scope.checkIfPreexisting = function(){
    var series_is_preexisting = localStorageService.get('series_is_preexisting');
    if(series_is_preexisting > 0){
      return true;
    }
    return false;
  }

  $scope.refreshEpisodes = function(){
    $scope.isLoading = true;
    $scope.allowRefresh = false;
    series_id = localStorageService.get('series_id');
    var body = {};
    body.series_is_preexisting = localStorageService.get('series_is_preexisting');
    return user.refreshEpisodes(series_id, body)
    .then(function (res) {
      loadSeriesAll();
    })
    .catch(function (err) {
      if (err.data && err.data.message) {
        showAlert(err.data.message);
      } if (err.message) {
        showAlert(err.message);
      } else {
        showAlert("Something went wrong");
      }
    })
    .finally(function () {
      $scope.isLoading = false;
    })
  }

  $scope.loadEpisodesEdit = function(season, item){
    item.season = season;
    $rootScope.episode_current = item;
    $state.go('episodes_edit');
  }

  function showAlert(message) {
    $mdDialog.show(
      $mdDialog.alert()
        .parent(angular.element(document.querySelector('#popupContainer')))
        .clickOutsideToClose(false)
        .title('An Error Occurred.')
        .textContent(message)
        .ok('Log out')
    )
      .then(function () {
        $state.go('login');
      });
  };

  function loadSeriesAll(){
    $scope.isLoading = true;
    return user.getSeriesAll()
    .then(function (res) {
      console.log(res);
      var seasons = null;
      var seasonNums = [];
      res.data.forEach(function (obj, index) {
        var id = localStorageService.get("series_id");
        if (obj.id === id) {
          seasons = obj.seasons;
          for(var i=1;i<=seasons.length;i++){
            seasonNums.push(i);
          }
        }
      });
      $scope.items = seasonNums;
      $scope.seasons = seasons;
    })
    .catch(function (err) {
      if (err.data && err.data.message) {
        showAlert(err.data.message);
      } if (err.message) {
        showAlert(err.message);
      } else {
        showAlert("Something went wrong");
      }
    })
    .finally(function () {
      $scope.isLoading = false;
    })
  }

  $scope.setLoading = function(bool){
    $scope.isLoading = bool;
  }

  $scope.openDialog = function($event, episode) {
    $scope.isLoading = true;
    var parentEl = angular.element(document.body);
    $mdDialog.show({
      parent: parentEl,
      targetEvent: $event,
      templateUrl: 'templates/dialog.tmpl.html',
      locals: {
        episode: episode,
        checkIfOwner: $scope.checkIfOwner,
        setLoading: $scope.setLoading,
        localStorageService: localStorageService
      },
      controller: DialogController,
      clickOutsideToClose:true
    });
    function DialogController($scope, $mdDialog, episode, checkIfOwner, setLoading, localStorageService) {
      $scope.episode = episode;
      $scope.checkIfOwner = checkIfOwner;
      $scope.checkIfClaimed = function(episode){
        if(!angular.equals(episode.claimed_by_user,'0')){
          return true;
        }
        return false;
      }
      $scope.checkIfClaimedByUser = function (episode){
        var current_user_id = localStorageService.get('user_id');
        if(angular.equals(episode.claimed_by_user, current_user_id)){
          return true;
        }
        return false;
      }
      $scope.checkIfWatched = function(episode){
        if(angular.equals(episode.is_watched,'T')){
          return true;
        }
        return false;
      }
      $scope.claimEpisode = function (episode) {
        user.claimEpisode(episode.id)
          .then(function (res) {
            loadSeriesAll();
          })
          .catch(function (err) {
            try {
              console.log(err);
            } catch (err) {
              console.log(err);
              $scope.message = "Cannot claim episode - please contact system admin";
            }
          })
          .finally(function(){
            closeDialog();
          });
      }
      $scope.unClaimEpisode = function (episode) {
        user.unClaimEpisode(episode.id)
          .then(function (res) {
            loadSeriesAll();
          })
          .catch(function (err) {
            try {
              console.log(err);
            } catch (err) {
              console.log(err);
              $scope.message = "Cannot claim episode - please contact system admin";
            }
          })
          .finally(function(){
            closeDialog();
          });
      }
      $scope.watchEpisode = function (episode) {
        user.watchEpisode(episode.id)
          .then(function (res) {
            loadSeriesAll();
          })
          .catch(function (err) {
            try {
              console.log(err);
            } catch (err) {
              console.log(err);
              $scope.message = "Cannot watch episode - please contact system admin";
            }
          })
          .finally(function(){
            closeDialog();
          });
      }
      $scope.unWatchEpisode = function (episode) {
        user.unWatchEpisode(episode.id)
          .then(function (res) {
            loadSeriesAll();
          })
          .catch(function (err) {
            try {
              console.log(err);
            } catch (err) {
              console.log(err);
              $scope.message = "Cannot unwatch episode - please contact system admin";
            }
          })
          .finally(function(){
            closeDialog();
          });
      }
      $scope.closeDialog = closeDialog;
      function closeDialog() {
        $mdDialog.hide();
        setLoading(false);
      }
    }
  }
}]);
app.controller('EpisodesCreateCtrl', ['$scope', '$state', 'user','auth', 'localStorageService', function($scope, $state, user, auth, localStorageService) {
  
  $scope.body = {};
  console.log("EpisodesCreateCtrl called.");

  $scope.create = function() {
    $scope.isLoading = true;
    var series_id = localStorageService.get('series_id');
    user.createEpisode(series_id, $scope.body)
      .then(function(res){
        $scope.message_success = "Episode created successfully!";
        setTimeout(function(){ 
          $scope.isLoading = false;
          $state.go('nav.episodes');
        }, 1800);
      })
      .catch(function(err){
        try{
          console.log(err);
          $scope.message = err.data.message;
        }catch(err){
          console.log(err);
          $scope.message = "Cannot register - please contact system admin";
        }finally{
          $scope.isLoading = false;
        }
      })
  }
}]);
app.controller('EpisodesEditCtrl', ['$rootScope', '$scope', '$state', 'user', 'auth', '$mdDialog',
function ($rootScope, $scope, $state, user, auth, $mdDialog) {

  $scope.body = {};
  $scope.body = $rootScope.episode_current;

  console.log("EpisodesEditCtrl called.");

  $scope.update = function () {
    $scope.isLoading = true;
    var params = $scope.body;
    user.updateEpisode(params.id, params)
      .then(function (res) {
        $scope.message_success = "Episode updated successfully!";
        setTimeout(function () {
          $scope.isLoading = false;
          $state.go('nav.episodes');
        }, 1800);
      })
      .catch(function (err) {
        try {
          console.log(err);
          $scope.message = err.data.message;
        } catch (err) {
          console.log(err);
          $scope.message = "Cannot update - please contact system admin";
        } finally {
          $scope.isLoading = false;
        }
      })
  }

  $scope.showDelete = function (ev) {
    // Appending dialog to document.body to cover sidenav in docs app
    var confirm = $mdDialog.confirm()
      .title('Delete this episode?')
      .textContent('Episode data will be deleted.')
      .ariaLabel('Lucky day')
      .targetEvent(ev)
      .ok('Delete')
      .cancel('Cancel');

    $mdDialog.show(confirm).then(function () {
      deleteEpisode();
    }, function () {
    });
  };

  function deleteEpisode() {
    $scope.isLoading = true;
    var params = $scope.body;
    user.deleteEpisode(params.id)
      .then(function (res) {
        $scope.message_success = "Episode deleted successfully!";
        setTimeout(function () {
          $scope.isLoading = false;
          $state.go('nav.episodes');
        }, 1800);
      })
      .catch(function (err) {
        try {
          console.log(err);
          $scope.message = err.data.message;
        } catch (err) {
          console.log(err);
          $scope.message = "Cannot delete - please contact system admin";
        } finally {
          $scope.isLoading = false;
        }
      })
  }

}]);
app.controller('ForgotPasswordCtrl', ['$scope', '$state', 'user','auth', function($scope, $state, user, auth) {
  var $scope = $scope;

  console.log("ForgotPasswordCtrl called.");

  $scope.forgotPassword = function() {
    $scope.isLoading = true;
    user.forgotPassword($scope.email)
      .then(function(res){
        $state.go('login', { register: "Recovery email sent successfully if exists." });
      })
      .catch(function(err){
        console.log(err);
        try{
          $scope.message = err.data.message;
        }catch(err){
          console.log(err);
          $scope.message = "Cannot send recovery email - please contact system admin";
        }
      })
      .finally(function(){
        $scope.isLoading = false;
      })
  }

}]);
app.controller('LoginCtrl', ['$scope', '$state', '$stateParams', 'user','auth', 'localStorageService', function($scope, $state, $stateParams, user, auth, localStorageService) {
  var $scope = $scope;
  $scope.showPassword = false;

  $scope.flipPass = function(){
    $scope.showPassword = $scope.showPassword ? false : true;
  }

  console.log("LoginCtrl called.");

  if($stateParams.register != null){
    $scope.username = $stateParams.username;
    $scope.register_message = $stateParams.register;
  }

  $scope.login = function() {
    $scope.isLoading = true;
    user.login($scope.username, $scope.password)
      .then(function(res){
        var token = res.data ? res.data.token : null;
        if(token) { 
          auth.saveToken(token);
          localStorageService.set('user_id', res.data.decoded.user_id);
          console.log('JWT:', token);
        }
        $state.go('nav.series');
      })
      .catch(function(err){
        console.log(err);
        try{
          $scope.message = err.data.message;
        }catch(err){
          console.log(err);
          $scope.message = "Cannot log in - please contact system admin";
        }
      })
      .finally(function(){
        $scope.isLoading = false;
      })
  }

}]);
app.controller('NavCtrl', ['$scope', '$state', 'user', 'auth', function ($scope, $state, user, auth) {
  console.log("Navbar called.");

  var series_name = "My Watch List";
  var manage_name = "User Info";
  $scope.series_name = series_name;
  $scope.manage_name = manage_name;

  $scope.title = "My Watch List";

  $scope.logout = function () {
    auth.logout && auth.logout()
  }

  $scope.button = [true, false];
  $scope.open = function (item) {
    if (item === 0) {
      $scope.button[1] = true;
      $scope.button[0] = false;
      $scope.title = series_name;
      $state.go('nav.series');
    } else {
      $scope.button[1] = true;
      $scope.button[0] = false;
      $scope.title = manage_name;
      $state.go('nav.user_info');
    }
  }
}]);
app.controller('PasswordRecoveryCtrl', ['$scope', '$state', 'user','auth', function($scope, $state, user, auth) {
  var $scope = $scope;

  console.log("PasswordRecoveryCtrl called.");

  $scope.recoverPassword = function() {
    $scope.isLoading = true;
    debugger;
    user.recoverPassword($scope.password, $state.params.requestID)
      .then(function(res){
        $state.go('login', { register: "Password updated." });
      })
      .catch(function(err){
        console.log(err);
        try{
          $scope.message = err.data.message;
        }catch(err){
          console.log(err);
          $scope.message = "Cannot reset password - please contact system admin";
        }
      })
      .finally(function(){
        $scope.isLoading = false;
      })
  }

}]);
app.controller('RegisterCtrl', ['$scope', '$state', 'user','auth', function($scope, $state, user, auth) {
  var $scope = $scope;

  console.log("RegisterCtrl called.");

  $scope.register = function() {
    $scope.isLoading = true;
    user.register($scope.username, $scope.password, $scope.email)
      .then(function(res){
        $state.go('login', { username: $scope.username, register: "Registration successful -> "+$scope.username+" created!" });
      })
      .catch(function(err){
        console.log(err);
        try{
          $scope.message = err.data.message;
        }catch(err){
          console.log(err);
          $scope.message = "Cannot register - please contact system admin";
        }
      })
      .finally(function(){
        $scope.isLoading = false;
      })
  }

}]);
app.controller('SeriesCtrl', ['$rootScope', '$scope', '$state', 'user','auth', '$mdDialog', '$q', 'localStorageService', '$window',
function($rootScope, $scope, $state, user, auth, $mdDialog, $q, localStorageService, $window) {
  console.log("ListCtrl initiated");

  $scope.isLoading = true;
  $scope.isEditable = false;

  $scope.current_user_id = localStorageService.get('user_id');

  $scope.hgt = $window.innerHeight - 152;

  var publicSeries = [];
  var privateSeries = [];
  $scope.change = function(tab){
    if(tab===0){
      $scope.series = publicSeries;
    }else{
      $scope.series = privateSeries;
    }
  }

  user.getSeries()
  .then(function(res){
    console.log(res);
    res.data.forEach(function(obj, index){
      if(obj.name){
        obj.letter = obj.name.charAt(0).toUpperCase();
      }
      if(obj.is_private === 'F'){
        publicSeries.push(obj);
      }else{
        privateSeries.push(obj);
      }
    });
    $scope.series = publicSeries;
  })
  .catch(function(err){
    if(err.data && err.data.message){
      showAlert(err.data.message); 
    }if(err.message){
      showAlert(err.message); 
    }else{
      showAlert("Something went wrong");
    }   
  })
  .finally(function(){
    $scope.isLoading = false;
  })

  $scope.listIndexes = {}; 
  $scope.load = function(item){
     $scope.isLoading = true;
     localStorageService.set("series_id",item.id);
     localStorageService.set("series_name",item.name);
     localStorageService.set("series_user_id",item.user_id);
     localStorageService.set("series_is_private",item.is_private);
     localStorageService.set('series_is_preexisting',item.preexisting_id);
     $state.go('nav.episodes');
  }

  $scope.loadSeriesEdit = function(item){
    $rootScope.series_current = item;
    $state.go('series_edit');
  }

  function showAlert(message) {
    $mdDialog.show(
      $mdDialog.alert()
        .parent(angular.element(document.querySelector('#popupContainer')))
        .clickOutsideToClose(false)
        .title('Session Expired')
        .textContent(message)
        .ok('Log out')
    )
    .then(function(){
      $state.go('login');
    });
  };

}]);
app.controller('SeriesCreateCtrl', ['$scope', '$state', 'user','auth', function($scope, $state, user, auth) {
  
  $scope.body = {};

  $scope.isPreexisting = false;
  if($state.params.name != null){
    $scope.isPreexisting = true;
    $scope.body.name = $state.params.name;
    $scope.body.description = $state.params.description;
    $scope.body.preexisting_id = $state.params.preexisting_id;
  }

  $scope.body.is_private = false;

  console.log("SeriesCreateCtrl called.");

  $scope.create = function() {
    $scope.isLoading = true;
    $scope.body.is_private = $scope.body.is_private ? 'T':'F';
    user.createSeries($scope.body)
      .then(function(res){
        debugger;
        $scope.message_success = "Series created successfully!";
        setTimeout(function(){ 
          $scope.isLoading = false;
          $state.go('nav.series');
        }, 1800);
      })
      .catch(function(err){
        debugger;
        try{
          console.log(err);
          $scope.message = err.data.message;
        }catch(err){
          console.log(err);
          $scope.message = "Cannot register - please contact system admin";
        }finally{
          $scope.isLoading = false;
        }
      })
  }
}]);
app.controller('SeriesEditCtrl', ['$rootScope', '$scope', '$state', 'user', 'auth', '$mdDialog',
function ($rootScope, $scope, $state, user, auth, $mdDialog) {

  $scope.body = {};
  $scope.body = $rootScope.series_current;
  if($scope.body){
    $scope.body.is_private = $scope.body.is_private == 'T' ? true : false;
  }

  console.log("SeriesEditCtrl called.");

  $scope.update = function () {
    $scope.isLoading = true;
    debugger;
    var params = $scope.body;
    params.is_private = (params.is_private == true || params.is_private == 'T') ? 'T' : 'F';
    user.updateSeries(params.id, params)
      .then(function (res) {
        $scope.message_success = "Series updated successfully!";
        setTimeout(function () {
          $scope.isLoading = false;
          $state.go('nav.series');
        }, 1800);
      })
      .catch(function (err) {
        try {
          console.log(err);
          $scope.message = err.data.message;
        } catch (err) {
          console.log(err);
          $scope.message = "Cannot update - please contact system admin";
        } finally {
          $scope.isLoading = false;
        }
      })
  }

  $scope.showDelete = function (ev) {
    // Appending dialog to document.body to cover sidenav in docs app
    var confirm = $mdDialog.confirm()
      .title('Delete this series?')
      .textContent('Series and associated episode data will be deleted as well.')
      .ariaLabel('Lucky day')
      .targetEvent(ev)
      .ok('Delete')
      .cancel('Cancel');

    $mdDialog.show(confirm).then(function () {
      deleteSeries();
    }, function () {
    });
  };

  function deleteSeries() {
    $scope.isLoading = true;
    debugger;
    var params = $scope.body;
    user.deleteSeries(params.id)
      .then(function (res) {
        $scope.message_success = "Series deleted successfully!";
        setTimeout(function () {
          $scope.isLoading = false;
          $state.go('nav.series');
        }, 1800);
      })
      .catch(function (err) {
        try {
          console.log(err);
          $scope.message = err.data.message;
        } catch (err) {
          console.log(err);
          $scope.message = "Cannot delete - please contact system admin";
        } finally {
          $scope.isLoading = false;
        }
      })
  }

}]);
app.controller('SeriesPreexistingCtrl', ['$scope', '$state', 'user', 'auth', function ($scope, $state, user, auth) {

  $scope.search = "";

  console.log("SeriesPreexistingCtrl called.");

  $scope.search = function () {
    $scope.isLoading = true;
    user.searchPrexistingSeries($scope.name)
      .then(function (res) {
        $scope.results = res.data;
      })
      .catch(function (err) {
        try {
          console.log(err);
          $scope.message = err.data.message;
        } catch (err) {
          console.log(err);
          $scope.message = "Cannot find series - please contact system admin";
        } finally {
          $scope.isLoading = false;
        }
      })
      .finally(function () {
        $scope.isLoading = false;
      });
  }

  $scope.createSeries = function (series) {
    $state.go('series_create',
      {
        'name': series.name,
        'description': series.description,
        'preexisting_id': series.id
      }
    );
  }
}]);
app.controller('UserInfoCtrl', ['$scope', '$state', 'user','auth', function($scope, $state, user, auth) {
  var $scope = $scope;

  console.log("RegisterCtrl called.");

  $scope.register = function() {
    $scope.isLoading = true;
    user.register($scope.username, $scope.password, $scope.email)
      .then(function(res){
        $state.go('login', { username: $scope.username, register: "Registration successful -> "+$scope.username+" created!" });
      })
      .catch(function(err){
        console.log(err);
        try{
          $scope.message = err.data.message;
        }catch(err){
          console.log(err);
          $scope.message = "Cannot register - please contact system admin";
        }
      })
      .finally(function(){
        $scope.isLoading = false;
      })
  }

}]);