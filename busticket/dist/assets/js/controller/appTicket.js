$(function () {

    function parseHash(newHash, oldHash) {
        crossroads.parse(newHash);
    }
    crossroads.ignoreState = true;

    var viewticketRoute = crossroads.addRoute('/ticket', function () {

        $.ajax({
            type: "GET",
            url: 'assets/api/ticket',
            dataType: "json",
            success: function (data) {
                $.get('assets/js/templates/ticket.handlebars').then(function (src) {

                    var template = Handlebars.compile(src);
                    var html = template({ "ticketlist": data });

                    $("#divcontent").empty();
                    $("#divcontent").html(html);

                    $(".page-title").empty();
                    $(".page-title").append("Bus Ticket");

                    $(".breadcrumb").empty();
                    $(".breadcrumb").append("<li class='breadcrumb-item'><a href='#home'>Home</a></li>");
                    $(".breadcrumb").append("<li class='breadcrumb-item active'>Ticket</li>");

                })
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

    var createTicketRoute = crossroads.addRoute('/ticket/add', function () {

        $.get('assets/js/templates/ticket-create.handlebars').then(function (src) {

            var template = Handlebars.compile(src);

            $("#divcontent").empty();
            $("#divcontent").html(template);

            $(".page-title").empty();
            $(".page-title").append("Create New Bus Ticket");

            $(".breadcrumb").empty();
            $(".breadcrumb").append("<li class='breadcrumb-item'><a href='#home'>Home</a></li>");
            $(".breadcrumb").append("<li class='breadcrumb-item'><a href='#ticket'>Ticket</a></li>");
            $(".breadcrumb").append("<li class='breadcrumb-item active'>Create New Ticket</li>");
        })

    });

    var editTicketRoute = crossroads.addRoute('/ticket/edit/{id}', function (id) {

        $.ajax({
            type: "GET",
            url: 'assets/api/ticket/' + id,
            dataType: "json",
            success: function (data) {
                $.get('assets/js/templates/ticket-edit.handlebars').then(function (src) {

                    var template = Handlebars.compile(src);
                    var html = template(data);

                    $("#divcontent").empty();
                    $("#divcontent").html(html);

                    $(".page-title").empty();
                    $(".page-title").append("Edit Bus Ticket");

                    $(".breadcrumb").empty();
                    $(".breadcrumb").append("<li class='breadcrumb-item'><a href='#home'>Home</a></li>");
                    $(".breadcrumb").append("<li class='breadcrumb-item'><a href='#ticket'>Ticket</a></li>");
                    $(".breadcrumb").append("<li class='breadcrumb-item active'>Edit Ticket</li>");

                })
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

    hasher.initialized.add(parseHash); //parse initial hash
    hasher.changed.add(parseHash); //parse hash changes
    hasher.init(); //start listening for history change

});