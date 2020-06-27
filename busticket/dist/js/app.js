$(function(){

    function parseHash(newHash, oldHash){
        crossroads.parse(newHash);
    }

    var routeticket = crossroads.addRoute('/ticket', function(){

        $.get('js/templates/ticket.handlebars').then(function(src) {
          var ticketTemplate = Handlebars.compile(src);
  
            $("#divcontent").empty();
            $("#divcontent").html(ticketTemplate).hide().fadeIn(1000);

            $.ajax({
                type: "get", 
                url: 'api/ticket.php',
                dataType: "json",
                success: function(data){
                    var index = 1;
                    for (var x in data) { 
                        
                        index = parseInt(index) + parseInt(x);

                        $("#datatable tbody").append("<tr>" +
                                            "   <td>" + index + "</td>" +
                                            "   <td>" + data[x].id + "</td>" +
                                            "   <td>" + data[x].destfrom + "</td>" + 
                                            "   <td>" + data[x].destto + "</td>" +
                                            "   <td>" + data[x].date + "</td>" +
                                            "   <td>" + data[x].quantity +" / "+ data[x].max + "</td>" +
                                            "   <td>" + data[x].price + "</td>" +
                                            "   <td>" +
                                            "<a href='#ticket/edit' class='btn btn-icon waves-effect waves-light btn-dark'> <i class='fa fa-pencil-alt'></i></a> " +
                                            "<button class='btn btn-icon waves-effect waves-light btn-danger'> <i class='fa fa-trash'></i> </button>" +
                                            "  </td>" +
                                            "</tr>");
                    }
                },
                error: function() {
                    console.log("error");
                }
            });
    
            $(".breadcrumb").empty();
            $(".breadcrumb").append("<li class='breadcrumb-item'><a href='javascript: void(0);'>Home</a></li>");
            $(".breadcrumb").append("<li class='breadcrumb-item'><a href='javascript: void(0);'>ABC</a></li>");
            $(".breadcrumb").append("<li class='breadcrumb-item active'>XYZ</li>");

            $(".page-title").empty();
            $(".page-title").append("Bus Ticket");
  
            $(".navbar-collapse li").removeClass('active');
            $(".navbar-collapse li a[href='#contacts']").parent().addClass('active');
        });
  
      });
      
      var routecreateticket = crossroads.addRoute('/ticket/add', function(){

        $.get('js/templates/ticket-create.handlebars').then(function(src) {
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

      var routeeditticket = crossroads.addRoute('/ticket/edit', function(){

        $.get('js/templates/ticket-edit.handlebars').then(function(src) {
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