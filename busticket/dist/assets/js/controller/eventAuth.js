$(function () {
   //add by akif 28/6/2020
   //register submit form
   $(document.body).on("submit", "#registerform", function (e) {

      e.preventDefault();

      //get the value from the form
      var username = $("#registerformusername").val();
      var password = $("#registerformpassword").val();

      var obj = new Object();
      obj.username = username;
      obj.password = password;

      $.ajax({
         type: "post",
         url: 'api/registration',
         contentType: 'application/json',
         data: JSON.stringify(obj),
         dataType: "json",
         success: function (data) {

            if (data.registrationStatus) {

               bootbox.alert("Registration successful");

               //redirect to the /#login
               window.location.href = "#login";

            }
            else {

               bootbox.alert("Registration failed [" + data.errorMessage + "] - please try again!");
            }
         },
         error: function () {
            console.log("error");
         }
      });

   });
});