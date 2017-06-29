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

  $scope.openDialogSeason = function($event, season) {
    debugger;
    $scope.isLoading = true;
    var parentEl = angular.element(document.body);
    $mdDialog.show({
      parent: parentEl,
      targetEvent: $event,
      templateUrl: 'templates/dialog_season.tmpl.html',
      locals: {
        season: season,
        checkIfOwner: $scope.checkIfOwner,
        setLoading: $scope.setLoading,
        localStorageService: localStorageService
      },
      controller: DialogControllerSeason,
      clickOutsideToClose:true
    });
    function DialogControllerSeason($scope, $mdDialog, season, checkIfOwner, setLoading, localStorageService) {
      $scope.season = season;
      $scope.checkIfOwner = checkIfOwner;

      $scope.watchSeason = function (season) {
        var series_id = localStorageService.get("series_id");
        user.watchSeason(series_id, season)
          .then(function (res) {
            loadSeriesAll();
          })
          .catch(function (err) {
            try {
              console.log(err);
            } catch (err) {
              console.log(err);
              $scope.message = "Cannot watch season - please contact system admin";
            }
          })
          .finally(function(){
            closeDialog();
          });
      }
      $scope.unWatchSeason = function (season) {
        var series_id = localStorageService.get("series_id");
        user.unWatchSeason(series_id, season)
          .then(function (res) {
            loadSeriesAll();
          })
          .catch(function (err) {
            try {
              console.log(err);
            } catch (err) {
              console.log(err);
              $scope.message = "Cannot unwatch season - please contact system admin";
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