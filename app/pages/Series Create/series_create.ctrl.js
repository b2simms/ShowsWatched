app.controller('SeriesCreateCtrl', ['$scope', '$state', 'user','auth', function($scope, $state, user, auth) {
  
  $scope.body = {};
  $scope.body.is_private = false;

  console.log("SeriesCreateCtrl called.");

  $scope.create = function() {
    $scope.isLoading = true;
    debugger;
    $scope.body.is_private = $scope.body.is_private ? 'T':'F';
    user.createSeries($scope.body)
      .then(function(res){
        $scope.message_success = "Series created successfully!";
        setTimeout(function(){ 
          $scope.isLoading = false;
          $state.go('nav.series');
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