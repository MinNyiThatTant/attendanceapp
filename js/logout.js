$(function () {
  $(document).on("click", "#btnlogout", function (e) { 
    if (confirm("Are you sure you want to logout?")) {
      $.ajax({
        url: "ajaxhandler/logoutAjax.php",
        type: "POST",
        dataType: "json",
        data: { action: "logout" },
        success: function (response) {
          
          document.location.replace("login.php");
        },
        error: function () {
          alert("An error occurred while making the ajax call");
        },
      });
    }
  });
});