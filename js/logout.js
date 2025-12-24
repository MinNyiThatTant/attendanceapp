$(function (e) {
  $(document).on("click", "#btnlogout", function (e) {
    $.ajax({
      url: "ajaxhandler/logoutAjax.php",
      type: "POST",
      dataType: "json",
      data: { id: 1 },
      beforeSend: function (e) {},
      success: function (e) {
        document.location.replace("login.php");
      },
      error: function (e) {
        alert("An error occurred while making the ajax call");
      },
    });
  });
});
