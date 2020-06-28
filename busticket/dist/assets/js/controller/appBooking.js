$(function () {

    // $.ajaxSetup({
    //     beforeSend: function (xhr) {
    //         var token = sessionStorage.getItem("token");
    //         xhr.setRequestHeader("Authorization", "Bearer " + token);
    //     }
    // });

    function parseHash(newHash, oldHash) {
        crossroads.parse(newHash);
    }
    crossroads.ignoreState = true;
    console.log("test2");
    var bookingRoute = crossroads.addRoute('/booking', function () {
        console.log("test");
        $.ajax({
            type: "GET",
            url: 'assets/api/booking',
            dataType: "json",
            success: function (data) {
                $.get('assets/js/templates/booking.handlebars').then(function (src) {
                    var template = Handlebars.compile(src);
                    var html = template({ "bookings": data });
                    // $('body').empty();
                    // // $('body').html(html);
                    // $('body').append(html);
                    $("#divcontent").empty();
                    $("#divcontent").html(html);
                })
                console.log({ "bookings": data });
            },
            error: function (xhr, statusText, err) {
                $('body').empty();
                console.log("error");
                console.log(xhr);
                console.log(statusText);
                console.log(err);
            }
        })
    });
    crossroads.addRoute('/booking/flipstatus/', function () {
        console.log("test");
        $.ajax({
            type: "PUT",
            url: 'assets/api/booking',
            dataType: "json",
            success: function (data) {
                // $.get('assets/js/templates/booking.handlebars').then(function (src) {
                //     var template = Handlebars.compile(src);
                //     var html = template({ "bookings": data });
                //     $("#divcontent").empty();
                //     $("#divcontent").html(html).hide().fadeIn(1000);
                // })
                // console.log({ "bookings": data });
                console.log("hoho");
            },
            error: function (xhr, statusText, err) {
                // $('body').empty();
                console.log("error");
                console.log(xhr);
                console.log(statusText);
                console.log(err);
            }
        })
    });

    hasher.initialized.add(parseHash); //parse initial hash
    hasher.changed.add(parseHash); //parse hash changes
    hasher.init(); //start listening for history change

});