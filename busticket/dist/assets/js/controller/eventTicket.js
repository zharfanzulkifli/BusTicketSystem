$(document.body).on("submit", "#createticketForm", function (e) {

    e.preventDefault();

    var destfrom = $("#destfrom").val();
    var destto = $("#destto").val();
    var date = $("#date").val();
    var max = $("#max").val();
    var price = $("#price").val();

    var obj = new Object();
    obj.destfrom = destfrom;
    obj.destto = destto;
    obj.date = date;
    obj.max = max;
    obj.price = price;

    $.ajax({
        type: "POST",
        url: 'assets/api/ticket',
        contentType: 'application/json',
        data: JSON.stringify(obj),
        dataType: "json",
        success: function (data) {

            if (data.insertStatus) {

                alert("Ticket creation successful");

                window.location.href = "#ticket";

            }
            else {
                alert("Ticket creation failed - please try again: " + data.errorMessage)
            }
        },
        error: function (xhr, statusText, err) {

            if (xhr.status == 401) {
                //response text from the server if there is any
                var responseText = JSON.parse(xhr.responseText);
                bootbox.alert("Error 401 - Unauthorized: " + responseText.message);

                $("#loginname").html("noname");
                sessionStorage.removeItem("token");
                sessionStorage.removeItem("login");
                window.location.href = "#login";
                return;
            }

            if (xhr.status == 404) {
                bootbox.alert("Error 404 - API resource not found at the server");
            }

        }
    });
});

$(document.body).on("submit", "#editticketForm", function (e) {

    e.preventDefault();

    var id = $("#id").val();
    var destfrom = $("#destfrom").val();
    var destto = $("#destto").val();
    var date = $("#date").val();
    var max = $("#max").val();
    var price = $("#price").val();

    var obj = new Object();
    obj.destfrom = destfrom;
    obj.destto = destto;
    obj.date = date;
    obj.max = max;
    obj.price = price;

    $.ajax({
        type: "PUT",
        url: 'assets/api/ticket/' + id,
        contentType: 'application/json',
        data: JSON.stringify(obj),
        dataType: "json",
        success: function (data) {
            if (data.updateStatus) {

                alert("Ticket update successful");

                window.location.href = "#ticket";

            }
            else {
                alert("Ticket update failed - please try again: " + data.errorMessage)
            }
        },
        error: function (xhr, statusText, err) {

            if (xhr.status == 401) {
                //response text from the server if there is any
                var responseText = JSON.parse(xhr.responseText);
                bootbox.alert("Error 401 - Unauthorized: " + responseText.message);

                $("#loginname").html("noname");
                sessionStorage.removeItem("token");
                sessionStorage.removeItem("login");
                window.location.href = "#login";
                return;
            }

            if (xhr.status == 404) {
                bootbox.alert("Error 404 - API resource not found at the server");
            }

        }
    });
});