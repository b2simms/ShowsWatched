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