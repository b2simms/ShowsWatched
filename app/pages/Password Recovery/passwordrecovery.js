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