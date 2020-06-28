
   //add by akif 28/6/2020
   //register submit form
   $(document.body).on("submit", "#registerform", function (e) {
      console.log("212");
      e.preventDefault();

      //get the value from the form
      var email = $("#registeremail").val();
      var password = $("#registerpassword").val();
      var username = $("#registerusername").val();

      var obj = new Object();
      obj.email = email;
      obj.password = password;
      obj.username = username;

      $.ajax({
         type: "post",
         url: 'assets/api/registration',
         contentType: 'application/json',
         data: JSON.stringify(obj),
         dataType: "json",
         success: function (data) {
            console.log("2");
            if (data.registrationStatus) {
              // bootbox.alert("Registration successful");
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

   $(document.body).on("submit", "#loginform", function (e) {
      e.preventDefault();

		//get the value from the form
		var email = $("#loginemail").val();
		var password = $("#loginpassword").val();
		
	   var obj = new Object();
	   obj.email = email;
	   obj.password = password;

	   $.ajax({
	      type: "post",
	      url: 'assets/api/auth',
	      contentType: 'application/json',      
	      data: JSON.stringify(obj),            
	      dataType: "json",
	      success: function(data){

            if (data.loginStatus) {

               //bootbox.alert("Login successful");

               sessionStorage.setItem("token", data.token);

               //redirect to the /#home
		         window.location.href = "#ticket";

            } 
            else {

               //bootbox.alert("Login failed [" + data.errorMessage + "] - please try again!");
            }
		   },
		   error: function() {
		      console.log("error");
		   }
		});

   });