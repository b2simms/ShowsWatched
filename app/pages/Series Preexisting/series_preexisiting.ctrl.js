app.controller('SeriesPreexistingCtrl', ['$scope', '$state', 'user', 'auth', function ($scope, $state, user, auth) {

  $scope.search = "";

  console.log("SeriesPreexistingCtrl called.");

  $scope.search = function () {
    $scope.isLoading = true;
    user.searchPrexistingSeries($scope.name)
      .then(function (res) {
        $scope.results = res.data;
      })
      .catch(function (err) {
        try {
          console.log(err);
          $scope.message = err.data.message;
        } catch (err) {
          console.log(err);
          $scope.message = "Cannot find series - please contact system admin";
        } finally {
          $scope.isLoading = false;
        }
      })
      .finally(function () {
        $scope.isLoading = false;
      });
  }

  $scope.createSeries = function (series) {
    $state.go('series_create',
      {
        'name': series.name,
        'description': series.description,
        'preexisting_id': series.id
      }
    );
  }
}]);