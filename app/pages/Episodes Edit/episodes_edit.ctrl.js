app.controller('EpisodesEditCtrl', ['$rootScope', '$scope', '$state', 'user', 'auth', '$mdDialog',
function ($rootScope, $scope, $state, user, auth, $mdDialog) {

  $scope.body = {};
  $scope.body = $rootScope.episode_current;

  console.log("EpisodesEditCtrl called.");

  $scope.update = function () {
    $scope.isLoading = true;
    var params = $scope.body;
    user.updateEpisode(params.id, params)
      .then(function (res) {
        $scope.message_success = "Episode updated successfully!";
        setTimeout(function () {
          $scope.isLoading = false;
          $state.go('nav.episodes');
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
      .title('Delete this episode?')
      .textContent('Episode data will be deleted.')
      .ariaLabel('Lucky day')
      .targetEvent(ev)
      .ok('Delete')
      .cancel('Cancel');

    $mdDialog.show(confirm).then(function () {
      deleteEpisode();
    }, function () {
    });
  };

  function deleteEpisode() {
    $scope.isLoading = true;
    var params = $scope.body;
    user.deleteEpisode(params.id)
      .then(function (res) {
        $scope.message_success = "Episode deleted successfully!";
        setTimeout(function () {
          $scope.isLoading = false;
          $state.go('nav.episodes');
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