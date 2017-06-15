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