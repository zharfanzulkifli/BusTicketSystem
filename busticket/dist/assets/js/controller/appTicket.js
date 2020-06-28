$(function () {

    function parseHash(newHash, oldHash) {
        crossroads.parse(newHash);
    }
    crossroads.ignoreState = true;

    var ticketRoute = crossroads.addRoute('/ticket', function () {

        $.ajax({
            type: "GET",
            url: 'assets/api/ticket',
            dataType: "json",
            success: function (data) {
                $.get('assets/js/templates/ticket.handlebars').then(function (src) {
                    var template = Handlebars.compile(src);
                    var html = template({ "ticketlist": data });
                    // $('body').empty();
                    // // $('body').html(html);
                    // $('body').append(html);
                    $("#divcontent").empty();
                    $("#divcontent").html(html);
                })
                console.log({ "ticketlist": data });
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

    var routecreateticket = crossroads.addRoute('/ticket/add', function () {

        $.get('js/templates/ticket-create.handlebars').then(function (src) {
            var createticketTemplate = Handlebars.compile(src);

            $("#divcontent").empty();
            $("#divcontent").html(createticketTemplate).hide().fadeIn(1000);

            $(".breadcrumb").empty();
            $(".breadcrumb").append("<li class='breadcrumb-item'><a href='javascript: void(0);'>Home</a></li>");
            $(".breadcrumb").append("<li class='breadcrumb-item'><a href='javascript: void(0);'>ABC</a></li>");
            $(".breadcrumb").append("<li class='breadcrumb-item active'>DEF</li>");

            $(".page-title").empty();
            $(".page-title").append("Create New Ticket");

            $(".navbar-collapse li").removeClass('active');
            $(".navbar-collapse li a[href='#contacts']").parent().addClass('active');
        });

    });

    var routeeditticket = crossroads.addRoute('/ticket/edit', function () {

        $.get('js/templates/ticket-edit.handlebars').then(function (src) {
            var editticketTemplate = Handlebars.compile(src);

            $("#divcontent").empty();
            $("#divcontent").html(editticketTemplate).hide().fadeIn(1000);

            $(".breadcrumb").empty();
            $(".breadcrumb").append("<li class='breadcrumb-item'><a href='javascript: void(0);'>Home</a></li>");
            $(".breadcrumb").append("<li class='breadcrumb-item'><a href='javascript: void(0);'>ABC</a></li>");
            $(".breadcrumb").append("<li class='breadcrumb-item active'>DEF</li>");

            $(".page-title").empty();
            $(".page-title").append("Edit Ticket");

            $(".navbar-collapse li").removeClass('active');
            $(".navbar-collapse li a[href='#contacts']").parent().addClass('active');
        });

    });

    hasher.initialized.add(parseHash); //parse initial hash
    hasher.changed.add(parseHash); //parse hash changes
    hasher.init(); //start listening for history change

});