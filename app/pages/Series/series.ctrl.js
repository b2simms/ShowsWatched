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