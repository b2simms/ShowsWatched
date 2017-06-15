app.controller('SeriesEditCtrl', ['$rootScope', '$scope', '$state', 'user', 'auth', '$mdDialog',
function ($rootScope, $scope, $state, user, auth, $mdDialog) {

  $scope.body = {};
  $scope.body = $rootScope.series_current;
  if($scope.body){
    $scope.body.is_private = $scope.body.is_private == 'T' ? true : false;
  }

  console.log("SeriesEditCtrl called.");

  $scope.update = function () {
    $scope.isLoading = true;
    debugger;
    var params = $scope.body;
    params.is_private = (params.is_private == true || params.is_private == 'T') ? 'T' : 'F';
    user.updateSeries(params.id, params)
      .then(function (res) {
        $scope.message_success = "Series updated successfully!";
        setTimeout(function () {
          $scope.isLoading = false;
          $state.go('nav.series');
        }, 1800);
      })
      .catch(function (err) {
        try {
          console.log(err);
          $scope.message = err.data.message;
        } catch (err) {
          console.log(err);
          $scope.message = "Cannot update - please contact system admin";
        } finally {
          $scope.isLoading = false;
        }
      })
  }

  $scope.showDelete = function (ev) {
    // Appending dialog to document.body to cover sidenav in docs app
    var confirm = $mdDialog.confirm()
      .title('Delete this series?')
      .textContent('Series and associated episode data will be deleted as well.')
      .ariaLabel('Lucky day')
      .targetEvent(ev)
      .ok('Delete')
      .cancel('Cancel');

    $mdDialog.show(confirm).then(function () {
      deleteSeries();
    }, function () {
    });
  };

  function deleteSeries() {
    $scope.isLoading = true;
    debugger;
    var params = $scope.body;
    user.deleteSeries(params.id)
      .then(function (res) {
        $scope.message_success = "Series deleted successfully!";
        setTimeout(function () {
          $scope.isLoading = false;
          $state.go('nav.series');
        }, 1800);
      })
      .catch(function (err) {
        try {
          console.log(err);
          $scope.message = err.data.message;
        } catch (err) {
          console.log(err);
          $scope.message = "Cannot delete - please contact system admin";
        } finally {
          $scope.isLoading = false;
        }
      })
  }

}]);