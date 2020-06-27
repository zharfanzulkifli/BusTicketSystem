$(function () {

    function parseHash(newHash, oldHash) {
        crossroads.parse(newHash);
    }

    crossroads.addRoute('/booking', function () {
        $.ajax({
            type: "GET",
            url: 'assets/api/booking.php',               
            dataType: "json",
            success: function(data){
                $.get('assets/js/templates/booking.handlebars').then(function(src){
                    var template = Handlebars.compile(src);
                    var html = template({"bookings":data});
                    $('body').empty();
                    $('body').html(html);
                })
                console.log({"bookings":data});
            },
            error: function(xhr, statusText, err) {
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