app.controller('NavCtrl', ['$scope', '$state', 'user', 'auth', function ($scope, $state, user, auth) {
  console.log("Navbar called.");

  var series_name = "My Watch List";
  var manage_name = "User Info";
  $scope.series_name = series_name;
  $scope.manage_name = manage_name;

  $scope.title = "My Watch List";

  $scope.logout = function () {
    auth.logout && auth.logout()
  }

  $scope.button = [true, false];
  $scope.open = function (item) {
    if (item === 0) {
      $scope.button[1] = true;
      $scope.button[0] = false;
      $scope.title = series_name;
      $state.go('nav.series');
    } else {
      $scope.button[1] = true;
      $scope.button[0] = false;
      $scope.title = manage_name;
      $state.go('nav.user_info');
    }
  }
}]);