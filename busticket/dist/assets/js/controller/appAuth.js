$(function () {

    function parseHash(newHash, oldHash) {
        crossroads.parse(newHash);
    }
    crossroads.ignoreState = true;

    var routeregister = crossroads.addRoute('/register', function () {

        if (sessionStorage.token) {
            window.location.href = "#home";
            return;
        }

        $.get('js/templates/register.handlebars').then(function (src) {
            var createRegisterTemplate = Handlebars.compile(src);

            $("#divcontent").empty();
            $("#divcontent").html(createRegisterTemplate).hide().fadeIn(100);
        });

    });
    //add by akif 28/6/2020
    var routelogin = crossroads.addRoute('/login', function () {

        if (sessionStorage.token) {
            window.location.href = "#home";
            return;
        }

        $.get('js/templates/login.handlebars').then(function (src) {
            var createLoginTemplate = Handlebars.compile(src);

            $("#divcontent").empty();
            $("#divcontent").html(createLoginTemplate).hide().fadeIn(100);

        });

    });
    //add by akif 28/6/2020
    var routelogout = crossroads.addRoute('/logout', function () {

        $("#loginname").html("noname");
        sessionStorage.removeItem("token");
        window.location.href = "#login";
        return;

    });

    hasher.initialized.add(parseHash); //parse initial hash
    hasher.changed.add(parseHash); //parse hash changes
    hasher.init(); //start listening for history change

});