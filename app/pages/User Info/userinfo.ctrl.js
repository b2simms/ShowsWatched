app.controller('UserInfoCtrl', ['$scope', '$state', 'user','auth', 'localStorageService', 
function($scope, $state, user, auth, localStorageService) {
  $scope.body = {};
  $scope.body.id = localStorageService.get('user_id');
  $scope.body.username = localStorageService.get('user_name');
  $scope.body.email = localStorageService.get('user_email');

  $scope.updateInfo = function() {
    $scope.isLoading = true;
    user.updateInfo($scope.body)
      .then(function(res){
        $scope.success_message = "Information updated.";
        $scope.message = null;
      })
      .catch(function(err){
        console.log(err);
        try{
          $scope.success_message = null;
          $scope.message = err.data.message;
        }catch(err){
          console.log(err);
          $scope.message = "Cannot update your information at this time.";
        }
      })
      .finally(function(){
        $scope.isLoading = false;
      })
  }

}]);