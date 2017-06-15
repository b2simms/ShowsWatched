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