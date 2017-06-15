app.controller('LoginCtrl', ['$scope', '$state', '$stateParams', 'user','auth', 'localStorageService', function($scope, $state, $stateParams, user, auth, localStorageService) {
  var $scope = $scope;

  console.log("LoginCtrl called.");

  if($stateParams.username != null){
    $scope.username = $stateParams.username;
    $scope.register_message = $stateParams.register;
  }

  $scope.login = function() {
    $scope.isLoading = true;
    user.login($scope.username, $scope.password)
      .then(function(res){
        var token = res.data ? res.data.token : null;
        if(token) { 
          auth.saveToken(token);
          localStorageService.set('user_id', res.data.decoded.user_id);
          console.log('JWT:', token);
        }
        $state.go('nav.series');
      })
      .catch(function(err){
        console.log(err);
        try{
          $scope.message = err.data.message;
        }catch(err){
          console.log(err);
          $scope.message = "Cannot log in - please contact system admin";
        }
      })
      .finally(function(){
        $scope.isLoading = false;
      })
  }

}]);