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
        $state.go('login', { register: "Recovery email sent successfully if exists." });
      })
      .finally(function(){
        $scope.isLoading = false;
      })
  }

}]);