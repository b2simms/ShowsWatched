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