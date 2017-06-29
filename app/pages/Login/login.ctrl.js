app.controller('LoginCtrl', ['$scope', '$state', '$stateParams', 'user','auth', 'localStorageService', function($scope, $state, $stateParams, user, auth, localStorageService) {
  var $scope = $scope;
  $scope.showPassword = false;

  $scope.flipPass = function(){
    $scope.showPassword = $scope.showPassword ? false : true;
  }

  console.log("LoginCtrl called.");

  if($stateParams.register != null){
    $scope.username = $stateParams.username;
    $scope.register_message = $stateParams.register;
  }

  $scope.login = function() {
    $scope.isLoading = true;
    user.login($scope.username, $scope.password)
      .then(function(res){
        debugger;
        var token = res.data ? res.data.token : null;
        var decoded = res.data.decoded;
        if(token) { 
          auth.saveToken(token);
          localStorageService.set('user_id', decoded.user_id);
          localStorageService.set('user_name', decoded.name);
          localStorageService.set('user_email', decoded.email);
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