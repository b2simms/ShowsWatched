app.config(function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/login');
    $stateProvider
    .state('nav', {
        templateUrl: 'templates/navbar.html',
        controller: 'NavCtrl'
    })
    .state('login', {
        url: '/login',
        params: {username:null,register:null},
        templateUrl: 'templates/login.html',
        controller: 'LoginCtrl'
    })
    .state('register', {
        url: '/register',
        templateUrl: 'templates/register.html',
        controller: 'RegisterCtrl'
    })
    .state('nav.series', {
        url: '/series',
        templateUrl: 'templates/series.html',
        controller: 'SeriesCtrl'
    })
    .state('series_create', {
        url: '/series_create',
        templateUrl: 'templates/series_create.html',
        controller: 'SeriesCreateCtrl'
    })
    .state('series_edit', {
        url: '/series_edit',
        templateUrl: 'templates/series_edit.html',
        controller: 'SeriesEditCtrl'
    })
    .state('nav.episodes', {
        url: '/episodes',
        templateUrl: 'templates/episodes.html',
        controller: 'EpisodesCtrl'
    })
    .state('episodes_create', {
        url: '/episodes_create',
        templateUrl: 'templates/episodes_create.html',
        controller: 'EpisodesCreateCtrl'
    })
    .state('episodes_edit', {
        url: '/episodes_edit',
        templateUrl: 'templates/episodes_edit.html',
        controller: 'EpisodesEditCtrl'
    })
});
