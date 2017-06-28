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
    .state('forgot_password', {
        url: '/forgot_password',
        templateUrl: 'templates/forgotpassword.html',
        controller: 'ForgotPasswordCtrl'
    })
    .state('password_recovery', {
        url: '/password_recovery?requestID',
        templateUrl: 'templates/passwordrecovery.html',
        controller: 'PasswordRecoveryCtrl'
    })
    .state('nav.user_info', {
        url: '/user_info',
        templateUrl: 'templates/userinfo.html',
        controller: 'UserInfoCtrl'
    })
    .state('nav.series', {
        url: '/series',
        templateUrl: 'templates/series.html',
        controller: 'SeriesCtrl'
    })
    .state('series_create', {
        url: '/series_create',
        params: {'name':null,'description':null,'preexisting_id':null},
        templateUrl: 'templates/series_create.html',
        controller: 'SeriesCreateCtrl'
    })
    .state('series_edit', {
        url: '/series_edit',
        templateUrl: 'templates/series_edit.html',
        controller: 'SeriesEditCtrl'
    })
     .state('series_preexisting', {
        url: '/series_preexisting',
        templateUrl: 'templates/series_preexisting.html',
        controller: 'SeriesPreexistingCtrl'
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
