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

    Handlebars.registerHelper("displaystatus", function(status){
        if (status == 0){
            return "<span class='badge badge-danger'>Pending</span>";
        } else if (status == 1) {
            return "<span class='badge badge-success'>Accepted</span>";
        }
    });

    crossroads.ignoreState = true;
    var bookingRoute = crossroads.addRoute('/booking', function () {
        $.ajax({
            type: "GET",
            url: 'assets/api/booking',
            dataType: "json",
            success: function (data) {
                $.get('assets/js/templates/booking.handlebars').then(function (src) {
                    var template = Handlebars.compile(src);
                    var html = template({ "bookings": data });
                    $("#divcontent").empty();
                    $("#divcontent").html(html);
                })
            },
            error: function (xhr, statusText, err) {
                console.log("error");
                console.log(xhr);
                console.log(statusText);
                console.log(err);
            }
        })
    });
    // crossroads.addRoute('/booking/flipstatus/{id}', function ($id) {
    //     $.ajax({
    //         type: "PUT",
    //         url: 'assets/api/booking/flipstatus/' + $id,
    //         dataType: "json",
    //         success: function (data) {
    //             $.ajax({
    //                 type: "GET",
    //                 url: 'assets/api/booking',
    //                 dataType: "json",
    //                 success: function (data) {
    //                     $.get('assets/js/templates/booking.handlebars').then(function (src) {
    //                         var template = Handlebars.compile(src);
    //                         var html = template({ "bookings": data });
    //                         $("#divcontent").empty();
    //                         $("#divcontent").html(html);
    //                     })
    //                 },
    //                 error: function (xhr, statusText, err) {
    //                     console.log("error");
    //                     console.log(xhr);
    //                     console.log(statusText);
    //                     console.log(err);
    //                 }
    //             })
    //         },
    //         error: function (xhr, statusText, err) {
    //             // $('body').empty();
    //             console.log("error");
    //             console.log(xhr);
    //             console.log(statusText);
    //             console.log(err);
    //         }
    //     })
    // });

    hasher.initialized.add(parseHash); //parse initial hash
    hasher.changed.add(parseHash); //parse hash changes
    hasher.init(); //start listening for history change

});